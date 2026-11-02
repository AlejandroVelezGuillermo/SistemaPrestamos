<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../controllers/ClienteController.php";


try {

    if (!isset($_SESSION["usuario"])) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "No has iniciado sesión"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $rol =
        $_SESSION["usuario"]["rol"];


    if (
        $rol !== "administrador" &&
        $rol !== "gerente"
    ) {

        http_response_code(403);

        echo json_encode([
            "success" => false,
            "message" => "No tienes permisos para gestionar clientes"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $controller =
        new ClienteController();


    // =========================================================
    // GET
    // =========================================================

    if ($_SERVER["REQUEST_METHOD"] === "GET") {

        if (isset($_GET["id"])) {

            $resultado =
                $controller->obtener(
                    $_GET["id"]
                );


            if (!$resultado["success"]) {
                http_response_code(404);
            }


            echo json_encode(
                $resultado,
                JSON_UNESCAPED_UNICODE
            );

            exit;
        }


        $clientes =
            $controller->listar();


        echo json_encode([
            "success" => true,
            "total" => count($clientes),
            "data" => $clientes
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // =========================================================
    // POST
    // =========================================================

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $datos =
            json_decode(
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


        $resultado =
            $controller->crear(
                $datos
            );


        if (!$resultado["success"]) {
            http_response_code(400);
        }


        echo json_encode(
            $resultado,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    // =========================================================
    // PUT = EDITAR CLIENTE
    // =========================================================

    if ($_SERVER["REQUEST_METHOD"] === "PUT") {

        if (!isset($_GET["id"])) {

            http_response_code(400);

            echo json_encode([
                "success" => false,
                "message" => "Debes indicar el cliente"
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }


        $datos =
            json_decode(
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


        $resultado =
            $controller->actualizar(
                $_GET["id"],
                $datos
            );


        if (!$resultado["success"]) {
            http_response_code(400);
        }


        echo json_encode(
            $resultado,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    http_response_code(405);


    echo json_encode([
        "success" => false,
        "message" => "Método no permitido"
    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    http_response_code(500);


    echo json_encode([
        "success" => false,
        "message" => "Error en la base de datos"
    ], JSON_UNESCAPED_UNICODE);


} catch (Exception $e) {

    http_response_code(500);


    echo json_encode([
        "success" => false,
        "message" => "Error interno del servidor"
    ], JSON_UNESCAPED_UNICODE);
}