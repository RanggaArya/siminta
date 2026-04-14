<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Filament\Forms\Get;

class AccountSettings extends Page implements HasForms
{
  use InteractsWithForms;

  protected static ?string $title = 'Pengaturan Akun';
  protected static string $view = 'filament.pages.account-settings';
  protected static bool $shouldRegisterNavigation = false;
  protected static ?string $slug = 'account-settings';

  public ?string $name = null;
  public ?string $email = null;
  public ?string $jabatan = null;
  public ?string $unit = null;
  public ?string $password = null;
  public ?string $password_confirmation = null;
  public ?string $current_password = null;


  public function mount(): void
  {
    $u = Auth::user();
    $this->form->fill([
      'name'    => $u->name,
      'email'   => $u->email,
      'jabatan' => $u->jabatan,
      'unit'    => $u->unit,
    ]);
  }

  protected function getForms(): array
  {
    return [
      'form' => $this->makeForm()
        ->schema([
          Forms\Components\Section::make('Profil')->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('email')->email()->required(),
            Forms\Components\TextInput::make('jabatan')->label('Jabatan'),
            Forms\Components\TextInput::make('unit')->label('Unit'),
          ])->columns(2),

          Forms\Components\Section::make('Ubah Password')->schema([
            Forms\Components\TextInput::make('current_password')
              ->label('Password saat ini')
              ->password()
              ->revealable()
              ->dehydrated(false)
              ->required(fn(Get $get) => filled($get('password')))
              ->rule('current_password'),

            Forms\Components\TextInput::make('password')
              ->label('Password baru')
              ->password()
              ->revealable()
              ->minLength(8)
              ->same('password_confirmation')
              ->dehydrated(),

            Forms\Components\TextInput::make('password_confirmation')
              ->label('Konfirmasi password baru')
              ->password()
              ->revealable()
              ->dehydrated(false),
          ])->columns(2),
        ]),
    ];
  }

  public function save(): void
  {
    /** @var \App\Models\User $u */
    $u = Auth::user();
    $data = $this->form->getState();

    $u->name    = $data['name'];
    $u->email   = $data['email'];
    $u->jabatan = $data['jabatan'] ?? null;
    $u->unit    = $data['unit'] ?? null;

    if (! empty($data['password'])) {
      $u->password = Hash::make($data['password']);
    }

    $u->save();

    Notification::make()
      ->title('Akun berhasil diperbarui.')
      ->success()
      ->send();
    $this->redirect(route('filament.admin.pages.dashboard'));
  }
  protected function getHeaderActions(): array
  {
    return [
      \Filament\Actions\Action::make('save')
        ->label('Simpan')
        ->color('primary')
        ->action('save'),
    ];
  }
}
