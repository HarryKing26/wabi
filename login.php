<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
redirigirSiAutenticado();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    
    if (login($conn, $correo, $password)) {
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Correo o contraseña incorrectos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Wabi-Sabi Tours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Estilos del index copiados y adaptados para el login */
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
            box-shadow: 0 0 0 2px #da291c33;
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
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover {
            background: #b82218;
        }

        .btn-login:active::after {
            content: '';
            position: absolute;
            left: 50%; top: 50%;
            width: 200%; height: 200%;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%) scale(0);
            animation: btnRipple 0.5s forwards;
        }

        @keyframes btnRipple {
            to { transform: translate(-50%, -50%) scale(1); opacity: 0; }
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
            background: #eafaf1;
            border: 1.5px solid #28a745;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 1.08em;
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
            box-shadow: 0 2px 12px rgba(40,167,69,0.08);
        }

        .success-message i {
            font-size: 1.3em;
        }

        .animated-bounce-in {
            animation: bounceIn 0.8s;
        }

        @keyframes bounceIn {
            0% { transform: scale(0.7); opacity: 0; }
            60% { transform: scale(1.1); opacity: 1; }
            80% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }

        .animated-shake {
            animation: shake 0.5s;
        }

        @keyframes shake {
            0% { transform: translateX(0); }
            20% { transform: translateX(-8px); }
            40% { transform: translateX(8px); }
            60% { transform: translateX(-6px); }
            80% { transform: translateX(6px); }
            100% { transform: translateX(0); }
        }

        .animated-fade-in {
            animation: fadeInUp 0.7s;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><span style="color: #da291c;">WABI-SABI</span> TOURS</h1>
            <p>Inicia sesión para acceder a tu cuenta</p>
        </div>

        <!-- Animación de confirmación de registro -->
        <?php if (isset($_GET['registro']) && $_GET['registro'] === 'ok'): ?>
            <div class="success-message animated-bounce-in">
                <i class="fas fa-check-circle"></i> ¡Registro exitoso! Ahora puedes iniciar sesión.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message animated-shake"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="animated-fade-in">
            <div class="form-group">
                <label for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" class="form-control" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-login">Iniciar Sesión</button>
        </form>

        <div class="login-footer animated-fade-in">
            ¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a>
        </div>
    </div>
    <script>
        // Animación de entrada para el formulario y footer
        document.querySelectorAll('.animated-fade-in').forEach(el => {
            el.style.opacity = 0;
            setTimeout(() => { el.style.opacity = 1; }, 200);
        });
        // Animación de shake en error ya está en CSS
        // Animación de bounceIn en éxito ya está en CSS
    </script>
</body>
</html>