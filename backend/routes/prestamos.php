<?php

session_start();

header(
    "Content-Type: application/json; charset=UTF-8"
);

require_once __DIR__ . "/../controllers/PrestamoController.php";


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


    $usuarioSesion =
        $_SESSION["usuario"];

    $rol =
        $usuarioSesion["rol"] ?? null;

    $idUsuarioSesion =
        $usuarioSesion["id_usuario"] ?? null;


    // =========================================================
    // PERMISOS
    // =========================================================

    if (
        $rol !== "administrador" &&
        $rol !== "gerente"
    ) {

        http_response_code(403);

        echo json_encode([
            "success" => false,
            "message" => "No tienes permisos para gestionar préstamos"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $controller =
        new PrestamoController();


    // =========================================================
    // GET
    // =========================================================

    if ($_SERVER["REQUEST_METHOD"] === "GET") {


        // -----------------------------------------------------
        // MÉTODOS DE PAGO
        // prestamos.php?metodos=1
        // -----------------------------------------------------

        if (isset($_GET["metodos"])) {

            $resultado =
                $controller->listarMetodosPago();


            echo json_encode(
                $resultado,
                JSON_UNESCAPED_UNICODE
            );

            exit;
        }


        // -----------------------------------------------------
        // CUOTAS DE UN PRÉSTAMO
        // prestamos.php?cuotas=1&id=1
        // -----------------------------------------------------

        if (
            isset($_GET["cuotas"]) &&
            isset($_GET["id"])
        ) {

            $resultado =
                $controller->listarCuotas(
                    $_GET["id"]
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


        // -----------------------------------------------------
        // DESEMBOLSO DE UN PRÉSTAMO
        // prestamos.php?desembolso=1&id=1
        // -----------------------------------------------------

        if (
            isset($_GET["desembolso"]) &&
            isset($_GET["id"])
        ) {

            $resultado =
                $controller->obtenerDesembolso(
                    $_GET["id"]
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


        // -----------------------------------------------------
        // OBTENER UN PRÉSTAMO
        // prestamos.php?id=1
        // -----------------------------------------------------

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


        // -----------------------------------------------------
        // LISTAR TODOS
        // -----------------------------------------------------

        $resultado =
            $controller->listar();


        echo json_encode(
            $resultado,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    // =========================================================
    // POST
    // =========================================================

    if ($_SERVER["REQUEST_METHOD"] === "POST") {


        // -----------------------------------------------------
        // LEER JSON
        // -----------------------------------------------------

        $contenido =
            file_get_contents("php://input");


        $datos =
            json_decode(
                $contenido,
                true
            );


        if (!is_array($datos)) {

            http_response_code(400);

            echo json_encode([
                "success" => false,
                "message" => "Datos inválidos"
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }


        $accion =
            trim(
                (string) (
                    $datos["accion"] ?? ""
                )
            );


        // -----------------------------------------------------
        // VALIDAR USUARIO
        // -----------------------------------------------------

        if (
            !$idUsuarioSesion ||
            !is_numeric($idUsuarioSesion)
        ) {

            http_response_code(401);

            echo json_encode([
                "success" => false,
                "message" => "No fue posible identificar al usuario"
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }


        // =====================================================
        // DESEMBOLSAR PRÉSTAMO
        // =====================================================

        if ($accion === "desembolsar") {

            $idPrestamo =
                $datos["id_prestamo"] ?? null;


            if (
                !$idPrestamo ||
                !is_numeric($idPrestamo)
            ) {

                http_response_code(400);

                echo json_encode([
                    "success" => false,
                    "message" => "Préstamo inválido"
                ], JSON_UNESCAPED_UNICODE);

                exit;
            }


            $resultado =
                $controller->desembolsar(
                    (int) $idPrestamo,
                    (int) $idUsuarioSesion,
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


        // =====================================================
        // ACCIÓN DESCONOCIDA
        // =====================================================

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Acción no válida"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // =========================================================
    // MÉTODO NO PERMITIDO
    // =========================================================

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