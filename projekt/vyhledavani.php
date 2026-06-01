<?php
require_once __DIR__ . '/src/bootstrap.php';

$query = $_GET['q'] ?? '';
$products = [];

if (trim($query) !== '') {
    $productRepo = new ProductRepository();
    $products = $productRepo->search($query);
}

$pageTitle = 'Vyhledávání | Čajový svět';
require __DIR__ . '/partials/header.php';
?>

    <main>
        <h1 style="color: var(--heading-color); margin-bottom: 25px;">Hledat produkty</h1>
        
        <!-- Search bar container -->
        <form class="search-container" action="vyhledavani.php" method="GET" style="display: flex; gap: 10px; margin-bottom: 30px;">
            <input type="text" name="q" placeholder="Zadejte název čaje (např. zelený, černý, ovocný...)" value="<?= htmlspecialchars($query) ?>" style="padding: 15px; font-size: 1.1rem; flex: 1; border-radius: 10px; border: 1px solid var(--border-color);">
            <button type="submit" class="btn" style="margin-top: 0; padding: 15px 30px; cursor: pointer; border: none;">Vyhledat 🔍</button>
        </form>

        <?php if (trim($query) !== ''): ?>
            <p style="margin-bottom: 30px; font-size: 1.1rem; opacity: 0.8;">Výsledky vyhledávání pro: <strong>"<?= htmlspecialchars($query) ?>"</strong> (nalezeno <?= count($products) ?> produktů)</p>

            <div class="shop-inner" style="width: 100%; padding: 0; background: transparent; box-shadow: none; display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
                <?php if (empty($products)): ?>
                    <p style="grid-column: 1 / -1; opacity: 0.7;">Žádné produkty nebyly nalezeny.</p>
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
        <?php endif; ?>
    </main>

<?php
require __DIR__ . '/partials/footer.php';
?>
