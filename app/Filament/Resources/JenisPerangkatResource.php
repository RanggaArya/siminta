<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JenisPerangkatResource\Pages;
use App\Filament\Resources\JenisPerangkatResource\RelationManagers;
use App\Models\JenisPerangkat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use App\Models\User as AppUser;


class JenisPerangkatResource extends Resource
{
    protected static ?string $model = JenisPerangkat::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Jenis Perangkat';
    protected static ?string $pluralModelLabel = 'Jenis Perangkat';
    protected static ?string $slug = 'jenis-perangkat';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_jenis')
                    ->label('Nama Jenis')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                TextInput::make('prefix')
                    ->label('Prefix (1 huruf)')
                    ->default('B')
                    ->maxLength(1)
                    ->required(),

                TextInput::make('kode_jenis')
                    ->label('Kode Jenis (mis. 02.4)')
                    ->required()
                    ->rule('regex:/^\d{2}\.\d{1}$/')
                    ->helperText('Format: 2 digit, titik, 1 digit. Contoh: 02.4'),
            ])->columns(3);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_jenis')->searchable()->sortable(),
                TextColumn::make('prefix')->label('Prefix')->sortable(),
                TextColumn::make('kode_jenis')->label('Kode Jenis')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ManageJenisPerangkat::route('/'),
        ];
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.jenis.manage');
    }
    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.jenis.manage');
    }
    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.jenis.manage');
    }
    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.jenis.manage');
    }
    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.jenis.manage');
    }
    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.jenis.manage');
    }
    public static function canDeleteAny(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.jenis.manage');
    }
}
