<?php

require_once __DIR__ . "/../models/Pago.php";

class PagoController
{
    private $pago;

    public function __construct()
    {
        $this->pago =
            new Pago();
    }


    // =========================================================
    // LISTAR PRÉSTAMOS ACTIVOS
    // =========================================================

    public function listarPrestamosActivos()
    {
        return [
            "success" => true,
            "data" =>
                $this->pago
                    ->obtenerPrestamosActivos()
        ];
    }


    // =========================================================
    // OBTENER PRÉSTAMO
    // =========================================================

    public function obtenerPrestamo($idPrestamo)
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


        $prestamo =
            $this->pago
                ->obtenerPrestamoPorId(
                    (int) $idPrestamo
                );


        if (!$prestamo) {

            return [
                "success" => false,
                "message" =>
                    "Préstamo no encontrado"
            ];
        }


        return [
            "success" => true,
            "data" => $prestamo
        ];
    }


    // =========================================================
    // LISTAR CUOTAS
    // =========================================================

    public function listarCuotas($idPrestamo)
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


        $idPrestamo =
            (int) $idPrestamo;


        $prestamo =
            $this->pago
                ->obtenerPrestamoPorId(
                    $idPrestamo
                );


        if (!$prestamo) {

            return [
                "success" => false,
                "message" =>
                    "Préstamo no encontrado"
            ];
        }


        $cuotas =
            $this->pago
                ->obtenerCuotasPorPrestamo(
                    $idPrestamo
                );


        // =====================================================
        // RESUMEN
        // =====================================================

        $totalCuotas =
            count($cuotas);

        $cuotasPendientes = 0;
        $cuotasPagadas = 0;
        $cuotasParciales = 0;
        $cuotasVencidas = 0;

        $totalPagado = 0.00;
        $saldoPendiente = 0.00;


        foreach ($cuotas as $cuota) {

            $estatus =
                strtolower(
                    trim(
                        (string) $cuota[
                            "estatus"
                        ]
                    )
                );


            switch ($estatus) {

                case "pendiente":

                    $cuotasPendientes++;

                    break;


                case "pagada":

                    $cuotasPagadas++;

                    break;


                case "parcial":

                    $cuotasParciales++;

                    break;


                case "vencida":

                    $cuotasVencidas++;

                    break;
            }


            $totalPagado +=
                (float) $cuota[
                    "monto_pagado"
                ];


            $saldoPendiente +=
                (float) $cuota[
                    "saldo_pendiente"
                ];
        }


        return [
            "success" => true,

            "data" => [

                "prestamo" =>
                    $prestamo,

                "cuotas" =>
                    $cuotas,

                "resumen" => [

                    "total_cuotas" =>
                        $totalCuotas,

                    "cuotas_pendientes" =>
                        $cuotasPendientes,

                    "cuotas_pagadas" =>
                        $cuotasPagadas,

                    "cuotas_parciales" =>
                        $cuotasParciales,

                    "cuotas_vencidas" =>
                        $cuotasVencidas,

                    "total_pagado" =>
                        round(
                            $totalPagado,
                            2
                        ),

                    "saldo_pendiente" =>
                        round(
                            $saldoPendiente,
                            2
                        )
                ]
            ]
        ];
    }


    // =========================================================
    // LISTAR MÉTODOS DE PAGO
    // =========================================================

    public function listarMetodosPago()
    {
        return [
            "success" => true,
            "data" =>
                $this->pago
                    ->obtenerMetodosPago()
        ];
    }


    // =========================================================
    // REGISTRAR PAGO DE UNA CUOTA
    // =========================================================

    public function registrarPago(
        $idUsuario,
        $datos
    )
    {
        // =====================================================
        // VALIDAR USUARIO
        // =====================================================

        if (
            !$idUsuario ||
            !is_numeric($idUsuario)
        ) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible identificar al usuario que registra el pago"
            ];
        }


        $idUsuario =
            (int) $idUsuario;


        // =====================================================
        // PRÉSTAMO
        // =====================================================

        $idPrestamo =
            $datos["id_prestamo"] ?? null;


        if (
            !$idPrestamo ||
            !is_numeric($idPrestamo)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Selecciona un préstamo válido"
            ];
        }


        $idPrestamo =
            (int) $idPrestamo;


        // =====================================================
        // CUOTA
        // =====================================================

        $idCuota =
            $datos["id_cuota"] ?? null;


        if (
            !$idCuota ||
            !is_numeric($idCuota)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Selecciona una cuota válida"
            ];
        }


        $idCuota =
            (int) $idCuota;


        // =====================================================
        // MÉTODO DE PAGO
        // =====================================================

        $idMetodoPago =
            $datos["id_metodo_pago"] ?? null;


        if (
            !$idMetodoPago ||
            !is_numeric($idMetodoPago)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Selecciona un método de pago"
            ];
        }


        $idMetodoPago =
            (int) $idMetodoPago;


        $metodoPago =
            $this->pago
                ->obtenerMetodoPagoPorId(
                    $idMetodoPago
                );


        if (
            !$metodoPago ||
            $metodoPago["estatus"] !== "activo"
        ) {

            return [
                "success" => false,
                "message" =>
                    "El método de pago seleccionado no está disponible"
            ];
        }


        // =====================================================
        // MONTO
        // =====================================================

        $monto =
            $datos["monto"] ?? null;


        if (
            !is_numeric($monto) ||
            (float) $monto <= 0
        ) {

            return [
                "success" => false,
                "message" =>
                    "Ingresa un monto válido"
            ];
        }


        $monto =
            round(
                (float) $monto,
                2
            );


        // =====================================================
        // REFERENCIA
        // =====================================================

        $referencia =
            trim(
                (string) (
                    $datos["referencia"] ?? ""
                )
            );


        if ($referencia === "") {

            $referencia = null;
        }


        // =====================================================
        // COMPROBANTE
        // =====================================================

        $comprobante =
            trim(
                (string) (
                    $datos["comprobante"] ?? ""
                )
            );


        if ($comprobante === "") {

            $comprobante = null;
        }


        // =====================================================
        // OBSERVACIONES
        // =====================================================

        $observaciones =
            trim(
                (string) (
                    $datos["observaciones"] ?? ""
                )
            );


        if ($observaciones === "") {

            $observaciones = null;
        }


        // =====================================================
        // FECHA DE PAGO
        // =====================================================

        $fechaRecibida =
            trim(
                (string) (
                    $datos["fecha_pago"] ?? ""
                )
            );


        try {

            if ($fechaRecibida === "") {

                $fechaPago =
                    new DateTimeImmutable();

            } else {

                $fechaBase =
                    DateTimeImmutable::createFromFormat(
                        "!Y-m-d",
                        $fechaRecibida
                    );


                $errores =
                    DateTimeImmutable::getLastErrors();


                if (
                    !$fechaBase ||
                    (
                        is_array($errores) &&
                        (
                            $errores["warning_count"] > 0 ||
                            $errores["error_count"] > 0
                        )
                    )
                ) {

                    return [
                        "success" => false,
                        "message" =>
                            "La fecha de pago no es válida"
                    ];
                }


                $ahora =
                    new DateTimeImmutable();


                $fechaPago =
                    $fechaBase->setTime(
                        (int) $ahora->format("H"),
                        (int) $ahora->format("i"),
                        (int) $ahora->format("s")
                    );
            }


        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                    "La fecha de pago no es válida"
            ];
        }


        // =====================================================
        // TRANSACCIÓN
        // =====================================================

        try {

            $this->pago
                ->iniciarTransaccion();


            // =================================================
            // BLOQUEAR PRÉSTAMO
            // =================================================

            $prestamoBloqueado =
                $this->pago
                    ->obtenerPrestamoParaActualizar(
                        $idPrestamo
                    );


            if (!$prestamoBloqueado) {

                throw new Exception(
                    "Préstamo no encontrado"
                );
            }


            // =================================================
            // CONSULTAR DATOS COMPLETOS
            // =================================================

            $prestamo =
                $this->pago
                    ->obtenerPrestamoPorId(
                        $idPrestamo
                    );


            if (!$prestamo) {

                throw new Exception(
                    "No fue posible consultar el préstamo"
                );
            }


            $estadoPrestamo =
                strtolower(
                    trim(
                        (string) $prestamo[
                            "estado_prestamo"
                        ]
                    )
                );


            // =================================================
            // SOLO ACTIVOS O ATRASADOS
            // =================================================

            if (
                $estadoPrestamo !== "activo" &&
                $estadoPrestamo !== "atrasado"
            ) {

                throw new Exception(
                    "Este préstamo no está disponible para recibir pagos"
                );
            }


            // =================================================
            // BLOQUEAR CUOTA
            // =================================================

            $cuota =
                $this->pago
                    ->obtenerCuotaParaActualizar(
                        $idCuota
                    );


            if (!$cuota) {

                throw new Exception(
                    "Cuota no encontrada"
                );
            }


            // =================================================
            // COMPROBAR QUE PERTENEZCA AL PRÉSTAMO
            // =================================================

            if (
                (int) $cuota["id_prestamo"] !==
                $idPrestamo
            ) {

                throw new Exception(
                    "La cuota seleccionada no pertenece a este préstamo"
                );
            }


            // =================================================
            // COMPROBAR QUE NO ESTÉ PAGADA
            // =================================================

            if (
                strtolower(
                    (string) $cuota["estatus"]
                ) === "pagada"
            ) {

                throw new Exception(
                    "Esta cuota ya está pagada"
                );
            }


            // =================================================
            // SALDO ACTUAL
            // =================================================

            $saldoActual =
                round(
                    (float) $cuota[
                        "saldo_pendiente"
                    ],
                    2
                );


            $pagadoActual =
                round(
                    (float) $cuota[
                        "monto_pagado"
                    ],
                    2
                );


            if ($saldoActual <= 0) {

                throw new Exception(
                    "Esta cuota ya no tiene saldo pendiente"
                );
            }


            // =================================================
            // EVITAR PAGAR DE MÁS
            // =================================================

            if (
                $monto >
                $saldoActual
            ) {

                throw new Exception(
                    "El pago no puede ser mayor al saldo pendiente de " .
                    number_format(
                        $saldoActual,
                        2
                    )
                );
            }


            // =================================================
            // CALCULAR NUEVOS SALDOS
            // =================================================

            $nuevoMontoPagado =
                round(
                    $pagadoActual +
                    $monto,
                    2
                );


            $nuevoSaldo =
                round(
                    $saldoActual -
                    $monto,
                    2
                );


            if (
                abs($nuevoSaldo) < 0.01
            ) {

                $nuevoSaldo =
                    0.00;
            }


            // =================================================
            // ESTADO DE LA CUOTA
            // =================================================

            if ($nuevoSaldo <= 0) {

                $nuevoSaldo =
                    0.00;


                $estatusCuota =
                    "pagada";


                $fechaPagoCuota =
                    $fechaPago->format(
                        "Y-m-d H:i:s"
                    );


            } else {

                $estatusCuota =
                    "parcial";


                $fechaPagoCuota =
                    null;
            }


            // =================================================
            // REGISTRAR PAGO
            // =================================================

            $idPago =
                $this->pago
                    ->crearPago([

                        "id_prestamo" =>
                            $idPrestamo,

                        "id_cuota" =>
                            $idCuota,

                        "id_cliente" =>
                            (int) $prestamo[
                                "id_cliente"
                            ],

                        "id_metodo_pago" =>
                            $idMetodoPago,

                        "monto" =>
                            $monto,

                        "referencia" =>
                            $referencia,

                        "comprobante" =>
                            $comprobante,

                        "fecha_pago" =>
                            $fechaPago->format(
                                "Y-m-d H:i:s"
                            ),

                        "registrado_por" =>
                            $idUsuario,

                        "observaciones" =>
                            $observaciones
                    ]);


            // =================================================
            // ACTUALIZAR CUOTA
            // =================================================

            $resultadoCuota =
                $this->pago
                    ->actualizarCuota(

                        $idCuota,

                        $nuevoMontoPagado,

                        $nuevoSaldo,

                        $estatusCuota,

                        $fechaPagoCuota
                    );


            if (!$resultadoCuota) {

                throw new Exception(
                    "No fue posible actualizar la cuota"
                );
            }


            // =================================================
            // COMPROBAR SI YA SE PAGÓ TODO EL PRÉSTAMO
            // =================================================

            $cuotasConSaldo =
                $this->pago
                    ->contarCuotasConSaldo(
                        $idPrestamo
                    );


            $prestamoLiquidado =
                false;


            if ($cuotasConSaldo === 0) {

                $estadoLiquidado =
                    $this->pago
                        ->obtenerEstadoPrestamoPorNombre(
                            "liquidado"
                        );


                if (!$estadoLiquidado) {

                    throw new Exception(
                        "No existe el estado liquidado para préstamos"
                    );
                }


                $resultadoLiquidacion =
                    $this->pago
                        ->liquidarPrestamo(
                            $idPrestamo,
                            (int) $estadoLiquidado[
                                "id_estado_prestamo"
                            ]
                        );


                if (!$resultadoLiquidacion) {

                    throw new Exception(
                        "No fue posible liquidar el préstamo"
                    );
                }


                $prestamoLiquidado =
                    true;
            }


            // =================================================
            // CONFIRMAR
            // =================================================

            $this->pago
                ->confirmarTransaccion();


            return [
                "success" => true,

                "message" =>
                    $estatusCuota === "pagada"
                        ? "Pago registrado y cuota liquidada correctamente"
                        : "Pago parcial registrado correctamente",

                "data" => [

                    "id_pago" =>
                        $idPago,

                    "id_prestamo" =>
                        $idPrestamo,

                    "id_cuota" =>
                        $idCuota,

                    "numero_cuota" =>
                        (int) $cuota[
                            "numero_cuota"
                        ],

                    "monto_pagado_ahora" =>
                        $monto,

                    "monto_pagado_cuota" =>
                        $nuevoMontoPagado,

                    "saldo_pendiente_cuota" =>
                        $nuevoSaldo,

                    "estatus_cuota" =>
                        $estatusCuota,

                    "prestamo_liquidado" =>
                        $prestamoLiquidado
                ]
            ];


        } catch (Throwable $e) {

            $this->pago
                ->cancelarTransaccion();


            return [
                "success" => false,
                "message" =>
                    $e->getMessage()
            ];
        }
    }


    // =========================================================
    // HISTORIAL DE PAGOS
    // =========================================================

    public function listarHistorial()
    {
        try {

            $pagos =
                $this->pago
                    ->obtenerHistorialPagos();


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
    // OBTENER PAGO POR ID
    // =========================================================

    public function obtenerPago($idPago)
    {
        if (
            !$idPago ||
            !is_numeric($idPago)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Pago inválido"
            ];
        }


        $idPago =
            (int) $idPago;


        try {

            $pago =
                $this->pago
                    ->obtenerPagoPorId(
                        $idPago
                    );


            if (!$pago) {

                return [
                    "success" => false,
                    "message" =>
                        "Pago no encontrado"
                ];
            }


            return [
                "success" => true,
                "data" => $pago
            ];


        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible consultar el pago"
            ];
        }
    }


    // =========================================================
    // RESUMEN GENERAL DE PAGOS
    // =========================================================

    public function obtenerResumen()
    {
        try {

            $resumen =
                $this->pago
                    ->obtenerResumenPagos();


            return [
                "success" => true,

                "data" => [

                    "total_pagos" =>
                        (int) (
                            $resumen[
                                "total_pagos"
                            ] ?? 0
                        ),

                    "total_pagado" =>
                        round(
                            (float) (
                                $resumen[
                                    "total_pagado"
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
                        ),

                    "total_por_cobrar" =>
                        round(
                            (float) (
                                $resumen[
                                    "total_por_cobrar"
                                ] ?? 0
                            ),
                            2
                        )
                ]
            ];


        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible obtener el resumen de pagos"
            ];
        }
    }
}