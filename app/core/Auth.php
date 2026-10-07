<?php
/**
 * Autentifikacija – podaci o prijavljenom korisniku čuvaju se u sesiji.
 */
class Auth
{
    public static function login(array $user): void
    {
        // Nova ID sesije posle prijave – zaštita od session fixation napada
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'    => (int) $user['id'],
            'ime'   => $user['ime'],
            'email' => $user['email'],
            'uloga' => $user['uloga'],
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function isAdmin(): bool
    {
        return ($_SESSION['user']['uloga'] ?? null) === 'admin';
    }
}
