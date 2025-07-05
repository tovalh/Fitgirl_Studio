<?php
// admin/update_lead_status.php
session_start();
header('Content-Type: application/json');

// Seguridad: solo usuarios logueados pueden hacer esto
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit;
}

require_once '../php/config.php';

$lead_id = $_POST['id'] ?? null;
$new_status = 'Contactado';

if (!$lead_id) {
    echo json_encode(['success' => false, 'message' => 'ID de lead no proporcionado.']);
    exit;
}

try {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);

    $sql = "UPDATE leads SET status = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$new_status, $lead_id])) {
        echo json_encode(['success' => true, 'message' => 'Lead actualizado correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el lead.']);
    }
} catch (PDOException $e) {
    // En producción, loguear el error, no mostrarlo
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}