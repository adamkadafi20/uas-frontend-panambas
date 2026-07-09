<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AdminLoginController;
use App\Http\Controllers\admin\HomeController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\SubCategoryController;
use App\Http\Controllers\admin\TempImagesController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\FrontAuthController;
use App\Http\Controllers\UserController as FrontUserController;
use App\Http\Controllers\admin\ShippingController;
use App\Http\Controllers\admin\DiscountController;
use App\Http\Controllers\admin\ShopController;
use App\Http\Controllers\ChatController; 
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// =======================================================
// FRONT-END ROUTES (Pengunjung & Pembeli)
// =======================================================
Route::get('/', [FrontController::class, 'index'])->name('front.home');
Route::get('/shop', [FrontController::class, 'shop'])->name('front.shop'); 
Route::get('/product/{id}', [FrontController::class, 'product'])->name('front.product'); 
Route::get('/cart', [FrontController::class, 'cart'])->name('cart'); 
Route::get('/checkout', [FrontController::class, 'checkout'])->name('front.checkout');
Route::get('/thanks/{orderId}', [FrontController::class, 'thanks'])->name('front.thanks');

// Aksi Keranjang
Route::post('/add-to-cart', [FrontController::class, 'addToCart'])->name('front.addToCart');
Route::post('/update-cart', [FrontController::class, 'updateCart'])->name('front.updateCart');
Route::post('/delete-item', [FrontController::class, 'deleteItem'])->name('front.deleteItem.cart');

// Rute Auth (Login, Register, Lupa Password)
Route::middleware('guest')->group(function () {
    Route::get('/login', [FrontAuthController::class, 'login'])->name('login');
    Route::post('/login', [FrontAuthController::class, 'authenticate'])->name('login.post');
    Route::get('/register', [FrontAuthController::class, 'register'])->name('register');
    Route::post('/register', [FrontAuthController::class, 'storeRegister'])->name('register.post');
    Route::get('/lupa-password', [FrontAuthController::class, 'forgotPassword'])->name('forgot-password');
    Route::post('/lupa-password', [FrontAuthController::class, 'processForgotPassword'])->name('forgot-password.post');
});
Route::post('/logout', [FrontAuthController::class, 'logout'])->name('logout');


