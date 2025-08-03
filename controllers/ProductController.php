<?php

require_once 'models/Product.php';
require_once 'models/Variant.php';

function listProducts()
{
    $products = Product::allWithVariants();
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
    Product::create($data, $_FILES);
    header('Location: ?route=products');
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
