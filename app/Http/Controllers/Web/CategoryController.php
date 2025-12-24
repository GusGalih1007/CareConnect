<?php

namespace App\Http\Controllers\Web;

use App\Enum\DonationRequestPriority;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    private function logInfo(string $message, array $context = [])
    {
        Log::info('[Web] [CategoryController] ' . $message, $context);
    }

    private function logError(string $message, \Throwable $e = null, array $context = [])
    {
        Log::error(
            '[Web] [CategoryController] ' . $message,
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
        try
        {
            $data = Category::get();
    
            return view('dashboard.category.index', compact('data'));
        } catch (Exception $e) {
            $this->logError('Failed rendering index page', $e, [
                'success' => false
            ]);
            return redirect()->back()->with('error', 'Gagal memuat halaman. Coba lagi nanti');
        }
    }

    public function trashed()
    {
        try
        {
            $data = Category::onlyTrashed()->get();

            return view('dashboard.category.trash', compact('data'));
        } catch (Exception $e) {
            $this->logError('Failed rendering trash page', $e, [
                'success' => false
            ]);

            return redirect()->back()->with('error', 'Gagal memuat halaman. Coba lagi nanti');
        }
    }

    public function restoreById($id)
    {
        try
        {
            $data = Category::onlyTrashed()->findOrFail($id);

            if (!$data)
            {
                return redirect()->back()->with('error', 'Data tidak ditemukan');
            }

            $data->restore();

            return redirect()->back()->with('success', 'Data berhasil dipulihkan');
        } catch (Exception $e) {
            $this->logError('Failed to restore data', $e, [
                'id' => $id
            ]);
            return redirect()->back()->with('error', 'Gagal memulihkan data. Kesalahan dalam sistem');
        }
    }

    public function restoreAll()
    {
        try
        {
            $data = Category::onlyTrashed();

            $data->restore();

            return redirect()->route('admin.category.index')->with('success', 'Semua data telah dipulihkan');
        } catch (Exception $e) {
            $this->logError('Failed to restore all data', $e, []);
            return redirect()->back()->with('error', 'Gagal memulihkan semua data. Kesalahan dalam sistem');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try
        {
            $data = null;
    
            return view('dashboard.category.form', compact('data'));
        } catch (Exception $e) {
            $this->logError('Failed rendering create page', $e, [
                'success' => false
            ]);
            return redirect()->back()->with('error', 'Gagal memuat halaman. Coba lagi nanti');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->logInfo('Category store attempt', [
            'payload' => $request->all()
        ]);

        $validate = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100', 'unique:categories,category_name']
        ]);

        if ($validate->fails())
        {
            $this->logError('Validation Error', null, [
                'error' => $validate->errors()
            ]);
            return redirect()->back()->withErrors($validate)->withInput()->with('error', 'Lengkapi data penting sebelum simpan');
        }

        try
        {
            $created = Category::create([
                'category_name' => $request->name
            ]);

            $this->logInfo('Store data successfull', [
                'result' => $created
            ]);

            return redirect()->route('admin.category.index')->with('success', 'Data berhasil Ditambahkan');
        } catch (Exception $e) {
            $this->logError('Error attempting store data', $e, [
                'payload' => $request->all()
            ]);
            return redirect()->back()->with('error', 'Data gagal ditambahkan. Terjadi kesalahan dalam sistem');
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
        try
        {
            $data = Category::findOrFail($id);

            if (!$data)
            {
                return redirect()->back()->with('error', 'Data tidak ditemukan');
            }
            return view('dashboard.category.form', compact('data'));
        } catch (Exception $e) {
            $this->logError('Failed rendering edit page', $e, [
                'success' => false
            ]);

            return redirect()->back()->with('error', 'Gagal masuk halaman edit. Coba lagi nanti');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validate = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100', 'unique:categories,category_id,' . $id . ',category_id']
        ]);

        if ($validate->fails())
        {
            $this->logError('Validation Error', null, [
                'error' => $validate->errors()
            ]);
            return redirect()->back()->withErrors($validate)->with('error', 'Lengkapi data penting sebelum simpan');
        }

        $this->logInfo('Update data attempt', [
            'id' => $id,
            'payload' => $request->all()
        ]);

        try
        {
            $data = Category::findOrFail($id);

            if (!$data)
            {
                return redirect()->back()->with('error', 'Data tidak ditemukan.');
            }

            $data->update([
                'category_name' => $request->name
            ]);

            $this->logInfo('Data updated successfully', [
                'result' => $data
            ]);

            return redirect()->route('admin.category.index')->with('success', 'Data berhasil di edit');
        } catch (Exception $e) {
            $this->logError('Failed updating data', $e, [
                'payload' => $request->all()
            ]);
            return redirect()->back()->with('error', 'Gagal merubah data. Coba lagi nanti');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try
        {
            $this->logInfo('Delete data attempt', [
                'category_id' => $id 
            ]);
    
            $data = Category::findOrFail($id);
    
            $data->delete();
    
            $this->logInfo('Data deleted successfully', [
                'success' => true
            ]);
    
            return redirect()->route('admin.category.index')->with('success', 'Data berhasil dihapus');
        } catch (Exception $e) {
            $this->logError('Failed deleting data', $e, [
                'id' => $id
            ]);
            return redirect()->back()->with('error', 'Gagal menghapus data. Coba lagi nanti');
        }
    }
}
