<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenarikanAlatResource\Pages;
use App\Models\PenarikanAlat;
use App\Models\Perangkat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TagsColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\User as AppUser;

class PenarikanAlatResource extends Resource
{
    protected static ?string $model = PenarikanAlat::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box-x-mark';
    protected static ?string $navigationGroup = 'Manajemen Inventaris';
    protected static ?string $navigationLabel = 'Penarikan Alat';
    protected static ?string $modelLabel = 'Penarikan Alat';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('perangkat_id')
                    ->label('Pilih Perangkat (Kode Inv.)')
                    ->relationship('perangkat', 'nomor_inventaris')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->columnSpan('full')
                    ->getOptionLabelUsing(fn($value) => Perangkat::find($value)?->nama_perangkat . ' (' . Perangkat::find($value)?->nomor_inventaris . ')')
                    ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                        if (blank($state)) return;
                        $perangkat = Perangkat::with('lokasi')->find($state);
                        if ($perangkat) {
                            $set('nama_perangkat', $perangkat->nama_perangkat);
                            $set('nomor_inventaris', $perangkat->nomor_inventaris);
                            $set('tipe', $perangkat->tipe);
                            $set('spesifikasi', $perangkat->spesifikasi);
                            $set('lokasi_id', $perangkat->lokasi_id);
                            $set('tahun_pembelian', $perangkat->tahun_pengadaan);
                        }
                    }),

                TextInput::make('nama_perangkat')
                    ->label('Nama Perangkat')
                    ->disabled()->dehydrated(),
                TextInput::make('nomor_inventaris')
                    ->label('No. Inventaris')
                    ->disabled()->dehydrated(),
                TextInput::make('tipe')
                    ->label('Tipe')
                    ->disabled()->dehydrated(),
                Select::make('lokasi_id')
                    ->label('Lokasi Terakhir')
                    ->relationship('lokasi', 'nama_lokasi')
                    ->disabled()->dehydrated(),
                TextInput::make('tahun_pembelian')
                    ->label('Tahun Pembelian')
                    ->disabled()->dehydrated(),
                Textarea::make('spesifikasi')
                    ->label('Spesifikasi')
                    ->disabled()->dehydrated()
                    ->columnSpan('full'),

                DatePicker::make('tanggal_penarikan')
                    ->label('Tanggal Penarikan')
                    ->required()
                    ->default(now())
                    ->native(false),

                CheckboxList::make('alasan_penarikan')
                    ->label('Alasan Penarikan')
                    ->options([
                        'Rusak' => 'Rusak',
                        'Tidak Layak Pakai' => 'Tidak Layak Pakai',
                        'Melebihi Masa Pakai' => 'Melebihi Masa Pakai',
                    ])
                    ->columns(3),

                Textarea::make('alasan_lainnya')
                    ->label('Alasan Lainnya (jika ada)')
                    ->nullable()
                    ->rows(2)
                    ->columnSpan('full'),

                Radio::make('tindak_lanjut_tipe')
                    ->label('Tindak Lanjut')
                    ->options([
                        'Perbaikan' => 'Perbaikan',
                        'Ganti Baru' => 'Ganti Baru',
                        'Pindahan' => 'Pindahan',
                        'Lainnya' => 'Lainnya',
                    ])
                    ->required()
                    ->live(),

                Textarea::make('tindak_lanjut_detail')
                    ->label('Detail Tindak Lanjut')
                    ->placeholder(fn(Forms\Get $get): string => match ($get('tindak_lanjut_tipe')) {
                        'Pindahan' => 'Diisi Pindahan dari Unit...',
                        'Lainnya' => 'Diisi keterangan lainnya...',
                        default => 'Detail (opsional)',
                    })
                    ->visible(fn(Forms\Get $get) => in_array($get('tindak_lanjut_tipe'), ['Pindahan', 'Lainnya']))
                    ->rows(2)
                    ->columnSpan('full'),

                TextInput::make('user_id')
                    ->hidden()
                    ->dehydrated()
                    ->default(fn() => Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_inventaris')
                    ->searchable()->sortable(),
                TextColumn::make('nama_perangkat')
                    ->searchable()->limit(30),
                TextColumn::make('lokasi.nama_lokasi')
                    ->label('Lokasi Snapshot'),
                TextColumn::make('tanggal_penarikan')
                    ->date('d M Y')
                    ->sortable(),
                TagsColumn::make('alasan_penarikan')
                    ->label('Alasan'),
                TextColumn::make('tindak_lanjut_tipe')
                    ->label('Tindak Lanjut')
                    ->getStateUsing(function ($record) {
                        $tipe = $record->tindak_lanjut_tipe;
                        $detail = $record->tindak_lanjut_detail;

                        if (in_array($tipe, ['Pindahan', 'Lainnya']) && filled($detail)) {
                            return "{$tipe} ({$detail})";
                        }

                        return $tipe;
                    })
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(function () {
                        $user = Auth::user();
                        return $user instanceof AppUser && $user->canDo('penarikan.view');
                    }),
                Tables\Actions\EditAction::make()
                    ->visible(function () {
                        $user = Auth::user();
                        return $user instanceof AppUser && $user->canDo('penarikan.edit');
                    }),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('tanggal_penarikan', 'desc');
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
            'index'  => Pages\ListPenarikanAlats::route('/'),
            'create' => Pages\CreatePenarikanAlat::route('/create'),
            'edit'   => Pages\EditPenarikanAlat::route('/{record}/edit'),
            'resume' => Pages\ResumePenarikanAlats::route('/resume'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('penarikan.view');
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('penarikan.create');
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('penarikan.edit');
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('penarikan.delete');
    }

    public static function canDeleteAny(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('penarikan.delete');
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('penarikan.view');
    }
}
