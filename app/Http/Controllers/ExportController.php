<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Filament\Exports\PerangkatResumeExport;
use App\Filament\Exports\MutasiResumeExport;
use App\Models\Perangkat;
use App\Models\Mutasi;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Models\Peminjaman;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use App\Models\PenarikanAlat;
use App\Models\Supervisi;
use App\Filament\Exports\SupervisiAllExport;
use Illuminate\Support\Str;

class ExportController extends Controller
{
    private function buildResumeQuery(?int $year, ?int $monthFrom, ?int $monthTo, string $dateField = 'created_at')
    {
        if ($monthFrom && $monthTo && $monthFrom > $monthTo) {
            [$monthFrom, $monthTo] = [$monthTo, $monthFrom];
        }

        $query = Perangkat::query()
            ->leftJoin('statuses', 'perangkats.status_id', '=', 'statuses.id');

        if ($year) {
            $query->whereYear("perangkats.$dateField", $year);

            if ($monthFrom && (!$monthTo || $monthTo == $monthFrom)) {
                $query->whereMonth("perangkats.$dateField", $monthFrom);
            }

            if ($monthFrom && $monthTo && $monthTo > $monthFrom) {
                $query->whereMonth("perangkats.$dateField", '>=', $monthFrom)
                    ->whereMonth("perangkats.$dateField", '<=', $monthTo);
            }
        }

        return $query->select(
            'perangkats.nama_perangkat',
            DB::raw("COUNT(CASE WHEN statuses.nama_status = 'Digunakan' THEN 1 END) AS aktif_count"),
            DB::raw("SUM(CASE WHEN statuses.nama_status = 'Digunakan' THEN perangkats.harga ELSE 0 END) AS aktif_sum"),
            DB::raw("COUNT(CASE WHEN statuses.nama_status = 'Dalam Perbaikan' THEN 1 END) AS rusak_count"),
            DB::raw("SUM(CASE WHEN statuses.nama_status = 'Dalam Perbaikan' THEN perangkats.harga ELSE 0 END) AS rusak_sum"),
            DB::raw("COUNT(CASE WHEN statuses.nama_status = 'Tidak Digunakan' THEN 1 END) AS tidak_digunakan_count"),
            DB::raw("SUM(CASE WHEN statuses.nama_status = 'Tidak Digunakan' THEN perangkats.harga ELSE 0 END) AS tidak_digunakan_sum"),
            DB::raw("COUNT(perangkats.id) AS total_count"),
            DB::raw("SUM(perangkats.harga) AS total_sum")
        )
            ->groupBy('perangkats.nama_perangkat')
            ->orderBy('perangkats.nama_perangkat');
    }

    private function getResumeDataByPeriod(?int $year, ?int $monthFrom, ?int $monthTo)
    {
        $data = $this->buildResumeQuery($year, $monthFrom, $monthTo)->get();

        $grandTotal = [
            'aktif_count'            => (int) $data->sum('aktif_count'),
            'aktif_sum'              => (float) $data->sum('aktif_sum'),
            'rusak_count'            => (int) $data->sum('rusak_count'),
            'rusak_sum'              => (float) $data->sum('rusak_sum'),
            'tidak_digunakan_count'  => (int) $data->sum('tidak_digunakan_count'),
            'tidak_digunakan_sum'    => (float) $data->sum('tidak_digunakan_sum'),
            'total_count'            => (int) $data->sum('total_count'),
            'total_sum'              => (float) $data->sum('total_sum'),
        ];

        return compact('data', 'grandTotal');
    }

    private function makePeriodeLabel(?int $year, ?int $monthFrom, ?int $monthTo): string
    {
        if (!$year && !$monthFrom && !$monthTo) {
            return mb_strtoupper(now()->locale('id')->translatedFormat('F Y'));
        }

        if (!$year && ($monthFrom || $monthTo)) {
            $year = (int) now()->year;
        }

        if ($year && !$monthFrom && !$monthTo) {
            return (string) $year;
        }

        if ($year && $monthFrom && (!$monthTo || $monthTo == $monthFrom)) {
            $single = \Illuminate\Support\Carbon::create($year, $monthFrom, 1)
                ->locale('id')->translatedFormat('F Y');

            return mb_strtoupper($single);
        }

        if ($year && $monthFrom && $monthTo) {
            if ($monthFrom > $monthTo) {
                [$monthFrom, $monthTo] = [$monthTo, $monthFrom];
            }

            $start = \Illuminate\Support\Carbon::create($year, $monthFrom, 1)
                ->locale('id')->translatedFormat('F');
            $end = \Illuminate\Support\Carbon::create($year, $monthTo, 1)
                ->locale('id')->translatedFormat('F Y');

            return mb_strtoupper($start . '–' . $end);
        }

        return (string) $year;
    }

