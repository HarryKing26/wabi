<?php
// Incluir conexión a la base de datos
require_once 'includes/db2.php';

// Procesamiento de filtros
$filtros = [
    'fecha_desde' => isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '',
    'fecha_hasta' => isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '',
    'paquete' => isset($_GET['paquete']) ? $_GET['paquete'] : '',
    'destino' => isset($_GET['destino']) ? $_GET['destino'] : '',
    'estado' => isset($_GET['estado']) ? $_GET['estado'] : ''
];

// Construir consulta base con filtros
$sql = "SELECT * FROM pedidos WHERE 1=1";
$params = [];

if (!empty($filtros['fecha_desde'])) {
    $sql .= " AND fecha_registro >= ?";
    $params[] = $filtros['fecha_desde'];
}

if (!empty($filtros['fecha_hasta'])) {
    $sql .= " AND fecha_registro <= ?";
    $params[] = $filtros['fecha_hasta'] . ' 23:59:59';
}

if (!empty($filtros['paquete'])) {
    $sql .= " AND paquete = ?";
    $params[] = $filtros['paquete'];
}

if (!empty($filtros['destino'])) {
    $sql .= " AND destino = ?";
    $params[] = $filtros['destino'];
}

if (!empty($filtros['estado'])) {
    $sql .= " AND estado = ?";
    $params[] = $filtros['estado'];
}

$sql .= " ORDER BY fecha_registro DESC";

// Obtener datos filtrados
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consultas para estadísticas
$estadisticas = [];

// Total de pedidos
$stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos");
$estadisticas['total_pedidos'] = $stmt->fetchColumn();

