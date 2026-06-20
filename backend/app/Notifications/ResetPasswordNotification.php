<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
	public function toMail($notifiable): MailMessage
	{
		$resetUrl = url(route('password.reset', [
			'token' => $this->token,
			'email' => $notifiable->getEmailForPasswordReset(),
		], false));

		$expireMinutes = (int) config('auth.passwords.users.expire', 60);

		return (new MailMessage)
			->subject('Reset Password - '.config('app.name'))
			->greeting('Halo!')
			->line('Anda menerima email ini karena ada permintaan reset password untuk akun Anda.')
			->action('Reset Password (Web)', $resetUrl)
			->line('Untuk aplikasi mobile PEKA Stunting, buka menu Lupa Password, lalu masukkan token berikut:')
			->line($this->token)
			->line("Token berlaku {$expireMinutes} menit.")
			->line('Jika Anda tidak meminta reset password, abaikan email ini.');
	}
}
