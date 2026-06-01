<?php
require_once __DIR__ . '/src/bootstrap.php';

$categoryRepo = new CategoryRepository();
$categories = $categoryRepo->getAll();

$pageTitle = 'Kategorie čajů | Čajový svět';
require __DIR__ . '/partials/header.php';
?>

    <main>
        <h1 style="color: var(--heading-color); margin-bottom: 20px; text-align: center;">Kategorie čajů</h1>
        <p style="text-align: center; margin-bottom: 40px; font-size: 1.2rem; opacity: 0.8;">Vyberte si ze široké nabídky našich výběrových čajů z celého světa.</p>
        
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
                <article class="category-card">
                    <img src="<?= htmlspecialchars($cat->image) ?>" alt="<?= htmlspecialchars($cat->name) ?>">
                    <div class="category-info">
                        <h2><?= htmlspecialchars($cat->name) ?></h2>
                        <p><?= htmlspecialchars($cat->description) ?></p>
                        <a href="produkty.php?category=<?= urlencode($cat->slug) ?>" class="btn">Zobrazit produkty →</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </main>

<?php
require __DIR__ . '/partials/footer.php';
?>
