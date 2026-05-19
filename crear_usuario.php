<?php
require_once 'conexion.php';

$nombre   = 'Administrador';
$email    = 'admin@empresa.com';
$password = '1234';
$rol      = 'admin';

$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conexion->prepare(
    "INSERT INTO usuario (nombre, email, password, rol) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ssss", $nombre, $email, $hash, $rol);

if ($stmt->execute()) {
    echo "<p style='font-family:monospace;color:green;font-size:18px;'>✅ Usuario creado. Email: <b>{$email}</b> | Contraseña: <b>{$password}</b></p>";
    echo "<p style='font-family:monospace;color:red;'>⚠️ Elimina este archivo del servidor después de usarlo.</p>";
    echo "<a href='index.php' style='font-family:monospace;color:#6c63ff;'>→ Ir al Login</a>";
} else {
    echo "<p style='font-family:monospace;color:red;'>❌ Error: " . $stmt->error . "</p>";
    echo "<p style='font-family:monospace;color:orange;'>¿El usuario ya existe? Intenta: <a href='index.php'>→ Login</a></p>";
}

$stmt->close();
$conexion->close();
?>
