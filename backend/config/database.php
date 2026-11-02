<?php

/*
$host = "localhost";
$dbname = "sistema_prestamos";
$username = "root";
$password = "";

try {

    $conexion = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $conexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $conexion->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {

    die("Error de conexión con la base de datos: " . $e->getMessage());

}*/




// ==========================================
// CONEXIÓN ONLINE - INFINITYFREE
// ==========================================
$host = "sql110.infinityfree.com";
$dbname = "if0_42776014_sistema_prestamos";
$username = "if0_42776014";
$password = "LtN6irQFxL";


try {

    $conexion = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $conexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $conexion->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {

    die("Error de conexión con la base de datos: " . $e->getMessage());

}