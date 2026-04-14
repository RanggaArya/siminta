<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class FilamentResetPassword extends ResetPassword
{
    protected string $panelId = 'admin';

    public function toMail($notifiable): MailMessage
    {
        $routeName = "filament.{$this->panelId}.auth.password-reset.reset";

        $expire = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        $url = URL::temporarySignedRoute(
            $routeName,
            now()->addMinutes($expire),
            [
                'email' => $notifiable->getEmailForPasswordReset(),
                'token' => $this->token,
            ]
        );

        return (new MailMessage)
            ->subject('Reset Password')
            ->line('Kami menerima permintaan reset password untuk akun Anda.')
            ->action('Atur Ulang Password', $url)
            ->line('Abaikan jika Anda tidak meminta reset.');
    }
}

