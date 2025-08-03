<?php

// controllers/TransportController.php

require_once 'models/Transport.php';

function listTransports()
{
    $transports = Transport::all();
    include 'views/transports/index.php';
}

function createTransportForm()
{
    include 'views/transports/create.php';
}

function storeTransport()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'name' => $_POST['name'] ?? '',
            'transport_type' => $_POST['transport_type'] ?? '',
            'description' => $_POST['description'] ?? '',
            'contact_info' => $_POST['contact_info'] ?? '',
        ];

        if (!empty($data['name']) && !empty($data['transport_type'])) {
            Transport::create($data);
            $_SESSION['success'] = "Transporteur ajouté avec succès.";
            header('Location: ?route=transports');
            exit;
        } else {
            $_SESSION['error'] = "Champs obligatoires manquants.";
        }
    }

    include 'views/transports/create.php';
}


function editTransportForm($id)
{
    $transport = Transport::find($id);
    include 'views/transports/edit.php';
}

function updateTransport($id)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'name' => $_POST['name'] ?? '',
            'transport_type' => $_POST['transport_type'] ?? '',
            'contact_info' => $_POST['contact_info'] ?? '',
            'description' => $_POST['description'] ?? '',
        ];

        if (!empty($data['name']) && !empty($data['transport_type'])) {
            Transport::update($id, $data);
            $_SESSION['success'] = "Transporteur mis à jour avec succès.";
            header('Location: ?route=transports');
            exit;
        } else {
            $_SESSION['error'] = "Champs obligatoires manquants.";
        }
    }

    $transport = Transport::find($id);
    include 'views/transports/edit.php';
}

function deleteTransport($id)
{
    Transport::delete($id);
    header("Location: ?route=transports");
}
