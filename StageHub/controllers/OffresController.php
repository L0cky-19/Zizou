<?php
require_once './models/OffreModel.php';

class OffresController {
    public function index() {
        $model = new OffreModel();
        $offres = $model->getAllOffres();
        require './views/offres/index.php';
    }
}
