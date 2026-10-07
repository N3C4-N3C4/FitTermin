<?php
class HomeController extends Controller
{
    public function index(): void
    {
        $treninzi = (new Trening())->all(['buduci' => true, 'limit' => 3]);
        $rezervisani = Auth::check() ? (new Rezervacija())->treningIdsZaKorisnika(Auth::id()) : [];

        $this->view('home/index', [
            'pageTitle'   => 'Početna',
            'treninzi'    => $treninzi,
            'rezervisani' => $rezervisani,
        ]);
    }
}
