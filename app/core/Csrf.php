<?php
/**
 * CSRF zaštita: svaka forma i AJAX zahtev šalju token koji se poredi sa onim u sesiji.
 */
class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verify(): bool
    {
        $sent = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return is_string($sent)
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $sent);
    }
}
