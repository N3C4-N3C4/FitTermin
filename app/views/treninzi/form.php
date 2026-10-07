<?php
/**
 * Forma za dodavanje i izmenu treninga.
 * @var array|null $trening  null = novi trening
 */
$isEdit = $trening !== null;
$action = $isEdit ? url('treninzi/' . $trening['id'] . '/izmeni') : url('treninzi');

// Vrednost polja: stari unos (posle greške) > postojeći podatak > podrazumevano
$val = fn (string $key, $default = '') => old($key, $trening[$key] ?? $default);

$datum = old('datum_vreme', $isEdit ? date('Y-m-d\TH:i', strtotime($trening['datum_vreme'])) : '');
$slika = $val('slika', null);
$err = fn (string $key) => isset($errors[$key]) ? 'is-invalid' : '';
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h1 class="h3 mb-4"><?= $isEdit ? 'Izmena treninga' : 'Novi trening' ?></h1>

        <form action="<?= $action ?>" method="post" class="card border-0 shadow-sm rounded-4" novalidate>
            <?= csrf_field() ?>
            <div class="card-body p-4 row g-3">
                <div class="col-md-8">
                    <label for="naziv" class="form-label">Naziv *</label>
                    <input type="text" class="form-control <?= $err('naziv') ?>" id="naziv" name="naziv"
                           value="<?= e($val('naziv')) ?>" maxlength="120" required>
                    <div class="invalid-feedback"><?= e($errors['naziv'] ?? '') ?></div>
                </div>
                <div class="col-md-4">
                    <label for="kategorija" class="form-label">Kategorija *</label>
                    <select class="form-select <?= $err('kategorija') ?>" id="kategorija" name="kategorija" required>
                        <option value="">Izaberi…</option>
                        <?php foreach ($kategorije as $k): ?>
                            <option value="<?= e($k) ?>" <?= $val('kategorija') === $k ? 'selected' : '' ?>><?= e($k) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"><?= e($errors['kategorija'] ?? '') ?></div>
                </div>

                <div class="col-12">
                    <label for="opis" class="form-label">Opis</label>
                    <textarea class="form-control" id="opis" name="opis" rows="4"><?= e($val('opis')) ?></textarea>
                </div>

                <div class="col-md-6">
                    <label for="trener" class="form-label">Trener *</label>
                    <input type="text" class="form-control <?= $err('trener') ?>" id="trener" name="trener"
                           value="<?= e($val('trener')) ?>" maxlength="100" required>
                    <div class="invalid-feedback"><?= e($errors['trener'] ?? '') ?></div>
                </div>
                <div class="col-md-6">
                    <label for="datum_vreme" class="form-label">Datum i vreme *</label>
                    <input type="datetime-local" class="form-control <?= $err('datum_vreme') ?>" id="datum_vreme" name="datum_vreme"
                           value="<?= e($datum) ?>" required>
                    <div class="invalid-feedback"><?= e($errors['datum_vreme'] ?? '') ?></div>
                </div>
                <div class="col-md-6">
                    <label for="trajanje_min" class="form-label">Trajanje (min) *</label>
                    <input type="number" class="form-control <?= $err('trajanje_min') ?>" id="trajanje_min" name="trajanje_min"
                           value="<?= e($val('trajanje_min', 60)) ?>" min="15" max="240" step="5" required>
                    <div class="invalid-feedback"><?= e($errors['trajanje_min'] ?? '') ?></div>
                </div>
                <div class="col-md-6">
                    <label for="kapacitet" class="form-label">Broj mesta *</label>
                    <input type="number" class="form-control <?= $err('kapacitet') ?>" id="kapacitet" name="kapacitet"
                           value="<?= e($val('kapacitet', 10)) ?>" min="1" max="100" required>
                    <div class="invalid-feedback"><?= e($errors['kapacitet'] ?? '') ?></div>
                </div>

                <!-- Upload slike preko veb servisa /api/upload (AJAX) -->
                <div class="col-12">
                    <label for="slikaInput" class="form-label">Slika</label>
                    <input type="file" class="form-control <?= $err('slika') ?>" id="slikaInput" accept="image/jpeg,image/png,image/webp,image/gif"
                           data-upload-url="<?= url('api/upload') ?>">
                    <div class="invalid-feedback"><?= e($errors['slika'] ?? '') ?></div>
                    <div class="form-text">JPG, PNG, WEBP ili GIF, najviše 2 MB. Slika se otprema odmah po izboru.</div>
                    <input type="hidden" name="slika" id="slikaFile" value="<?= e($slika ?? '') ?>">

                    <div class="progress mt-2 d-none" id="uploadProgress" style="height: 6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" style="width: 100%"></div>
                    </div>
                    <div class="mt-3 <?= $slika ? '' : 'd-none' ?>" id="slikaPreviewWrap">
                        <img src="<?= $slika ? e(upload_url($slika)) : '' ?>" id="slikaPreview" class="img-thumbnail" style="max-height: 200px" alt="Pregled slike">
                        <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="slikaUkloni"><i class="bi bi-x-lg"></i> Ukloni sliku</button>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-0 p-4 pt-0 d-flex gap-2">
                <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg"></i> Sačuvaj</button>
                <a href="<?= $isEdit ? url('treninzi/' . $trening['id']) : url('treninzi') ?>" class="btn btn-outline-secondary">Otkaži</a>
            </div>
        </form>
    </div>
</div>