    public function exportPerangkatResumeExcel(Request $request)
    {
        ini_set('memory_limit', '512M');

        $year      = $request->integer('year');
        $monthFrom = $request->integer('month_from');
        $monthTo   = $request->integer('month_to');

        if (!$monthFrom && !$monthTo) {
            $single   = $request->integer('month');
            $monthFrom = $single;
            $monthTo   = $single;
        }

        [$monthFrom, $monthTo] = $this->normalizeMonthRange($monthFrom, $monthTo);

        $resume       = $this->getResumeDataByPeriod($year, $monthFrom, $monthTo);
        $periodeLabel = $this->makePeriodeLabel($year, $monthFrom, $monthTo);

        $password = $this->getExportPassword();

        $filename = 'resume_inventaris_' . str_replace([' ', '–'], ['_', '-'], strtolower($periodeLabel)) . '.xlsx';

        return Excel::download(
            new PerangkatResumeExport($resume['data'], $resume['grandTotal'], $periodeLabel, $password),
            $filename
        );
    }

    public function exportPerangkatResumePdf(Request $request)
    {
        ini_set('memory_limit', '512M');

        $year      = $request->integer('year');
        $monthFrom = $request->integer('month_from');
        $monthTo   = $request->integer('month_to');

        if (!$monthFrom && !$monthTo) {
            $single   = $request->integer('month');
            $monthFrom = $single;
            $monthTo   = $single;
        }

        [$monthFrom, $monthTo] = $this->normalizeMonthRange($monthFrom, $monthTo);

        $resume       = $this->getResumeDataByPeriod($year, $monthFrom, $monthTo);
        $periodeLabel = $this->makePeriodeLabel($year, $monthFrom, $monthTo);

        $viewData = [
            'data'         => $resume['data'],
            'grandTotal'   => $resume['grandTotal'],
            'bulanTahun'   => $periodeLabel,
            'periodeLabel' => $periodeLabel,
        ];

        $pdf = PDF::loadView('exports.perangkat-resume-pdf', $viewData)
            ->setPaper('A4', 'landscape');

        $userPassword  = $this->getExportPassword();
        $ownerPassword = $this->getExportOwnerPassword();

        $this->encryptPdf($pdf, $userPassword, $ownerPassword, ['print']);

        $filename = 'resume_inventaris_' . str_replace([' ', '–'], ['_', '-'], strtolower($periodeLabel)) . '.pdf';
        return $pdf->download($filename);
    }

    private function buildMutasiResumeQuery(?int $year, ?int $monthFrom, ?int $monthTo, string $dateField = 'tanggal_mutasi')
    {
        if ($monthFrom && $monthTo && $monthFrom > $monthTo) {
            [$monthFrom, $monthTo] = [$monthTo, $monthFrom];
        }

        $query = Mutasi::query()
            ->leftJoin('lokasis as asal', 'mutasis.lokasi_asal_id', '=', 'asal.id')
            ->leftJoin('lokasis as tujuan', 'mutasis.lokasi_mutasi_id', '=', 'tujuan.id')
            ->leftJoin('kondisis', 'mutasis.kondisi_id', '=', 'kondisis.id');

        if ($year) {
            $query->whereYear("mutasis.$dateField", $year);

            if ($monthFrom && (!$monthTo || $monthTo == $monthFrom)) {
                $query->whereMonth("mutasis.$dateField", $monthFrom);
            }

            if ($monthFrom && $monthTo && $monthTo > $monthFrom) {
                $query->whereMonth("mutasis.$dateField", '>=', $monthFrom)
                    ->whereMonth("mutasis.$dateField", '<=', $monthTo);
            }
        }

        return $query->select(
            'tujuan.nama_lokasi as lokasi_tujuan',
            DB::raw("COUNT(mutasis.id) AS total_mutasi"),
            DB::raw("COUNT(CASE WHEN mutasis.tanggal_diterima IS NOT NULL THEN 1 END) AS diterima_count"),
            DB::raw("COUNT(CASE WHEN mutasis.tanggal_diterima IS NULL THEN 1 END) AS pending_count"),
            DB::raw("COUNT(CASE WHEN kondisis.nama_kondisi = 'Baik' THEN 1 END) AS kondisi_baik"),
            DB::raw("COUNT(CASE WHEN kondisis.nama_kondisi = 'Rusak' THEN 1 END) AS kondisi_rusak")
        )
            ->groupBy('tujuan.nama_lokasi')
            ->orderBy('tujuan.nama_lokasi');
    }

