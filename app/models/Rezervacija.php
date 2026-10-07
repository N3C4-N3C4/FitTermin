<?php
/**
 * Model rezervacija (tabela "rezervacije") – veza korisnika i treninga.
 */
class Rezervacija extends Model
{
    /**
     * Rezerviše mesto na treningu. Koristi transakciju i zaključavanje reda
     * (SELECT ... FOR UPDATE) da dva korisnika ne bi istovremeno zauzela poslednje mesto.
     * @return string 'ok' | 'ne_postoji' | 'proslo' | 'popunjeno' | 'vec_rezervisano'
     */
    public function rezervisi(int $korisnikId, int $treningId): string
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT kapacitet, datum_vreme FROM treninzi WHERE id = ? FOR UPDATE');
            $stmt->execute([$treningId]);
            $trening = $stmt->fetch();

            if (!$trening) {
                $this->db->rollBack();
                return 'ne_postoji';
            }
            if (strtotime($trening['datum_vreme']) < time()) {
                $this->db->rollBack();
                return 'proslo';
            }
            if ($this->postoji($korisnikId, $treningId)) {
                $this->db->rollBack();
                return 'vec_rezervisano';
            }
            if ($this->brojRezervacija($treningId) >= (int) $trening['kapacitet']) {
                $this->db->rollBack();
                return 'popunjeno';
            }

            $stmt = $this->db->prepare('INSERT INTO rezervacije (korisnik_id, trening_id) VALUES (?, ?)');
            $stmt->execute([$korisnikId, $treningId]);
            $this->db->commit();
            return 'ok';
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function otkazi(int $korisnikId, int $treningId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM rezervacije WHERE korisnik_id = ? AND trening_id = ?');
        $stmt->execute([$korisnikId, $treningId]);
        return $stmt->rowCount() > 0;
    }

    public function postoji(int $korisnikId, int $treningId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM rezervacije WHERE korisnik_id = ? AND trening_id = ?');
        $stmt->execute([$korisnikId, $treningId]);
        return (bool) $stmt->fetchColumn();
    }

    public function brojRezervacija(int $treningId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM rezervacije WHERE trening_id = ?');
        $stmt->execute([$treningId]);
        return (int) $stmt->fetchColumn();
    }

    /** ID-jevi treninga koje je korisnik rezervisao (za označavanje u listi) */
    public function treningIdsZaKorisnika(int $korisnikId): array
    {
        $stmt = $this->db->prepare('SELECT trening_id FROM rezervacije WHERE korisnik_id = ?');
        $stmt->execute([$korisnikId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /** Sve rezervacije korisnika sa podacima o treningu */
    public function zaKorisnika(int $korisnikId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.id AS rezervacija_id, r.kreiran AS rezervisano, t.*
             FROM rezervacije r
             JOIN treninzi t ON t.id = r.trening_id
             WHERE r.korisnik_id = ?
             ORDER BY t.datum_vreme ASC'
        );
        $stmt->execute([$korisnikId]);
        return $stmt->fetchAll();
    }

    /** Spisak prijavljenih na trening (vidi admin) */
    public function ucesnici(int $treningId): array
    {
        $stmt = $this->db->prepare(
            'SELECT k.ime, k.email, r.kreiran
             FROM rezervacije r
             JOIN korisnici k ON k.id = r.korisnik_id
             WHERE r.trening_id = ?
             ORDER BY r.kreiran ASC'
        );
        $stmt->execute([$treningId]);
        return $stmt->fetchAll();
    }
}
