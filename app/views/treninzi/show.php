<?php
$slobodno = max(0, (int) $trening['kapacitet'] - (int) $trening['zauzeto']);
$procenat = $trening['kapacitet'] > 0 ? round($trening['zauzeto'] / $trening['kapacitet'] * 100) : 100;
$proslo = strtotime($trening['datum_vreme']) < time();
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('treninzi') ?>">Raspored</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($trening['naziv']) ?></li>
    </ol>
</nav>

<div class="row g-4" data-trening-card="<?= (int) $trening['id'] ?>">
    <div class="col-lg-7">
        <?php if ($trening['slika']): ?>
            <img src="<?= e(upload_url($trening['slika'])) ?>" class="img-fluid rounded-4 shadow-sm w-100 detail-img" alt="<?= e($trening['naziv']) ?>">
        <?php else: ?>
            <div class="trening-placeholder detail-img rounded-4 kat-<?= e(strtolower($trening['kategorija'])) ?>">
                <i class="bi bi-activity"></i>
            </div>
        <?php endif; ?>

        <h1 class="h2 mt-4"><?= e($trening['naziv']) ?></h1>
        <span class="badge text-bg-dark mb-3"><?= e($trening['kategorija']) ?></span>
        <p class="lead"><?= nl2br(e($trening['opis'] ?? '')) ?></p>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <ul class="list-unstyled mb-4">
                    <li class="mb-2"><i class="bi bi-calendar-event me-2"></i><?= e(dan_u_nedelji($trening['datum_vreme'])) ?>, <?= e(format_datum($trening['datum_vreme'])) ?></li>
                    <li class="mb-2"><i class="bi bi-clock me-2"></i><?= (int) $trening['trajanje_min'] ?> minuta</li>
                    <li class="mb-2"><i class="bi bi-person me-2"></i>Trener: <?= e($trening['trener']) ?></li>
                </ul>

                <div class="d-flex justify-content-between small mb-1">
                    <span>Slobodna mesta</span>
                    <strong data-slobodno><?= $slobodno ?> / <?= (int) $trening['kapacitet'] ?></strong>
                </div>
                <div class="progress mb-4" style="height: 8px;">
                    <div class="progress-bar <?= $slobodno === 0 ? 'bg-danger' : 'bg-warning' ?>" data-progress style="width: <?= $procenat ?>%"></div>
                </div>

                <?php if ($proslo): ?>
                    <div class="alert alert-secondary mb-0">Ovaj trening je završen.</div>
                <?php elseif (!Auth::check()): ?>
                    <a href="<?= url('login') ?>" class="btn btn-warning w-100">Prijavi se za rezervaciju</a>
                <?php elseif (!Auth::isAdmin()): ?>
                    <?php if ($rezervisano): ?>
                        <button class="btn btn-danger w-100" data-action="otkazi" data-id="<?= (int) $trening['id'] ?>">Otkaži rezervaciju</button>
                    <?php else: ?>
                        <button class="btn btn-warning w-100" data-action="rezervisi" data-id="<?= (int) $trening['id'] ?>" <?= $slobodno === 0 ? 'disabled' : '' ?>>
                            <?= $slobodno === 0 ? 'Popunjeno' : 'Rezerviši mesto' ?>
                        </button>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (Auth::isAdmin()): ?>
                    <div class="d-flex gap-2">
                        <a href="<?= url('treninzi/' . $trening['id'] . '/izmeni') ?>" class="btn btn-outline-dark flex-fill"><i class="bi bi-pencil"></i> Izmeni</a>
                        <form action="<?= url('treninzi/' . $trening['id'] . '/obrisi') ?>" method="post" class="flex-fill"
                              onsubmit="return confirm('Obrisati ovaj trening i sve rezervacije?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline-danger w-100"><i class="bi bi-trash"></i> Obriši</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Spoljni veb servis: prognoza za vreme treninga (Open-Meteo), popunjava app.js -->
        <div class="card border-0 shadow-sm rounded-4 mb-4" data-forecast data-datetime="<?= e(date('c', strtotime($trening['datum_vreme']))) ?>">
            <div class="card-body p-4">
                <h2 class="h6 text-muted mb-3"><i class="bi bi-cloud-sun"></i> Prognoza u vreme treninga (<?= e(GYM_CITY) ?>)</h2>
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-hourglass-split fs-1" data-weather-icon></i>
                    <div>
                        <div class="fs-4 fw-bold" data-weather-temp></div>
                        <div data-weather-desc class="text-muted">Učitavanje…</div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (Auth::isAdmin()): ?>
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h2 class="h6 text-muted mb-3"><i class="bi bi-people"></i> Prijavljeni članovi (<?= count($ucesnici) ?>)</h2>
                    <?php if (!$ucesnici): ?>
                        <p class="text-muted small mb-0">Još niko nije rezervisao ovaj termin.</p>
                    <?php else: ?>
                        <ol class="small mb-0">
                            <?php foreach ($ucesnici as $u): ?>
                                <li><?= e($u['ime']) ?> <span class="text-muted">(<?= e($u['email']) ?>)</span></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
