<?php
require_once __DIR__ . '/src/bootstrap.php';

$pageTitle = 'Kontakt | Čajový svět';
require __DIR__ . '/partials/header.php';
?>

    <main>
        <h1 style="color: var(--heading-color); margin-bottom: 30px; text-align: center;">Napište nám</h1>
        
        <div style="display: flex; gap: 40px; flex-wrap: wrap; margin-top: 20px;">
            <!-- Left Side: Contact details -->
            <div style="flex: 1; min-width: 280px; display: flex; flex-direction: column; gap: 20px;">
                <h2 style="color: var(--heading-color);">Kontaktní údaje</h2>
                
                <p>Máte nějaký dotaz, připomínku nebo zájem o spolupráci? Neváhejte nás kontaktovat prostřednictvím telefonu, e-mailu nebo kontaktního formuláře.</p>
                
                <div>
                    <p><strong>📞 Telefon:</strong> +420 123 456 789</p>
                    <p><strong>✉ E-mail:</strong> <a href="mailto:ales.macicek@frengp.com" style="color: var(--primary-color); font-weight: bold;">ales.macicek@frengp.com</a></p>
                </div>

                <div>
                    <h3>📍 Adresa kamenné prodejny:</h3>
                    <p>Čajový Svět s.r.o.</p>
                    <p>Veselá 45/2</p>
                    <p>602 00 Brno</p>
                </div>

                <div>
                    <h3>🕒 Otevírací doba:</h3>
                    <p>Pondělí - Pátek: 9:00 - 18:00</p>
                    <p>Sobota: 9:00 - 13:00</p>
                    <p>Neděle: Zavřeno</p>
                </div>
            </div>

            <!-- Right Side: Inquiry Form -->
            <div style="flex: 1; min-width: 280px; background: rgba(0,0,0,0.02); padding: 30px; border-radius: 20px; box-shadow: inset 0 2px 5px rgba(0,0,0,0.02);">
                <h2 style="color: var(--heading-color); margin-bottom: 20px;">Kontaktní formulář</h2>
                
                <form class="checkout-form" onsubmit="alert('Zpráva byla odeslána!'); return false;">
                    <div>
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Vaše jméno *</label>
                        <input type="text" placeholder="Jan Novák" required>
                    </div>

                    <div>
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">E-mailová adresa *</label>
                        <input type="email" placeholder="jan.novak@email.cz" required>
                    </div>

                    <div>
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Zpráva *</label>
                        <textarea placeholder="Sem napište váš dotaz..." style="min-height: 120px;" required></textarea>
                    </div>

                    <button type="submit" class="order-btn" style="margin-top: 10px; cursor: pointer; border: none;">Odeslat zprávu</button>
                </form>
            </div>
        </div>
    </main>

<?php
require __DIR__ . '/partials/footer.php';
?>
