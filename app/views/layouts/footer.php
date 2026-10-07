    </div>
</main>

<footer class="bg-dark text-white-50 py-3 mt-auto">
    <div class="container d-flex flex-column flex-md-row justify-content-between small gap-2">
        <span>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?> – rezervacija termina u teretani</span>
        <span>Vremenska prognoza: <a class="link-light" href="https://open-meteo.com/" target="_blank" rel="noopener">Open-Meteo</a></span>
    </div>
</footer>

<!-- Kontejner za toast obaveštenja (popunjava ga JavaScript) -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>
