<?php

require_once __DIR__ . "/../config/database.php";

class Solicitud
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }


    // =========================================================
    // LISTAR SOLICITUDES
    // =========================================================

    public function obtenerTodas()
    {
        $sql = "SELECT
                    s.id_solicitud,
                    s.id_usuario,
                    s.id_tipo_prestamo,
                    s.id_estado_solicitud,
                    s.monto_solicitado,
                    s.plazo_meses,
                    s.frecuencia_pago,
                    s.motivo,
                    s.fecha_solicitud,
                    s.fecha_revision,
                    s.usuario_revisor,
                    s.observaciones_revision,
                    s.observaciones,
                    s.estatus,

                    u.nombre,
                    u.apellido_paterno,
                    u.apellido_materno,
                    u.correo,
                    u.telefono,

                    tp.nombre AS tipo_prestamo,

                    es.nombre AS estado_solicitud

                FROM solicitudes s

                INNER JOIN usuarios u
                    ON s.id_usuario = u.id_usuario

                INNER JOIN tipos_prestamo tp
                    ON s.id_tipo_prestamo = tp.id_tipo_prestamo

                INNER JOIN estados_solicitud es
                    ON s.id_estado_solicitud = es.id_estado_solicitud

                WHERE u.rol = 'cliente'

                ORDER BY s.id_solicitud DESC";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute();

        return $consulta->fetchAll();
    }


    // =========================================================
    // OBTENER SOLICITUD POR ID
    // =========================================================

    public function obtenerPorId($idSolicitud)
    {
        $sql = "SELECT
                    s.id_solicitud,
                    s.id_usuario,
                    s.id_tipo_prestamo,
                    s.id_estado_solicitud,
                    s.monto_solicitado,
                    s.plazo_meses,
                    s.frecuencia_pago,
                    s.motivo,
                    s.fecha_solicitud,
                    s.fecha_revision,
                    s.usuario_revisor,
                    s.observaciones_revision,
                    s.observaciones,
                    s.estatus,

                    u.nombre,
                    u.apellido_paterno,
                    u.apellido_materno,
                    u.correo,
                    u.telefono,

                    tp.nombre AS tipo_prestamo,
                    tp.monto_minimo,
                    tp.monto_maximo,
                    tp.plazo_minimo,
                    tp.plazo_maximo,

                    es.nombre AS estado_solicitud,

                    CONCAT_WS(
                        ' ',
                        ur.nombre,
                        ur.apellido_paterno,
                        ur.apellido_materno
                    ) AS nombre_revisor

                FROM solicitudes s

                INNER JOIN usuarios u
                    ON s.id_usuario = u.id_usuario

                INNER JOIN tipos_prestamo tp
                    ON s.id_tipo_prestamo = tp.id_tipo_prestamo

                INNER JOIN estados_solicitud es
                    ON s.id_estado_solicitud = es.id_estado_solicitud

                LEFT JOIN usuarios ur
                    ON s.usuario_revisor = ur.id_usuario

                WHERE s.id_solicitud = :id_solicitud

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_solicitud" => $idSolicitud
        ]);

        return $consulta->fetch();
    }


    // =========================================================
    // OBTENER TIPOS DE PRÉSTAMO ACTIVOS
    // =========================================================

    public function obtenerTiposPrestamo()
    {
        $sql = "SELECT
                    id_tipo_prestamo,
                    nombre,
                    descripcion,
                    monto_minimo,
                    monto_maximo,
                    plazo_minimo,
                    plazo_maximo
                FROM tipos_prestamo
                WHERE estatus = 'activo'
                ORDER BY nombre ASC";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute();

        return $consulta->fetchAll();
    }


    // =========================================================
    // OBTENER TIPO DE PRÉSTAMO POR ID
    // =========================================================

    public function obtenerTipoPrestamoPorId($idTipoPrestamo)
    {
        $sql = "SELECT
                    id_tipo_prestamo,
                    nombre,
                    monto_minimo,
                    monto_maximo,
                    plazo_minimo,
                    plazo_maximo,
                    estatus
                FROM tipos_prestamo
                WHERE id_tipo_prestamo = :id_tipo_prestamo
                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_tipo_prestamo" => $idTipoPrestamo
        ]);

        return $consulta->fetch();
    }


    // =========================================================
    // OBTENER ESTADO PENDIENTE
    // =========================================================

    public function obtenerEstadoPendiente()
    {
        $sql = "SELECT
                    id_estado_solicitud
                FROM estados_solicitud
                WHERE nombre = 'pendiente'
                AND estatus = 'activo'
                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute();

        return $consulta->fetch();
    }


    // =========================================================
    // OBTENER ESTADO POR NOMBRE
    // =========================================================

    public function obtenerEstadoPorNombre($nombre)
    {
        $sql = "SELECT
                    id_estado_solicitud,
                    nombre,
                    descripcion,
                    estatus
                FROM estados_solicitud
                WHERE nombre = :nombre
                AND estatus = 'activo'
                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":nombre" => $nombre
        ]);

        return $consulta->fetch();
    }


    // =========================================================
    // CREAR SOLICITUD
    // =========================================================

    public function crear($datos)
    {
        $sql = "INSERT INTO solicitudes
                (
                    id_usuario,
                    id_tipo_prestamo,
                    id_estado_solicitud,
                    monto_solicitado,
                    plazo_meses,
                    frecuencia_pago,
                    motivo,
                    observaciones,
                    estatus
                )
                VALUES
                (
                    :id_usuario,
                    :id_tipo_prestamo,
                    :id_estado_solicitud,
                    :monto_solicitado,
                    :plazo_meses,
                    :frecuencia_pago,
                    :motivo,
                    :observaciones,
                    'activo'
                )";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([

            ":id_usuario" =>
                $datos["id_usuario"],

            ":id_tipo_prestamo" =>
                $datos["id_tipo_prestamo"],

            ":id_estado_solicitud" =>
                $datos["id_estado_solicitud"],

            ":monto_solicitado" =>
                $datos["monto_solicitado"],

            ":plazo_meses" =>
                $datos["plazo_meses"],

            ":frecuencia_pago" =>
                $datos["frecuencia_pago"],

            ":motivo" =>
                $datos["motivo"],

            ":observaciones" =>
                $datos["observaciones"]
        ]);

        return $this->conexion->lastInsertId();
    }


    // =========================================================
    // CAMBIAR ESTADO DE UNA SOLICITUD
    // =========================================================

    public function cambiarEstado(
        $idSolicitud,
        $idEstadoSolicitud,
        $idUsuarioRevisor,
        $observacionesRevision = null
    )
    {
        $sql = "UPDATE solicitudes

                SET
                    id_estado_solicitud = :id_estado_solicitud,
                    fecha_revision = NOW(),
                    usuario_revisor = :usuario_revisor,
                    observaciones_revision = :observaciones_revision

                WHERE id_solicitud = :id_solicitud

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        return $consulta->execute([

            ":id_estado_solicitud" =>
                $idEstadoSolicitud,

            ":usuario_revisor" =>
                $idUsuarioRevisor,

            ":observaciones_revision" =>
                $observacionesRevision,

            ":id_solicitud" =>
                $idSolicitud
        ]);
    }
}