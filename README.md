# FitTermin – rezervacija termina u teretani

FitTermin je veb aplikacija za online rezervaciju grupnih treninga u teretani.
Članovi pregledaju raspored, filtriraju treninge i rezervišu ili otkazuju mesto jednim klikom,
bez osvežavanja stranice. Administrator (vlasnik teretane) dodaje, menja i briše treninge,
otprema slike i vidi spisak prijavljenih članova za svaki termin.

**Live demo:** https://fittermin.infinityfree.io

## Podaci za prijavu

| Uloga         | Email                | Lozinka     |
|---------------|----------------------|-------------|
| Administrator | `admin@fittermin.rs` | `Admin123!` |
| Član          | `profesor@fittermin.rs`  | `test`  |

Nalozi iznad važe na live sajtu. Fajl database/schema.sql sadrži zasebne probne naloge (admin@fittermin.rs / Admin123!, clan@fittermin.rs / Clan123!) za lokalnu instalaciju.

Nov nalog člana može se napraviti i preko stranice **Registracija**.

## Funkcionalnosti

| # | Zahtev | Kako je urađeno |
|---|--------|-----------------|
| 1 | MVC arhitektura | Sopstveni MVC bez frameworka: `index.php` (front controller) → `Router` → kontroleri (`app/controllers`) → modeli (`app/models`) → view-ovi (`app/views`) |
| 2 | CRUD | Kompletan CRUD nad entitetom **Trening** (admin) |
| 3 | Baza podataka | MySQL, tabele `korisnici`, `treninzi`, `rezervacije` (strani ključevi, transakcije) |
| 4 | Login sistem | Registracija, prijava, odjava, uloge `admin` i `clan` |
| 5 | Upload slika | Slika se šalje veb servisu `POST /api/upload` (AJAX); provera tipa po sadržaju, max 2 MB, nasumično ime |
| 6 | Spoljni veb servis | [Open-Meteo API](https://open-meteo.com/): trenutno vreme na početnoj i prognoza za vreme svakog treninga |
| 7 | AJAX | Rezervacija i otkazivanje, pretraga i filtriranje rasporeda, brisanje treninga, upload slike |
| 8 | JavaScript | Kartice, brojač slobodnih mesta, toast poruke, indikator jačine lozinke – sve se menja kroz DOM |
| 9 | Bezbednost | `password_hash`/`password_verify`, PDO prepared statements, CSRF tokeni, `htmlspecialchars` (XSS), kontrola pristupa po ulogama, `session_regenerate_id`, zaštićeni folderi preko `.htaccess` |
| 10 | Dizajn | Bootstrap 5 + Bootstrap Icons, responzivno (telefon, tablet, desktop) |

## Veb servisi aplikacije (JSON API)

| Metoda | Putanja | Opis |
|--------|---------|------|
| GET  | `/api/treninzi?q=&kategorija=` | Lista treninga sa pretragom i filterom |
| POST | `/api/rezervacije` | Rezervacija (`trening_id`) – samo prijavljeni |
| POST | `/api/rezervacije/{id}/otkazi` | Otkazivanje rezervacije |
| POST | `/api/treninzi/{id}/obrisi` | Brisanje treninga – samo admin |
| POST | `/api/upload` | Upload slike (`multipart/form-data`, polje `slika`) – samo admin |

## Tehnologije

- **Backend:** PHP 8 (bez frameworka), MySQL (PDO)
- **Frontend:** HTML5, CSS3, JavaScript (Fetch API), Bootstrap 5
- **Hosting:** InfinityFree

## Struktura projekta

```
FitTermin/
├── index.php              # front controller – jedina ulazna tačka
├── .htaccess              # preusmeravanje na index.php, zaštita foldera
├── config/
│   ├── config.php         # podešavanja (baza, lokacija teretane...)
│   └── config.local.example.php
├── app/
│   ├── routes.php         # spisak ruta
│   ├── core/              # Router, Controller, Model, Database, Auth, Csrf, helpers
│   ├── controllers/       # Home, Auth, Trening, Rezervacija, Api
│   ├── models/            # User, Trening, Rezervacija
│   └── views/             # layouts, home, auth, treninzi, rezervacije, errors
├── assets/
│   ├── css/style.css
│   └── js/app.js          # AJAX, DOM, Open-Meteo
├── uploads/               # otpremljene slike (PHP se ovde ne izvršava)
└── database/schema.sql    # tabele + početni podaci
```

## Lokalno pokretanje (XAMPP)

1. Kopirati folder `FitTermin` u `C:\xampp\htdocs\`.
2. Pokrenuti **Apache** i **MySQL** u XAMPP Control Panel-u.
3. U phpMyAdmin-u (`http://localhost/phpmyadmin`) napravi bazu `fittermin` (utf8mb4_unicode_ci)
   i importovati `database/schema.sql`.
4. Otvoriti `http://localhost/FitTermin`.

Podrazumevana podešavanja baze su `root` bez lozinke. Ako su drugačija, kopirati
`config/config.local.example.php` u `config/config.local.php` i upisati svoje podatke.
