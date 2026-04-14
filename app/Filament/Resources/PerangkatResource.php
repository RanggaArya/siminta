<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PerangkatResource\Pages;
use App\Filament\Resources\PerangkatResource\RelationManagers;
use App\Models\Perangkat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\User as AppUser;
use Illuminate\Validation\Rule;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Forms\Components\Actions\Action as FormAction;
use App\Models\Kategori;
use Filament\Tables\Columns\BadgeColumn;

class PerangkatResource extends Resource
{
    protected static ?string $model = Perangkat::class;
    protected static ?string $navigationIcon = 'heroicon-s-computer-desktop';
    protected static ?string $navigationGroup = 'Manajemen Inventaris';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Perangkat IT';
    protected static ?string $modelLabel = 'Perangkat IT';
    protected static ?string $pluralModelLabel = 'Perangkat IT';
    protected static ?string $slug = 'perangkat';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Data Perangkat')->tabs([
                    Tabs\Tab::make('Informasi Utama')
                        ->schema([
                            TextInput::make('nama_perangkat')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan('full'),
                            TextInput::make('nomor_inventaris')
                                ->label('Nomor Inventaris')
                                ->maxLength(255)
                                ->disabled()
                                ->dehydrated()
                                ->placeholder(
                                    fn(string $operation) =>
                                    $operation === 'create' ? 'Akan digenerate otomatis...' : null
                                ),
                            TextInput::make('kode')
                                ->maxLength(255),
                            Select::make('jenis_id')
                                ->label('Jenis Perangkat')
                                ->relationship('jenis', 'nama_jenis')
                                ->searchable()
                                ->required()
                                // ->preload()
                                ->createOptionForm([
                                    TextInput::make('nama_jenis')
                                        ->label('Nama Jenis')
                                        ->required()
                                        ->rule(fn() => Rule::unique('jenis_perangkats', 'nama_jenis')),
                                    TextInput::make('prefix')
                                        ->label('Prefix (1 huruf)')
                                        ->default('B')
                                        ->maxLength(1)
                                        ->required(),
                                    TextInput::make('kode_jenis')
                                        ->label('Kode Jenis (mis. 02.4)')
                                        ->required(),
                                ])
                                ->createOptionAction(function (FormAction $action) {
                                    $auth = Auth::user();
                                    $canManage = $auth instanceof AppUser
                                        && $auth->canDo('perangkat.jenis.manage');
                                    $action->visible($canManage);
                                    $action->modalHeading('Tambah Jenis');
                                }),

                            Select::make('kategori_id')
                                ->label('Kategori (Kode Perangkat)')
                                ->relationship('kategori', 'nama_kategori')
                                ->searchable()
                                ->required()
                                ->helperText('Contoh: CPU (005), Monitor (019), dll.')
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('nama_kategori')
                                        ->label('Nama Kategori')
                                        ->required()
                                        ->unique('kategoris', 'nama_kategori'),
                                    Forms\Components\TextInput::make('kode_kategori')
                                        ->label('Kode Kategori (3 digit)')
                                        ->required()
                                        ->mask('999')
                                        ->unique('kategoris', 'kode_kategori'),
                                ]),
                            TextInput::make('tipe')
                                ->label('Tipe / Merek')
                                ->maxLength(255),
                        ])->columns(2),

                    Tabs\Tab::make('Detail & Pengadaan')
                        ->schema([
                            Section::make('Spesifikasi & Deskripsi')
                                ->schema([
                                    Textarea::make('spesifikasi')
                                        ->rows(5)
                                        ->columnSpan('full'),
                                    Textarea::make('deskripsi')
                                        ->rows(3)
                                        ->columnSpan('full'),
                                ])->columns(1),

                            Section::make('Info Pengadaan')
                                ->schema([
                                    Select::make('perolehan')
                                        ->options([
                                            'Pembelian' => 'Pembelian',
                                            'Hibah' => 'Hibah',
                                            'Sewa' => 'Sewa',
                                        ]),
                                    TextInput::make('tahun_pengadaan')
                                        ->label('Tahun Pengadaan')
                                        ->numeric()
                                        ->default(now()->year)
                                        ->minValue(1990)
                                        ->maxValue(now()->year + 1)
                                        ->mask('9999'),
                                    TextInput::make('harga')
                                        ->label('Harga')
                                        ->helperText('Masukkan angka saja, otomatis diformat.')
                                        ->required(false)
                                        ->numeric()
                                        ->minValue(0)
                                        ->rule('integer')
                                        ->prefix('Rp')
                                        ->dehydrateStateUsing(
                                            fn($state) => $state === null
                                                ? null
                                                : max(0, (int) preg_replace('/\D+/', '', (string) $state))
                                        )
                                        ->validationAttribute('Harga'),
                                    DatePicker::make('tanggal_distribusi')
                                        ->label('Tanggal Distribusi'),
                                ])->columns(2),
                        ]),

                    Tabs\Tab::make('Status & Lokasi')
                        ->schema([
                            Select::make('lokasi_id')
                                ->label('Lokasi')
                                ->relationship('lokasi', 'nama_lokasi')
                                ->searchable()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('nama_lokasi')->required()->unique(),
                                ])
                                ->required(),
                            Select::make('status_id')
                                ->label('Status')
                                ->relationship('status', 'nama_status')
                                ->searchable()
                                ->required()
                                ->createOptionForm([
                                    TextInput::make('nama_status')
                                        ->label('Nama Status')
                                        ->required()
                                        ->rule(fn() => Rule::unique('statuses', 'nama_status')),
                                ])
                                ->createOptionAction(function (FormAction $action) {
                                    $auth = Auth::user();
                                    $canManage = $auth instanceof AppUser
                                        && $auth->canDo('perangkat.status.manage');
                                    $action->visible($canManage);
                                    $action->modalHeading('Tambah Status');
                                }),

                            Select::make('kondisi_id')
                                ->label('Kondisi')
                                ->relationship('kondisi', 'nama_kondisi')
                                ->searchable()
                                ->required()
                                ->createOptionForm([
                                    TextInput::make('nama_kondisi')
                                        ->label('Nama Kondisi')
                                        ->required()
                                        ->rule(fn() => Rule::unique('kondisis', 'nama_kondisi')),
                                ])
                                ->createOptionAction(function (FormAction $action) {
                                    $auth = Auth::user();
                                    $canManage = $auth instanceof AppUser
                                        && $auth->canDo('perangkat.kondisi.manage');
                                    $action->visible($canManage);
                                    $action->modalHeading('Tambah Kondisi');
                                }),

                        ])->columns(3),

                    Tabs\Tab::make('Catatan Tambahan')
                        ->schema([
                            Textarea::make('catatan')
                                ->rows(5),
                            Textarea::make('mutasi')
                                ->label('Riwayat Mutasi / Perpindahan')
                                ->rows(5),
                            Textarea::make('upgrade')
                                ->label('Riwayat Upgrade')
                                ->rows(5),
                        ]),

                ])->columnSpan('full'),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_inventaris')
                    ->label('Nomor Inventaris')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_perangkat')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jenis.nama_jenis')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tipe')
                    ->label('Tipe / Merek')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('lokasi.nama_lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status.nama_status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Aktif' => 'success',
                        'Perbaikan' => 'warning',
                        'Rusak' => 'danger',
                        'Hilang' => 'danger',
                        'Disimpan' => 'gray',
                        default => 'info',
                    }),
                TextColumn::make('kondisi.nama_kondisi')
                    ->label('Kondisi')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('harga')
                //     ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('maintenanceTerakhir.created_at')
                    ->label('Maintenance Terakhir')
                    ->since()
                    ->sortable()
                    ->placeholder('Belum maintenance')
                    ->tooltip(fn($record) =>
                    $record->maintenanceTerakhir?->deskripsi_pekerjaan)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('kategori.masa_pakai_tahun')
                    ->label('Masa Pakai')
                    ->suffix(' Tahun')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('tahun_expired')
                ->label('Status Expired')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->getStateUsing(function (Perangkat $record) {
                    $tahun = $record->tahun_expired;
                    if ($tahun === null) return 'N/A';
                    return $record->isExpired() ? "Expired ({$tahun})" : "Baik ({$tahun})";
                })
                ->color(fn (Perangkat $record) => $record->isExpired() ? 'danger' : 'success')
                ->tooltip(fn ($record) => $record->isExpired() ? 'Harga sudah diatur menjadi Rp 0' : null)
                ->alignCenter(),
                TextColumn::make('harga')
                    ->money('idr') // Atau format mata uang yang sesuai, pastikan ini ada di config/app.php
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('lokasi_id')
                    ->label('Lokasi')
                    ->relationship('lokasi', 'nama_lokasi')
                    ->preload()
                    ->multiple(),
                SelectFilter::make('status_id')
                    ->label('Status')
                    ->relationship('status', 'nama_status')
                    ->preload()
                    ->multiple(),
                SelectFilter::make('jenis_id')
                    ->label('Jenis')
                    ->relationship('jenis', 'nama_jenis')
                    ->preload()
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(function () {
                        $user = Auth::user();
                        return $user instanceof AppUser && $user->canDo('perangkat.view');
                    }),

                Tables\Actions\EditAction::make()
                    ->visible(function () {
                        $user = Auth::user();
                        return $user instanceof AppUser && $user->canDo('perangkat.edit');
                    }),

                Tables\Actions\DeleteAction::make()
                    ->visible(function () {
                        $user = Auth::user();
                        return $user instanceof AppUser && $user->canDo('perangkat.delete');
                    }),

                TableAction::make('Cetak Stiker')
                    ->icon('heroicon-o-printer')
                    ->label('Stiker')
                    ->url(
                        fn(Perangkat $record): string =>
                        route('cetak.satu.stiker', ['perangkat' => $record->id])
                    )
                    ->openUrlInNewTab(),
            ])


            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Data Terpilih'),
                ]),
            ])
            ->defaultPaginationPageOption(5)
            ->paginated([5, 15, 25, 50, 100, 500]);
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
            'index' => Pages\ListPerangkat::route('/'),
            'create' => Pages\CreatePerangkat::route('/create'),
            'edit' => Pages\EditPerangkat::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['lokasi', 'jenis', 'status', 'kondisi', 'maintenanceTerakhir'])
            ->select(['id', 'nama_perangkat', 'nomor_inventaris', 'jenis_id', 'tipe', 'lokasi_id', 'status_id', 'kondisi_id', 'harga', 'created_at', 'tahun_pengadaan', 'spesifikasi', 'deskripsi', 'kategori_id', 'kode', 'perolehan'])
            ->latest('created_at');
    }
    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.view');
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.create');
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.edit');
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.delete');
    }

    public static function canDeleteAny(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.delete');
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.view');
    }
}
