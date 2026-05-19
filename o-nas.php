<?php
declare(strict_types=1);
require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();

$pageTitle = 'O nás | All for Bikes';
$cartItemCount = $cart->getTotalQuantity();

require __DIR__ . '/partials/header.php';
?>

    <section class="hero-section">
        <div class="video-container">
            <video autoplay muted loop playsinline class="hero-video-bg">
                <source src="assets/images/FASTEST RUN OF MY LIFE - winning Red Bull Genova Cerro Abajo 2024.mp4" type="video/mp4">
            </video>
            <div class="hero-video-overlay">
                <div class="container">
                    <h1 class="hero-title">All for Bikes</h1>
                    <p class="hero-subtitle">Vše pro vaši jízdu na jednom místě</p>
                    <a href="produkty.php" class="btn-primary">Prozkoumat kola</a>
                </div>
            </div>
        </div>
    </section>

    <main class="container about-content">
        <div class="about-grid">
            <div class="about-text">
                <h2>Kdo jsme?</h2>
                <p>All for Bikes vzniklo z čisté vášně pro dvě kola. Nejsme jen obyčejný e-shop, jsme komunita jezdců, kteří testují každý model, který u nás najdete.</p>
                <h3 class="about-subtitle">Naše mise</h3>
                <p>Věříme, že kolo je nejlepší nástroj pro svobodu. Proto se zaměřujeme na značky, které posouvají hranice technologií a designu.</p>
            </div>
            <div class="about-features">
                <div class="feature-item">
                    <span class="feature-icon">✔</span>
                    <h4>Odborné poradenství</h4>
                    <p>Sami jezdíme, takže víme, co prodáváme.</p>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">✔</span>
                    <h4>Prémiové značky</h4>
                    <p>Santa Cruz, Specialized, Kona a další.</p>
                </div>
            </div>
        </div>
    </main>

    <section class="container team-section">
        <h2 class="section-title">Lidé za All for Bikes</h2>
        <div class="product-grid">
            <article class="product-card team-card">
                <div class="product-image"><img src="assets/images/team1.png" alt="Dano Mašinka" class="team-photo"></div>
                <h3 class="product-title">Dano "Enduro" Mašinka</h3>
                <p class="product-type">Zakladatel & Hlavní tester</p>
                <p class="team-desc">Dano založil All for Bikes po 10 letech závodění. Má na starosti výběr těch nejlepších kol.</p>
            </article>
            <article class="product-card team-card">
                <div class="product-image"><img src="assets/images/team2.png" alt="Venda Benda" class="team-photo"></div>
                <h3 class="product-title">Venda Benda</h3>
                <p class="product-type">Vedoucí servisu</p>
                <p class="team-desc">Pokud vám v kole něco skřípe, Venda je ten, kdo to opraví. Expert na seřízení a nastavení.</p>
            </article>
            <article class="product-card team-card">
                <div class="product-image"><img src="assets/images/team3.png" alt="Lucie Kolářová" class="team-photo"></div>
                <h3 class="product-title">Lucie Kolářová</h3>
                <p class="product-type">Community manager</p>
                <p class="team-desc">Organizátorka našich společných vyjížděk a expertka na gravel biky.</p>
            </article>
        </div>
    </section>

<?php require __DIR__ . '/partials/footer.php'; ?>