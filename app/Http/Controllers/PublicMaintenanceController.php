<?php

namespace App\Http\Controllers;

use App\Models\RiwayatMaintenance;

class PublicMaintenanceController extends Controller
{
    public function show(RiwayatMaintenance $riwayat)
    {
        $riwayat->load([
            'perangkat:id,nomor_inventaris,nama_perangkat',
            'lokasi:id,nama_lokasi',
            'user:id,name',
            'maintenanceTypes:id,nama',
            'komponenDetails.komponen:id,nama',
        ]);

        return view('public.maintenance.show', compact('riwayat'));
    }
}
