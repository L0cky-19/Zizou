<?php
require_once './config/database.php';

// Fonction pour récupérer toutes les entreprises
function getAllEntreprises() {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM Entreprises");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour récupérer le nombre total d'entreprises
function getTotalEntreprises() {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) FROM Entreprises");
    return $stmt->fetchColumn();
}

// Fonction pour récupérer les entreprises avec pagination
function getEntreprisesByPage($page, $entreprisesPerPage) {
    $offset = ($page - 1) * $entreprisesPerPage;
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM Entreprises LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $entreprisesPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour ajouter une entreprise
function addEntreprise() {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("INSERT INTO Entreprises (Nom, Description, Email, Telephone, Note) VALUES ('', '', '', '', 0)");
    $stmt->execute();
    return $pdo->lastInsertId();
}

// Fonction pour supprimer une entreprise
function deleteEntreprise($id) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("DELETE FROM Entreprises WHERE ID_Entreprise = ?");
    $stmt->execute([$id]);
}

// Fonction pour mettre à jour une entreprise
function updateEntreprise($id, $nom, $description, $email, $telephone, $note) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("UPDATE Entreprises SET Nom = ?, Description = ?, Email = ?, Telephone = ?, Note = ? WHERE ID_Entreprise = ?");
    $stmt->execute([$nom, $description, $email, $telephone, $note, $id]);
}

// Gestion des requêtes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $id = addEntreprise();
                echo json_encode(['success' => true, 'id' => $id]);
                break;
            case 'delete':
                if (isset($_POST['id'])) {
                    deleteEntreprise($_POST['id']);
                    echo json_encode(['success' => true]);
                }
                break;
            case 'update':
                if (isset($_POST['id'], $_POST['nom'], $_POST['description'], $_POST['email'], $_POST['telephone'], $_POST['note'])) {
                    updateEntreprise($_POST['id'], $_POST['nom'], $_POST['description'], $_POST['email'], $_POST['telephone'], $_POST['note']);
                    echo json_encode(['success' => true]);
                }
                break;
        }
    }
    exit;
}

// Définir le nombre d'entreprises par page
$entreprisesPerPage = 20;

// Récupérer la page actuelle depuis les paramètres GET (par défaut : 1)
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Récupérer le nombre total d'entreprises
$totalEntreprises = getTotalEntreprises();

// Calculer le nombre total de pages
$totalPages = ceil($totalEntreprises / $entreprisesPerPage);

// Récupérer les entreprises pour la page actuelle
$entreprises = getEntreprisesByPage($page, $entreprisesPerPage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des Entreprises</title>
    <link rel="stylesheet" href="../public/CSS/styles.css">
    <script>
        async function addEntreprise() {
            const response = await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'add' })
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            }
        }

        async function deleteEntreprise(id) {
            if (confirm("Êtes-vous sûr de vouloir supprimer cette entreprise ?")) {
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
                if (index < 5) { // Nom, Description, Email, Téléphone, Note
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
            const nom = cells[0].querySelector('input').value;
            const description = cells[1].querySelector('input').value;
            const email = cells[2].querySelector('input').value;
            const telephone = cells[3].querySelector('input').value;
            const note = cells[4].querySelector('input').value;

            const response = await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'update', id: id, nom: nom, description: description, email: email, telephone: telephone, note: note })
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
        <h1 class="nav-header">Gestion</h1>
        <nav class="nav-container">
            <ul class="nav-list">
                <li class="nav-item"><a class="nav-on nav-link">Entreprises</a></li>
                <li class="nav-item"><a href="./etudiants" class="nav-link">Étudiants</a></li>
                <li class="nav-item"><a href="./offres" class="nav-link">Offres</a></li>
                <li class="nav-item"><a href="./pilotes" class="nav-link">Pilotes</a></li>
                <li class="nav-item"><a href="../home" class="nav-link">Accueil</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <div class="top-bar">
            <button class="pagination-button" onclick="addEntreprise()">Ajouter une entreprise</button>
        </div>
        <section class="management-section">
            <table class="management-table">
                <thead>
                    <tr class="table-header-row">
                        <th class="table-header">Nom</th>
                        <th class="table-header">Description</th>
                        <th class="table-header">Email</th>
                        <th class="table-header">Téléphone</th>
                        <th class="table-header">Note</th>
                        <th class="table-header">Éditer</th>
                        <th class="table-header">Voir</th>
                        <th class="table-header">Supprimer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entreprises as $entreprise): ?>
                    <tr class="table-row">
                        <td class="table-data"><?= htmlspecialchars($entreprise['Nom']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($entreprise['Description']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($entreprise['Email']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($entreprise['Telephone']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($entreprise['Note']) ?></td>
                        <td class="table-data">
                            <button class="edit-button" onclick="editRow(this, <?= $entreprise['ID_Entreprise'] ?>)">Éditer</button>
                        </td>
                        <td class="table-data"><button class="view-button">Voir</button></td>
                        <td class="table-data">
                            <button class="delete-button" onclick="deleteEntreprise(<?= $entreprise['ID_Entreprise'] ?>)">Supprimer</button>
                        </td>
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
