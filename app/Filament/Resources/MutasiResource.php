<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MutasiResource\Pages;
use App\Models\Mutasi;
use App\Models\Perangkat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\User as AppUser;

class MutasiResource extends Resource
{
    protected static ?string $model = Mutasi::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Manajemen Inventaris';
    protected static ?string $navigationLabel = 'Mutasi Perangkat';
    protected static ?string $modelLabel = 'Mutasi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('perangkat_id')
                    ->label('Pilih Perangkat (Nomor Inventaris)')
                    ->relationship('perangkat', 'nomor_inventaris')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->columnSpan('full')
                    ->getOptionLabelUsing(fn($value) => Perangkat::find($value)?->nama_perangkat . ' (' . Perangkat::find($value)?->nomor_inventaris . ')')
                    ->disabled(fn() => request()->query('perangkat_id') !== null)
                    ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                        if (blank($state)) {
                            $set('nama_perangkat', null);
                            $set('nomor_inventaris', null);
                            $set('tipe', null);
                            $set('kondisi_id', null);
                            $set('lokasi_asal_id', null);
                            return;
                        }

                        $perangkat = Perangkat::with(['kondisi', 'lokasi'])->find($state);
                        if ($perangkat) {
                            $set('nama_perangkat', $perangkat->nama_perangkat);
                            $set('nomor_inventaris', $perangkat->nomor_inventaris);
                            $set('tipe', $perangkat->tipe);
                            $set('kondisi_id', $perangkat->kondisi_id);
                            $set('lokasi_asal_id', $perangkat->lokasi_id);
                        }
                    }),

                TextInput::make('nama_perangkat')
                    ->label('Nama Perangkat')
                    ->required()
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('nomor_inventaris')
                    ->label('No. Inventaris')
                    ->required()
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('tipe')
                    ->label('Tipe')
                    ->nullable(),

                Select::make('kondisi_id')
                    ->label('Kondisi')
                    ->relationship('kondisi', 'nama_kondisi')
                    ->searchable()
                    ->preload()
                    ->disabled()
                    ->dehydrated(),

                Select::make('lokasi_asal_id')
                    ->label('Lokasi Asal (Snapshot)')
                    ->relationship('lokasiAsal', 'nama_lokasi')
                    ->searchable()
                    ->preload()
                    ->disabled()
                    ->dehydrated(),

                Select::make('lokasi_mutasi_id')
                    ->label('Lokasi Mutasi (Tujuan)')
                    ->relationship('lokasiMutasi', 'nama_lokasi')
                    ->searchable()
                    ->preload()
                    ->required(),

                DatePicker::make('tanggal_mutasi')
                    ->label('Tanggal Mutasi')
                    ->required()
                    ->default(now())
                    ->native(false),

                DatePicker::make('tanggal_diterima')
                    ->label('Tanggal Diterima')
                    ->nullable()
                    ->native(false),

                Textarea::make('alasan_mutasi')
                    ->label('Alasan Mutasi')
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
                    ->label('No. Inventaris')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_perangkat')
                    ->label('Nama Perangkat')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('lokasiAsal.nama_lokasi')
                    ->label('Lokasi Asal')
                    ->sortable(),
                TextColumn::make('lokasiMutasi.nama_lokasi')
                    ->label('Lokasi Tujuan')
                    ->sortable(),
                TextColumn::make('kondisi.nama_kondisi')
                    ->label('Kondisi')
                    ->badge(),
                TextColumn::make('tanggal_mutasi')
                    ->label('Tgl. Mutasi')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('tanggal_diterima')
                    ->label('Tgl. Diterima')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('alasan_mutasi')
                    ->label('Alasan Mutasi')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label('Dicatat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\Action::make('resume')
                    ->label('Lihat Resume')
                    ->icon('heroicon-o-presentation-chart-bar')
                    ->url(fn() => static::getUrl('resume'))
                    ->visible(function () {
                        $user = Auth::user();
                        return $user instanceof AppUser && $user->canDo('perangkat.mutasi');
                    }),
            ])

            ->filters([
                //
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
            ])
            ->defaultSort('tanggal_mutasi', 'desc');
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
            'index' => Pages\ListMutasis::route('/'),
            'create' => Pages\CreateMutasi::route('/create'),
            'edit' => Pages\EditMutasi::route('/{record}/edit'),
            'resume' => Pages\ResumeMutasis::route('/resume'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.mutasi');
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.mutasi');
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.mutasi');
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.mutasi');
    }

    public static function canDeleteAny(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.mutasi');
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.mutasi');
    }
}
