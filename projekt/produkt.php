<?php
require_once __DIR__ . '/src/bootstrap.php';

$productRepo = new ProductRepository();
$categoryRepo = new CategoryRepository();

$slug = $_GET['slug'] ?? '';
$product = $productRepo->getBySlug($slug);

if (!$product) {
    header('Location: 404.php');
    exit;
}

$images = $productRepo->getImages($product->id);
$params = $productRepo->getParameters($product->id);

$selectableParams = array_filter($params, fn(ProductParameterDTO $p) => $p->isSelectable());
$infoParams = array_filter($params, fn(ProductParameterDTO $p) => !$p->isSelectable());

$category = $categoryRepo->getById($product->categoryId);

$pageTitle = $product->name . ' | Čajový svět';
require __DIR__ . '/partials/header.php';
?>

    <main>
        <!-- Breadcrumb navigation -->
        <div style="font-size: 0.9rem; opacity: 0.7; margin-bottom: 20px;">
            <a href="index.php">Domů</a> &gt; 
            <a href="kategorie.php">Kategorie čajů</a> &gt; 
            <?php if ($category): ?>
                <a href="produkty.php?category=<?= urlencode($category->slug) ?>"><?= htmlspecialchars($category->name) ?></a> &gt; 
            <?php endif; ?>
            <?= htmlspecialchars($product->name) ?>
        </div>

        <div class="product-detail-container">
            <!-- Left Side: Image Gallery -->
            <div class="product-gallery">
                <div class="main-image">
                    <img src="<?= htmlspecialchars($product->image) ?>" alt="<?= htmlspecialchars($product->name) ?>" id="mainImg">
                </div>
                <div class="thumbnail-images">
                    <!-- Base product image -->
                    <img src="<?= htmlspecialchars($product->image) ?>" alt="<?= htmlspecialchars($product->name) ?>" class="active" onclick="document.getElementById('mainImg').src=this.src; document.querySelectorAll('.thumbnail-images img').forEach(el=>el.classList.remove('active')); this.classList.add('active');">
                    <!-- Gallery images -->
                    <?php foreach ($images as $img): ?>
                        <img src="<?= htmlspecialchars($img->image) ?>" alt="Detail" onclick="document.getElementById('mainImg').src=this.src; document.querySelectorAll('.thumbnail-images img').forEach(el=>el.classList.remove('active')); this.classList.add('active');">
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right Side: Info & Actions -->
            <div class="product-info">
                <h1 style="color: var(--heading-color); margin-bottom: 15px;"><?= htmlspecialchars($product->name) ?></h1>
                
                <p style="font-size: 1.2rem; font-weight: bold; color: var(--primary-color); margin-bottom: 20px;">
                    <?= number_format($product->price, 0, ',', ' ') ?> Kč / 100g
                </p>
                
                <p style="line-height: 1.6; margin-bottom: 25px; opacity: 0.9;">
                    <?= htmlspecialchars($product->description) ?>
                </p>

                <!-- Purchase form -->
                <form class="checkout-form" action="cart-action.php" method="POST" style="margin-bottom: 30px;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $product->id ?>">
                    <input type="hidden" name="redirect_to" value="kosik.php">

                    <?php if ($product->hasVariants): ?>
                        <div>
                            <label for="variant" style="font-weight: bold; display: block; margin-bottom: 5px;">Hmotnost balení:</label>
                            <select id="variant" name="variant" style="padding: 10px; width: 100%; max-width: 300px; border-radius: 8px;">
                                <?php foreach ($selectableParams as $param): ?>
                                    <option value="<?= htmlspecialchars($param->value) ?>"><?= htmlspecialchars($param->value) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div>
                        <label for="qty" style="font-weight: bold; display: block; margin-bottom: 5px;">Množství:</label>
                        <input type="number" id="qty" name="quantity" value="1" min="1" style="width: 100px; padding: 10px; border-radius: 8px;">
                    </div>

                    <button type="submit" class="order-btn" style="border: none; cursor: pointer; display: inline-block; text-align: center;">🛒 Přidat do košíku</button>
                </form>

                <!-- Technical parameters table -->
                <?php if (!empty($infoParams)): ?>
                    <h3 style="color: var(--heading-color); margin-bottom: 10px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px;">Specifikace čaje</h3>
                    <table class="product-info-table">
                        <?php foreach ($infoParams as $param): ?>
                            <tr>
                                <th><?= htmlspecialchars($param->name) ?>:</th>
                                <td><?= htmlspecialchars($param->value) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>

<?php
require __DIR__ . '/partials/footer.php';
?>
