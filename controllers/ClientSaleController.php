<?php

require_once 'models/Country.php';
require_once 'models/RealStock.php';
require_once 'models/Variant.php';
require_once 'models/ClientSale.php';
require_once 'models/ClientSaleItem.php';

function storeClientSale()
{
    global $pdo;

    $saleDate = $_POST['sale_date'] ?? null;
    $countryId = $_POST['country_id'] ?? null;
    $customerName = $_POST['customer_name'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $variantIds = $_POST['variant_id'] ?? [];
    $quantities = $_POST['quantity_sold'] ?? [];

    if (!$saleDate || !$countryId || empty($variantIds) || empty($quantities)) {
        $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis.";
        header("Location: ?route=client_sales/create&country_id=" . $countryId);
        exit;
    }

    // ✅ Upload du fichier justificatif
    $proofPath = null;
    if (!empty($_FILES['proof_file']['name'])) {
        $uploadDir = 'uploads/sales_proofs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['proof_file']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['proof_file']['tmp_name'], $targetPath)) {
            $proofPath = $targetPath;
        }
    }

    try {
        $pdo->beginTransaction();

        // ✅ Insertion de la facture
        $saleId = ClientSale::create($saleDate, $countryId, $customerName, $notes, $proofPath);

        // ✅ Insertion des lignes
        for ($i = 0; $i < count($variantIds); $i++) {
            $variantId = (int) $variantIds[$i];
            $quantity = (int) $quantities[$i];
            if ($variantId && $quantity > 0) {
                ClientSaleItem::create($saleId, $variantId, $quantity);
            }
        }

        $pdo->commit();
        $_SESSION['success'] = "Facture enregistrée avec succès.";
        header("Location: ?route=stocks");
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
        header("Location: ?route=client_sales/create&country_id=" . $countryId);
    }

    exit;
}


function listClientSales()
{
    $sales = ClientSale::getAllWithCountry();
    include 'views/client_sales/index.php';
}




function createClientSale($countryId = null)
{
    // 📍 Charger tous les pays pour le menu déroulant
    $countries = Country::all();

    // ⚠️ Si aucun pays sélectionné → afficher choix uniquement
    if (!$countryId) {
        include 'views/client_sales/select_country.php';
        return;
    }

    // 📍 Charger uniquement les variantes en stock réel > 0 pour ce pays
    $variants = RealStock::getAvailableVariantsByCountry($countryId);

    // 🔁 Charger le pays sélectionné
    $selectedCountry = Country::getById($countryId);

    include 'views/client_sales/create.php';
}

function showClientSale($id)
{
    $sale = ClientSale::findWithCountry($id);
    $items = ClientSaleItem::getItemsWithDetails($id);

    if (!$sale) {
        $_SESSION['error'] = "Facture introuvable.";
        header("Location: ?route=client_sales");
        exit;
    }

    include 'views/client_sales/show.php';
}


function showCountrySelectionForm()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM countries");
    $countries = $stmt->fetchAll();
    include 'views/client_sales/select_country.php';
}


function editClientSale($id)
{
    // 📍 Récupérer la facture principale
    $sale = ClientSale::findWithCountry($id);
    if (!$sale) {
        $_SESSION['error'] = "Facture introuvable.";
        header("Location: ?route=client_sales");
        exit;
    }

    // 📍 Récupérer les lignes de vente
    $items = ClientSaleItem::getItemsWithDetails($id);

    // 📍 Charger la liste des pays pour permettre le changement
    $countries = Country::all();

    // 📍 Charger les variantes disponibles dans le pays sélectionné
    $variants = RealStock::getAvailableVariantsByCountry($sale['country_id']);

    // 📍 Charger la vue d’édition
    include 'views/client_sales/edit.php';
}

function updateClientSale($id)
{
    global $pdo;

    $saleDate = $_POST['sale_date'] ?? null;
    $countryId = $_POST['country_id'] ?? null;
    $customerName = $_POST['customer_name'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $variantIds = $_POST['variant_id'] ?? [];
    $quantities = $_POST['quantity_sold'] ?? [];

    if (!$saleDate || !$countryId || empty($variantIds) || empty($quantities)) {
        $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis.";
        header("Location: ?route=client_sales/edit/$id");
        exit;
    }

    // ✅ Gestion du fichier justificatif
    $proofPath = null;
    if (!empty($_FILES['proof_file']['name'])) {
        $uploadDir = 'uploads/sales_proofs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['proof_file']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['proof_file']['tmp_name'], $targetPath)) {
            $proofPath = $targetPath;
        }
    }

    try {
        $pdo->beginTransaction();

        // 🔁 Mettre à jour la facture principale
        ClientSale::update($id, $saleDate, $countryId, $customerName, $notes, $proofPath);

        // 🧹 Supprimer les anciennes lignes
        ClientSaleItem::deleteBySaleId($id);

        // ✅ Réinsérer les lignes avec les nouvelles données
        for ($i = 0; $i < count($variantIds); $i++) {
            $variantId = (int) $variantIds[$i];
            $quantity = (int) $quantities[$i];
            if ($variantId && $quantity > 0) {
                ClientSaleItem::create($id, $variantId, $quantity);
            }
        }

        $pdo->commit();
        $_SESSION['success'] = "Facture mise à jour avec succès.";
        header("Location: ?route=client_sales/show/$id");
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
        header("Location: ?route=client_sales/edit/$id");
    }

    exit;
}


function deleteClientSale($id)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Supprimer les lignes de vente
        ClientSaleItem::deleteBySaleId($id);

        // Supprimer la facture
        ClientSale::delete($id);

        $pdo->commit();
        $_SESSION['success'] = "Facture supprimée avec succès.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    }

    header("Location: ?route=client_sales");
    exit;
}
