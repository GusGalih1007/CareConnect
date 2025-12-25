<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\GeocodingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    private function logInfo(string $message, array $context = [])
    {
        Log::info('[Web] [DonationRequestController] ' . $message, $context);
    }

    private function logError(string $message, \Throwable $e = null, array $context = [])
    {
        Log::error(
            '[Web] [DonationRequestController] ' . $message,
            array_merge(
                [
                    'exception' => $e?->getMessage(),
                    'trace' => $e?->getTraceAsString(),
                ],
                $context,
            ),
        );
    }

    protected $geoCodingService;
    public function __construct(GeocodingService $geoCodingService)
    {
        $this->geoCodingService = $geoCodingService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user)
        {
            return redirect()->route('login.form')->with('error', 'Anda belum login. Silahkan login terlebih dahulu');
        }

        $validate = Validator::make($request->all(), [
            'address' => 'required|string|max:255'
        ]);

        if ($validate->fails())
        {
            $this->logError('Validation Error', null, [
                'error' => $validate->errors()
            ]);
            return redirect()->back()->withErrors($validate);
        }

        try {
            $coordinate = $this->geoCodingService->getCoordinatesFromAddress($request->address);

            // dd($coordinate);

            Location::create([
                'user_id' => $user->user_id,
                'address' => $request->address,
                'latitude' => $coordinate['latitude'] ?? null,
                'longitude' => $coordinate['longitude'] ?? null,
            ]);

            return redirect()->back()->with('success', 'Alamat baru telah ditambahkan');
        } catch (Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();

        if (!$user)
        {
            return redirect()->route('login.form')->with('error', 'Anda belum login. Silahkan login terlebih dahulu');
        }

        try 
        {
            $data = Location::findOrFail($id);
    
            if (!$data)
            {
                return redirect()->back()->with('error', 'Data tidak dapat ditemukan');
            }
    
            $data->delete();
    
            return redirect()->back()->with('success', 'Data berhasil dihapus');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
