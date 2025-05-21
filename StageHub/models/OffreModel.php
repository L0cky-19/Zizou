<?php
require_once './config/database.php';
class OffreModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getAllOffres() {
        $sql = "SELECT Offres.*, Entreprises.Nom AS Entreprise 
                FROM Offres 
                JOIN Entreprises ON Offres.ID_Entreprise = Entreprises.ID_Entreprise";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
