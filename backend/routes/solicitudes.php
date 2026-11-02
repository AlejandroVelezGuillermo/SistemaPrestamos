<?php

session_start();

header(
    "Content-Type: application/json; charset=UTF-8"
);

require_once __DIR__ . "/../controllers/SolicitudController.php";
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


    $controller =
        new SolicitudController();

    $prestamoController =
        new PrestamoController();


    // =========================================================
    // GET
    // =========================================================

    if ($_SERVER["REQUEST_METHOD"] === "GET") {


        // -----------------------------------------------------
        // TIPOS DE PRÉSTAMO
        // -----------------------------------------------------

        if (isset($_GET["tipos"])) {

            $resultado =
                $controller->listarTipos();


            echo json_encode(
                $resultado,
                JSON_UNESCAPED_UNICODE
            );

            exit;
        }


        // -----------------------------------------------------
        // OBTENER SOLICITUD
        // -----------------------------------------------------

        if (isset($_GET["id"])) {

            if (
                $rol !== "administrador" &&
                $rol !== "gerente"
            ) {

                http_response_code(403);

                echo json_encode([
                    "success" => false,
                    "message" => "No tienes permisos para consultar esta solicitud"
                ], JSON_UNESCAPED_UNICODE);

                exit;
            }


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
        // LISTAR TODAS
        // -----------------------------------------------------

        if (
            $rol !== "administrador" &&
            $rol !== "gerente"
        ) {

            http_response_code(403);

            echo json_encode([
                "success" => false,
                "message" => "No tienes permisos para consultar todas las solicitudes"
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }


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


        if (
            $rol !== "administrador" &&
            $rol !== "gerente"
        ) {

            http_response_code(403);

            echo json_encode([
                "success" => false,
                "message" => "No tienes permisos para realizar esta acción"
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }


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
                    $datos["accion"] ?? "crear"
                )
            );


        // =====================================================
        // CREAR SOLICITUD
        // =====================================================

        if ($accion === "crear") {

            $resultado =
                $controller->crear($datos);


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
        // VALIDAR SOLICITUD Y USUARIO
        // =====================================================

        $idSolicitud =
            $datos["id_solicitud"] ?? null;


        if (
            !$idSolicitud ||
            !is_numeric($idSolicitud)
        ) {

            http_response_code(400);

            echo json_encode([
                "success" => false,
                "message" => "Solicitud inválida"
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }


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


        $observaciones =
            isset($datos["observaciones"])
                ? trim(
                    (string) $datos["observaciones"]
                )
                : null;


        if ($observaciones === "") {

            $observaciones = null;
        }


        // =====================================================
        // PONER EN REVISIÓN
        // =====================================================

        if ($accion === "poner_en_revision") {

            $resultado =
                $controller->ponerEnRevision(
                    (int) $idSolicitud,
                    (int) $idUsuarioSesion,
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


        // =====================================================
        // APROBAR Y CREAR PRÉSTAMO
        // =====================================================

        if ($accion === "aprobar") {

            $resultado =
                $prestamoController
                    ->crearDesdeSolicitud(
                        (int) $idSolicitud,
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
        // RECHAZAR
        // =====================================================

        if ($accion === "rechazar") {

            $resultado =
                $controller->rechazar(
                    (int) $idSolicitud,
                    (int) $idUsuarioSesion,
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