<?php

namespace App\Services;

use App\Enum\DonationMatchStatus;
use App\Enum\DonationRequestCondition;
use App\Enum\DonationRequestStatus;
use App\Enum\DonationStatus;
use App\Models\Donation;
use App\Models\DonationItemMatch;
use App\Models\DonationItems;
use App\Models\DonationRequest;
use App\Models\DonationRequestItems;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MatchingService
{
    private function logInfo(string $message, array $context = [])
    {
        Log::info('[Services] [MatchingService] '.$message, $context);
    }

    private function logError(string $message, ?\Throwable $e = null, array $context = [])
    {
        Log::error(
            '[Services] [MatchingService] '.$message,
            array_merge(
                [
                    'exception' => $e?->getMessage(),
                    'trace' => $e?->getTraceAsString(),
                ],
                $context,
            ),
        );
    }

    /**
     * Find matches for a donation request
     */
    public function findMatchesForRequest(DonationRequest $donationRequest)
    {
        $matches = collect();

        foreach ($donationRequest->items as $requestItem) {
            if ($requestItem->getRemainingQuantityAttribute() <= 0) {
                continue;
            }

            // Find matching donation items
            $matchingItems = DonationItems::where('category_id', $requestItem->category_id)
                ->where('condition', '>=', $requestItem->preferred_condition)
                ->where('status', DonationStatus::Available)
                ->where('quantity', '>', DB::raw('reserved_quantity'))
                ->with(['donation.location', 'donation.user'])
                ->get();

            foreach ($matchingItems as $donationItem) {
                $score = $this->calculateMatchScore($requestItem, $donationItem, $donationRequest);

                $matches->push([
                    'donation_item' => $donationItem,
                    'request_item' => $requestItem,
                    'score' => $score,
                    'available_quantity' => $donationItem->getAvailableQuantityAttribute(),
                    'needed_quantity' => $requestItem->getRemainingQuantityAttribute(),
                    'distance' => $this->calculateDistance(
                        $donationRequest->location,
                        $donationItem->donation->location
                    ),
                ]);
            }
        }

        // Sort by score descending
        return $matches->sortByDesc('score')->values();
    }

    /**
     * Find matches for a donation
     */
    public function findMatchesForDonation(Donation $donation)
    {
        $matches = collect();

        foreach ($donation->items as $donationItem) {
            if ($donationItem->getAvailableQuantityAttribute() <= 0) {
                continue;
            }

            // Find matching request items
            $matchingItems = DonationRequestItems::where('category_id', $donationItem->category_id)
                ->where('preferred_condition', '<=', $donationItem->condition)
                ->where('status', DonationRequestStatus::Pending)
                ->whereRaw('quantity > fulfilled_quantity')
                ->with(['request.location', 'request.user', 'request'])
                ->get();

            foreach ($matchingItems as $requestItem) {
                $score = $this->calculateMatchScore($requestItem, $donationItem, $requestItem->request);

                $matches->push([
                    'request_item' => $requestItem,
                    'donation_item' => $donationItem,
                    'score' => $score,
                    'available_quantity' => $donationItem->getAvailableQuantityAttribute(),
                    'needed_quantity' => $requestItem->getRemainingQuantityAttribute(),
                    'distance' => $this->calculateDistance(
                        $requestItem->request->location,
                        $donation->location
                    ),
                ]);
            }
        }

        // Sort by score descending
        return $matches->sortByDesc('score')->values();
    }

    /**
     * Match donation to a specific request
     */
    public function matchDonationToRequest(Donation $donation, $requestId)
    {
        $request = DonationRequest::with('items')->findOrFail($requestId);
        $matchesCreated = 0;

        DB::beginTransaction();

        try {
            foreach ($donation->items as $donationItem) {
                foreach ($request->items as $requestItem) {
                    if ($this->canMatch($requestItem, $donationItem)) {
                        $score = $this->calculateMatchScore($requestItem, $donationItem, $request);
                        $matchQuantity = min(
                            $donationItem->getAvailableQuantityAttribute(),
                            $requestItem->getRemainingQuantityAttribute()
                        );

                        if ($matchQuantity > 0) {
                            DonationItemMatch::create([
                                'donation_item_match_id' => Str::uuid(),
                                'donation_item_id' => $donationItem->donation_item_id,
                                'donation_request_item_id' => $requestItem->donation_request_item_id,
                                'matched_quantity' => $matchQuantity,
                                'score' => $score,
                                'status' => DonationMatchStatus::Pending,
                            ]);

                            $matchesCreated++;
                        }
                    }
                }
            }

            DB::commit();

            return $matchesCreated;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate match score between request item and donation item
     */
    private function calculateMatchScore(DonationRequestItems $requestItem, DonationItems $donationItem, DonationRequest $request)
    {
        $score = 0;

        // 1. Category match (50 points)
        if ($donationItem->category_id === $requestItem->category_id) {
            $score += 50;
        }

        // 2. Condition match (30 points)
        // Gunakan getNumericValue() dari enum
        $donationConditionValue = $donationItem->condition?->getNumericValue() ?? 2; // Default good_used
        $requestConditionValue = $requestItem->preferred_condition?->getNumericValue() ?? 2; // Default good_used

        if ($donationConditionValue >= $requestConditionValue) {
            $score += 30;
            // Bonus for better condition
            if ($donationConditionValue > $requestConditionValue) {
                $score += 10;
            }
        }

        // 3. Quantity availability (20 points max)
        $quantityRatio = min($donationItem->getAvailableQuantityAttribute() / $requestItem->getRemainingQuantityAttribute(), 1);
        $score += round(20 * $quantityRatio);

        // 4. Priority bonus (10 points for urgent)\

        $score += $requestItem->priority?->getPriorityScore() ?? 0;

        // 5. Location proximity (up to 30 points)
        if ($request->location && $donationItem->donation->location) {
            $distance = $this->calculateDistance($request->location, $donationItem->donation->location);
            if ($distance < 5) { // Within 5km
                $score += 30;
            } elseif ($distance < 10) { // Within 10km
                $score += 20;
            } elseif ($distance < 20) { // Within 20km
                $score += 10;
            }
        }

        return min($score, 150); // Cap at 150
    }

    /**
 * Check if items can be matched
 */
private function canMatch(DonationRequestItems $requestItem, DonationItems $donationItem)
{
    // Check category
    if ($donationItem->category_id !== $requestItem->category_id) {
        return false;
    }
    
    // Check condition using enum numeric values
    $donationConditionValue = $donationItem->condition?->getNumericValue() ?? 2;
    $requestConditionValue = $requestItem->preferred_condition?->getNumericValue() ?? 2;
    
    if ($donationConditionValue < $requestConditionValue) {
        return false;
    }
    
    // Check availability
    if ($donationItem->getAvailableQuantityAttribute() <= 0) {
        return false;
    }
    
    // Check if still needed
    if ($requestItem->getRemainingQuantityAttribute() <= 0) {
        return false;
    }
    
    return true;
}

    /**
     * Calculate distance between two locations (simplified)
     */
    private function calculateDistance($location1, $location2)
    {
        if (! $location1 || ! $location2 ||
            ! $location1->latitude || ! $location1->longitude ||
            ! $location2->latitude || ! $location2->longitude) {
            return 999; // Large distance if unknown
        }

        // Haversine formula for distance calculation
        $lat1 = deg2rad($location1->latitude);
        $lon1 = deg2rad($location1->longitude);
        $lat2 = deg2rad($location2->latitude);
        $lon2 = deg2rad($location2->longitude);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat / 2) * sin($dlat / 2) +
             cos($lat1) * cos($lat2) *
             sin($dlon / 2) * sin($dlon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Earth radius in kilometers
        $earthRadius = 6371;

        return $earthRadius * $c;
    }

    /**
     * Accept a match
     */
    public function acceptMatch($matchId, $quantity)
    {
        $match = DonationItemMatch::with(['donationItem', 'requestItem'])->findOrFail($matchId);

        DB::beginTransaction();

        try {
            // Check if still valid
            if (! $match->isPending()) {
                throw new Exception('Match tidak dalam status pending.');
            }

            // Check quantities
            $maxQuantity = min(
                $match->donationItem->getAvailableQuantityAttribute(),
                $match->requestItem->getRemainingQuantityAttribute(),
                $match->matched_quantity
            );

            if ($quantity > $maxQuantity) {
                throw new Exception("Jumlah maksimum yang dapat diterima adalah {$maxQuantity}");
            }

            // Update donation item
            $match->donationItem->reserveQuantity($quantity);

            // Update request item
            $match->requestItem->updateFulfilledQuantity($quantity);

            // Update match
            $match->status = DonationMatchStatus::Accepted;
            $match->save();

            // Check if donation is now fully reserved
            if ($match->donationItem->donation->getAvailableItemsAttribute() <= 0) {
                $match->donationItem->donation->update(['status' => DonationStatus::Reserved]);
            }

            // Check if request is now fulfilled
            if ($match->requestItem->request->getFulfilledItemsAttribute() >=
                $match->requestItem->request->getTotalItemsAttribute()) {
                $match->requestItem->request->update(['status' => DonationRequestStatus::Fulfilled]);
            }

            DB::commit();

            return $match;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reject a match
     */
    public function rejectMatch($matchId)
    {
        $match = DonationItemMatch::findOrFail($matchId);

        if (! $match->isPending()) {
            throw new Exception('Hanya match yang pending dapat ditolak.');
        }

        $match->status = DonationMatchStatus::Rejected;
        $match->save();

        return $match;
    }
}
