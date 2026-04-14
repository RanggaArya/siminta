<x-filament-panels::page>
    {{ $this->form }}

    @php
    $fmt = fn($v) => number_format($v ?? 0, 0, ',', '.');

    $year = $this->year ?? now()->year;
    $monthFrom = $this->monthFrom;
    $monthTo = $this->monthTo;

    if ($monthFrom && $monthTo && $monthFrom > $monthTo) {
    [$monthFrom, $monthTo] = [$monthTo, $monthFrom];
    }

    if (!$year && !$monthFrom && !$monthTo) {
    $bulanTahun = mb_strtoupper(now()->locale('id')->translatedFormat('F Y'));
    }
    elseif ($year && !$monthFrom && !$monthTo) {
    $bulanTahun = (string) $year;
    }
    elseif ($year && $monthFrom && (!$monthTo || $monthTo == $monthFrom)) {
    $bulanTahun = mb_strtoupper(
    \Carbon\Carbon::createFromDate($year, $monthFrom, 1)
    ->locale('id')->translatedFormat('F Y')
    );
    }
    else {
    $start = \Carbon\Carbon::createFromDate($year, $monthFrom, 1)
    ->locale('id')->translatedFormat('F');
    $end = \Carbon\Carbon::createFromDate($year, $monthTo, 1)
    ->locale('id')->translatedFormat('F Y');

    $bulanTahun = mb_strtoupper($start . '–' . $end);
    }
    @endphp
        <div class="mt-4 flex justify-end">
        <div class="inline-flex gap-x-2">
            <x-filament::button 
                tag="a" 
                href="{{ route('export.perangkat.resume.excel', [
                    'year'       => $this->year,
                    'month_from' => $this->monthFrom,
                    'month_to'   => $this->monthTo,
                ]) }}" 
                icon="heroicon-o-document-arrow-down"
                target="_blank"
            >
                Export Excel
            </x-filament::button>

            <x-filament::button 
                tag="a" 
                href="{{ route('export.perangkat.resume.pdf', [
                    'year'       => $this->year,
                    'month_from' => $this->monthFrom,
                    'month_to'   => $this->monthTo,
                ]) }}" 
                icon="heroicon-o-document-text"
                color="primary"
                target="_blank"
            >
                Export PDF
            </x-filament::button>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        <x-filament::card>
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Perangkat ({{ $bulanTahun }})</div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $fmt($this->grandTotal['total_count']) }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Semua perangkat terdaftar</div>
        </x-filament::card>
        <x-filament::card>
            <div class="text-sm text-gray-500 dark:text-gray-400">Digunakan</div>
            <div class="mt-2 text-2xl font-bold text-success-600">{{ $fmt($this->grandTotal['aktif_count']) }} unit</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Rp {{ $fmt($this->grandTotal['aktif_sum']) }}</div>
        </x-filament::card>
        <x-filament::card>
            <div class="text-sm text-gray-500 dark:text-gray-400">Dalam Perbaikan</div>
            <div class="mt-2 text-2xl font-bold text-danger-600">{{ $fmt($this->grandTotal['rusak_count']) }} unit</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Rp {{ $fmt($this->grandTotal['rusak_sum']) }}</div>
        </x-filament::card>
        <x-filament::card>
            <div class="text-sm text-gray-500 dark:text-gray-400">Tidak Digunakan</div>
            <div class="mt-2 text-2xl font-bold text-warning-600">{{ $fmt($this->grandTotal['tidak_digunakan_count']) }} unit</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Rp {{ $fmt($this->grandTotal['tidak_digunakan_sum']) }}</div>
        </x-filament::card>
    </div>

    <x-filament::section heading="Detail Laporan Inventaris" class="mt-6">
        
        <div class="resume-table-wrapper overflow-x-auto overflow-y-auto rounded-lg" style="max-height: 65vh;">

            <table class="resume-table min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700 relative">
                <thead class="sticky top-0 z-20">
                    <tr class="text-left">
                        <th scope="col" class="sticky-column header-sticky px-4 py-4 font-medium whitespace-nowrap border-r border-emerald-700 dark:border-emerald-900 min-w-[200px]">Nama Perangkat</th>
                        
                        <th scope="col" class="px-4 py-4 font-medium whitespace-nowrap">Digunakan (Unit)</th>
                        <th scope="col" class="px-4 py-4 font-medium whitespace-nowrap">Digunakan (Rp)</th>
                        <th scope="col" class="px-4 py-4 font-medium whitespace-nowrap">Dalam Perbaikan (Unit)</th>
                        <th scope="col" class="px-4 py-4 font-medium whitespace-nowrap">Dalam Perbaikan (Rp)</th>
                        <th scope="col" class="px-4 py-4 font-medium whitespace-nowrap">Tidak Digunakan (Unit)</th>
                        <th scope="col" class="px-4 py-4 font-medium whitespace-nowrap">Tidak Digunakan (Rp)</th>
                        <th scope="col" class="px-4 py-4 font-medium whitespace-nowrap">Total (Unit)</th>
                        <th scope="col" class="px-4 py-4 font-medium whitespace-nowrap">Total (Rp)</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($this->rows as $row)
                    <tr>
                        <td class="sticky-column body-sticky px-3 py-2 whitespace-nowrap font-medium border-r border-gray-300 dark:border-gray-600">
                            {{ $row->nama_perangkat }}
                        </td>
                        
                        <td class="px-3 py-2 whitespace-nowrap text-right">{{ $fmt($row->aktif_count) }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-right">Rp {{ $fmt($row->aktif_sum) }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-right">{{ $fmt($row->rusak_count) }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-right">Rp {{ $fmt($row->rusak_sum) }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-right">{{ $fmt($row->tidak_digunakan_count) }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-right">Rp {{ $fmt($row->tidak_digunakan_sum) }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-right font-bold">{{ $fmt($row->total_count) }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-right font-bold text-emerald-600 dark:text-emerald-400">Rp {{ $fmt($row->total_sum) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400 bg-white dark:bg-zinc-900">Tidak ada data pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>

                <tfoot class="sticky bottom-0 z-20 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
                    <tr class="font-bold">
                        <td class="sticky-column footer-sticky px-3 py-3 border-r border-gray-300 dark:border-gray-600">Grand Total</td>
                        <td class="px-3 py-3 whitespace-nowrap text-right">{{ $fmt($this->grandTotal['aktif_count']) }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-right">Rp {{ $fmt($this->grandTotal['aktif_sum']) }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-right">{{ $fmt($this->grandTotal['rusak_count']) }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-right">Rp {{ $fmt($this->grandTotal['rusak_sum']) }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-right">{{ $fmt($this->grandTotal['tidak_digunakan_count']) }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-right">Rp {{ $fmt($this->grandTotal['tidak_digunakan_sum']) }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-right text-emerald-700 dark:text-emerald-400">{{ $fmt($this->grandTotal['total_count']) }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-right text-emerald-700 dark:text-emerald-400">Rp {{ $fmt($this->grandTotal['total_sum']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-filament::section>

    <style>
        /* 1. Kustomisasi Scrollbar */
        .resume-table-wrapper::-webkit-scrollbar { width: 8px; height: 8px; }
        .resume-table-wrapper::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        .resume-table-wrapper::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .resume-table-wrapper::-webkit-scrollbar-thumb:hover { background: #10b981; }
        .dark .resume-table-wrapper::-webkit-scrollbar-track { background: #18181b; }
        .dark .resume-table-wrapper::-webkit-scrollbar-thumb { background: #3f3f46; }
        .dark .resume-table-wrapper::-webkit-scrollbar-thumb:hover { background: #10b981; }

        /* 2. Header Berwarna Gradient */
        .resume-table th {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            border-bottom: 5px solid #047857 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
            color: #ffffff !important;
            letter-spacing: 0.5px;
        }
        .dark .resume-table th {
            background: linear-gradient(135deg, #065f46 0%, #022c22 100%) !important;
            border-bottom: 3px solid #064e3b !important;
        }

        /* 3. Warna Body & Selang-Seling (Solid agar tidak tembus pandang) */
        .resume-table tbody tr td { background-color: #ffffff; transition: background-color 0.2s ease; color: #020617; }
        .resume-table tbody tr:nth-child(even) td { background-color: #e2e8f0; }
        .resume-table tbody tr:hover td { background-color: #ecfdf5; }
        
        .dark .resume-table tbody tr td { background-color: #18181b; color: #ffffff; }
        .dark .resume-table tbody tr:nth-child(even) td { background-color: #222225; }
        .dark .resume-table tbody tr:hover td { background-color: #163326; }

        /* 4. Warna Footer Solid */
        .resume-table tfoot td { background-color: #f8fafc; color: #020617; border-top: 2px solid #cbd5e1; }
        .dark .resume-table tfoot td { background-color: #09090b; color: #ffffff; border-top: 2px solid #3f3f46; }

        /* 5. CSS KHUSUS UNTUK FREEZE KOLOM KIRI */
        .sticky-column {
            position: sticky !important;
            left: 0 !important;
        }
        
        /* Z-Index Hierarchy untuk kolom yang di-freeze */
        .header-sticky { z-index: 40 !important; }
        .body-sticky { z-index: 10 !important; }
        .footer-sticky { z-index: 30 !important; }

        /* Memastikan warna kolom kiri selalu menutupi teks di bawahnya saat di-scroll */
        .resume-table tbody tr .body-sticky { background-color: inherit; } 
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const slider = document.querySelector('.resume-table-wrapper');
            if (!slider) return;

            let isDown = false;
            let startX;
            let scrollLeft;
            let isDragging = false;

            slider.addEventListener('mousedown', (e) => {
                // Abaikan jika yang diklik adalah filter atau tombol
                if (e.target.closest("button, a, input, select")) return;
                
                isDown = true;
                isDragging = false;
                startX = e.pageX - slider.offsetLeft;
                scrollLeft = slider.scrollLeft;
            });

            slider.addEventListener('mouseleave', () => { isDown = false; });
            slider.addEventListener('mouseup', () => { isDown = false; });

            slider.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                
                const x = e.pageX - slider.offsetLeft;
                const walk = (x - startX);
                
                if (Math.abs(walk) > 3) {
                    isDragging = true;
                    e.preventDefault(); // Mencegah teks terblok warna biru
                }
                
                if (isDragging) {
                    slider.scrollLeft = scrollLeft - (walk * 1.5);
                }
            });
        });
    </script>

</x-filament-panels::page>