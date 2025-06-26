<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
redirigirSiNoAutenticado();

$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Wabi-Sabi Tours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @font-face {
            font-family: 'Toragon';
            src: url('font/toragon.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        :root {
            --primary: #da291c;
            --primary-light: rgba(218, 41, 28, 0.1);
            --primary-dark: #b82218;
            --text: #333;
            --text-light: #666;
            --bg: #f9f9f9;
            --card-bg: #fff;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
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
            color: var(--text);
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
            color: var(--primary);
            transform: scale(1.08);
            outline: none;
        }

        .dashboard-container {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 16px;
            box-shadow: var(--shadow);
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
            padding: 40px;
        }

        .welcome-message {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 20px;
        }

        .welcome-message h2 {
            font-family: 'Toragon', Arial, sans-serif;
            color: var(--primary);
            font-size: 2.2em;
            margin-bottom: 10px;
        }

        .welcome-message p {
            font-size: 1.1em;
            color: var(--text-light);
        }

        .welcome-message::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }

        .user-info {
            background: var(--primary-light);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 4px solid var(--primary);
        }

        .user-info h3 {
            color: var(--primary);
            margin-top: 0;
            font-size: 1.4em;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-info h3 i {
            font-size: 1.2em;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .info-card {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-top: 3px solid var(--primary);
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .info-card h3, .info-card h4 {
            color: var(--primary);
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.3em;
        }

        .info-card p {
            color: var(--text-light);
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .chart-container {
            position: relative;
            height: 250px;
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            border: none;
            cursor: pointer;
            font-size: 0.95em;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(218, 41, 28, 0.2);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary-light);
        }

        .admin-section {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px dashed var(--primary);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .stat-value {
            font-size: 2.5em;
            font-weight: bold;
            color: var(--primary);
            margin: 10px 0;
        }

        .stat-card .stat-label {
            color: var(--text-light);
            font-size: 0.9em;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            background: var(--primary-light);
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 25px;
                width: 95%;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1><span class="destacado">WABI-SABI</span> - TOURS A JAPÓN </h1>
        <nav>
            <a href="index.html">Inicio</a>
            <a href="#paquetes">Paquetes</a>
            <a href="#contactanos">Contacto</a>
            <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
        </nav>
    </header>
    <div class="espacio-header"></div>

    <div class="dashboard-container">
        <?php if (isset($_GET['reserva']) && $_GET['reserva'] === 'ok'): ?>
            <div style="background:#eafaf1;border:1.5px solid #28a745;color:#28a745;padding:16px 20px;border-radius:10px;margin-bottom:24px;display:flex;align-items:center;gap:10px;font-weight:bold;animation:bounceIn 0.7s;">
                <i class="fas fa-check-circle"></i> ¡Reserva realizada con éxito! Puedes ver tu solicitud abajo.
            </div>
        <?php endif; ?>

        <div class="welcome-message">
            <h2>Bienvenido, <?php echo htmlspecialchars($usuario['nombres']); ?></h2>
            <p>Panel de control de Wabi-Sabi Tours</p>
        </div>

        <!-- Acciones rápidas -->
        <div style="display: flex; flex-wrap: wrap; gap: 18px; justify-content: center; margin-bottom: 32px;">
            <a href="formulario.php" class="btn" style="min-width:170px;"><i class="fas fa-plus-circle"></i> Nueva reserva</a>
            <a href="editar_perfil.php" class="btn btn-outline" style="min-width:170px;"><i class="fas fa-user-edit"></i> Editar perfil</a>
            <a href="mailto:soporte@wabisabi.com" class="btn" style="min-width:170px;"><i class="fas fa-headset"></i> Contactar soporte</a>
            <button type="button" class="btn btn-outline" id="btn-favorito" style="min-width:170px;"><i class="fas fa-heart"></i> Añadir favorito</button>
        </div>

        <!-- Notificaciones -->
        <div id="notificaciones" style="background: #fff8e1; border-left: 5px solid #da291c; border-radius: 10px; box-shadow: 0 2px 10px rgba(218,41,28,0.07); padding: 18px 24px; margin-bottom: 32px; display: flex; align-items: center; gap: 16px;">
            <i class="fas fa-bell" style="color:#da291c; font-size:1.5em;"></i>
            <div>
                <strong>¡Recuerda!</strong> Puedes editar tu perfil o contactar soporte en cualquier momento desde este panel.
            </div>
        </div>

        <div class="user-info">
            <h3><i class="fas fa-user-circle"></i> Tu información</h3>
            <div class="info-grid">
                <div class="info-card">
                    <p><strong>Nombre completo:</strong> <?php echo htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']); ?></p>
                    <p><strong>Correo electrónico:</strong> <?php echo htmlspecialchars($usuario['correo']); ?></p>
                    <p><strong>Teléfono:</strong> <?php echo !empty($usuario['telefono']) ? htmlspecialchars($usuario['telefono']) : 'No proporcionado'; ?></p>
                </div>
                <div class="info-card">
                    <p><strong>Rol:</strong> <span class="badge"><?php echo ucfirst($usuario['rol']); ?></span></p>
                    <p><strong>Miembro desde:</strong> <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></p>
                    <p><strong>Última conexión:</strong> <?php echo $usuario['ultima_conexion'] ? date('d/m/Y H:i', strtotime($usuario['ultima_conexion'])) : 'Primer inicio'; ?></p>
                </div>
            </div>
        </div>

        <?php if (esAdministrador()): ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">1,248</div>
                <div class="stat-label">Reservas totales</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">342</div>
                <div class="stat-label">Usuarios activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">¥8,950K</div>
                <div class="stat-label">Ingresos 2023</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">92%</div>
                <div class="stat-label">Satisfacción</div>
            </div>
        </div>
        <?php endif; ?>

        <div class="info-grid">
            <div class="info-card">
                <h3><i class="fas fa-calendar-alt"></i> Tus reservas</h3>
                <p>Aquí encontrarás tus paquetes reservados.</p>
                <div class="chart-container">
                    <canvas id="reservasChart"></canvas>
                </div>
                <a href="#" class="btn">Ver todas las reservas</a>
            </div>
            <div class="info-card">
                <h3><i class="fas fa-heart"></i> Favoritos</h3>
                <p>Guarda tus lugares favoritos para visitar en Japón.</p>
                <div class="chart-container">
                    <canvas id="favoritosChart"></canvas>
                </div>
                <a href="#" class="btn btn-outline">Añadir favoritos</a>
            </div>
            <!-- Nuevo gráfico: Visitas (barra horizontal) -->
            <div class="info-card">
                <h3><i class="fas fa-chart-bar"></i> Visitas al sitio</h3>
                <p>Estadísticas de visitas recientes.</p>
                <div class="chart-container">
                    <canvas id="visitasChart"></canvas>
                </div>
            </div>
            <!-- Nuevo gráfico: Intereses (radar) -->
            <div class="info-card">
                <h3><i class="fas fa-bullseye"></i> Tus intereses</h3>
                <p>Áreas de interés según tu actividad.</p>
                <div class="chart-container">
                    <canvas id="interesesChart"></canvas>
                </div>
            </div>
        </div>

        <?php if (esAdministrador()): ?>
        <div class="admin-section">
            <h3 style="color: var(--primary);"><i class="fas fa-lock"></i> Panel de Administrador</h3>
            <div class="info-grid">
                <div class="info-card">
                    <h4><i class="fas fa-users-cog"></i> Gestión de usuarios</h4>
                    <p>Administra los usuarios registrados en el sistema.</p>
                    <div class="chart-container">
                        <canvas id="usuariosChart"></canvas>
                    </div>
                    <a href="crud.php" class="btn">Ir a gestión</a>
                </div>
                <div class="info-card">
                    <h4><i class="fas fa-chart-bar"></i> Destinos populares</h4>
                    <p>Los destinos más reservados este mes.</p>
                    <div class="chart-container">
                        <canvas id="destinosChart"></canvas>
                    </div>
                    <a href="reportes.php" class="btn">Ver reportes</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 40px;">
            <a href="index.html" class="btn btn-outline">Volver al sitio principal</a>
        </div>
    </div>

    <script>
        // Gráfico de reservas del usuario
        const reservasCtx = document.getElementById('reservasChart').getContext('2d');
        const reservasChart = new Chart(reservasCtx, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: 'Reservas por mes',
                    data: [2, 1, 3, 0, 2, 1],
                    backgroundColor: 'rgba(218, 41, 28, 0.1)',
                    borderColor: 'rgba(218, 41, 28, 0.9)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: 'rgba(218, 41, 28, 0.9)',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Gráfico de favoritos
        const favoritosCtx = document.getElementById('favoritosChart').getContext('2d');
        const favoritosChart = new Chart(favoritosCtx, {
            type: 'doughnut',
            data: {
                labels: ['Kioto', 'Tokio', 'Osaka', 'Hiroshima', 'Nara'],
                datasets: [{
                    data: [5, 3, 2, 1, 1],
                    backgroundColor: [
                        'rgba(218, 41, 28, 0.8)',
                        'rgba(218, 41, 28, 0.6)',
                        'rgba(218, 41, 28, 0.4)',
                        'rgba(218, 41, 28, 0.3)',
                        'rgba(218, 41, 28, 0.2)'
                    ],
                    borderColor: 'rgba(255, 255, 255, 0.8)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 20
                        }
                    }
                },
                cutout: '65%'
            }
        });

        // Gráfico de visitas (barra horizontal)
        const visitasCtx = document.getElementById('visitasChart').getContext('2d');
        new Chart(visitasCtx, {
            type: 'bar',
            data: {
                labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
                datasets: [{
                    label: 'Visitas',
                    data: [12, 19, 8, 15, 22, 17, 10],
                    backgroundColor: 'rgba(218, 41, 28, 0.7)',
                    borderRadius: 8,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    y: { grid: { display: false } }
                }
            }
        });

        // Gráfico de intereses (radar)
        const interesesCtx = document.getElementById('interesesChart').getContext('2d');
        new Chart(interesesCtx, {
            type: 'radar',
            data: {
                labels: ['Cultura', 'Gastronomía', 'Tecnología', 'Naturaleza', 'Historia', 'Compras'],
                datasets: [{
                    label: 'Nivel de interés',
                    data: [8, 7, 6, 9, 7, 5],
                    backgroundColor: 'rgba(218,41,28,0.15)',
                    borderColor: 'rgba(218,41,28,0.8)',
                    pointBackgroundColor: '#da291c',
                    pointBorderColor: '#fff',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    r: {
                        angleLines: { color: 'rgba(0,0,0,0.07)' },
                        grid: { color: 'rgba(0,0,0,0.07)' },
                        pointLabels: { color: '#333', font: { size: 14 } },
                        min: 0, max: 10
                    }
                }
            }
        });

        // Acción rápida: Añadir favorito
        document.getElementById('btn-favorito').onclick = function() {
            alert('¡Añadido a favoritos!');
        };

        <?php if (esAdministrador()): ?>
        // Gráfico de usuarios (solo para admin)
        const usuariosCtx = document.getElementById('usuariosChart').getContext('2d');
        const usuariosChart = new Chart(usuariosCtx, {
            type: 'bar',
            data: {
                labels: ['Clientes', 'Guías', 'Administradores'],
                datasets: [{
                    label: 'Usuarios por rol',
                    data: [280, 45, 17],
                    backgroundColor: [
                        'rgba(218, 41, 28, 0.7)',
                        'rgba(218, 41, 28, 0.5)',
                        'rgba(218, 41, 28, 0.3)'
                    ],
                    borderColor: [
                        'rgba(218, 41, 28, 1)',
                        'rgba(218, 41, 28, 1)',
                        'rgba(218, 41, 28, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Gráfico de destinos populares (solo para admin)
        const destinosCtx = document.getElementById('destinosChart').getContext('2d');
        const destinosChart = new Chart(destinosCtx, {
            type: 'bar',
            data: {
                labels: ['Kioto', 'Tokio', 'Osaka', 'Hakone', 'Nara'],
                datasets: [{
                    label: 'Reservas',
                    data: [125, 98, 76, 45, 32],
                    backgroundColor: 'rgba(218, 41, 28, 0.7)',
                    borderColor: 'rgba(218, 41, 28, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>