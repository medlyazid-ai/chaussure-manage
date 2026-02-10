<?php

require_once 'models/Variant.php';
require_once 'models/Product.php';

function listVariants()
{
    $productId = $_GET['product_id'] ?? null;
    $search = $_GET['search'] ?? null;
    $size = $_GET['size'] ?? null;
    $color = $_GET['color'] ?? null;

    $variants = Variant::filter($productId, $search, $size, $color);
    $products = Product::all();

    include 'views/variants/index.php';
}

function storeVariant()
{
    $productId = $_POST['product_id'] ?? null;
    $size = trim($_POST['size'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $sku = trim($_POST['sku'] ?? '');

    if (!$productId || $size === '' || $color === '') {
        $_SESSION['error'] = "Produit, taille et couleur obligatoires.";
        header('Location: ?route=variants');
        exit;
    }

    Variant::create($productId, $size, $color, $sku ?: null);
    $_SESSION['success'] = "Variante ajoutée.";
    header('Location: ?route=variants');
    exit;
}

function updateVariant($id)
{
    $size = trim($_POST['size'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $sku = trim($_POST['sku'] ?? '');

    if ($size === '' || $color === '') {
        $_SESSION['error'] = "Taille et couleur obligatoires.";
        header('Location: ?route=variants');
        exit;
    }

    Variant::update($id, $size, $color, $sku ?: null);
    $_SESSION['success'] = "Variante mise à jour.";
    header('Location: ?route=variants');
    exit;
}

function deleteVariant($id)
{
    try {
        Variant::delete($id);
        $_SESSION['success'] = "Variante supprimée.";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    header('Location: ?route=variants');
    exit;
}

function showVariantStockByCountry()
{
    header('Content-Type: text/html; charset=utf-8');
    $variantId = $_GET['variant_id'] ?? null;
    if (!$variantId) {
        echo '<div class="text-muted">Variante invalide.</div>';
        exit;
    }
    $rows = Variant::stockByCountry($variantId);
    if (empty($rows)) {
        echo '<div class="text-muted">Aucun stock pour cette variante.</div>';
        exit;
    }
    echo '<table class="table table-sm table-bordered mb-0">';
    echo '<thead><tr><th>Pays</th><th>Stock</th></tr></thead><tbody>';
    foreach ($rows as $r) {
        $flag = htmlspecialchars($r['flag']);
        $name = htmlspecialchars($r['country_name']);
        $stock = (int)$r['current_stock'];
        echo "<tr><td>$flag $name</td><td>$stock</td></tr>";
    }
    echo '</tbody></table>';
    exit;
}
