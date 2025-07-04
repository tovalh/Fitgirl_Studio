<?php
// admin/dashboard.php

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html');
    exit;
}

require_once '../php/config.php';

// --- NUEVO: CÁLCULO DE KPIS ---
$total_leads = 0;
$leads_hoy = 0;
$clases_count = [];
$leads = [];

try {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Contar total de leads
    $total_leads = $pdo->query("SELECT count(id) FROM leads")->fetchColumn();

    // Contar leads de hoy (PostgreSQL)
    $leads_hoy = $pdo->query("SELECT count(id) FROM leads WHERE fecha_registro >= CURRENT_DATE")->fetchColumn();

    // Contar la clase más popular
    $stmt_clases = $pdo->query("SELECT clase_interes, COUNT(clase_interes) as count FROM leads GROUP BY clase_interes ORDER BY count DESC LIMIT 1");
    $clase_popular_data = $stmt_clases->fetch(PDO::FETCH_ASSOC);
    $clase_mas_popular = $clase_popular_data ? ucfirst($clase_popular_data['clase_interes']) . " ({$clase_popular_data['count']})" : "N/A";

    // Obtener todos los leads para la tabla
    $sql = "SELECT id, nombre, email, telefono, clase_interes, fecha_registro FROM leads ORDER BY fecha_registro DESC";
    $leads = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Error de Base de Datos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Profesional - FitGirl Studio</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #FF1B6B;
            --secondary-color: #1A1A1A;
            --light-gray: #f1f5f9; /* Un gris más suave */
            --dark-gray: #343a40;
            --bs-success-rgb: 75, 192, 192; /* Para un verde más moderno */
        }
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--light-gray);
        }
        .navbar {
            background-color: var(--secondary-color);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: 800;
            color: white !important;
        }
        .welcome-text { color: #ccc !important; }
        .btn-logout {
            background: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            transition: all 0.3s ease;
        }
        .btn-logout:hover {
            background-color: var(--primary-color);
            color: white;
        }
        .kpi-card {
            background-color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        .kpi-card .icon {
            font-size: 2rem;
            padding: 1rem;
            border-radius: 50%;
            color: white;
            background-color: var(--primary-color);
        }
        .kpi-card .number {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--dark-gray);
        }
        .kpi-card .title {
            color: #6c757d;
            font-weight: 600;
        }
        #leadsTable {
            border-radius: 1rem;
            padding: 1rem;
            background-color: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .dataTables_wrapper .row {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><i class="fas fa-chart-line"></i> FitGirl Admin</a>
        <span class="navbar-text welcome-text ms-4">
                Bienvenida, <?php echo htmlspecialchars($_SESSION['username']); ?>!
            </span>
        <div class="ms-auto">
            <a href="logout.php" class="btn btn-logout btn-sm">Cerrar Sesión <i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</nav>

<div class="container-fluid mt-4 px-4">
    <h1 class="mb-4" style="font-weight: 800; color: var(--dark-gray);">Dashboard General</h1>

    <div class="row g-4 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="kpi-card d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-users"></i></div>
                <div>
                    <div class="number"><?php echo $total_leads; ?></div>
                    <div class="title">Total de Leads</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="kpi-card d-flex align-items-center">
                <div class="icon me-3 bg-success"><i class="fas fa-calendar-day"></i></div>
                <div>
                    <div class="number"><?php echo $leads_hoy; ?></div>
                    <div class="title">Leads de Hoy</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="kpi-card d-flex align-items-center">
                <div class="icon me-3 bg-info"><i class="fas fa-star"></i></div>
                <div>
                    <div class="number" style="font-size: 1.8rem;"><?php echo $clase_mas_popular; ?></div>
                    <div class="title">Clase más Popular</div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5" id="leadsTable">
        <h2 class="mb-4" style="font-weight: 700;">Detalle de Leads</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <table id="myDataTable" class="table table-hover" style="width:100%">
            <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Clase</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($leads as $lead): ?>
                <tr>
                    <td><?php echo htmlspecialchars($lead['id']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($lead['fecha_registro'])); ?></td>
                    <td><?php echo htmlspecialchars($lead['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($lead['email']); ?></td>
                    <td><?php echo htmlspecialchars($lead['telefono']); ?></td>
                    <td><span class="badge bg-primary-subtle text-primary-emphasis rounded-pill"><?php echo htmlspecialchars(ucfirst($lead['clase_interes'])); ?></span></td>
                    <td>
                        <button class="btn btn-success btn-sm" title="Marcar como Contactado"><i class="fas fa-check"></i></button>
                        <button class="btn btn-danger btn-sm" title="Eliminar Lead"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#myDataTable').DataTable({
            // Traducción al español
            language: {
                "decimal": "",
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
                "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Entradas",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            }
        });
    });
</script>
</body>
</html>