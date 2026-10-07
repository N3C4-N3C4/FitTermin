<?php
/**
 * Model treninga – osnovni entitet aplikacije (tabela "treninzi").
 */
class Trening extends Model
{
    public const KATEGORIJE = ['Kardio', 'Snaga', 'Joga', 'Pilates', 'CrossFit', 'Boks'];

    /** Deo upita koji uz trening vraća i broj zauzetih mesta */
    private const SELECT = 'SELECT t.*, COUNT(r.id) AS zauzeto
                            FROM treninzi t
                            LEFT JOIN rezervacije r ON r.trening_id = t.id';

    /**
     * Lista treninga sa filterima.
     * @param array{q?:string, kategorija?:string, buduci?:bool} $filteri
     */
    public function all(array $filteri = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filteri['q'])) {
            $where[] = '(t.naziv LIKE ? OR t.trener LIKE ? OR t.opis LIKE ?)';
            $like = '%' . $filteri['q'] . '%';
            array_push($params, $like, $like, $like);
        }
        if (!empty($filteri['kategorija']) && in_array($filteri['kategorija'], self::KATEGORIJE, true)) {
            $where[] = 't.kategorija = ?';
            $params[] = $filteri['kategorija'];
        }
        if (!empty($filteri['buduci'])) {
            $where[] = 't.datum_vreme >= ?';
            $params[] = date('Y-m-d H:i:s');
        }

        $sql = self::SELECT
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' GROUP BY t.id ORDER BY t.datum_vreme ASC';

        if (!empty($filteri['limit'])) {
            $sql .= ' LIMIT ' . (int) $filteri['limit'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(self::SELECT . ' WHERE t.id = ? GROUP BY t.id');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $d): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO treninzi (naziv, kategorija, opis, trener, datum_vreme, trajanje_min, kapacitet, slika)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $d['naziv'], $d['kategorija'], $d['opis'], $d['trener'],
            $d['datum_vreme'], $d['trajanje_min'], $d['kapacitet'], $d['slika'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): void
    {
        $stmt = $this->db->prepare(
            'UPDATE treninzi
             SET naziv = ?, kategorija = ?, opis = ?, trener = ?, datum_vreme = ?,
                 trajanje_min = ?, kapacitet = ?, slika = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $d['naziv'], $d['kategorija'], $d['opis'], $d['trener'],
            $d['datum_vreme'], $d['trajanje_min'], $d['kapacitet'], $d['slika'], $id,
        ]);
    }

    /** Briše trening i njegovu sliku sa diska. Rezervacije se brišu kaskadno (FK). */
    public function delete(int $id): bool
    {
        $trening = $this->find($id);
        if (!$trening) {
            return false;
        }
        $stmt = $this->db->prepare('DELETE FROM treninzi WHERE id = ?');
        $stmt->execute([$id]);
        self::deleteImage($trening['slika']);
        return true;
    }

    public static function deleteImage(?string $file): void
    {
        if ($file && preg_match('/^[a-f0-9]{32}\.(jpg|png|webp|gif)$/', $file)) {
            $path = UPLOAD_DIR . '/' . $file;
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    /** Pretvara red iz baze u niz za JSON odgovor veb servisa. */
    public static function toArray(array $t, array $rezervisaniIds = []): array
    {
        $zauzeto = (int) $t['zauzeto'];
        $kapacitet = (int) $t['kapacitet'];
        return [
            'id'           => (int) $t['id'],
            'naziv'        => $t['naziv'],
            'kategorija'   => $t['kategorija'],
            'opis'         => $t['opis'],
            'trener'       => $t['trener'],
            'datum_vreme'  => date('c', strtotime($t['datum_vreme'])),
            'datum'        => format_datum($t['datum_vreme']),
            'dan'          => dan_u_nedelji($t['datum_vreme']),
            'trajanje_min' => (int) $t['trajanje_min'],
            'kapacitet'    => $kapacitet,
            'zauzeto'      => $zauzeto,
            'slobodno'     => max(0, $kapacitet - $zauzeto),
            'slika_url'    => upload_url($t['slika']),
            'proslo'       => strtotime($t['datum_vreme']) < time(),
            'rezervisano'  => in_array((int) $t['id'], $rezervisaniIds, true),
            'url'          => url('treninzi/' . $t['id']),
        ];
    }

    /** Validacija podataka iz forme. Vraća [čisti podaci, greške]. */
    public static function validate(array $in): array
    {
        $d = [
            'naziv'        => trim((string) ($in['naziv'] ?? '')),
            'kategorija'   => (string) ($in['kategorija'] ?? ''),
            'opis'         => trim((string) ($in['opis'] ?? '')),
            'trener'       => trim((string) ($in['trener'] ?? '')),
            'datum_vreme'  => (string) ($in['datum_vreme'] ?? ''),
            'trajanje_min' => (int) ($in['trajanje_min'] ?? 0),
            'kapacitet'    => (int) ($in['kapacitet'] ?? 0),
            'slika'        => (string) ($in['slika'] ?? '') ?: null,
        ];
        $err = [];

        if (mb_strlen($d['naziv']) < 3 || mb_strlen($d['naziv']) > 120) {
            $err['naziv'] = 'Naziv mora imati između 3 i 120 karaktera.';
        }
        if (!in_array($d['kategorija'], self::KATEGORIJE, true)) {
            $err['kategorija'] = 'Izaberite kategoriju.';
        }
        if (mb_strlen($d['trener']) < 2 || mb_strlen($d['trener']) > 100) {
            $err['trener'] = 'Unesite ime trenera.';
        }
        $dt = DateTime::createFromFormat('Y-m-d\TH:i', $d['datum_vreme']);
        if (!$dt) {
            $err['datum_vreme'] = 'Unesite ispravan datum i vreme.';
        } else {
            $d['datum_vreme'] = $dt->format('Y-m-d H:i:00');
        }
        if ($d['trajanje_min'] < 15 || $d['trajanje_min'] > 240) {
            $err['trajanje_min'] = 'Trajanje mora biti između 15 i 240 minuta.';
        }
        if ($d['kapacitet'] < 1 || $d['kapacitet'] > 100) {
            $err['kapacitet'] = 'Kapacitet mora biti između 1 i 100.';
        }
        if ($d['slika'] !== null
            && (!preg_match('/^[a-f0-9]{32}\.(jpg|png|webp|gif)$/', $d['slika'])
                || !is_file(UPLOAD_DIR . '/' . $d['slika']))) {
            $err['slika'] = 'Slika nije ispravna – otpremite je ponovo.';
        }

        return [$d, $err];
    }
}
