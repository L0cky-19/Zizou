<?php
session_start();
session_destroy(); // Supprime toutes les données de la session
header('Location: http://localhost/StageHub/home');  // Redirige vers la page d'accueil
exit;
?>