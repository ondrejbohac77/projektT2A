<?php
/**
 * PARTIAL: Produktová karta (pro výpisy v kategoriích a na HP)
 */
?>
<article class="product-card"> 
    <h3 class="product-title">
        <a href="produkt.php?slug=<?= htmlspecialchars($product->slug) ?>">
            <?= htmlspecialchars($product->name) ?>
        </a>
    </h3>
    <p class="product-type"><?= htmlspecialchars($product->categoryName ?? '') ?></p>
    <div class="product-image">
        <a href="produkt.php?slug=<?= htmlspecialchars($product->slug) ?>">
            <div class="product-badge-wrapper">
                <?php if ($product->hasDiscount()): ?>
                    <span class="badge badge-discount">Sleva</span>
                <?php endif; ?>
                <?php if ($product->featured): ?>
                    <span class="badge badge-recommended">Doporučujeme</span>
                <?php endif; ?>
            </div>
            <img src="<?= htmlspecialchars($product->image) ?>" alt="<?= htmlspecialchars($product->name) ?>">
        </a>
    </div>
    <div class="product-price-row">
        <?php if ($product->hasVariants): ?>
            <a href="produkt.php?slug=<?= htmlspecialchars($product->slug) ?>" class="btn-price-buy">
                <div class="card-price-group">
                    <?php if ($product->hasDiscount()): ?>
                        <span class="original-price"><?= number_format($product->originalPrice, 0, ',', ' ') ?> Kč</span>
                    <?php endif; ?>
                    <span class="price"><?= number_format($product->price, 0, ',', ' ') ?> Kč</span>
                </div>
                <span class="btn-divider"></span>
                <span class="variant-label">Vybrat variantu</span>
            </a>
        <?php else: ?>
            <form method="post" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" class="add-to-cart-form"> 
                <input type="hidden" name="product_id" value="<?= $product->id ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <button type="submit" name="add_to_cart" class="btn-price-buy">
                    <div class="card-price-group">
                        <?php if ($product->hasDiscount()): ?>
                            <span class="original-price"><?= number_format($product->originalPrice, 0, ',', ' ') ?> Kč</span>
                        <?php endif; ?>
                        <span class="price"><?= number_format($product->price, 0, ',', ' ') ?> Kč</span>
                    </div>
                    <span class="btn-divider"></span>
                    <span class="variant-label">Do košíku</span>
                </button>
            </form>
        <?php endif; ?>
    </div>
</article>