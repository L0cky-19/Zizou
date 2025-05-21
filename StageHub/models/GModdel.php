<?php

require_once './config/database.php';

class GModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection(); // Connexion à la base de données
    }

    public function getEntreprises()
    {
        $query = $this->db->prepare("SELECT * FROM entreprises");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEtudiants()
    {
        $query = $this->db->prepare("SELECT * FROM etudiants");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOffres()
    {
        $query = $this->db->prepare("SELECT * FROM offres");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPilotes()
    {
        $query = $this->db->prepare("SELECT * FROM pilotes");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
