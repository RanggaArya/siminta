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
                       1. Kustomisasi Scrollbar agar Elegan & Modern
                       ======================================================= */
                    .fi-ta-content::-webkit-scrollbar { width: 8px; height: 8px; }
                    .fi-ta-content::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
                    .fi-ta-content::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
                    .fi-ta-content::-webkit-scrollbar-thumb:hover { background: #10b981; }

                    .dark .fi-ta-content::-webkit-scrollbar-track { background: #18181b; }
                    .dark .fi-ta-content::-webkit-scrollbar-thumb { background: #3f3f46; }
                    .dark .fi-ta-content::-webkit-scrollbar-thumb:hover { background: #10b981; }

                    /* =======================================================
                       2. Area Scroll Tabel (Independen)
                       ======================================================= */
                    .fi-ta-content {
                        max-height: 55vh !important; 
                        overflow-y: auto !important;
                        overflow-x: auto !important;
                        border-radius: 8px;
                    }

                    /* =======================================================
                       3. Sticky Header
                       ======================================================= */
                    .fi-ta-table thead {
                        position: sticky !important;
                        top: 0 !important;
                        z-index: 15 !important;
                    }

                    .fi-ta-table th {
                        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
                        border-bottom: 5px solid #047857 !important;
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
                        padding-top: 5px !important;
                        padding-bottom: 5px !important;
                    }

                    .fi-ta-table th *, .fi-ta-table th button, .fi-ta-table th span, .fi-ta-table th svg {
                        color: #ffffff !important; font-weight: 600 !important; letter-spacing: 0.5px;
                    }

                    .dark .fi-ta-table th {
                        background: linear-gradient(135deg, #065f46 0%, #022c22 100%) !important;
                        border-bottom: 3px solid #064e3b !important;
                        box-shadow: 0 4px 10px -1px rgba(0, 0, 0, 0.5) !important;
                    }

                    /* =======================================================
                       4. WARNA TABEL SESUAI REQUEST (TAPI ANTI TEMBUS PANDANG)
                       ======================================================= */
                    .fi-ta-table tbody tr { background-color: transparent !important; }
                    
                    /* Light Mode */
                    .fi-ta-table tbody tr td { background-color: #ffffff !important; transition: background-color 0.2s ease-in-out; }
                    .fi-ta-table tbody tr:nth-child(even) td { background-color: #e2e8f0 !important; } /* Sesuai kode Anda */
                    .fi-ta-table tbody tr:hover td { background-color: #ecfdf5 !important; } /* Sesuai kode Anda */

                    /* Dark Mode */
                    .dark .fi-ta-table tbody tr td { background-color: #18181b !important; transition: background-color 0.2s ease-in-out; }
                    .dark .fi-ta-table tbody tr:nth-child(even) td { background-color: #222225 !important; } 
                    .dark .fi-ta-table tbody tr:hover td { background-color: #163326 !important; } 

                    /* =======================================================
                       5. Background Website
                       ======================================================= */
                    body {
                        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #ffffff 100%);
                        background-attachment: fixed !important;
                    }
                    .dark body {
                        background: linear-gradient(135deg, #020617 0%, #022c22 50%, #000000 100%);
                        background-attachment: fixed !important;
                    }
                    
                    .dark body::before {
                        content: ""; position: fixed; inset: 0; pointer-events: none;
                        background: radial-gradient(circle at 20% 30%, rgba(16, 185, 129, 0.15), transparent 60%);
                    }

                    body.sidebar-is-minimized .fi-sidebar-header .fi-logo { display: none !important; }
                    @media (max-width: 1023px) { .custom-topbar-logo { display: none !important; } }
                    
                    .stat-info .fi-wi-stats-overview-stat-icon { color: #0ea5e9 !important; }
                    .stat-success .fi-wi-stats-overview-stat-icon { color: #10b981 !important; }
                    .stat-warning .fi-wi-stats-overview-stat-icon { color: #f59e0b !important; }
                    .stat-danger .fi-wi-stats-overview-stat-icon { color: #f43f5e !important; }
                    
                    /* =======================================================
                       11. FIX POPUP DROPDOWN (Z-INDEX HIERARCHY)
                       ======================================================= */
                    /* HANYA naikkan z-index untuk dropdown agar tidak tertutup header tabel */
                    .fi-dropdown-panel,
                    .choices__list--dropdown {
                        z-index: 9999 !important;
                    }
                </style>'
            )

            // =======================================================
            // HOOK: JAVASCRIPT DRAG-TO-SCROLL & FREEZE KOLOM (VERSI SEMPURNA)
            // =======================================================
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => '<script>
                    document.addEventListener("DOMContentLoaded", () => {
                        let isDown = false;
                        let isDragging = false; // Detektor khusus untuk membedakan klik vs geser
                        let startX;
                        let scrollLeft;
                        let slider = null;

                        // 1. KETIKA MOUSE DITEKAN
                        document.addEventListener("mousedown", (e) => {
                            // Abaikan HANYA jika yang diklik adalah input form murni (seperti checkbox)
                            if (e.target.closest("input, select, textarea")) return;
                            
                            slider = e.target.closest(".fi-ta-content");
                            if (!slider) return;

                            isDown = true;
                            isDragging = false; // Reset status drag
                            startX = e.pageX - slider.offsetLeft;
                            scrollLeft = slider.scrollLeft;
                        });

                        // 2. KETIKA MOUSE DILEPAS ATAU KELUAR AREA
                        document.addEventListener("mouseup", () => { isDown = false; });
                        document.addEventListener("mouseleave", () => { isDown = false; });

                        // 3. KETIKA MOUSE DIGESER
                        document.addEventListener("mousemove", (e) => {
                            if (!isDown || !slider) return;
                            
                            const x = e.pageX - slider.offsetLeft;
                            const walk = (x - startX); // Hitung jarak geser
                            
                            // Jika mouse digeser lebih dari 3 pixel, anggap ini DRAG, bukan sekadar KLIK
                            if (Math.abs(walk) > 3) {
                                isDragging = true;
                                e.preventDefault(); // Mencegah teks ter-blok warna biru
                            }
                            
                            // Eksekusi pergerakan tabel
                            if (isDragging) {
                                slider.scrollLeft = scrollLeft - (walk * 1.5);
                            }
                        });

                        // 4. MENCEGAH FILAMENT MEMBUKA HALAMAN EDIT JIKA SEDANG DRAG
                        document.addEventListener("click", (e) => {
                            // Jika statusnya sedang "isDragging", hentikan aksi klik!
                            if (isDragging && slider && slider.contains(e.target)) {
                                e.preventDefault();
                                e.stopPropagation();
                            }
                        }, true); // Menggunakan "Capture Phase" agar dicegat sebelum Filament menyadarinya

                        // 5. MENCEGAH BROWSER MENYERET LINK ("GHOST DRAG")
                        document.addEventListener("dragstart", (e) => {
                            if (slider && slider.contains(e.target)) {
                                e.preventDefault();
                            }
                        });

                        // 6. FITUR AUTO FREEZE 3 KOLOM KIRI (HANYA UNTUK LAPTOP/PC)
                        function applyStickyColumns() {
                            const isDesktop = window.innerWidth >= 1024; // Cek apakah layar Lebar (PC/Laptop)

                            document.querySelectorAll(".fi-ta-table").forEach(table => {
                                const headerCells = table.querySelectorAll("thead tr:first-child th");
                                if (headerCells.length < 4) return;

                                let offsets = [];
                                let currentLeft = 0;
                                
                                for (let i = 0; i < 3; i++) {
                                    offsets.push(currentLeft);
                                    if(isDesktop) {
                                        currentLeft += headerCells[i].getBoundingClientRect().width;
                                    }
                                }

                                table.querySelectorAll("tr").forEach(row => {
                                    const cells = row.querySelectorAll("th, td");
                                    for (let i = 0; i < 3; i++) {
                                        if (cells[i]) {
                                            if (isDesktop) {
                                                // Jika di Laptop/PC: Aktifkan Freeze
                                                cells[i].style.setProperty("position", "sticky", "important");
                                                cells[i].style.setProperty("left", offsets[i] + "px", "important");
                                                cells[i].style.setProperty("z-index", cells[i].tagName === "TH" ? "50" : "10", "important");
                                                
                                                if (i === 2) {
                                                    let borderColor = document.documentElement.classList.contains("dark") ? "1px solid #3f3f46" : "1px solid #cbd5e1";
                                                    cells[i].style.setProperty("border-right", borderColor, "important");
                                                }
                                            } else {
                                                // Jika di HP/Tablet: Lepaskan Freeze
                                                cells[i].style.removeProperty("position");
                                                cells[i].style.removeProperty("left");
                                                cells[i].style.removeProperty("z-index");
                                                
                                                if (i === 2) {
                                                    cells[i].style.removeProperty("border-right");
                                                }
                                            }
                                        }
                                    }
                                });
                            });
                        }

                        // Jalankan fungsi saat load
                        setTimeout(applyStickyColumns, 500);

                        // Eksekusi ulang setiap kali layar direnggangkan atau diputar orientasinya
                        window.addEventListener("resize", () => {
                            setTimeout(applyStickyColumns, 100); 
                        });

                        const mainContent = document.querySelector("main");
                        if (mainContent) {
                            const observer = new MutationObserver((mutations) => {
                                let shouldUpdate = false;
                                mutations.forEach(m => { 
                                    if (m.addedNodes.length > 0) shouldUpdate = true; 
                                });
                                if (shouldUpdate) setTimeout(applyStickyColumns, 100);
                            });
                            observer.observe(mainContent, { childList: true, subtree: true });
                        }
                    });
                </script>'
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