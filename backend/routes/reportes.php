<?php

session_start();

header(
    "Content-Type: application/json; charset=UTF-8"
);

require_once __DIR__ . "/../controllers/ReporteController.php";


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
                "No tienes permisos para consultar reportes"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $controller =
        new ReporteController();


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
    // RESUMEN GENERAL
    //
    // reportes.php?resumen=1
    // =========================================================

    if (isset($_GET["resumen"])) {

        $resultado =
            $controller
                ->resumenGeneral();


        if (!$resultado["success"]) {

            http_response_code(500);
        }


        echo json_encode(
            $resultado,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    // =========================================================
    // SOLICITUDES POR ESTADO
    //
    // reportes.php?solicitudes=1
    // =========================================================

    if (isset($_GET["solicitudes"])) {

        $resultado =
            $controller
                ->solicitudesPorEstado();


        if (!$resultado["success"]) {

            http_response_code(500);
        }


        echo json_encode(
            $resultado,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    // =========================================================
    // PRÉSTAMOS POR ESTADO
    //
    // reportes.php?prestamos=1
    // =========================================================

    if (isset($_GET["prestamos"])) {

        $resultado =
            $controller
                ->prestamosPorEstado();


        if (!$resultado["success"]) {

            http_response_code(500);
        }


        echo json_encode(
            $resultado,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    // =========================================================
    // ÚLTIMOS PAGOS
    //
    // reportes.php?ultimos_pagos=1
    // reportes.php?ultimos_pagos=1&limite=20
    // =========================================================

    if (isset($_GET["ultimos_pagos"])) {

        $limite =
            $_GET["limite"] ?? 10;


        $resultado =
            $controller
                ->ultimosPagos(
                    $limite
                );


        if (!$resultado["success"]) {

            http_response_code(500);
        }


        echo json_encode(
            $resultado,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    // =========================================================
    // PAGOS POR RANGO DE FECHAS
    //
    // reportes.php?pagos_rango=1
    // &fecha_inicio=2026-08-01
    // &fecha_fin=2026-08-31
    // =========================================================

    if (isset($_GET["pagos_rango"])) {

        $fechaInicio =
            $_GET["fecha_inicio"] ?? "";


        $fechaFin =
            $_GET["fecha_fin"] ?? "";


        $resultado =
            $controller
                ->pagosPorRango(
                    $fechaInicio,
                    $fechaFin
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
    // SIN ACCIÓN
    // =========================================================

    echo json_encode([
        "success" => true,
        "message" =>
            "API de reportes disponible",
        "endpoints" => [
            "resumen",
            "solicitudes",
            "prestamos",
            "ultimos_pagos",
            "pagos_rango"
        ]
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