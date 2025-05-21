<?php
require_once './config/database.php';
class WishlistModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getWishlistByUserId($userId) {
        $sql = "SELECT Offres.* FROM WishLists 
                JOIN Offres ON WishLists.ID_Offre = Offres.ID_Offre 
                WHERE WishLists.ID_User = :userId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
