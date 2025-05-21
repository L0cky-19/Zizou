<?php
require_once './config/database.php';

// Fonction pour récupérer le nombre total d'étudiants
function getTotalStudents() {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) FROM Users WHERE Role = 'user'");
    return $stmt->fetchColumn();
}

// Fonction pour récupérer les étudiants avec pagination
function getStudentsByPage($page, $studentsPerPage) {
    $offset = ($page - 1) * $studentsPerPage;
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT ID_User, Nom, Prenom, Email FROM Users WHERE Role = 'user' LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $studentsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour ajouter un étudiant
function addStudent() {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("INSERT INTO Users (Nom, Prenom, Email, MotDePasse, Role) VALUES ('', '', '', MD5('default_password'), 'user')");
    $stmt->execute();
    return $pdo->lastInsertId();
}

// Fonction pour supprimer un étudiant
function deleteStudent($id) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("DELETE FROM Users WHERE ID_User = ?");
    $stmt->execute([$id]);
}

// Fonction pour mettre à jour un étudiant
function updateStudent($id, $nom, $prenom, $email, $password = null) {
    $pdo = Database::getConnection();
    if ($password) {
        $hashedPassword = md5($password);
        $stmt = $pdo->prepare("UPDATE Users SET Nom = ?, Prenom = ?, Email = ?, MotDePasse = ? WHERE ID_User = ?");
        $stmt->execute([$nom, $prenom, $email, $hashedPassword, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE Users SET Nom = ?, Prenom = ?, Email = ? WHERE ID_User = ?");
        $stmt->execute([$nom, $prenom, $email, $id]);
    }
}

// Gestion des requêtes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $id = addStudent();
                echo json_encode(['success' => true, 'id' => $id]);
                break;
            case 'delete':
                if (isset($_POST['id'])) {
                    deleteStudent($_POST['id']);
                    echo json_encode(['success' => true]);
                }
                break;
            case 'update':
                if (isset($_POST['id'], $_POST['nom'], $_POST['prenom'], $_POST['email'])) {
                    $password = $_POST['password'] ?? null;
                    updateStudent($_POST['id'], $_POST['nom'], $_POST['prenom'], $_POST['email'], $password);
                    echo json_encode(['success' => true]);
                }
                break;
        }
    }
    exit;
}

// Pagination
$studentsPerPage = 10;
$totalStudents = getTotalStudents();
$totalPages = ceil($totalStudents / $studentsPerPage);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) $page = 1;
if ($page > $totalPages) $page = $totalPages;

$students = getStudentsByPage($page, $studentsPerPage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des Étudiants</title>
    <link rel="stylesheet" href="../public/CSS/styles.css">
    <script>
        async function addStudent() {
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

        async function deleteStudent(id) {
            if (confirm("Êtes-vous sûr de vouloir supprimer cet étudiant ?")) {
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
                if (index < 4) { // Nom, Prénom, Email, Mot de passe
                    const currentText = cell.innerText;
                    if (index === 3) { // Mot de passe (vide par défaut pour édition)
                        cell.innerHTML = `<input type='password' placeholder='Nouveau mot de passe'>`;
                    } else {
                        cell.innerHTML = `<input type='text' value='${currentText}'>`;
                    }
                }
            });
            button.innerText = 'Sauvegarder';
            button.setAttribute('onclick', `saveRow(this, ${id})`);
        }

        async function saveRow(button, id) {
            const row = button.closest('tr');
            const cells = row.querySelectorAll('.table-data');
            const nom = cells[0].querySelector('input').value;
            const prenom = cells[1].querySelector('input').value;
            const email = cells[2].querySelector('input').value;
            const password = cells[3].querySelector('input').value;

            const response = await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'update', id: id, nom: nom, prenom: prenom, email: email, password: password })
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
                <li class="nav-item"><a href="entreprises" class="nav-link">Entreprises</a></li>
                <li class="nav-item"><a class="nav-on nav-link">Étudiants</a></li>
                <li class="nav-item"><a href="offres" class="nav-link">Offres</a></li>
                <li class="nav-item"><a href="pilotes" class="nav-link">Pilotes</a></li>
                <li class="nav-item"><a href="../home" class="nav-link">Accueil</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <div class="top-bar">
            <button class="pagination-button" onclick="addStudent()">Ajouter un étudiant</button>
        </div>
        <section class="management-section">
            <table class="management-table">
                <thead>
                    <tr class="table-header-row">
                        <th class="table-header">Nom</th>
                        <th class="table-header">Prénom</th>
                        <th class="table-header">Email</th>
                        <th class="table-header">Mot de passe</th>
                        <th class="table-header">Éditer</th>
                        <th class="table-header">Voir</th>
                        <th class="table-header">Supprimer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr class="table-row">
                        <td class="table-data"><?= htmlspecialchars($student['Nom']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($student['Prenom']) ?></td>
                        <td class="table-data"><?= htmlspecialchars($student['Email']) ?></td>
                        <td class="table-data">********</td>
                        <td class="table-data">
                            <button class="edit-button" onclick="editRow(this, <?= $student['ID_User'] ?>)">Éditer</button>
                        </td>
                        <td class="table-data"><button class="view-button">Voir</button></td>
                        <td class="table-data">
                            <button class="delete-button" onclick="deleteStudent(<?= $student['ID_User'] ?>)">Supprimer</button>
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
