<?php
namespace App\Http\Controllers;

use App\Models\Perangkat;
use Illuminate\Http\Request;

class PublicPerangkatController extends Controller
{
    public function show(Perangkat $perangkat)
    {
        $perangkat->load('riwayatMaintenances', 'lokasi', 'jenis', 'status', 'kondisi');
        return view('public.perangkat-detail', compact('perangkat'));
    }
    public function cetakSemuaStiker()
    {
        set_time_limit(300);
        // Gunakan eager load untuk menghindari N+1
        // Gunakan cursor() supaya tidak load semua data sekaligus
        $records = Perangkat::with('lokasi')->cursor();
    
        return view('cetak-stiker-massal', compact('records'));
    }

    public function cetakSatu(Perangkat $perangkat)
    {
        $record = $perangkat; 
        return view('cetak-stiker', compact('record'));
    }
}