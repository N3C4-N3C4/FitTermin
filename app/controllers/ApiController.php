<?php
/**
 * Veb servisi aplikacije (REST/JSON). Poziva ih JavaScript preko AJAX-a (fetch),
 * pa se stranica ne osvežava.
 */
class ApiController extends Controller
{
    /** GET /api/treninzi?q=&kategorija= – pretraga i filtriranje */
    public function treninzi(): void
    {
        $filteri = [
            'q'          => trim((string) ($_GET['q'] ?? '')),
            'kategorija' => (string) ($_GET['kategorija'] ?? ''),
            'buduci'     => !Auth::isAdmin(),
        ];
        $rezervisani = Auth::check() ? (new Rezervacija())->treningIdsZaKorisnika(Auth::id()) : [];

        $lista = array_map(
            fn (array $t) => Trening::toArray($t, $rezervisani),
            (new Trening())->all($filteri)
        );

        $this->json(['success' => true, 'count' => count($lista), 'data' => $lista]);
    }

    /** POST /api/rezervacije  { trening_id } – rezervacija termina */
    public function rezervisi(): void
    {
        $this->requireLogin();
        $treningId = (int) ($_POST['trening_id'] ?? 0);

        $status = (new Rezervacija())->rezervisi(Auth::id(), $treningId);
        $poruke = [
            'ok'              => 'Termin je rezervisan!',
            'ne_postoji'      => 'Trening ne postoji.',
            'proslo'          => 'Ovaj termin je već prošao.',
            'popunjeno'       => 'Nažalost, termin je popunjen.',
            'vec_rezervisano' => 'Već ste rezervisali ovaj termin.',
        ];

        $this->json(
            ['success' => $status === 'ok', 'message' => $poruke[$status], 'trening' => $this->stanje($treningId)],
            $status === 'ok' ? 200 : 422
        );
    }

    /** POST /api/rezervacije/{id}/otkazi – otkazivanje (id = id treninga) */
    public function otkazi(int $treningId): void
    {
        $this->requireLogin();
        $ok = (new Rezervacija())->otkazi(Auth::id(), $treningId);

        $this->json(
            [
                'success' => $ok,
                'message' => $ok ? 'Rezervacija je otkazana.' : 'Rezervacija nije pronađena.',
                'trening' => $this->stanje($treningId),
            ],
            $ok ? 200 : 404
        );
    }

    /** POST /api/treninzi/{id}/obrisi – brisanje treninga (admin) */
    public function obrisiTrening(int $id): void
    {
        $this->requireAdmin();
        $ok = (new Trening())->delete($id);
        $this->json(
            ['success' => $ok, 'message' => $ok ? 'Trening je obrisan.' : 'Trening ne postoji.'],
            $ok ? 200 : 404
        );
    }

    /**
     * POST /api/upload – otpremanje slike (multipart/form-data, polje "slika").
     * Vraća ime fajla koje forma zatim šalje zajedno sa ostalim podacima.
     */
    public function upload(): void
    {
        $this->requireAdmin();
        $file = $_FILES['slika'] ?? null;

        if (!$file || !is_array($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'message' => 'Slika nije primljena.'], 422);
        }
        if (is_array($file['error'])) {
            $this->json(['success' => false, 'message' => 'Pošaljite jednu sliku.'], 422);
        }
        if ($file['size'] > UPLOAD_MAX_BYTES) {
            $this->json(['success' => false, 'message' => 'Slika je veća od 2 MB.'], 422);
        }

        // Tip fajla proveravamo po sadržaju, ne po ekstenziji koju šalje korisnik
        $dozvoljeni = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset($dozvoljeni[$mime]) || @getimagesize($file['tmp_name']) === false) {
            $this->json(['success' => false, 'message' => 'Dozvoljeni formati su JPG, PNG, WEBP i GIF.'], 422);
        }

        // Nasumično ime – sprečava prepisivanje i pogađanje imena fajlova
        $ime = bin2hex(random_bytes(16)) . '.' . $dozvoljeni[$mime];
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }
        if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . '/' . $ime)) {
            $this->json(['success' => false, 'message' => 'Greška pri čuvanju slike.'], 500);
        }

        $this->json([
            'success' => true,
            'message' => 'Slika je otpremljena.',
            'file'    => $ime,
            'url'     => upload_url($ime),
        ]);
    }

    /** Trenutno stanje treninga (broj slobodnih mesta) za osvežavanje prikaza */
    private function stanje(int $treningId): ?array
    {
        $t = (new Trening())->find($treningId);
        if (!$t) {
            return null;
        }
        return Trening::toArray($t, (new Rezervacija())->treningIdsZaKorisnika(Auth::id()));
    }
}
