<?php

session_start();

header(
    "Content-Type: application/json; charset=UTF-8"
);

require_once __DIR__ . "/../controllers/PortalClienteController.php";


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
        strtolower(
            trim(
                (string) (
                    $usuarioSesion["rol"] ?? ""
                )
            )
        );


    // =========================================================
    // SOLO CLIENTES
    // =========================================================

    if ($rol !== "cliente") {

        http_response_code(403);

        echo json_encode([
            "success" => false,
            "message" =>
                "Esta sección es exclusiva para clientes"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // =========================================================
    // OBTENER ID DEL CLIENTE DESDE LA SESIÓN
    //
    // IMPORTANTE:
    // El navegador NO decide qué cliente consultar.
    // =========================================================

    $idCliente =
        $usuarioSesion["id_usuario"] ??
        $usuarioSesion["id"] ??
        null;


    if (
        !$idCliente ||
        !is_numeric($idCliente)
    ) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" =>
                "No fue posible identificar al cliente de la sesión"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $idCliente =
        (int) $idCliente;


    $controller =
        new PortalClienteController();


    // =========================================================
    // SOLO GET
    // =========================================================

    if ($_SERVER["REQUEST_METHOD"] !== "GET") {

        http_response_code(405);

        echo json_encode([
            "success" => false,
            "message" =>
                "Método no permitido"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // =========================================================
    // RESUMEN DEL CLIENTE
    //
    // portal_cliente.php?resumen=1
    // =========================================================

    if (isset($_GET["resumen"])) {

        $resultado =
            $controller
                ->obtenerResumen(
                    $idCliente
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
    // LISTAR PRÉSTAMOS DEL CLIENTE
    //
    // portal_cliente.php?prestamos=1
    // =========================================================

    if (isset($_GET["prestamos"])) {

        $resultado =
            $controller
                ->obtenerPrestamos(
                    $idCliente
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
    // DETALLE DE UN PRÉSTAMO
    //
    // portal_cliente.php?prestamo=1&id_prestamo=1
    // =========================================================

    if (isset($_GET["prestamo"])) {

        $idPrestamo =
            $_GET["id_prestamo"] ?? null;


        $resultado =
            $controller
                ->obtenerPrestamo(
                    $idPrestamo,
                    $idCliente
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


    // =========================================================
    // CUOTAS DE UN PRÉSTAMO
    //
    // portal_cliente.php?cuotas=1&id_prestamo=1
    // =========================================================

    if (isset($_GET["cuotas"])) {

        $idPrestamo =
            $_GET["id_prestamo"] ?? null;


        $resultado =
            $controller
                ->obtenerCuotas(
                    $idPrestamo,
                    $idCliente
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


    // =========================================================
    // HISTORIAL DE PAGOS
    //
    // portal_cliente.php?pagos=1&id_prestamo=1
    // =========================================================

    if (isset($_GET["pagos"])) {

        $idPrestamo =
            $_GET["id_prestamo"] ?? null;


        $resultado =
            $controller
                ->obtenerPagos(
                    $idPrestamo,
                    $idCliente
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


    // =========================================================
    // INFORMACIÓN COMPLETA DEL PRÉSTAMO
    //
    // portal_cliente.php?completo=1&id_prestamo=1
    // =========================================================

    if (isset($_GET["completo"])) {

        $idPrestamo =
            $_GET["id_prestamo"] ?? null;


        $resultado =
            $controller
                ->obtenerPrestamoCompleto(
                    $idPrestamo,
                    $idCliente
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


    // =========================================================
    // SIN ACCIÓN
    // =========================================================

    echo json_encode([
        "success" => true,
        "message" =>
            "API del portal del cliente disponible"
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