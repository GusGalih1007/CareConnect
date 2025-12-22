<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Category::get();

        return view('', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = null;

        return view('', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100', 'unique:categories,category_name']
        ]);

        if ($validate->fails())
        {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        try
        {
            Category::create([
                'category_name' => $request->name
            ]);

            return redirect()->route('')->with('success', 'Data berhasil Ditambahkan');
        } catch (Exception $e) {
            Log::error('Something wrong while creating data: ' . $e->getMessage());
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
        //
    }
}
