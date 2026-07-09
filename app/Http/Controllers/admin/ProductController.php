<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
    {
        $query = Product::with(['images', 'variations'])->latest();

        // 1. Fitur Pencarian
        if ($request->keyword) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->keyword . '%')
                  ->orWhere('sku', 'like', '%' . $request->keyword . '%');
            });
        }

        // --- INI TAMBAHAN BUAT FILTER KATEGORI BRE ---
        if ($request->category) {
            // Pakai 'like' biar kalau pilih Kategori Utama, sub-kategorinya ikut ke-filter
            $query->where('category_id', 'like', $request->category . '%');
        }
        // ---------------------------------------------

        // 2. Fitur Filter Tab Pintar (Ngecek sampai ke variasi)
        if ($request->tab == 'tersedia') {
            $query->where(function($q) {
                // Induk stok > 0 ATAU punya anak variasi yg stoknya > 0
                $q->where(function($q1) {
                    $q1->whereDoesntHave('variations')->where('qty', '>', 0);
                })->orWhereHas('variations', function($q2) {
                    $q2->where('stock', '>', 0);
                });
            });
        } elseif ($request->tab == 'habis') {
            $query->where(function($q) {
                // Induk stok == 0 ATAU punya anak variasi yg stoknya == 0
                $q->where(function($q1) {
                    $q1->whereDoesntHave('variations')->where('qty', '<=', 0);
                })->orWhereHas('variations', function($q2) {
                    $q2->where('stock', '<=', 0);
                });
            });
        }

        $products = $query->paginate(30);
        return view('admin.products.list', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil settingan toko, kalau kosong (belum disave) kita bikin default-nya
        $setting = Setting::first();
        if (!$setting) {
            $setting = new Setting();
            $setting->global_reguler = 1;
            $setting->global_kargo = 1;
            $setting->global_instant = 0;
        }

        return view('admin.products.create', compact('setting'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi Pinter & Cek Nama Unik
        $rules = [
            'title' => 'required|unique:products,title',
            'description' => 'required',
            'weight' => 'required|numeric',
        ];

        // Pesan error khusus biar lebih humanis
        $messages = [
            'title.unique' => 'Nama produk sama dengan etalase yang lain. Silakan gunakan nama yang lebih spesifik (Contoh: Bibit Buah Naga Merah).',
            'title.required' => 'Nama produk wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'weight.required' => 'Berat pengiriman wajib diisi.',
        ];

        // Harga utama HANYA wajib kalau variasi NGGAK aktif
        if (!$request->has('variations')) {
            $rules['price'] = 'required';
            $messages['price.required'] = 'Harga wajib diisi jika tidak menggunakan variasi.';
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 2. Simpan Data Dasar
        $product = new \App\Models\Product();
        $product->title = $request->title;
        $product->sku = $request->sku;
        $product->category_id = $request->category_id; 
        $product->description = $request->description;
        
        $product->brand = $request->brand;
        $product->origin = $request->origin;
        $product->size = $request->size;
        $product->light_requirement = $request->light_requirement;
        $product->color = $request->color;
        $product->care_level = $request->care_level;
        $product->bentuk_pupuk = $request->bentuk_pupuk;
        $product->volume_isi = $request->volume_isi;
        $product->bahan_material = $request->bahan_material;
        $product->dimensi = $request->dimensi;
        
        // Penjualan & Pengiriman
        $product->price = $request->price ? str_replace(['.', ','], '', $request->price) : 0;
        $product->qty = max(0, (int)($request->qty ?? 0));
        $product->min_qty = $request->min_qty ?? 1;
        $product->max_purchase_type = $request->max_purchase_type;
        $product->max_purchase_limit = $request->max_purchase_limit;
        
        $product->weight = $request->weight;
        $product->is_reguler = $request->has('is_reguler') ? 1 : 0;
        $product->is_instant = $request->has('is_instant') ? 1 : 0;
        // $product->is_kargo = $request->has('is_kargo') ? 1 : 0; // Dimatiin biar DB ga error
        
        // --- LOGIKA VIDEO DIKEMBALIKAN SESUAI REQUEST ---
        if ($request->input('remove_existing_video') == '1') {
            if ($product->video_path && \Storage::disk('public')->exists($product->video_path)) {
                \Storage::disk('public')->delete($product->video_path);
            }
            $product->video_path = null; // Kosongin di database
        }

        if ($request->hasFile('video')) {
            // Simpan file ke folder storage/app/public/products/videos
            $videoPath = $request->file('video')->store('products/videos', 'public');
            $product->video_path = $videoPath; 
        }
        // ------------------------------------------------
        
        $product->save();

        // 3. Simpan Gambar Utama (Drag & Drop)
        if ($request->image_order) {
            $orderArray = explode(',', $request->image_order);
            foreach ($orderArray as $index => $item) {
                if (str_contains($item, 'new_')) {
                    $newIdx = str_replace('new_', '', $item);
                    $base64Data = $request->images_base64[$newIdx] ?? null;

                    if ($base64Data) {
                        $image_parts = explode(";base64,", $base64Data);
                        $image_type = explode("image/", $image_parts[0])[1];
                        $image_base64 = base64_decode($image_parts[1]);
                        $fileName = time() . '_' . uniqid() . '.' . $image_type;
                        
                        \Storage::disk('public')->put('products/' . $fileName, $image_base64);

                        \App\Models\ProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => 'products/' . $fileName,
                            'is_promo'   => ($index == 0) ? 1 : 0
                        ]);
                    }
                }
            }
        }

        // 4. Simpan Variasi (Shopee Style)
        if ($request->has('variations')) {
            foreach ($request->variations as $key => $varData) {
                $variation = new \App\Models\ProductVariation();
                $variation->product_id = $product->id;
                $variation->variation_name = $request->variation_name; 
                $variation->variation_option = $varData['option'];     
                $variation->price = str_replace(['.', ','], '', $varData['price'] ?? 0);
                $variation->stock = max(0, (int)($varData['stock'] ?? 0));
                $variation->sku = $varData['sku'] ?? null;

                if ($request->has("var_images.{$key}")) {
                    $base64Image = $request->input("var_images.{$key}");
                    if (str_contains($base64Image, ';base64,')) {
                        $image_parts = explode(";base64,", $base64Image);
                        $image_type = explode("image/", $image_parts[0])[1];
                        $fileName = time() . '_var_' . uniqid() . '.' . $image_type;
                        \Storage::disk('public')->put('products/variations/' . $fileName, base64_decode($image_parts[1]));
                        $variation->image_path = 'products/variations/' . $fileName;
                    }
                }
                $variation->save();
            }
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($productId, Request $request)
    {
        // 1. Cari produk (Kodingan asli lo)
        $product = Product::find($productId);
        
        if (empty($product)) {
            return redirect()->route('products.index')->with('error', 'Product not found');
        }

        // 2. Tarik foto produk (Kodingan asli lo)
        $productImages = ProductImage::where('product_id', $product->id)->get();

        // 3. Panggil settingan jasa kirim global (TAMBAHAN BARU)
        $setting = Setting::first();
        if (!$setting) {
            $setting = new Setting();
            $setting->global_reguler = 1;
            $setting->global_kargo = 1;
            $setting->global_instant = 0;
        }

        // 4. Passing semua datanya ke view (Tambahin 'setting' di compact)
        return view('admin.products.edit', compact('product', 'productImages', 'setting'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($productId, Request $request)
    {
        $product = Product::find($productId);
        if (empty($product)) return redirect()->back()->with('error', 'Product not found');

        // 1. Validasi Pinter & Cek Nama Unik (Kecuali ID produk ini sendiri)
        $rules = [
            'title' => 'required|unique:products,title,' . $productId,
            'description' => 'required',
            'weight' => 'required|numeric',
        ];

        // Pesan error khusus biar lebih humanis
        $messages = [
            'title.unique' => 'Nama produk sama dengan etalase yang lain. Silakan gunakan nama yang lebih spesifik (Contoh: Bibit Buah Naga Merah).',
            'title.required' => 'Nama produk wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'weight.required' => 'Berat pengiriman wajib diisi.',
        ];

        // Harga utama HANYA wajib kalau variasi NGGAK aktif
        if (!$request->has('variations')) {
            $rules['price'] = 'required';
            $messages['price.required'] = 'Harga wajib diisi jika tidak menggunakan variasi.';
        }

        $validator = Validator::make($request->all(), $rules, $messages);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 2. Update Data Dasar
        $product->title = $request->title;
        $product->sku = $request->sku;
        $product->category_id = $request->category_id; 
        $product->description = $request->description;
        
        $product->brand = $request->brand;
        $product->origin = $request->origin;
        $product->size = $request->size;
        $product->light_requirement = $request->light_requirement;
        $product->color = $request->color;
        $product->care_level = $request->care_level;
        $product->bentuk_pupuk = $request->bentuk_pupuk;
        $product->volume_isi = $request->volume_isi;
        $product->bahan_material = $request->bahan_material;
        $product->dimensi = $request->dimensi;

        // Penjualan & Pengiriman
        $product->price = $request->price ? str_replace(['.', ','], '', $request->price) : 0;
        $product->qty = max(0, (int)($request->qty ?? 0));
        $product->min_qty = $request->min_qty ?? 1;
        $product->max_purchase_type = $request->max_purchase_type;
        $product->max_purchase_limit = $request->max_purchase_limit;
        
        $product->weight = $request->weight;
        $product->is_reguler = $request->has('is_reguler') ? 1 : 0;
        $product->is_instant = $request->has('is_instant') ? 1 : 0;
        // $product->is_kargo = $request->has('is_kargo') ? 1 : 0; // Dimatiin juga

        // Logika Video
        if ($request->input('remove_existing_video') == '1') {
            if ($product->video_path && Storage::disk('public')->exists($product->video_path)) {
                Storage::disk('public')->delete($product->video_path);
            }
            $product->video_path = null; // Kosongin di database
        }
        
        if ($request->hasFile('video')) {
            // Cek kalau produk ini sebelumnya udah punya video, kita hapus dulu file lamanya
            if ($product->video_path && Storage::disk('public')->exists($product->video_path)) {
                Storage::disk('public')->delete($product->video_path);
            }
            // Simpan file video yang baru
            $videoPath = $request->file('video')->store('products/videos', 'public');
            $product->video_path = $videoPath;
        }
        $product->save();

        // 3. LOGIKA FOTO (Hapus lama & Urutan Baru)
        if ($request->deleted_images) {
            $deletedIds = explode(',', rtrim($request->deleted_images, ','));
            foreach($deletedIds as $id) {
                $img = ProductImage::find($id);
                if($img) {
                    \Storage::disk('public')->delete($img->image_path);
                    $img->delete();
                }
            }
        }

        if ($request->image_order) {
            $orderArray = explode(',', $request->image_order);
            foreach ($orderArray as $index => $item) {
                if (str_contains($item, 'old_')) {
                    $imgId = str_replace('old_', '', $item);
                    ProductImage::where('id', $imgId)->update(['is_promo' => ($index == 0) ? 1 : 0]);
                } 
                elseif (str_contains($item, 'new_')) {
                    $newIdx = str_replace('new_', '', $item);
                    $base64Data = $request->images_base64[$newIdx] ?? null;

                    if ($base64Data) {
                        $image_parts = explode(";base64,", $base64Data);
                        $image_type = explode("image/", $image_parts[0])[1];
                        $image_base64 = base64_decode($image_parts[1]);
                        $fileName = time() . '_' . uniqid() . '.' . $image_type;
                        \Storage::disk('public')->put('products/' . $fileName, $image_base64);

                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => 'products/' . $fileName,
                            'is_promo'   => ($index == 0) ? 1 : 0
                        ]);
                    }
                }
            }
        }

        // 4. Update Variasi
        if ($request->has('variations')) {
            $product->variations()->delete(); 
            foreach ($request->variations as $key => $varData) {
                $variation = new \App\Models\ProductVariation();
                $variation->product_id = $product->id;
                $variation->variation_name = $request->variation_name;
                $variation->variation_option = $varData['option'];
                $variation->price = str_replace(['.', ','], '', $varData['price'] ?? 0);
                $variation->stock = max(0, (int)($varData['stock'] ?? 0));
                $variation->sku = $varData['sku'] ?? null;

                if ($request->has("var_images.{$key}")) {
                    $imgData = $request->input("var_images.{$key}");
                    if (str_contains($imgData, ';base64,')) {
                        $image_parts = explode(";base64,", $imgData);
                        $image_type = explode("image/", $image_parts[0])[1];
                        $fileName = time() . '_var_' . uniqid() . '.' . $image_type;
                        \Storage::disk('public')->put('products/variations/' . $fileName, base64_decode($image_parts[1]));
                        $variation->image_path = 'products/variations/' . $fileName;
                    } else {
                        $variation->image_path = str_replace(asset('storage') . '/', '', $imgData);
                    }
                }
                $variation->save();
            }
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($productId, Request $request)
    {
        $product = Product::find($productId);

        if (empty($product)) {
            return redirect()->back()->with('error', 'Product not found');
        }

        $productImages = ProductImage::where('product_id', $productId)->get();

        // Hapus file fisik gambar dari storage
        if (!empty($productImages)) {
            foreach ($productImages as $productImage) {
                Storage::disk('public')->delete($productImage->image_path);
            }
            // Hapus data dari DB (Sebenernya otomatis kalau pakai onDelete('cascade') di migration, tapi buat jaga-jaga)
            ProductImage::where('product_id', $productId)->delete();
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus!');
    }

    // FUNGSI BUAT QUICK EDIT (Harga & Stok dari Popup)
    public function quickUpdate(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['status' => false, 'message' => 'Produk tidak ditemukan']);
        }

        $type = $request->type; // Nangkep ini mau update 'price' atau 'stock'

        if ($request->has('variations')) {
            // Update masing-masing anak variasi
            foreach ($request->variations as $varId => $val) {
                $variation = \App\Models\ProductVariation::find($varId);
                if ($variation && $variation->product_id == $product->id) {
                    if ($type == 'price') {
                        $variation->price = str_replace(['.', ','], '', $val);
                    }
                    if ($type == 'stock') {
                        $variation->stock = max(0, (int)$val);
                    }
                    $variation->save();
                }
            }
            
            // Kalau yang diupdate stok variasi, total stok induknya harus kita hitung ulang
            if ($type == 'stock') {
                $product->qty = $product->variations()->sum('stock');
                $product->save();
            }
        } else {
            // Kalau NGGAK PUNYA variasi (Produk Tunggal)
            if ($type == 'price') {
                $product->price = str_replace(['.', ','], '', $request->main_val);
            }
            if ($type == 'stock') {
                $product->qty = max(0, (int)$request->main_val);
            }
            $product->save();
        }

        return response()->json([
            'status' => true, 
            'message' => ($type == 'price' ? 'Harga' : 'Stok') . ' berhasil diupdate!'
        ]);
    }
}