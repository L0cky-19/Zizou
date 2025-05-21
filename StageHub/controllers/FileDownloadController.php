<?php

require_once './models/WishlistModel.php';

class FileDownloadController {
    private $wishlistModel;

    public function __construct() {
        $this->wishlistModel = new WishlistModel();
    }

    public function downloadFile() {
        // Vérifier si l'utilisateur est connecté
        session_start();
        if (!isset($_SESSION['user'])) {
            echo "403 - Accès interdit";
            exit;
        }

        // Récupérer les paramètres (exemple : type=fichier&file=nom_du_fichier.pdf)
        $fileType = $_GET['type'] ?? null;
        $fileName = $_GET['file'] ?? null;

        // Valider les paramètres
        if (!$fileType || !$fileName) {
            echo "400 - Requête invalide";
            exit;
        }

        // Déterminer le chemin du fichier en fonction du type
        $basePath = '';
        if ($fileType === 'cv') {
            $basePath = './Dossier/CV/';
        } elseif ($fileType === 'lettre') {
            $basePath = './Dossier/Lettre/';
        } else {
            echo "400 - Type de fichier invalide";
            exit;
        }

        $filePath = $basePath . basename($fileName);

        // Vérifier si le fichier existe
        if (!file_exists($filePath)) {
            echo "404 - Fichier introuvable";
            exit;
        }

        // Envoyer le fichier au client
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}
