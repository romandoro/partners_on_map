<?php

declare(strict_types=1);
require_once __DIR__ . '/../../db_connect.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'You are logged in.']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You are not logged in.']);
}