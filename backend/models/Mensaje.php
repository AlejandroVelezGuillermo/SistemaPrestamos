<?php

require_once __DIR__ . "/../config/database.php";


class Mensaje
{
    private $conexion;


    public function __construct()
    {
        global $conexion;

        $this->conexion = $conexion;
    }


    // ============================================================
    // OBTENER CLIENTES
    // ============================================================

    public function obtenerClientes()
    {
        $sql = "
            SELECT
                id_usuario,
                nombre,
                apellido_paterno,
                apellido_materno,
                correo,
                telefono,
                rol,
                estatus,

                CONCAT(
                    nombre,
                    ' ',
                    apellido_paterno,
                    IF(
                        apellido_materno IS NOT NULL
                        AND apellido_materno != '',
                        CONCAT(' ', apellido_materno),
                        ''
                    )
                ) AS nombre_completo

            FROM usuarios

            WHERE rol = 'cliente'

            ORDER BY
                nombre ASC,
                apellido_paterno ASC
        ";


        $consulta =
            $this->conexion->prepare($sql);


        $consulta->execute();


        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }


    // ============================================================
    // ENVIAR MENSAJE
    // ============================================================

    public function enviarMensaje(
        $idCliente,
        $enviadoPor,
        $asunto,
        $mensaje
    ) {

        $sql = "
            INSERT INTO mensajes
            (
                id_cliente,
                enviado_por,
                asunto,
                mensaje,
                leido,
                fecha_envio,
                fecha_lectura,
                estatus
            )
            VALUES
            (
                :id_cliente,
                :enviado_por,
                :asunto,
                :mensaje,
                0,
                NOW(),
                NULL,
                'activo'
            )
        ";


        $consulta =
            $this->conexion->prepare($sql);


        return $consulta->execute([

            ':id_cliente' =>
                $idCliente,

            ':enviado_por' =>
                $enviadoPor,

            ':asunto' =>
                $asunto,

            ':mensaje' =>
                $mensaje

        ]);
    }


    // ============================================================
    // OBTENER MENSAJES ENVIADOS
    // ADMINISTRADOR / GERENTE
    // ============================================================

    public function obtenerMensajesEnviados()
    {

        $sql = "
            SELECT

                m.id_mensaje,
                m.id_cliente,
                m.enviado_por,
                m.asunto,
                m.mensaje,
                m.leido,
                m.fecha_envio,
                m.fecha_lectura,
                m.estatus,
                


                (
                    SELECT COUNT(*)

                    FROM respuestas_mensajes r

                    WHERE r.id_mensaje = m.id_mensaje

                    AND r.id_usuario = m.id_cliente

                    AND r.leido = 0

                    AND r.estatus = 'activo'
                ) AS respuestas_no_leidas,


                CONCAT(
                    c.nombre,
                    ' ',
                    c.apellido_paterno,
                    IF(
                        c.apellido_materno IS NOT NULL
                        AND c.apellido_materno != '',
                        CONCAT(
                            ' ',
                            c.apellido_materno
                        ),
                        ''
                    )
                ) AS nombre_cliente,


                c.correo AS correo_cliente,


                c.telefono AS telefono_cliente,


                CONCAT(
                    u.nombre,
                    ' ',
                    u.apellido_paterno,
                    IF(
                        u.apellido_materno IS NOT NULL
                        AND u.apellido_materno != '',
                        CONCAT(
                            ' ',
                            u.apellido_materno
                        ),
                        ''
                    )
                ) AS nombre_emisor,


                u.rol AS rol_emisor


            FROM mensajes m


            INNER JOIN usuarios c

                ON c.id_usuario =
                    m.id_cliente


            INNER JOIN usuarios u

                ON u.id_usuario =
                    m.enviado_por


            WHERE m.estatus = 'activo'


            ORDER BY

                m.fecha_envio DESC,

                m.id_mensaje DESC
        ";


        $consulta =
            $this->conexion->prepare($sql);


        $consulta->execute();


        return $consulta->fetchAll(
            PDO::FETCH_ASSOC
        );
    }


    // ============================================================
    // OBTENER MENSAJES DEL CLIENTE
    // ============================================================

