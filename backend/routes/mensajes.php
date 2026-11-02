<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../controllers/MensajeController.php';

$controller = new MensajeController();


// ============================================================
// VALIDAR SESIÓN
// ============================================================

if (!isset($_SESSION['usuario'])) {

    echo json_encode([
        'success' => false,
        'message' => 'No hay una sesión activa.'
    ]);

    exit;
}


$usuarioSesion = $_SESSION['usuario'];

$idUsuario =
    $usuarioSesion['id_usuario']
    ?? $usuarioSesion['id']
    ?? null;

$rol =
    strtolower(
        trim(
            $usuarioSesion['rol']
            ?? ''
        )
    );


if (!$idUsuario) {

    echo json_encode([
        'success' => false,
        'message' => 'No se pudo identificar al usuario.'
    ]);

    exit;
}


// ============================================================
// GET
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'GET') {


    // --------------------------------------------------------
    // LISTAR CLIENTES
    // ADMINISTRADOR / GERENTE
    // --------------------------------------------------------

    if (isset($_GET['clientes'])) {

        if (
            $rol !== 'administrador'
            && $rol !== 'gerente'
        ) {

            echo json_encode([
                'success' => false,
                'message' => 'No tienes permiso para consultar esta información.'
            ]);

            exit;
        }


        echo json_encode(
            $controller->obtenerClientes()
        );

        exit;
    }


    // --------------------------------------------------------
    // MENSAJES ENVIADOS
    // ADMINISTRADOR / GERENTE
    // --------------------------------------------------------

    if (isset($_GET['enviados'])) {

        if (
            $rol !== 'administrador'
            && $rol !== 'gerente'
        ) {

            echo json_encode([
                'success' => false,
                'message' => 'No tienes permiso para consultar esta información.'
            ]);

            exit;
        }


        echo json_encode(
            $controller->obtenerMensajesEnviados()
        );

        exit;
    }


    // --------------------------------------------------------
    // MENSAJES DEL CLIENTE
    // SOLO CLIENTE
    // --------------------------------------------------------

    if (isset($_GET['mis_mensajes'])) {

        if ($rol !== 'cliente') {

            echo json_encode([
                'success' => false,
                'message' => 'Esta opción es exclusiva para clientes.'
            ]);

            exit;
        }


        echo json_encode(
            $controller->obtenerMensajesCliente(
                $idUsuario
            )
        );

        exit;
    }


    // --------------------------------------------------------
    // OBTENER CONVERSACIÓN
    // CLIENTE / ADMINISTRADOR / GERENTE
    // --------------------------------------------------------

    if (isset($_GET['conversacion'])) {

        if (
            $rol !== 'cliente'
            && $rol !== 'administrador'
            && $rol !== 'gerente'
        ) {

            echo json_encode([
                'success' => false,
                'message' => 'No tienes permiso para ver conversaciones.'
            ]);

            exit;
        }


        $idMensaje =
            $_GET['id_mensaje']
            ?? 0;


        echo json_encode(
            $controller->obtenerConversacion(
                $idMensaje,
                $idUsuario,
                $rol
            )
        );

        exit;
    }


    // --------------------------------------------------------
    // OBTENER UN MENSAJE DEL CLIENTE
    // SOLO CLIENTE
    // --------------------------------------------------------

    if (isset($_GET['mensaje'])) {

        if ($rol !== 'cliente') {

            echo json_encode([
                'success' => false,
                'message' => 'Esta opción es exclusiva para clientes.'
            ]);

            exit;
        }


        $idMensaje =
            $_GET['id_mensaje']
            ?? 0;


        echo json_encode(
            $controller->obtenerMensajeCliente(
                $idMensaje,
                $idUsuario
            )
        );

        exit;
    }


    // --------------------------------------------------------
    // CONTAR NO LEÍDOS
    // SOLO CLIENTE
    // --------------------------------------------------------

    if (isset($_GET['no_leidos'])) {

        if ($rol !== 'cliente') {

            echo json_encode([
                'success' => false,
                'message' => 'Esta opción es exclusiva para clientes.'
            ]);

            exit;
        }


        echo json_encode(
            $controller->contarNoLeidos(
                $idUsuario
            )
        );

        exit;
    }


    echo json_encode([
        'success' => false,
        'message' => 'Acción GET no válida.'
    ]);

    exit;
}


