<?php

namespace App\Http\Controllers\Web;

use App\Enum\DonationMatchStatus;
use App\Http\Controllers\Controller;
use App\Models\DonationItemMatch;
use App\Models\DonationRequest;
use App\Models\Donation;
use App\Services\MatchingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MatchController extends Controller
{
    protected $matchingService;

    public function __construct(MatchingService $matchingService)
    {
        $this->matchingService = $matchingService;
    }

    private function logInfo(string $message, array $context = [])
    {
        Log::info('[Web] [MatchController] '.$message, $context);
    }

    private function logError(string $message, ?\Throwable $e = null, array $context = [])
    {
        Log::error(
            '[Web] [MatchController] '.$message,
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
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = Auth::user();

            // Get matches where user is involved
            $matches = DonationItemMatch::with([
                'donationItem.donation.user',
                'donationItem.category',
                'requestItem.request.user',
                'requestItem.category',
            ])
                ->whereHas('donationItem.donation', function ($q) use ($user) {
                    $q->where('user_id', $user->user_id);
                })
                ->orWhereHas('requestItem.request', function ($q) use ($user) {
                    $q->where('user_id', $user->user_id);
                })
                ->where('status', DonationMatchStatus::Pending)
                ->latest()
                ->paginate(12);

            return view('dashboard.donation-match.index', compact('matches'));
        } catch (Exception $e) {
            $this->logError('Failed to render index page', $e, []);

            return redirect()->back()->with('error', 'Gagal memuat halaman. Coba lagi nanti');
        }
    }

    /**
     * Accept a match
     */
    public function accept($id, Request $request)
    {
        $match = DonationItemMatch::with(['donationItem.donation', 'requestItem.request'])
            ->findOrFail($id);

        // Check authorization
        $user = Auth::user();
        $isDonor = $match->donationItem->donation->user_id == $user->user_id;
        $isRequester = $match->requestItem->request->user_id == $user->user_id;

        if (! $isDonor && ! $isRequester) {
            abort(403, 'Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $this->matchingService->acceptMatch($id, $request->quantity);

            return redirect()->route('admin.donation-match.index')
                ->with('success', 'Match berhasil diterima.');

        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a match
     */
    public function reject($id)
    {
        $match = DonationItemMatch::with(['donationItem.donation', 'requestItem.request'])
            ->findOrFail($id);

        // Check authorization
        $user = Auth::user();
        $isDonor = $match->donationItem->donation->user_id == $user->user_id;
        $isRequester = $match->requestItem->request->user_id == $user->user_id;

        if (! $isDonor && ! $isRequester) {
            abort(403, 'Unauthorized');
        }

        try {
            $this->matchingService->rejectMatch($id);

            return redirect()->route('admin.donation-match.index')
                ->with('success', 'Match berhasil ditolak.');

        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $match = DonationItemMatch::with([
                'donationItem.donation.user',
                'donationItem.donation.location',
                'donationItem.category',
                'requestItem.request.user',
                'requestItem.request.location',
                'requestItem.category',
            ])->findOrFail($id);

            // Check authorization
            $user = Auth::user();
            $isDonor = $match->donationItem->donation->user_id == $user->user_id;
            $isRequester = $match->requestItem->request->user_id == $user->user_id;

            if (! $isDonor && ! $isRequester && ! $user->isAdmin()) {
                abort(403, 'Unauthorized');
            }

            return view('dashboard.donation-match.show', compact('match'));

        } catch (Exception $e) {
            $this->logError('Failed to render show page', $e, [
                'id' => $id,
            ]);

            return redirect()->back()->with('error', 'Gagal memuat halaman. Coba lagi nanti');
        }
    }

    /**
     * Show matches for a specific donation request
     */
    public function forRequest($requestId)
    {
        $request = DonationRequest::findOrFail($requestId);
        
        // Check authorization
        if ($request->user_id != Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }
        
        $matches = DonationItemMatch::with([
            'donationItem.donation.user',
            'donationItem.category'
        ])
        ->whereHas('requestItem', function ($q) use ($requestId) {
            $q->where('donation_request_id', $requestId);
        })
        ->latest()
        ->paginate(10);
        
        return view('dashboard.donation-match.request-match', compact('matches', 'request'));
    }

    /**
     * Show matches for a specific donation
     */
    public function forDonation($donationId)
    {
        $donation = Donation::findOrFail($donationId);
        
        // Check authorization
        if ($donation->user_id != Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }
        
        $matches = DonationItemMatch::with([
            'requestItem.request.user',
            'requestItem.category'
        ])
        ->whereHas('donationItem', function ($q) use ($donationId) {
            $q->where('donation_id', $donationId);
        })
        ->latest()
        ->paginate(10);
        
        return view('dashboard.donation-match.donation-match', compact('matches', 'donation'));
    }
}
