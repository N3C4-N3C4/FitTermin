<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <h1 class="h3 mb-4 text-center">Prijava</h1>
                <form action="<?= url('login') ?>" method="post" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= e(old('email')) ?>" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label for="lozinka" class="form-label">Lozinka</label>
                        <input type="password" class="form-control" id="lozinka" name="lozinka" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Prijavi se</button>
                </form>
                <p class="text-center small mt-4 mb-0">Nemaš nalog? <a href="<?= url('registracija') ?>">Registruj se</a></p>
            </div>
        </div>
    </div>
</div>
