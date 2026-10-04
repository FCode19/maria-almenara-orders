<?php

namespace App\Helpers;

use App\Core\Session;

class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf_token');

        if (!$token) {
            $token = bin2hex(random_bytes(32));

            Session::set('_csrf_token', $token);
        }

        return $token;
    }

    public static function field(): string
    {
        $token = htmlspecialchars(
            self::token(),
            ENT_QUOTES,
            'UTF-8'
        );

        return '<input type="hidden" name="_token" value="' . $token . '">';
    }

    public static function validate(?string $token): bool
    {
        $stored = Session::get('_csrf_token');

        if (!$stored || !$token) {
            return false;
        }

        return hash_equals($stored, $token);
    }

    public static function regenerate(): void
    {
        Session::set(
            '_csrf_token',
            bin2hex(random_bytes(32))
        );
    }
}