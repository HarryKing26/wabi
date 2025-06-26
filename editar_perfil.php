<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
redirigirSiNoAutenticado();

$usuario = $_SESSION['usuario'];
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);
    // Validación básica
    if ($nombres && $apellidos && $correo) {
        $id = $usuario['id'];
        $stmt = $conn->prepare('UPDATE usuarios SET nombres=?, apellidos=?, telefono=?, correo=? WHERE id=?');
        $stmt->bind_param('ssssi', $nombres, $apellidos, $telefono, $correo, $id);
        if ($stmt->execute()) {
            $mensaje = 'Perfil actualizado correctamente.';
            // Actualizar sesión
            $_SESSION['usuario']['nombres'] = $nombres;
            $_SESSION['usuario']['apellidos'] = $apellidos;
            $_SESSION['usuario']['telefono'] = $telefono;
            $_SESSION['usuario']['correo'] = $correo;
        } else {
            $error = 'Error al actualizar el perfil.';
        }
        $stmt->close();
    } else {
        $error = 'Por favor completa todos los campos obligatorios.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Wabi-Sabi Tours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @font-face {
            font-family: 'Toragon';
            src: url('font/toragon.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        body {
            margin: 0; padding: 0;
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
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: -1;
        }
        .perfil-container {
            background: rgba(255,255,255,0.97);
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.18);
            width: 90%; max-width: 500px;
            margin: 80px auto;
            padding: 40px 30px 30px 30px;
            text-align: center;
        }
        h2 {
            font-family: 'Toragon', Arial, sans-serif;
            color: #da291c;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 18px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #555;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            transition: border 0.3s;
        }
        .form-control:focus {
            border-color: #da291c;
            outline: none;
            box-shadow: 0 0 0 2px #da291c33;
        }
        .btn-guardar {
            background: #da291c;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
            font-weight: bold;
            margin-top: 10px;
        }
        .btn-guardar:hover {
            background: #b82218;
        }
        .mensaje-exito {
            color: #28a745;
            background: #eafaf1;
            border: 1.5px solid #28a745;
            border-radius: 8px;
            padding: 12px 18px;
            margin-bottom: 18px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
            box-shadow: 0 2px 12px rgba(40,167,69,0.08);
            animation: bounceIn 0.8s;
        }
        .mensaje-error {
            color: #da291c;
            background: #fff0f0;
            border: 1.5px solid #da291c;
            border-radius: 8px;
            padding: 12px 18px;
            margin-bottom: 18px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
            box-shadow: 0 2px 12px rgba(218,41,28,0.08);
            animation: shake 0.5s;
        }
        @keyframes bounceIn {
            0% { transform: scale(0.7); opacity: 0; }
            60% { transform: scale(1.1); opacity: 1; }
            80% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }
        @keyframes shake {
            0% { transform: translateX(0); }
            20% { transform: translateX(-8px); }
            40% { transform: translateX(8px); }
            60% { transform: translateX(-6px); }
            80% { transform: translateX(6px); }
            100% { transform: translateX(0); }
        }
        .btn-volver {
            display: inline-block;
            margin-top: 18px;
            background: transparent;
            border: 2px solid #da291c;
            color: #da291c;
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: bold;
            text-decoration: none;
            transition: background 0.3s, color 0.3s;
        }
        .btn-volver:hover {
            background: #da291c;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="perfil-container">
        <h2><i class="fas fa-user-edit"></i> Editar Perfil</h2>
        <?php if ($mensaje): ?>
            <div class="mensaje-exito"><i class="fas fa-check-circle"></i> <?php echo $mensaje; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mensaje-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="nombres">Nombres</label>
                <input type="text" id="nombres" name="nombres" class="form-control" required value="<?php echo htmlspecialchars($usuario['nombres']); ?>">
            </div>
            <div class="form-group">
                <label for="apellidos">Apellidos</label>
                <input type="text" id="apellidos" name="apellidos" class="form-control" required value="<?php echo htmlspecialchars($usuario['apellidos']); ?>">
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="text" id="telefono" name="telefono" class="form-control" value="<?php echo htmlspecialchars($usuario['telefono']); ?>">
            </div>
            <div class="form-group">
                <label for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" class="form-control" required value="<?php echo htmlspecialchars($usuario['correo']); ?>">
            </div>
            <button type="submit" class="btn-guardar"><i class="fas fa-save"></i> Guardar Cambios</button>
        </form>
        <a href="dashboard.php" class="btn-volver"><i class="fas fa-arrow-left"></i> Volver al Dashboard</a>
    </div>
</body>
</html>
