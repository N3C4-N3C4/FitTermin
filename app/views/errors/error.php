<div class="text-center py-5">
    <h1 class="display-1 fw-bold text-warning"><?= (int) $code ?></h1>
    <h2 class="h4 mb-3"><?= e($title) ?></h2>
    <?php if (!empty($message)): ?>
        <p class="text-muted"><?= e($message) ?></p>
    <?php endif; ?>
    <a href="<?= url('') ?>" class="btn btn-dark mt-3"><i class="bi bi-house"></i> Nazad na početnu</a>
</div>
