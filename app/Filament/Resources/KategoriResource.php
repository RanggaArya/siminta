<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KategoriResource\Pages;
use App\Models\Kategori;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use App\Models\User as AppUser;
use Filament\Forms\Components\Textarea; 
use Filament\Tables\Columns\BadgeColumn;

class KategoriResource extends Resource
{
    protected static ?string $model = Kategori::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?string $modelLabel = 'Kategori Perangkat';
    protected static ?string $slug = 'kategori';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('nama_kategori')
                ->label('Nama Kategori')
                ->required()
                ->unique(ignoreRecord: true),
            TextInput::make('kode_kategori')
                ->label('Kode Kategori (3 digit)')
                ->required()
                ->mask('999')
                ->unique(ignoreRecord: true),
            TextInput::make('masa_pakai_tahun')
                ->label('Masa Pakai (Tahun)')
                ->numeric()
                ->minValue(1)
                ->maxValue(20)
                ->required()
                ->default(10)
                ->helperText('Masa pakai alat dalam tahun (1-20 tahun).'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nama_kategori')->label('Nama')->searchable()->sortable(),
            TextColumn::make('kode_kategori')->label('Kode')->sortable(),
            TextColumn::make('masa_pakai_tahun')->label('Masa Pakai')->suffix(' Tahun')->sortable(),
        ])
            ->filters([])
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
            'index'  => Pages\ListKategoris::route('/'),
            'create' => Pages\CreateKategori::route('/create'),
            'edit'   => Pages\EditKategori::route('/{record}/edit'),
        ];
    }
    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.kategori.manage');
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.kategori.manage');
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.kategori.manage');
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.kategori.manage');
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.kategori.manage');
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.kategori.manage');
    }

    public static function canDeleteAny(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('perangkat.kategori.manage');
    }
}
