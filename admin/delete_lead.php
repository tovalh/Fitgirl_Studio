<?php
// admin/delete_lead.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit;
}

require_once '../php/config.php';

$lead_id = $_POST['id'] ?? null;

if (!$lead_id) {
    echo json_encode(['success' => false, 'message' => 'ID de lead no proporcionado.']);
    exit;
}

try {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);

    $sql = "DELETE FROM leads WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$lead_id])) {
        echo json_encode(['success' => true, 'message' => 'Lead eliminado correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el lead.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}