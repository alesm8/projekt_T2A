<?php
require_once __DIR__ . '/src/bootstrap.php';

$productRepo = new ProductRepository();
$featuredProducts = $productRepo->getFeatured();

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
      <?php if (empty($featuredProducts)): ?>
        <p style="text-align: center; width: 100%; font-size: 1.2rem; grid-column: 1 / -1;">Žádné doporučené produkty nebyly nalezeny.</p>
      <?php else: ?>
        <?php foreach ($featuredProducts as $product): ?>
          <article class="produkt">
            <a href="#p<?= $product->id ?>">
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

  <!-- Modals for details on homepage -->
  <?php foreach ($featuredProducts as $product): ?>
    <div id="p<?= $product->id ?>" class="popup">
      <div class="popup-content">
        <a href="#!" class="close">✖</a>
        <div class="popup-inner">
          <div class="popup-image">
            <img src="<?= htmlspecialchars($product->image) ?>" alt="<?= htmlspecialchars($product->name) ?>">
          </div>
          <div class="popup-text">
            <h2><?= htmlspecialchars($product->name) ?></h2>
            <p><?= htmlspecialchars($product->description) ?></p>
            <strong><?= number_format($product->price, 0, ',', ' ') ?> Kč / 100g</strong>
            
            <form action="cart-action.php" method="POST" style="margin-top: 15px;">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= $product->id ?>">
              <input type="hidden" name="redirect_to" value="index.php">
              <button type="submit" class="btn" style="border: none; cursor: pointer; display: inline-block;">Přidat do košíku</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

<?php
require __DIR__ . '/partials/footer.php';
?>