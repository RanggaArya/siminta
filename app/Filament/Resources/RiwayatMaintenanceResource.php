<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiwayatMaintenanceResource\Pages;
use App\Models\RiwayatMaintenance;
use App\Models\Perangkat;
use App\Models\MaintenanceType;
use App\Models\Komponen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\User as AppUser;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Repeater;


class RiwayatMaintenanceResource extends Resource
{
    protected static ?string $model = RiwayatMaintenance::class;
    protected static ?string $navigationIcon = 'heroicon-c-cog';
    protected static ?string $navigationGroup = 'Manajemen Inventaris';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('perangkat_id')
                ->label('Perangkat (Kode Inv)')
                ->relationship('perangkat', 'nomor_inventaris')
                ->required()
                ->searchable()
                ->getSearchResultsUsing(function (string $query) {
                    $q = Perangkat::query()
                        ->select(['id', 'nomor_inventaris'])
                        ->orderBy('nomor_inventaris');

                    if (filled($query)) {
                        $q->where('nomor_inventaris', 'like', "%{$query}%");
                    }
                    return $q->limit(50)->pluck('nomor_inventaris', 'id')->toArray();
                })
                ->getOptionLabelUsing(fn($value) => Perangkat::find($value)?->nama_perangkat)
                ->default(fn() => request()->query('perangkat_id'))
                ->disabled(fn() => request()->query('perangkat_id') !== null)
                ->dehydrated()
                ->live()
                ->reactive()
                ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                    if (blank($state)) {
                        $set('lokasi_id', null);
                        return;
                    }