    private function getMutasiResumeDataByPeriod(?int $year, ?int $monthFrom, ?int $monthTo)
    {
        $data = $this->buildMutasiResumeQuery($year, $monthFrom, $monthTo)->get();

        $grandTotal = [
            'total_mutasi'   => (int) $data->sum('total_mutasi'),
            'diterima_count' => (int) $data->sum('diterima_count'),
            'pending_count'  => (int) $data->sum('pending_count'),
            'kondisi_baik'   => (int) $data->sum('kondisi_baik'),
            'kondisi_rusak'  => (int) $data->sum('kondisi_rusak'),
        ];

        return compact('data', 'grandTotal');
    }

    private function makeMutasiPeriodeLabel(?int $year, ?int $monthFrom, ?int $monthTo): string
    {
        if (!$year && !$monthFrom && !$monthTo) {
            return mb_strtoupper(now()->locale('id')->translatedFormat('F Y'));
        }

        if (!$year && ($monthFrom || $monthTo)) {
            $year = (int) now()->year;
        }

        if ($year && !$monthFrom && !$monthTo) {
            return (string) $year;
        }

        if ($year && $monthFrom && (!$monthTo || $monthTo == $monthFrom)) {
            $single = \Illuminate\Support\Carbon::create($year, $monthFrom, 1)
                ->locale('id')->translatedFormat('F Y');

            return mb_strtoupper($single);
        }

        if ($year && $monthFrom && $monthTo) {
            if ($monthFrom > $monthTo) {
                [$monthFrom, $monthTo] = [$monthTo, $monthFrom];
            }

            $start = \Illuminate\Support\Carbon::create($year, $monthFrom, 1)
                ->locale('id')->translatedFormat('F');
            $end = \Illuminate\Support\Carbon::create($year, $monthTo, 1)
                ->locale('id')->translatedFormat('F Y');

            return mb_strtoupper($start . '–' . $end);
        }

        return (string) $year;
    }

    public function exportMutasiResumeExcel(Request $request)
    {
        ini_set('memory_limit', '512M');

        $year      = $request->integer('year');
        $monthFrom = $request->integer('month_from');
        $monthTo   = $request->integer('month_to');

        if (!$monthFrom && !$monthTo) {
            $single   = $request->integer('month');
            $monthFrom = $single;
            $monthTo   = $single;
        }

        [$monthFrom, $monthTo] = $this->normalizeMonthRange($monthFrom, $monthTo);

        $resume       = $this->getMutasiResumeDataByPeriod($year, $monthFrom, $monthTo);
        $periodeLabel = $this->makeMutasiPeriodeLabel($year, $monthFrom, $monthTo);

        $filename = 'resume_mutasi_' . str_replace([' ', '–'], ['_', '-'], strtolower($periodeLabel)) . '.xlsx';

        return Excel::download(
            new MutasiResumeExport($resume['data'], $resume['grandTotal'], $periodeLabel),
            $filename
        );
    }

    public function exportMutasiResumePdf(Request $request)
    {
        ini_set('memory_limit', '512M');

        $year      = $request->integer('year');
        $monthFrom = $request->integer('month_from');
        $monthTo   = $request->integer('month_to');

        if (!$monthFrom && !$monthTo) {
            $single   = $request->integer('month');
            $monthFrom = $single;
            $monthTo   = $single;
        }

        [$monthFrom, $monthTo] = $this->normalizeMonthRange($monthFrom, $monthTo);

        $detail       = $this->getMutasiDetailDataByPeriod($year, $monthFrom, $monthTo);
        $periodeLabel = $this->makeMutasiPeriodeLabel($year, $monthFrom, $monthTo);


        $viewData = [
            'periodeLabel' => $periodeLabel,
            'rows'         => $detail['items'],
            'summary'      => $detail['summary'],
        ];

        $pdf = PDF::loadView('exports.mutasi-resume-pdf', $viewData)
            ->setPaper('A4', 'landscape');

        $userPassword  = $this->getExportPassword();
        $ownerPassword = $this->getExportOwnerPassword();
        $this->encryptPdf($pdf, $userPassword, $ownerPassword, ['print']);

        $filename = 'resume_mutasi_' . str_replace([' ', '–'], ['_', '-'], strtolower($periodeLabel)) . '.pdf';
        return $pdf->download($filename);
    }

