<?php
require_once './models/EntrepriseModel.php';

class EntreprisesController {
    public function index() {
        $model = new EntrepriseModel();
        $entreprises = $model->getAllEntreprises();
        require './views/entreprises/index.php';
    }
}
