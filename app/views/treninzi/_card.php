<?php
/**
 * Kartica jednog treninga (koristi se na početnoj i u rasporedu).
 * Isti izgled pravi i JavaScript (renderCard u app.js) kad se lista učita preko AJAX-a.
 * @var array $t
 * @var array $rezervisani
 */
$slobodno = max(0, (int) $t['kapacitet'] - (int) $t['zauzeto']);
$procenat = $t['kapacitet'] > 0 ? round($t['zauzeto'] / $t['kapacitet'] * 100) : 100;
$proslo = strtotime($t['datum_vreme']) < time();
$jeRezervisan = in_array((int) $t['id'], $rezervisani, true);
?>
<div class="col" data-trening-card="<?= (int) $t['id'] ?>">
    <div class="card h-100 shadow-sm trening-card">
        <?php if ($t['slika']): ?>
            <img src="<?= e(upload_url($t['slika'])) ?>" class="card-img-top trening-img" alt="<?= e($t['naziv']) ?>">
        <?php else: ?>
            <div class="trening-img trening-placeholder kat-<?= e(strtolower($t['kategorija'])) ?>">
                <i class="bi bi-activity"></i>
            </div>
        <?php endif; ?>

        <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <span class="badge text-bg-dark"><?= e($t['kategorija']) ?></span>
                <?php if ($proslo): ?><span class="badge text-bg-secondary">Završen</span><?php endif; ?>
            </div>
            <h3 class="h5 card-title mb-1"><?= e($t['naziv']) ?></h3>
            <p class="small text-muted mb-2">
                <i class="bi bi-calendar-event"></i> <?= e(dan_u_nedelji($t['datum_vreme'])) ?>, <?= e(format_datum($t['datum_vreme'])) ?><br>
                <i class="bi bi-person"></i> <?= e($t['trener']) ?> &middot; <i class="bi bi-clock"></i> <?= (int) $t['trajanje_min'] ?> min
            </p>

            <div class="mt-auto">
                <div class="d-flex justify-content-between small mb-1">
                    <span>Slobodna mesta</span>
                    <strong data-slobodno><?= $slobodno ?> / <?= (int) $t['kapacitet'] ?></strong>
                </div>
                <div class="progress mb-3" style="height: 6px;">
                    <div class="progress-bar <?= $slobodno === 0 ? 'bg-danger' : 'bg-warning' ?>" data-progress style="width: <?= $procenat ?>%"></div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= url('treninzi/' . $t['id']) ?>" class="btn btn-outline-dark btn-sm">Detalji</a>
                    <?php if (Auth::check() && !Auth::isAdmin() && !$proslo): ?>
                        <?php if ($jeRezervisan): ?>
                            <button class="btn btn-danger btn-sm" data-action="otkazi" data-id="<?= (int) $t['id'] ?>">Otkaži</button>
                        <?php else: ?>
                            <button class="btn btn-warning btn-sm" data-action="rezervisi" data-id="<?= (int) $t['id'] ?>" <?= $slobodno === 0 ? 'disabled' : '' ?>>
                                <?= $slobodno === 0 ? 'Popunjeno' : 'Rezerviši' ?>
                            </button>
                        <?php endif; ?>
                    <?php elseif (!Auth::check() && !$proslo): ?>
                        <a href="<?= url('login') ?>" class="btn btn-warning btn-sm">Prijavi se za rezervaciju</a>
                    <?php endif; ?>
                    <?php if (Auth::isAdmin()): ?>
                        <a href="<?= url('treninzi/' . $t['id'] . '/izmeni') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i></a>
                        <button class="btn btn-outline-danger btn-sm" data-action="obrisi" data-id="<?= (int) $t['id'] ?>" data-naziv="<?= e($t['naziv']) ?>"><i class="bi bi-trash"></i></button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