// ============================================================
// POST
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $datos =
        json_decode(
            file_get_contents('php://input'),
            true
        );


    if (!is_array($datos)) {

        $datos = $_POST;
    }


    $accion =
        $datos['accion']
        ?? '';


    // --------------------------------------------------------
    // ENVIAR MENSAJE
    // ADMINISTRADOR / GERENTE
    // --------------------------------------------------------

    if ($accion === 'enviar') {

        if (
            $rol !== 'administrador'
            && $rol !== 'gerente'
        ) {

            echo json_encode([
                'success' => false,
                'message' => 'No tienes permiso para enviar mensajes.'
            ]);

            exit;
        }


        $idCliente =
            $datos['id_cliente']
            ?? 0;

        $asunto =
            $datos['asunto']
            ?? '';

        $mensaje =
            $datos['mensaje']
            ?? '';


        echo json_encode(
            $controller->enviarMensaje(
                $idCliente,
                $idUsuario,
                $asunto,
                $mensaje
            )
        );

        exit;
    }


    // --------------------------------------------------------
    // RESPONDER MENSAJE
    // CLIENTE / ADMINISTRADOR / GERENTE
    // --------------------------------------------------------

    if ($accion === 'responder') {

        if (
            $rol !== 'cliente'
            && $rol !== 'administrador'
            && $rol !== 'gerente'
        ) {

            echo json_encode([
                'success' => false,
                'message' => 'No tienes permiso para responder mensajes.'
            ]);

            exit;
        }


        $idMensaje =
            $datos['id_mensaje']
            ?? 0;

        $respuestaMensaje =
            $datos['mensaje']
            ?? '';


        echo json_encode(
            $controller->responderMensaje(
                $idMensaje,
                $idUsuario,
                $rol,
                $respuestaMensaje
            )
        );

        exit;
    }


    // --------------------------------------------------------
// MARCAR RESPUESTAS COMO LEÍDAS
// CLIENTE / ADMINISTRADOR / GERENTE
// --------------------------------------------------------

    if ($accion === 'marcar_respuestas_leidas') {

        if (
            $rol !== 'cliente'
            && $rol !== 'administrador'
            && $rol !== 'gerente'
        ) {

            echo json_encode([
                'success' => false,
                'message' => 'No tienes permiso para realizar esta acción.'
            ]);

            exit;
        }


        $idMensaje =
            $datos['id_mensaje']
            ?? 0;


        echo json_encode(
            $controller->marcarRespuestasComoLeidas(
                $idMensaje,
                $idUsuario,
                $rol
            )
        );

        exit;
    }


    // --------------------------------------------------------
    // MARCAR MENSAJE ORIGINAL COMO LEÍDO
    // SOLO CLIENTE
    // --------------------------------------------------------

    if ($accion === 'marcar_leido') {

        if ($rol !== 'cliente') {

            echo json_encode([
                'success' => false,
                'message' => 'Esta opción es exclusiva para clientes.'
            ]);

            exit;
        }


        $idMensaje =
            $datos['id_mensaje']
            ?? 0;


        echo json_encode(
            $controller->marcarComoLeido(
                $idMensaje,
                $idUsuario
            )
        );

        exit;
    }


    // --------------------------------------------------------
    // ELIMINAR MENSAJE
    // ADMINISTRADOR / GERENTE
    // --------------------------------------------------------

    if ($accion === 'eliminar') {

        if (
            $rol !== 'administrador'
            && $rol !== 'gerente'
        ) {

            echo json_encode([
                'success' => false,
                'message' => 'No tienes permiso para eliminar mensajes.'
            ]);

            exit;
        }


        $idMensaje =
            $datos['id_mensaje']
            ?? 0;


        echo json_encode(
            $controller->eliminarMensaje(
                $idMensaje
            )
        );

        exit;
    }


    echo json_encode([
        'success' => false,
        'message' => 'Acción POST no válida.'
    ]);

    exit;
}


// ============================================================
// MÉTODO NO PERMITIDO
// ============================================================

http_response_code(405);

echo json_encode([
    'success' => false,
    'message' => 'Método no permitido.'
]);