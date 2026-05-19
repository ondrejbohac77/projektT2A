<?php

/**
 * PARTIAL: Patička stránky
 *
 * Uzavírá HTML strukturu otevřenou v header.php.
 */

?>

<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-section">
            <h4>Informace</h4>
            <ul>
                <li><a href="o-nas.php">O nás</a></li>
                <li><a href="kontakt.php">Kontakt</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h4>Kontakt</h4>
            <p>Email: <a href="mailto:info@allforbikes.cz">info@allforbikes.cz</a></p>
            <p>Tel: +420 123 456 789</p>
        </div>
        <div class="footer-section">
            <h4>Sledujte nás</h4>
            <ul>
                <li><a href="404.php">Instagram</a></li>
                <li><a href="404.php">YouTube</a></li>
                <li><a href="404.php">Facebook</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <p class="disclaimer">UPOZORNĚNÍ: Tento web je fiktivní studentský projekt. Veškeré texty, ceny a fotografie jsou pouze ilustrační.</p>
            <p>&copy; <?= date('Y') ?> All for Bikes - Studentský projekt PRG</p>
        </div>
    </div>
</footer>

<!-- Propojení skriptu pro galerii a další interakce -->
<script src="/assets/script.js"></script>
</body>
</html>