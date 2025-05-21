<?php

class GController
{
    public function __construct()
    {
        // Vérifie si une session est déjà active avant de démarrer une nouvelle session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérification de la connexion
        if (!isset($_SESSION['user'])) {
            header('Location: ../home'); // Redirige vers la page de connexion si non connecté
            exit;
        }

        // Vérification si 'role' est défini dans la session utilisateur
        if (!isset($_SESSION['user']['role'])) {
            echo "Erreur : 'role' n'est pas défini dans la session.";
            var_dump($_SESSION); // Debug temporaire pour afficher la session
            exit;
        }
    }

    /**
     * Affiche la page des entreprises (accessible aux pilotes et admins)
     */
    public function entreprises()
    {
        $this->checkAccess(['pilote', 'admin']);
        require './views/G/Gentreprises.php';
    }

    /**
     * Affiche la page des étudiants (accessible uniquement aux admins)
     */
    public function etudiants()
    {
        $this->checkAccess(['pilote', 'admin']);
        require './views/G/Getudiants.php';
    }

    /**
     * Affiche la page des offres (accessible aux pilotes et admins)
     */
    public function offres()
    {
        $this->checkAccess(['pilote', 'admin']);
        require './views/G/Goffres.php';
    }

    /**
     * Affiche la page des pilotes (accessible uniquement aux admins)
     */
    public function pilotes()
    {
        $this->checkAccess(['admin']);
        require './views/G/Gpilotes.php';
    }

    /**
     * Affiche la page des candidatures (accessible aux pilotes et admins)
     */
    public function candidatures()
    {
        $this->checkAccess(['pilote', 'admin']);
        require './views/G/Gcandidatures.php';
    }

    /**
     * Affiche la page des wishlists (accessible uniquement aux utilisateurs connectés)
     */
    public function wishlists()
    {
        $this->checkAccess(['pilote', 'admin', 'etudiant']);
        require './views/G/Gwishlists.php';
    }

    /**
     * Affiche la page des évaluations (accessible uniquement aux admins)
     */
    public function evaluations()
    {
        $this->checkAccess(['admin']);
        require './views/G/Gevaluations.php';
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
