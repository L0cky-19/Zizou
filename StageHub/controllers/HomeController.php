<?php
require_once './models/HomeModel.php';

class HomeController {
    public function index() {
        $model = new HomeModel();
        require './views/home/index.php';
    }
}