    /**
     *
     * @param \Barryvdh\DomPDF\PDF $pdf
     * @param string $userPassword   
     * @param string $ownerPassword 
     * @param array  $permissions
     */
    private function encryptPdf($pdf, string $userPassword, string $ownerPassword, array $permissions = []): void
    {
        try {
            $pdf->render();
            $dompdf = $pdf->getDomPDF();
            $canvas = $dompdf->getCanvas();

            $cpdf = null;
            foreach (['pdf', 'cpdf', '_pdf'] as $propName) {
                $ref = new \ReflectionClass($canvas);
                if ($ref->hasProperty($propName)) {
                    $prop = $ref->getProperty($propName);
                    $prop->setAccessible(true);
                    $cpdf = $prop->getValue($canvas);
                    if ($cpdf) break;
                }
            }
            if ($cpdf && method_exists($cpdf, 'setEncryption')) {
                $cpdf->setEncryption($userPassword, $ownerPassword, $permissions);
            }
        } catch (\Throwable $e) {
            //
        }
    }
    private function buildMutasiDetailQuery(?int $year, ?int $monthFrom, ?int $monthTo, string $dateField = 'tanggal_mutasi')
    {
        if ($monthFrom && $monthTo && $monthFrom > $monthTo) {
            [$monthFrom, $monthTo] = [$monthTo, $monthFrom];
        }

        $q = Mutasi::query()
            ->leftJoin('lokasis as asal', 'mutasis.lokasi_asal_id', '=', 'asal.id')
            ->leftJoin('lokasis as tujuan', 'mutasis.lokasi_mutasi_id', '=', 'tujuan.id')
            ->leftJoin('kondisis', 'mutasis.kondisi_id', '=', 'kondisis.id')
            ->leftJoin('users', 'mutasis.user_id', '=', 'users.id');

        if ($year) {
            $q->whereYear("mutasis.$dateField", $year);

            if ($monthFrom && (!$monthTo || $monthTo == $monthFrom)) {
                $q->whereMonth("mutasis.$dateField", $monthFrom);
            }

            if ($monthFrom && $monthTo && $monthTo > $monthFrom) {
                $q->whereMonth("mutasis.$dateField", '>=', $monthFrom)
                    ->whereMonth("mutasis.$dateField", '<=', $monthTo);
            }
        }

        return $q->select(
            'mutasis.id',
            'mutasis.nomor_inventaris',
            'mutasis.nama_perangkat',
            'mutasis.tipe',
            'kondisis.nama_kondisi as kondisi',
            'asal.nama_lokasi as lokasi_asal',
            'tujuan.nama_lokasi as lokasi_tujuan',
            'mutasis.tanggal_mutasi',
            'mutasis.tanggal_diterima',
            'mutasis.alasan_mutasi',
            'users.name as dicatat_oleh'
        )
            ->orderBy('mutasis.tanggal_mutasi', 'asc')
            ->orderBy('mutasis.id', 'asc');
    }

    private function getMutasiDetailDataByPeriod(?int $year, ?int $monthFrom, ?int $monthTo): array
    {
        $items = $this->buildMutasiDetailQuery($year, $monthFrom, $monthTo)->get();

        $summary = [
            'total'    => $items->count(),
            'diterima' => $items->whereNotNull('tanggal_diterima')->count(),
            'pending'  => $items->whereNull('tanggal_diterima')->count(),
            'baik'     => $items->where('kondisi', 'Baik')->count(),
            'rusak'    => $items->where('kondisi', 'Rusak')->count(),
        ];

        return compact('items', 'summary');
    }

