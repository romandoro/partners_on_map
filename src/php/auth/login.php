<?php
declare(strict_types=1);

use JetBrains\PhpStorm\NoReturn;

session_start();
require_once __DIR__ . '/../../db_connect.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$redirect_uri = $_POST['redirect_uri'] ?? '/';

// Validazione input
if (empty($username) || empty($password)) {
    sendJsonResponse(false, 'Username e password sono obbligatori.');
}

try {
    $conn = getConnection();  // Ottieni sempre una nuova connessione PDO
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Login di successo, imposta la sessione e reindirizza
        $_SESSION['user_id'] = $user['id'];

        // Valida redirect_uri
        if (!isValidRedirect($redirect_uri)) {
            $redirect_uri = '/';
        }

        sendJsonResponse(true, 'Login avvenuto con successo.', $redirect_uri);

    } else {
        // Credenziali non valide
        sendJsonResponse(false, 'Credenziali non valide.');
    }
} catch (PDOException $e) {
    // Log dell'errore per il debug
    error_log("Errore nel login: " . $e->getMessage());
    sendJsonResponse(false, 'Errore durante l\'accesso.');
} finally {
    // Chiudi la connessione PDO nella clausola finally per garantire che venga chiusa anche in caso di eccezione
    $conn = null;
}
#[NoReturn]
function sendJsonResponse(bool $success, string $message, string $redirect_uri = null): void
{
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'redirect_uri' => $redirect_uri
    ]);
    exit;
}

// Funzione per validare la redirect_uri
function isValidRedirect(string $uri): bool
{
    // Consenti solo percorsi interni che iniziano con "/"
    return preg_match('/^\//', $uri) === 1;
}