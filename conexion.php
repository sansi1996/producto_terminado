<?php
// Configuración de la conexión a Docker MySQL

$host = "127.0.0.1";
$usuario = "root";
$password = "root";
$base_datos = "producto_terminado";
$puerto = 3307;

// Crear conexión
$conexion = new mysqli(
    $host,
    $usuario,
    $password,
    $base_datos,
    $puerto
);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Charset UTF-8
$conexion->set_charset("utf8mb4");

?>