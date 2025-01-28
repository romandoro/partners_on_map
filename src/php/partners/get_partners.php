<?php
// Imposta l'intestazione per restituire una risposta JSON
header('Content-Type: application/json');

// Include il file di connessione al database
require_once __DIR__ . '/../../db_connect.php';


try {
    // Query SQL per selezionare i partner
    $conn = getConnection();
    $sql = "
        SELECT 
            p.id, 
            p.name, 
            p.street, 
            p.zip_code, 
            p.city, 
            p.country, 
            p.language, 
            p.partita_iva, 
            p.email, 
            p.web_site, 
            p.phone,
            GROUP_CONCAT(pr.prov_name SEPARATOR ',') AS provinces
        FROM partners p
        LEFT JOIN partner_provinces pp ON p.id = pp.partner_id
        LEFT JOIN provinces pr ON pp.province_id = pr.id
        GROUP BY p.id
    ";

    // Prepara la query
    $stmt = $conn->prepare($sql);

    // Esegui la query
    $stmt->execute();

    // Ottieni i risultati
    $partners = $stmt->fetchAll();

    // Itera sui risultati per formattare le province
    foreach ($partners as &$partner) {
        // Se `provinces` è NULL, inizializza con un array vuoto
        $partner['provinces'] = $partner['provinces'] ? explode(',', $partner['provinces']) : [];
    }


    // Restituisci i dati dei partner in formato JSON
    echo json_encode($partners);


} catch (PDOException $e) {
    // Gestisci l'errore della query
    http_response_code(500);
    echo json_encode(["error" => "Errore durante l'esecuzione della query: " . $e->getMessage()]);
}