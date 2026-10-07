<?php
$user = Auth::user();
$path = current_path();
$active = fn (string $p) => ($path === $p || ($p !== '/' && str_starts_with($path, $p))) ? 'active' : '';
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($pageTitle ?? '') ? $pageTitle . ' | ' . APP_NAME : APP_NAME) ?></title>

    <!-- Podaci koje koristi JavaScript -->
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <meta name="base-url" content="<?= e(base_path()) ?>">
    <meta name="user-role" content="<?= e($user['uloga'] ?? 'gost') ?>">
    <meta name="gym-location" content="<?= e(GYM_LAT . ',' . GYM_LON) ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= url('') ?>">
            <i class="bi bi-lightning-charge-fill text-warning"></i> <?= e(APP_NAME) ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Meni">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link <?= $active('/') ?>" href="<?= url('') ?>">Početna</a></li>
                <li class="nav-item"><a class="nav-link <?= $active('/treninzi') ?>" href="<?= url('treninzi') ?>">Raspored</a></li>
                <?php if ($user): ?>
                    <li class="nav-item"><a class="nav-link <?= $active('/moje-rezervacije') ?>" href="<?= url('moje-rezervacije') ?>">Moje rezervacije</a></li>
                <?php endif; ?>
                <?php if (Auth::isAdmin()): ?>
                    <li class="nav-item"><a class="nav-link <?= $active('/treninzi/novi') ?>" href="<?= url('treninzi/novi') ?>"><i class="bi bi-plus-circle"></i> Novi trening</a></li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav align-items-lg-center gap-lg-2">
                <?php if ($user): ?>
                    <li class="nav-item navbar-text small">
                        <i class="bi bi-person-circle"></i> <?= e($user['ime']) ?>
                        <?php if (Auth::isAdmin()): ?><span class="badge bg-warning text-dark ms-1">admin</span><?php endif; ?>
                    </li>
                    <li class="nav-item">
                        <form action="<?= url('logout') ?>" method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline-light btn-sm ms-lg-2 mt-2 mt-lg-0" type="submit">Odjava</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link <?= $active('/login') ?>" href="<?= url('login') ?>">Prijava</a></li>
                    <li class="nav-item"><a class="btn btn-warning btn-sm" href="<?= url('registracija') ?>">Registracija</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="flex-grow-1 py-4">
    <div class="container">
        <?php foreach (($flashes ?? []) as $f): ?>
            <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show" role="alert">
                <?= e($f['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zatvori"></button>
            </div>
        <?php endforeach; ?>
