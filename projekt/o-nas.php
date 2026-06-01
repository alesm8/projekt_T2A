<?php
require_once __DIR__ . '/src/bootstrap.php';

$pageTitle = 'O nás | Čajový svět';
require __DIR__ . '/partials/header.php';
?>

    <main style="max-width: 900px;">
        <h1 style="color: var(--heading-color); margin-bottom: 25px; text-align: center;">Náš příběh</h1>
        
        <div style="line-height: 1.8; font-size: 1.1rem; display: flex; flex-direction: column; gap: 20px;">
            <p>
                Vítejte v **Čajovém světě**! Jsme parta nadšenců a milovníků dobrého čaje. Naše cesta začala před několika lety, kdy jsme si uvědomili, že najít skutečně kvalitní, čerstvý sypaný čaj bez chemických aromat a náhražek je v běžných obchodech téměř nadlidský úkol.
            </p>

            <div style="display: flex; gap: 20px; flex-wrap: wrap; margin: 20px 0; align-items: center;">
                <img src="assets/images/wellness.jpg" alt="Čajová plantáž" style="flex: 1; min-width: 250px; border-radius: 15px; max-height: 250px; object-fit: cover;">
                <div style="flex: 1; min-width: 250px;">
                    <h3 style="color: var(--heading-color); margin-bottom: 10px;">Láska k tradici</h3>
                    <p>Osobně vybíráme ty nejkvalitnější čajové lístky z rodinných plantáží v Číně, Japonsku, Indii a na Cejlonu. Dbáme na férový přístup k pěstitelům a šetrnost k životnímu prostředí při sklizni i balení.</p>
                </div>
            </div>

            <p>
                Náš sortiment tvoří jak tradiční jednodruhové čaje (zelené, černé, oolongy), tak i naše vlastní originální bylinné a ovocné směsi, které mícháme ručně podle osvědčených rodinných receptur.
            </p>

            <blockquote style="border-left: 5px solid var(--primary-color); padding-left: 20px; font-style: italic; background: rgba(0,0,0,0.02); padding: 15px 20px; border-radius: 0 10px 10px 0; margin: 15px 0;">
                "Věříme, že šálek správně připraveného čaje dokáže vyřešit většinu každodenních starostí, uklidnit rozbouřenou mysl a přinést chvíli pohody do uspěchaného dne."
            </blockquote>

            <p>
                Děkujeme, že jste s námi na této voňavé cestě! Pokud máte jakékoli dotazy nebo si nevíte rady s výběrem, neváhejte nám napsat. Jsme tu pro vás.
            </p>
        </div>
    </main>

<?php
require __DIR__ . '/partials/footer.php';
?>
