<?php
// Imposta l'intestazione per restituire una risposta JSON
header('Content-Type: application/json');

// Include il file di connessione al database
require_once __DIR__ . '/../../db_connect.php';



try {
    // Query SQL per selezionare le province
    $conn = getConnection();
    $sql = "SELECT id, prov_name FROM provinces ORDER BY prov_name";

    // Prepara la query
    $stmt = $conn->prepare($sql);

    // Esegui la query
    $stmt->execute();

    // Ottieni i risultati
    $provinces = $stmt->fetchAll();

    // Restituisci i dati delle province in formato JSON
    echo json_encode($provinces);


} catch (PDOException $e) {
    // Gestisci l'errore della query
    http_response_code(500);
    echo json_encode(["error" => "Errore durante l'esecuzione della query: " . $e->getMessage()]);
}