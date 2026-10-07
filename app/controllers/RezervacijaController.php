<?php
class RezervacijaController extends Controller
{
    /** Lista rezervacija prijavljenog korisnika */
    public function index(): void
    {
        $this->requireLogin();
        $this->view('rezervacije/index', [
            'pageTitle'   => 'Moje rezervacije',
            'rezervacije' => (new Rezervacija())->zaKorisnika(Auth::id()),
        ]);
    }
}
