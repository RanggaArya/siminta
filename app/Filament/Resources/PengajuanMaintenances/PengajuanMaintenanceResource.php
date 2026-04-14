<?php

namespace App\Filament\Resources\PengajuanMaintenances;

use App\Filament\Resources\PengajuanMaintenances\Pages\CreatePengajuanMaintenance;
use App\Filament\Resources\PengajuanMaintenances\Pages\EditPengajuanMaintenance;
use App\Filament\Resources\PengajuanMaintenances\Pages\ListPengajuanMaintenances;
use App\Filament\Resources\PengajuanMaintenances\Pages\ViewPengajuanMaintenance;
use App\Filament\Resources\PengajuanMaintenances\Schemas\PengajuanMaintenanceForm;
use App\Filament\Resources\PengajuanMaintenances\Schemas\PengajuanMaintenanceInfolist;
use App\Filament\Resources\PengajuanMaintenances\Tables\PengajuanMaintenancesTable;
use App\Models\PengajuanMaintenance;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class PengajuanMaintenanceResource extends Resource
{
    protected static ?string $model = PengajuanMaintenance::class;

    // protected static ?string $navigationIcon = 'heroicon-o-arrow-up-on-square-stack';
    // protected static ?string $navigationGroup = 'Inventaris Alat';
    // protected static ?string $modelLabel = 'Pengajuan Maintenance';
    // protected static ?string $pluralModelLabel = 'Pengajuan Maintenance';
    protected static ?string $navigationIcon = 'heroicon-o-arrows-up-down';
    protected static ?string $navigationGroup = 'Manajemen Inventaris';
    protected static ?string $navigationLabel = 'Pengajuan Maintenance';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return PengajuanMaintenanceForm::configure($form);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return PengajuanMaintenanceInfolist::configure($infolist);
    }

    public static function table(Table $table): Table
    {
        return PengajuanMaintenancesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengajuanMaintenances::route('/'),
            'create' => CreatePengajuanMaintenance::route('/create'),
            'view' => ViewPengajuanMaintenance::route('/{record}'),
            'edit' => EditPengajuanMaintenance::route('/{record}/edit'),
        ];
    }
}