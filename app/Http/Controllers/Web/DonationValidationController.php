<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DonationRequestValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class DonationValidationController extends Controller
{
    private function logInfo(string $message, array $context = [])
    {
        Log::info('[Web] [DonationValidationController] ' . $message, $context);
    }

    private function logError(string $message, ?\Throwable $e = null, array $context = [])
    {
        Log::error(
            '[Web] [DonationValidationController]'. $message,
            array_merge([
                'exception' => $e?->getMessage(),
                'trace' => $e?->getTraceAsString(),
            ], $context));
    }

    public function index()
    {
        try
        {
            $data = DonationRequestValidation::with('items.category', 'admin', 'request')->get();

            return view('dashboard.donation-validation.index', compact('data'));
        } catch (Exception $e) {
            $this->logError('Failed to render index page', $e, []);
            return redirect()->back()->with('error', 'Gagal memuat halaman. Coba lagi nanti');
        }
    }

    public function donationApprove(Request $request, $params)
    {
        
    }

    public function donationReject(Request $request, $params)
    {
        
    }

    public function donationRevision(Request $request, $params)
    {
        
    }
    
    public function donationItemApprove(Request $request, $params)
    {
        
    }

    public function donationItemReject(Request $request, $params)
    {
        
    }

    public function donationItemRevision(Request $request, $params)
    {
        
    }
}
