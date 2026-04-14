<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use App\Models\User as AppUser;
use Filament\Forms\Components\CheckboxList;

class UserResource extends Resource
{
    protected static ?string $model = AppUser::class;
    protected static ?string $navigationIcon = 'heroicon-s-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('jabatan')->label('Jabatan')->maxLength(255),
                Forms\Components\TextInput::make('unit')->label('Unit')->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->required(fn(string $operation) => $operation === 'create')
                    ->visible(function (?Model $record, string $operation) {
                        if ($operation === 'create') return true;
                        return (int)Auth::id() === (int)($record?->id);
                    })
                    ->dehydrateStateUsing(fn($state) => $state ? Hash::make($state) : null)
                    ->dehydrated(function ($state, ?Model $record, string $operation) {
                        if (! filled($state)) return false;
                        if ($operation === 'create') return true;
                        return (int)Auth::id() === (int)($record?->id);
                    })
                    ->maxLength(255),

                Select::make('role')
                    ->label('Role')
                    ->options(function () {
                        $auth = Auth::user();
                        if ($auth instanceof AppUser && $auth->isSuperAdmin()) {
                            return [
                                'super-admin' => 'Super Admin',
                                'admin'       => 'Admin',
                                'user'        => 'User',
                                'teknisi'     => 'Teknisi',
                            ];
                        }
                        return ['user' => 'User'];
                    })
                    ->default('user')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        if ($state === 'user') {
                            $set('permissions', []);
                        }
                    })
                    ->disabled(function (string $operation) {
                        $auth = Auth::user();
                        return $operation === 'edit'
                            && !($auth instanceof AppUser && $auth->isSuperAdmin());
                    }),


                CheckboxList::make('permissions')
                    ->label('Hak Akses Khusus')
                    ->helperText('Jika dikosongkan, user akan mengikuti default berdasarkan role.')
                    ->options(function (Forms\Get $get) {
                        $role = $get('role');

                        $all = [
                            'dashboard.view' => 'Lihat Dashboard',

                            'user.view'   => 'Lihat user',
                            'user.manage' => 'Kelola user (tambah, edit, hapus)',

                            'resume.view'   => 'Lihat resume',
                            // 'resume.manage' => 'Kelola resume (tambah, edit, hapus)',

                            'peminjaman.view'   => 'Lihat peminjaman',
                            'peminjaman.create' => 'Ajukan peminjaman',
                            'peminjaman.manage' => 'Kelola peminjaman (edit, hapus, acc)',

                            'perangkat.view'   => 'Lihat perangkat',
                            'perangkat.manage' => 'Kelola perangkat (tambah, edit, hapus)',

                            'perangkat.import' => 'Import perangkat',
                            'perangkat.mutasi' => 'Kelola mutasi perangkat',
                            'perangkat.jenis.manage'    => 'Kelola master jenis',
                            'perangkat.status.manage'   => 'Kelola master status',
                            'perangkat.kondisi.manage'  => 'Kelola master kondisi',
                            'perangkat.lokasi.manage'   => 'Kelola master lokasi',
                            'perangkat.kategori.manage' => 'Kelola master kategori',

                            'maintenance.view'   => 'Lihat maintenance',
                            'maintenance.manage' => 'Kelola maintenance (tambah, edit, hapus)',

                            'penarikan.view'   => 'Lihat penarikan alat',
                            'penarikan.manage' => 'Kelola penarikan alat (tambah, edit, hapus)',
                        ];

                        if ($role === 'user') {
                            return [
                                'dashboard.view'   => $all['dashboard.view'],
                                'peminjaman.view'  => $all['peminjaman.view'],
                                'peminjaman.create' => $all['peminjaman.create'],
                            ];
                        }

                        return $all;
                    })
                    ->columns(2)
                    ->visible(function () {
                        $auth = Auth::user();
                        return $auth instanceof AppUser && $auth->isSuperAdmin();
                    })
                    ->columns(2)
                    ->visible(function () {
                        $auth = Auth::user();
                        return $auth instanceof AppUser && $auth->isSuperAdmin();
                    }),

            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jabatan')->label('Jabatan')->toggleable(),
                Tables\Columns\TextColumn::make('unit')->label('Unit')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $auth = Auth::user();

        if (! $auth instanceof AppUser) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('id', '!=', $auth->id);

        if ($auth->isSuperAdmin()) {
            return $query->whereIn('role', ['admin', 'user', 'teknisi']);
        }

        if ($auth->isAdmin()) {
            return $query->where('role', 'user');
        }

        return $query->whereRaw('1 = 0');
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    public static function canView(Model $record): bool
    {
        $auth = Auth::user();
        if (! $auth instanceof AppUser) {
            return false;
        }

        if ($auth->isSuperAdmin()) {
            return $auth->canDo('user.view');
        }

        if ($auth->canDo('user.view')) {
            if ($record->isSuperAdmin()) {
                return false;
            }
            return true;
        }

        return false;
    }

    public static function canCreate(): bool
    {
        $auth = Auth::user();
        return $auth instanceof AppUser && $auth->canDo('user.create');
    }

    public static function canEdit(Model $record): bool
    {
        $auth = Auth::user();
        if (! $auth instanceof AppUser) {
            return false;
        }

        if (! $auth->canDo('user.edit')) {
            return false;
        }

        if ($auth->isAdmin() && $record->isSuperAdmin()) {
            return false;
        }

        return true;
    }

    public static function canDelete(Model $record): bool
    {
        $auth = Auth::user();
        if (! $auth instanceof AppUser) {
            return false;
        }

        if ($auth->id === $record->id) {
            return false;
        }

        if (! $auth->canDo('user.delete')) {
            return false;
        }

        if ($auth->isAdmin() && $record->isSuperAdmin()) {
            return false;
        }

        return true;
    }

    public static function canDeleteAny(): bool
    {
        $auth = Auth::user();
        return $auth instanceof AppUser && $auth->canDo('user.delete');
    }

    public static function shouldRegisterNavigation(): bool
    {
        $auth = Auth::user();
        return $auth instanceof AppUser && $auth->canDo('user.view');
    }
}
