<?php
require_once './models/UserModel.php';

class UsersController {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['motDePasse'];

            $model = new UserModel();
            $user = $model->getUserByEmail($email);

            if ($user && password_verify($password, $user['MotDePasse'])) {
                session_start();
                $_SESSION['user'] = $user;
                header('Location: /public/index.php');
            } else {
                $error = "Identifiants incorrects";
                require './views/users/err_login.php';
            }
        } else {
            require './views/users/login.php';
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: /public/index.php');
    }
}
