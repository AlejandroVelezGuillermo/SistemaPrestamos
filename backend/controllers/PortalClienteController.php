<?php

require_once __DIR__ . "/../models/PortalCliente.php";

class PortalClienteController
{
    private $portalCliente;

    public function __construct()
    {
        $this->portalCliente =
            new PortalCliente();
    }


    // =========================================================
    // RESUMEN GENERAL DEL CLIENTE
    // =========================================================

    public function obtenerResumen(
        $idCliente
    )
    {
        if (
            !$idCliente ||
            !is_numeric($idCliente)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Cliente inválido"
            ];
        }


        try {

            $resumen =
                $this->portalCliente
                    ->obtenerResumenCliente(
                        (int) $idCliente
                    );


            return [
                "success" => true,
                "data" => $resumen
            ];


        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible obtener el resumen del cliente"
            ];
        }
    }


    // =========================================================
    // LISTAR PRÉSTAMOS DEL CLIENTE
    // =========================================================

    public function obtenerPrestamos(
        $idCliente
    )
    {
        if (
            !$idCliente ||
            !is_numeric($idCliente)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Cliente inválido"
            ];
        }


        try {

            $prestamos =
                $this->portalCliente
                    ->obtenerPrestamosCliente(
                        (int) $idCliente
                    );


            return [
                "success" => true,
                "data" => $prestamos
            ];


        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible obtener los préstamos del cliente"
            ];
        }
    }


    // =========================================================
    // DETALLE DE UN PRÉSTAMO
    // =========================================================

    public function obtenerPrestamo(
        $idPrestamo,
        $idCliente
    )
    {
        if (
            !$idPrestamo ||
            !is_numeric($idPrestamo)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Préstamo inválido"
            ];
        }


        if (
            !$idCliente ||
            !is_numeric($idCliente)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Cliente inválido"
            ];
        }


        try {

            $prestamo =
                $this->portalCliente
                    ->obtenerPrestamoClientePorId(
                        (int) $idPrestamo,
                        (int) $idCliente
                    );


            if (!$prestamo) {

                return [
                    "success" => false,
                    "message" =>
                        "Préstamo no encontrado"
                ];
            }


            $proximaCuota =
                $this->portalCliente
                    ->obtenerProximaCuota(
                        (int) $idPrestamo,
                        (int) $idCliente
                    );


            $prestamo["proxima_cuota"] =
                $proximaCuota
                    ? $proximaCuota
                    : null;


            $prestamo["progreso_porcentaje"] =
                $this->calcularProgreso(
                    $prestamo["total_pagar"] ?? 0,
                    $prestamo["total_pagado"] ?? 0
                );


            $prestamo["texto_restante"] =
                $this->crearTextoRestante(
                    $prestamo["cuotas_pendientes"] ?? 0,
                    $prestamo["frecuencia_pago"] ?? ""
                );


            return [
                "success" => true,
                "data" => $prestamo
            ];


        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible obtener el préstamo"
            ];
        }
    }


    // =========================================================
    // CUOTAS DEL PRÉSTAMO
    // =========================================================

    public function obtenerCuotas(
        $idPrestamo,
        $idCliente
    )
    {
        if (
            !$idPrestamo ||
            !is_numeric($idPrestamo)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Préstamo inválido"
            ];
        }


        if (
            !$idCliente ||
            !is_numeric($idCliente)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Cliente inválido"
            ];
        }


        try {

            // -------------------------------------------------
            // PRIMERO VERIFICAMOS QUE EL PRÉSTAMO SEA DEL CLIENTE
            // -------------------------------------------------

            $prestamo =
                $this->portalCliente
                    ->obtenerPrestamoClientePorId(
                        (int) $idPrestamo,
                        (int) $idCliente
                    );


            if (!$prestamo) {

                return [
                    "success" => false,
                    "message" =>
                        "Préstamo no encontrado"
                ];
            }


            $cuotas =
                $this->portalCliente
                    ->obtenerCuotasPrestamo(
                        (int) $idPrestamo,
                        (int) $idCliente
                    );


            return [
                "success" => true,
                "data" => $cuotas
            ];


        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible obtener las cuotas del préstamo"
            ];
        }
    }


    // =========================================================
    // HISTORIAL DE PAGOS
    // =========================================================

    public function obtenerPagos(
        $idPrestamo,
        $idCliente
    )
    {
        if (
            !$idPrestamo ||
            !is_numeric($idPrestamo)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Préstamo inválido"
            ];
        }


        if (
            !$idCliente ||
            !is_numeric($idCliente)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Cliente inválido"
            ];
        }


        try {

            // -------------------------------------------------
            // VERIFICAR QUE EL PRÉSTAMO PERTENEZCA AL CLIENTE
            // -------------------------------------------------

            $prestamo =
                $this->portalCliente
                    ->obtenerPrestamoClientePorId(
                        (int) $idPrestamo,
                        (int) $idCliente
                    );


            if (!$prestamo) {

                return [
                    "success" => false,
                    "message" =>
                        "Préstamo no encontrado"
                ];
            }


            $pagos =
                $this->portalCliente
                    ->obtenerPagosPrestamo(
                        (int) $idPrestamo,
                        (int) $idCliente
                    );


            return [
                "success" => true,
                "data" => $pagos
            ];


        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible obtener el historial de pagos"
            ];
        }
    }


    // =========================================================
    // INFORMACIÓN COMPLETA DE UN PRÉSTAMO
    //
    // Esta función nos servirá mucho en el frontend porque
    // devuelve detalle + cuotas + pagos en una sola respuesta.
    // =========================================================

    public function obtenerPrestamoCompleto(
        $idPrestamo,
        $idCliente
    )
    {
        if (
            !$idPrestamo ||
            !is_numeric($idPrestamo)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Préstamo inválido"
            ];
        }


        if (
            !$idCliente ||
            !is_numeric($idCliente)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Cliente inválido"
            ];
        }


        try {

            $prestamo =
                $this->portalCliente
                    ->obtenerPrestamoClientePorId(
                        (int) $idPrestamo,
                        (int) $idCliente
                    );


            if (!$prestamo) {

                return [
                    "success" => false,
                    "message" =>
                        "Préstamo no encontrado"
                ];
            }


            $cuotas =
                $this->portalCliente
                    ->obtenerCuotasPrestamo(
                        (int) $idPrestamo,
                        (int) $idCliente
                    );


            $pagos =
                $this->portalCliente
                    ->obtenerPagosPrestamo(
                        (int) $idPrestamo,
                        (int) $idCliente
                    );


            $proximaCuota =
                $this->portalCliente
                    ->obtenerProximaCuota(
                        (int) $idPrestamo,
                        (int) $idCliente
                    );


            $prestamo["proxima_cuota"] =
                $proximaCuota
                    ? $proximaCuota
                    : null;


            $prestamo["progreso_porcentaje"] =
                $this->calcularProgreso(
                    $prestamo["total_pagar"] ?? 0,
                    $prestamo["total_pagado"] ?? 0
                );


            $prestamo["texto_restante"] =
                $this->crearTextoRestante(
                    $prestamo["cuotas_pendientes"] ?? 0,
                    $prestamo["frecuencia_pago"] ?? ""
                );


            return [
                "success" => true,

                "data" => [

                    "prestamo" =>
                        $prestamo,

                    "cuotas" =>
                        $cuotas,

                    "pagos" =>
                        $pagos
                ]
            ];


        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible obtener la información completa del préstamo"
            ];
        }
    }


    // =========================================================
    // CALCULAR PROGRESO
    // =========================================================

    private function calcularProgreso(
        $totalPagar,
        $totalPagado
    )
    {
        $totalPagar =
            (float) $totalPagar;


        $totalPagado =
            (float) $totalPagado;


        if ($totalPagar <= 0) {

            return 0;
        }


        $porcentaje =
            (
                $totalPagado /
                $totalPagar
            ) * 100;


        if ($porcentaje < 0) {

            $porcentaje = 0;
        }


        if ($porcentaje > 100) {

            $porcentaje = 100;
        }


        return round(
            $porcentaje,
            2
        );
    }


    // =========================================================
    // TEXTO DE PAGOS RESTANTES
    // =========================================================

    private function crearTextoRestante(
        $cuotasPendientes,
        $frecuencia
    )
    {
        $cuotasPendientes =
            (int) $cuotasPendientes;


        $frecuencia =
            strtolower(
                trim(
                    (string) $frecuencia
                )
            );


        if ($cuotasPendientes <= 0) {

            return "Préstamo liquidado";
        }


        switch ($frecuencia) {

            case "semanal":

                return $cuotasPendientes .
                    (
                        $cuotasPendientes === 1
                            ? " pago semanal restante"
                            : " pagos semanales restantes"
                    );


            case "quincenal":

                return $cuotasPendientes .
                    (
                        $cuotasPendientes === 1
                            ? " pago quincenal restante"
                            : " pagos quincenales restantes"
                    );


            case "mensual":

                return $cuotasPendientes .
                    (
                        $cuotasPendientes === 1
                            ? " pago mensual restante"
                            : " pagos mensuales restantes"
                    );


            default:

                return $cuotasPendientes .
                    (
                        $cuotasPendientes === 1
                            ? " cuota restante"
                            : " cuotas restantes"
                    );
        }
    }
}