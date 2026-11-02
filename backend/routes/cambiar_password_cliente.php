<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../config/database.php";

try {

    // =========================================================
    // SOLO PERMITIR POST
    // =========================================================

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {

        http_response_code(405);

        echo json_encode([
            "success" => false,
            "message" => "Método no permitido."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // =========================================================
    // VERIFICAR SESIÓN
    // =========================================================

    $usuarioSesion = $_SESSION["usuario"] ?? null;

    if (!$usuarioSesion) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "No hay una sesión activa."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // =========================================================
    // VERIFICAR QUE SEA CLIENTE
    // =========================================================

    $rol = strtolower(
        trim(
            (string) ($usuarioSesion["rol"] ?? "")
        )
    );

    if ($rol !== "cliente") {

        http_response_code(403);

        echo json_encode([
            "success" => false,
            "message" => "No tienes permiso para realizar esta acción."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // =========================================================
    // OBTENER ID DEL CLIENTE DESDE LA SESIÓN
    // =========================================================

    $idUsuario =
        $usuarioSesion["id_usuario"]
        ?? $usuarioSesion["id"]
        ?? null;

    if (!$idUsuario) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "No fue posible identificar al usuario."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // =========================================================
    // RECIBIR DATOS
    // =========================================================

    $datos = json_decode(
        file_get_contents("php://input"),
        true
    );

    if (!is_array($datos)) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Datos inválidos."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $passwordActual =
        (string) ($datos["password_actual"] ?? "");

    $passwordNueva =
        (string) ($datos["password_nueva"] ?? "");

    $passwordConfirmar =
        (string) ($datos["password_confirmar"] ?? "");


    // =========================================================
    // VALIDACIONES
    // =========================================================

    if (
        $passwordActual === "" ||
        $passwordNueva === "" ||
        $passwordConfirmar === ""
    ) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Todos los campos son obligatorios."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    if (strlen($passwordNueva) < 6) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "La nueva contraseña debe tener al menos 6 caracteres."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    if ($passwordNueva !== $passwordConfirmar) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Las nuevas contraseñas no coinciden."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    if ($passwordActual === $passwordNueva) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "La nueva contraseña debe ser diferente a la actual."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // =========================================================
    // BUSCAR CONTRASEÑA ACTUAL
    // =========================================================

    $consulta = $conexion->prepare("
        SELECT password
        FROM usuarios
        WHERE id_usuario = :id_usuario
          AND rol = 'cliente'
        LIMIT 1
    ");

    $consulta->execute([
        ":id_usuario" => $idUsuario
    ]);

    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);


    if (!$usuario) {

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "message" => "No se encontró el usuario."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // =========================================================
    // COMPROBAR CONTRASEÑA ACTUAL
    // =========================================================

    if (!password_verify($passwordActual, $usuario["password"])) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "La contraseña actual es incorrecta."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // =========================================================
    // CREAR NUEVO HASH
    // =========================================================

    $nuevoHash = password_hash(
        $passwordNueva,
        PASSWORD_DEFAULT
    );

    if ($nuevoHash === false) {
        throw new Exception(
            "No fue posible generar la nueva contraseña."
        );
    }


    // =========================================================
    // ACTUALIZAR CONTRASEÑA
    // =========================================================

    $actualizar = $conexion->prepare("
        UPDATE usuarios
        SET password = :password
        WHERE id_usuario = :id_usuario
          AND rol = 'cliente'
    ");

    $actualizar->execute([
        ":password" => $nuevoHash,
        ":id_usuario" => $idUsuario
    ]);


    // =========================================================
    // REGENERAR ID DE SESIÓN
    // =========================================================

    session_regenerate_id(true);


    // =========================================================
    // RESPUESTA
    // =========================================================

    echo json_encode([
        "success" => true,
        "message" => "Contraseña actualizada correctamente."
    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Error interno del servidor."
    ], JSON_UNESCAPED_UNICODE);
}