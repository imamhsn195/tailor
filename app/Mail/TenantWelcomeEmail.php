<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TenantWelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Tenant $tenant;
    public string $email;
    public string $password;
    public string $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Tenant $tenant, string $email, string $password, string $loginUrl)
    {
        $this->tenant = $tenant;
        $this->email = $email;
        $this->password = $password;
        $this->loginUrl = $loginUrl;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome to ' . config('app.name') . ' - Your Account is Ready!')
            ->view('emails.tenant.welcome')
            ->with([
                'tenant' => $this->tenant,
                'email' => $this->email,
                'password' => $this->password,
                'loginUrl' => $this->loginUrl,
            ]);
    }
}

