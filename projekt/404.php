<?php
require_once __DIR__ . '/src/bootstrap.php';

$pageTitle = 'Stránka nenalezena | Čajový svět';
require __DIR__ . '/partials/header.php';
?>

    <main style="text-align: center; padding: 80px 20px;">
        <span style="font-size: 6rem;">🍃</span>
        <h1 style="color: var(--heading-color); font-size: 3rem; margin-top: 20px; margin-bottom: 10px;">Chyba 404</h1>
        <h2 style="margin-bottom: 20px; font-weight: normal; opacity: 0.8;">Tato stránka byla odfouknuta větrem...</h2>
        <p style="max-width: 500px; margin: 0 auto 30px; line-height: 1.6; opacity: 0.9;">
            Omlouváme se, ale hledaná stránka neexistuje. Možná byla přesunuta, přejmenována nebo jste zadali špatnou adresu.
        </p>
        <a href="index.php" class="btn" style="padding: 15px 30px; font-size: 1.1rem;">Návrat domů</a>
    </main>

<?php
require __DIR__ . '/partials/footer.php';
?>
