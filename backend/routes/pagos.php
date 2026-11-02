<?php

session_start();

header(
    "Content-Type: application/json; charset=UTF-8"
);

require_once __DIR__ . "/../controllers/PagoController.php";


try {

    // =========================================================
    // VALIDAR SESIÓN
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
            "message" =>
                "No tienes permisos para gestionar pagos"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $controller =
        new PagoController();


    // =========================================================
    // GET
    // =========================================================

    if ($_SERVER["REQUEST_METHOD"] === "GET") {


        // -----------------------------------------------------
        // HISTORIAL DE PAGOS
        //
        // pagos.php?historial=1
        // -----------------------------------------------------

        if (isset($_GET["historial"])) {

            $resultado =
                $controller
                    ->listarHistorial();


            if (!$resultado["success"]) {

                http_response_code(500);
            }


            echo json_encode(
                $resultado,
                JSON_UNESCAPED_UNICODE
            );

            exit;
        }


        // -----------------------------------------------------
        // RESUMEN GENERAL DE PAGOS
        //
        // pagos.php?resumen=1
        // -----------------------------------------------------

        if (isset($_GET["resumen"])) {

            $resultado =
                $controller
                    ->obtenerResumen();


            if (!$resultado["success"]) {

                http_response_code(500);
            }


            echo json_encode(
                $resultado,
                JSON_UNESCAPED_UNICODE
            );

            exit;
        }


        // -----------------------------------------------------
        // OBTENER PAGO POR ID
        //
        // pagos.php?id_pago=1
        // -----------------------------------------------------

        if (isset($_GET["id_pago"])) {

            $resultado =
                $controller
                    ->obtenerPago(
                        $_GET["id_pago"]
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
        // MÉTODOS DE PAGO
        //
        // pagos.php?metodos=1
        // -----------------------------------------------------

        if (isset($_GET["metodos"])) {

            $resultado =
                $controller
                    ->listarMetodosPago();


            echo json_encode(
                $resultado,
                JSON_UNESCAPED_UNICODE
            );

            exit;
        }


        // -----------------------------------------------------
        // CUOTAS DE UN PRÉSTAMO
        //
        // pagos.php?cuotas=1&id_prestamo=1
        // -----------------------------------------------------

        if (
            isset($_GET["cuotas"]) &&
            isset($_GET["id_prestamo"])
        ) {

            $resultado =
                $controller
                    ->listarCuotas(
                        $_GET["id_prestamo"]
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
        //
        // pagos.php?id_prestamo=1
        // -----------------------------------------------------

        if (isset($_GET["id_prestamo"])) {

            $resultado =
                $controller
                    ->obtenerPrestamo(
                        $_GET["id_prestamo"]
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
        // LISTAR PRÉSTAMOS ACTIVOS
        //
        // pagos.php
        // -----------------------------------------------------

        $resultado =
            $controller
                ->listarPrestamosActivos();


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
                "message" =>
                    "No fue posible identificar al usuario"
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }


        // =====================================================
        // REGISTRAR PAGO
        // =====================================================

        if ($accion === "registrar_pago") {

            $resultado =
                $controller
                    ->registrarPago(
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
        // ACCIÓN NO VÁLIDA
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
        "message" =>
            "Error en la base de datos"
    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" =>
            "Error interno del servidor"
    ], JSON_UNESCAPED_UNICODE);
}