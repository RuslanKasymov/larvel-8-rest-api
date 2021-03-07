@extends('layouts.mail')

@section('content')
    <table cellpadding="0" cellspacing="0" style="max-width: 560px;    width: 100%;" align="center">
        <tbody>
        <tr>
            <td class="title"
                style="font-family: Arial, Helvetica, sans-serif;font-size: 36px; font-weight: 700; color: #161E2E; padding-top: 48px; padding-bottom: 24px;">
                Hello {{ $name }}!
            </td>
        </tr>
        <tr>
            <td
                style="font-family: Arial, Helvetica, sans-serif; font-size: 16px; color: #161E2E; line-height: 25px; padding-bottom: 50px;">
                You are receiving this email because we received a password reset request for your account.
            </td>
        </tr>
        <tr>
            <td align="center" style="padding-bottom: 50px;;">
                <a style="background: #0277E7; padding-top: 15px;padding-bottom: 16px;padding-left: 47px;padding-right: 47px; color: #ffffff; font-size: 16px; text-decoration: none; border-radius: 10px;  font-weight: 700; font-family: Arial, Helvetica, sans-serif;
                            " href="{{ config('app.frontend_url') }}/setup-password?hash={{$hash}}">Reset Password</a>
            </td>
        </tr>
        <tr>
            <td
                style="font-family: Arial, Helvetica, sans-serif; font-size: 16px; color: #161E2E; line-height: 25px; padding-bottom: 50px;">
                <p>If you’re having trouble clicking the "Reset Password" button, copy and paste the URL
                    below into your web browser:</p>
                <a href="{{ config('app.frontend_url') }}/setup-password?hash={{ $hash }}" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #3869D4;">
                    {{ config('app.frontend_url') }}/setup-password?hash={{ $hash }}
                </a>
            </td>
        </tr>
        </tbody>
    </table>
@endsection
