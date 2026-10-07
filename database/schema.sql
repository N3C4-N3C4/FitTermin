-- =========================================================
-- FitTermin – baza podataka (MySQL / MariaDB)
-- Lokalno (XAMPP / phpMyAdmin): napravi bazu "fittermin" pa importuj ovaj fajl.
-- InfinityFree: baza se pravi u kontrolnom panelu, pa se ovaj fajl importuje
-- kroz phpMyAdmin (bez CREATE DATABASE linije).
-- =========================================================

SET NAMES utf8mb4;

DROP TABLE IF EXISTS rezervacije;
DROP TABLE IF EXISTS treninzi;
DROP TABLE IF EXISTS korisnici;

CREATE TABLE korisnici (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ime        VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    lozinka    VARCHAR(255) NOT NULL,              -- password_hash() (bcrypt)
    uloga      ENUM('admin', 'clan') NOT NULL DEFAULT 'clan',
    kreiran    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE treninzi (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    naziv        VARCHAR(120) NOT NULL,
    kategorija   VARCHAR(40)  NOT NULL,
    opis         TEXT NULL,
    trener       VARCHAR(100) NOT NULL,
    datum_vreme  DATETIME NOT NULL,
    trajanje_min SMALLINT UNSIGNED NOT NULL DEFAULT 60,
    kapacitet    SMALLINT UNSIGNED NOT NULL DEFAULT 10,
    slika        VARCHAR(100) NULL,               -- ime fajla u folderu uploads/
    kreiran      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_datum (datum_vreme)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rezervacije (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    korisnik_id INT UNSIGNED NOT NULL,
    trening_id  INT UNSIGNED NOT NULL,
    kreiran     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_korisnik_trening (korisnik_id, trening_id),
    CONSTRAINT fk_rez_korisnik FOREIGN KEY (korisnik_id) REFERENCES korisnici(id) ON DELETE CASCADE,
    CONSTRAINT fk_rez_trening  FOREIGN KEY (trening_id)  REFERENCES treninzi(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Početni podaci
-- admin@fittermin.rs / Admin123!
-- clan@fittermin.rs  / Clan123!
-- ---------------------------------------------------------
INSERT INTO korisnici (ime, email, lozinka, uloga) VALUES
('Administrator', 'admin@fittermin.rs', '$2y$10$3./N2NgLOUzOlRPs6b4nbe6OCbP7cruZkmef8FQ/B2F/Ljpd4oayi', 'admin'),
('Marko Petrović', 'clan@fittermin.rs', '$2y$10$jhLJ7D0CANeSh0kQ1MjGz.ucToV7MscKoEOT2AdVH3dXNun4rkk62', 'clan');

-- Termini se prave relativno u odnosu na današnji dan, da bi uvek bili u budućnosti
INSERT INTO treninzi (naziv, kategorija, opis, trener, datum_vreme, trajanje_min, kapacitet) VALUES
('Jutarnja joga', 'Joga', 'Lagan početak dana: disanje, istezanje i balans. Pogodno za sve nivoe.', 'Ana Jovanović', TIMESTAMP(CURDATE() + INTERVAL 1 DAY, '08:00:00'), 60, 12),
('HIIT kardio', 'Kardio', 'Intervalni trening visokog intenziteta – 40 sekundi rada, 20 sekundi pauze.', 'Nikola Ilić', TIMESTAMP(CURDATE() + INTERVAL 1 DAY, '18:00:00'), 45, 15),
('Snaga – celo telo', 'Snaga', 'Osnovne vežbe sa tegovima: čučanj, mrtvo dizanje, potisak. Fokus na pravilnu tehniku.', 'Stefan Marković', TIMESTAMP(CURDATE() + INTERVAL 2 DAY, '19:00:00'), 60, 8),
('Pilates', 'Pilates', 'Jačanje core mišića i poboljšanje držanja tela.', 'Ana Jovanović', TIMESTAMP(CURDATE() + INTERVAL 3 DAY, '10:00:00'), 50, 10),
('CrossFit WOD', 'CrossFit', 'Trening dana – kombinacija gimnastike, dizanja tegova i kondicije.', 'Nikola Ilić', TIMESTAMP(CURDATE() + INTERVAL 4 DAY, '17:30:00'), 60, 12),
('Boks za početnike', 'Boks', 'Osnovni udarci, rad nogu i rad na džakovima. Rukavice obezbeđujemo.', 'Miloš Đorđević', TIMESTAMP(CURDATE() + INTERVAL 5 DAY, '20:00:00'), 75, 10),
('Funkcionalni trening', 'Snaga', 'Vežbe sa sopstvenom težinom, kettlebell i TRX.', 'Stefan Marković', TIMESTAMP(CURDATE() + INTERVAL 7 DAY, '18:30:00'), 60, 2);

INSERT INTO rezervacije (korisnik_id, trening_id) VALUES (2, 2), (2, 7);
