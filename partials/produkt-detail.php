<?php

/**
 * PARTIAL: Detail produktu
 *
 * Tento soubor je určen k vložení do produkt.php.
 * Obsahuje pouze HTML strukturu pro zobrazení detailu produktu.
 * Data jsou předána z nadřazeného souboru.
 *
 * Očekává proměnné:
 *   $product (ProductDTO)
 *   $images (list<ProductImageDTO>)
 *   $selectableParams (list<ProductParameterDTO>)
 *   $infoParams (list<ProductParameterDTO>)
 */

?>
<div class="product-main-card">
    <div class="product-gallery">
        <div class="slider-container">
            <!-- Hlavní obrázek produktu -->
            <img src="<?= htmlspecialchars($product->image) ?>" alt="<?= htmlspecialchars($product->name) ?>" class="product-slider-img">

            <!-- Doplňkové obrázky z galerie -->
            <?php if (!empty($images)): ?>
                <?php foreach ($images as $img): ?>
                    <?php if (!empty($img->image)): ?>
                        <img src="<?= htmlspecialchars($img->image) ?>" alt="<?= htmlspecialchars($product->name) ?> detail" class="product-slider-img">
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Navigační šipky -->
        <div class="slider-nav">
            <button type="button" class="prev-btn" aria-label="Předchozí obrázek">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                </svg>
            </button>
            <button type="button" class="next-btn" aria-label="Další obrázek">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Pravá strana – info -->
    <div class="product-buy-box">
        <span class="product-category-tag">
            <?= htmlspecialchars($product->categoryName ?? '') ?>
        </span>

        <h1 class="product-detail-title">
            <?= htmlspecialchars($product->name) ?>
        </h1>

        <p class="product-short-info">
            <?= htmlspecialchars($product->description) ?>
        </p>

        <!-- Formulář s výběrem variant a tlačítkem přidat do košíku (jediný formulář) -->
        <form method="post" style="margin-top: 20px;">
            <input type="hidden" name="product_id" value="<?= $product->id ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <?php foreach ($selectableParams as $param): ?>
                <div class="form-group">
                    <label for="variant-<?= htmlspecialchars($param->name) ?>">
                        <?= htmlspecialchars($param->name) ?>:
                    </label>
                    <select
                        name="variants[<?= htmlspecialchars($param->name) ?>]"
                        id="variant-<?= htmlspecialchars($param->name) ?>"
                        class="form-control"
                        required
                    >
                        <option value="">-- Vyberte --</option>
                        <?php foreach ($param->getOptions() as $option): ?>
                            <option value="<?= htmlspecialchars($option) ?>">
                                <?= htmlspecialchars($option) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endforeach; ?>

            <button type="submit" name="add_to_cart" class="btn-add-to-cart-large">
                <div class="btn-price-group">
                    <?php if ($product->hasDiscount()): ?>
                        <span class="original-price-large"><?= number_format($product->originalPrice, 0, ',', ' ') ?> Kč</span>
                    <?php endif; ?>
                    <span class="price-large"><?= number_format($product->price, 0, ',', ' ') ?> Kč</span>
                </div>
                <span class="btn-divider"></span>
                <span class="btn-label">Vložit do košíku</span>
            </button>
        </form>
    </div>
</div>

<?php if (!empty($infoParams) || !empty($product->description)): // Zobrazí wrapper pouze pokud je co zobrazit ?>
<div class="product-description-wrapper">
    <?php if (!empty($infoParams)): // Zobrazí technické parametry, pokud existují ?>
    <h2 class="about-subtitle">Technické parametry</h2>
    <table class="params-table">
        <?php foreach ($infoParams as $param): ?>
        <tr>
            <th><?= htmlspecialchars($param->name) ?></th>
            <td><?= htmlspecialchars($param->value) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <?php if (!empty($product->description)): // Zobrazí detailní popis, pokud existuje ?>
    <h2 class="about-subtitle">Detailní popis</h2>
    <div class="description-text">
        <p><?= htmlspecialchars($product->description) ?></p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>