<?php
require_once './models/WishlistModel.php';

class WishlistController
{
    public function __construct()
    {
        // Vérifie si une session est déjà active avant de démarrer une nouvelle session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérification de la connexion
        if (!isset($_SESSION['user'])) {
            header('Location: ../StageHub/home'); // Redirige vers la page de connexion si non connecté
            exit;
        }

        // Vérification si 'role' est défini dans la session utilisateur
        if (!isset($_SESSION['user']['role'])) {
            echo "Erreur : 'role' n'est pas défini dans la session.";
            var_dump($_SESSION); // Debug temporaire pour afficher la session
            exit;
        }

        // Vérification des droits d'accès pour les utilisateurs ayant le rôle "user"
        $this->checkAccess(['user']);
    }

    /**
     * Affiche la page des wishlists (accessible uniquement aux utilisateurs ayant le rôle "user")
     */
    public function index()
    {
        require './views/wishlist/index.php';
    }

    /**
     * Vérifie si l'utilisateur a les droits nécessaires pour accéder à une page
     * @param array $allowedRoles Rôles autorisés pour accéder à la page
     */
    private function checkAccess(array $allowedRoles)
    {
        // Vérification si 'role' est défini dans la session utilisateur
        if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], $allowedRoles)) {
            $this->accessDenied();
        }
    }

    /**
     * Affiche un message d'accès refusé et arrête l'exécution
     */
    private function accessDenied()
    {
        echo "Accès refusé : Vous n'avez pas les droits nécessaires pour accéder à cette page.";
        exit;
    }
}
