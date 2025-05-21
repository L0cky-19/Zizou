<?php
require_once './config/database.php';

// Fonction pour récupérer toutes les offres avec leurs informations
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
            Entreprises.Nom AS Nom_Entreprise,
            (SELECT COUNT(*) FROM Candidatures WHERE Candidatures.ID_Offre = Offres.ID_Offre) AS Nombre_Candidatures
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

// Fonction pour récupérer l'ID d'une entreprise à partir de son nom
function getEntrepriseIdByName($nomEntreprise) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT ID_Entreprise FROM Entreprises WHERE Nom = ?");
    $stmt->execute([$nomEntreprise]);
    return $stmt->fetchColumn();
}

// Fonction pour ajouter une offre vide
function addOffre($nomEntreprise) {
    $idEntreprise = getEntrepriseIdByName($nomEntreprise);
    if (!$idEntreprise) {
        return false; // Si l'entreprise n'existe pas, retourner false
    }
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        INSERT INTO Offres (Titre, Date_Debut, Date_Fin, Description, Remuneration, ID_Entreprise) 
        VALUES ('Nouvelle Offre', CURDATE(), CURDATE(), '', 0, ?)
    ");
    $stmt->execute([$idEntreprise]);
    return $pdo->lastInsertId();
}

// Fonction pour supprimer une offre
function deleteOffre($idOffre) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("DELETE FROM Offres WHERE ID_Offre = ?");
    $stmt->execute([$idOffre]);
}

// Fonction pour mettre à jour une offre
function updateOffre($idOffre, $titre, $description, $remuneration, $dateDebut, $dateFin) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        UPDATE Offres 
        SET Titre = ?, Description = ?, Remuneration = ?, Date_Debut = ?, Date_Fin = ?
        WHERE ID_Offre = ?
    ");
    $stmt->execute([$titre, $description, $remuneration, $dateDebut, $dateFin, $idOffre]);
}

// Gestion des requêtes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (isset($_POST['nomEntreprise'])) {
                    $id = addOffre($_POST['nomEntreprise']);
                    if ($id) {
                        echo json_encode(['success' => true, 'id' => $id]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Entreprise introuvable']);
                    }
                }
                break;
            case 'delete':
                if (isset($_POST['id'])) {
                    deleteOffre($_POST['id']);
                    echo json_encode(['success' => true]);
                }
                break;
            case 'update':
                if (isset($_POST['id'], $_POST['titre'], $_POST['description'], $_POST['remuneration'], $_POST['dateDebut'], $_POST['dateFin'])) {
                    updateOffre($_POST['id'], $_POST['titre'], $_POST['description'], $_POST['remuneration'], $_POST['dateDebut'], $_POST['dateFin']);
                    echo json_encode(['success' => true]);
                }
                break;
        }
    }
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offresParPage = 10; // Nombre d'offres par page
$totalOffres = countOffres();
$totalPages = ceil($totalOffres / $offresParPage);

// Récupérer les offres pour la page actuelle
$offres = getAllOffres($page, $offresParPage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Offres de Stage</title>
    <link rel="stylesheet" href="../public/CSS/styles.css">
    <script>
        async function addOffre() {
            const nomEntreprise = prompt("Entrez le nom de l'entreprise liée à cette offre :");
            if (nomEntreprise) {
                const response = await fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ action: 'add', nomEntreprise: nomEntreprise })
                });
                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message);
                }
            }
        }

        async function deleteOffre(id) {
            if (confirm("Êtes-vous sûr de vouloir supprimer cette offre ?")) {
                const response = await fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ action: 'delete', id: id })
                });
                const result = await response.json();
                if (result.success) {
                    location.reload();
                }
            }
        }

        function editRow(button, id) {
            const row = button.closest('tr');
            const cells = row.querySelectorAll('.table-data');
            cells.forEach((cell, index) => {
                if (index > 0 && index < cells.length - 3) { // Ignorer l'ID, Nombre de Candidatures et les boutons
                    const currentText = cell.innerText;
                    cell.innerHTML = `<input type='text' value='${currentText}'>`;
                }
            });
            button.innerText = 'Sauvegarder';
            button.setAttribute('onclick', `saveRow(this, ${id})`);
        }

        async function saveRow(button, id) {
            const row = button.closest('tr');
            const cells = row.querySelectorAll('.table-data');
            const titre = cells[1].querySelector('input').value;
            const description = cells[2].querySelector('input').value;
            const remuneration = cells[3].querySelector('input').value;
            const dateDebut = cells[4].querySelector('input').value;
            const dateFin = cells[5].querySelector('input').value;

            const response = await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'update', id: id, titre: titre, description: description, remuneration: remuneration, dateDebut: dateDebut, dateFin: dateFin })
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            }
        }
    </script>
