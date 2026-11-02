<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../controllers/DocumentoClienteController.php";


try {

    // =========================================================
    // SESIÓN
    // =========================================================

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
            "message" => "No tienes permisos para gestionar documentos"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $controller =
        new DocumentoClienteController();


    // =========================================================
    // GET = LISTAR
    // documentos_clientes.php?id_usuario=2
    // =========================================================

    if ($_SERVER["REQUEST_METHOD"] === "GET") {

        $idUsuario =
            $_GET["id_usuario"] ?? null;


        $resultado =
            $controller->listar(
                $idUsuario
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
    // POST = SUBIR DOCUMENTO
    // =========================================================

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $idUsuario =
            $_POST["id_usuario"] ?? null;

        $tipoDocumento =
            $_POST["tipo_documento"] ?? "";

        $observaciones =
            trim(
                $_POST["observaciones"] ?? ""
            );

        $archivo =
            $_FILES["archivo"] ?? null;


        $resultado =
            $controller->subir(
                $idUsuario,
                $tipoDocumento,
                $archivo,
                $observaciones
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
    // PUT = CAMBIAR ESTATUS
    // documentos_clientes.php?id_documento=1
    // =========================================================

    if ($_SERVER["REQUEST_METHOD"] === "PUT") {

        $idDocumento =
            $_GET["id_documento"] ?? null;


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


        $estatus =
            $datos["estatus"] ?? "";

        $observaciones =
            trim(
                $datos["observaciones"] ?? ""
            );


        $resultado =
            $controller->cambiarEstatus(
                $idDocumento,
                $estatus,
                $observaciones
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


} catch (Throwable $e) {

    http_response_code(500);


    echo json_encode([
        "success" => false,
        "message" => "Error interno del servidor"
    ], JSON_UNESCAPED_UNICODE);
}