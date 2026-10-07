<?php
/**
 * Prijava, registracija i odjava korisnika.
 */
class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            redirect('');
        }
        $this->view('auth/login', ['pageTitle' => 'Prijava']);
    }

    public function login(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $lozinka = (string) ($_POST['lozinka'] ?? '');

        $user = (new User())->findByEmail($email);

        // password_verify poredi unetu lozinku sa hešom iz baze
        if (!$user || !password_verify($lozinka, $user['lozinka'])) {
            $_SESSION['old'] = ['email' => $email];
            flash('danger', 'Pogrešan email ili lozinka.');
            redirect('login');
        }

        Auth::login($user);
        flash('success', 'Dobro došli, ' . $user['ime'] . '!');
        redirect($user['uloga'] === 'admin' ? 'treninzi' : '');
    }

    public function registerForm(): void
    {
        if (Auth::check()) {
            redirect('');
        }
        $this->view('auth/register', ['pageTitle' => 'Registracija']);
    }

    public function register(): void
    {
        $ime = trim((string) ($_POST['ime'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $lozinka = (string) ($_POST['lozinka'] ?? '');
        $potvrda = (string) ($_POST['lozinka_potvrda'] ?? '');

        $users = new User();
        $errors = [];

        if (mb_strlen($ime) < 2 || mb_strlen($ime) > 100) {
            $errors[] = 'Ime mora imati između 2 i 100 karaktera.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email adresa nije ispravna.';
        } elseif ($users->emailExists($email)) {
            $errors[] = 'Nalog sa ovom email adresom već postoji.';
        }
        if (strlen($lozinka) < 8 || !preg_match('/\d/', $lozinka) || !preg_match('/[A-Za-z]/', $lozinka)) {
            $errors[] = 'Lozinka mora imati najmanje 8 karaktera, uključujući slova i brojeve.';
        }
        if ($lozinka !== $potvrda) {
            $errors[] = 'Lozinke se ne poklapaju.';
        }

        if ($errors) {
            $_SESSION['old'] = ['ime' => $ime, 'email' => $email];
            foreach ($errors as $err) {
                flash('danger', $err);
            }
            redirect('registracija');
        }

        $id = $users->create($ime, $email, $lozinka);
        Auth::login($users->find($id));
        flash('success', 'Nalog je napravljen. Sada možete rezervisati termine!');
        redirect('treninzi');
    }

    public function logout(): void
    {
        Auth::logout();
        flash('info', 'Odjavljeni ste.');
        redirect('');
    }
}
