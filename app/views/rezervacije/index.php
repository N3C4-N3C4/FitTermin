<h1 class="h3 mb-4">Moje rezervacije</h1>

<?php
$predstojece = array_filter($rezervacije, fn ($r) => strtotime($r['datum_vreme']) >= time());
$prosle = array_filter($rezervacije, fn ($r) => strtotime($r['datum_vreme']) < time());
?>

<div class="text-center text-muted py-5 <?= $predstojece ? 'd-none' : '' ?>" id="nemaRezervacija">
    <i class="bi bi-calendar-x fs-1"></i>
    <p class="mt-2">Nemate predstojećih rezervacija.</p>
    <a href="<?= url('treninzi') ?>" class="btn btn-warning">Pogledaj raspored</a>
</div>

<?php if ($predstojece): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Trening</th>
                    <th>Termin</th>
                    <th class="d-none d-md-table-cell">Trener</th>
                    <th class="text-end">Akcija</th>
                </tr>
                </thead>
                <tbody id="rezervacijeTabela">
                <?php foreach ($predstojece as $r): ?>
                    <tr data-rezervacija-row="<?= (int) $r['id'] ?>">
                        <td>
                            <a href="<?= url('treninzi/' . $r['id']) ?>" class="fw-semibold link-dark"><?= e($r['naziv']) ?></a><br>
                            <span class="badge text-bg-light"><?= e($r['kategorija']) ?></span>
                        </td>
                        <td><?= e(dan_u_nedelji($r['datum_vreme'])) ?>, <?= e(format_datum($r['datum_vreme'])) ?></td>
                        <td class="d-none d-md-table-cell"><?= e($r['trener']) ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-danger" data-action="otkazi" data-id="<?= (int) $r['id'] ?>" data-remove-row>Otkaži</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php if ($prosle): ?>
    <h2 class="h5 text-muted mt-5 mb-3">Završeni treninzi</h2>
    <ul class="list-group">
        <?php foreach ($prosle as $r): ?>
            <li class="list-group-item d-flex justify-content-between">
                <span><?= e($r['naziv']) ?></span>
                <span class="text-muted small"><?= e(format_datum($r['datum_vreme'])) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