// =======================================================
// AUTHENTICATED USER ROUTES (Pembeli Login & Fitur Chat)
// =======================================================
Route::middleware(['auth','prevent-back-history'])->group(function () {
    // Profil & Pesanan
    Route::get('/user/profile', [FrontUserController::class, 'profile'])->name('user.profile');
    Route::post('/user/profile/update', [FrontUserController::class, 'updateProfile'])->name('user.profile.update');
    Route::get('/pesanan-saya', [FrontController::class, 'myOrders'])->name('front.pesanan');
    Route::get('/user/orders', [FrontUserController::class, 'orders'])->name('user.orders');
    Route::get('/user/orders/{id}', [FrontUserController::class, 'orderDetail'])->name('user.orders.detail');
    Route::post('/user/orders/{id}/refund', [\App\Http\Controllers\admin\OrderController::class, 'submitRefund'])->name('user.orders.refund');
    
    // 👉 INI YANG DIBENERIN: Diarahin ke buyerCancelOrder di OrderController
    Route::post('/user/orders/cancel', [OrderController::class, 'buyerCancelOrder'])->name('user.orders.cancel');
    Route::post('/user/orders/{id}/undo-cancel', [OrderController::class, 'undoCancelRequest'])->name('user.orders.undo_cancel');
    
    Route::put('/user/orders/{id}/complete', [FrontController::class, 'completeOrder'])->name('user.orders.complete');
    Route::post('/rating/store', [FrontController::class, 'storeRating'])->name('front.rating.store');

    // Proses Checkout
    Route::post('/cart/add', [FrontController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/remove', [FrontController::class, 'removeCart'])->name('cart.remove');
    Route::get('/checkout', [FrontController::class, 'checkout'])->name('checkout');
    Route::get('/thanks/{orderId}', [FrontController::class, 'thanks'])->name('thanks');
    Route::post('/process-order', [FrontController::class, 'processOrder'])->name('process.order');
    Route::post('/address/store', [FrontController::class, 'storeAddress'])->name('address.store');

    // Fitur Chat
    Route::prefix('chat')->group(function () {
        Route::get('/list', [ChatController::class, 'getChatList'])->name('chat.list');
        Route::get('/fetch/{userId}', [ChatController::class, 'fetchMessages'])->name('chat.fetch');
        Route::post('/send', [ChatController::class, 'sendMessage'])->name('chat.send');
        Route::get('/unread-count', [ChatController::class, 'getGlobalUnreadCount'])->name('chat.unreadCount');
        Route::get('/products', [ChatController::class, 'getChatProducts'])->name('chat.products'); 
        Route::get('/orders', [ChatController::class, 'getChatOrders'])->name('chat.orders');
    });
});


// =======================================================
// BACK-END ADMIN ROUTES (Super Admin & Seller)
// =======================================================
Route::group(['prefix'=> 'admin'],function(){
    
    // Admin Guest (Belum Login)
    Route::group(['middleware' => 'admin.guest'],function(){
        Route::get('/login',[AdminLoginController::class,'index'])->name('admin.login');
        Route::post('/authenticate',[AdminLoginController::class, 'authenticate'])->name('admin.authenticate');
        Route::get('/forgot-password', [AdminLoginController::class, 'forgotPassword'])->name('admin.forgotPassword');
        Route::post('/process-forgot-password', [AdminLoginController::class, 'processForgotPassword'])->name('admin.processForgotPassword');
    });

    // Admin Auth (Sudah Login)
    Route::group(['middleware' => 'admin.auth'],function(){

        // Dashboard & Pengaturan Umum
        Route::get('/dashboard',[HomeController::class,'index'])->name('admin.dashboard');
        Route::get('/logout',[HomeController::class,'logout'])->name('admin.logout');
        
        // Settings & Security
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('admin.settings');
        Route::post('/settings/profile', [\App\Http\Controllers\Admin\SettingController::class, 'updateProfile'])->name('admin.settings.updateProfile');
        Route::post('/settings/address', [\App\Http\Controllers\Admin\SettingController::class, 'updateAddress'])->name('admin.settings.updateAddress');
        Route::post('/verify-security', [\App\Http\Controllers\Admin\SettingController::class, 'verifySecurity'])->name('admin.verifySecurity');
        
        // Manajemen User (Seller)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}/update', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}/delete', [UserController::class, 'destroy'])->name('users.delete');
        
        // Manajemen Banner
        Route::get('/banners', [\App\Http\Controllers\admin\BannerController::class, 'index'])->name('banners.index');
        Route::get('/banners/create', [\App\Http\Controllers\admin\BannerController::class, 'create'])->name('banners.create');
        Route::post('/banners', [\App\Http\Controllers\admin\BannerController::class, 'store'])->name('banners.store');
        Route::delete('/banners/{id}', [\App\Http\Controllers\admin\BannerController::class, 'destroy'])->name('banners.destroy');
        
        // Fitur Saldo & Ulasan
        Route::get('/saldo', [\App\Http\Controllers\admin\SaldoController::class, 'index'])->name('admin.saldo.index');
        Route::get('/reviews', [\App\Http\Controllers\admin\ProductRatingController::class, 'index'])->name('reviews.index');
        Route::post('/reviews/{id}/reply', [\App\Http\Controllers\admin\ProductRatingController::class, 'reply'])->name('reviews.reply');
        
        // Manajemen Kategori
        Route::get('/categories',[CategoryController::class,'index'])->name('categories.index');
        Route::get('/categories/create',[CategoryController::class,'create'])->name('categories.create');
        Route::post('/categories',[CategoryController::class,'store'])->name('categories.store');
        Route::get('/categories/{category}/edit',[CategoryController::class,'edit'])->name('categories.edit');
        Route::put('/categories/{category}',[CategoryController::class,'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.delete');

        // Manajemen Sub Kategori
        Route::get('/sub-categories', [SubCategoryController::class, 'index'])->name('sub-categories.index');
        Route::get('/sub-categories/create', [SubCategoryController::class, 'create'])->name('sub-categories.create');
        Route::post('/sub-categories', [SubCategoryController::class, 'store'])->name('sub-categories.store');
        Route::get('/sub-categories/{subCategory}/edit', [SubCategoryController::class, 'edit'])->name('sub-categories.edit');
        Route::put('/sub-categories/{subCategory}', [SubCategoryController::class, 'update'])->name('sub-categories.update');
        Route::delete('/sub-categories/{subCategory}', [SubCategoryController::class, 'destroy'])->name('sub-categories.delete');

        // Manajemen Brand
        Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
        Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
        Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
        Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.delete');

        // Manajemen Produk
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::post('/products/{id}/quick-update', [\App\Http\Controllers\admin\ProductController::class, 'quickUpdate'])->name('products.quick_update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy'); 
        Route::get('/get-products', [ProductController::class, 'getProducts'])->name('products.getProducts');
        
        // Dekorasi Toko
        Route::get('/admin/shop', [ShopController::class, 'index'])->name('admin.shop.index');

        // Manajemen Pesanan (Orders)
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/mass-shipping', [OrderController::class, 'massShipping'])->name('orders.mass_shipping');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.detail');
        Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
        // Rute untuk aksi proses Refund oleh Admin/Seller
        Route::post('/orders/{id}/process-refund', [\App\Http\Controllers\admin\OrderController::class, 'approveRefund'])->name('admin.orders.processRefund');
        Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
        Route::post('/orders/update-status/{id}', [OrderController::class, 'updateShippingStatus'])->name('orders.updateStatus');
        Route::get('/orders/{order}/print-label', [OrderController::class, 'printLabel'])->name('orders.printLabel');
        Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.delete');
        Route::post('/orders/{order}/send-email', [OrderController::class, 'sendInvoiceEmail'])->name('orders.sendInvoiceEmail');
        Route::put('/orders/{orderId}/cancel', [OrderController::class, 'cancelOrder'])->name('orders.cancel');
        Route::post('/orders/mass-print', [OrderController::class, 'massPrint'])->name('orders.massPrint');
        
        // Utilities (Upload Gambar Sementara)
        Route::post('/upload-temp-image',[TempImagesController::class,'create'])->name('temp-images.create');
      
        // Generate Slug Otomatis
        Route::get('/getSlug',function(Request $request){
            $slug = '';
            if (!empty($request->title)) {
                $slug = Str::slug($request->title);
            }
            return response()->json([
                'status' => true,
                'slug' => $slug
            ]);
        })->name('getSlug');

    });
});

Route::get('/buka-gambar', function () {
    \Illuminate\Support\Facades\Artisan::call('storage:link');
    return 'Berhasil buka gembok gambar Bre!';
});