<!DOCTYPE php>
<php lang="fr">
<?php include './views/partials/navbar.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StageHUB - Trouvez votre stage facilement</title>
    <link rel="stylesheet" href="./public/CSS/styles.css">
</head>
<body>

<main>
    <section class="description">
        <h2>Bienvenue sur StageHUB</h2>
        <p>Découvrez des offres adaptées à votre profil, explorez les entreprises et créez votre wishlist !</p>
    </section>

    <?php
    // Inclure le contrôleur et initialiser la base de données
    require_once __DIR__ . '/../../controllers/HomeController.php';

    $stats = $model->getStats();?>

<section class="stats">
    <div><h3><?= $stats['offres'] ?></h3><p>Offres de stage</p></div>
    <div><h3><?= $stats['entreprises'] ?></h3><p>Entreprises partenaires</p></div>
    <div><h3><?= $stats['etudiants'] ?></h3><p>Étudiants inscrits</p></div>
</section>


    <section class="images-container">
        <a href="offres"><img src="placeholder.png" alt="Offres"><p>Voir les offres</p></a>
        <a href="entreprises"><img src="placeholder.png" alt="Entreprises"><p>Découvrir les entreprises</p></a>
        <a href="wishlist"><img src="placeholder.png" alt="Whishlist"><p>Gérer ma WishList</p></a>
    </section>
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