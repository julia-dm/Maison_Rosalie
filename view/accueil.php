<?php
require_once __DIR__ . "/inc/header.php";

// Image du praliné : version Figma optimisée si elle existe, sinon la vignette de la carte
function pralineImage(string $mainImage): string
{
    $name = pathinfo($mainImage, PATHINFO_FILENAME);
    return file_exists(RACINE_PATH . "/public/images/figma/$name.webp")
        ? "images/figma/$name.webp"
        : "images/cards/$mainImage";
}
?>

<main>
    <section class="hero">
        <div class="container">
            <div class="hero-text">
                <h1 class="hero-title">Nos créations, pour vos moments !</h1>
                <p class="hero-subtitle">Découvrez nos nouvelles recettes.<br>
                    Une création printanière inspirée des fruits confits.<br>
                    Des saveurs délicates, imaginées pour surprendre.</p>
                <a class="hero-link" href="?page=recettes">Découvrez nos recettes</a>
            </div>
            <div class="hero-img">
                <img src="images/figma/orange.webp" width="600" height="647" alt="Praliné à l'orange glacé, décoré d'une feuille et d'un zeste confit">
            </div>
        </div>
    </section>

    <section class="creations" aria-labelledby="creations-title">
        <h2 class="creations-eyebrow" id="creations-title">Une nouvelle page se dévoile</h2>
        <p class="creations-subtitle">Quatre créations, quatre éclats de gourmandise.</p>

        <?php if (!empty($recipes)): ?>
            <ul class="creations-list">
                <?php foreach ($recipes as $recipe): ?>
                    <li class="creations-item">
                        <a href="?page=recetteDetails&amp;slug=<?= urlencode($recipe->getSlug()) ?>">
                            <img src="<?= htmlspecialchars(pralineImage($recipe->getMainImage())) ?>" loading="lazy" alt="">
                            <span class="creations-name"><?= htmlspecialchars($recipe->getTitle()) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="creations-empty">Nos recettes arrivent bientôt.</p>
        <?php endif; ?>
    </section>

    <section class="atelier-banner" aria-label="Dans l'atelier de la Maison Rosalie">
        <img src="images/figma/atelier-banner.jpg" loading="lazy" alt="Mains de chocolatier fouettant de la ganache dans un bol, à côté d'un carnet de recettes">
    </section>
</main>
<?php
require_once __DIR__ . "/inc/footer.php";
?>
