<?php

require_once './config/database.php';
require_once './views/partials/navbar.php'; // Inclusion de la navbar avec la gestion de la session

// Fonction pour récupérer toutes les candidatures d'un utilisateur avec pagination
function getCandidaturesByUser($userId, $page, $itemsPerPage) {
    $pdo = Database::getConnection();
    $offset = ($page - 1) * $itemsPerPage;
    $stmt = $pdo->prepare("
        SELECT 
            Candidatures.ID_Candidature,
            Candidatures.Date_Candidature,
            Candidatures.CV_Chemin,
            Candidatures.Chemin_Lettre_Motivation,
            Offres.ID_Offre,
            Offres.Titre,
            Offres.Description,
            Offres.Remuneration,
            Offres.Date_Debut,
            Offres.Date_Fin,
            Entreprises.Nom AS Nom_Entreprise
        FROM Candidatures
        INNER JOIN Offres ON Candidatures.ID_Offre = Offres.ID_Offre
        INNER JOIN Entreprises ON Offres.ID_Entreprise = Entreprises.ID_Entreprise
        WHERE Candidatures.ID_User = :userId
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour compter le nombre total de candidatures
function countCandidaturesByUser($userId) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total 
        FROM Candidatures 
        WHERE ID_User = :userId
    ");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchColumn();
}

// Fonction pour supprimer une candidature
function removeCandidature($candidatureId) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("DELETE FROM Candidatures WHERE ID_Candidature = :candidatureId");
    $stmt->bindValue(':candidatureId', $candidatureId, PDO::PARAM_INT);
    $stmt->execute();
}

// Fonction pour modifier les fichiers d'une candidature
function updateCandidatureFiles($candidatureId, $cvPath, $lettrePath = null) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        UPDATE Candidatures
        SET CV_Chemin = :cvPath, Chemin_Lettre_Motivation = :lettrePath
        WHERE ID_Candidature = :candidatureId
    ");
    $stmt->bindValue(':cvPath', $cvPath, PDO::PARAM_STR);
    $stmt->bindValue(':lettrePath', $lettrePath, PDO::PARAM_STR);
    $stmt->bindValue(':candidatureId', $candidatureId, PDO::PARAM_INT);
    $stmt->execute();
}

// Gestion des actions "Supprimer" et "Modifier"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'supprimer') {
        $candidatureId = $_POST['idCandidature'];
        removeCandidature($candidatureId);
        echo "<script>alert('Candidature supprimée avec succès !');</script>";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'modifier') {
        $candidatureId = $_POST['idCandidature'];
        $cvPath = null;
        $lettrePath = null;

        // Vérifier et enregistrer le CV
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $cvName = uniqid() . '_' . basename($_FILES['cv']['name']);
            $cvPath = './Dossier/CV/' . $cvName;
            move_uploaded_file($_FILES['cv']['tmp_name'], $cvPath);
        }

        // Vérifier et enregistrer la lettre de motivation (optionnelle)
        if (isset($_FILES['lettre']) && $_FILES['lettre']['error'] === UPLOAD_ERR_OK) {
            $lettreName = uniqid() . '_' . basename($_FILES['lettre']['name']);
            $lettrePath = './Dossier/Lettre/' . $lettreName;
            move_uploaded_file($_FILES['lettre']['tmp_name'], $lettrePath);
        }

        updateCandidatureFiles($candidatureId, $cvPath, $lettrePath);
        echo "<script>alert('Fichiers modifiés avec succès !');</script>";
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 5; // Nombre d'éléments par page
$totalItems = countCandidaturesByUser($_SESSION['user']['id']); // Nombre total de candidatures
$totalPages = ceil($totalItems / $itemsPerPage);

// Récupérer les candidatures pour la page actuelle
$candidatures = getCandidaturesByUser($_SESSION['user']['id'], $page, $itemsPerPage);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StageHUB - Mes Candidatures</title>
    <link rel="stylesheet" href="./public/CSS/styles.css">
</head>
<body>
<main>
    <h2 class="nav-header">Mes Candidatures</h2>
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
                    <th>Date de candidature</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($candidatures)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">Vous n'avez pas encore postulé à une offre.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($candidatures as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['Nom_Entreprise']); ?></td>
                            <td><?= htmlspecialchars($item['Titre']); ?></td>
                            <td><?= htmlspecialchars($item['Description']); ?></td>
                            <td><?= htmlspecialchars($item['Remuneration']); ?></td>
                            <td><?= htmlspecialchars($item['Date_Debut']); ?></td>
                            <td><?= htmlspecialchars($item['Date_Fin']); ?></td>
                            <td><?= htmlspecialchars($item['Date_Candidature']); ?></td>
                            <td>
                                <!-- Formulaire pour supprimer une candidature -->
                                <form method="POST" style="display: inline-block;">
                                    <input type="hidden" name="action" value="supprimer">
                                    <input type="hidden" name="idCandidature" value="<?= $item['ID_Candidature']; ?>">
                                    <button class="btn-primary" type="submit">Supprimer</button>
                                </form>
                                <!-- Formulaire pour modifier les fichiers -->
                                <form method="POST" enctype="multipart/form-data" style="display: inline-block;">
                                    <input type="hidden" name="action" value="modifier">
                                    <input type="hidden" name="idCandidature" value="<?= $item['ID_Candidature']; ?>">
                                    <label>CV : 
                                        <input type="file" name="cv" accept="application/pdf" required>
                                    </label>
                                    <label>Lettre :
                                        <input type="file" name="lettre" accept="application/pdf">
                                    </label>
                                    <button class="btn-primary" type="submit">Modifier</button>
                                </form>
                            </td>
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
</html>
