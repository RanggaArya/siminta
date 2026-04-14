<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrMaintenanceController;
use App\Http\Controllers\PublicPerangkatController;
use App\Http\Controllers\PublicMaintenanceController;
use App\Models\Perangkat;
use App\Http\Controllers\ExportController;
use App\Filament\Exports\MutasiResumeExport;
use Illuminate\Support\Facades\Artisan;
// use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\SsoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


Route::get('/', function () {
    return redirect('/admin');
});
Route::get('/perangkat/{perangkat}', [PublicPerangkatController::class, 'show'])
    ->name('public.perangkat.show');

Route::get('/maintenance/{riwayat}', [PublicMaintenanceController::class, 'show'])
    ->name('public.maintenance.show');

Route::get('/cetak-semua-stiker', [PublicPerangkatController::class, 'cetakSemuaStiker'])
    ->name('cetak.semua.stiker');

Route::get('/cetak/stiker/{perangkat}', [PublicPerangkatController::class, 'cetakSatu'])
    ->name('cetak.satu.stiker');

Route::get('/export/perangkat-resume/excel', [ExportController::class, 'exportPerangkatResumeExcel'])
    ->name('export.perangkat.resume.excel')
    ->middleware('auth');

Route::get('/export/perangkat-resume/pdf', [ExportController::class, 'exportPerangkatResumePdf'])
    ->name('export.perangkat.resume.pdf')
    ->middleware('auth');
Route::get('/export/mutasi/resume-pdf', [ExportController::class, 'exportMutasiResumePdf'])
    ->name('mutasi.resume.pdf')
    ->middleware('auth');

Route::get('/export/peminjaman/resume-pdf', [ExportController::class, 'exportPeminjamanResumePdf'])
    ->name('peminjaman.resume.pdf')
    ->middleware('auth');

Route::get('/export/penarikan/resume-pdf', [ExportController::class, 'exportPenarikanResumePdf'])
    ->name('penarikan.resume.pdf')
    ->middleware('auth');

Route::get('/export/perangkat-all', [ExportController::class, 'exportPerangkatAllExcel'])
    ->name('export.perangkat.all.excel');
    
Route::middleware(['auth'])->group(function () {
  Route::get('/export/supervisi/excel', [ExportController::class, 'exportSupervisiExcel'])
    ->name('export.supervisi.excel');
});
Route::get('/auth-sso', [SsoController::class, 'loginViaToken']);

// ROUTE SSO LOGIN DINAMIS (Bisa Mutasi / Maintenance)
Route::get('/sso-login', function (Request $request) {
    try {
        $payload = json_decode(Crypt::decryptString($request->query('token')), true);

        if (now()->timestamp > $payload['expires']) {
            abort(403, 'SSO Token Sudah Kedaluwarsa (Expired). Silakan ulangi dari Scanner.');
        }

        $user = User::find($payload['user_id']);
        if ($user) {
            Auth::login($user); 
            $request->session()->regenerate(); 
        } else {
            abort(403, 'User ID tidak ditemukan di database target.');
        }

        // --- BACA ACTION UNTUK MENENTUKAN ARAH REDIRECT ---
        $action = $request->query('action', 'mutasi'); // Default mutasi
        $path = $action === 'maintenance' 
            ? '/admin/riwayat-maintenances/create' 
            : '/admin/mutasis/create';

        // Buang parameter 'token' dan 'action' dari URL agar bersih
        $queryData = $request->except(['token', 'action']);
        
        // Redirect final ke Form Filament
        $redirectUrl = url($path . '?' . http_build_query($queryData));

        return redirect($redirectUrl);

    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
        abort(403, 'SSO Gagal: Token tidak valid atau APP_KEY berbeda! Pastikan APP_KEY di Scanner dan Inventaris sama persis.');
    } catch (\Exception $e) {
        abort(403, 'SSO Gagal: ' . $e->getMessage());
    }
});
    
// Route::get('/refresh-system', function () {
    
//     // 1. Clear Cache
//     Artisan::call('optimize:clear');
    
//     // 2. Storage Link (Opsional, buat jaga-jaga kalau gambar tidak muncul)
//     Artisan::call('storage:link');

//     return '<h1>System Refreshed!</h1><p>Cache, Config, Route, and View cleared.</p>';
// });

Route::model('perangkat', Perangkat::class);

// Route::get('/test-reminder-h3', function () {
//     Artisan::call('peminjaman:reminder-h3');
//     return nl2br(Artisan::output());
// });

// Route::get('/test-schedule-list', function () {
//     Artisan::call('schedule:list');
//     return '<pre>' . e(Artisan::output()) . '</pre>';
// });

Route::get('/maintenance-clear', function () {

    abort_unless(request('key') === 'siminta123', 403);

    Artisan::call('optimize:clear');

    return 'DONE-REFRESH';
});

// Route::get('/run-super-seeder', function () {

//     abort_unless(request('key') === 'siminta123', 403);

//     Artisan::call('db:seed', [
//         '--class' => 'Database\\Seeders\\UserSuperSeeder',
//         '--force' => true
//     ]);

//     return 'SUPER USER SEEDED ✅';
// });


// Route::get('/storagelink', function () {
//     $targetFolder = storage_path('app/public');
//     $linkFolder = public_path('storage');
//     try {
//         symlink($targetFolder, $linkFolder);
//         return 'Storage link berhasil dibuat!';
//     } catch (\Exception $e) {
//         return 'Gagal membuat storage link: ' . $e->getMessage();
//     }
// });
