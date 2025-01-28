<?php
// Imposta l'intestazione per restituire una risposta JSON
header('Content-Type: application/json');

// Include il file di connessione al database
require_once __DIR__ . '/../../db_connect.php';

try {
    // Ottieni i dati dal form e li valida
    $conn = getConnection();
    $partner_id = $_POST['partner_id'] ?? null;
    $name = trim($_POST['name']);
    $street = trim($_POST['street']);
    $zip_code = trim($_POST['zip_code']);
    $city = trim($_POST['city']);
    $country = trim($_POST['country']);
    $language = trim($_POST['language']);
    $partita_iva = trim($_POST['partita_iva']);
    $email = trim($_POST['email']);
    $web_site = trim($_POST['web_site']);
    $phone = trim($_POST['phone']);
    $provinces = json_decode($_POST['provinces'], true); // Decodifica il JSON come array associativo


    // Verifica se $provinces è un array
    if (!is_array($provinces)) {
        throw new Exception("Dati delle province non validi.");
    }
    // Verifica se è stata selezionata almeno una provincia
    if (empty($provinces)) {
        throw new Exception("Seleziona almeno una provincia.");
    }

    if (!$partner_id) {
        throw new Exception("ID partner non valido");
    }

    // Query SQL per aggiornare un partner esistente
    $sql = "UPDATE partners SET name = :name, street = :street, zip_code = :zip_code, city = :city, country = :country, language = :language, partita_iva = :partita_iva, email = :email, web_site = :web_site, phone = :phone WHERE id = :partner_id";

    $stmt = $conn->prepare($sql);
    // Collega i parametri alla query
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':street', $street);
    $stmt->bindParam(':zip_code', $zip_code);
    $stmt->bindParam(':city', $city);
    $stmt->bindParam(':country', $country);
    $stmt->bindParam(':language', $language);
    $stmt->bindParam(':partita_iva', $partita_iva);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':web_site', $web_site);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':partner_id', $partner_id);
    // Esegui la query
    $stmt->execute();

    // Elimina le province associate al partner dalla tabella `partner_provinces`
    $sql = "DELETE FROM partner_provinces WHERE partner_id = :partner_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':partner_id', $partner_id);
    $stmt->execute();


    // Inserisci le province nella tabella `partner_provinces`
    foreach ($provinces as $province_id) {
        $sql = "INSERT INTO partner_provinces (partner_id, province_id) VALUES (:partner_id, :province_id)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':partner_id', $partner_id);
        $stmt->bindParam(':province_id', $province_id);
        $stmt->execute();
    }

    // Restituisci un messaggio di successo
    echo json_encode(["success" => true, "message" => "Partner aggiornato con successo"]);

}   catch (PDOException $e) {
    // Gestisci l'errore della query
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Errore durante l'aggiornamento del partner: " . $e->getMessage()]);
}   catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}