<?php

session_start();

header(
    "Content-Type: application/json; charset=UTF-8"
);

require_once __DIR__ . "/../controllers/ConfiguracionController.php";


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
    // SOLO ADMINISTRADOR
    // =========================================================

    if ($rol !== "administrador") {

        http_response_code(403);

        echo json_encode([
            "success" => false,
            "message" =>
                "No tienes permisos para administrar la configuración"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $controller =
        new ConfiguracionController();


    // =========================================================
    // GET
    // =========================================================

    if ($_SERVER["REQUEST_METHOD"] === "GET") {


        // -----------------------------------------------------
        // TIPOS DE PRÉSTAMO
        //
        // configuracion.php?tipos_prestamo=1
        // -----------------------------------------------------

        if (isset($_GET["tipos_prestamo"])) {

            if (isset($_GET["id"])) {

                $resultado =
                    $controller
                        ->obtenerTipoPrestamo(
                            $_GET["id"]
                        );

            } else {

                $resultado =
                    $controller
                        ->listarTiposPrestamo();
            }


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
        // MÉTODOS DE PAGO
        //
        // configuracion.php?metodos_pago=1
        // -----------------------------------------------------

        if (isset($_GET["metodos_pago"])) {

            if (isset($_GET["id"])) {

                $resultado =
                    $controller
                        ->obtenerMetodoPago(
                            $_GET["id"]
                        );

            } else {

                $resultado =
                    $controller
                        ->listarMetodosPago();
            }


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
        // ESTADOS DE PRÉSTAMO
        //
        // configuracion.php?estados_prestamo=1
        // -----------------------------------------------------

        if (isset($_GET["estados_prestamo"])) {

            $resultado =
                $controller
                    ->listarEstadosPrestamo();


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
        // ESTADOS DE SOLICITUD
        //
        // configuracion.php?estados_solicitud=1
        // -----------------------------------------------------

        if (isset($_GET["estados_solicitud"])) {

            $resultado =
                $controller
                    ->listarEstadosSolicitud();


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
        // SIN ACCIÓN
        // -----------------------------------------------------

        echo json_encode([
            "success" => true,
            "message" =>
                "API de configuración disponible"
        ], JSON_UNESCAPED_UNICODE);

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
                "message" =>
                    "Datos inválidos"
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }


        $accion =
            strtolower(
                trim(
                    (string) (
                        $datos["accion"] ?? ""
                    )
                )
            );


        // =====================================================
        // CREAR TIPO DE PRÉSTAMO
        // =====================================================

        if ($accion === "crear_tipo_prestamo") {

            $resultado =
                $controller
                    ->crearTipoPrestamo(
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
        // ACTUALIZAR TIPO DE PRÉSTAMO
        // =====================================================

        if ($accion === "actualizar_tipo_prestamo") {

            $resultado =
                $controller
                    ->actualizarTipoPrestamo(
                        $datos["id_tipo_prestamo"] ?? null,
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
        // CAMBIAR ESTATUS TIPO DE PRÉSTAMO
        // =====================================================

        if ($accion === "cambiar_estatus_tipo_prestamo") {

            $resultado =
                $controller
                    ->cambiarEstatusTipoPrestamo(
                        $datos["id_tipo_prestamo"] ?? null,
                        $datos["estatus"] ?? ""
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
        // CREAR MÉTODO DE PAGO
        // =====================================================

        if ($accion === "crear_metodo_pago") {

            $resultado =
                $controller
                    ->crearMetodoPago(
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
        // ACTUALIZAR MÉTODO DE PAGO
        // =====================================================

        if ($accion === "actualizar_metodo_pago") {

            $resultado =
                $controller
                    ->actualizarMetodoPago(
                        $datos["id_metodo_pago"] ?? null,
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
        // CAMBIAR ESTATUS MÉTODO DE PAGO
        // =====================================================

        if ($accion === "cambiar_estatus_metodo_pago") {

            $resultado =
                $controller
                    ->cambiarEstatusMetodoPago(
                        $datos["id_metodo_pago"] ?? null,
                        $datos["estatus"] ?? ""
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
        // ACCIÓN INVÁLIDA
        // =====================================================

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" =>
                "Acción no válida"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // =========================================================
    // MÉTODO NO PERMITIDO
    // =========================================================

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" =>
            "Método no permitido"
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