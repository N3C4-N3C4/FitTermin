<?php
/**
 * Konfiguracija aplikacije.
 *
 * Podrazumevane vrednosti su za lokalni rad (XAMPP).
 * Za InfinityFree napravi fajl config/config.local.php (on se ne šalje na GitHub)
 * i u njemu prepiši DB_* konstante podacima iz InfinityFree kontrolnog panela.
 */

if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

defined('DB_HOST') || define('DB_HOST', 'localhost');
defined('DB_NAME') || define('DB_NAME', 'fittermin');
defined('DB_USER') || define('DB_USER', 'root');
defined('DB_PASS') || define('DB_PASS', '');

defined('APP_NAME')  || define('APP_NAME', 'FitTermin');
defined('APP_DEBUG') || define('APP_DEBUG', false);

// Lokacija teretane – koristi se za vremensku prognozu (Open-Meteo API)
defined('GYM_CITY') || define('GYM_CITY', 'Beograd');
defined('GYM_LAT')  || define('GYM_LAT', 44.8125);
defined('GYM_LON')  || define('GYM_LON', 20.4612);

// Upload slika
define('UPLOAD_DIR', ROOT . '/uploads');
define('UPLOAD_MAX_BYTES', 2 * 1024 * 1024); // 2 MB

date_default_timezone_set('Europe/Belgrade');
