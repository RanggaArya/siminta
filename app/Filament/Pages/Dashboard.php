<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\PerangkatStatsOverview;
use App\Filament\Widgets\RecentMaintenances;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dashboard';
    public function getWidgets(): array
    {
        $user = Auth::user();
        if ($user->role === 'user') {
            return [
                \App\Filament\Widgets\PerangkatStatsOverview::class,
                \App\Filament\Widgets\ResumePeminjamanWidget::class,
            ];
        }
        return [
            \App\Filament\Widgets\PerangkatStatsOverview::class,
            \App\Filament\Widgets\RecentMaintenances::class,
        ];
    }
    public function getColumns(): int | string |array
    {
        return 12;
    }
}
