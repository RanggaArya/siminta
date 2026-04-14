<?php

namespace App\Filament\Resources\PerangkatResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use App\Models\User as AppUser;
use Illuminate\Database\Eloquent\Model;

class RiwayatMaintenancesRelationManager extends RelationManager
{
    protected static string $relationship = 'riwayatMaintenances';
    protected static ?string $title = 'Riwayat Maintenance';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('harga')
                    ->label('Harga Maintenance')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('Rp')
                    ->dehydrateStateUsing(
                        fn($state) => $state === null
                            ? null
                            : max(0, (float) preg_replace('/[^\d.]/', '', (string) $state))
                    )
                    ->validationAttribute('Harga'),
                Textarea::make('deskripsi')
                    ->label('Deskripsi Pekerjaan')
                    ->required()
                    ->columnSpan('full'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('deskripsi')
            ->columns([
                TextColumn::make('deskripsi')
                    ->label('Deskripsi Pekerjaan')
                    ->limit(50),
                TextColumn::make('harga')
                    ->label('Harga Maintenance')
                    ->money('IDR', true),
                TextColumn::make('created_at')
                    ->label('Tanggal Maintenance')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public function canCreate(): bool
    {
        $user = Auth::user();
        if (! $user instanceof AppUser) {
            return false;
        }
        return $user->isAdminOrSuper();
    }

    public function canEdit(Model $record): bool
    {
        $user = Auth::user();
        if (! $user instanceof AppUser) {
            return false;
        }
        return $user->isAdminOrSuper();
    }
}
