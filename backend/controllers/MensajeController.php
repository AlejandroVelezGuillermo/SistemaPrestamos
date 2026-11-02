<?php

require_once __DIR__ . '/../models/Mensaje.php';

class MensajeController
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new Mensaje();
    }


    // ============================================================
    // LISTAR CLIENTES
    // SOLO ADMINISTRADOR / GERENTE
    // ============================================================

    public function obtenerClientes()
    {
        try {

            $clientes =
                $this->modelo->obtenerClientes();

            return [
                'success' => true,
                'data' => $clientes
            ];

        } catch (Exception $e) {

            return [
                'success' => false,
                'message' => 'No se pudieron obtener los clientes.'
            ];
        }
    }


    // ============================================================
    // ENVIAR MENSAJE
    // SOLO ADMINISTRADOR / GERENTE
    // ============================================================

    public function enviarMensaje(
        $idCliente,
        $enviadoPor,
        $asunto,
        $mensaje
    ) {
        try {

            $idCliente = (int) $idCliente;
            $enviadoPor = (int) $enviadoPor;

            $asunto =
                trim((string) $asunto);

            $mensaje =
                trim((string) $mensaje);


            if ($idCliente <= 0) {

                return [
                    'success' => false,
                    'message' => 'Selecciona un cliente válido.'
                ];
            }


            if ($enviadoPor <= 0) {

                return [
                    'success' => false,
                    'message' => 'No se pudo identificar al usuario que envía el mensaje.'
                ];
            }


            if ($asunto === '') {

                return [
                    'success' => false,
                    'message' => 'El asunto es obligatorio.'
                ];
            }


            if (mb_strlen($asunto) > 150) {

                return [
                    'success' => false,
                    'message' => 'El asunto no puede superar los 150 caracteres.'
                ];
            }


            if ($mensaje === '') {

                return [
                    'success' => false,
                    'message' => 'El mensaje es obligatorio.'
                ];
            }


            $resultado =
                $this->modelo->enviarMensaje(
                    $idCliente,
                    $enviadoPor,
                    $asunto,
                    $mensaje
                );


            if (!$resultado) {

                return [
                    'success' => false,
                    'message' => 'No se pudo enviar el mensaje.'
                ];
            }


            return [
                'success' => true,
                'message' => 'Mensaje enviado correctamente.'
            ];

        } catch (PDOException $e) {

            return [
                'success' => false,
                'message' => 'Ocurrió un error al guardar el mensaje.'
            ];

        } catch (Exception $e) {

            return [
                'success' => false,
                'message' => 'Ocurrió un error inesperado.'
            ];
        }
    }


    // ============================================================
    // MENSAJES ENVIADOS
    // ADMINISTRADOR / GERENTE
    // ============================================================

    public function obtenerMensajesEnviados()
    {
        try {

            $mensajes =
                $this->modelo->obtenerMensajesEnviados();

            return [
                'success' => true,
                'data' => $mensajes
            ];

        } catch (Exception $e) {

            return [
                'success' => false,
                'message' => 'No se pudieron obtener los mensajes enviados.'
            ];
        }
    }


    // ============================================================
    // MENSAJES DEL CLIENTE
    // ============================================================

    public function obtenerMensajesCliente($idCliente)
    {
        try {

            $idCliente =
                (int) $idCliente;


            if ($idCliente <= 0) {

                return [
                    'success' => false,
                    'message' => 'Cliente no válido.'
                ];
            }


            $mensajes =
                $this->modelo->obtenerMensajesCliente(
                    $idCliente
                );


            return [
                'success' => true,
                'data' => $mensajes
            ];

        } catch (Exception $e) {

            return [
                'success' => false,
                'message' => 'No se pudieron obtener los mensajes.'
            ];
        }
    }


    // ============================================================
    // OBTENER UN MENSAJE DEL CLIENTE
    // ============================================================

    public function obtenerMensajeCliente(
        $idMensaje,
        $idCliente
    ) {
        try {

            $idMensaje =
                (int) $idMensaje;

            $idCliente =
                (int) $idCliente;


            if (
                $idMensaje <= 0 ||
                $idCliente <= 0
            ) {

                return [
                    'success' => false,
                    'message' => 'Datos no válidos.'
                ];
            }


            $mensaje =
                $this->modelo->obtenerMensajeCliente(
                    $idMensaje,
                    $idCliente
                );


            if (!$mensaje) {

                return [
                    'success' => false,
                    'message' => 'Mensaje no encontrado.'
                ];
            }


            return [
                'success' => true,
                'data' => $mensaje
            ];

        } catch (Exception $e) {

            return [
                'success' => false,
                'message' => 'No se pudo obtener el mensaje.'
            ];
        }
    }


    // ============================================================
    // MARCAR COMO LEÍDO
    // ============================================================

    public function marcarComoLeido(
        $idMensaje,
        $idCliente
    ) {
        try {

            $idMensaje =
                (int) $idMensaje;

            $idCliente =
                (int) $idCliente;


            if (
                $idMensaje <= 0 ||
                $idCliente <= 0
            ) {

                return [
                    'success' => false,
                    'message' => 'Datos no válidos.'
                ];
            }


            $mensaje =
                $this->modelo->obtenerMensajeCliente(
                    $idMensaje,
                    $idCliente
                );


            if (!$mensaje) {

                return [
                    'success' => false,
                    'message' => 'Mensaje no encontrado.'
                ];
            }


            if ((int) $mensaje['leido'] === 1) {

                return [
                    'success' => true,
                    'message' => 'El mensaje ya estaba marcado como leído.'
                ];
            }


            $resultado =
                $this->modelo->marcarComoLeido(
                    $idMensaje,
                    $idCliente
                );


            if (!$resultado) {

                return [
                    'success' => false,
                    'message' => 'No se pudo marcar el mensaje como leído.'
                ];
            }


            return [
                'success' => true,
                'message' => 'Mensaje marcado como leído.'
            ];

        } catch (Exception $e) {

            return [
                'success' => false,
                'message' => 'Ocurrió un error al actualizar el mensaje.'
            ];
        }
    }


    // ============================================================
    // CONTAR MENSAJES NO LEÍDOS
    // ============================================================

    public function contarNoLeidos($idCliente)
    {
        try {

            $idCliente =
                (int) $idCliente;


            if ($idCliente <= 0) {

                return [
                    'success' => false,
                    'message' => 'Cliente no válido.'
                ];
            }


            $total =
                $this->modelo->contarNoLeidos(
                    $idCliente
                );


            return [
                'success' => true,
                'data' => [
                    'total_no_leidos' => $total
                ]
            ];

        } catch (Exception $e) {

            return [
                'success' => false,
                'message' => 'No se pudieron contar los mensajes no leídos.'
            ];
        }
    }


    // ============================================================
    // ELIMINAR MENSAJE
    // SOLO ADMINISTRADOR / GERENTE
    // ============================================================

    public function eliminarMensaje($idMensaje)
    {
        try {

            $idMensaje =
                (int) $idMensaje;


            if ($idMensaje <= 0) {

                return [
                    'success' => false,
                    'message' => 'Mensaje no válido.'
                ];
            }


            $resultado =
                $this->modelo->eliminarMensaje(
                    $idMensaje
                );


            if (!$resultado) {

                return [
                    'success' => false,
                    'message' => 'No se pudo eliminar el mensaje.'
                ];
            }


            return [
                'success' => true,
                'message' => 'Mensaje eliminado correctamente.'
            ];

        } catch (Exception $e) {

            return [
                'success' => false,
                'message' => 'Ocurrió un error al eliminar el mensaje.'
            ];
        }
    }


    // ============================================================
    // OBTENER CONVERSACIÓN
    // CLIENTE / ADMINISTRADOR / GERENTE
    // ============================================================

    public function obtenerConversacion(
        $idMensaje,
        $idUsuario,
        $rol
    ) {
        try {

            $idMensaje =
                (int) $idMensaje;

            $idUsuario =
                (int) $idUsuario;

            $rol =
                strtolower(
                    trim((string) $rol)
                );


            if (
                $idMensaje <= 0 ||
                $idUsuario <= 0
            ) {

                return [
                    'success' => false,
                    'message' => 'Datos no válidos.'
                ];
            }


            $mensaje =
                $this->modelo->obtenerMensajePorId(
                    $idMensaje
                );


            if (!$mensaje) {

                return [
                    'success' => false,
                    'message' => 'La conversación no existe.'
                ];
            }


            // ----------------------------------------------------
            // SI ES CLIENTE, EL MENSAJE DEBE PERTENECERLE
            // ----------------------------------------------------

            if ($rol === 'cliente') {

                if (
                    (int) $mensaje['id_cliente']
                    !== $idUsuario
                ) {

                    return [
                        'success' => false,
                        'message' => 'No tienes permiso para ver esta conversación.'
                    ];
                }
            }


            // ----------------------------------------------------
            // VALIDAR ROLES PERMITIDOS
            // ----------------------------------------------------

            if (
                $rol !== 'cliente' &&
                $rol !== 'administrador' &&
                $rol !== 'gerente'
            ) {

                return [
                    'success' => false,
                    'message' => 'No tienes permiso para ver esta conversación.'
                ];
            }


            $respuestas =
                $this->modelo->obtenerRespuestasMensaje(
                    $idMensaje
                );


            return [
                'success' => true,
                'data' => [
                    'mensaje' => $mensaje,
                    'respuestas' => $respuestas
                ]
            ];

        } catch (Exception $e) {

            return [
                'success' => false,
                'message' => 'No se pudo obtener la conversación.'
            ];
        }
    }


    // ============================================================
    // RESPONDER MENSAJE
    // CLIENTE / ADMINISTRADOR / GERENTE
    // ============================================================

    public function responderMensaje(
        $idMensaje,
        $idUsuario,
        $rol,
        $mensajeRespuesta
    ) {
        try {

            $idMensaje =
                (int) $idMensaje;

            $idUsuario =
                (int) $idUsuario;

            $rol =
                strtolower(
                    trim((string) $rol)
                );

            $mensajeRespuesta =
                trim(
                    (string) $mensajeRespuesta
                );


            if ($idMensaje <= 0) {

                return [
                    'success' => false,
                    'message' => 'Mensaje no válido.'
                ];
            }


            if ($idUsuario <= 0) {

                return [
                    'success' => false,
                    'message' => 'No se pudo identificar al usuario.'
                ];
            }


            if ($mensajeRespuesta === '') {

                return [
                    'success' => false,
                    'message' => 'Escribe una respuesta.'
                ];
            }


            if (mb_strlen($mensajeRespuesta) > 5000) {

                return [
                    'success' => false,
                    'message' => 'La respuesta es demasiado larga.'
                ];
            }


            // ----------------------------------------------------
            // COMPROBAR QUE EL MENSAJE ORIGINAL EXISTE
            // ----------------------------------------------------

            $mensajeOriginal =
                $this->modelo->obtenerMensajePorId(
                    $idMensaje
                );


            if (!$mensajeOriginal) {

                return [
                    'success' => false,
                    'message' => 'La conversación no existe.'
                ];
            }


            // ----------------------------------------------------
            // CLIENTE:
            // SOLO PUEDE RESPONDER SUS PROPIOS MENSAJES
            // ----------------------------------------------------

            if ($rol === 'cliente') {

                if (
                    (int) $mensajeOriginal['id_cliente']
                    !== $idUsuario
                ) {

                    return [
                        'success' => false,
                        'message' => 'No tienes permiso para responder esta conversación.'
                    ];
                }
            }


            // ----------------------------------------------------
            // SOLO ROLES PERMITIDOS
            // ----------------------------------------------------

            if (
                $rol !== 'cliente' &&
                $rol !== 'administrador' &&
                $rol !== 'gerente'
            ) {

                return [
                    'success' => false,
                    'message' => 'No tienes permiso para responder mensajes.'
                ];
            }


            // ----------------------------------------------------
            // GUARDAR RESPUESTA
            // ----------------------------------------------------

            $resultado =
                $this->modelo->enviarRespuesta(
                    $idMensaje,
                    $idUsuario,
                    $mensajeRespuesta
                );


            if (!$resultado) {

                return [
                    'success' => false,
                    'message' => 'No se pudo enviar la respuesta.'
                ];
            }


            return [
                'success' => true,
                'message' => 'Respuesta enviada correctamente.'
            ];

        } catch (PDOException $e) {

            return [
                'success' => false,
                'message' => 'Ocurrió un error al guardar la respuesta.'
            ];

        } catch (Exception $e) {

            return [
                'success' => false,
                'message' => 'Ocurrió un error inesperado.'
            ];
        }
    }
    // ============================================================
    // MARCAR RESPUESTAS COMO LEÍDAS
    // ADMINISTRADOR / GERENTE
    // ============================================================

