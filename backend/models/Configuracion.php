<?php

require_once __DIR__ . "/../config/database.php";

class Configuracion
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }


    // =========================================================
    // TIPOS DE PRÉSTAMO
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
                    plazo_maximo,
                    estatus,
                    fecha_creacion

                FROM tipos_prestamo

                ORDER BY id_tipo_prestamo DESC";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute();

        return $consulta->fetchAll();
    }


    public function obtenerTipoPrestamoPorId(
        $idTipoPrestamo
    )
    {
        $sql = "SELECT
                    id_tipo_prestamo,
                    nombre,
                    descripcion,
                    monto_minimo,
                    monto_maximo,
                    plazo_minimo,
                    plazo_maximo,
                    estatus,
                    fecha_creacion

                FROM tipos_prestamo

                WHERE id_tipo_prestamo =
                    :id_tipo_prestamo

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_tipo_prestamo" =>
                $idTipoPrestamo
        ]);

        return $consulta->fetch();
    }


    public function crearTipoPrestamo(
        $datos
    )
    {
        $sql = "INSERT INTO tipos_prestamo
                (
                    nombre,
                    descripcion,
                    monto_minimo,
                    monto_maximo,
                    plazo_minimo,
                    plazo_maximo,
                    estatus
                )
                VALUES
                (
                    :nombre,
                    :descripcion,
                    :monto_minimo,
                    :monto_maximo,
                    :plazo_minimo,
                    :plazo_maximo,
                    :estatus
                )";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([

            ":nombre" =>
                $datos["nombre"],

            ":descripcion" =>
                $datos["descripcion"],

            ":monto_minimo" =>
                $datos["monto_minimo"],

            ":monto_maximo" =>
                $datos["monto_maximo"],

            ":plazo_minimo" =>
                $datos["plazo_minimo"],

            ":plazo_maximo" =>
                $datos["plazo_maximo"],

            ":estatus" =>
                $datos["estatus"]
        ]);

        return $this->conexion->lastInsertId();
    }


    public function actualizarTipoPrestamo(
        $idTipoPrestamo,
        $datos
    )
    {
        $sql = "UPDATE tipos_prestamo

                SET
                    nombre = :nombre,
                    descripcion = :descripcion,
                    monto_minimo = :monto_minimo,
                    monto_maximo = :monto_maximo,
                    plazo_minimo = :plazo_minimo,
                    plazo_maximo = :plazo_maximo,
                    estatus = :estatus

                WHERE id_tipo_prestamo =
                    :id_tipo_prestamo

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        return $consulta->execute([

            ":nombre" =>
                $datos["nombre"],

            ":descripcion" =>
                $datos["descripcion"],

            ":monto_minimo" =>
                $datos["monto_minimo"],

            ":monto_maximo" =>
                $datos["monto_maximo"],

            ":plazo_minimo" =>
                $datos["plazo_minimo"],

            ":plazo_maximo" =>
                $datos["plazo_maximo"],

            ":estatus" =>
                $datos["estatus"],

            ":id_tipo_prestamo" =>
                $idTipoPrestamo
        ]);
    }


    public function cambiarEstatusTipoPrestamo(
        $idTipoPrestamo,
        $estatus
    )
    {
        $sql = "UPDATE tipos_prestamo

                SET estatus = :estatus

                WHERE id_tipo_prestamo =
                    :id_tipo_prestamo

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        return $consulta->execute([

            ":estatus" =>
                $estatus,

            ":id_tipo_prestamo" =>
                $idTipoPrestamo
        ]);
    }


    // =========================================================
    // MÉTODOS DE PAGO
    // =========================================================

    public function obtenerMetodosPago()
    {
        $sql = "SELECT
                    id_metodo_pago,
                    nombre,
                    descripcion,
                    estatus,
                    fecha_creacion

                FROM metodos_pago

                ORDER BY id_metodo_pago ASC";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute();

        return $consulta->fetchAll();
    }


    public function obtenerMetodoPagoPorId(
        $idMetodoPago
    )
    {
        $sql = "SELECT
                    id_metodo_pago,
                    nombre,
                    descripcion,
                    estatus,
                    fecha_creacion

                FROM metodos_pago

                WHERE id_metodo_pago =
                    :id_metodo_pago

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_metodo_pago" =>
                $idMetodoPago
        ]);

        return $consulta->fetch();
    }


    public function crearMetodoPago(
        $datos
    )
    {
        $sql = "INSERT INTO metodos_pago
                (
                    nombre,
                    descripcion,
                    estatus
                )
                VALUES
                (
                    :nombre,
                    :descripcion,
                    :estatus
                )";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([

            ":nombre" =>
                $datos["nombre"],

            ":descripcion" =>
                $datos["descripcion"],

            ":estatus" =>
                $datos["estatus"]
        ]);

        return $this->conexion->lastInsertId();
    }


    public function actualizarMetodoPago(
        $idMetodoPago,
        $datos
    )
    {
        $sql = "UPDATE metodos_pago

                SET
                    nombre = :nombre,
                    descripcion = :descripcion,
                    estatus = :estatus

                WHERE id_metodo_pago =
                    :id_metodo_pago

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        return $consulta->execute([

            ":nombre" =>
                $datos["nombre"],

            ":descripcion" =>
                $datos["descripcion"],

            ":estatus" =>
                $datos["estatus"],

            ":id_metodo_pago" =>
                $idMetodoPago
        ]);
    }


    public function cambiarEstatusMetodoPago(
        $idMetodoPago,
        $estatus
    )
    {
        $sql = "UPDATE metodos_pago

                SET estatus = :estatus

                WHERE id_metodo_pago =
                    :id_metodo_pago

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        return $consulta->execute([

            ":estatus" =>
                $estatus,

            ":id_metodo_pago" =>
                $idMetodoPago
        ]);
    }


    // =========================================================
    // ESTADOS DE PRÉSTAMO
    // SOLO CONSULTA
    // =========================================================

    public function obtenerEstadosPrestamo()
    {
        $sql = "SELECT
                    id_estado_prestamo,
                    nombre,
                    descripcion,
                    estatus

                FROM estados_prestamo

                ORDER BY id_estado_prestamo ASC";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute();

        return $consulta->fetchAll();
    }


    // =========================================================
    // ESTADOS DE SOLICITUD
    // SOLO CONSULTA
    // =========================================================

    public function obtenerEstadosSolicitud()
    {
        $sql = "SELECT
                    id_estado_solicitud,
                    nombre,
                    descripcion,
                    estatus

                FROM estados_solicitud

                ORDER BY id_estado_solicitud ASC";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute();

        return $consulta->fetchAll();
    }
}