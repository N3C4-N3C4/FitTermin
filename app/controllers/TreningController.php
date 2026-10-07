<?php
/**
 * CRUD nad treninzima.
 * Pregled je dostupan svima, a dodavanje, izmena i brisanje samo administratoru.
 */
class TreningController extends Controller
{
    private Trening $treninzi;

    public function __construct()
    {
        $this->treninzi = new Trening();
    }

    /** READ – lista (filtriranje se radi preko AJAX-a, vidi ApiController::treninzi) */
    public function index(): void
    {
        $filteri = [
            'q'          => trim((string) ($_GET['q'] ?? '')),
            'kategorija' => (string) ($_GET['kategorija'] ?? ''),
            'buduci'     => !Auth::isAdmin(), // admin vidi i prošle termine
        ];
        $rezervisani = Auth::check() ? (new Rezervacija())->treningIdsZaKorisnika(Auth::id()) : [];

        $this->view('treninzi/index', [
            'pageTitle'   => 'Raspored treninga',
            'treninzi'    => $this->treninzi->all($filteri),
            'filteri'     => $filteri,
            'kategorije'  => Trening::KATEGORIJE,
            'rezervisani' => $rezervisani,
        ]);
    }

    /** READ – detalji jednog treninga */
    public function show(int $id): void
    {
        $trening = $this->treninzi->find($id) ?? abort(404, 'Trening ne postoji.');
        $rez = new Rezervacija();

        $this->view('treninzi/show', [
            'pageTitle'   => $trening['naziv'],
            'trening'     => $trening,
            'rezervisano' => Auth::check() && $rez->postoji(Auth::id(), $id),
            'ucesnici'    => Auth::isAdmin() ? $rez->ucesnici($id) : [],
        ]);
    }

    /** CREATE – forma */
    public function create(): void
    {
        $this->requireAdmin();
        $this->view('treninzi/form', [
            'pageTitle'  => 'Novi trening',
            'trening'    => null,
            'kategorije' => Trening::KATEGORIJE,
            'errors'     => $_SESSION['errors'] ?? [],
        ]);
        unset($_SESSION['errors']);
    }

    /** CREATE – snimanje */
    public function store(): void
    {
        $this->requireAdmin();
        [$data, $errors] = Trening::validate($_POST);

        if ($errors) {
            $this->vratiNaFormu($errors, 'treninzi/novi');
        }

        $id = $this->treninzi->create($data);
        flash('success', 'Trening je dodat.');
        redirect('treninzi/' . $id);
    }

    /** UPDATE – forma */
    public function edit(int $id): void
    {
        $this->requireAdmin();
        $trening = $this->treninzi->find($id) ?? abort(404, 'Trening ne postoji.');

        $this->view('treninzi/form', [
            'pageTitle'  => 'Izmena treninga',
            'trening'    => $trening,
            'kategorije' => Trening::KATEGORIJE,
            'errors'     => $_SESSION['errors'] ?? [],
        ]);
        unset($_SESSION['errors']);
    }

    /** UPDATE – snimanje */
    public function update(int $id): void
    {
        $this->requireAdmin();
        $trening = $this->treninzi->find($id) ?? abort(404, 'Trening ne postoji.');
        [$data, $errors] = Trening::validate($_POST);

        if ($errors) {
            $this->vratiNaFormu($errors, "treninzi/$id/izmeni");
        }

        $this->treninzi->update($id, $data);

        // Ako je slika zamenjena ili uklonjena, obriši staru sa diska
        if ($trening['slika'] && $trening['slika'] !== $data['slika']) {
            Trening::deleteImage($trening['slika']);
        }

        flash('success', 'Izmene su sačuvane.');
        redirect('treninzi/' . $id);
    }

    /** DELETE – klasična forma (ako JavaScript nije dostupan); AJAX verzija je u ApiController */
    public function destroy(int $id): void
    {
        $this->requireAdmin();
        if ($this->treninzi->delete($id)) {
            flash('success', 'Trening je obrisan.');
        }
        redirect('treninzi');
    }

    private function vratiNaFormu(array $errors, string $path): never
    {
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $_POST;
        unset($_SESSION['old']['_csrf']);
        flash('danger', 'Proverite unete podatke.');
        redirect($path);
    }
}