    public function obtenerMensajesCliente(
        $idCliente
    ) {

        $sql = "
        SELECT

            m.id_mensaje,
            m.id_cliente,
            m.enviado_por,
            m.asunto,
            m.mensaje,
            m.leido,
            m.fecha_envio,
            m.fecha_lectura,
            m.estatus,


            (
                SELECT COUNT(*)

                FROM respuestas_mensajes r

                WHERE r.id_mensaje = m.id_mensaje

                AND r.id_usuario != m.id_cliente

                AND r.leido = 0

                AND r.estatus = 'activo'

            ) AS respuestas_no_leidas,


            CONCAT(
                u.nombre,
                ' ',
                u.apellido_paterno,
                IF(
                    u.apellido_materno IS NOT NULL
                    AND u.apellido_materno != '',
                    CONCAT(
                        ' ',
                        u.apellido_materno
                    ),
                    ''
                )
            ) AS enviado_por_nombre,


            CONCAT(
                u.nombre,
                ' ',
                u.apellido_paterno,
                IF(
                    u.apellido_materno IS NOT NULL
                    AND u.apellido_materno != '',
                    CONCAT(
                        ' ',
                        u.apellido_materno
                    ),
                    ''
                )
            ) AS remitente,


            u.rol AS rol_remitente


        FROM mensajes m


        INNER JOIN usuarios u

            ON u.id_usuario =
                m.enviado_por


        WHERE m.id_cliente =
            :id_cliente


        AND m.estatus = 'activo'


        ORDER BY

            m.fecha_envio DESC,

            m.id_mensaje DESC
    ";


        $consulta =
            $this->conexion->prepare($sql);


        $consulta->execute([

            ':id_cliente' =>
                $idCliente

        ]);


        return $consulta->fetchAll(
            PDO::FETCH_ASSOC
        );
    }


    // ============================================================
    // OBTENER UN MENSAJE DEL CLIENTE
    // ============================================================

    public function obtenerMensajeCliente(
        $idMensaje,
        $idCliente
    ) {

        $sql = "
            SELECT

                m.id_mensaje,
                m.id_cliente,
                m.enviado_por,
                m.asunto,
                m.mensaje,
                m.leido,
                m.fecha_envio,
                m.fecha_lectura,
                m.estatus,


                CONCAT(
                    u.nombre,
                    ' ',
                    u.apellido_paterno,
                    IF(
                        u.apellido_materno IS NOT NULL
                        AND u.apellido_materno != '',
                        CONCAT(
                            ' ',
                            u.apellido_materno
                        ),
                        ''
                    )
                ) AS enviado_por_nombre,


                u.rol AS rol_remitente


            FROM mensajes m


            INNER JOIN usuarios u

                ON u.id_usuario =
                    m.enviado_por


            WHERE m.id_mensaje =
                :id_mensaje


            AND m.id_cliente =
                :id_cliente


            AND m.estatus = 'activo'


            LIMIT 1
        ";


        $consulta =
            $this->conexion->prepare($sql);


        $consulta->execute([

            ':id_mensaje' =>
                $idMensaje,

            ':id_cliente' =>
                $idCliente

        ]);


        return $consulta->fetch(
            PDO::FETCH_ASSOC
        );
    }


    // ============================================================
    // MARCAR MENSAJE ORIGINAL COMO LEÍDO
    // ============================================================

    public function marcarComoLeido(
        $idMensaje,
        $idCliente
    ) {

        $sql = "
            UPDATE mensajes

            SET
                leido = 1,

                fecha_lectura =
                    COALESCE(
                        fecha_lectura,
                        NOW()
                    )

            WHERE id_mensaje =
                :id_mensaje

            AND id_cliente =
                :id_cliente

            AND estatus = 'activo'
        ";


        $consulta =
            $this->conexion->prepare($sql);


        return $consulta->execute([

            ':id_mensaje' =>
                $idMensaje,

            ':id_cliente' =>
                $idCliente

        ]);
    }


    // ============================================================
    // CONTAR MENSAJES NO LEÍDOS DEL CLIENTE
    // ============================================================

    public function contarNoLeidos(
        $idCliente
    ) {

        $sql = "
            SELECT
                COUNT(*) AS total

            FROM mensajes

            WHERE id_cliente =
                :id_cliente

            AND leido = 0

            AND estatus = 'activo'
        ";


        $consulta =
            $this->conexion->prepare($sql);


        $consulta->execute([

            ':id_cliente' =>
                $idCliente

        ]);


        $resultado =
            $consulta->fetch(
                PDO::FETCH_ASSOC
            );


        return (int) (
            $resultado['total']
            ?? 0
        );
    }


    // ============================================================
    // ELIMINAR MENSAJE
    // ELIMINADO LÓGICO
    // ============================================================

    public function eliminarMensaje(
        $idMensaje
    ) {

        $sql = "
            UPDATE mensajes

            SET estatus =
                'eliminado'

            WHERE id_mensaje =
                :id_mensaje
        ";


        $consulta =
            $this->conexion->prepare($sql);


        return $consulta->execute([

            ':id_mensaje' =>
                $idMensaje

        ]);
    }


    // ============================================================
    // OBTENER MENSAJE POR ID
    // PARA CONVERSACIÓN
    // ============================================================

