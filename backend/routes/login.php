<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../controllers/AuthController.php";


try {

    // =========================================================
    // VERIFICAR MÉTODO
    // =========================================================

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {

        http_response_code(405);

        echo json_encode([
            "success" => false,
            "message" => "Método no permitido"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // =========================================================
    // OBTENER DATOS
    // =========================================================

    $datos = json_decode(
        file_get_contents("php://input"),
        true
    );


    if (!$datos) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Datos inválidos"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $correo = trim(
        $datos["correo"] ?? ""
    );

    $password =
        $datos["password"] ?? "";


    // =========================================================
    // VALIDAR CAMPOS
    // =========================================================

    if ($correo === "" || $password === "") {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "El correo y la contraseña son obligatorios"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // =========================================================
    // AUTENTICAR
    // =========================================================

    $controller = new AuthController();

    $resultado = $controller->login(
        $correo,
        $password
    );


    // =========================================================
    // LOGIN INCORRECTO
    // =========================================================

    if (!$resultado["success"]) {

        http_response_code(401);

        echo json_encode(
            $resultado,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    // =========================================================
    // CREAR SESIÓN
    // =========================================================

    $usuario = $resultado["usuario"];


    session_regenerate_id(true);


    $_SESSION["usuario"] = [
        "id_usuario" => $usuario["id_usuario"],
        "nombre" => $usuario["nombre"],
        "apellido_paterno" => $usuario["apellido_paterno"],
        "apellido_materno" => $usuario["apellido_materno"],
        "correo" => $usuario["correo"],
        "telefono" => $usuario["telefono"],
        "rol" => $usuario["rol"],
        "estatus" => $usuario["estatus"]
    ];


    // =========================================================
    // RESPUESTA
    // =========================================================

    echo json_encode([

        "success" => true,

        "message" => "Inicio de sesión correcto",

        "usuario" => $_SESSION["usuario"]

    ], JSON_UNESCAPED_UNICODE);


} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([

        "success" => false,

        "message" => "Error interno del servidor"

    ], JSON_UNESCAPED_UNICODE);
}