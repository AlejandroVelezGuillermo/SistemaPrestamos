<?php

require_once __DIR__ . "/../config/database.php";

class Prestamo
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }


    // =========================================================
    // LISTAR TODOS LOS PRÉSTAMOS
    // =========================================================

    public function obtenerTodos()
    {
        $sql = "SELECT
                    p.id_prestamo,
                    p.id_solicitud,
                    p.id_cliente,
                    p.id_tipo_prestamo,
                    p.id_estado_prestamo,

                    p.monto_aprobado,
                    p.tasa_interes,
                    p.total_intereses,
                    p.total_pagar,

                    p.plazo_meses,
                    p.frecuencia_pago,
                    p.numero_cuotas,
                    p.monto_cuota,

                    p.requiere_aval,
                    p.requiere_garantia,

                    p.fecha_aprobacion,
                    p.fecha_desembolso,
                    p.fecha_inicio_pago,
                    p.fecha_vencimiento,

                    p.aprobado_por,
                    p.observaciones,
                    p.estatus,
                    p.fecha_creacion,

                    u.nombre,
                    u.apellido_paterno,
                    u.apellido_materno,
                    u.correo,
                    u.telefono,

                    tp.nombre AS tipo_prestamo,

                    ep.nombre AS estado_prestamo,

                    CONCAT_WS(
                        ' ',
                        ua.nombre,
                        ua.apellido_paterno,
                        ua.apellido_materno
                    ) AS nombre_aprobador

                FROM prestamos p

                INNER JOIN usuarios u
                    ON p.id_cliente = u.id_usuario

                INNER JOIN tipos_prestamo tp
                    ON p.id_tipo_prestamo = tp.id_tipo_prestamo

                INNER JOIN estados_prestamo ep
                    ON p.id_estado_prestamo = ep.id_estado_prestamo

                LEFT JOIN usuarios ua
                    ON p.aprobado_por = ua.id_usuario

                WHERE u.rol = 'cliente'

                ORDER BY p.id_prestamo DESC";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute();

        return $consulta->fetchAll();
    }


    // =========================================================
    // OBTENER PRÉSTAMO POR ID
    // =========================================================

    public function obtenerPorId($idPrestamo)
    {
        $sql = "SELECT
                    p.id_prestamo,
                    p.id_solicitud,
                    p.id_cliente,
                    p.id_tipo_prestamo,
                    p.id_estado_prestamo,

                    p.monto_aprobado,
                    p.tasa_interes,
                    p.total_intereses,
                    p.total_pagar,

                    p.plazo_meses,
                    p.frecuencia_pago,
                    p.numero_cuotas,
                    p.monto_cuota,

                    p.requiere_aval,
                    p.requiere_garantia,

                    p.fecha_aprobacion,
                    p.fecha_desembolso,
                    p.fecha_inicio_pago,
                    p.fecha_vencimiento,

                    p.aprobado_por,
                    p.observaciones,
                    p.estatus,
                    p.fecha_creacion,

                    u.nombre,
                    u.apellido_paterno,
                    u.apellido_materno,
                    u.correo,
                    u.telefono,

                    tp.nombre AS tipo_prestamo,

                    ep.nombre AS estado_prestamo,

                    CONCAT_WS(
                        ' ',
                        ua.nombre,
                        ua.apellido_paterno,
                        ua.apellido_materno
                    ) AS nombre_aprobador

                FROM prestamos p

                INNER JOIN usuarios u
                    ON p.id_cliente = u.id_usuario

                INNER JOIN tipos_prestamo tp
                    ON p.id_tipo_prestamo = tp.id_tipo_prestamo

                INNER JOIN estados_prestamo ep
                    ON p.id_estado_prestamo = ep.id_estado_prestamo

                LEFT JOIN usuarios ua
                    ON p.aprobado_por = ua.id_usuario

                WHERE p.id_prestamo = :id_prestamo

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_prestamo" => $idPrestamo
        ]);

        return $consulta->fetch();
    }


    // =========================================================
    // OBTENER PRÉSTAMO PARA ACTUALIZAR
    // BLOQUEA LA FILA DURANTE LA TRANSACCIÓN
    // =========================================================

    public function obtenerPorIdParaActualizar($idPrestamo)
    {
        $sql = "SELECT *
                FROM prestamos
                WHERE id_prestamo = :id_prestamo
                LIMIT 1
                FOR UPDATE";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_prestamo" => $idPrestamo
        ]);

        return $consulta->fetch();
    }


    // =========================================================
    // OBTENER ESTADO DE PRÉSTAMO POR NOMBRE
    // =========================================================

    public function obtenerEstadoPorNombre($nombre)
    {
        $sql = "SELECT
                    id_estado_prestamo,
                    nombre,
                    descripcion,
                    estatus
                FROM estados_prestamo
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
    // OBTENER MÉTODOS DE PAGO ACTIVOS
    // =========================================================

    public function obtenerMetodosPago()
    {
        $sql = "SELECT
                    id_metodo_pago,
                    nombre,
                    descripcion
                FROM metodos_pago
                WHERE estatus = 'activo'
                ORDER BY id_metodo_pago ASC";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute();

        return $consulta->fetchAll();
    }


    // =========================================================
    // OBTENER MÉTODO DE PAGO POR ID
    // =========================================================

    public function obtenerMetodoPagoPorId($idMetodoPago)
    {
        $sql = "SELECT
                    id_metodo_pago,
                    nombre,
                    descripcion,
                    estatus
                FROM metodos_pago
                WHERE id_metodo_pago = :id_metodo_pago
                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_metodo_pago" => $idMetodoPago
        ]);

        return $consulta->fetch();
    }


    // =========================================================
    // COMPROBAR SI UNA SOLICITUD YA TIENE PRÉSTAMO
    // =========================================================

    public function existePorSolicitud($idSolicitud)
    {
        $sql = "SELECT id_prestamo
                FROM prestamos
                WHERE id_solicitud = :id_solicitud
                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_solicitud" => $idSolicitud
        ]);

        return $consulta->fetch() ? true : false;
    }


    // =========================================================
    // COMPROBAR SI YA EXISTE UN DESEMBOLSO REALIZADO
    // =========================================================

    public function tieneDesembolsoRealizado($idPrestamo)
    {
        $sql = "SELECT id_desembolso
                FROM desembolsos
                WHERE id_prestamo = :id_prestamo
                AND estatus = 'realizado'
                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_prestamo" => $idPrestamo
        ]);

        return $consulta->fetch() ? true : false;
    }


    // =========================================================
    // CONTAR CUOTAS GENERADAS
    // =========================================================

    public function contarCuotas($idPrestamo)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM cuotas
                WHERE id_prestamo = :id_prestamo";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_prestamo" => $idPrestamo
        ]);

        $resultado =
            $consulta->fetch();

        return (int) ($resultado["total"] ?? 0);
    }


    // =========================================================
    // CREAR PRÉSTAMO
    // =========================================================

    public function crear($datos)
    {
        $sql = "INSERT INTO prestamos
                (
                    id_solicitud,
                    id_cliente,
                    id_tipo_prestamo,
                    id_estado_prestamo,

                    monto_aprobado,
                    tasa_interes,
                    total_intereses,
                    total_pagar,

                    plazo_meses,
                    frecuencia_pago,
                    numero_cuotas,
                    monto_cuota,

                    requiere_aval,
                    requiere_garantia,

                    fecha_aprobacion,

                    aprobado_por,

                    observaciones,

                    estatus
                )
                VALUES
                (
                    :id_solicitud,
                    :id_cliente,
                    :id_tipo_prestamo,
                    :id_estado_prestamo,

                    :monto_aprobado,
                    :tasa_interes,
                    :total_intereses,
                    :total_pagar,

                    :plazo_meses,
                    :frecuencia_pago,
                    :numero_cuotas,
                    :monto_cuota,

                    :requiere_aval,
                    :requiere_garantia,

                    NOW(),

                    :aprobado_por,

                    :observaciones,

                    'pendiente'
                )";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([

            ":id_solicitud" =>
                $datos["id_solicitud"],

            ":id_cliente" =>
                $datos["id_cliente"],

            ":id_tipo_prestamo" =>
                $datos["id_tipo_prestamo"],

            ":id_estado_prestamo" =>
                $datos["id_estado_prestamo"],

            ":monto_aprobado" =>
                $datos["monto_aprobado"],

            ":tasa_interes" =>
                $datos["tasa_interes"],

            ":total_intereses" =>
                $datos["total_intereses"],

            ":total_pagar" =>
                $datos["total_pagar"],

            ":plazo_meses" =>
                $datos["plazo_meses"],

            ":frecuencia_pago" =>
                $datos["frecuencia_pago"],

            ":numero_cuotas" =>
                $datos["numero_cuotas"],

            ":monto_cuota" =>
                $datos["monto_cuota"],

            ":requiere_aval" =>
                $datos["requiere_aval"],

            ":requiere_garantia" =>
                $datos["requiere_garantia"],

            ":aprobado_por" =>
                $datos["aprobado_por"],

            ":observaciones" =>
                $datos["observaciones"]
        ]);

        return $this->conexion->lastInsertId();
    }


    // =========================================================
    // REGISTRAR DESEMBOLSO
    // =========================================================

    public function crearDesembolso($datos)
    {
        $sql = "INSERT INTO desembolsos
                (
                    id_prestamo,
                    id_metodo_pago,
                    monto,
                    referencia,
                    comprobante,
                    fecha_desembolso,
                    realizado_por,
                    observaciones,
                    estatus
                )
                VALUES
                (
                    :id_prestamo,
                    :id_metodo_pago,
                    :monto,
                    :referencia,
                    :comprobante,
                    :fecha_desembolso,
                    :realizado_por,
                    :observaciones,
                    'realizado'
                )";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([

            ":id_prestamo" =>
                $datos["id_prestamo"],

            ":id_metodo_pago" =>
                $datos["id_metodo_pago"],

            ":monto" =>
                $datos["monto"],

            ":referencia" =>
                $datos["referencia"],

            ":comprobante" =>
                $datos["comprobante"],

            ":fecha_desembolso" =>
                $datos["fecha_desembolso"],

            ":realizado_por" =>
                $datos["realizado_por"],

            ":observaciones" =>
                $datos["observaciones"]
        ]);

        return $this->conexion->lastInsertId();
    }


    // =========================================================
    // ACTIVAR PRÉSTAMO DESPUÉS DEL DESEMBOLSO
    // =========================================================

    public function activarPrestamo(
        $idPrestamo,
        $idEstadoActivo,
        $fechaDesembolso,
        $fechaInicioPago,
        $fechaVencimiento
    )
    {
        $sql = "UPDATE prestamos

                SET
                    id_estado_prestamo = :id_estado_prestamo,
                    fecha_desembolso = :fecha_desembolso,
                    fecha_inicio_pago = :fecha_inicio_pago,
                    fecha_vencimiento = :fecha_vencimiento,
                    estatus = 'activo'

                WHERE id_prestamo = :id_prestamo

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        return $consulta->execute([

            ":id_estado_prestamo" =>
                $idEstadoActivo,

            ":fecha_desembolso" =>
                $fechaDesembolso,

            ":fecha_inicio_pago" =>
                $fechaInicioPago,

            ":fecha_vencimiento" =>
                $fechaVencimiento,

            ":id_prestamo" =>
                $idPrestamo
        ]);
    }


    // =========================================================
    // CREAR CUOTA
    // =========================================================

    public function crearCuota($datos)
    {
        $sql = "INSERT INTO cuotas
                (
                    id_prestamo,
                    numero_cuota,
                    fecha_vencimiento,

                    monto_capital,
                    monto_interes,
                    monto_total,

                    monto_pagado,
                    saldo_pendiente,

                    fecha_pago,
                    estatus,
                    observaciones
                )
                VALUES
                (
                    :id_prestamo,
                    :numero_cuota,
                    :fecha_vencimiento,

                    :monto_capital,
                    :monto_interes,
                    :monto_total,

                    0.00,
                    :saldo_pendiente,

                    NULL,
                    'pendiente',
                    NULL
                )";

        $consulta =
            $this->conexion->prepare($sql);

        return $consulta->execute([

            ":id_prestamo" =>
                $datos["id_prestamo"],

            ":numero_cuota" =>
                $datos["numero_cuota"],

            ":fecha_vencimiento" =>
                $datos["fecha_vencimiento"],

            ":monto_capital" =>
                $datos["monto_capital"],

            ":monto_interes" =>
                $datos["monto_interes"],

            ":monto_total" =>
                $datos["monto_total"],

            ":saldo_pendiente" =>
                $datos["monto_total"]
        ]);
    }


    // =========================================================
    // OBTENER CUOTAS DE UN PRÉSTAMO
    // =========================================================

    public function obtenerCuotas($idPrestamo)
    {
        $sql = "SELECT
                    id_cuota,
                    id_prestamo,
                    numero_cuota,
                    fecha_vencimiento,
                    monto_capital,
                    monto_interes,
                    monto_total,
                    monto_pagado,
                    saldo_pendiente,
                    fecha_pago,
                    estatus,
                    observaciones

                FROM cuotas

                WHERE id_prestamo = :id_prestamo

                ORDER BY numero_cuota ASC";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_prestamo" => $idPrestamo
        ]);

        return $consulta->fetchAll();
    }


    // =========================================================
    // OBTENER DESEMBOLSO REALIZADO
    // =========================================================

    public function obtenerDesembolso($idPrestamo)
    {
        $sql = "SELECT
                    d.id_desembolso,
                    d.id_prestamo,
                    d.id_metodo_pago,
                    d.monto,
                    d.referencia,
                    d.comprobante,
                    d.fecha_desembolso,
                    d.realizado_por,
                    d.observaciones,
                    d.estatus,

                    mp.nombre AS metodo_pago,

                    CONCAT_WS(
                        ' ',
                        u.nombre,
                        u.apellido_paterno,
                        u.apellido_materno
                    ) AS nombre_realizado_por

                FROM desembolsos d

                INNER JOIN metodos_pago mp
                    ON d.id_metodo_pago = mp.id_metodo_pago

                LEFT JOIN usuarios u
                    ON d.realizado_por = u.id_usuario

                WHERE d.id_prestamo = :id_prestamo
                AND d.estatus = 'realizado'

                ORDER BY d.id_desembolso DESC

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_prestamo" => $idPrestamo
        ]);

        return $consulta->fetch();
    }


    // =========================================================
    // INICIAR TRANSACCIÓN
    // =========================================================

    public function iniciarTransaccion()
    {
        if (!$this->conexion->inTransaction()) {

            return $this->conexion->beginTransaction();
        }

        return true;
    }


    // =========================================================
    // CONFIRMAR TRANSACCIÓN
    // =========================================================

    public function confirmarTransaccion()
    {
        if ($this->conexion->inTransaction()) {

            return $this->conexion->commit();
        }

        return true;
    }


    // =========================================================
    // CANCELAR TRANSACCIÓN
    // =========================================================

    public function cancelarTransaccion()
    {
        if ($this->conexion->inTransaction()) {

            return $this->conexion->rollBack();
        }

        return true;
    }
}