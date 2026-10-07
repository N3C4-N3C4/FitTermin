<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <h1 class="h3 mb-4 text-center">Registracija</h1>
                <form action="<?= url('registracija') ?>" method="post" id="registerForm" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="ime" class="form-label">Ime i prezime</label>
                        <input type="text" class="form-control" id="ime" name="ime" value="<?= e(old('ime')) ?>"
                               minlength="2" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= e(old('email')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="lozinka" class="form-label">Lozinka</label>
                        <input type="password" class="form-control" id="lozinka" name="lozinka" minlength="8" required>
                        <!-- Indikator jačine lozinke – menja ga JavaScript dok korisnik kuca -->
                        <div class="progress mt-2" style="height: 5px;">
                            <div class="progress-bar" id="passwordStrengthBar" style="width: 0"></div>
                        </div>
                        <div class="form-text" id="passwordStrengthText">Najmanje 8 karaktera, slova i brojevi.</div>
                    </div>
                    <div class="mb-4">
                        <label for="lozinka_potvrda" class="form-label">Ponovi lozinku</label>
                        <input type="password" class="form-control" id="lozinka_potvrda" name="lozinka_potvrda" required>
                        <div class="invalid-feedback">Lozinke se ne poklapaju.</div>
                    </div>
                    <button type="submit" class="btn btn-warning w-100">Napravi nalog</button>
                </form>
                <p class="text-center small mt-4 mb-0">Već imaš nalog? <a href="<?= url('login') ?>">Prijavi se</a></p>
            </div>
        </div>
    </div>
</div>