    private function buildPeminjamanDetailQuery(?int $year, ?int $monthFrom, ?int $monthTo)
    {
        $q = Peminjaman::query()
            ->leftJoin('users as requester', 'peminjamans.requested_by_user_id', '=', 'requester.id')
            ->leftJoin('users as approver', 'peminjamans.approved_by_user_id', '=', 'approver.id');

        if ($year) {
            $q->whereYear('peminjamans.tanggal_mulai', $year);

            if ($monthFrom && (!$monthTo || $monthTo == $monthFrom)) {
                $q->whereMonth('peminjamans.tanggal_mulai', $monthFrom);
            }

            if ($monthFrom && $monthTo && $monthTo > $monthFrom) {
                $q->whereMonth('peminjamans.tanggal_mulai', '>=', $monthFrom)
                    ->whereMonth('peminjamans.tanggal_mulai', '<=', $monthTo);
            }
        }

        return $q->select(
            'peminjamans.id',
            'peminjamans.nomor_inventaris',
            'peminjamans.nama_barang',
            'peminjamans.merk',
            'peminjamans.kondisi_terakhir',
            'peminjamans.pihak_kedua_nama',
            'peminjamans.peminjam_email',
            'peminjamans.tanggal_mulai',
            'peminjamans.tanggal_selesai',
            'peminjamans.status',
            'peminjamans.alasan_pinjam',
            'approver.name as dicatat_oleh'
        )
            ->orderBy('peminjamans.tanggal_mulai', 'asc')
            ->orderBy('peminjamans.id', 'asc');
    }
    private function getPeminjamanDetailDataByPeriod(?int $year, ?int $monthFrom, ?int $monthTo): array
    {
        $items = $this->buildPeminjamanDetailQuery($year, $monthFrom, $monthTo)->get();
        return ['items' => $items];
    }

    public function exportPeminjamanResumePdf(Request $request)
    {
        ini_set('memory_limit', '512M');

        $yearInput = $request->input('year');
        if ($yearInput === 'all' || $yearInput === null || $yearInput === '') {
            $year = null;
        } else {
            $year = (int) $yearInput;
        }

        $monthFrom  = $request->integer('month_from');
        $monthTo    = $request->integer('month_to');

        $detail       = $this->getPeminjamanDetailDataByPeriod($year, $monthFrom, $monthTo);
        $periodeLabel = $this->makePeminjamanPeriodeLabel($year, $monthFrom, $monthTo);

        $viewData = [
            'periodeLabel' => $periodeLabel,
            'rows'         => $detail['items'],
        ];

        $pdf = PDF::loadView('exports.peminjaman-resume-pdf', $viewData)
            ->setPaper('A4', 'landscape');

        $userPassword  = $this->getExportPassword();
        $ownerPassword = $this->getExportOwnerPassword();
        $this->encryptPdf($pdf, $userPassword, $ownerPassword, ['print']);

        $filename = 'resume_peminjaman_' . str_replace([' ', '–'], ['_', '-'], strtolower($periodeLabel)) . '.pdf';
        return $pdf->download($filename);
    }