                    $perangkat = Perangkat::select(['id', 'lokasi_id'])->find($state);
                    if ($perangkat) {
                        $set('lokasi_id', $perangkat->lokasi_id);
                    }
                }),

            TextInput::make('user_id')
                ->hidden()
                ->dehydrated()
                ->default(fn() => Auth::id()),

            DatePicker::make('tanggal_maintenance')
                ->label('Tanggal Maintenance')
                ->required()
                ->native(false),

            Select::make('lokasi_id')
                ->label('Lokasi Ruangan')
                ->relationship('lokasi', 'nama_lokasi')
                ->searchable()
                ->preload()
                ->disabled()
                ->dehydrated()
                ->default(fn() => request()->query('lokasi_id')),

            TextInput::make('nama_pemilik')
                ->label('Nama Pemilik/Pengguna Alat')
                ->maxLength(150)
                ->nullable(),

            Select::make('maintenanceTypes')
                ->label('Jenis Maintenance')
                ->relationship('maintenanceTypes', 'nama')
                ->multiple()
                ->preload()
                ->required()
                ->createOptionForm([
                    TextInput::make('nama')
                        ->label('Nama Jenis')
                        ->required()
                        ->unique(MaintenanceType::class, 'nama'),
                ])
                ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                    $user = Auth::user();
                    $can = $user instanceof AppUser && $user->canDo('maintenance.create');
                    $action->visible($can);
                    $action->modalHeading('Tambah Jenis Maintenance');
                }),
            Select::make('status_akhir')
                ->label('Status Akhir')
                ->options([
                    'berfungsi' => 'Berfungsi',
                    'berfungsi_sebagian' => 'Berfungsi Sebagian',
                    'tidak_berfungsi' => 'Tidak Berfungsi',
                ])
                ->nullable(),
            Repeater::make('komponenDetails')
                ->label('Komponen Dicek/Diganti')
                ->relationship('komponenDetails')
                ->defaultItems(1)
                ->minItems(0)
                ->reorderable(false)
                ->columnSpan('full')
                ->schema([
                    Select::make('komponen_id')
                        ->label('Komponen')
                        ->relationship('komponen', 'nama')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            TextInput::make('nama')
                                ->label('Nama Komponen')
                                ->required()
                                ->unique(Komponen::class, 'nama'),
                        ])
                        ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                            $user = Auth::user();
                            $can = $user instanceof AppUser && $user->canDo('maintenance.create');
                            $action->visible($can);
                            $action->modalHeading('Tambah Komponen');
                        }),

                    Select::make('aksi')
                        ->label('Aksi')
                        ->options([
                            'dicek'   => 'Dicek',
                            'diganti' => 'Diganti',
                        ])
                        ->required(),

                    Textarea::make('keterangan')
                        ->label('Keterangan / Hasil Cek')
                        ->rows(2)
                        ->columnSpan('full')
                        ->nullable(),
                ])
                ->columns([
                    'default' => 1,
                    'md' => 3,
                ]),

            Textarea::make('deskripsi')
                ->label('Deskripsi Pekerjaan')
                ->required()
                ->columnSpan('full'),

            Textarea::make('catatan')
                ->label('Catatan Tambahan')
                ->nullable()
                ->columnSpan('full'),

            FileUpload::make('foto')
                ->label('Foto (opsional)')
                ->disk('public')
                ->visibility('public')
                ->multiple()
                ->image()
                ->imageEditor()
                ->downloadable()
                ->reorderable()
                ->directory('maintenance-photos')
                ->nullable()
                ->columnSpan('full'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('perangkat.nomor_inventaris')
                    ->searchable()
                    ->label('Nomor Inventaris'),

                TextColumn::make('perangkat.nama_perangkat')
                    ->label('Perangkat'),

                TextColumn::make('lokasi.nama_lokasi')
                    ->label('Ruangan')
                    ->toggleable(),

                TextColumn::make('nama_pemilik')
                    ->label('Pemilik/Pengguna')
                    ->toggleable(),

                TextColumn::make('maintenanceTypes.nama')
                    ->label('Jenis')
                    ->badge()
                    ->separator(', '),

                TextColumn::make('komponen_summary')
                    ->label('Komponen → Aksi (Keterangan)')
                    ->wrap()
                    ->limit(120)
                    ->getStateUsing(function (RiwayatMaintenance $record) {
                        $record->loadMissing('komponenDetails.komponen');

                        return $record->komponenDetails
                            ->map(function ($row) {
                                $nama = $row->komponen?->nama ?? '-';
                                $aksi = $row->aksi ?: '-';
                                $ket  = trim((string) $row->keterangan);
                                return $ket ? "{$nama} → {$aksi} ({$ket})" : "{$nama} → {$aksi}";
                            })
                            ->join('; ');
                    }),

                TextColumn::make('status_akhir')
                    ->label('Status Akhir')
                    ->badge()
                    ->formatStateUsing(fn($state) => str_replace('_', ' ', ucfirst($state)))
                    ->color(fn($state) => match ($state) {
                        'berfungsi' => 'success',
                        'berfungsi_sebagian' => 'warning',
                        'tidak_berfungsi' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('deskripsi')
                    ->label('Deskripsi Pekerjaan')
                    ->limit(50),

                TextColumn::make('tanggal_maintenance')
                    ->label('Tanggal Maintenance')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Ditambahkan Oleh')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('perangkat_id')
                    ->label('Perangkat')
                    ->relationship('perangkat', 'nama_perangkat')
                    ->multiple(),

                SelectFilter::make('lokasi_id')
                    ->label('Ruangan')
                    ->relationship('lokasi', 'nama_lokasi')
                    ->multiple(),

                SelectFilter::make('status_akhir')
                    ->label('Status')
                    ->options([
                        'berfungsi' => 'Berfungsi',
                        'berfungsi_sebagian' => 'Berfungsi Sebagian',
                        'tidak_berfungsi' => 'Tidak Berfungsi',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'perangkat:id,nama_perangkat,nomor_inventaris',
                'lokasi:id,nama_lokasi',
                'user:id,name',
                'maintenanceTypes:id,nama',
                'komponenDetails.komponen:id,nama',
            ])

            ->select([
                'id',
                'perangkat_id',
                'user_id',
                'lokasi_id',
                'nama_pemilik',
                'status_akhir',
                'catatan',
                'foto',
                'deskripsi',
                'harga',
                'tanggal_maintenance',
                'created_at',
                'updated_at',
            ])
            ->latest('tanggal_maintenance');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRiwayatMaintenances::route('/'),
            'create' => Pages\CreateRiwayatMaintenance::route('/create'),
            'edit'   => Pages\EditRiwayatMaintenance::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('maintenance.view');
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('maintenance.create');
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('maintenance.edit');
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('maintenance.delete');
    }

    public static function canDeleteAny(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('maintenance.delete');
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('maintenance.view');
    }
}
