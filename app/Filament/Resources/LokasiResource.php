<?php

namespace App\Filament\Resources;

use App;
use App\Filament\Resources\LokasiResource\Pages;
use App\Filament\Resources\LokasiResource\RelationManagers;
use App\Models\Lokasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\User as AppUser;

class LokasiResource extends Resource
{
    protected static ?string $model = Lokasi::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Lokasi Perangkat';
    protected static ?string $pluralModelLabel = 'Lokasi Perangkat';
    protected static ?string $slug = 'lokasi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_lokasi')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->columnSpan('full'),
                Textarea::make('deskripsi_lokasi')
                    ->rows(3)
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('deskripsi_lokasi')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLokasis::route('/'),
        ];
    }
    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.lokasi.manage');
    }
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.lokasi.manage');
    }
    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.lokasi.manage');
    }
    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.lokasi.manage');
    }
    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.lokasi.manage');
    }
    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.lokasi.manage');
    }
    public static function canDeleteAny(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.lokasi.manage');
    }
}
