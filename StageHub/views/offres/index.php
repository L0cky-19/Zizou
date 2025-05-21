<?php
require_once './config/database.php';
require_once './views/partials/navbar.php'; // Inclusion de la navbar avec la gestion de la session

// Fonction pour récupérer les offres avec pagination
function getAllOffres($page, $offresParPage) {
    $pdo = Database::getConnection();
    $offset = ($page - 1) * $offresParPage;
    $stmt = $pdo->prepare("
        SELECT 
            Offres.ID_Offre,
            Offres.Titre,
            Offres.Description,
            Offres.Remuneration,
            Offres.Date_Debut,
            Offres.Date_Fin,
            Entreprises.Nom AS Nom_Entreprise
        FROM Offres
        INNER JOIN Entreprises ON Offres.ID_Entreprise = Entreprises.ID_Entreprise
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $offresParPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour compter le nombre total d'offres
function countOffres() {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM Offres");
    return $stmt->fetchColumn();
}

// Fonction pour enregistrer une candidature
function saveCandidature($idOffre, $idUser, $cvPath, $lettrePath = null) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        INSERT INTO Candidatures (Date_Candidature, CV_Chemin, Chemin_Lettre_Motivation, ID_Offre, ID_User) 
        VALUES (CURDATE(), ?, ?, ?, ?)
    ");
    $stmt->execute([$cvPath, $lettrePath, $idOffre, $idUser]);
}

// Fonction pour créer les dossiers si nécessaires
function createDirectories() {
    $cvDir = './Dossier/CV';
    $lettreDir = './Dossier/Lettre';

    // Création du dossier CV
    if (!is_dir($cvDir)) {
        mkdir($cvDir, 0777, true);
    }

    // Création du dossier Lettre
    if (!is_dir($lettreDir)) {
        mkdir($lettreDir, 0777, true);
    }
}

// Appeler la fonction pour s'assurer que les dossiers existent
createDirectories();

// Gestion du formulaire de candidature
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'candidater') {
    $idOffre = $_POST['idOffre'];
    $idUser = $_SESSION['user']['id']; // ID de l'utilisateur connecté
    $cvPath = null;
    $lettrePath = null;

    // Vérifier et enregistrer le CV
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
        $cvName = uniqid() . '_' . basename($_FILES['cv']['name']);
        $cvPath = './Dossier/CV/' . $cvName;
        move_uploaded_file($_FILES['cv']['tmp_name'], $cvPath);
    } else {
        echo "<script>alert('Le CV est obligatoire !');</script>";
        exit;
    }

    // Vérifier et enregistrer la lettre de motivation (optionnelle)
    if (isset($_FILES['lettre']) && $_FILES['lettre']['error'] === UPLOAD_ERR_OK) {
        $lettreName = uniqid() . '_' . basename($_FILES['lettre']['name']);
        $lettrePath = './Dossier/Lettre/' . $lettreName;
        move_uploaded_file($_FILES['lettre']['tmp_name'], $lettrePath);
    }

    // Enregistrer la candidature dans la base de données
    saveCandidature($idOffre, $idUser, $cvPath, $lettrePath);

    echo "<script>alert('Candidature envoyée avec succès !');</script>";
}

// Gestion de la pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offresParPage = 5; // Nombre d'offres par page
$totalOffres = countOffres(); // Nombre total d'offres
$totalPages = ceil($totalOffres / $offresParPage);

// Récupération des offres pour la page actuelle
$offres = getAllOffres($page, $offresParPage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StageHUB - Trouvez votre stage facilement</title>
    <link rel="stylesheet" href="./public/CSS/styles.css">
</head>
<body>
<main>
    <h2 class="nav-header">Liste des offres</h2>
    <!-- Conteneur pour rendre le tableau responsive -->
    <div style="overflow-x: auto;">
        <table class="management-table">
            <thead>
                <tr>
                    <th>Entreprise</th>
                    <th>Offre</th>
                    <th>Description</th>
                    <th>Rémunération (€)</th>
                    <th>Date de début</th>
                    <th>Date de fin</th>
                    <?php if (isset($userRole) && $userRole === 'user'): ?>
                        <th>Candidater</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($offres as $offre): ?>
                    <tr>
                        <td><?= htmlspecialchars($offre['Nom_Entreprise']); ?></td>
                        <td><?= htmlspecialchars($offre['Titre']); ?></td>
                        <td><?= htmlspecialchars($offre['Description']); ?></td>
                        <td><?= htmlspecialchars($offre['Remuneration']); ?></td>
                        <td><?= htmlspecialchars($offre['Date_Debut']); ?></td>
                        <td><?= htmlspecialchars($offre['Date_Fin']); ?></td>
                        <?php if (isset($userRole) && $userRole === 'user'): ?>
                            <td>
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="candidater">
                                    <input type="hidden" name="idOffre" value="<?= $offre['ID_Offre']; ?>">
                                    <label>CV (PDF obligatoire) : 
                                        <input type="file" name="cv" accept="application/pdf" required>
                                    </label>
                                    <label>Lettre de motivation (optionnelle) : 
                                        <input type="file" name="lettre" accept="application/pdf">
                                    </label>
                                    <button class="btn-primary" type="submit">Candidater</button>
                                </form>
                            </td>
                        <?php else: ?>
                            <td>Non disponible</td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
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
</html>
