<?php

namespace App\Http\Controllers\Web;

use App\Enum\DonationRequestPriority;
use App\Enum\DonationRequestStatus;
use App\Enum\DonationRequestValidationStatus;
use App\Enum\DonationType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DonationRequest;
use App\Models\DonationRequestItems;
use App\Models\DonationRequestValidation;
use App\Models\Location;
use App\Services\MatchingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DonationRequestController extends Controller
{
    private function logInfo(string $message, array $context = [])
    {
        Log::info('[Web] [DonationRequestController] '.$message, $context);
    }

    private function logError(string $message, ?\Throwable $e = null, array $context = [])
    {
        Log::error(
            '[Web] [DonationRequestController] '.$message,
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
            // $data = DonationRequest::with('items.category', 'location')
            //     ->where('user_id', $user->user_id)
            //     ->latest()
            //     ->get();
            $allData = DonationRequest::with('items.category', 'location')
                ->where('user_id', $user->user_id)
                ->latest()
                ->paginate(10);

            return view('dashboard.donation-request.index', compact('allData'));
        } catch (Exception $e) {
            $this->logError('Failed rendering index page', $e, []);

            return redirect()->back()->with('error', 'Gagal memuat halaman. Coba lagi nanti');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $request = null;
            $categories = Category::all();
            $userLocations = Location::where('user_id', Auth::id())->get();
            $priority = DonationRequestPriority::cases();

            return view('dashboard.donation-request.form', compact('categories', 'userLocations', 'request', 'priority'));
        } catch (Exception $e) {
            $this->logError('Failed rendering create page', $e, []);

            return redirect()->back()->with('error', 'Gagal memuat halaman. Coba lagi nanti');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->logInfo('Donation request store attempt', [
            'request_body' => $request->all(),
        ]);

        $validate = Validator::make($request->all(), [
            'title' => 'required|string|max:150',
            'general_description' => 'nullable|string',
            'location_id' => 'required|exists:locations,location_id',
            'priority' => 'required|in:low,normal,urgent',

            // Items validation
            'items' => 'required|array|min:1',
            'items.*.category_id' => 'required|exists:categories,category_id',
            'items.*.item_name' => 'nullable|string|max:100',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.preferred_condition' => 'required|in:new,good_used,needs_repair',
            'items.*.priority' => 'required|in:low,normal,urgent',
        ]);

        if ($validate->fails()) {
            $this->logError('Validation Error', null, [
                'error' => $validate->errors(),
            ]);

            return redirect()->back()
                ->withErrors($validate)
                ->with('error', 'Lengkapi data penting sebelum simpan')
                ->withInput();
        }

        DB::beginTransaction();

        try {
            if ($request->items > 0)
            {
                $requestType = DonationType::Multi->value;
            } else {
                $requestType = DonationType::Single->value;
            }

            $donationRequest = DonationRequest::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'general_description' => $request->general_description,
                'donation_type' => $requestType,
                'location_id' => $request->location_id,
                'priority' => $request->priority,
                'status' => DonationRequestStatus::Pending,
            ]);

            $this->logInfo('Donation request created', [
                'donation_request_id' => $donationRequest->donation_request_id,
                'user_id' => Auth::id(),
            ]);

            // Create donation request items
            foreach ($request->items as $item) {
                $requestItem = DonationRequestItems::create([
                    'donation_request_id' => $donationRequest->donation_request_id,
                    'category_id' => $item['category_id'],
                    'item_name' => $item['item_name'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'preferred_condition' => $item['preferred_condition'],
                    'priority' => $item['priority'],
                    'status' => DonationRequestStatus::Pending,
                    'fulfilled_quantity' => 0,
                ]);

                $this->logInfo('Created donation request item', [
                    'donation_request_item_id' => $requestItem->donation_request_item_id,
                    'donation_request_id' => $donationRequest->donation_request_id,
                    'category_id' => $item['category_id'],
                ]);
            }

            DB::commit();

            $this->logInfo('Donation request successfully stored and validated', [
                'donation_request_id' => $donationRequest->donation_request_id,
            ]);

            return redirect()->route('admin.donation-request.show', $donationRequest->donation_request_id)
                ->with('success', 'Permintaan donasi berhasil dibuat. Menunggu validasi admin.');
        } catch (Exception $e) {
            $this->logError('Failed to store data', $e, [
                'request_body' => $request->all(),
            ]);
            DB::rollBack();

            return redirect()->back()->with('error', 'Data gagal ditambahkan. Terjadi kesalahan dalam sistem')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $request = DonationRequest::with([
                'items.category',
                'location',
                'user',
                'validation',
            ])->findOrFail($id);

            // Check authorization
            if ($request->user_id != Auth::id() && ! Auth::user()->isAdmin()) {
                abort(403, 'Unauthorized');
            }

            // Get potential matches
            $potentialMatches = $this->getPotentialMatches($request);

            return view('dashboard.donation-request.show', compact('request', 'potentialMatches'));
        } catch (Exception $e) {
            $this->logError('Failed rendering show page', $e, []);

            return redirect()->back()->with('error', 'Gagal memuat halaman. Coba lagi nanti');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $request = DonationRequest::with('items')->findOrFail($id);

            // Check authorization
            if ($request->user_id != Auth::id()) {
                abort(403, 'Unauthorized');
            }

            // Only allow editing if pending
            if (! $request->isPending()) {
                return redirect()->route('admin.donation-request.show', $id)
                    ->with('error', 'Hanya permintaan yang masih pending dapat diedit.');
            }

            $categories = Category::all();
            $userLocations = Location::where('user_id', Auth::id())->get();

            return view('dashboard.donation-request.form', compact('request', 'categories', 'userLocations'));
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
        $donationRequest = DonationRequest::findOrFail($id);

        // Check authorization
        if ($donationRequest->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Only allow updating if pending
        if (! $donationRequest->isPending()) {
            return redirect()->route('admin.donation-request.show', $id)
                ->with('error', 'Hanya permintaan yang masih pending dapat diedit.');
        }

        $validate = Validator::make($request->all(), [
            'title' => 'required|string|max:150',
            'general_description' => 'nullable|string',
            'location_id' => 'required|exists:locations,location_id',
            'priority' => 'required|in:low,normal,urgent',

            // Items validation
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:donation_request_items,donation_request_item_id',
            'items.*.category_id' => 'required|exists:categories,category_id',
            'items.*.item_name' => 'nullable|string|max:100',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.preferred_condition' => 'required|in:new,good_used,needs_repair',
            'items.*.priority' => 'required|in:low,normal,urgent',
        ]);

        if ($validate->fails()) {
            $this->logError('Validation Error', null, [
                'error' => $validate->errors(),
            ]);

            return redirect()->back()
                ->withErrors($validate)
                ->withInput()
                ->with('error', 'Lengkapi data penting sebelum simpan');
        }

        DB::beginTransaction();

        try {
            // Update donation request
            $donationRequest->update([
                'title' => $request->title,
                'general_description' => $request->general_description,
                'donation_type' => $request->donation_type,
                'location_id' => $request->location_id,
                'priority' => $request->priority,
            ]);

            $this->logInfo('Donation request updated', [
                'donation_request_id' => $donationRequest->donation_request_id,
            ]);

            // Get existing item IDs
            $existingItemIds = $donationRequest->items->pluck('donation_request_item_id')->toArray();
            $updatedItemIds = [];

            // Update or create items
            foreach ($request->items as $itemData) {
                if (isset($itemData['id']) && in_array($itemData['id'], $existingItemIds)) {
                    // Update existing item
                    $item = DonationRequestItems::find($itemData['id']);
                    $item->update([
                        'category_id' => $itemData['category_id'],
                        'item_name' => $itemData['item_name'],
                        'description' => $itemData['description'],
                        'quantity' => $itemData['quantity'],
                        'preferred_condition' => $itemData['preferred_condition'],
                        'priority' => $itemData['priority'],
                        'updated_at' => now(),
                    ]);

                    $updatedItemIds[] = $itemData['id'];
                } else {
                    // Create new item
                    $newItem = DonationRequestItems::create([
                        'donation_request_id' => $donationRequest->donation_request_id,
                        'category_id' => $itemData['category_id'],
                        'item_name' => $itemData['item_name'],
                        'description' => $itemData['description'],
                        'quantity' => $itemData['quantity'],
                        'preferred_condition' => $itemData['preferred_condition'],
                        'priority' => $itemData['priority'],
                        'status' => DonationRequestStatus::Pending,
                        'fulfilled_quantity' => 0,
                    ]);

                    $updatedItemIds[] = $newItem->donation_request_item_id;
                }
            }

            // Delete items that were removed
            $itemsToDelete = array_diff($existingItemIds, $updatedItemIds);
            if (! empty($itemsToDelete)) {
                // Delete donation request items
                DonationRequestItems::whereIn('donation_request_item_id', $itemsToDelete)->delete();
            }

            // Check if all items are deleted, delete the request if empty
            if (empty($updatedItemIds)) {
                $donationRequest->delete();
                DB::commit();

                return redirect()->route('admin.donation-request.index')
                    ->with('warning', 'Permintaan donasi dihapus karena tidak memiliki item.');
            }

            DB::commit();

            $this->logInfo('Donation request successfully updated', [
                'donation_request_id' => $donationRequest->donation_request_id,
            ]);

            return redirect()->route('admin.donation-request.show', $donationRequest->donation_request_id)
                ->with('success', 'Permintaan donasi berhasil diperbarui.');

        } catch (Exception $e) {
            $this->logError('Failed updating data', $e, [
                'request_body' => $request->all(),
            ]);
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Gagal merubah data. Coba lagi nanti')
                ->withInput()->withInput();
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $request = DonationRequest::findOrFail($id);

            // Check authorization
            if ($request->user_id != Auth::id()) {
                abort(403, 'Unauthorized');
            }

            // Only allow deletion if pending
            if (! $request->isPending()) {
                return redirect()->route('admin.donation-request.show', $id)
                    ->with('error', 'Hanya permintaan yang masih pending dapat dihapus.');
            }

            $this->logInfo('Attempt to delete donation request', [
                'donation_request_id' => $id,
            ]);

            // Get validation record
            $requestValidation = $request->validation;

            // Delete item validations first
            if ($requestValidation) {
                $requestValidation->delete();
            }

            // Delete donation request items
            DonationRequestItems::where('donation_request_id', $id)->delete();

            $request->delete();

            $this->logInfo('Donation request successfully deleted', [
                'donation_request_id' => $id,
            ]);

            return redirect()->route('admin.donation-request.index')
                ->with('success', 'Permintaan donasi berhasil dihapus.');
        } catch (Exception $e) {
            $this->logError('Failed deleting data', $e, [
                'id' => $id,
            ]);

            return redirect()->back()->with('error', 'Gagal menghapus data. Coba lagi nanti');
        }
    }

    /**
     * Get potential matches for a donation request
     */
    private function getPotentialMatches(DonationRequest $request)
    {
        try {
            $matchingService = new MatchingService;

            return $matchingService->findMatchesForRequest($request);
        } catch (Exception $e) {
            $this->logError('Failed get potential matches', $e, []);

            return redirect()->back()->with('error', 'Tidak dapat mencari data yang cocok. Coba lagi nanti');
        }
    }

    /**
     * Show all active requests (for donors)
     */
    public function browse()
    {
        try {
            $requests = DonationRequest::with(['items.category', 'location', 'user'])
                ->latest()
                ->paginate(12);

            // dd($requests);

            $categories = Category::all();

            return view('dashboard.donation-request.browse', compact('requests', 'categories'));
        } catch (Exception $e) {
            $this->logError('Failed to redered browse page', $e, []);

            return redirect()->back()->with('error', 'Gagal memuat halaman. Coba lagi nanti');
        }
    }

    /**
     * Filter requests
     */
    public function filter(Request $request)
    {
        try {
            $query = DonationRequest::with(['items.category', 'location', 'user']);

            // Filter by category
            if ($request->has('category_id') && $request->category_id) {
                $query->whereHas('items', function ($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            // Filter by priority
            if ($request->has('priority') && $request->priority) {
                $query->where('priority', $request->priority);
            }

            // Filter by location (simple text search)
            if ($request->has('condition') && $request->condition) {
                $query->whereHas('items', function ($q) use ($request) {
                    $q->where('preferred_condition', 'like', '%'.$request->condition.'%');
                });
            }

            $requests = $query->latest()->paginate(12);
            $categories = Category::all();

            return view('dashboard.donation-request.browse', compact('requests', 'categories'));
        } catch (Exception $e) {
            $this->logError('Failed to filter data', $e, [
                'request_body' => $request->all(),
            ]);

            return redirect()->back()->with('error', 'Gagal memfilter data. Coba lagi nanti');
        }
    }
}
