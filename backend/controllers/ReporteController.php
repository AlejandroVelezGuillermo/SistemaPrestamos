<?php

require_once __DIR__ . "/../models/Reporte.php";

class ReporteController
{
    private $reporte;

    public function __construct()
    {
        $this->reporte =
            new Reporte();
    }


    // =========================================================
    // RESUMEN GENERAL
    // =========================================================

    public function resumenGeneral()
    {
        try {

            $resumen =
                $this->reporte
                    ->obtenerResumenGeneral();


            return [
                "success" => true,

                "data" => [

                    "total_clientes" =>
                        (int) (
                            $resumen[
                                "total_clientes"
                            ] ?? 0
                        ),

                    "solicitudes_pendientes" =>
                        (int) (
                            $resumen[
                                "solicitudes_pendientes"
                            ] ?? 0
                        ),

                    "solicitudes_aprobadas" =>
                        (int) (
                            $resumen[
                                "solicitudes_aprobadas"
                            ] ?? 0
                        ),

                    "solicitudes_rechazadas" =>
                        (int) (
                            $resumen[
                                "solicitudes_rechazadas"
                            ] ?? 0
                        ),

                    "prestamos_activos" =>
                        (int) (
                            $resumen[
                                "prestamos_activos"
                            ] ?? 0
                        ),

                    "prestamos_atrasados" =>
                        (int) (
                            $resumen[
                                "prestamos_atrasados"
                            ] ?? 0
                        ),

                    "prestamos_liquidados" =>
                        (int) (
                            $resumen[
                                "prestamos_liquidados"
                            ] ?? 0
                        ),

                    "capital_colocado" =>
                        round(
                            (float) (
                                $resumen[
                                    "capital_colocado"
                                ] ?? 0
                            ),
                            2
                        ),

                    "total_cobrado" =>
                        round(
                            (float) (
                                $resumen[
                                    "total_cobrado"
                                ] ?? 0
                            ),
                            2
                        ),

                    "saldo_pendiente" =>
                        round(
                            (float) (
                                $resumen[
                                    "saldo_pendiente"
                                ] ?? 0
                            ),
                            2
                        ),

                    "total_pagos" =>
                        (int) (
                            $resumen[
                                "total_pagos"
                            ] ?? 0
                        )
                ]
            ];


        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible obtener el resumen general"
            ];
        }
    }


    // =========================================================
    // SOLICITUDES POR ESTADO
    // =========================================================

    public function solicitudesPorEstado()
    {
        try {

            return [
                "success" => true,
                "data" =>
                    $this->reporte
                        ->obtenerSolicitudesPorEstado()
            ];


        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible obtener el reporte de solicitudes"
            ];
        }
    }


    // =========================================================
    // PRÉSTAMOS POR ESTADO
    // =========================================================

    public function prestamosPorEstado()
    {
        try {

            return [
                "success" => true,
                "data" =>
                    $this->reporte
                        ->obtenerPrestamosPorEstado()
            ];


        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible obtener el reporte de préstamos"
            ];
        }
    }


    // =========================================================
    // ÚLTIMOS PAGOS
    // =========================================================

    public function ultimosPagos(
        $limite = 10
    )
    {
        if (!is_numeric($limite)) {

            $limite = 10;
        }


        $limite =
            (int) $limite;


        if ($limite <= 0) {

            $limite = 10;
        }


        try {

            return [
                "success" => true,
                "data" =>
                    $this->reporte
                        ->obtenerUltimosPagos(
                            $limite
                        )
            ];


        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible obtener los últimos pagos"
            ];
        }
    }


    // =========================================================
    // PAGOS POR RANGO
    // =========================================================

    public function pagosPorRango(
        $fechaInicio,
        $fechaFin
    )
    {
        $validacion =
            $this->validarRangoFechas(
                $fechaInicio,
                $fechaFin
            );


        if (!$validacion["success"]) {

            return $validacion;
        }


        try {

            $pagos =
                $this->reporte
                    ->obtenerPagosPorRango(
                        $fechaInicio,
                        $fechaFin
                    );


            $resumen =
                $this->reporte
                    ->obtenerResumenPagosPorRango(
                        $fechaInicio,
                        $fechaFin
                    );


            return [
                "success" => true,

                "data" => [

                    "fecha_inicio" =>
                        $fechaInicio,

                    "fecha_fin" =>
                        $fechaFin,

                    "resumen" => [

                        "total_pagos" =>
                            (int) (
                                $resumen[
                                    "total_pagos"
                                ] ?? 0
                            ),

                        "total_cobrado" =>
                            round(
                                (float) (
                                    $resumen[
                                        "total_cobrado"
                                    ] ?? 0
                                ),
                                2
                            ),

                        "promedio_pago" =>
                            round(
                                (float) (
                                    $resumen[
                                        "promedio_pago"
                                    ] ?? 0
                                ),
                                2
                            )
                    ],

                    "pagos" =>
                        $pagos
                ]
            ];


        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible generar el reporte por fechas"
            ];
        }
    }


    // =========================================================
    // VALIDAR RANGO DE FECHAS
    // =========================================================

    private function validarRangoFechas(
        $fechaInicio,
        $fechaFin
    )
    {
        $fechaInicio =
            trim(
                (string) $fechaInicio
            );


        $fechaFin =
            trim(
                (string) $fechaFin
            );


        if (
            $fechaInicio === "" ||
            $fechaFin === ""
        ) {

            return [
                "success" => false,
                "message" =>
                    "Selecciona la fecha inicial y la fecha final"
            ];
        }


        $inicio =
            DateTimeImmutable::createFromFormat(
                "!Y-m-d",
                $fechaInicio
            );


        $erroresInicio =
            DateTimeImmutable::getLastErrors();


        if (
            !$inicio ||
            (
                is_array($erroresInicio) &&
                (
                    $erroresInicio[
                        "warning_count"
                    ] > 0 ||
                    $erroresInicio[
                        "error_count"
                    ] > 0
                )
            )
        ) {

            return [
                "success" => false,
                "message" =>
                    "La fecha inicial no es válida"
            ];
        }


        $fin =
            DateTimeImmutable::createFromFormat(
                "!Y-m-d",
                $fechaFin
            );


        $erroresFin =
            DateTimeImmutable::getLastErrors();


        if (
            !$fin ||
            (
                is_array($erroresFin) &&
                (
                    $erroresFin[
                        "warning_count"
                    ] > 0 ||
                    $erroresFin[
                        "error_count"
                    ] > 0
                )
            )
        ) {

            return [
                "success" => false,
                "message" =>
                    "La fecha final no es válida"
            ];
        }


        if ($inicio > $fin) {

            return [
                "success" => false,
                "message" =>
                    "La fecha inicial no puede ser mayor que la fecha final"
            ];
        }


        return [
            "success" => true
        ];
    }
}