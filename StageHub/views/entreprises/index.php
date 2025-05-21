<?php
require_once './config/database.php';
require_once './views/partials/navbar.php'; // Inclusion de la navbar

// Fonction pour récupérer les entreprises avec pagination
function getAllEntreprises($page, $entreprisesParPage) {
    $pdo = Database::getConnection();
    $offset = ($page - 1) * $entreprisesParPage;
    $stmt = $pdo->prepare("
        SELECT 
            Nom,
            Description,
            Email,
            Telephone,
            Note
        FROM Entreprises
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $entreprisesParPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour compter le nombre total d'entreprises
function countEntreprises() {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM Entreprises");
    return $stmt->fetchColumn();
}

// Gestion de la pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$entreprisesParPage = 10; // Nombre d'entreprises par page
$totalEntreprises = countEntreprises(); // Nombre total d'entreprises
$totalPages = ceil($totalEntreprises / $entreprisesParPage);

// Récupération des entreprises pour la page actuelle
$entreprises = getAllEntreprises($page, $entreprisesParPage);
?>

<!DOCTYPE php>
<php lang="fr">
<?php include './views/partials/navbar.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StageHUB - Liste des entreprises</title>
    <link rel="stylesheet" href="./public/CSS/styles.css">
</head>
<body>
<main>
    <h2>Liste des entreprises</h2>
    <!-- Conteneur pour rendre le tableau responsive -->
    <div style="overflow-x: auto;">
        <table class="management-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($entreprises)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">Aucune entreprise disponible.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($entreprises as $entreprise): ?>
                        <tr>
                            <td><?= htmlspecialchars($entreprise['Nom']); ?></td>
                            <td><?= htmlspecialchars($entreprise['Description']); ?></td>
                            <td><?= htmlspecialchars($entreprise['Email']); ?></td>
                            <td><?= htmlspecialchars($entreprise['Telephone']); ?></td>
                            <td><?= htmlspecialchars($entreprise['Note']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination-container">
        <button class="pagination-button" onclick="location.href='?page=1'" <?= $page == 1 ? 'disabled' : '' ?>>&laquo;</button>
        <button class="pagination-button" onclick="location.href='?page=<?= $page - 1 ?>'" <?= $page == 1 ? 'disabled' : '' ?>>Précédent</button>
        <span class="pagination-info">Page <?= $page ?> sur <?= $totalPages ?></span>
        <button class="pagination-button" onclick="location.href='?page=<?= $page + 1 ?>'" <?= $page == $totalPages ? 'disabled' : '' ?>>Suivant</button>
        <button class="pagination-button" onclick="location.href='?page=<?= $totalPages ?>'" <?= $page == $totalPages ? 'disabled' : '' ?>>&raquo;</button>
    </div>
</main>

<footer class="site-footer">
    <div class="footer-legal">
        <p>&copy; 2025 StageHUB - Tous droits réservés</p>
        <p>
            <strong>Mentions légales :</strong><br>
            Nom de l’entreprise : Web4All<br>
            Numéro SIRET : 123 456 789 00012<br>
            Forme juridique : Société à Responsabilité Limitée (SARL)<br>
            Capital social : 10 000 €<br>
            Adresse du siège social : 13 Avenue Simone Veil
        </p>
        <p>
            <strong>Hébergeur :</strong><br>
            Nom de l’hébergeur : StageHub<br>
            Adresse : 12 Avenue Simone Veil<br>
            Téléphone : 07 62 00 32 27
        </p>
    </div>
    <div class="footer-links">
        <p>
            <a href="https://www.service-public.fr/professionnels-entreprises/vosdroits/F31228" target="_blank" rel="noopener noreferrer">Mentions légales</a> | 
            <a href="https://www.cnil.fr/fr/reglement-europeen-protection-donnees" target="_blank" rel="noopener noreferrer">Politique de confidentialité</a>
        </p>
    </div>
</footer>
</body>
</php>
