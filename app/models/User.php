<?php
/**
 * Model korisnika (tabela "korisnici").
 */
class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM korisnici WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM korisnici WHERE email = ?');
        $stmt->execute([$email]);
        return (bool) $stmt->fetchColumn();
    }

    /** Novi korisnik – lozinka se čuva isključivo kao heš. */
    public function create(string $ime, string $email, string $lozinka): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO korisnici (ime, email, lozinka, uloga) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$ime, $email, password_hash($lozinka, PASSWORD_DEFAULT), 'clan']);
        return (int) $this->db->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM korisnici WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
