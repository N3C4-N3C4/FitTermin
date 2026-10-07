<section class="hero rounded-4 p-4 p-md-5 mb-4 text-white">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <h1 class="display-5 fw-bold">Rezerviši svoj termin u teretani</h1>
            <p class="lead mb-4">Joga, HIIT, snaga, boks i još mnogo toga. Pogledaj raspored, izaberi trening
                i rezerviši mesto u par klikova – bez poziva i čekanja.</p>
            <a href="<?= url('treninzi') ?>" class="btn btn-warning btn-lg me-2"><i class="bi bi-calendar-check"></i> Pogledaj raspored</a>
            <?php if (!Auth::check()): ?>
                <a href="<?= url('registracija') ?>" class="btn btn-outline-light btn-lg">Napravi nalog</a>
            <?php endif; ?>
        </div>

        <!-- Spoljni veb servis: trenutno vreme (Open-Meteo), popunjava app.js -->
        <div class="col-lg-5">
            <div class="card bg-white bg-opacity-10 border-0 text-white" id="weatherNow">
                <div class="card-body">
                    <div class="small text-white-50 mb-1"><i class="bi bi-geo-alt"></i> <?= e(GYM_CITY) ?> – trenutno vreme</div>
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-cloud-sun display-5" data-weather-icon></i>
                        <div>
                            <div class="display-6 fw-bold" data-weather-temp>--°C</div>
                            <div data-weather-desc>Učitavanje prognoze…</div>
                        </div>
                    </div>
                    <div class="small mt-2 text-white-50" data-weather-tip></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="row g-3 mb-5 text-center">
    <div class="col-md-4">
        <div class="p-4 bg-white rounded-4 shadow-sm h-100">
            <i class="bi bi-calendar2-week fs-1 text-warning"></i>
            <h2 class="h5 mt-2">Raspored uvek pri ruci</h2>
            <p class="text-muted mb-0">Svi grupni treninzi na jednom mestu, sa brojem slobodnih mesta u realnom vremenu.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-4 bg-white rounded-4 shadow-sm h-100">
            <i class="bi bi-hand-index-thumb fs-1 text-warning"></i>
            <h2 class="h5 mt-2">Rezervacija jednim klikom</h2>
            <p class="text-muted mb-0">Rezerviši ili otkaži termin bez osvežavanja stranice.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-4 bg-white rounded-4 shadow-sm h-100">
            <i class="bi bi-people fs-1 text-warning"></i>
            <h2 class="h5 mt-2">Bez gužve</h2>
            <p class="text-muted mb-0">Ograničen broj mesta po treningu – trener ima vremena za svakog.</p>
        </div>
    </div>
</section>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Sledeći treninzi</h2>
    <a href="<?= url('treninzi') ?>" class="link-dark">Ceo raspored <i class="bi bi-arrow-right"></i></a>
</div>

<?php if (!$treninzi): ?>
    <p class="text-muted">Trenutno nema zakazanih treninga.</p>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($treninzi as $t): ?>
            <?php require ROOT . '/app/views/treninzi/_card.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
