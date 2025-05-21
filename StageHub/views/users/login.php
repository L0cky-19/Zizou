<?php
session_start();

// Connexion à la base de données
$host = 'localhost';
$dbname = 'stagehub';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Vérification des données du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $motDePasse = $_POST['motDePasse'];

    // Debugging : Vérifie les données reçues
    var_dump("Email reçu : ", $email);
    var_dump("Mot de passe reçu : ", $motDePasse);

    // Requête pour récupérer l'utilisateur
    $sql = "SELECT * FROM Users WHERE Email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debugging : Vérifie si un utilisateur est trouvé
    var_dump("Utilisateur trouvé : ", $user);

    // Vérification du mot de passe
    if ($user && md5($motDePasse) === $user['MotDePasse']) {
        // Debugging : Mot de passe correct
        var_dump("Mot de passe correct !");
        $_SESSION['user'] = [
            'id' => $user['ID_User'],
            'prenom' => $user['Prenom'],
            'nom' => $user['Nom'],
            'role' => $user['Role']
        ];
        header('Location: http://localhost/StageHub/home'); // Redirection vers la page d'accueil
        exit;
    } else {
        // Debugging : Mot de passe incorrect ou utilisateur non trouvé
        var_dump("Mot de passe incorrect ou utilisateur non trouvé !");
        header('Location: views/users/err_login.php'); // Redirection en cas d'erreur
        exit;
    }
} else {
    // Si la méthode n'est pas POST, on redirige vers le formulaire de connexion
    header('Location: login_form.php');
    exit;
}