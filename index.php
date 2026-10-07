<?php
/**
 * Front controller – jedina ulazna tačka aplikacije.
 * Svi zahtevi (preko .htaccess) dolaze ovde, a Router ih prosleđuje
 * odgovarajućem kontroleru (C), koji koristi modele (M) i prikazuje view (V).
 */
declare(strict_types=1);

define('ROOT', __DIR__);

require ROOT . '/config/config.php';
require ROOT . '/app/core/helpers.php';

// Autoload klasa iz app/core, app/controllers i app/models
spl_autoload_register(function (string $class): void {
    foreach (['core', 'controllers', 'models'] as $dir) {
        $file = ROOT . "/app/$dir/$class.php";
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

// Bezbedna sesija
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

// CSRF zaštita za sve POST zahteve (forme i AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !Csrf::verify()) {
    abort(403, 'Sesija je istekla ili zahtev nije validan. Osveži stranicu i pokušaj ponovo.');
}

$router = new Router();
require ROOT . '/app/routes.php';

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], current_path());
} catch (PDOException $e) {
    error_log($e->getMessage());
    abort(500, APP_DEBUG ? $e->getMessage() : 'Greška u radu sa bazom podataka.');
}
