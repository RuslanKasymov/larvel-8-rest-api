<?php

namespace App\Mails;

class ForgotPasswordMail extends BaseMail
{
    public function __construct($to, array $data)
    {
        parent::__construct(
            config('mail.from.address'),
            $to,
            $data,
            trans('auth.password_reset'),
            'emails.forgot_password'
        );
    }
}