    private function getExportPassword(): string
    {
        try {
            $passwordFromDb = optional(Setting::where('key', 'export_password')->first())->value;


            if (!empty($passwordFromDb)) {
                return $passwordFromDb;
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengambil password ekspor dari database: ' . $e->getMessage());
        }

        return 'INV-' . now()->format('Ymd');
    }
    private function getExportOwnerPassword(): string
    {
        try {
            $passwordFromDb = optional(Setting::where('key', 'export_owner_password')->first())->value;
            if (!empty($passwordFromDb)) {
                return $passwordFromDb;
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengambil password owner dari database: ' . $e->getMessage());
        }

        return config('app.pdf_owner_password', 'owner123');
    }
    private function buildPenarikanDetailQuery(?int $year, ?int $monthFrom, ?int $monthTo)
    {
        if ($monthFrom && $monthTo && $monthFrom > $monthTo) {
            [$monthFrom, $monthTo] = [$monthTo, $monthFrom];
        }

        $q = PenarikanAlat::query()
            ->leftJoin('lokasis', 'penarikan_alats.lokasi_id', '=', 'lokasis.id')
            ->leftJoin('users', 'penarikan_alats.user_id', '=', 'users.id');

        if ($year) {
            $q->whereYear('penarikan_alats.tanggal_penarikan', $year);

            if ($monthFrom && (!$monthTo || $monthTo == $monthFrom)) {
                $q->whereMonth('penarikan_alats.tanggal_penarikan', $monthFrom);
            }

            if ($monthFrom && $monthTo && $monthTo > $monthFrom) {
                $q->whereMonth('penarikan_alats.tanggal_penarikan', '>=', $monthFrom)
                    ->whereMonth('penarikan_alats.tanggal_penarikan', '<=', $monthTo);
            }
        }

        return $q->select(
            'penarikan_alats.id',
            'penarikan_alats.nomor_inventaris',
            'penarikan_alats.nama_perangkat',
            'penarikan_alats.tipe',
            'lokasis.nama_lokasi as lokasi_snapshot',
            'penarikan_alats.tahun_pembelian',
            'penarikan_alats.tanggal_penarikan',
            'penarikan_alats.alasan_penarikan',
            'penarikan_alats.alasan_lainnya',
            'penarikan_alats.tindak_lanjut_tipe',
            'penarikan_alats.tindak_lanjut_detail',
            'users.name as dicatat_oleh',
        )
            ->orderBy('penarikan_alats.tanggal_penarikan', 'asc')
            ->orderBy('penarikan_alats.id', 'asc');
    }

    private function getPenarikanDetailDataByPeriod(?int $year, ?int $monthFrom, ?int $monthTo): array
    {
        $items = $this->buildPenarikanDetailQuery($year, $monthFrom, $monthTo)->get();

        $summary = [
            'total'   => $items->count(),
            'rusak'   => $items->filter(fn($r) => in_array('Rusak', (array) $r->alasan_penarikan ?? []))->count(),
            'tl_perb' => $items->where('tindak_lanjut_tipe', 'Perbaikan')->count(),
            'tl_baru' => $items->where('tindak_lanjut_tipe', 'Ganti Baru')->count(),
            'tl_pind' => $items->where('tindak_lanjut_tipe', 'Pindahan')->count(),
        ];

        return compact('items', 'summary');
    }

    public function exportPenarikanResumePdf(Request $request)
    {
        ini_set('memory_limit', '512M');

        $year      = $request->integer('year');
        $monthFrom = $request->integer('month_from');
        $monthTo   = $request->integer('month_to');

        if (!$monthFrom && !$monthTo) {
            $single   = $request->integer('month');
            $monthFrom = $single;
            $monthTo   = $single;
        }

        [$monthFrom, $monthTo] = $this->normalizeMonthRange($monthFrom, $monthTo);

        $detail       = $this->getPenarikanDetailDataByPeriod($year, $monthFrom, $monthTo);
        $periodeLabel = $this->makeMutasiPeriodeLabel($year, $monthFrom, $monthTo);


        $viewData = [
            'periodeLabel' => $periodeLabel,
            'rows'         => $detail['items'],
            'summary'      => $detail['summary'],
        ];

        $pdf = PDF::loadView('exports.penarikan-resume-pdf', $viewData)
            ->setPaper('A4', 'landscape');

        $this->encryptPdf(
            $pdf,
            $this->getExportPassword(),
            $this->getExportOwnerPassword(),
            ['print']
        );

        $filename = 'resume_penarikan_' . str_replace([' ', '–'], ['_', '-'], strtolower($periodeLabel)) . '.pdf';
        return $pdf->download($filename);
    }
    private function makePeminjamanPeriodeLabel(?int $year, ?int $monthFrom, ?int $monthTo): string
    {
        if (!$year && !$monthFrom && !$monthTo) {
            return 'SEMUA TAHUN';
        }

        if (!$year && ($monthFrom || $monthTo)) {
            $year = (int) now()->year;
        }

        if ($year && !$monthFrom && !$monthTo) {
            return (string) $year;
        }

        if ($year && $monthFrom && (!$monthTo || $monthTo == $monthFrom)) {
            $single = \Illuminate\Support\Carbon::create($year, $monthFrom, 1)
                ->locale('id')->translatedFormat('F Y');

            return mb_strtoupper($single);
        }

        if ($year && $monthFrom && $monthTo && $monthTo > $monthFrom) {
            $start = \Illuminate\Support\Carbon::create($year, $monthFrom, 1)
                ->locale('id')->translatedFormat('F');
            $end = \Illuminate\Support\Carbon::create($year, $monthTo, 1)
                ->locale('id')->translatedFormat('F Y');

            return mb_strtoupper($start . '–' . $end);
        }

        return (string) ($year ?? 'SEMUA TAHUN');
    }

    private function normalizeMonthRange(?int $monthFrom, ?int $monthTo): array
    {
        if ($monthFrom && $monthTo && $monthFrom > $monthTo) {
            return [$monthTo, $monthFrom];
        }

        return [$monthFrom, $monthTo];
    }

    public function exportSupervisiExcel(Request $request)
    {
        ini_set('memory_limit', '512M');

        $userId = $request->integer('user_id') ?: null;
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $password = $this->getExportPassword();

        $filename = 'supervisi_export_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new SupervisiAllExport($password, $userId, $startDate, $endDate),
            $filename
        );
    }
}
