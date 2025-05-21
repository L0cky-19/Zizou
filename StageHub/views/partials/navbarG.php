<link rel="stylesheet" href="./public/CSS/styles.css">
<?php
// Vérifier si une session est déjà active avant de démarrer une nouvelle session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
$isConnected = isset($_SESSION['user']); // Si une session utilisateur existe
$userName = $isConnected ? htmlspecialchars($_SESSION['user']['prenom']) : null;
$userRole = $isConnected ? htmlspecialchars($_SESSION['user']['role']) : null;

// Déterminer la page actuelle
$currentPage = basename($_SERVER['PHP_SELF']); // Récupère le nom du fichier actuel
?>

<header>
    <h1>StageHUB</h1>
    <nav>
        <ul>
            <li>
                <?php if ($currentPage === 'home'): ?>
                    <a class="a1">Accueil</a>
                <?php else: ?>
                    <a href="../home">Accueil</a>
                <?php endif; ?>
            </li>
            <li>
                <?php if ($currentPage === 'offres'): ?>
                    <a class="a1">Offres</a>
                <?php else: ?>
                    <a href="../offres">Offres</a>
                <?php endif; ?>
            </li>
            <li>
                <?php if ($currentPage === 'entreprises'): ?>
                    <a class="a1">Entreprises</a>
                <?php else: ?>
                    <a href="../entreprises">Entreprises</a>
                <?php endif; ?>
            </li>
            <li>
                <?php if ($currentPage === 'wishlist'): ?>
                    <a class="a1">Wishlist</a>
                <?php else: ?>
                    <a href="../wishlist">Wishlist</a>
                <?php endif; ?>
            </li>
        </ul>
        <div id="user-buttons">
            <?php if ($isConnected): ?>
                <button class="big-button user-name" onclick="toggleUserMenu()">Bonjour, <?= $userName ?></button>
                <div id="user-menu" class="hidden">
                    <?php if ($userRole === 'admin'): ?>
                        <a href="./etudiants">Gestion des étudiants</a>
                        <a href="./pilotes">Gestion des pilotes</a>
                        <a href="./entreprises">Gestion des entreprises</a>
                        <a href="./offres">Gestion des offres</a>
                    <?php elseif ($userRole === 'pilote'): ?>
                        <a href="./etudiants">Gestion des étudiants</a>
                        <a href="./entreprises">Gestion des entreprises</a>
                        <a href="./offres">Gestion des offres</a>
                    <?php endif; ?>
                    <a href="../views/users/logout.php" class="logout-link">Déconnexion</a>
                </div>
            <?php else: ?>
                <button id="loginButton" class="big-button" onclick="toggleElement('loginForm')">Connexion</button>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Formulaire de connexion -->
    <div id="loginForm" class="hidden">
        <form action="/StageHub/login" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="motDePasse" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
        <div id="errorMessage" style="display: none;">L'email ou le mot de passe est incorrect.</div>
    </div>
</header>

<script>
    function toggleUserMenu() {
        const userMenu = document.getElementById('user-menu');
        userMenu.classList.toggle('hidden'); // Ajoute ou retire la classe "hidden"
    }

    function toggleElement(id) {
        document.getElementById(id).classList.toggle("active");
    }
</script>
