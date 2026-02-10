<?php

require_once 'models/Country.php';
require_once 'models/RealStock.php';
require_once 'models/Variant.php';
require_once 'models/ClientSale.php';
require_once 'models/ClientSaleItem.php';
require_once 'utils.php';
require_once 'models/Company.php';
require_once 'models/CompanyStock.php';
require_once 'models/Partner.php';
require_once 'models/PartnerAccount.php';

function storeClientSale()
{
    global $pdo;

    $saleDate = $_POST['sale_date'] ?? null;
    $countryId = $_POST['country_id'] ?? null;
    $companyId = $_POST['company_id'] ?? null;
    $partnerId = $_POST['partner_id'] ?? null;
    $accountId = $_POST['account_id'] ?? null;
    $amountReceived = $_POST['amount_received'] ?? '';
    $currency = trim($_POST['currency'] ?? 'USD');
    $receivedDate = $_POST['received_date'] ?? null;
    $paymentMethod = trim($_POST['payment_method'] ?? '');
    $variantIds = $_POST['variant_id'] ?? [];
    $quantities = $_POST['quantity_sold'] ?? [];

    if (!$saleDate || !$countryId || !$companyId || !$partnerId || !$accountId || $amountReceived === '' || empty($variantIds) || empty($quantities)) {
        $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis.";
        header("Location: ?route=client_sales/create&country_id=" . $countryId);
        exit;
    }

    if (!is_numeric($amountReceived)) {
        $_SESSION['error'] = "Le montant reçu est invalide.";
        header("Location: ?route=client_sales/create&country_id=" . $countryId);
        exit;
    }

    if (!$receivedDate) {
        $receivedDate = $saleDate;
    }

    // ✅ Upload du fichier justificatif
    $proofPath = null;
    if (!empty($_FILES['proof_file']['name'])) {
        validate_upload_or_throw(
            $_FILES['proof_file'],
            ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
            5 * 1024 * 1024
        );
        $uploadDir = 'uploads/sales_proofs/';
        ensure_upload_dir($uploadDir);
        $fileName = time() . '_' . bin2hex(random_bytes(4)) . '_' . sanitize_filename($_FILES['proof_file']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['proof_file']['tmp_name'], $targetPath)) {
            $proofPath = $targetPath;
        } else {
            throw new Exception("Échec de l'upload du justificatif.");
        }
    }

    try {
        $pdo->beginTransaction();

        // ✅ Insertion de la facture
        $saleId = ClientSale::create($saleDate, $countryId, $companyId, $partnerId, $accountId, $amountReceived, $currency, $receivedDate, $paymentMethod, $proofPath);

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
    $countryId = $_GET['country_id'] ?? null;
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;
    $companyId = $_GET['company_id'] ?? null;

    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;
    $total = ClientSale::countFiltered($countryId, $dateFrom, $dateTo, $companyId);
    $totalPages = (int)ceil($total / $perPage);

    $sales = ClientSale::filter($countryId, $dateFrom, $dateTo, $companyId, $perPage, $offset);
    $countries = Country::all();
    $companies = Company::all();
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

    // 🔁 Charger le pays sélectionné
    $selectedCountry = Country::getById($countryId);
    $companies = Company::byCountry($countryId);
    $partners = Partner::all();

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
    $variants = CompanyStock::getAvailableVariantsByCompany($sale['company_id']);
    $companies = Company::byCountry($sale['country_id']);
    $partners = Partner::all();
    $accountsForPartner = $sale['partner_id'] ? PartnerAccount::byPartner($sale['partner_id']) : [];

    // 📍 Charger la vue d’édition
    include 'views/client_sales/edit.php';
}

function fetchCompanySaleVariants($companyId)
{
    header('Content-Type: text/html; charset=utf-8');
    $variants = [];
    if ($companyId) {
        $variants = CompanyStock::getAvailableVariantsByCompany($companyId);
    }
    include 'views/client_sales/_variant_options.php';
    exit;
}

function updateClientSale($id)
{
    global $pdo;

    $saleDate = $_POST['sale_date'] ?? null;
    $countryId = $_POST['country_id'] ?? null;
    $companyId = $_POST['company_id'] ?? null;
    $partnerId = $_POST['partner_id'] ?? null;
    $accountId = $_POST['account_id'] ?? null;
    $amountReceived = $_POST['amount_received'] ?? '';
    $currency = trim($_POST['currency'] ?? 'USD');
    $receivedDate = $_POST['received_date'] ?? null;
    $paymentMethod = trim($_POST['payment_method'] ?? '');
    $variantIds = $_POST['variant_id'] ?? [];
    $quantities = $_POST['quantity_sold'] ?? [];

    if (!$saleDate || !$countryId || !$companyId || !$partnerId || !$accountId || $amountReceived === '' || empty($variantIds) || empty($quantities)) {
        $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis.";
        header("Location: ?route=client_sales/edit/$id");
        exit;
    }

    if (!is_numeric($amountReceived)) {
        $_SESSION['error'] = "Le montant reçu est invalide.";
        header("Location: ?route=client_sales/edit/$id");
        exit;
    }

    if (!$receivedDate) {
        $receivedDate = $saleDate;
    }

    // ✅ Gestion du fichier justificatif
    $proofPath = null;
    if (!empty($_FILES['proof_file']['name'])) {
        validate_upload_or_throw(
            $_FILES['proof_file'],
            ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
            5 * 1024 * 1024
        );
        $uploadDir = 'uploads/sales_proofs/';
        ensure_upload_dir($uploadDir);
        $fileName = time() . '_' . bin2hex(random_bytes(4)) . '_' . sanitize_filename($_FILES['proof_file']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['proof_file']['tmp_name'], $targetPath)) {
            $proofPath = $targetPath;
        } else {
            throw new Exception("Échec de l'upload du justificatif.");
        }
    }

    try {
        $pdo->beginTransaction();

        // 🔁 Mettre à jour la facture principale
        ClientSale::update($id, $saleDate, $countryId, $companyId, $partnerId, $accountId, $amountReceived, $currency, $receivedDate, $paymentMethod, $proofPath);

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
