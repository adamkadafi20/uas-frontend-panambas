<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    private $storeId = 1; // ID patokan untuk "Toko"

    // --- FUNGSI PINTAR: Nentuin dia Pembeli atau Staf Toko ---
    private function getEffectiveUserId() 
    {
        $user = Auth::user();
        // Kalau yang login role-nya admin/seller, atau emang ID-nya 1, dia bertindak sebagai TOKO (ID 1)
        if (in_array($user->role, ['admin', 'seller']) || $user->id == $this->storeId) {
            return $this->storeId;
        }
        // Kalau pembeli biasa, pakai ID asli mereka (misal ID 5)
        return $user->id;
    }

    // --- FUNGSI BARU: Ngambil Total Notif Unread buat Floating Icon ---
    public function getGlobalUnreadCount()
    {
        $effectiveId = $this->getEffectiveUserId();
        $count = Message::where('receiver_id', $effectiveId)->where('is_read', false)->count();
        return response()->json(['unread_count' => $count]);
    }

    // --- DAFTAR KONTAK ---
    public function getChatList()
    {
        $effectiveId = $this->getEffectiveUserId();

        $userIds = Message::where('sender_id', $effectiveId)
            ->orWhere('receiver_id', $effectiveId)
            ->get()
            ->flatMap(function($msg) {
                return [$msg->sender_id, $msg->receiver_id];
            })
            ->unique()
            ->reject(fn($id) => $id == $effectiveId);

        $users = User::whereIn('id', $userIds)->get()->map(function($user) use ($effectiveId) {
            
            $latestMsg = Message::where(function($q) use ($user, $effectiveId) {
                $q->where('sender_id', $effectiveId)->where('receiver_id', $user->id);
            })->orWhere(function($q) use ($user, $effectiveId) {
                $q->where('sender_id', $user->id)->where('receiver_id', $effectiveId);
            })->latest()->first();

            $unreadCount = Message::where('sender_id', $user->id)
                                  ->where('receiver_id', $effectiveId)
                                  ->where('is_read', false)
                                  ->count();

            if ($latestMsg) {
                if ($latestMsg->type === 'image') {
                    $user->latest_message = '📷 Mengirim foto';
                } elseif ($latestMsg->type === 'video') {
                    $user->latest_message = '🎥 Mengirim video';
                } elseif ($latestMsg->type === 'product') {
                    $user->latest_message = '📦 Membagikan produk';
                } elseif ($latestMsg->type === 'order') {
                    $user->latest_message = '🧾 Membagikan pesanan';
                } else {
                    $user->latest_message = $latestMsg->message;
                }
                $user->latest_time = $latestMsg->created_at->format('H:i');
                $user->latest_time_raw = $latestMsg->created_at;
            } else {
                $user->latest_message = '';
                $user->latest_time = '';
                $user->latest_time_raw = null;
            }
            
            $user->unread_count = $unreadCount;

            return $user;
        });
        
        $users = $users->sortByDesc('latest_time_raw')->values();

        return response()->json($users);
    }

    // --- TARIK PESAN & OTOMATIS BACA ---
    public function fetchMessages($userId)
    {
        $effectiveId = $this->getEffectiveUserId();

        Message::where('sender_id', $userId)
               ->where('receiver_id', $effectiveId)
               ->where('is_read', false)
               ->update(['is_read' => true]);
        
        $messages = Message::where(function($q) use ($userId, $effectiveId) {
            $q->where('sender_id', $effectiveId)->where('receiver_id', $userId);
        })->orWhere(function($q) use ($userId, $effectiveId) {
            $q->where('sender_id', $userId)->where('receiver_id', $effectiveId);
        })->orderBy('created_at', 'asc')->get();

        return response()->json(['messages' => $messages, 'my_id' => $effectiveId]);
    }

    // --- FUNGSI KIRIM PESAN ---
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov|max:10240',
        ]);

        $effectiveId = $this->getEffectiveUserId();

        $data = [
            'sender_id' => $effectiveId, 
            'receiver_id' => $request->receiver_id,
            'message' => $request->message ?? '',
            'type' => $request->type ?? 'text',
            'is_read' => false
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('chat_files', 'public'); 
            $data['file_path'] = $path;
            
            if (str_contains($file->getMimeType(), 'video')) {
                $data['type'] = 'video';
            } else {
                $data['type'] = 'image';
            }
        } elseif ($request->has('reference_id')) {
            $data['reference_id'] = $request->reference_id;
        }

        $message = Message::create($data);
        return response()->json(['success' => true, 'message' => $message]);
    }

    // --- FUNGSI CARI PRODUK ---
    public function getChatProducts(Request $request)
    {
        $search = $request->search;
        
        $products = \App\Models\Product::when($search, function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%");
        })->latest()->take(20)->get();

        $formattedProducts = $products->map(function($product) {
            $minPrice = $product->price;
            $maxPrice = $product->price;

            try {
                $variants = \Illuminate\Support\Facades\DB::table('product_variations')
                                ->where('product_id', $product->id)
                                ->get();

                if ($variants->isNotEmpty()) {
                    $minPrice = $variants->min('price');
                    $maxPrice = $variants->max('price');
                }
            } catch (\Exception $e) {}

            if ($minPrice > 0 && $maxPrice > 0 && $minPrice != $maxPrice) {
                $priceLabel = $minPrice . '-' . $maxPrice;
            } else {
                $priceLabel = $minPrice > 0 ? $minPrice : ($maxPrice > 0 ? $maxPrice : 0); 
            }

            return [
                'id' => $product->id,
                'title' => $product->title,
                'price_label' => (string) $priceLabel,
            ];
        });

        return response()->json($formattedProducts);
    }

    // --- FUNGSI CARI PESANAN ---
    public function getChatOrders(Request $request)
    {
        $search = $request->search;
        $effectiveId = $this->getEffectiveUserId();

        $orders = \App\Models\Order::when($search, function($q) use ($search) {
            $q->where('id', 'like', "%{$search}%")
              ->orWhere('invoice_number', 'like', "%{$search}%");
        })->when($effectiveId != $this->storeId, function($q) use ($effectiveId) {
            // Kalau pembeli yg nyari, cuma nampilin pesanan dia sendiri
            $q->where('user_id', $effectiveId);
        })->latest()->take(20)->get();
        
        return response()->json($orders);
    }
}