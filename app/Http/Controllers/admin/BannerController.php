<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('id', 'desc')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        // Validasi: Boleh upload foto (jpg, png) atau video (mp4) max 20MB
        $request->validate([
            'title' => 'required',
            'image' => 'required|mimes:jpg,jpeg,png,webp,mp4|max:20480',
        ]);

        // Simpan file ke folder storage/app/public/banners
        $path = $request->file('image')->store('banners', 'public');

        Banner::create([
            'title' => $request->title,
            'image_path' => $path,
            'link' => $request->link,
            'status' => $request->status ?? 1
        ]);

        return redirect()->route('banners.index')->with('success', 'Banner promosi berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        
        // Hapus file aslinya dari folder
        if(Storage::disk('public')->exists($banner->image_path)){
            Storage::disk('public')->delete($banner->image_path);
        }
        
        $banner->delete();
        return redirect()->route('banners.index')->with('success', 'Banner berhasil dihapus!');
    }
}