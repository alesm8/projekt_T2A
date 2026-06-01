<?php
require_once __DIR__ . '/src/bootstrap.php';

$categoryRepo = new CategoryRepository();
$productRepo = new ProductRepository();

$categories = $categoryRepo->getAll();

// Get active category slug from query param
$categorySlug = $_GET['category'] ?? '';

// Fallback to first category if none is selected
if (empty($categorySlug) && !empty($categories)) {
    $categorySlug = $categories[0]->slug;
}

$activeCategory = $categoryRepo->getBySlug($categorySlug);

if ($activeCategory) {
    $products = $productRepo->getByCategorySlug($activeCategory->slug);
    $pageTitle = $activeCategory->name . ' | Čajový svět';
} else {
    $products = [];
    $pageTitle = 'Produkty | Čajový svět';
}

require __DIR__ . '/partials/header.php';
?>

    <main>
        <div style="display: flex; gap: 30px; flex-wrap: wrap; margin-top: 20px;">
            <!-- Sidebar Filters -->
            <aside style="flex: 1; min-width: 200px; background: rgba(0,0,0,0.03); padding: 20px; border-radius: 20px;">
                <h3 style="color: var(--heading-color); margin-bottom: 15px;">Kategorie</h3>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px;">
                    <?php foreach ($categories as $cat): ?>
                        <?php 
                        $isActive = ($cat->slug === $categorySlug);
                        $style = $isActive ? 'font-weight: bold; color: var(--primary-color);' : '';
                        ?>
                        <li>
                            <a href="produkty.php?category=<?= urlencode($cat->slug) ?>" style="<?= $style ?>">
                                🌿 <?= htmlspecialchars($cat->name) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                

            </aside>

            <!-- Product Grid Area -->
            <div style="flex: 3; min-width: 300px;">
                <?php if ($activeCategory): ?>
                    <h1 style="color: var(--heading-color); margin-bottom: 10px;"><?= htmlspecialchars($activeCategory->name) ?></h1>
                    <p style="margin-bottom: 30px; opacity: 0.8;"><?= htmlspecialchars($activeCategory->description ?? 'Výběr těch nejlepších produktů.') ?></p>
                <?php else: ?>
                    <h1 style="color: var(--heading-color); margin-bottom: 10px;">Produkty</h1>
                    <p style="margin-bottom: 30px; opacity: 0.8;">Vyberte kategorii čajů ze seznamu.</p>
                <?php endif; ?>
                
                <div class="shop-inner" style="width: 100%; padding: 0; background: transparent; box-shadow: none; display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
                    <?php if (empty($products)): ?>
                        <p style="grid-column: 1 / -1; opacity: 0.7;">V této kategorii nejsou žádné produkty.</p>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <article class="produkt" style="display: flex; flex-direction: column;">
                                <img src="<?= htmlspecialchars($product->image) ?>" alt="<?= htmlspecialchars($product->name) ?>">
                                <p><?= htmlspecialchars($product->name) ?></p>
                                <strong><?= number_format($product->price, 0, ',', ' ') ?> Kč / 100g</strong>
                                <a href="produkt.php?slug=<?= urlencode($product->slug) ?>" class="btn" style="margin-top: auto;">Detail produktu</a>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

<?php
require __DIR__ . '/partials/footer.php';
?>
