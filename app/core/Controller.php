<?php
/**
 * Osnovna klasa za kontrolere: prikaz view-a, JSON odgovor i kontrola pristupa.
 */
abstract class Controller
{
    /** Prikazuje view unutar layout-a (header + sadržaj + footer). */
    protected function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $flashes = get_flashes();

        require ROOT . '/app/views/layouts/header.php';
        require ROOT . "/app/views/$view.php";
        require ROOT . '/app/views/layouts/footer.php';

        unset($_SESSION['old']);
    }

    /** JSON odgovor – koristi se za veb servise / AJAX. */
    protected function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Samo ulogovani korisnici */
    protected function requireLogin(): void
    {
        if (!Auth::check()) {
            if (is_ajax()) {
                $this->json(['success' => false, 'message' => 'Morate biti prijavljeni.'], 401);
            }
            flash('warning', 'Prijavite se da biste nastavili.');
            redirect('login');
        }
    }

    /** Samo administrator */
    protected function requireAdmin(): void
    {
        $this->requireLogin();
        if (!Auth::isAdmin()) {
            abort(403, 'Ova akcija je dozvoljena samo administratoru.');
        }
    }
}
