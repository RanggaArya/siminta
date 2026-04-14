<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Perangkat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;

class PerangkatStatsOverview extends BaseWidget
{
    protected ?string $heading = 'Ringkasan Inventaris SIMINTA';
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 12;
    
    // WAJIB STATIC UNTUK SIMINTA
    protected static ?string $pollingInterval = '15s';

    // WAJIB: Memaksa layout menjadi 4 kolom sejajar agar tidak melar
    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        return Cache::remember('stats.perangkat.siminta.premium.animated', 300, function () {
            
            // Pengambilan Data (Sesuai query bawaan SIMINTA)
            $totalPerangkat   = Perangkat::count();
            $aktif            = Perangkat::whereHas('status', fn($q) => $q->where('nama_status', 'Digunakan'))->count();
            $rusak            = Perangkat::whereHas('status', fn($q) => $q->where('nama_status', 'Dalam Perbaikan'))->count();
            $tidakDigunakan   = Perangkat::whereHas('status', fn($q) => $q->where('nama_status', 'Tidak Digunakan'))->count();

            return [
                // STAT 1: TOTAL (INFO / BLUE TEAL)
                Stat::make(new HtmlString('<span class="text-premium">Total Perangkat</span>'), number_format($totalPerangkat))
                    ->description('Semua perangkat terdaftar')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->chart([10, 15, 12, 25, 18, 30, 25])
                    ->color('info')
                    ->icon('heroicon-o-computer-desktop')
                    ->extraAttributes([
                        'class' => 'stat-premium stat-info',
                        'style' => 'background: rgba(14, 165, 233, 0.08); border-bottom: 4px solid #0ea5e9; backdrop-filter: blur(12px); border-radius: 16px; animation-delay: 0.1s;'
                    ]),

                // STAT 2: AKTIF (SUCCESS / EMERALD GREEN)
                Stat::make(new HtmlString('<span class="text-premium">Perangkat Aktif</span>'), number_format($aktif))
                    ->description('Status = Digunakan')
                    ->descriptionIcon('heroicon-m-check-badge')
                    ->chart([5, 10, 8, 15, 12, 18, 20])
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->extraAttributes([
                        'class' => 'stat-premium stat-success',
                        'style' => 'background: rgba(16, 185, 129, 0.08); border-bottom: 4px solid #10b981; backdrop-filter: blur(12px); border-radius: 16px; animation-delay: 0.2s;'
                    ]),

                // STAT 3: RUSAK (WARNING / AMBER ORANGE)
                Stat::make(new HtmlString('<span class="text-premium">Dalam Perbaikan</span>'), number_format($rusak))
                    ->description('Status = Dalam Perbaikan')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->chart([2, 8, 12, 7, 5, 3, 1])
                    ->color('warning')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->extraAttributes([
                        'class' => 'stat-premium stat-warning',
                        'style' => 'background: rgba(245, 158, 11, 0.08); border-bottom: 4px solid #f59e0b; backdrop-filter: blur(12px); border-radius: 16px; animation-delay: 0.3s;'
                    ]),

                // STAT 4: NON-AKTIF (DANGER / ROSE RED) + INJEKSI CSS ANIMASI DEWA
                Stat::make(new HtmlString('<span class="text-premium">Tidak Digunakan</span>'), number_format($tidakDigunakan))
                    ->description(new HtmlString('Status = Tidak Digunakan
                        <style>
                            /* 1. ANIMASI MUNCUL BERGANTIAN (FADE UP) */
                            .stat-premium { 
                                opacity: 0;
                                animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                                position: relative;
                                overflow: hidden;
                                transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease, border-color 0.3s ease !important; 
                                cursor: pointer; 
                                border: 1px solid rgba(255, 255, 255, 0.1) !important; 
                            }
                            
                            @keyframes slideUpFade {
                                0% { opacity: 0; transform: translateY(40px); }
                                100% { opacity: 1; transform: translateY(0); }
                            }

                            /* 2. EFEK KILAPAN KACA (SWEEPING SHINE) */
                            .stat-premium::before {
                                content: "";
                                position: absolute;
                                top: 0;
                                left: -150%;
                                width: 50%;
                                height: 100%;
                                background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0) 100%);
                                transform: skewX(-25deg);
                                animation: shineSweep 5s infinite;
                                pointer-events: none;
                                z-index: 1;
                            }

                            @keyframes shineSweep {
                                0% { left: -150%; }
                                20% { left: 200%; }
                                100% { left: 200%; }
                            }

                            /* Hover Saat Disorot (Melompat & Shadow Putih Terang) */
                            .stat-premium:hover { 
                                transform: translateY(-8px) scale(1.02) !important; 
                                border-color: rgba(255, 255, 255, 0.6) !important;
                                box-shadow: 0 15px 35px rgba(255, 255, 255, 0.25), 0 0 20px rgba(255, 255, 255, 0.1) !important; 
                                z-index: 10;
                            }
                            
                            /* 3. IKON BERDENYUT & BERCAHAYA (BREATHING GLOW) */
                            .stat-premium svg {
                                animation: breatheGlow 2.5s infinite alternate ease-in-out;
                                position: relative;
                                z-index: 2;
                            }

                            /* Pewarnaan Glow Ikon Khusus SIMINTA */
                            .stat-info svg    { color: #0ea5e9 !important; filter: drop-shadow(0 0 5px rgba(14,165,233,0.6)); }
                            .stat-success svg { color: #10b981 !important; filter: drop-shadow(0 0 5px rgba(16,185,129,0.6)); }
                            .stat-warning svg { color: #f59e0b !important; filter: drop-shadow(0 0 5px rgba(245,158,11,0.6)); }
                            .stat-danger svg  { color: #f43f5e !important; filter: drop-shadow(0 0 5px rgba(244,63,94,0.6)); }

                            @keyframes breatheGlow {
                                0% { transform: scale(1); filter: drop-shadow(0 0 2px currentColor); }
                                100% { transform: scale(1.15); filter: drop-shadow(0 0 12px currentColor); }
                            }

                            /* Mode Gelap (Dark Mode) */
                            .dark .stat-premium { border: 1px solid rgba(255, 255, 255, 0.05) !important; }
                            .dark .stat-premium:hover { box-shadow: 0 15px 40px rgba(255, 255, 255, 0.25), 0 0 25px rgba(255, 255, 255, 0.15) !important; }
                            .dark .stat-info    { background: rgba(14, 165, 233, 0.15) !important; }
                            .dark .stat-success { background: rgba(16, 185, 129, 0.15) !important; }
                            .dark .stat-warning { background: rgba(245, 158, 11, 0.15) !important; }
                            .dark .stat-danger  { background: rgba(244, 63, 94, 0.15) !important; }
                            
                            .dark .stat-premium::before {
                                background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0) 100%);
                            }
                            
                            .text-premium { font-weight: 800 !important; letter-spacing: 0.3px; }
                        </style>
                    '))
                    ->descriptionIcon('heroicon-m-x-circle')
                    ->chart([2, 4, 3, 5, 4, 6, 8])
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->extraAttributes([
                        'class' => 'stat-premium stat-danger',
                        'style' => 'background: rgba(244, 63, 94, 0.08); border-bottom: 4px solid #f43f5e; backdrop-filter: blur(12px); border-radius: 16px; animation-delay: 0.4s;'
                    ]),
            ];
        });
    }
}