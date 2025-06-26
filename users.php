<?php
ini_set('display_errors', 1);              // Muestra los errores en pantalla
ini_set('display_startup_errors', 1);      // Muestra errores durante el arranque de PHP
error_reporting(E_ALL);                    // Reporta todos los errores y advertencias
// create_users.php
require_once 'includes/db.php';

// Usuario administrador
$admin_password = password_hash('Admin1234', PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO usuarios (nombres, apellidos, correo, password, rol) VALUES (?, ?, ?, ?, ?)");
$stmt->execute(['Admin', 'WabiSabi', 'admin@wabisabi.com', $admin_password, 'administrador']);

// Usuario normal
$user_password = password_hash('User1234', PASSWORD_DEFAULT);
$stmt->execute(['Usuario', 'Ejemplo', 'usuario@ejemplo.com', $user_password, 'usuario']);

echo "Usuarios creados exitosamente";
?>