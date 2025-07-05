<?php
// admin/api/chart_data.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}

require_once '../../php/config.php'; // Salimos dos niveles para llegar a la carpeta php

$data = [
    'labels' => [],
    'values' => []
];

try {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);

    // Consulta para obtener el número de leads de los últimos 7 días
    $sql = "SELECT DATE(fecha_registro) as dia, COUNT(id) as total
            FROM leads
            WHERE fecha_registro >= CURRENT_DATE - INTERVAL '6 days'
            GROUP BY DATE(fecha_registro)
            ORDER BY dia ASC";

    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Creamos un array con los últimos 7 días para asegurarnos de que no falte ninguno
    $period = new DatePeriod(
        new DateTime('-6 days'),
        new DateInterval('P1D'),
        new DateTime('+1 day')
    );

    $days_data = [];
    foreach($results as $row){
        $days_data[$row['dia']] = $row['total'];
    }

    foreach ($period as $date) {
        $day_key = $date->format('Y-m-d');
        $data['labels'][] = $date->format('d/m');
        $data['values'][] = $days_data[$day_key] ?? 0;
    }

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    $data['error'] = 'Error de base de datos';
}

echo json_encode($data);