public function marcarRespuestasComoLeidas(
    $idMensaje,
    $idUsuario,
    $rol
) {
    try {

        $idMensaje =
            (int) $idMensaje;

        $idUsuario =
            (int) $idUsuario;

        $rol =
            strtolower(
                trim(
                    (string) $rol
                )
            );


        if ($idMensaje <= 0) {

            return [
                'success' => false,
                'message' => 'Mensaje no válido.'
            ];
        }


        if ($idUsuario <= 0) {

            return [
                'success' => false,
                'message' => 'No se pudo identificar al usuario.'
            ];
        }


        if (
            $rol !== 'cliente'
            && $rol !== 'administrador'
            && $rol !== 'gerente'
        ) {

            return [
                'success' => false,
                'message' => 'No tienes permiso para realizar esta acción.'
            ];
        }


        $mensaje =
            $this->modelo->obtenerMensajePorId(
                $idMensaje
            );


        if (!$mensaje) {

            return [
                'success' => false,
                'message' => 'La conversación no existe.'
            ];
        }


        // Si es cliente, verificar que la conversación
        // realmente le pertenezca.
        if (
            $rol === 'cliente'
            && (int) $mensaje['id_cliente'] !== $idUsuario
        ) {

            return [
                'success' => false,
                'message' => 'No tienes permiso para acceder a esta conversación.'
            ];
        }


        $resultado =
            $this->modelo->marcarRespuestasComoLeidas(
                $idMensaje,
                $idUsuario
            );


        if (!$resultado) {

            return [
                'success' => false,
                'message' => 'No se pudieron marcar las respuestas como leídas.'
            ];
        }


        return [
            'success' => true,
            'message' => 'Respuestas marcadas como leídas.'
        ];


    } catch (PDOException $e) {

        return [
            'success' => false,
            'message' => 'Ocurrió un error al actualizar las respuestas.'
        ];


    } catch (Exception $e) {

        return [
            'success' => false,
            'message' => 'Ocurrió un error inesperado.'
        ];
    }
}

}