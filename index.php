<?php
declare(strict_types=1);

// Avvia la sessione PHP
use JetBrains\PhpStorm\NoReturn;

session_start();

// Ottieni il percorso della richiesta senza la parte iniziale del percorso dello script
$request = $_SERVER['REQUEST_URI'];
$basePath = dirname($_SERVER['SCRIPT_NAME']);
$request = str_replace($basePath, '', $request);

// Rimuovi gli slash iniziali e finali
$request = trim($request, '/');

/**
 * Funzione per verificare se l'utente è autenticato (ha una sessione valida).
 * @return bool
 */
function isAuthenticated(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Funzione per reindirizzare l'utente alla pagina di login.
 * Termina l'esecuzione dello script dopo il redirect.
 */
#[NoReturn]
function redirectToLogin(): void
{
    header('Location: /login');
    exit; // È importante terminare lo script dopo il reindirizzamento.
}


/**
 * Funzione per gestire il rendering di una pagina.
 * @param string $filePath
 * @return void
 */
function renderPage(string $filePath): void {
    if (file_exists($filePath) && is_file($filePath)) {
        include $filePath;
    } else {
        http_response_code(404);
        echo "404 Not Found";
    }
}

// Gestione delle rotte
switch ($request) {
    case '':
        // Pagina principale
        if (isAuthenticated()) {
            renderPage('public/index.html');
        } else {
            redirectToLogin();
        }
        break;

    case 'manage-partners':
        // Pagina di gestione dei partner
        if (isAuthenticated()) {
            renderPage('public/manage_partners.html');
        } else {
            redirectToLogin();
        }
        break;

    case 'login':
        // Pagina di login (accessibile senza login)
        renderPage('public/login.html');
        break;

    case 'php/login':
        // Elaborazione del login
        include 'src/php/auth/login.php';
        break;

    case 'logout':
        // Elaborazione del logout
        include 'src/php/auth/logout.php';
        header('Location: /login'); // reindirizza alla pagina di login
        exit;
        break;

    case 'php/check_login':
        // Verifica dell'autenticazione
        include 'src/php/auth/check_login.php';
        break;

    default:
        // Gestione dei file statici (css, js, immagini)
        $filePath = __DIR__ . '/public/' . $request;
        if (file_exists($filePath) && is_file($filePath)) {
            // Determina il Content-Type del file basandosi sull'estensione
            $mime_types = [
                'css'  => 'text/css',
                'js'  => 'application/javascript',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2'
            ];

            $ext = pathinfo($filePath, PATHINFO_EXTENSION);

            if(isset($mime_types[$ext])){
                header('Content-Type: ' . $mime_types[$ext]);
            }

            readfile($filePath); // Invia il file al client
        } else {
            // Gestione 404
            http_response_code(404);
            echo "404 Not Found";
        }
        break;
}