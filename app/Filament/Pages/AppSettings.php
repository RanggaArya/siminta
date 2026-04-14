<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\User as AppUser;
use Illuminate\Database\Eloquent\Model;

class AppSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.app-settings';
    protected static ?string $title = 'Pengaturan Aplikasi';
    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::whereIn('key', ['export_password', 'export_owner_password'])
            ->get()
            ->pluck('value', 'key')
            ->all();

        $this->form->fill([
            'export_password' => $settings['export_password'] ?? null,
            'export_owner_password' => $settings['export_owner_password'] ?? null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pengaturan Ekspor')
                    ->description('Atur kata sandi default untuk semua file PDF dan Excel yang diekspor.')
                    ->schema([
                        Forms\Components\TextInput::make('export_password')
                            ->label('Password Ekspor PDF/Excel')
                            ->placeholder('Contoh: Rahasia123')
                            ->password()
                            ->revealable()
                            ->helperText('Kosongkan untuk menggunakan password dinamis (INV-TANGGAL).'),
                        Forms\Components\TextInput::make('export_owner_password')
                            ->label('Password Pemilik (Owner) PDF')
                            ->placeholder('Contoh: MasterRahasia456')
                            ->password()
                            ->revealable()
                            ->helperText('Hanya untuk PDF. Kosongkan untuk pakai default sistem.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::updateOrCreate(
            ['key' => 'export_password'],
            ['value' => $data['export_password'] ?? null]
        );

        Setting::updateOrCreate(
            ['key' => 'export_owner_password'],
            ['value' => $data['export_owner_password'] ?? null]
        );

        Notification::make()
            ->title('Pengaturan berhasil disimpan.')
            ->success()
            ->send();
        $this->redirect(route('filament.admin.pages.dashboard'));
    }
    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Simpan Pengaturan')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user instanceof AppUser) {
            return false;
        }

        return $user->isSuperAdmin();
    }
    public static function shouldRegisterNavigation(): bool
    {
        return False;
    }
    public static function canViewAny(): bool
    {
        $user = Auth::user();
        if (! $user instanceof AppUser) {
            return false;
        }

        return $user->isSuperAdmin();
    }
    public static function canCreate(): bool
    {
        $user = Auth::user();
        if (! $user instanceof AppUser) {
            return false;
        }

        return $user->isSuperAdmin();
    }
    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        if (! $user instanceof AppUser) {
            return false;
        }
        return $user->isSuperAdmin();
    }
    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        if (! $user instanceof AppUser) {
            return false;
        }
        return $user->isSuperAdmin();
    }
    public static function canDeleteAny(): bool
    {
        $user = Auth::user();
        if (! $user instanceof AppUser) {
            return false;
        }
        return $user->isSuperAdmin();
    }
}
