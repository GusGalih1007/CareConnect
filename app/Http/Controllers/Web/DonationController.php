<?php

namespace App\Http\Controllers\Web;

use App\Enum\DonationStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Donation;
use App\Models\DonationItems;
use App\Models\DonationRequest;
use App\Models\Location;
use App\Services\MatchingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DonationController extends Controller
{
    private function logInfo(string $message, array $context = [])
    {
        Log::info('[Web] [DonationController] '.$message, $context);
    }

    private function logError(string $message, ?\Throwable $e = null, array $context = [])
    {
        Log::error(
            '[Web] [DonationController] '.$message,
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

            $donations = Donation::with(['donationItem.category', 'location', 'donationRequest'])
                ->where('user_id', $user->user_id)
                ->latest()
                ->paginate(12);

            return view('dashboard.donation.index', compact('donations'));
        } catch (Exception $e) {
            $this->logError('Failed to render index page', $e, []);

            return redirect()->back()->with('error', 'Gagal memuat halaman. Coba lagi nanti');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $donation = null;
            $categories = Category::all();
            $userLocations = Location::where('user_id', Auth::id())->get();
            $activeRequests = DonationRequest::active()->get();

            return view('dashboard.donation.form', compact('categories', 'userLocations', 'activeRequests', 'donation'));
        } catch (Exception $e) {
            $this->logError('Failed to render form page', $e, []);

            return redirect()->back()->with('error', 'Gagal memuat halaman. Coba lagi nanti');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->logInfo('Donation store attempt', [
            'request_body' => $request->all(),
        ]);
        $validate = Validator::make($request->all(), [
            'title' => 'required|string|max:150',
            'general_description' => 'nullable|string',
            'donation_type' => 'required|in:single_item,multiple_items',
            'request_id' => 'nullable|exists:donation_requests,donation_request_id',
            'location_id' => 'required|exists:locations,location_id',

            // Items validation
            'items' => 'required|array|min:1',
            'items.*.category_id' => 'required|exists:categories,category_id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.condition' => 'required|in:new,good_used,needs_repair',
            'items.*.images' => 'nullable|array',
            'items.*.images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validate->fails()) {
            $this->logError('Validation Error', null, [
                'error' => $validate->errors(),
            ]);

            return redirect()->back()
                ->withErrors($validate)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Create donation
            $donation = Donation::create([
                'user_id' => Auth::id(),
                'request_id' => $request->request_id,
                'title' => $request->title,
                'general_description' => $request->general_description,
                'donation_type' => $request->donation_type,
                'status' => DonationStatus::Available,
                'location_id' => $request->location_id,
            ]);

            $this->logInfo('Donation created', [
                'donation_id' => $donation->donation_id,
                'user_id' => Auth::id(),
            ]);

            // Create donation items
            foreach ($request->items as $itemData) {
                $images = [];

                // Handle image uploads
                if (isset($itemData['images']) && is_array($itemData['images'])) {
                    foreach ($itemData['images'] as $image) {
                        if ($image->isValid()) {
                            $path = $image->store('donation-items', 'public');
                            $images[] = $path;

                            $this->logInfo('image path has been saved', [
                                'path' => $path,
                            ]);
                        }
                    }
                }

                $donationItem = DonationItems::create([
                    'donation_id' => $donation->donation_id,
                    'category_id' => $itemData['category_id'],
                    'item_name' => $itemData['item_name'],
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'condition' => $itemData['condition'],
                    'status' => DonationStatus::Available,
                    'reserved_quantity' => 0,
                    'images' => $images ?: null,
                ]);

                $this->logInfo('Donation item has been saved', [
                    'donation_id' => $donationItem->donation_id,
                    'user_id' => Auth::id(),
                ]);
            }

            // If targeting a specific request, create matches
            if ($request->request_id) {
                $matchingService = new MatchingService;
                $matchingService->matchDonationToRequest($donation, $request->request_id);

                $this->logInfo('matches has been created', []);
            }

            DB::commit();

            $this->logInfo('Data has been saved', []);

            return redirect()->route('admin.donation.show', $donation->donation_id)
                ->with('success', 'Donasi berhasil dibuat.');
        } catch (Exception $e) {
            DB::rollBack();
            $this->logError('Failed to store data', $e, [
                'request_body' => $request->all(),
            ]);

            return redirect()->back()->with('error', 'Gagal menambahkan data. Coba lagi nanti');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $donation = Donation::with([
                'items.category',
                'location',
                'user',
                'targetRequest',
                'itemMatches.requestItem.request',
            ])->findOrFail($id);

            // Check authorization
            if ($donation->user_id != Auth::id() && ! Auth::user()->isAdmin()) {
                abort(403, 'Unauthorized');
            }

            // Get potential matches for this donation
            $potentialMatches = $this->getPotentialMatches($donation);

            return view('dashboard.donation.show', compact('donation', 'potentialMatches'));
        } catch (Exception $e) {
            $this->logError('Failed to render show page', $e, []);

            return redirect()->back()->with('error', 'Gagal memuat halaman. Coba lagi nanti');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $donation = Donation::with('items')->findOrFail($id);

            // Check authorization
            if ($donation->user_id != Auth::id()) {
                abort(403, 'Unauthorized');
            }

            // Only allow editing if available
            if (! $donation->isAvailable()) {
                return redirect()->route('admin.donation.show', $id)
                    ->with('error', 'Hanya donasi yang masih available dapat diedit.');
            }

            $categories = Category::all();
            $userLocations = Location::where('user_id', Auth::id())->get();
            $activeRequests = DonationRequest::active()->get();

            return view('dashboard.donation.form', compact('donation', 'categories', 'userLocations', 'activeRequests'));
        } catch (Exception $e) {
            $this->logError('Failed rendering edit page', $e, []);

            return redirect()->back()->with('error', 'Gagal memuat halaman. Coba lagi nanit');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->logInfo('Donation store attempt', [
            'request_body' => $request->all(),
        ]);

        $donation = Donation::findOrFail($id);

        // Check authorization
        if ($donation->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Only allow updating if available
        if (! $donation->isAvailable()) {
            return redirect()->route('admin.donation.show', $id)
                ->with('error', 'Hanya donasi yang masih available dapat diedit.');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:150',
            'general_description' => 'nullable|string',
            'donation_type' => 'required|in:single_item,multiple_items',
            'request_id' => 'nullable|exists:donation_requests,donation_request_id',
            'location_id' => 'required|exists:locations,location_id',

            // Items validation
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:donation_items,donation_item_id',
            'items.*.category_id' => 'required|exists:categories,category_id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.condition' => 'required|in:new,good_used,needs_repair',
            'items.*.images' => 'nullable|array',
            'items.*.images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            $this->logError('Validation error', null, [
                'request_body' => $request->all(),
                'message' => $validator,
            ]);

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Update donation
            $donation->update([
                'title' => $request->title,
                'general_description' => $request->general_description,
                'donation_type' => $request->donation_type,
                'request_id' => $request->request_id,
                'location_id' => $request->location_id,
            ]);

            $this->logInfo('Donation has beed updated', [
                'donation_id' => $id,
                'user_id' => Auth::id(),
            ]);

            // Get existing item IDs
            $existingItemIds = $donation->items->pluck('donation_item_id')->toArray();
            $updatedItemIds = [];

            // Update or create items
            foreach ($request->items as $itemData) {
                if (isset($itemData['id']) && in_array($itemData['id'], $existingItemIds)) {
                    // Update existing item
                    $item = DonationItems::find($itemData['id']);

                    $images = $item->images ?: [];

                    // Handle new image uploads
                    if (isset($itemData['images']) && is_array($itemData['images'])) {
                        foreach ($itemData['images'] as $image) {
                            if ($image->isValid()) {
                                $path = $image->store('donation-items', 'public');
                                $images[] = $path;
                                $this->logInfo('image path has been saved', [
                                    'path' => $path,
                                ]);
                            }
                        }
                    }

                    $item->update([
                        'category_id' => $itemData['category_id'],
                        'item_name' => $itemData['item_name'],
                        'description' => $itemData['description'],
                        'quantity' => $itemData['quantity'],
                        'condition' => $itemData['condition'],
                        'images' => $images,
                    ]);

                    $this->logInfo('Donation item has beed updated', [
                        'donation_item_id' => $item->donation_item_id,
                        'user_id' => Auth::id()
                    ]);
                    $updatedItemIds[] = $itemData['id'];
                } else {
                    // Create new item
                    $images = [];

                    // Handle image uploads
                    if (isset($itemData['images']) && is_array($itemData['images'])) {
                        foreach ($itemData['images'] as $image) {
                            if ($image->isValid()) {
                                $path = $image->store('donation-items', 'public');
                                $images[] = $path;
                                $this->logInfo('image path has been saved', [
                                    'path' => $path,
                                ]);
                            }
                        }
                    }

                    $newItem = DonationItems::create([
                        'donation_id' => $donation->donation_id,
                        'category_id' => $itemData['category_id'],
                        'item_name' => $itemData['item_name'],
                        'description' => $itemData['description'],
                        'quantity' => $itemData['quantity'],
                        'condition' => $itemData['condition'],
                        'status' => DonationStatus::Available,
                        'reserved_quantity' => 0,
                        'images' => $images ?: null,
                    ]);

                    $this->logInfo('New Item has been saved', [
                        'donation_item_id' => $newItem->donatino_item_id,
                        'user_id' => Auth::id()
                    ]);
                    $updatedItemIds[] = $newItem->donation_item_id;
                }
            }

            // Delete items that were removed
            $itemsToDelete = array_diff($existingItemIds, $updatedItemIds);
            if (! empty($itemsToDelete)) {
                DonationItems::whereIn('donation_item_id', $itemsToDelete)->delete();
                $this->logInfo('Old item has been deleted', [
                    'user_id' => Auth::id()
                ]);
            }

            // Update matches if request changed
            if ($donation->request_id != $request->request_id) {
                $matchingService = new MatchingService;

                // Clear old matches
                $donation->itemMatches()->delete();

                // Create new matches if targeting a request
                if ($request->request_id) {
                    $matchingService->matchDonationToRequest($donation, $request->request_id);
                }
            }

            DB::commit();

            $this->logInfo('Data has been saved', []);

            return redirect()->route('admin.donation.show', $donation->donation_id)
                ->with('success', 'Donasi berhasil diperbarui.');
        } catch (Exception $e) {
            DB::rollBack();
            $this->logError('Failed to update data', $e, [
                'id' => $id,
                'request_body' => $request->all(),
            ]);

            return redirect()->back()->with('error', 'Gagal merubah data. Coba lagi nanti');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $donation = Donation::findOrFail($id);

            $this->logInfo('Donation has been found', [
                'donation_id' => $donation->donation_id
            ]);

            // Check authorization
            if ($donation->user_id != Auth::id()) {
                abort(403, 'Unauthorized');
            }

            // Only allow deletion if available
            if (! $donation->isAvailable()) {
                return redirect()->route('admin.donation.show', $id)
                    ->with('error', 'Hanya donasi yang masih available dapat dihapus.');
            }

            $donation->delete();

            return redirect()->route('admin.donation.index')
                ->with('success', 'Donasi berhasil dihapus.');
        } catch (Exception $e) {
            $this->logError('Failed to delete data', $e, [
                'parameter' => $id,
            ]);

            return redirect()->back()->with('error', 'Gagal menghapus data. Coba lagi nanti');
        }
    }

    /**
     * Get potential matches for a donation
     */
    private function getPotentialMatches(Donation $donation)
    {
        try {
            $matchingService = new MatchingService;

            return $matchingService->findMatchesForDonation($donation);
        } catch (Exception $e) {
            $this->logError('Failed get potential matches', $e, []);

            return redirect()->back()->with('error', 'Tidak dapat mencari data yang cocok. Coba lagi nanti');
        }
    }

    /**
     * Browse available donations (for requesters)
     */
    public function browse()
    {
        try {
            $donations = Donation::with(['items.category', 'location', 'user'])
                ->available()
                ->latest()
                ->paginate(12);

            $categories = Category::all();

            return view('dashboard.donation.browse', compact('donations', 'categories'));
        } catch (Exception $e) {
            $this->logError('Failed to render browse page', $e, []);

            return redirect()->back()->with('error', 'Gagal memuat halaman. Coba lagi nanti');
        }
    }

    /**
     * Filter donations
     */
    public function filter(Request $request)
    {
        try {
            $query = Donation::with(['items.category', 'location', 'user'])
                ->available();

            // Filter by category
            if ($request->has('category_id') && $request->category_id) {
                $query->whereHas('items', function ($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            // Filter by condition
            if ($request->has('condition') && $request->condition) {
                $query->whereHas('items', function ($q) use ($request) {
                    $q->where('condition', $request->condition);
                });
            }

            // Filter by location
            if ($request->has('location') && $request->location) {
                $query->whereHas('location', function ($q) use ($request) {
                    $q->where('address', 'like', '%'.$request->location.'%');
                });
            }

            $donations = $query->latest()->paginate(12);
            $categories = Category::all();

            return view('dashboard.donation.browse', compact('donations', 'categories'));
        } catch (Exception $e) {
            $this->logError('Failed to filter data', $e, [
                'request' => $request->all(),
            ]);

            return redirect()->back()->with('error', 'Gagal memfilter data. Coba lagi nanti');
        }
    }

    /**
     * Offer donation to a specific request
     */
    public function offerToRequest($id, Request $request)
    {
        try {
            $donation = Donation::findOrFail($id);
            $targetRequest = DonationRequest::findOrFail($request->request_id);

            // Check authorization
            if ($donation->user_id != Auth::id()) {
                abort(403, 'Unauthorized');
            }

            // Check if donation is available
            if (! $donation->isAvailable()) {
                return redirect()->back()
                    ->with('error', 'Donasi tidak tersedia untuk ditawarkan.');
            }

            // Create matches
            $matchingService = new MatchingService;
            $matches = $matchingService->matchDonationToRequest($donation, $targetRequest->donation_request_id);

            if ($matches > 0) {
                // Update donation to target this request
                $donation->update(['request_id' => $targetRequest->donation_request_id]);

                return redirect()->route('admin.donation.show', $donation->donation_id)
                    ->with('success', "Donasi berhasil ditawarkan ke permintaan. {$matches} item cocok ditemukan.");
            } else {
                return redirect()->back()
                    ->with('error', 'Tidak ada item yang cocok dengan permintaan ini.');
            }
        } catch (Exception $e) {
            $this->logError('Something wrong happen', $e, [
                'id' => $id,
                'request' => $request->all(),
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan dalam sistem. Coba lagi nanti');
        }
    }

    /**
     * Update donation status
     */
    public function updateStatus($id, Request $request)
    {

        try {
            $donation = Donation::findOrFail($id);

            // Check authorization
            if ($donation->user_id != Auth::id() && ! Auth::user()->isAdmin()) {
                abort(403, 'Unauthorized');
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:available,reserved,picked_up,delivered,cancelled',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator);
            }

            $donation->update(['status' => $request->status]);

            return redirect()->back()
                ->with('success', 'Status donasi berhasil diperbarui.');
        } catch (Exception $e) {
            $this->logError('Failed to update status', $e, [
                'id' => $id,
                'request' => $request->all(),
            ]);

            return redirect()->back()->with('error', 'Gagal merubah status donasi. Coba lagi nanti');
        }
    }
}
