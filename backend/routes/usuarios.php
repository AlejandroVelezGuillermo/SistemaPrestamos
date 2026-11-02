<?php

session_start();

header(
    "Content-Type: application/json; charset=UTF-8"
);

require_once __DIR__ . "/../controllers/UsuarioController.php";


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
    // SOLO ADMINISTRADOR
    // =========================================================

    if ($rol !== "administrador") {

        http_response_code(403);

        echo json_encode([
            "success" => false,
            "message" =>
                "No tienes permisos para administrar usuarios"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $controller =
        new UsuarioController();


    // =========================================================
    // GET
    // =========================================================

    if ($_SERVER["REQUEST_METHOD"] === "GET") {


        // -----------------------------------------------------
        // OBTENER USUARIO POR ID
        //
        // usuarios.php?id=1
        // -----------------------------------------------------

        if (isset($_GET["id"])) {

            $resultado =
                $controller
                    ->obtener(
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
        // LISTAR USUARIOS
        //
        // usuarios.php
        // -----------------------------------------------------

        $resultado =
            $controller
                ->listar();


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
            strtolower(
                trim(
                    (string) (
                        $datos["accion"] ?? ""
                    )
                )
            );


        // =====================================================
        // CREAR USUARIO
        // =====================================================

        if ($accion === "crear") {

            $resultado =
                $controller
                    ->crear(
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
        // ACTUALIZAR USUARIO
        // =====================================================

        if ($accion === "actualizar") {

            $idUsuario =
                $datos["id_usuario"] ?? null;


            $resultado =
                $controller
                    ->actualizar(
                        $idUsuario,
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
        // CAMBIAR CONTRASEÑA
        // =====================================================

        if ($accion === "cambiar_password") {

            $idUsuario =
                $datos["id_usuario"] ?? null;


            $password =
                $datos["password"] ?? "";


            $resultado =
                $controller
                    ->cambiarPassword(
                        $idUsuario,
                        $password
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
        // CAMBIAR ESTATUS
        // =====================================================

        if ($accion === "cambiar_estatus") {

            $idUsuario =
                $datos["id_usuario"] ?? null;


            $estatus =
                $datos["estatus"] ?? "";


            // Evitar que el administrador se bloquee
            // o desactive a sí mismo desde este módulo.

            if (
                is_numeric($idUsuario) &&
                is_numeric($idUsuarioSesion) &&
                (int) $idUsuario ===
                (int) $idUsuarioSesion &&
                $estatus !== "activo"
            ) {

                http_response_code(400);

                echo json_encode([
                    "success" => false,
                    "message" =>
                        "No puedes desactivar o bloquear tu propia cuenta"
                ], JSON_UNESCAPED_UNICODE);

                exit;
            }


            $resultado =
                $controller
                    ->cambiarEstatus(
                        $idUsuario,
                        $estatus
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