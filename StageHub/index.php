<?php

require_once './controllers/OffresController.php';
require_once './controllers/EntreprisesController.php';
require_once './controllers/WishlistController.php';
require_once './controllers/HomeController.php';
require_once './controllers/GController.php';

// Récupérer l'URI
$uri = $_GET["uri"];

// Routage
if ($uri === 'offres') {
    $controller = new OffresController();
    $controller->index();
} elseif ($uri === 'entreprises') {
    $controller = new EntreprisesController();
    $controller->index();
} elseif ($uri === 'wishlist') {
    $controller = new WishlistController();
    $controller->index();
} elseif ($uri === 'home') {
    $controller = new HomeController();
    $controller->index();

} elseif ($uri === 'gestion') {
    $controller = new GestionController();
    $controller->index();
} elseif ($uri === 'G') {
    $controller = new GController();
    $controller->index();
} elseif ($uri === 'G/entreprises') {
    $controller = new GController();
    $controller->entreprises();
} elseif ($uri === 'G/etudiants') {
    $controller = new GController();
    $controller->etudiants();
} elseif ($uri === 'G/offres') {
    $controller = new GController();
    $controller->offres();
} elseif ($uri === 'G/pilotes') {
    $controller = new GController();
    $controller->pilotes();

} elseif ($uri === 'login') {
    require './views/users/login.php';
} elseif ($uri === 'views/users/err_login.php') {
    require './views/users/err_login.php';
} elseif ($uri === 'views/users/logout.php') {
    require './views/users/logout.php';

} else {
    echo "404 - Page non trouvée";
}
