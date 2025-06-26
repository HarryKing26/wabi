<?php
// Incluir conexión a la base de datos
require_once 'includes/db2.php';

// Procesamiento del formulario
$mensaje = '';
$error = false;

// Detectar si viene del dashboard
$from_dashboard = isset($_GET['from']) && $_GET['from'] === 'dashboard';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar los datos
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $apellidos = filter_input(INPUT_POST, 'apellidos', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $celular = filter_input(INPUT_POST, 'celular', FILTER_SANITIZE_STRING);
    $tipo_documento = filter_input(INPUT_POST, 'tipo_documento', FILTER_SANITIZE_STRING);
    $numero_documento = filter_input(INPUT_POST, 'numero_documento', FILTER_SANITIZE_STRING);
    $sexo = filter_input(INPUT_POST, 'sexo', FILTER_SANITIZE_STRING);
    $edad = filter_input(INPUT_POST, 'edad', FILTER_VALIDATE_INT);
    $paquete = filter_input(INPUT_POST, 'paquete', FILTER_SANITIZE_STRING);
    $destino = filter_input(INPUT_POST, 'destino', FILTER_SANITIZE_STRING);
    $num_pasajeros = filter_input(INPUT_POST, 'num_pasajeros', FILTER_VALIDATE_INT);
    $fecha_inicio = filter_input(INPUT_POST, 'fecha_inicio', FILTER_SANITIZE_STRING);
    $fecha_fin = filter_input(INPUT_POST, 'fecha_fin', FILTER_SANITIZE_STRING);
    $comentarios = filter_input(INPUT_POST, 'comentarios', FILTER_SANITIZE_STRING);
    $from_dashboard_post = isset($_POST['from_dashboard']) && $_POST['from_dashboard'] === '1';

    // Validar campos requeridos
    $camposRequeridos = [
        'nombre' => $nombre,
        'apellidos' => $apellidos,
        'email' => $email,
        'celular' => $celular,
        'tipo_documento' => $tipo_documento,
        'numero_documento' => $numero_documento,
        'sexo' => $sexo,
        'edad' => $edad,
        'paquete' => $paquete,
        'destino' => $destino,
        'num_pasajeros' => $num_pasajeros,
        'fecha_inicio' => $fecha_inicio,
        'fecha_fin' => $fecha_fin
    ];

    $camposFaltantes = [];
    foreach ($camposRequeridos as $campo => $valor) {
        if (empty($valor)) {
            $camposFaltantes[] = $campo;
        }
    }

    if (!empty($camposFaltantes)) {
        $mensaje = '<div class="error-mensaje">Por favor complete todos los campos requeridos.</div>';
        $error = true;
    } else {
        try {
            // Insertar en la base de datos, agregando nota si viene del dashboard
            $comentarios_final = $comentarios;
            if ($from_dashboard_post) {
                $comentarios_final = '[Reserva desde dashboard] ' . $comentarios;
                // Redirigir al dashboard con mensaje de éxito
                $mensaje = '';
                header('Location: dashboard.php?reserva=ok');
                exit();
            }
            $stmt = $pdo->prepare("INSERT INTO pedidos (
                nombre, apellidos, email, celular, tipo_documento, numero_documento, 
                sexo, edad, paquete, destino, num_pasajeros, fecha_inicio, 
                fecha_fin, comentarios, fecha_registro
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
            )");
            $stmt->execute([
                $nombre, $apellidos, $email, $celular, $tipo_documento, $numero_documento,
                $sexo, $edad, $paquete, $destino, $num_pasajeros, $fecha_inicio,
                $fecha_fin, $comentarios_final
            ]);

            // Preparar el correo electrónico
            $to = 'info@wabisabi-japan.com';
            $subject = 'Nueva solicitud de reserva - Wabi-Sabi Japan Tours';
            $message = "
                <html>
                <head>
                    <title>Nueva solicitud de reserva</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .datos { background: #f9f9f9; padding: 15px; border-radius: 5px; }
                        h2 { color: #da291c; }
                        .label { font-weight: bold; color: #555; }
                    </style>
                </head>
                <body>
                    <h2>Nueva solicitud de reserva</h2>
                    <div class='datos'>
                        <p><span class='label'>Nombre:</span> $nombre $apellidos</p>
                        <p><span class='label'>Email:</span> $email</p>
                        <p><span class='label'>Celular:</span> $celular</p>
                        <p><span class='label'>Documento:</span> $tipo_documento: $numero_documento</p>
                        <p><span class='label'>Sexo:</span> $sexo</p>
                        <p><span class='label'>Edad:</span> $edad</p>
                        <p><span class='label'>Paquete seleccionado:</span> $paquete</p>
                        <p><span class='label'>Destino:</span> $destino</p>
                        <p><span class='label'>Número de pasajeros:</span> $num_pasajeros</p>
                        <p><span class='label'>Fecha de inicio:</span> $fecha_inicio</p>
                        <p><span class='label'>Fecha de fin:</span> $fecha_fin</p>
                        <p><span class='label'>Comentarios adicionales:</span> " . ($comentarios ? $comentarios : 'Ninguno') . "</p>
                    </div>
                    <p>Por favor contactar al cliente lo antes posible para confirmar disponibilidad.</p>
                </body>
                </html>
            ";

            // Cabeceras para el correo HTML
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: $email\r\n";
            $headers .= "Reply-To: $email\r\n";

            // Enviar el correo
            if (mail($to, $subject, $message, $headers)) {
                $mensaje = '<div class="exito-mensaje">¡Gracias por tu solicitud! Nos pondremos en contacto contigo pronto para confirmar tu reserva.</div>';
                
                // Limpiar los campos del formulario
                $nombre = $apellidos = $email = $celular = $tipo_documento = $numero_documento = '';
                $sexo = $edad = $paquete = $destino = $num_pasajeros = $fecha_inicio = $fecha_fin = $comentarios = '';
            } else {
                $mensaje = '<div class="error-mensaje">Hubo un error al enviar tu solicitud. Por favor inténtalo de nuevo más tarde.</div>';
                $error = true;
            }
        } catch (PDOException $e) {
            $mensaje = '<div class="error-mensaje">Error al procesar tu solicitud: ' . $e->getMessage() . '</div>';
            $error = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Reserva - Wabi-Sabi Japan Tours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Estilos heredados del index.html */
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

        /* Estilos específicos del formulario */
        .formulario-reserva {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 30px;
            margin: 30px auto;
            max-width: 800px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="number"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s, box-shadow 0.3s;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus,
        .form-group input[type="tel"]:focus,
        .form-group input[type="number"]:focus,
        .form-group input[type="date"]:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #da291c;
            box-shadow: 0 0 0 3px rgba(218, 41, 28, 0.2);
            outline: none;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .required-field::after {
            content: " *";
            color: #da291c;
        }

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

        .fechas-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .documento-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
        }

        .datos_personales-container {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .fechas-container,
            .documento-container,
            .datos_personales-container {
                grid-template-columns: 1fr;
            }
            
            .formulario-reserva {
                padding: 20px;
            }
        }

        /* Estilos para Select2 */
        .select2-container--default .select2-selection--single {
            height: 46px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 46px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px;
        }

        .animated-fade-in {
            animation: fadeInUp 0.8s;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .exito-mensaje, .error-mensaje {
            animation: bounceIn 0.7s;
        }
        @keyframes bounceIn {
            0% { transform: scale(0.7); opacity: 0; }
            60% { transform: scale(1.1); opacity: 1; }
            80% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }

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

        /* Redes sociales footer mejoradas */
        .redes-sociales {
            display: flex;
            justify-content: center;
            gap: 18px;
            margin: 24px 0 0 0;
        }
        .redes-sociales a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #fff;
            color: #da291c;
            font-size: 1.5em;
            box-shadow: 0 2px 8px rgba(218,41,28,0.10);
            transition: background 0.3s, color 0.3s, transform 0.2s;
            text-decoration: none;
        }
        .redes-sociales a:hover {
            background: #da291c;
            color: #fff;
            transform: scale(1.13) rotate(-8deg);
        }
    </style>
</head>
<body>
    <!-- Botón flecha arriba -->
    <button id="btn-arriba" class="boton-flecha" onclick="window.scrollTo({top:0,behavior:'smooth'})" title="Ir arriba" aria-label="Subir">
        <i class="fas fa-arrow-up"></i>
    </button>
    <!-- Botón flecha abajo -->
    <button id="btn-abajo" class="boton-flecha" onclick="window.scrollTo({top:document.body.scrollHeight,behavior:'smooth'})" title="Ir abajo" aria-label="Bajar">
        <i class="fas fa-arrow-down"></i>
    </button>

    <header>
        <h1><span class="destacado">WABI-SABI</span> - TOURS A JAPÓN</h1>
        <nav>
            <a href="index.html">Inicio</a>
            <a href="index.html#paquetes">Paquetes</a>
            <a href="index.html#requisitos">Requisitos</a>
            <a href="index.html#contactanos">Contacto</a>
            <a href="formulario.php">Formulario</a>
            <a href="pitch.html">Pitch</a>
            <a href="login.php" class="btn-login-nav" style="background:#ffd700;color:#b82218;font-weight:bold;padding:10px 22px;border-radius:8px;margin-left:10px;box-shadow:0 2px 8px rgba(218,41,28,0.10);text-transform:uppercase;">Iniciar sesión</a>
        </nav>
    </header>
    <div class="espacio-header"></div>

    <div class="formulario-reserva">
        <h2>Formulario de Reserva</h2>
        <p>Por favor completa todos los campos requeridos para solicitar tu reserva. Nos pondremos en contacto contigo para confirmar disponibilidad.</p>
        
        <?php if (!empty($mensaje)) echo $mensaje; ?>
        
        <form action="formulario.php<?php echo $from_dashboard ? '?from=dashboard' : ''; ?>" method="post" class="animated-fade-in">
            <?php if ($from_dashboard): ?>
                <input type="hidden" name="from_dashboard" value="1">
            <?php endif; ?>
            <div class="form-group">
                <label for="nombre" class="required-field">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="apellidos" class="required-field">Apellidos</label>
                <input type="text" id="apellidos" name="apellidos" value="<?php echo isset($apellidos) ? htmlspecialchars($apellidos) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email" class="required-field">Email</label>
                <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="celular" class="required-field">Número de celular</label>
                <input type="tel" id="celular" name="celular" value="<?php echo isset($celular) ? htmlspecialchars($celular) : ''; ?>" required>
            </div>
            
            <div class="documento-container">
                <div class="form-group">
                    <label for="tipo_documento" class="required-field">Tipo de documento</label>
                    <select id="tipo_documento" name="tipo_documento" required>
                        <option value="">Seleccione...</option>
                        <option value="DNI" <?php echo (isset($tipo_documento) && $tipo_documento == 'DNI') ? 'selected' : ''; ?>>DNI</option>
                        <option value="Pasaporte" <?php echo (isset($tipo_documento) && $tipo_documento == 'Pasaporte') ? 'selected' : ''; ?>>Pasaporte</option>
                        <option value="Carné Extranjería" <?php echo (isset($tipo_documento) && $tipo_documento == 'Carné Extranjería') ? 'selected' : ''; ?>>Carné Extranjería</option>
                        <option value="Otro" <?php echo (isset($tipo_documento) && $tipo_documento == 'Otro') ? 'selected' : ''; ?>>Otro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="numero_documento" class="required-field">Número de documento</label>
                    <input type="text" id="numero_documento" name="numero_documento" value="<?php echo isset($numero_documento) ? htmlspecialchars($numero_documento) : ''; ?>" required>
                </div>
            </div>
            
            <div class="datos-personales-container">
                <div class="form-group">
                    <label for="sexo" class="required-field">Sexo</label>
                    <select id="sexo" name="sexo" required>
                        <option value="">Seleccione...</option>
                        <option value="Masculino" <?php echo (isset($sexo) && $sexo == 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                        <option value="Femenino" <?php echo (isset($sexo) && $sexo == 'Femenino') ? 'selected' : ''; ?>>Femenino</option>
                        <option value="Otro" <?php echo (isset($sexo) && $sexo == 'Otro') ? 'selected' : ''; ?>>Otro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edad" class="required-field">Edad</label>
                    <input type="number" id="edad" name="edad" min="1" max="120" value="<?php echo isset($edad) ? htmlspecialchars($edad) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="num_pasajeros" class="required-field">N° de pasajeros</label>
                    <input type="number" id="num_pasajeros" name="num_pasajeros" min="1" max="20" value="<?php echo isset($num_pasajeros) ? htmlspecialchars($num_pasajeros) : '1'; ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="paquete" class="required-field">Paquete a elegir</label>
                <select id="paquete" name="paquete" required>
                    <option value="">Selecciona un paquete</option>
                    <option value="Tour Esencial Tokio" <?php echo (isset($paquete) && $paquete == 'Tour Esencial Tokio') ? 'selected' : ''; ?>>Tour Esencial Tokio (5 días / 4 noches)</option>
                    <option value="Tour Gold Tokio" <?php echo (isset($paquete) && $paquete == 'Tour Gold Tokio') ? 'selected' : ''; ?>>Tour Gold Tokio (10 días / 9 noches)</option>
                    <option value="Experiencia Tour Premium" <?php echo (isset($paquete) && $paquete == 'Experiencia Tour Premium') ? 'selected' : ''; ?>>Experiencia Tour Premium (12 días / 11 noches)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="destino" class="required-field">Destino principal</label>
                <select id="destino" name="destino" required>
                    <option value="">Selecciona un destino</option>
                    <option value="Tokio" <?php echo (isset($destino) && $destino == 'Tokio') ? 'selected' : ''; ?>>Tokio</option>
                    <option value="Kioto" <?php echo (isset($destino) && $destino == 'Kioto') ? 'selected' : ''; ?>>Kioto</option>
                    <option value="Osaka" <?php echo (isset($destino) && $destino == 'Osaka') ? 'selected' : ''; ?>>Osaka</option>
                    <option value="Hiroshima" <?php echo (isset($destino) && $destino == 'Hiroshima') ? 'selected' : ''; ?>>Hiroshima</option>
                    <option value="Sapporo" <?php echo (isset($destino) && $destino == 'Sapporo') ? 'selected' : ''; ?>>Sapporo</option>
                    <option value="Fukuoka" <?php echo (isset($destino) && $destino == 'Fukuoka') ? 'selected' : ''; ?>>Fukuoka</option>
                    <option value="Varios" <?php echo (isset($destino) && $destino == 'Varios') ? 'selected' : ''; ?>>Varios destinos</option>
                </select>
            </div>
            
            <div class="fechas-container">
                <div class="form-group">
                    <label for="fecha_inicio" class="required-field">Fecha de inicio del tour</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo isset($fecha_inicio) ? htmlspecialchars($fecha_inicio) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="fecha_fin" class="required-field">Fecha de fin del tour</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo isset($fecha_fin) ? htmlspecialchars($fecha_fin) : ''; ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="comentarios">Comentarios adicionales (opcional)</label>
                <textarea id="comentarios" name="comentarios"><?php echo isset($comentarios) ? htmlspecialchars($comentarios) : ''; ?></textarea>
            </div>
            
            <div class="form-group" style="text-align: center;">
                <button type="submit" class="btn">Enviar solicitud de reserva</button>
            </div>
        </form>
    </div>

    <footer id="contactanos">
        <h2>Contacta con Wabi-Sabi Japan Tours</h2>
        <p>¡Déjanos ayudarte a planificar el viaje de tus sueños a Japón!</p>
        <div style="margin: 30px auto; max-width: 600px;">
            <a href="mailto:info@wabisabi-japan.com" class="btn" style="margin: 10px;"><i class="fas fa-envelope"></i> Email</a>
            <a href="tel:+573001234567" class="btn" style="margin: 10px;"><i class="fas fa-phone"></i> Llamar</a>
            <a href="https://wa.me/573001234567" class="btn" style="margin: 10px;"><i class="fab fa-whatsapp"></i> WhatsApp</a>
        </div>

        <div class="redes-sociales">
            <a href="https://www.facebook.com/WabiSabiJapanTours" target="_blank" aria-label="Facebook">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="https://www.instagram.com/WabiSabiJapan" target="_blank" aria-label="Instagram">
                <i class="fab fa-instagram"></i>
            </a>
            <a href="https://twitter.com/WabiSabiTours" target="_blank" aria-label="X">
                <i class="fab fa-x-twitter"></i>
            </a>
            <a href="https://www.youtube.com/user/WabiSabiJapan" target="_blank" aria-label="YouTube">
                <i class="fab fa-youtube"></i>
            </a>
        </div>

        <div style="margin-top: 30px;">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d207446.2481473782!2d139.60078095!3d35.66844145!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x60188b857628235d%3A0xcdd8aef709a2b520!2sTokyo%2C%20Jap%C3%B3n!5e0!3m2!1ses!2ses!4v1717023883894!5m2!1ses!2ses"
                width="100%" height="450" style="border:0; border-radius: 8px;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>

        <p style="margin-top: 30px;">© 2025 Wabi-Sabi Japan - Creada únicamente con fines académicos.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Inicializar Select2 para selects
        $(document).ready(function() {
            $('#paquete, #destino, #tipo_documento, #sexo').select2({
                width: '100%'
            });
            
            // Validación de fechas en el cliente
            const fechaInicio = document.getElementById('fecha_inicio');
            const fechaFin = document.getElementById('fecha_fin');
            
            // Establecer fecha mínima como hoy
            const hoy = new Date().toISOString().split('T')[0];
            fechaInicio.min = hoy;
            
            // Actualizar fecha mínima de fin cuando cambia la de inicio
            fechaInicio.addEventListener('change', function() {
                fechaFin.min = this.value;
                
                // Si la fecha de fin es anterior a la nueva fecha de inicio, resetearla
                if (fechaFin.value && fechaFin.value < this.value) {
                    fechaFin.value = '';
                }
            });
            
            // Validación adicional al enviar el formulario
            document.querySelector('form').addEventListener('submit', function(e) {
                if (fechaInicio.value && fechaFin.value && fechaFin.value < fechaInicio.value) {
                    alert('La fecha de fin no puede ser anterior a la fecha de inicio.');
                    e.preventDefault();
                }
                
                // Validar que la edad sea un número válido
                const edad = document.getElementById('edad');
                if (edad.value < 1 || edad.value > 120) {
                    alert('Por favor ingrese una edad válida (1-120 años).');
                    e.preventDefault();
                }
                
                // Validar número de pasajeros
                const numPasajeros = document.getElementById('num_pasajeros');
                if (numPasajeros.value < 1 || numPasajeros.value > 20) {
                    alert('El número de pasajeros debe estar entre 1 y 20.');
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>