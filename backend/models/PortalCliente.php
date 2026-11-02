<?php

require_once __DIR__ . "/../config/database.php";

class PortalCliente
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }


    // =========================================================
    // RESUMEN GENERAL DEL CLIENTE
    // =========================================================

    public function obtenerResumenCliente($idCliente)
    {
        $sql = "
            SELECT

                COUNT(DISTINCT p.id_prestamo) AS total_prestamos,

                COALESCE(
                    SUM(
                        CASE
                            WHEN ep.nombre IN ('activo', 'atrasado')
                            THEN p.monto_aprobado
                            ELSE 0
                        END
                    ),
                    0
                ) AS capital_activo,

                COALESCE(
                    (
                        SELECT SUM(pg.monto)
                        FROM pagos pg
                        WHERE pg.id_cliente = :id_cliente_pago
                        AND pg.estatus = 'confirmado'
                    ),
                    0
                ) AS total_pagado,

                COALESCE(
                    (
                        SELECT SUM(c.saldo_pendiente)

                        FROM cuotas c

                        INNER JOIN prestamos pr
                            ON pr.id_prestamo = c.id_prestamo

                        INNER JOIN estados_prestamo epr
                            ON epr.id_estado_prestamo =
                               pr.id_estado_prestamo

                        WHERE pr.id_cliente =
                              :id_cliente_saldo

                        AND epr.nombre IN (
                            'activo',
                            'atrasado'
                        )

                        AND pr.estatus IN (
                            'activo',
                            'atrasado'
                        )

                        AND c.saldo_pendiente > 0
                    ),
                    0
                ) AS saldo_pendiente

            FROM prestamos p

            INNER JOIN estados_prestamo ep
                ON ep.id_estado_prestamo =
                   p.id_estado_prestamo

            WHERE p.id_cliente =
                  :id_cliente
        ";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_cliente" =>
                $idCliente,

            ":id_cliente_pago" =>
                $idCliente,

            ":id_cliente_saldo" =>
                $idCliente
        ]);

        return $consulta->fetch();
    }


    // =========================================================
    // OBTENER TODOS LOS PRÉSTAMOS DEL CLIENTE
    // =========================================================

    public function obtenerPrestamosCliente(
        $idCliente
    )
    {
        $sql = "
            SELECT

                p.id_prestamo,

                p.id_solicitud,

                p.id_cliente,

                p.id_tipo_prestamo,

                p.id_estado_prestamo,

                tp.nombre
                    AS tipo_prestamo,

                ep.nombre
                    AS estado_prestamo,

                s.monto_solicitado,

                p.monto_aprobado,

                p.tasa_interes,

                p.total_intereses,

                p.total_pagar,

                p.plazo_meses,

                p.frecuencia_pago,

                p.numero_cuotas,

                p.monto_cuota,

                p.fecha_aprobacion,

                p.fecha_desembolso,

                p.fecha_inicio_pago,

                p.fecha_vencimiento,

                p.estatus,

                p.fecha_creacion,

                COALESCE(
                    (
                        SELECT SUM(pg.monto)

                        FROM pagos pg

                        WHERE pg.id_prestamo =
                              p.id_prestamo

                        AND pg.id_cliente =
                            p.id_cliente

                        AND pg.estatus =
                            'confirmado'
                    ),
                    0
                ) AS total_pagado,

                COALESCE(
                    (
                        SELECT SUM(c.saldo_pendiente)

                        FROM cuotas c

                        WHERE c.id_prestamo =
                              p.id_prestamo
                    ),
                    0
                ) AS saldo_pendiente,

                (
                    SELECT COUNT(*)

                    FROM cuotas c

                    WHERE c.id_prestamo =
                          p.id_prestamo

                    AND c.estatus =
                        'pagada'
                ) AS cuotas_pagadas,

                (
                    SELECT COUNT(*)

                    FROM cuotas c

                    WHERE c.id_prestamo =
                          p.id_prestamo

                    AND c.saldo_pendiente > 0
                ) AS cuotas_pendientes,

                (
                    SELECT MIN(c.fecha_vencimiento)

                    FROM cuotas c

                    WHERE c.id_prestamo =
                          p.id_prestamo

                    AND c.saldo_pendiente > 0
                ) AS proximo_vencimiento

            FROM prestamos p

            LEFT JOIN solicitudes s
                ON s.id_solicitud =
                   p.id_solicitud

            LEFT JOIN tipos_prestamo tp
                ON tp.id_tipo_prestamo =
                   p.id_tipo_prestamo

            LEFT JOIN estados_prestamo ep
                ON ep.id_estado_prestamo =
                   p.id_estado_prestamo

            WHERE p.id_cliente =
                  :id_cliente

            ORDER BY
                p.id_prestamo DESC
        ";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_cliente" =>
                $idCliente
        ]);

        return $consulta->fetchAll();
    }


    // =========================================================
    // OBTENER UN PRÉSTAMO DEL CLIENTE
    //
    // IMPORTANTE:
    // Además del ID del préstamo verificamos id_cliente.
    // Así un cliente no puede consultar el préstamo de otro.
    // =========================================================

    public function obtenerPrestamoClientePorId(
        $idPrestamo,
        $idCliente
    )
    {
        $sql = "
            SELECT

                p.id_prestamo,

                p.id_solicitud,

                p.id_cliente,

                p.id_tipo_prestamo,

                p.id_estado_prestamo,

                tp.nombre
                    AS tipo_prestamo,

                ep.nombre
                    AS estado_prestamo,

                s.monto_solicitado,

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

                p.observaciones,

                p.estatus,

                p.fecha_creacion,

                COALESCE(
                    (
                        SELECT SUM(pg.monto)

                        FROM pagos pg

                        WHERE pg.id_prestamo =
                              p.id_prestamo

                        AND pg.id_cliente =
                            p.id_cliente

                        AND pg.estatus =
                            'confirmado'
                    ),
                    0
                ) AS total_pagado,

                COALESCE(
                    (
                        SELECT SUM(c.saldo_pendiente)

                        FROM cuotas c

                        WHERE c.id_prestamo =
                              p.id_prestamo
                    ),
                    0
                ) AS saldo_pendiente,

                (
                    SELECT COUNT(*)

                    FROM cuotas c

                    WHERE c.id_prestamo =
                          p.id_prestamo

                    AND c.estatus =
                        'pagada'
                ) AS cuotas_pagadas,

                (
                    SELECT COUNT(*)

                    FROM cuotas c

                    WHERE c.id_prestamo =
                          p.id_prestamo

                    AND c.saldo_pendiente > 0
                ) AS cuotas_pendientes,

                (
                    SELECT MIN(c.fecha_vencimiento)

                    FROM cuotas c

                    WHERE c.id_prestamo =
                          p.id_prestamo

                    AND c.saldo_pendiente > 0
                ) AS proximo_vencimiento

            FROM prestamos p

            LEFT JOIN solicitudes s
                ON s.id_solicitud =
                   p.id_solicitud

            LEFT JOIN tipos_prestamo tp
                ON tp.id_tipo_prestamo =
                   p.id_tipo_prestamo

            LEFT JOIN estados_prestamo ep
                ON ep.id_estado_prestamo =
                   p.id_estado_prestamo

            WHERE p.id_prestamo =
                  :id_prestamo

            AND p.id_cliente =
                :id_cliente

            LIMIT 1
        ";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_prestamo" =>
                $idPrestamo,

            ":id_cliente" =>
                $idCliente
        ]);

        return $consulta->fetch();
    }


    // =========================================================
    // CUOTAS DEL PRÉSTAMO
    // =========================================================

    public function obtenerCuotasPrestamo(
        $idPrestamo,
        $idCliente
    )
    {
        $sql = "
            SELECT

                c.id_cuota,

                c.id_prestamo,

                c.numero_cuota,

                c.fecha_vencimiento,

                c.monto_capital,

                c.monto_interes,

                c.monto_total,

                c.monto_pagado,

                c.saldo_pendiente,

                c.fecha_pago,

                c.estatus,

                c.observaciones

            FROM cuotas c

            INNER JOIN prestamos p
                ON p.id_prestamo =
                   c.id_prestamo

            WHERE c.id_prestamo =
                  :id_prestamo

            AND p.id_cliente =
                :id_cliente

            ORDER BY
                c.numero_cuota ASC
        ";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_prestamo" =>
                $idPrestamo,

            ":id_cliente" =>
                $idCliente
        ]);

        return $consulta->fetchAll();
    }


    // =========================================================
    // HISTORIAL DE PAGOS
    // =========================================================

    public function obtenerPagosPrestamo(
        $idPrestamo,
        $idCliente
    )
    {
        $sql = "
            SELECT

                pg.id_pago,

                pg.id_prestamo,

                pg.id_cuota,

                pg.monto,

                pg.referencia,

                pg.comprobante,

                pg.fecha_pago,

                pg.observaciones,

                pg.estatus,

                mp.nombre
                    AS metodo_pago,

                c.numero_cuota

            FROM pagos pg

            INNER JOIN prestamos p
                ON p.id_prestamo =
                   pg.id_prestamo

            LEFT JOIN metodos_pago mp
                ON mp.id_metodo_pago =
                   pg.id_metodo_pago

            LEFT JOIN cuotas c
                ON c.id_cuota =
                   pg.id_cuota

            WHERE pg.id_prestamo =
                  :id_prestamo

            AND pg.id_cliente =
                :id_cliente_pago

            AND p.id_cliente =
                :id_cliente_prestamo

            ORDER BY
                pg.fecha_pago DESC,
                pg.id_pago DESC
        ";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_prestamo" =>
                $idPrestamo,

            ":id_cliente_pago" =>
                $idCliente,

            ":id_cliente_prestamo" =>
                $idCliente
        ]);

        return $consulta->fetchAll();
    }


    // =========================================================
    // PRÓXIMA CUOTA
    // =========================================================

    public function obtenerProximaCuota(
        $idPrestamo,
        $idCliente
    )
    {
        $sql = "
            SELECT

                c.id_cuota,

                c.numero_cuota,

                c.fecha_vencimiento,

                c.monto_total,

                c.monto_pagado,

                c.saldo_pendiente,

                c.estatus

            FROM cuotas c

            INNER JOIN prestamos p
                ON p.id_prestamo =
                   c.id_prestamo

            WHERE c.id_prestamo =
                  :id_prestamo

            AND p.id_cliente =
                :id_cliente

            AND c.saldo_pendiente > 0

            ORDER BY
                c.fecha_vencimiento ASC,
                c.numero_cuota ASC

            LIMIT 1
        ";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_prestamo" =>
                $idPrestamo,

            ":id_cliente" =>
                $idCliente
        ]);

        return $consulta->fetch();
    }
}