</head>
<body class="page-body">
    <header class="navbar">
        <h1 class="nav-header">Gestion des Offres</h1>
        <nav class="nav-container">
            <ul class="nav-list">
                <li class="nav-item"><a href="./entreprises" class="nav-link">Entreprises</a></li>
                <li class="nav-item"><a href="./etudiants" class="nav-link">Étudiants</a></li>
                <li class="nav-item"><a class="nav-on nav-link">Offres</a></li>
                <li class="nav-item"><a href="./pilotes" class="nav-link">Pilotes</a></li>
                <li class="nav-item"><a href="../home" class="nav-link">Accueil</a></li>
            </ul>
        </nav>
    </header>
    <main class="main-content">
        <div>
            <button class="pagination-button" onclick="addOffre()">Ajouter une offre</button>
        </div>
        <section class="management-section">
            <table class="management-table">
                <thead>
                    <tr class="table-header-row">
                        <th class="table-header">Entreprise</th>
                        <th class="table-header">Offre</th>
                        <th class="table-header">Description</th>
                        <th class="table-header">Rémunération</th>
                        <th class="table-header">Date Début</th>
                        <th class="table-header">Date Fin</th>
                        <th class="table-header">Nombre de Candidatures</th>
                        <th class="table-header">Éditer</th>
                        <th class="table-header">Voir</th>
                        <th class="table-header">Supprimer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($offres as $offre): ?>
                        <tr class="table-row">
                            <td class="table-data"><?= htmlspecialchars($offre['Nom_Entreprise']) ?></td>
                            <td class="table-data"><?= htmlspecialchars($offre['Titre']) ?></td>
                            <td class="table-data"><?= htmlspecialchars($offre['Description']) ?></td>
                            <td class="table-data"><?= htmlspecialchars($offre['Remuneration']) ?>€</td>
                            <td class="table-data"><?= htmlspecialchars($offre['Date_Debut']) ?></td>
                            <td class="table-data"><?= htmlspecialchars($offre['Date_Fin']) ?></td>
                            <td class="table-data"><?= htmlspecialchars($offre['Nombre_Candidatures']) ?></td>
                            <td class="table-data"><button class="edit-button" onclick="editRow(this, <?= $offre['ID_Offre'] ?>)">Éditer</button></td>
                            <td class="table-data"><button class="view-button">Voir</button></td>
                            <td class="table-data"><button class="delete-button" onclick="deleteOffre(<?= $offre['ID_Offre'] ?>)">Supprimer</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <div class="pagination-container">
            <button class="pagination-button" onclick="location.href='?page=1'" <?= $page == 1 ? 'disabled' : '' ?>>&laquo;</button>
            <button class="pagination-button" onclick="location.href='?page=<?= $page - 1 ?>'" <?= $page == 1 ? 'disabled' : '' ?>>Précédent</button>
            <span class="pagination-info">Page <?= $page ?> sur <?= $totalPages ?></span>
            <button class="pagination-button" onclick="location.href='?page=<?= $page + 1 ?>'" <?= $page == $totalPages ? 'disabled' : '' ?>>Suivant</button>
            <button class="pagination-button" onclick="location.href='?page=<?= $totalPages ?>'" <?= $page == $totalPages ? 'disabled' : '' ?>>&raquo;</button>
        </div>
    </main>
</body>
</html>
