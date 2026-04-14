<?php

namespace App\Filament\Resources;

use App;
use App\Filament\Resources\KondisiResource\Pages;
use App\Filament\Resources\KondisiResource\RelationManagers;
use App\Models\Kondisi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\User as AppUser;

class KondisiResource extends Resource
{
    protected static ?string $model = Kondisi::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Kondisi Perangkat';
    protected static ?string $pluralModelLabel = 'Kondisi Perangkat';
    protected static ?string $slug = 'kondisi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_kondisi')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_kondisi')
                    ->searchable()
                    ->sortable()
                    ->badge(),
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
            'index' => Pages\ManageKondisis::route('/'),
        ];
    }
    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.kondisi.manage');
    }
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.kondisi.manage');
    }
    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.kondisi.manage');
    }
    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.kondisi.manage');
    }
    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.kondisi.manage');
    }
    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user-> canDo('perangkat.kondisi.manage');
    }
    public static function canDeleteAny(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.kondisi.manage');
    }
}