// Pedidos por estado
$stmt = $pdo->query("SELECT estado, COUNT(*) as cantidad FROM pedidos GROUP BY estado");
$estadisticas['por_estado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pedidos por paquete
$stmt = $pdo->query("SELECT paquete, COUNT(*) as cantidad FROM pedidos GROUP BY paquete");
$estadisticas['por_paquete'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pedidos por destino
$stmt = $pdo->query("SELECT destino, COUNT(*) as cantidad FROM pedidos GROUP BY destino");
$estadisticas['por_destino'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Promedio de pasajeros
$stmt = $pdo->query("SELECT AVG(num_pasajeros) as promedio FROM pedidos");
$estadisticas['promedio_pasajeros'] = $stmt->fetchColumn();

// Distribución por edad
$stmt = $pdo->query("
    SELECT 
        CASE 
            WHEN edad < 18 THEN 'Menor de 18'
            WHEN edad BETWEEN 18 AND 25 THEN '18-25'
            WHEN edad BETWEEN 26 AND 35 THEN '26-35'
            WHEN edad BETWEEN 36 AND 50 THEN '36-50'
            WHEN edad > 50 THEN 'Mayor de 50'
        END as grupo_edad,
        COUNT(*) as cantidad
    FROM pedidos
    GROUP BY grupo_edad
    ORDER BY MIN(edad)
");
$estadisticas['por_edad'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesamiento de acciones CRUD
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        try {
            switch ($_POST['accion']) {
                case 'actualizar_estado':
                    $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
                    $stmt->execute([$_POST['estado'], $_POST['id']]);
                    $mensaje = '<div class="exito-mensaje">Estado actualizado correctamente.</div>';
                    header("Location: reportes.php?" . http_build_query($filtros));
                    exit;
                    break;
                
                case 'eliminar':
                    $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $mensaje = '<div class="exito-mensaje">Registro eliminado correctamente.</div>';
                    header("Location: reportes.php?" . http_build_query($filtros));
                    exit;
                    break;
            }
        } catch (PDOException $e) {
            $mensaje = '<div class="error-mensaje">Error al procesar la acción: ' . $e->getMessage() . '</div>';
        }
    }
}

// Obtener opciones únicas para filtros
$stmt = $pdo->query("SELECT DISTINCT paquete FROM pedidos ORDER BY paquete");
$paquetes = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->query("SELECT DISTINCT destino FROM pedidos ORDER BY destino");
$destinos = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->query("SELECT DISTINCT estado FROM pedidos ORDER BY estado");
$estados = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD - Wabi-Sabi Japan Tours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <style>
        @font-face {
            font-family: 'Toragon';
            src: url('font/toragon.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background-image: url('https://images.unsplash.com/photo-1492571350019-22de08371fd3');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            position: relative;
            color: #333;
        }

        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }

        header {
            background: rgba(218, 41, 28, 0.9);
            color: white;
            padding: 15px 0;
            text-align: center;
            backdrop-filter: blur(5px);
            position: sticky;
            top: 0;
            z-index: 999;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            min-height: 90px;
        }

        header h1 {
            margin: 0;
            font-size: 2.5em;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
            letter-spacing: 1px;
            font-weight: bold;
            font-family: 'Toragon', Arial, sans-serif;
        }

        .espacio-header {
            height: 120px;
        }

        nav {
            margin-top: 0;
            padding: 10px 0;
            background: rgba(255, 255, 255, 0.15);
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            border-radius: 0 0 16px 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        nav a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            font-size: 1em;
            padding: 10px 18px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: rgba(218, 41, 28, 0.08);
        }

        nav a:hover,
        nav a:focus {
            background: rgba(255, 255, 255, 0.35);
            color: #da291c;
            transform: scale(1.08);
            outline: none;
        }

        h1, .titulo-pagina {
            font-family: 'Toragon', Arial, sans-serif;
            
        }


        .titulo-pagina {
            margin-top: 0;
            margin-bottom: 30px;
            text-align: center;
            color: #fff;
            font-size: 2.2em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .contenedor {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .caja {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .caja:hover {
            transform: translateY(-8px);
        }

        .info-section {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            padding: 30px;
            margin: 30px auto;
            max-width: 1200px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .info-section h2 {
            color: #da291c;
            border-bottom: 2px solid #da291c;
            padding-bottom: 10px;
        }

        .btn {
            display: inline-block;
            background: #da291c;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1em;
        }

        .btn:hover {
            background: #b82218;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-success {
            background: #28a745;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8em;
        }

        .destacado {
            color: #ffd700;
            font-weight: bold;
        }

        footer {
            background: rgba(218, 41, 28, 0.9);
            color: white;
            padding: 40px 20px;
            margin-top: 60px;
            text-align: center;
            backdrop-filter: blur(5px);
        }

        /* Estilos específicos de reportes */
        .reportes-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 30px;
            margin: 30px auto;
            max-width: 1200px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .filtros-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .form-group input[type="date"],
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s, box-shadow 0.3s;
        }

        .form-group select:focus,
        .form-group input[type="date"]:focus {
            border-color: #da291c;
            box-shadow: 0 0 0 3px rgba(218, 41, 28, 0.2);
            outline: none;
        }

        .grid-filtros {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .btn-filtrar {
            margin-top: 10px;
            width: 100%;
        }

        .btn-limpiar {
            background: #6c757d;
            margin-top: 10px;
            width: 100%;
        }

        .btn-limpiar:hover {
            background: #5a6268;
        }

        /* Estilos para gráficos */
        .graficos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .grafico-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(218, 41, 28, 0.1);
        }
        
        .grafico-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }
        
        .grafico-container h3 {
            color: #da291c;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.4em;
            text-align: center;
            font-family: 'Toragon', Arial, sans-serif;
            border-bottom: 2px dashed #eee;
            padding-bottom: 10px;
        }
        
        .grafico-container canvas {
            width: 100% !important;
            height: 350px !important;
        }

        /* Tarjetas de estadísticas */
        .estadisticas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .tarjeta-estadistica {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
            border: 1px solid rgba(218, 41, 28, 0.1);
            text-align: center;
        }
        
        .tarjeta-estadistica:hover {
            transform: translateY(-5px);
        }
        
        .tarjeta-estadistica h3 {
            color: #555;
            font-size: 1.1em;
            margin-top: 0;
            margin-bottom: 15px;
        }
        
        .tarjeta-estadistica .valor {
            font-size: 2.5em;
            font-weight: bold;
            color: #da291c;
            font-family: 'Toragon', Arial, sans-serif;
            margin: 10px 0;
        }
        
        .tarjeta-estadistica .descripcion {
            color: #777;
            font-size: 0.9em;
        }

        /* Tabla de datos */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #da291c;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .acciones-cell {
            white-space: nowrap;
        }

        /* Badges para estados */
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            min-width: 80px;
            text-align: center;
        }

        .badge-pendiente {
            background-color: #FFC107;
            color: #212529;
        }

        .badge-confirmado {
            background-color: #28A745;
            color: white;
        }

        .badge-cancelado {
            background-color: #DC3545;
            color: white;
        }

        .badge-completado {
            background-color: #17A2B8;
            color: white;
        }

        /* Mensajes */
        .exito-mensaje {
            background: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }

        .error-mensaje {
            background: #f44336;
            color: white;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Estilos para Select2 */
        .select2-container--default .select2-selection--single {
            height: 42px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 42px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }

        /* Botones de subir/bajar */
        .boton-flecha {
            position: fixed;
            right: 24px;
            width: 48px;
            height: 48px;
            border: none;
            border-radius: 50%;
            background: #da291c;
            color: #fff;
            font-size: 2em;
            box-shadow: 0 4px 16px rgba(218,41,28,0.15);
            cursor: pointer;
            z-index: 1001;
            transition: background 0.3s, transform 0.2s;
            opacity: 0.85;
        }
        #btn-arriba { bottom: 90px; }
        #btn-abajo { bottom: 30px; }
        .boton-flecha:hover { background: #b82218; transform: scale(1.08); opacity: 1; }

        /* Animaciones para tarjetas y gráficos */
        .tarjeta-estadistica, .grafico-container, .filtros-container, .reportes-container, table {
            animation: fadeInUp 0.7s;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        /* Animación para mensaje de éxito/error */
        .exito-mensaje, .error-mensaje {
            animation: bounceIn 0.7s;
        }
        @keyframes bounceIn {
            0% { transform: scale(0.7); opacity: 0; }
            60% { transform: scale(1.1); opacity: 1; }
            80% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }

        @media (max-width: 768px) {
            .grid-filtros,
            .estadisticas-grid,
            .graficos-grid {
                grid-template-columns: 1fr;
            }
            
            .grafico-container {
                padding: 15px;
            }
            
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
    <!-- Botón flecha arriba -->
    <button id="btn-arriba" class="boton-flecha" onclick="window.scrollTo({top:0,behavior:'smooth'})" title="Ir arriba">
        &#8679;
    </button>
    <!-- Botón flecha abajo -->
    <button id="btn-abajo" class="boton-flecha" onclick="window.scrollTo({top:document.body.scrollHeight,behavior:'smooth'})" title="Ir abajo">
        &#8681;
    </button>

    <header>
        <h1><span class="destacado">WABI-SABI</span> - TOURS A JAPÓN</h1>
        <nav>
            <a href="index.html">Inicio</a>
            <a href="index.html#paquetes">Paquetes</a>
            <a href="index.html#requisitos">Requisitos</a>
            <a href="index.html#contactanos">Contacto</a>
            <a href="formulario.php">Formulario</a>
            <a href="reportes.php">Reportes</a>
            <a href="pitch.html">Pitch</a>
        </nav>
    </header>
    <div class="espacio-header"></div>

    <div class="reportes-container">
        <h2 class="titulo-pagina">CRUD</h2>
        
        <?php if (!empty($mensaje)) echo $mensaje; ?>
        
        
        
        
        <!-- Tabla de datos -->
        <h3>Registros de Pedidos</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Paquete</th>
                    <th>Destino</th>
                    <th>Pasajeros</th>
                    <th>Fechas</th>
                    <th>Registro</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                <tr>
                    <td><?= htmlspecialchars($pedido['id']) ?></td>
                    <td><?= htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellidos']) ?></td>
                    <td><?= htmlspecialchars($pedido['email']) ?></td>
                    <td><?= htmlspecialchars($pedido['paquete']) ?></td>
                    <td><?= htmlspecialchars($pedido['destino']) ?></td>
                    <td><?= htmlspecialchars($pedido['num_pasajeros']) ?></td>
                    <td>
                        <?= date('d/m/Y', strtotime($pedido['fecha_inicio'])) ?> - 
                        <?= date('d/m/Y', strtotime($pedido['fecha_fin'])) ?>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($pedido['fecha_registro'])) ?></td>
                    <td>
                        <span class="badge badge-<?= strtolower($pedido['estado']) ?>">
                            <?= htmlspecialchars($pedido['estado']) ?>
                        </span>
                    </td>
                    <td class="acciones-cell">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="id" value="<?= $pedido['id'] ?>">
                            <input type="hidden" name="accion" value="actualizar_estado">
                            <select name="estado" onchange="this.form.submit()" style="padding: 5px; border-radius: 4px; border: 1px solid #ddd;">
                                <option value="pendiente" <?= ($pedido['estado'] == 'pendiente') ? 'selected' : '' ?>>Pendiente</option>
                                <option value="confirmado" <?= ($pedido['estado'] == 'confirmado') ? 'selected' : '' ?>>Confirmado</option>
                                <option value="cancelado" <?= ($pedido['estado'] == 'cancelado') ? 'selected' : '' ?>>Cancelado</option>
                                <option value="completado" <?= ($pedido['estado'] == 'completado') ? 'selected' : '' ?>>Completado</option>
                            </select>
                        </form>
                        
                        <form method="POST" style="display: inline; margin-left: 5px;">
                            <input type="hidden" name="id" value="<?= $pedido['id'] ?>">
                            <input type="hidden" name="accion" value="eliminar">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este registro?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="text-align: center; margin-top: 40px;">
            <a href="dashboard.php" class="btn btn-outline">Volver al panel de control</a>
        </div>
    </div>

    <footer id="contactanos">
        <h2>Contacta con Wabi-Sabi Japan Tours</h2>
        <p>© 2025 Wabi-Sabi Japan - Creada únicamente con fines académicos.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 para selects
            $('#paquete, #destino, #estado').select2({
                width: '100%'
            });

            // Configuración común para gráficos
            const configComun = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                size: 14,
                                family: 'Toragon',
                                weight: 'bold'
                            },
                            color: '#da291c',
                            padding: 24
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255,255,255,0.95)',
                        titleColor: '#da291c',
                        bodyColor: '#333',
                        borderColor: '#da291c',
                        borderWidth: 2,
                        titleFont: {
                            size: 16,
                            weight: 'bold',
                            family: 'Toragon'
                        },
                        bodyFont: {
                            size: 14,
                            family: 'Arial'
                        },
                        padding: 16,
                        cornerRadius: 12,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                label += context.parsed.y !== undefined ? context.parsed.y : context.parsed;
                                return label;
                            }
                        }
                    },
                    datalabels: {
                        display: true,
                        color: '#fff',
                        backgroundColor: '#da291c',
                        borderRadius: 8,
                        padding: 6,
                        font: {
                            weight: 'bold',
                            size: 13,
                            family: 'Toragon'
                        },
                        formatter: (value, context) => {
                            const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${value} (${percentage}%)`;
                        }
                    }
                },
                layout: {
                    padding: 20
                },
                animation: {
                    duration: 1800,
                    easing: 'easeOutElastic',
                    animateScale: true,
                    animateRotate: true
                }
            };

            // Función para crear gradientes bonitos
            function crearGradiente(ctx, color1, color2) {
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, color1);
                gradient.addColorStop(1, color2);
                return gradient;
            }

            // Gráfico de estados (Doughnut)
            const ctxEstados = document.getElementById('graficoEstados').getContext('2d');
            new Chart(ctxEstados, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode(array_column($estadisticas['por_estado'], 'estado')) ?>,
                    datasets: [{
                        data: <?= json_encode(array_column($estadisticas['por_estado'], 'cantidad')) ?>,
                        backgroundColor: [
                            crearGradiente(ctxEstados, '#FFD600', '#FF9800'), // Pendiente
                            crearGradiente(ctxEstados, '#4CAF50', '#43E97B'), // Confirmado
                            crearGradiente(ctxEstados, '#F44336', '#FF6F91'), // Cancelado
                            crearGradiente(ctxEstados, '#2196F3', '#21D4FD')  // Completado
                        ],
                        borderColor: '#fff',
                        borderWidth: 3,
                        hoverBorderWidth: 5,
                        hoverOffset: 18,
                        shadowOffsetX: 0,
                        shadowOffsetY: 0,
                        shadowBlur: 20,
                        shadowColor: 'rgba(218,41,28,0.25)'
                    }]
                },
                options: {
                    ...configComun,
                    cutout: '68%',
                    plugins: {
                        ...configComun.plugins,
                        title: {
                            display: true,
                            text: '🎯 Distribución por Estado',
                            color: '#da291c',
                            font: {
                                size: 20,
                                weight: 'bold',
                                family: 'Toragon'
                            },
                            padding: 18
                        }
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 1800,
                        easing: 'easeOutElastic'
                    },
                    hover: {
                        mode: 'nearest',
                        animationDuration: 600
                    }
                },
                plugins: [ChartDataLabels]
            });

            // Gráfico de paquetes (Barras verticales)
            const ctxPaquetes = document.getElementById('graficoPaquetes').getContext('2d');
            new Chart(ctxPaquetes, {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_column($estadisticas['por_paquete'], 'paquete')) ?>,
                    datasets: [{
                        label: 'Reservas',
                        data: <?= json_encode(array_column($estadisticas['por_paquete'], 'cantidad')) ?>,
                        backgroundColor: crearGradiente(ctxPaquetes, '#DA291C', '#FFB300'),
                        borderColor: '#fff',
                        borderWidth: 2,
                        borderRadius: 12,
                        hoverBackgroundColor: '#E8715D',
                        shadowOffsetX: 0,
                        shadowOffsetY: 0,
                        shadowBlur: 18,
                        shadowColor: 'rgba(218,41,28,0.18)'
                    }]
                },
                options: {
                    ...configComun,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(218,41,28,0.07)' },
                            ticks: { color: '#da291c', font: { family: 'Toragon', size: 13 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#333', font: { family: 'Toragon', size: 13 } }
                        }
                    },
                    plugins: {
                        ...configComun.plugins,
                        title: {
                            display: true,
                            text: '🌸 Paquetes Más Populares',
                            color: '#da291c',
                            font: { size: 20, weight: 'bold', family: 'Toragon' },
                            padding: 18
                        },
                        legend: { display: false },
                        datalabels: {
                            display: true,
                            color: '#fff',
                            backgroundColor: '#da291c',
                            borderRadius: 8,
                            padding: 6,
                            font: { weight: 'bold', size: 13, family: 'Toragon' },
                            anchor: 'end',
                            align: 'top',
                            formatter: (value) => value
                        }
                    },
                    animation: {
                        duration: 1600,
                        easing: 'easeOutBounce'
                    }
                },
                plugins: [ChartDataLabels]
            });

            // Gráfico de destinos (Doughnut)
            const ctxDestinos = document.getElementById('graficoDestinos').getContext('2d');
            new Chart(ctxDestinos, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode(array_column($estadisticas['por_destino'], 'destino')) ?>,
                    datasets: [{
                        data: <?= json_encode(array_column($estadisticas['por_destino'], 'cantidad')) ?>,
                        backgroundColor: [
                            crearGradiente(ctxDestinos, '#DA291C', '#E8715D'),
                            crearGradiente(ctxDestinos, '#FFC845', '#FFB300'),
                            crearGradiente(ctxDestinos, '#5D8A66', '#3B7D4A'),
                            crearGradiente(ctxDestinos, '#3A5A78', '#1C3D5A'),
                            crearGradiente(ctxDestinos, '#8B5D5D', '#6D4C4C'),
                            crearGradiente(ctxDestinos, '#4A7C59', '#2E5E3E'),
                            crearGradiente(ctxDestinos, '#D4A59A', '#C08A7D')
                        ],
                        borderColor: '#fff',
                        borderWidth: 3,
                        hoverBorderWidth: 5,
                        hoverOffset: 18,
                        shadowOffsetX: 0,
                        shadowOffsetY: 0,
                        shadowBlur: 20,
                        shadowColor: 'rgba(218,41,28,0.18)'
                    }]
                },
                options: {
                    ...configComun,
                    cutout: '68%',
                    plugins: {
                        ...configComun.plugins,
                        title: {
                            display: true,
                            text: '🗾 Preferencia de Destinos',
                            color: '#da291c',
                            font: { size: 20, weight: 'bold', family: 'Toragon' },
                            padding: 18
                        }
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 1800,
                        easing: 'easeOutElastic'
                    },
                    hover: {
                        mode: 'nearest',
                        animationDuration: 600
                    }
                },
                plugins: [ChartDataLabels]
            });

            // Gráfico de edades (Barras verticales)
            const ctxEdades = document.getElementById('graficoEdades').getContext('2d');
            new Chart(ctxEdades, {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_column($estadisticas['por_edad'], 'grupo_edad')) ?>,
                    datasets: [{
                        label: 'Clientes',
                        data: <?= json_encode(array_column($estadisticas['por_edad'], 'cantidad')) ?>,
                        backgroundColor: crearGradiente(ctxEdades, '#3A5A78', '#21D4FD'),
                        borderColor: '#fff',
                        borderWidth: 2,
                        borderRadius: 12,
                        hoverBackgroundColor: '#2C4460',
                        shadowOffsetX: 0,
                        shadowOffsetY: 0,
                        shadowBlur: 18,
                        shadowColor: 'rgba(33,212,253,0.18)'
                    }]
                },
                options: {
                    ...configComun,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(33,212,253,0.07)' },
                            ticks: { color: '#2196F3', font: { family: 'Toragon', size: 13 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#333', font: { family: 'Toragon', size: 13 } }
                        }
                    },
                    plugins: {
                        ...configComun.plugins,
                        title: {
                            display: true,
                            text: '👥 Distribución por Grupos de Edad',
                            color: '#2196F3',
                            font: { size: 20, weight: 'bold', family: 'Toragon' },
                            padding: 18
                        },
                        legend: { display: false },
                        datalabels: {
                            display: true,
                            color: '#fff',
                            backgroundColor: '#2196F3',
                            borderRadius: 8,
                            padding: 6,
                            font: { weight: 'bold', size: 13, family: 'Toragon' },
                            anchor: 'end',
                            align: 'top',
                            formatter: (value) => value
                        }
                    },
                    animation: {
                        duration: 1600,
                        easing: 'easeOutBounce'
                    }
                },
                plugins: [ChartDataLabels]
            });

            // Gráfico de reservas por mes (línea)
            const ctxMeses = document.getElementById('graficoMeses').getContext('2d');
            $.ajax({
                url: 'includes/estadisticas_ajax.php',
                method: 'GET',
                data: { tipo: 'meses' },
                dataType: 'json',
                success: function(data) {
                    new Chart(ctxMeses, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Reservas',
                                data: data.values,
                                backgroundColor: crearGradiente(ctxMeses, '#da291c', '#fff'),
                                borderColor: '#da291c',
                                borderWidth: 3,
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: '#da291c',
                                pointRadius: 7,
                                pointHoverRadius: 12,
                                pointStyle: 'rectRounded',
                                shadowOffsetX: 0,
                                shadowOffsetY: 0,
                                shadowBlur: 18,
                                shadowColor: 'rgba(218,41,28,0.18)'
                            }]
                        },
                        options: {
                            ...configComun,
                            plugins: {
                                ...configComun.plugins,
                                title: {
                                    display: true,
                                    text: '📈 Reservas por Mes',
                                    color: '#da291c',
                                    font: { size: 20, weight: 'bold', family: 'Toragon' },
                                    padding: 18
                                },
                                legend: { display: false },
                                datalabels: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true, grid: { color: 'rgba(218,41,28,0.07)' }, ticks: { color: '#da291c', font: { family: 'Toragon', size: 13 } } },
                                x: { grid: { display: false }, ticks: { color: '#333', font: { family: 'Toragon', size: 13 } } }
                            },
                            animation: { duration: 1800, easing: 'easeOutElastic' }
                        }
                    });
                }
            });
            // Gráfico de promedio de pasajeros por paquete (barra horizontal)
            const ctxPromedioPaquete = document.getElementById('graficoPromedioPaquete').getContext('2d');
            $.ajax({
                url: 'includes/estadisticas_ajax.php',
                method: 'GET',
                data: { tipo: 'promedio_paquete' },
                dataType: 'json',
                success: function(data) {
                    new Chart(ctxPromedioPaquete, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Promedio de pasajeros',
                                data: data.values,
                                backgroundColor: crearGradiente(ctxPromedioPaquete, '#28a745', '#fff'),
                                borderColor: '#28a745',
                                borderWidth: 3,
                                borderRadius: 14,
                                shadowOffsetX: 0,
                                shadowOffsetY: 0,
                                shadowBlur: 18,
                                shadowColor: 'rgba(40,167,69,0.18)'
                            }]
                        },
                        options: {
                            ...configComun,
                            indexAxis: 'y',
                            plugins: {
                                ...configComun.plugins,
                                title: {
                                    display: true,
                                    text: '🧑‍🤝‍🧑 Promedio de Pasajeros por Paquete',
                                    color: '#28a745',
                                    font: { size: 20, weight: 'bold', family: 'Toragon' },
                                    padding: 18
                                },
                                legend: { display: false },
                                datalabels: {
                                    display: true,
                                    color: '#fff',
                                    backgroundColor: '#28a745',
                                    borderRadius: 8,
                                    padding: 6,
                                    font: { weight: 'bold', size: 13, family: 'Toragon' },
                                    anchor: 'end',
                                    align: 'right',
                                    formatter: (value) => value
                                }
                            },
                            scales: {
                                x: { beginAtZero: true, grid: { color: 'rgba(40,167,69,0.07)' }, ticks: { color: '#28a745', font: { family: 'Toragon', size: 13 } } },
                                y: { grid: { display: false }, ticks: { color: '#333', font: { family: 'Toragon', size: 13 } } }
                            },
                            animation: { duration: 1800, easing: 'easeOutElastic' }
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>