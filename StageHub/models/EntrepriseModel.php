<?php
require_once './config/database.php';
class EntrepriseModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getAllEntreprises() {
        $sql = "SELECT * FROM Entreprises";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
