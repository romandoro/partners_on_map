<?php
// Imposta l'intestazione per restituire una risposta JSON
header('Content-Type: application/json');

// Include il file di connessione al database
require_once __DIR__ . '/../../db_connect.php';


try {
    // Decodifica i dati JSON dalla richiesta
    $data = json_decode(file_get_contents('php://input'), true);
    // Ottieni l'ID del partner dal JSON
    $partner_id = $data['partner_id'];
    // Verifica se $partner_id è vuoto
    if (empty($partner_id)) {
        throw new Exception("ID partner non valido");
    }

    $conn = getConnection();

    // Elimina le righe dalla tabella `partner_provinces` dove partner_id corrisponde all'id del partner
    $sql = "DELETE FROM partner_provinces WHERE partner_id = :partner_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':partner_id', $partner_id);
    $stmt->execute();

    // Elimina il partner dalla tabella `partners`
    $sql = "DELETE FROM partners WHERE id = :partner_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':partner_id', $partner_id);
    $stmt->execute();


    // Restituisci una risposta JSON di successo
    echo json_encode(['success' => true, 'message' => 'Partner eliminato con successo']);

} catch (PDOException $e) {
    // Gestisci l'errore della query
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Errore durante l'eliminazione del partner: " . $e->getMessage()]);
}  catch (Exception $e) {
    // Gestisci l'errore della query
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}