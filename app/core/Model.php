<?php
/**
 * Osnovna klasa za modele – daje pristup bazi.
 */
abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }
}
