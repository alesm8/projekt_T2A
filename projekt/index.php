<?php
require_once __DIR__ . '/src/bootstrap.php';

$productRepo = new ProductRepository();
$allProducts = $productRepo->getAll();

$pageTitle = 'Čajový svět | Domů';
require __DIR__ . '/partials/header.php';
?>

  <section class="hero">
    <h1 class="hero-title">ČAJOVÝ SVĚT</h1>
    <span class="scroll">⬇ sjeď dolů ⬇</span>
  </section>

  <!-- Main Content Area -->
  <main class="shop">
    <div class="leaves">
      <span></span><span></span><span></span><span></span><span></span>
      <span></span><span></span><span></span><span></span><span></span>
      <span></span><span></span>
    </div>

    <div class="shop-inner">
      <?php if (empty($allProducts)): ?>
        <p style="text-align: center; width: 100%; font-size: 1.2rem; grid-column: 1 / -1;">Žádné produkty nebyly nalezeny.</p>
      <?php else: ?>
        <?php foreach ($allProducts as $product): ?>
          <article class="produkt">
            <a href="produkt.php?slug=<?= urlencode($product->slug) ?>">
              <img src="<?= htmlspecialchars($product->image) ?>" alt="<?= htmlspecialchars($product->name) ?>">
              <p><?= htmlspecialchars($product->name) ?></p>
              <strong><?= number_format($product->price, 0, ',', ' ') ?> Kč / 100g</strong>
            </a>
            <a href="produkt.php?slug=<?= urlencode($product->slug) ?>" class="btn" style="margin-top: auto;">Detail produktu</a>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>

<?php
require __DIR__ . '/partials/footer.php';
?>