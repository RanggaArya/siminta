<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SsoController extends Controller
{
    public function loginViaToken(Request $request)
    {
        $token = $request->query('token');
        $redirectUrl = $request->query('redirect', '/admin'); // Default ke admin

        if (!$token) return redirect('/admin/login')->with('error', 'Token missing');

        // Cari token di database
        $tokenData = DB::table('login_tokens')
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if ($tokenData) {
            // Login User
            $user = User::find($tokenData->user_id);
            if ($user) {
                Auth::login($user);
                
                // Hapus token biar one-time use
                DB::table('login_tokens')->where('id', $tokenData->id)->delete();
                
                // Redirect ke halaman Mutasi/Maintenance
                return redirect($redirectUrl);
            }
        }

        return redirect('/admin/login')->with('error', 'Login otomatis gagal/token expired');
    }
}