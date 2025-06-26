<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
redirigirSiAutenticado();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validaciones
    if (empty($nombres)) {
        $error = 'El nombre es requerido';
    } elseif (empty($apellidos)) {
        $error = 'Los apellidos son requeridos';
    } elseif (empty($correo)) {
        $error = 'El correo es requerido';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo no es válido';
    } elseif (empty($password)) {
        $error = 'La contraseña es requerida';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } else {
        // Verificar si el correo ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        
        if ($stmt->fetch()) {
            $error = 'Este correo ya está registrado';
        } else {
            // Registrar usuario
            if (registrarUsuario($conn, $nombres, $apellidos, $correo, $password)) {
                $success = 'Registro exitoso. Ahora puedes iniciar sesión.';
            } else {
                $error = 'Error al registrar. Intenta nuevamente.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Wabi-Sabi Tours</title>
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

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 500px;
            margin: 80px auto;
            padding: 40px;
            text-align: center;
        }

        .login-header h1 {
            font-family: 'Toragon', Arial, sans-serif;
            color: #da291c;
            margin-bottom: 30px;
            font-size: 2.2em;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
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
        }

        .btn-login {
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

        .btn-login:hover {
            background: #b82218;
        }

        .login-footer {
            margin-top: 25px;
            font-size: 0.95em;
        }

        .login-footer a {
            color: #da291c;
            text-decoration: none;
            font-weight: bold;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: #da291c;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .success-message {
            color: #28a745;
            margin-bottom: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><span style="color: #da291c;">WABI-SABI</span> TOURS</h1>
            <p>Crea una cuenta para comenzar</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php elseif ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="nombres">Nombres</label>
                <input type="text" id="nombres" name="nombres" class="form-control" required value="<?php echo isset($_POST['nombres']) ? htmlspecialchars($_POST['nombres']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="apellidos">Apellidos</label>
                <input type="text" id="apellidos" name="apellidos" class="form-control" required value="<?php echo isset($_POST['apellidos']) ? htmlspecialchars($_POST['apellidos']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" class="form-control" required value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Contraseña (mínimo 8 caracteres)</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmar contraseña</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>

            <button type="submit" class="btn-login">Registrarse</button>
        </form>
        <?php endif; ?>

        <div class="login-footer">
            ¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a>
        </div>
    </div>
</body>
</html>