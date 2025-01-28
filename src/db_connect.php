<?php
const SERVERNAME = "127.0.0.1:3309";
const USERNAME = "partner";
const PASSWORD = "password";
const DBNAME = "partners";

function getConnection()
{
  try {
    $conn = new PDO("mysql:host=" . SERVERNAME . ";dbname=" . DBNAME, USERNAME, PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $conn;
  } catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Connessione al database fallita: " . $e->getMessage()]);
    exit;
  }
}

