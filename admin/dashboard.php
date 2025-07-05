<?php
// admin/dashboard.php (Layout con Tabla Arriba y Gráfico Abajo)

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html');
    exit;
}

require_once '../php/config.php';

// (El código PHP para obtener los datos de la BD es el mismo)
$total_leads = 0; $leads_hoy = 0; $clase_mas_popular = "N/A"; $leads = []; $error_message = null;
try {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $total_leads = $pdo->query("SELECT count(id) FROM leads")->fetchColumn();
    $leads_hoy = $pdo->query("SELECT count(id) FROM leads WHERE fecha_registro >= CURRENT_DATE")->fetchColumn();
    $stmt_clases = $pdo->query("SELECT clase_interes, COUNT(clase_interes) as count FROM leads GROUP BY clase_interes ORDER BY count DESC LIMIT 1");
    if ($clase_popular_data = $stmt_clases->fetch(PDO::FETCH_ASSOC)) {
        $clase_mas_popular = ucfirst($clase_popular_data['clase_interes']) . " <span class='text-muted fw-normal'>({$clase_popular_data['count']})</span>";
    }
    $sql = "SELECT id, nombre, email, telefono, clase_interes, fecha_registro, status FROM leads ORDER BY fecha_registro DESC";
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
    <title>Dashboard - FitGirl Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #FF1B6B;
            --primary-light: rgba(255, 27, 107, 0.1);
            --sidebar-bg: #111827;
            --sidebar-link: #9ca3af;
            --sidebar-link-hover: #ffffff;
            --sidebar-link-active: #ffffff;
            --sidebar-link-active-bg: var(--primary-color);
            --background-color: #f9fafb;
            --card-bg: #ffffff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            overflow-x: hidden;
        }
        .wrapper { display: flex; }
        #sidebar {
            width: 250px;
            background: var(--sidebar-bg);
            min-height: 100vh;
            position: fixed;
            color: #fff;
            z-index: 1000;
        }
        #sidebar .sidebar-header {
            padding: 25px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        #sidebar .sidebar-header h3 { color: #fff; font-weight: 800; font-size: 1.5rem; }
        #sidebar .sidebar-header h3 .fa-dumbbell { color: var(--primary-color); }
        #sidebar .components { padding: 0; margin-top: 1rem; }
        #sidebar .components a {
            padding: 1rem 1.5rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            color: var(--sidebar-link);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
            border-left: 3px solid transparent;
        }
        #sidebar .components a .icon {
            width: 20px;
            margin-right: 1rem;
            text-align: center;
        }
        #sidebar .components a:hover {
            color: var(--sidebar-link-hover);
            background: rgba(255, 255, 255, 0.05);
            border-left: 3px solid var(--primary-color);
        }
        #sidebar .components li.active > a {
            color: var(--sidebar-link-active);
            background: var(--sidebar-link-active-bg);
            border-left: 3px solid var(--primary-color);
        }
        #content {
            width: calc(100% - 250px);
            margin-left: 250px;
            padding: 2.5rem;
        }
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .kpi-card {
            background-color: var(--card-bg);
            border-radius: 0.75rem;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .kpi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .kpi-card .icon { font-size: 1.5rem; padding: 1rem; border-radius: 0.5rem; color: var(--primary-color); background-color: var(--primary-light); }
        .kpi-card .number { font-size: 2rem; font-weight: 700; color: var(--text-main); }
        .kpi-card .title { color: var(--text-muted); font-size: 0.9rem; font-weight: 500;}
        .data-container {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 0.75rem;
            border: 1px solid var(--border-color);
        }
        .chart-container {
            position: relative;
            height: 350px;
            width: 100%;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-dumbbell"></i> FitGirl</h3>
        </div>
        <ul class="list-unstyled components">
            <li class="active">
                <a href="#"><i class="fas fa-tachometer-alt icon"></i> Dashboard</a>
            </li>
            <li>
                <a href="#"><i class="fas fa-users icon"></i> Clientes</a>
            </li>
            <li>
                <a href="#"><i class="fas fa-calendar-alt icon"></i> Calendario</a>
            </li>
            <li>
                <a href="#"><i class="fas fa-cogs icon"></i> Ajustes</a>
            </li>
        </ul>
        <div class="px-4" style="position: absolute; bottom: 20px; width: 100%;">
            <a href="logout.php" class="btn btn-dark w-100 d-flex align-items-center justify-content-center"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
        </div>
    </nav>

    <div id="content">
        <div class="content-header">
            <div>
                <h2 class="mb-0 fw-bold">Dashboard</h2>
                <p class="text-muted">Bienvenida de nuevo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-4 col-md-6">
                <div class="kpi-card"><div class="d-flex justify-content-between align-items-start"><div><p class="title mb-1">Total de Leads</p><h3 class="number mb-0"><?php echo $total_leads; ?></h3></div><div class="icon"><i class="fas fa-users"></i></div></div></div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="kpi-card"><div class="d-flex justify-content-between align-items-start"><div><p class="title mb-1">Leads Recibidos Hoy</p><h3 class="number mb-0"><?php echo $leads_hoy; ?></h3></div><div class="icon"><i class="fas fa-calendar-day"></i></div></div></div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="kpi-card"><div class="d-flex justify-content-between align-items-start"><div><p class="title mb-1">Clase más Popular</p><h3 class="number mb-0"><?php echo $clase_mas_popular; ?></h3></div><div class="icon"><i class="fas fa-star"></i></div></div></div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12">
                <div class="data-container">
                    <h5 class="fw-bold mb-3">Listado de Leads</h5>
                    <div id="action-feedback" class="alert d-none"></div>
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table id="myDataTable" class="table table-hover dt-responsive nowrap" style="width:100%">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Clase</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($leads as $lead): ?>
                                <tr id="lead-<?php echo $lead['id']; ?>">
                                    <td><?php echo htmlspecialchars($lead['id']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($lead['fecha_registro'])); ?></td>
                                    <td><?php echo htmlspecialchars($lead['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($lead['email']); ?></td>
                                    <td><?php echo htmlspecialchars($lead['telefono']); ?></td>
                                    <td><span class="badge bg-primary-subtle text-primary-emphasis rounded-pill"><?php echo htmlspecialchars(ucfirst($lead['clase_interes'])); ?></span></td>
                                    <td><span class="badge <?php echo ($lead['status'] === 'Contactado') ? 'bg-success' : 'bg-warning text-dark'; ?>"><?php echo htmlspecialchars($lead['status']); ?></span></td>
                                    <td>
                                        <button class="btn btn-success btn-sm action-btn-contact" data-id="<?php echo $lead['id']; ?>" title="Marcar como Contactado" <?php echo ($lead['status'] === 'Contactado') ? 'disabled' : ''; ?>><i class="fas fa-check"></i></button>
                                        <button class="btn btn-danger btn-sm action-btn-delete" data-id="<?php echo $lead['id']; ?>" title="Eliminar Lead"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="data-container">
                    <h5 class="fw-bold mb-3">Actividad Semanal</h5>
                    <div class="chart-container">
                        <canvas id="leadsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(document).ready(function() {
        // Inicializar DataTables con la opción responsiva
        const dataTable = $('#myDataTable').DataTable({
            responsive: true,
            language: {
                "decimal": "", "emptyTable": "No hay información", "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas", "infoEmpty": "Mostrando 0 a 0 de 0 Entradas", "infoFiltered": "(Filtrado de _MAX_ total entradas)", "infoPostFix": "", "thousands": ",", "lengthMenu": "Mostrar _MENU_ Entradas", "loadingRecords": "Cargando...", "processing": "Procesando...", "search": "Buscar:", "zeroRecords": "Sin resultados encontrados",
                "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" }
            }
        });

        // Función para mostrar feedback al usuario
        function showFeedback(message, isSuccess) {
            const feedbackDiv = $('#action-feedback');
            feedbackDiv.text(message).removeClass('d-none alert-success alert-danger').addClass(isSuccess ? 'alert-success' : 'alert-danger');
            setTimeout(() => { feedbackDiv.addClass('d-none'); }, 3000);
        }

        // Manejador de eventos para el botón "Contactado"
        $('#myDataTable').on('click', '.action-btn-contact', function() {
            const leadId = $(this).data('id');
            const button = $(this);
            $.ajax({
                url: 'update_lead_status.php', type: 'POST', data: { id: leadId }, dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showFeedback(response.message, true);
                        const rowNode = button.closest('tr');
                        const newStatusBadge = '<span class="badge bg-success">Contactado</span>';
                        dataTable.cell($(rowNode).find('td:eq(5)')).data(newStatusBadge).draw();
                        button.prop('disabled', true);
                    } else { showFeedback(response.message, false); }
                },
                error: function() { showFeedback('Error de comunicación con el servidor.', false); }
            });
        });

        // Manejador de eventos para el botón "Eliminar"
        $('#myDataTable').on('click', '.action-btn-delete', function() {
            const leadId = $(this).data('id');
            const row = $(this).closest('tr');
            if (confirm('¿Estás seguro de que quieres eliminar este lead? Esta acción no se puede deshacer.')) {
                $.ajax({
                    url: 'delete_lead.php', type: 'POST', data: { id: leadId }, dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showFeedback(response.message, true);
                            dataTable.row(row).remove().draw();
                        } else { showFeedback(response.message, false); }
                    },
                    error: function() { showFeedback('Error de comunicación con el servidor.', false); }
                });
            }
        });

        // Lógica del Gráfico
        const ctx = document.getElementById('leadsChart').getContext('2d');
        $.ajax({
            url: 'api/chart_data.php', type: 'GET', dataType: 'json',
            success: function(data) {
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(255, 27, 107, 0.5)');
                gradient.addColorStop(1, 'rgba(255, 27, 107, 0)');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Nuevos Leads', data: data.values, fill: true, backgroundColor: gradient, borderColor: 'rgba(255, 27, 107, 1)',
                            tension: 0.4, pointBackgroundColor: '#fff', pointBorderColor: 'rgba(255, 27, 107, 1)', pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                        plugins: { legend: { display: false } }
                    }
                });
            }
        });
    });
</script>
</body>
</html>