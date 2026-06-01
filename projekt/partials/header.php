<?php
require_once __DIR__ . '/../src/bootstrap.php';

$cart = new Cart();
$cartItemCount = $cart->getTotalQuantity();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Čajový svět') ?></title>
  <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
<header class="header-bar">
  <div class="header-logo">
    <a href="index.php">🍵 Čajový Svět</a>
  </div>
  <nav>
    <ul class="nav-links">
      <li><a href="index.php" <?php if (basename($_SERVER['PHP_SELF']) === 'index.php') echo 'class="active"'; ?>>Domů</a></li>
      <li><a href="kategorie.php" <?php if (basename($_SERVER['PHP_SELF']) === 'kategorie.php') echo 'class="active"'; ?>>Kategorie čajů</a></li>
      <li><a href="o-nas.php" <?php if (basename($_SERVER['PHP_SELF']) === 'o-nas.php') echo 'class="active"'; ?>>O nás</a></li>
      <li><a href="kontakt.php" <?php if (basename($_SERVER['PHP_SELF']) === 'kontakt.php') echo 'class="active"'; ?>>Kontakt</a></li>
      <li><a href="vyhledavani.php" <?php if (basename($_SERVER['PHP_SELF']) === 'vyhledavani.php') echo 'class="active"'; ?>>🔍 Hledat</a></li>
      <li><a href="kosik.php" <?php if (basename($_SERVER['PHP_SELF']) === 'kosik.php') echo 'class="active"'; ?>>🛒 Košík (<?= $cartItemCount ?>)</a></li>
    </ul>
  </nav>
</header>