    public function obtenerMensajePorId(
        $idMensaje
    ) {

        $sql = "
            SELECT

                m.id_mensaje,
                m.id_cliente,
                m.enviado_por,
                m.asunto,
                m.mensaje,
                m.leido,
                m.fecha_envio,
                m.fecha_lectura,
                m.estatus,


                CONCAT(
                    c.nombre,
                    ' ',
                    c.apellido_paterno,
                    IF(
                        c.apellido_materno IS NOT NULL
                        AND c.apellido_materno != '',
                        CONCAT(
                            ' ',
                            c.apellido_materno
                        ),
                        ''
                    )
                ) AS nombre_cliente,


                c.correo AS correo_cliente,


                CONCAT(
                    u.nombre,
                    ' ',
                    u.apellido_paterno,
                    IF(
                        u.apellido_materno IS NOT NULL
                        AND u.apellido_materno != '',
                        CONCAT(
                            ' ',
                            u.apellido_materno
                        ),
                        ''
                    )
                ) AS nombre_emisor,


                u.rol AS rol_emisor


            FROM mensajes m


            INNER JOIN usuarios c

                ON c.id_usuario =
                    m.id_cliente


            INNER JOIN usuarios u

                ON u.id_usuario =
                    m.enviado_por


            WHERE m.id_mensaje =
                :id_mensaje


            AND m.estatus = 'activo'


            LIMIT 1
        ";


        $consulta =
            $this->conexion->prepare($sql);


        $consulta->execute([

            ':id_mensaje' =>
                $idMensaje

        ]);


        return $consulta->fetch(
            PDO::FETCH_ASSOC
        );
    }


    // ============================================================
    // ENVIAR RESPUESTA
    // ============================================================

    public function enviarRespuesta(
        $idMensaje,
        $idUsuario,
        $mensaje
    ) {

        $sql = "
            INSERT INTO respuestas_mensajes
            (
                id_mensaje,
                id_usuario,
                mensaje,
                fecha_envio,
                leido,
                fecha_lectura,
                estatus
            )

            VALUES
            (
                :id_mensaje,
                :id_usuario,
                :mensaje,
                NOW(),
                0,
                NULL,
                'activo'
            )
        ";


        $consulta =
            $this->conexion->prepare($sql);


        return $consulta->execute([

            ':id_mensaje' =>
                $idMensaje,

            ':id_usuario' =>
                $idUsuario,

            ':mensaje' =>
                $mensaje

        ]);
    }


    // ============================================================
    // OBTENER RESPUESTAS DE UNA CONVERSACIÓN
    // ============================================================

    public function obtenerRespuestasMensaje(
        $idMensaje
    ) {

        $sql = "
            SELECT

                r.id_respuesta,
                r.id_mensaje,
                r.id_usuario,
                r.mensaje,
                r.fecha_envio,
                r.leido,
                r.fecha_lectura,
                r.estatus,


                u.nombre,
                u.apellido_paterno,
                u.apellido_materno,
                u.rol,


                CONCAT(
                    u.nombre,
                    ' ',
                    u.apellido_paterno,
                    IF(
                        u.apellido_materno IS NOT NULL
                        AND u.apellido_materno != '',
                        CONCAT(
                            ' ',
                            u.apellido_materno
                        ),
                        ''
                    )
                ) AS nombre_usuario


            FROM respuestas_mensajes r


            INNER JOIN usuarios u

                ON u.id_usuario =
                    r.id_usuario


            WHERE r.id_mensaje =
                :id_mensaje


            AND r.estatus = 'activo'


            ORDER BY

                r.fecha_envio ASC,

                r.id_respuesta ASC
        ";


        $consulta =
            $this->conexion->prepare($sql);


        $consulta->execute([

            ':id_mensaje' =>
                $idMensaje

        ]);


        return $consulta->fetchAll(
            PDO::FETCH_ASSOC
        );
    }


    // ============================================================
    // MARCAR RESPUESTAS COMO LEÍDAS
    //
    // Esta función la utilizaremos en el siguiente paso cuando
    // administrador/gerente abra una respuesta nueva.
    // ============================================================

    public function marcarRespuestasComoLeidas(
        $idMensaje,
        $idUsuarioActual
    ) {

        $sql = "
            UPDATE respuestas_mensajes

            SET

                leido = 1,

                fecha_lectura =
                    COALESCE(
                        fecha_lectura,
                        NOW()
                    )

            WHERE id_mensaje =
                :id_mensaje

            AND id_usuario !=
                :id_usuario

            AND leido = 0

            AND estatus = 'activo'
        ";


        $consulta =
            $this->conexion->prepare($sql);


        return $consulta->execute([

            ':id_mensaje' =>
                $idMensaje,

            ':id_usuario' =>
                $idUsuarioActual

        ]);
    }
}