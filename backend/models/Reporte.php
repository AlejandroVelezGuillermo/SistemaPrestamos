<?php

require_once __DIR__ . "/../config/database.php";

class Reporte
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }


    // =========================================================
    // RESUMEN GENERAL
    // =========================================================

    public function obtenerResumenGeneral()
    {
        $sql = "SELECT

                    (
                        SELECT COUNT(*)
                        FROM usuarios
                        WHERE rol = 'cliente'
                        AND estatus = 'activo'
                    ) AS total_clientes,

                    (
                        SELECT COUNT(*)
                        FROM solicitudes s

                        INNER JOIN estados_solicitud es
                            ON s.id_estado_solicitud =
                               es.id_estado_solicitud

                        WHERE es.nombre = 'pendiente'
                        AND s.estatus = 'activo'
                    ) AS solicitudes_pendientes,

                    (
                        SELECT COUNT(*)
                        FROM solicitudes s

                        INNER JOIN estados_solicitud es
                            ON s.id_estado_solicitud =
                               es.id_estado_solicitud

                        WHERE es.nombre = 'aprobada'
                        AND s.estatus = 'activo'
                    ) AS solicitudes_aprobadas,

                    (
                        SELECT COUNT(*)
                        FROM solicitudes s

                        INNER JOIN estados_solicitud es
                            ON s.id_estado_solicitud =
                               es.id_estado_solicitud

                        WHERE es.nombre = 'rechazada'
                        AND s.estatus = 'activo'
                    ) AS solicitudes_rechazadas,

                    (
                        SELECT COUNT(*)
                        FROM prestamos p

                        INNER JOIN estados_prestamo ep
                            ON p.id_estado_prestamo =
                               ep.id_estado_prestamo

                        WHERE ep.nombre = 'activo'
                        AND p.estatus = 'activo'
                    ) AS prestamos_activos,

                    (
                        SELECT COUNT(*)
                        FROM prestamos p

                        INNER JOIN estados_prestamo ep
                            ON p.id_estado_prestamo =
                               ep.id_estado_prestamo

                        WHERE ep.nombre = 'atrasado'
                        AND p.estatus = 'atrasado'
                    ) AS prestamos_atrasados,

                    (
                        SELECT COUNT(*)
                        FROM prestamos p

                        INNER JOIN estados_prestamo ep
                            ON p.id_estado_prestamo =
                               ep.id_estado_prestamo

                        WHERE ep.nombre = 'liquidado'
                        AND p.estatus = 'liquidado'
                    ) AS prestamos_liquidados,

                    (
                        SELECT COALESCE(
                            SUM(monto_aprobado),
                            0
                        )

                        FROM prestamos

                        WHERE estatus IN (
                            'activo',
                            'atrasado',
                            'liquidado'
                        )
                    ) AS capital_colocado,

                    (
                        SELECT COALESCE(
                            SUM(monto),
                            0
                        )

                        FROM pagos

                        WHERE estatus = 'confirmado'
                    ) AS total_cobrado,

                    (
                        SELECT COALESCE(
                            SUM(saldo_pendiente),
                            0
                        )

                        FROM cuotas

                        WHERE saldo_pendiente > 0
                    ) AS saldo_pendiente,

                    (
                        SELECT COUNT(*)
                        FROM pagos
                        WHERE estatus = 'confirmado'
                    ) AS total_pagos";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute();

        return $consulta->fetch();
    }


    // =========================================================
    // SOLICITUDES POR ESTADO
    // =========================================================

    public function obtenerSolicitudesPorEstado()
    {
        $sql = "SELECT
                    es.nombre AS estado,
                    COUNT(*) AS total

                FROM solicitudes s

                INNER JOIN estados_solicitud es
                    ON s.id_estado_solicitud =
                       es.id_estado_solicitud

                WHERE s.estatus = 'activo'

                GROUP BY
                    es.id_estado_solicitud,
                    es.nombre

                ORDER BY
                    es.id_estado_solicitud ASC";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute();

        return $consulta->fetchAll();
    }


    // =========================================================
    // PRÉSTAMOS POR ESTADO
    // =========================================================

    public function obtenerPrestamosPorEstado()
    {
        $sql = "SELECT
                    ep.nombre AS estado,
                    COUNT(*) AS total,

                    COALESCE(
                        SUM(p.monto_aprobado),
                        0
                    ) AS monto

                FROM prestamos p

                INNER JOIN estados_prestamo ep
                    ON p.id_estado_prestamo =
                       ep.id_estado_prestamo

                GROUP BY
                    ep.id_estado_prestamo,
                    ep.nombre

                ORDER BY
                    ep.id_estado_prestamo ASC";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute();

        return $consulta->fetchAll();
    }


    // =========================================================
    // ÚLTIMOS PAGOS
    // =========================================================

    public function obtenerUltimosPagos(
        $limite = 10
    )
    {
        $limite =
            max(
                1,
                min(
                    100,
                    (int) $limite
                )
            );


        $sql = "SELECT
                    pg.id_pago,
                    pg.id_prestamo,
                    pg.id_cuota,
                    pg.monto,
                    pg.fecha_pago,
                    pg.estatus,

                    c.numero_cuota,

                    u.nombre,
                    u.apellido_paterno,
                    u.apellido_materno,
                    u.correo,

                    mp.nombre AS metodo_pago

                FROM pagos pg

                INNER JOIN cuotas c
                    ON pg.id_cuota =
                       c.id_cuota

                INNER JOIN usuarios u
                    ON pg.id_cliente =
                       u.id_usuario

                INNER JOIN metodos_pago mp
                    ON pg.id_metodo_pago =
                       mp.id_metodo_pago

                WHERE pg.estatus = 'confirmado'

                ORDER BY
                    pg.fecha_pago DESC,
                    pg.id_pago DESC

                LIMIT {$limite}";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute();

        return $consulta->fetchAll();
    }


    // =========================================================
    // PAGOS POR RANGO DE FECHAS
    // =========================================================

    public function obtenerPagosPorRango(
        $fechaInicio,
        $fechaFin
    )
    {
        $sql = "SELECT
                    pg.id_pago,
                    pg.id_prestamo,
                    pg.id_cuota,
                    pg.monto,
                    pg.referencia,
                    pg.comprobante,
                    pg.fecha_pago,
                    pg.estatus,

                    c.numero_cuota,

                    u.nombre,
                    u.apellido_paterno,
                    u.apellido_materno,
                    u.correo,

                    mp.nombre AS metodo_pago

                FROM pagos pg

                INNER JOIN cuotas c
                    ON pg.id_cuota =
                       c.id_cuota

                INNER JOIN usuarios u
                    ON pg.id_cliente =
                       u.id_usuario

                INNER JOIN metodos_pago mp
                    ON pg.id_metodo_pago =
                       mp.id_metodo_pago

                WHERE pg.estatus = 'confirmado'

                AND DATE(pg.fecha_pago)
                    BETWEEN :fecha_inicio
                    AND :fecha_fin

                ORDER BY
                    pg.fecha_pago DESC,
                    pg.id_pago DESC";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([

            ":fecha_inicio" =>
                $fechaInicio,

            ":fecha_fin" =>
                $fechaFin
        ]);

        return $consulta->fetchAll();
    }


    // =========================================================
    // RESUMEN DE PAGOS POR RANGO
    // =========================================================

    public function obtenerResumenPagosPorRango(
        $fechaInicio,
        $fechaFin
    )
    {
        $sql = "SELECT
                    COUNT(*) AS total_pagos,

                    COALESCE(
                        SUM(monto),
                        0
                    ) AS total_cobrado,

                    COALESCE(
                        AVG(monto),
                        0
                    ) AS promedio_pago

                FROM pagos

                WHERE estatus = 'confirmado'

                AND DATE(fecha_pago)
                    BETWEEN :fecha_inicio
                    AND :fecha_fin";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([

            ":fecha_inicio" =>
                $fechaInicio,

            ":fecha_fin" =>
                $fechaFin
        ]);

        return $consulta->fetch();
    }
}