<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminLoginController extends Controller
{
    public function index(){
        return view('admin.login');
    }
    public function authenticate(Request $request){

        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->passes()){

            // Ganti guard ke 'web' karena akun lo ada di tabel users
            if (Auth::guard('web')->attempt(['email' => $request->email,'password'=>
            $request->password],$request->get('remember'))) {

                $admin = Auth::guard('web')->user();

                // Cek role 'admin' sesuai isi database lo tadi
                if ($admin->role == 'admin' || $admin->role == 'seller') {
                    return redirect()->route('admin.dashboard');
                } else {
                    Auth::guard('web')->logout();
                    return redirect()->route('admin.login')->with('error','You are not authorized to access admin panel.');
                }

            }else{
                return redirect()->route('admin.login')->with('error','Either Email/Password is Incorrect');
            }

        }else{
            return redirect()->route('admin.login')
            ->withErrors($validator)
            ->withInput($request->only('email'));
        }
    }
    public function forgotPassword() {
    return view('admin.forgot-password');
}

public function processForgotPassword(Request $request) {
    $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'new_password' => 'required|min:5'
        ], [
            'email.exists' => 'Email tidak terdaftar dalam sistem.'
        ]);

    if ($validator->passes()) {
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->new_password);
        $user->save();
        

       return redirect()->route('admin.login')->with('success', 'Password berhasil diubah. Silahkan login dengan password baru.');
        } else {
            return redirect()->route('admin.forgotPassword')
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }
    }

    
}
