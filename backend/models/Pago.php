<?php

require_once __DIR__ . "/../config/database.php";

class Pago
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }


    // =========================================================
    // OBTENER PRÉSTAMOS ACTIVOS
    // =========================================================

    public function obtenerPrestamosActivos()
    {
        $sql = "SELECT
                    p.id_prestamo,
                    p.id_cliente,
                    p.monto_aprobado,
                    p.total_pagar,
                    p.numero_cuotas,
                    p.monto_cuota,
                    p.frecuencia_pago,
                    p.fecha_inicio_pago,
                    p.fecha_vencimiento,

                    u.nombre,
                    u.apellido_paterno,
                    u.apellido_materno,
                    u.correo,

                    tp.nombre AS tipo_prestamo,

                    ep.nombre AS estado_prestamo

                FROM prestamos p

                INNER JOIN usuarios u
                    ON p.id_cliente = u.id_usuario

                INNER JOIN tipos_prestamo tp
                    ON p.id_tipo_prestamo = tp.id_tipo_prestamo

                INNER JOIN estados_prestamo ep
                    ON p.id_estado_prestamo = ep.id_estado_prestamo

                WHERE ep.nombre = 'activo'
                AND p.estatus = 'activo'

                ORDER BY p.id_prestamo DESC";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute();

        return $consulta->fetchAll();
    }


    // =========================================================
    // OBTENER PRÉSTAMO POR ID
    // =========================================================

    public function obtenerPrestamoPorId($idPrestamo)
    {
        $sql = "SELECT
                    p.id_prestamo,
                    p.id_cliente,
                    p.id_estado_prestamo,
                    p.monto_aprobado,
                    p.tasa_interes,
                    p.total_intereses,
                    p.total_pagar,
                    p.plazo_meses,
                    p.frecuencia_pago,
                    p.numero_cuotas,
                    p.monto_cuota,
                    p.fecha_inicio_pago,
                    p.fecha_vencimiento,
                    p.estatus,

                    u.nombre,
                    u.apellido_paterno,
                    u.apellido_materno,
                    u.correo,

                    tp.nombre AS tipo_prestamo,

                    ep.nombre AS estado_prestamo

                FROM prestamos p

                INNER JOIN usuarios u
                    ON p.id_cliente = u.id_usuario

                INNER JOIN tipos_prestamo tp
                    ON p.id_tipo_prestamo = tp.id_tipo_prestamo

                INNER JOIN estados_prestamo ep
                    ON p.id_estado_prestamo = ep.id_estado_prestamo

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
    // =========================================================

    public function obtenerPrestamoParaActualizar($idPrestamo)
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
    // OBTENER CUOTAS DE UN PRÉSTAMO
    // =========================================================

    public function obtenerCuotasPorPrestamo($idPrestamo)
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
    // OBTENER CUOTA POR ID
    // =========================================================

    public function obtenerCuotaPorId($idCuota)
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

                WHERE id_cuota = :id_cuota

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_cuota" => $idCuota
        ]);

        return $consulta->fetch();
    }


    // =========================================================
    // OBTENER CUOTA PARA ACTUALIZAR
    // =========================================================

    public function obtenerCuotaParaActualizar($idCuota)
    {
        $sql = "SELECT *
                FROM cuotas
                WHERE id_cuota = :id_cuota
                LIMIT 1
                FOR UPDATE";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_cuota" => $idCuota
        ]);

        return $consulta->fetch();
    }


    // =========================================================
    // OBTENER MÉTODOS DE PAGO
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
    // REGISTRAR PAGO
    // =========================================================

    public function crearPago($datos)
    {
        $sql = "INSERT INTO pagos
                (
                    id_prestamo,
                    id_cuota,
                    id_cliente,
                    id_metodo_pago,
                    monto,
                    referencia,
                    comprobante,
                    fecha_pago,
                    registrado_por,
                    observaciones,
                    estatus
                )
                VALUES
                (
                    :id_prestamo,
                    :id_cuota,
                    :id_cliente,
                    :id_metodo_pago,
                    :monto,
                    :referencia,
                    :comprobante,
                    :fecha_pago,
                    :registrado_por,
                    :observaciones,
                    'confirmado'
                )";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([

            ":id_prestamo" =>
                $datos["id_prestamo"],

            ":id_cuota" =>
                $datos["id_cuota"],

            ":id_cliente" =>
                $datos["id_cliente"],

            ":id_metodo_pago" =>
                $datos["id_metodo_pago"],

            ":monto" =>
                $datos["monto"],

            ":referencia" =>
                $datos["referencia"],

            ":comprobante" =>
                $datos["comprobante"],

            ":fecha_pago" =>
                $datos["fecha_pago"],

            ":registrado_por" =>
                $datos["registrado_por"],

            ":observaciones" =>
                $datos["observaciones"]
        ]);

        return $this->conexion->lastInsertId();
    }


    // =========================================================
    // ACTUALIZAR CUOTA DESPUÉS DE UN PAGO
    // =========================================================

    public function actualizarCuota(
        $idCuota,
        $montoPagado,
        $saldoPendiente,
        $estatus,
        $fechaPago
    )
    {
        $sql = "UPDATE cuotas

                SET
                    monto_pagado = :monto_pagado,
                    saldo_pendiente = :saldo_pendiente,
                    estatus = :estatus,
                    fecha_pago = :fecha_pago

                WHERE id_cuota = :id_cuota

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        return $consulta->execute([

            ":monto_pagado" =>
                $montoPagado,

            ":saldo_pendiente" =>
                $saldoPendiente,

            ":estatus" =>
                $estatus,

            ":fecha_pago" =>
                $fechaPago,

            ":id_cuota" =>
                $idCuota
        ]);
    }


    // =========================================================
    // CONTAR CUOTAS CON SALDO
    // =========================================================

    public function contarCuotasConSaldo($idPrestamo)
    {
        $sql = "SELECT COUNT(*) AS total

                FROM cuotas

                WHERE id_prestamo = :id_prestamo
                AND saldo_pendiente > 0";

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
    // OBTENER ESTADO DE PRÉSTAMO
    // =========================================================

    public function obtenerEstadoPrestamoPorNombre($nombre)
    {
        $sql = "SELECT
                    id_estado_prestamo,
                    nombre,
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
    // MARCAR PRÉSTAMO COMO LIQUIDADO
    // =========================================================

    public function liquidarPrestamo(
        $idPrestamo,
        $idEstadoLiquidado
    )
    {
        $sql = "UPDATE prestamos

                SET
                    id_estado_prestamo = :id_estado_prestamo,
                    estatus = 'liquidado'

                WHERE id_prestamo = :id_prestamo

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        return $consulta->execute([

            ":id_estado_prestamo" =>
                $idEstadoLiquidado,

            ":id_prestamo" =>
                $idPrestamo
        ]);
    }


    // =========================================================
    // OBTENER HISTORIAL DE PAGOS
    // =========================================================

    public function obtenerHistorialPagos()
    {
        $sql = "SELECT
                    pg.id_pago,
                    pg.id_prestamo,
                    pg.id_cuota,
                    pg.id_cliente,
                    pg.id_metodo_pago,
                    pg.monto,
                    pg.referencia,
                    pg.comprobante,
                    pg.fecha_pago,
                    pg.registrado_por,
                    pg.observaciones,
                    pg.estatus,

                    c.numero_cuota,

                    u.nombre,
                    u.apellido_paterno,
                    u.apellido_materno,
                    u.correo,

                    mp.nombre AS metodo_pago,

                    CONCAT(
                        COALESCE(ur.nombre, ''),
                        ' ',
                        COALESCE(ur.apellido_paterno, ''),
                        ' ',
                        COALESCE(ur.apellido_materno, '')
                    ) AS nombre_registrador

                FROM pagos pg

                INNER JOIN cuotas c
                    ON pg.id_cuota = c.id_cuota

                INNER JOIN usuarios u
                    ON pg.id_cliente = u.id_usuario

                INNER JOIN metodos_pago mp
                    ON pg.id_metodo_pago = mp.id_metodo_pago

                LEFT JOIN usuarios ur
                    ON pg.registrado_por = ur.id_usuario

                WHERE pg.estatus = 'confirmado'

                ORDER BY pg.fecha_pago DESC,
                         pg.id_pago DESC";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute();

        return $consulta->fetchAll();
    }


    // =========================================================
    // OBTENER PAGO POR ID
    // =========================================================

    public function obtenerPagoPorId($idPago)
    {
        $sql = "SELECT
                    pg.id_pago,
                    pg.id_prestamo,
                    pg.id_cuota,
                    pg.id_cliente,
                    pg.id_metodo_pago,
                    pg.monto,
                    pg.referencia,
                    pg.comprobante,
                    pg.fecha_pago,
                    pg.registrado_por,
                    pg.observaciones,
                    pg.estatus,

                    c.numero_cuota,

                    u.nombre,
                    u.apellido_paterno,
                    u.apellido_materno,
                    u.correo,

                    mp.nombre AS metodo_pago,

                    CONCAT(
                        COALESCE(ur.nombre, ''),
                        ' ',
                        COALESCE(ur.apellido_paterno, ''),
                        ' ',
                        COALESCE(ur.apellido_materno, '')
                    ) AS nombre_registrador

                FROM pagos pg

                INNER JOIN cuotas c
                    ON pg.id_cuota = c.id_cuota

                INNER JOIN usuarios u
                    ON pg.id_cliente = u.id_usuario

                INNER JOIN metodos_pago mp
                    ON pg.id_metodo_pago = mp.id_metodo_pago

                LEFT JOIN usuarios ur
                    ON pg.registrado_por = ur.id_usuario

                WHERE pg.id_pago = :id_pago

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_pago" => $idPago
        ]);

        return $consulta->fetch();
    }


    // =========================================================
    // OBTENER RESUMEN DE PAGOS
    // =========================================================

    public function obtenerResumenPagos()
    {
        // =====================================================
        // RESUMEN DE PAGOS CONFIRMADOS
        // =====================================================

        $sqlPagos = "SELECT
                        COUNT(*) AS total_pagos,

                        COALESCE(
                            SUM(monto),
                            0
                        ) AS total_pagado,

                        COALESCE(
                            AVG(monto),
                            0
                        ) AS promedio_pago

                    FROM pagos

                    WHERE estatus = 'confirmado'";

        $consultaPagos =
            $this->conexion->prepare(
                $sqlPagos
            );

        $consultaPagos->execute();

        $resumenPagos =
            $consultaPagos->fetch();


        // =====================================================
        // SALDO PENDIENTE REAL
        // =====================================================

        $sqlSaldo = "SELECT
                        COALESCE(
                            SUM(c.saldo_pendiente),
                            0
                        ) AS total_por_cobrar

                    FROM cuotas c

                    INNER JOIN prestamos p
                        ON c.id_prestamo = p.id_prestamo

                    INNER JOIN estados_prestamo ep
                        ON p.id_estado_prestamo =
                           ep.id_estado_prestamo

                    WHERE ep.nombre IN (
                        'activo',
                        'atrasado'
                    )

                    AND p.estatus IN (
                        'activo',
                        'atrasado'
                    )

                    AND c.saldo_pendiente > 0";

        $consultaSaldo =
            $this->conexion->prepare(
                $sqlSaldo
            );

        $consultaSaldo->execute();

        $resumenSaldo =
            $consultaSaldo->fetch();


        // =====================================================
        // DEVOLVER RESUMEN
        // =====================================================

        return [

            "total_pagos" =>
                $resumenPagos[
                    "total_pagos"
                ] ?? 0,

            "total_pagado" =>
                $resumenPagos[
                    "total_pagado"
                ] ?? 0,

            "promedio_pago" =>
                $resumenPagos[
                    "promedio_pago"
                ] ?? 0,

            "total_por_cobrar" =>
                $resumenSaldo[
                    "total_por_cobrar"
                ] ?? 0
        ];
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