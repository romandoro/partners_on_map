<?php

declare(strict_types=1);

session_start();

session_destroy();

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Logout effettuato con successo.']);

}