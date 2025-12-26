<?php

namespace App\Http\Controllers\Web;

use App\Enum\DonationRequestStatus;
use App\Enum\DonationRequestValidationStatus;
use App\Http\Controllers\Controller;
use App\Models\DonationRequest;
use App\Models\DonationRequestValidation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DonationValidationController extends Controller
{
    private function logInfo(string $message, array $context = [])
    {
        Log::info('[Web] [DonationValidationController] '.$message, $context);
    }

    private function logError(string $message, ?\Throwable $e = null, array $context = [])
    {
        Log::error(
            '[Web] [DonationValidationController]'.$message,
            array_merge([
                'exception' => $e?->getMessage(),
                'trace' => $e?->getTraceAsString(),
            ], $context));
    }

    public function index()
    {
        try {
            $data = DonationRequest::with([
                'items.category',
                'user',
                'validation',
            ])->get();

            return view('dashboard.donation-validation.index', compact('data'));
        } catch (Exception $e) {
            $this->logError('Failed to render index page', $e, []);

            return redirect()->back()->with('error', 'Gagal memuat halaman. Coba lagi nanti');
        }
    }

    public function show($id)
    {
        try {
            $data = DonationRequest::with([
                'items.category',
                'location',
                'user',
                'validation',
            ])->findOrFail($id);

            return view('dashboard.donation-validation.show', compact('data'));
        } catch (Exception $e) {
            $this->logError('Failed to render show page', $e, [
                'params' => 'id',
                'value' => $id,
            ]);

            return redirect()->back()->with('error', 'Gagal memuat halaman. Coba lagi nanti');
        }
    }

    public function donationValidate(Request $request, $id)
    {
        $this->logInfo('Approving donation attempt', [
            'params' => 'donation_request_id',
            'value' => $id,
            'request_body' => $request->all(),
        ]);

        $data = DonationRequest::findOrFail($id);

        if (! $data) {
            $this->logError('Data not found', null, [
                'params' => 'donation_request_id',
                'value' => $id,
            ]);

            return redirect()->route('admin.donation-validation.index')
                ->with('error', 'Data donasi tidak ditemukan');
        }

        if (! $data->isPending()) {
            return redirect()->route('admin.donation-validation.show', $id)
                ->with('error', 'Hanya permintaan yang masih pending dapat dikonfirmasi.');
        }

        $validate = Validator::make($request->all(), [
            'status' => ['string', 'required', 'in:approved,rejected,need_revision'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validate->fails()) {
            $this->logError('Validation Error', null, [
                'user_id' => Auth::id(),
                'request_body' => $request->all(),
            ]);

            return redirect()->back()
                ->with('error', 'Perika kembali data yang anda kirimkan dan coba lagi')
                ->withInput()
                ->withErrors($validate);
        }

        DB::beginTransaction();

        try {
            $validation = DonationRequestValidation::where('donation_request_id', $id)
                ->first();

            if (! $validation) {
                throw new Exception('Request validation data not found', 404);
            }

            switch ($request->status) {
                case 'approved':
                    $validationStatus = DonationRequestValidationStatus::Approved->value;
                    $requestStatus = DonationRequestStatus::Active->value;
                    $message = 'berhasil disetujui';
                    break;
                case 'rejected':
                    $validationStatus = DonationRequestValidationStatus::Rejected->value;
                    $requestStatus = DonationRequestStatus::Rejected->value;
                    $message = 'berhasil ditolak';
                    break;
                case 'need_revision':
                    $validationStatus = DonationRequestValidationStatus::NeedRevision->value;
                    $requestStatus = DonationRequestStatus::Rejected->value;
                    $message = 'berhasil dikoreksi';
                    break;
                default:
                    throw new Exception('Status tidak valid', $request->status);
            }

            $validation->update([
                'note' => $request->note,
                'status' => $validationStatus,
            ]);

            $data->update([
                'status' => $requestStatus,
            ]);

            $this->logInfo('Request validation data updated successfully', [
                'request_validation_id' => $validation->request_validation_id,
                'status' => $validationStatus,
                'request_status' => $requestStatus,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Permintaan '.$message);
        } catch (Exception $e) {
            $this->logError('Failed to validate donation request', $e, [
                'donation_request_id' => $id,
                'user_id' => Auth::id(),
                'request_body' => $request->all(),
            ]);
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal merubah status permintaan. Coba lagi nanti');
        }
    }
}
