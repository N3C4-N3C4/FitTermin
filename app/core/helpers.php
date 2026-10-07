<?php
/**
 * Pomoćne funkcije dostupne u celoj aplikaciji (posebno u view-ovima).
 */

/** Escape izlaza – zaštita od XSS napada. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Osnovna putanja aplikacije (radi i kad je u podfolderu, npr. localhost/FitTermin). */
function base_path(): string
{
    static $base = null;
    if ($base === null) {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    }
    return $base;
}

/** Pravi URL unutar aplikacije: url('treninzi/5') -> /FitTermin/treninzi/5 */
function url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

/** Putanja za statičke fajlove (CSS, JS, slike) */
function asset(string $path): string
{
    $file = ROOT . '/' . ltrim($path, '/');
    $version = is_file($file) ? '?v=' . filemtime($file) : '';
    return url($path) . $version;
}

/** URL slike treninga ili null */
function upload_url(?string $file): ?string
{
    return $file ? url('uploads/' . $file) : null;
}

/** Trenutna ruta, npr. "/treninzi/5" */
function current_path(): string
{
    if (isset($_GET['url'])) {
        $path = (string) $_GET['url'];
    } else {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = base_path();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        $path = preg_replace('#^/index\.php#', '', $path);
    }
    return '/' . trim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

/** Jednokratna poruka (prikazuje se posle redirekcije) */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/** Stari unos forme posle neuspešne validacije */
function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['old'][$key] ?? $default;
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">';
}

function is_ajax(): bool
{
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
        || str_starts_with(current_path(), '/api/');
}

/** Prekida izvršavanje i prikazuje stranicu greške (ili JSON za API). */
function abort(int $code, string $message = ''): never
{
    http_response_code($code);
    $titles = [
        403 => 'Pristup zabranjen',
        404 => 'Stranica nije pronađena',
        405 => 'Metoda nije dozvoljena',
        500 => 'Greška na serveru',
    ];
    $title = $titles[$code] ?? 'Greška';

    if (is_ajax()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $message ?: $title], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pageTitle = $title;
    require ROOT . '/app/views/layouts/header.php';
    require ROOT . '/app/views/errors/error.php';
    require ROOT . '/app/views/layouts/footer.php';
    exit;
}

/** Formatiranje datuma: 2026-10-07 18:00:00 -> 07.10.2026. u 18:00 */
function format_datum(string $dt): string
{
    return date('d.m.Y. \u H:i', strtotime($dt));
}

function dan_u_nedelji(string $dt): string
{
    $dani = ['Nedelja', 'Ponedeljak', 'Utorak', 'Sreda', 'Četvrtak', 'Petak', 'Subota'];
    return $dani[(int) date('w', strtotime($dt))];
}
