<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Raspored treninga</h1>
    <?php if (Auth::isAdmin()): ?>
        <a href="<?= url('treninzi/novi') ?>" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Novi trening</a>
    <?php endif; ?>
</div>

<!-- Filteri: forma radi i bez JS-a (GET), a sa JS-om se rezultati učitavaju preko AJAX-a -->
<form class="row g-2 mb-4" id="filterForm" action="<?= url('treninzi') ?>" method="get" role="search">
    <div class="col-md-7">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="search" class="form-control" name="q" id="pretraga" placeholder="Pretraži po nazivu, treneru ili opisu…"
                   value="<?= e($filteri['q']) ?>" autocomplete="off">
        </div>
    </div>
    <div class="col-md-3">
        <select class="form-select" name="kategorija" id="kategorija">
            <option value="">Sve kategorije</option>
            <?php foreach ($kategorije as $k): ?>
                <option value="<?= e($k) ?>" <?= $filteri['kategorija'] === $k ? 'selected' : '' ?>><?= e($k) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2 d-grid">
        <button class="btn btn-outline-dark" type="submit">Filtriraj</button>
    </div>
</form>

<p class="small text-muted mb-3" id="resultCount">Pronađeno treninga: <?= count($treninzi) ?></p>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="treninziLista">
    <?php foreach ($treninzi as $t): ?>
        <?php require ROOT . '/app/views/treninzi/_card.php'; ?>
    <?php endforeach; ?>
</div>

<div class="text-center text-muted py-5 <?= $treninzi ? 'd-none' : '' ?>" id="praznaLista">
    <i class="bi bi-emoji-neutral fs-1"></i>
    <p class="mt-2">Nema treninga koji odgovaraju pretrazi.</p>
</div>
