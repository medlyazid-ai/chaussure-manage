<?php

require_once 'models/Product.php';
require_once 'models/Variant.php';

function listProducts()
{
    $search = $_GET['search'] ?? null;
    $category = $_GET['category'] ?? null;

    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;
    $total = Product::countFiltered($search, $category);
    $totalPages = (int)ceil($total / $perPage);

    $products = Product::filterWithVariants($search, $category, $perPage, $offset);
    $categories = Product::categories();
    include 'views/products/index.php';
}

function showCreateForm()
{
    include 'views/products/create.php';
}

function storeProduct()
{
    $data = $_POST;
    $data['variants'] = isset($_POST['variants']) ? $_POST['variants'] : [];
    try {
        Product::create($data, $_FILES);
        header('Location: ?route=products');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: ?route=products/create');
        exit;
    }
}


function showEditForm($id)
{
    $product = Product::find($id);
    $variants = Variant::findByProduct($id);
    include 'views/products/edit.php';
}

function updateProduct($id)
{
    Product::update($id, $_POST, $_POST['variants']);
    header('Location: ?route=products');
}

function deleteProduct($id)
{
    try {
        Product::delete($id);
        header('Location: ?route=products');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
        $products = Product::allWithVariants();
        include 'views/products/index.php';
    }
}
