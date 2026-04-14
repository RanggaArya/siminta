<?php

namespace App\Providers\Filament;

use Filament\View\PanelsRenderHook;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\LegacyComponents\Widget;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Illuminate\Support\Collection;
use App\Filament\Pages\AccountSettings;
use Filament\Navigation\UserMenuItem;
use App\Filament\Pages\AppSettings;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\User as AppUser;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandLogo(fn() => view('filament.brand'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('images/RSU.png'))
            ->login()
            ->userMenuItems([
                UserMenuItem::make()
                    ->label('Pengaturan Akun')
                    ->url(fn(): string => AccountSettings::getUrl())
                    ->icon('heroicon-o-cog-6-tooth'),
                UserMenuItem::make()
                    ->label('Peraturan Export')
                    ->url(fn(): string => AppSettings::getUrl())
                    ->icon('heroicon-o-cpu-chip')
                    ->visible(function (): bool {
                        $user = Auth::user();
                        if (!$user instanceof User) {
                            return false;
                        }
                        return $user->isSuperAdmin();
                    }),
            ])
            ->passwordReset()
            
            // =======================================================
            // PENGATURAN TEMA (WARNA & FONT)
            // =======================================================
            ->colors([
                'primary' => Color::Emerald,   // Warna utama (Hijau)
                'danger'  => Color::Rose,      // Warna error/hapus (Merah Pink)
                'info'    => Color::Sky,       // Warna informasi (Biru Langit)
                'success' => Color::Teal,      // Warna sukses (Hijau Kebiruan)
                'warning' => Color::Amber,     // Warna peringatan (Kuning/Oranye)
                'gray'    => Color::Slate,     // Warna abu-abu dasar yang lebih elegan
            ])
            ->font('Poppins') // Mengubah font bawaan menjadi lebih modern
            ->sidebarCollapsibleOnDesktop()
            //->topNavigation() // Hapus tanda // di awal baris ini jika ingin menu pindah ke atas
            // =======================================================

            ->icons([
                'heroicon-o-rectangle-stack',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->pages([
                \Filament\Pages\Dashboard::class,
                \App\Filament\Pages\ImportPerangkat::class,
                \App\Filament\Pages\PerangkatResume::class,
                \App\Filament\Pages\SupervisiReport::class,
                AccountSettings::class,
                AppSettings::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
            ])
            
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn (): string => '<div class="custom-topbar-logo flex items-center mr-4 ml-2" 
                    x-data 
                    x-show="!$store.sidebar.isOpen" 
                    x-init="
                        $watch(\'$store.sidebar.isOpen\', val => document.body.classList.toggle(\'sidebar-is-minimized\', !val)); 
                        $nextTick(() => document.body.classList.toggle(\'sidebar-is-minimized\', !$store.sidebar.isOpen));
                    " 
                    x-cloak>
                    ' . view('filament.brand')->render() . '
                </div>'
            )
            
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<style>
                    /* =======================================================
                       1. Kustomisasi Scrollbar agar Elegan & Modern (WebKit)
                       ======================================================= */
                    .fi-ta-content::-webkit-scrollbar {
                        width: 8px; /* Scrollbar lebih tipis */
                        height: 8px;
                    }
                    .fi-ta-content::-webkit-scrollbar-track {
                        background: #f1f5f9;
                        border-radius: 4px;
                    }
                    .fi-ta-content::-webkit-scrollbar-thumb {
                        background: #cbd5e1;
                        border-radius: 4px;
                    }
                    .fi-ta-content::-webkit-scrollbar-thumb:hover {
                        background: #10b981; /* Berubah hijau saat disorot mouse */
                    }

                    /* Scrollbar untuk Dark Mode */
                    .dark .fi-ta-content::-webkit-scrollbar-track {
                        background: #18181b;
                    }
                    .dark .fi-ta-content::-webkit-scrollbar-thumb {
                        background: #3f3f46;
                    }
                    .dark .fi-ta-content::-webkit-scrollbar-thumb:hover {
                        background: #10b981;
                    }

                    /* =======================================================
                       2. Area Scroll Tabel (Independen)
                       ======================================================= */
                    .fi-ta-content {
                        max-height: 55vh !important; 
                        overflow-y: auto !important;
                        border-radius: 8px; /* Membulatkan ujung area tabel */
                    }

                    /* =======================================================
                       3. Sticky Header dengan Efek Melayang 
                       ======================================================= */
                    .fi-ta-table thead {
                        position: sticky !important;
                        top: 0 !important;
                        z-index: 5 !important;
                    }

                    /* =======================================================
                       4. Desain Header Berwarna (Gradient) & Elegan
                       ======================================================= */
                    .fi-ta-table th {
                        /* Gradient Hijau Elegan (Sesuai tema aplikasi Anda) */
                        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
                        border-bottom: 5px solid #047857 !important; /* Garis bawah tegas */
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important; /* Efek melayang */
                        padding-top: 5px !important;
                        padding-bottom: 5px !important;
                    }

                    /* Memutihkan teks, icon sort, dan tombol di dalam header agar kontras */
                    .fi-ta-table th *,
                    .fi-ta-table th button,
                    .fi-ta-table th span,
                    .fi-ta-table th svg {
                        color: #ffffff !important;
                        font-weight: 600 !important;
                        letter-spacing: 0.5px; /* Spasi huruf agar terlihat premium */
                    }

                    /* =======================================================
                       5. Penyesuaian Header untuk Dark Mode
                       ======================================================= */
                    .dark .fi-ta-table th {
                        /* Gradient Hijau Gelap untuk Dark Mode */
                        background: linear-gradient(135deg, #065f46 0%, #022c22 100%) !important;
                        border-bottom: 3px solid #064e3b !important;
                        box-shadow: 0 4px 10px -1px rgba(0, 0, 0, 0.5) !important;
                    }

                    /* =======================================================
                       6. Warna Selang-Seling Baris Tabel (Zebra Striping)
                       ======================================================= */
                    .fi-ta-table tbody tr:nth-child(even) {
                        background-color: #e2e8f0 !important; /* Abu-abunya dinaikkan agar lebih jelas */
                    }
                    .dark .fi-ta-table tbody tr:nth-child(even) {
                        background-color: rgba(255, 255, 255, 0.04) !important; /* Sedikit diterangkan di dark mode */
                    }

                    /* =======================================================
                       7. Efek Sorot (Hover) saat Kena Kursor
                       ======================================================= */
                    .fi-ta-table tbody tr {
                        transition: background-color 0.2s ease-in-out; /* Animasi perpindahan warna yang mulus */
                    }
                    .fi-ta-table tbody tr:hover {
                        background-color: #ecfdf5 !important; /* Hijau mint sangat muda */
                    }
                    .dark .fi-ta-table tbody tr:hover {
                        background-color: rgba(16, 185, 129, 0.15) !important; /* Hijau transparan untuk dark mode */
                    }

                    /* =======================================================
                       8. BACKGROUND WEBSITE GRADASI (BODY)
                       ======================================================= */
                    /* LIGHT MODE (default) */
                    body {
                        background: linear-gradient(
                            135deg,
                            #f8fafc 0%,   /* putih soft */
                            #e2e8f0 50%,  /* abu terang */
                            #ffffff 100%
                        );
                    }
                    
                    /* DARK MODE */
                    .dark body {
                        background: linear-gradient(
                            135deg,
                            #020617 0%,   /* hitam deep */
                            #022c22 50%,  /* hijau gelap */
                            #000000 100%
                        );
                    }
                    
                    /* efek glow hanya saat dark */
                    .dark body::before {
                        content: "";
                        position: fixed;
                        inset: 0;
                        background: radial-gradient(
                            circle at 20% 30%,
                            rgba(16, 185, 129, 0.15),
                            transparent 60%
                        );
                        pointer-events: none;
                    }
                    
                    /* =======================================================
                       9. LOGO DINAMIS (TAMPIL DI SIDEBAR, PINDAH JIKA MINIMIZE)
                       ======================================================= */
                    /* Sembunyikan logo di sidebar HANYA ketika body mendapat class sidebar-is-minimized dari Alpine.js */
                    body.sidebar-is-minimized .fi-sidebar-header .fi-logo {
                        display: none !important;
                    }
                    
                    /* Sembunyikan logo custom kita di HP (karena di HP bawaan Filament sudah sangat rapi) */
                    @media (max-width: 1023px) {
                        .custom-topbar-logo {
                            display: none !important;
                        }
                    }
                    
                    /* =======================================================
                       10. WARNA ICON DI KOTAK STATISTIK
                       ======================================================= */
                    .stat-info .fi-wi-stats-overview-stat-icon { color: #0ea5e9 !important; }
                    .stat-success .fi-wi-stats-overview-stat-icon { color: #10b981 !important; }
                    .stat-warning .fi-wi-stats-overview-stat-icon { color: #f59e0b !important; }
                    .stat-danger .fi-wi-stats-overview-stat-icon { color: #f43f5e !important; }
                </style>'
            )
            
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}