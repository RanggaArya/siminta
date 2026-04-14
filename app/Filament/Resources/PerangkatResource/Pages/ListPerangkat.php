<?php

namespace App\Filament\Resources\PerangkatResource\Pages;

use App\Filament\Resources\PerangkatResource;
use App\Models\Perangkat;
use App\Models\JenisPerangkat;
use App\Models\Lokasi;
use App\Models\Status;
use App\Models\Kondisi;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use BladeUI\Icons\Components\Icon;
use App\Filament\Pages\ImportPerangkat;
use Filament\Actions\ActionGroup;

class ListPerangkat extends ListRecords
{
    protected static string $resource = PerangkatResource::class;
    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $canManage = $user instanceof User && $user->isAdminOrSuper();
        return [
            ActionGroup::make([
                Action::make('export_excel')
                    ->label('Download Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(route('export.perangkat.resume.excel'), shouldOpenInNewTab: true)
                    ->color('success'),
                Action::make('export_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(route('export.perangkat.resume.pdf'), shouldOpenInNewTab: true)
                    ->color('danger'),
            ])
                ->label('Download Resume')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->button()
                ->visible($canManage),
            Actions\CreateAction::make()
                ->label('Tambah Perangkat')
                ->Icon('heroicon-o-plus')
                ->visible(function () {
                    $user = Auth::user();
                    return $user instanceof User && $user->isAdminOrSuper();
                }),
            Actions\Action::make('importPreview')
                ->label('Import (Preview)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->visible(function () {
                    $user = Auth::user();
                    return $user instanceof User && $user->isAdminOrSuper();
                })
                ->url(ImportPerangkat::getUrl()),
        ];
    }
    public function setNomorInventarisAttribute($value): void
    {
        $trim = trim((string) $value);
        if ($value === null || $trim === '') {
            $this->attributes['nomor_inventaris'] = null;
            return;
        }
        $this->attributes['nomor_inventaris'] = strtoupper($trim);
    }
}
