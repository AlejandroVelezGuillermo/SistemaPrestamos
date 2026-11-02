<?php

require_once __DIR__ . "/../models/Prestamo.php";
require_once __DIR__ . "/../models/Solicitud.php";

class PrestamoController
{
    private $prestamo;
    private $solicitud;

    public function __construct()
    {
        $this->prestamo =
            new Prestamo();

        $this->solicitud =
            new Solicitud();
    }


    // =========================================================
    // LISTAR PRÉSTAMOS
    // =========================================================

    public function listar()
    {
        return [
            "success" => true,
            "data" =>
                $this->prestamo->obtenerTodos()
        ];
    }


    // =========================================================
    // OBTENER PRÉSTAMO
    // =========================================================

    public function obtener($idPrestamo)
    {
        if (
            !$idPrestamo ||
            !is_numeric($idPrestamo)
        ) {
            return [
                "success" => false,
                "message" => "Préstamo inválido"
            ];
        }


        $prestamo =
            $this->prestamo->obtenerPorId(
                (int) $idPrestamo
            );


        if (!$prestamo) {
            return [
                "success" => false,
                "message" => "Préstamo no encontrado"
            ];
        }


        return [
            "success" => true,
            "data" => $prestamo
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
                $this->prestamo
                    ->obtenerMetodosPago()
        ];
    }


    // =========================================================
    // LISTAR CUOTAS DE UN PRÉSTAMO
    // =========================================================

    public function listarCuotas($idPrestamo)
    {
        if (
            !$idPrestamo ||
            !is_numeric($idPrestamo)
        ) {
            return [
                "success" => false,
                "message" => "Préstamo inválido"
            ];
        }


        $prestamo =
            $this->prestamo->obtenerPorId(
                (int) $idPrestamo
            );


        if (!$prestamo) {
            return [
                "success" => false,
                "message" => "Préstamo no encontrado"
            ];
        }


        return [
            "success" => true,
            "data" =>
                $this->prestamo->obtenerCuotas(
                    (int) $idPrestamo
                )
        ];
    }


    // =========================================================
    // OBTENER DESEMBOLSO
    // =========================================================

    public function obtenerDesembolso($idPrestamo)
    {
        if (
            !$idPrestamo ||
            !is_numeric($idPrestamo)
        ) {
            return [
                "success" => false,
                "message" => "Préstamo inválido"
            ];
        }


        return [
            "success" => true,
            "data" =>
                $this->prestamo
                    ->obtenerDesembolso(
                        (int) $idPrestamo
                    )
        ];
    }


    // =========================================================
    // CREAR PRÉSTAMO DESDE SOLICITUD
    // =========================================================

    public function crearDesdeSolicitud(
        $idSolicitud,
        $idUsuarioAprobador,
        $datos
    )
    {
        if (
            !$idSolicitud ||
            !is_numeric($idSolicitud)
        ) {
            return [
                "success" => false,
                "message" => "Solicitud inválida"
            ];
        }


        if (
            !$idUsuarioAprobador ||
            !is_numeric($idUsuarioAprobador)
        ) {
            return [
                "success" => false,
                "message" => "Usuario aprobador inválido"
            ];
        }


        $idSolicitud =
            (int) $idSolicitud;

        $idUsuarioAprobador =
            (int) $idUsuarioAprobador;


        // =====================================================
        // OBTENER SOLICITUD
        // =====================================================

        $solicitud =
            $this->solicitud->obtenerPorId(
                $idSolicitud
            );


        if (!$solicitud) {
            return [
                "success" => false,
                "message" => "Solicitud no encontrada"
            ];
        }


        if (
            $solicitud["estatus"] !== "activo"
        ) {
            return [
                "success" => false,
                "message" => "La solicitud no está activa"
            ];
        }


        if (
            $solicitud["estado_solicitud"] !==
            "en_revision"
        ) {
            return [
                "success" => false,
                "message" =>
                    "La solicitud debe estar en revisión antes de aprobarla"
            ];
        }


        if (
            $this->prestamo
                ->existePorSolicitud(
                    $idSolicitud
                )
        ) {
            return [
                "success" => false,
                "message" =>
                    "Esta solicitud ya tiene un préstamo registrado"
            ];
        }


        // =====================================================
        // INTERÉS
        // =====================================================

        $tasaInteres =
            $datos["tasa_interes"] ?? null;


        if (!is_numeric($tasaInteres)) {
            return [
                "success" => false,
                "message" =>
                    "Ingresa una tasa de interés válida"
            ];
        }


        $tasaInteres =
            round(
                (float) $tasaInteres,
                2
            );


        if (
            $tasaInteres < 0 ||
            $tasaInteres > 100
        ) {
            return [
                "success" => false,
                "message" =>
                    "La tasa de interés debe estar entre 0% y 100%"
            ];
        }


        // =====================================================
        // AVAL Y GARANTÍA
        // =====================================================

        $requiereAval =
            !empty(
                $datos["requiere_aval"]
            ) ? 1 : 0;


        $requiereGarantia =
            !empty(
                $datos["requiere_garantia"]
            ) ? 1 : 0;


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
        // DATOS FINANCIEROS
        // =====================================================

        $montoAprobado =
            round(
                (float) $solicitud[
                    "monto_solicitado"
                ],
                2
            );


        $plazoMeses =
            (int) $solicitud[
                "plazo_meses"
            ];


        $frecuenciaPago =
            $solicitud[
                "frecuencia_pago"
            ];


        $totalIntereses =
            round(
                $montoAprobado *
                ($tasaInteres / 100),
                2
            );


        $totalPagar =
            round(
                $montoAprobado +
                $totalIntereses,
                2
            );


        $numeroCuotas =
            $this->calcularNumeroCuotas(
                $plazoMeses,
                $frecuenciaPago
            );


        if ($numeroCuotas <= 0) {
            return [
                "success" => false,
                "message" =>
                    "No fue posible calcular el número de cuotas"
            ];
        }


        $montoCuota =
            round(
                $totalPagar /
                $numeroCuotas,
                2
            );


        // =====================================================
        // ESTADOS
        // =====================================================

        $estadoPrestamo =
            $this->prestamo
                ->obtenerEstadoPorNombre(
                    "pendiente"
                );


        if (!$estadoPrestamo) {
            return [
                "success" => false,
                "message" =>
                    "No existe el estado pendiente para préstamos"
            ];
        }


        $estadoSolicitudAprobada =
            $this->solicitud
                ->obtenerEstadoPorNombre(
                    "aprobada"
                );


        if (!$estadoSolicitudAprobada) {
            return [
                "success" => false,
                "message" =>
                    "No existe el estado aprobada para solicitudes"
            ];
        }


        // =====================================================
        // TRANSACCIÓN
        // =====================================================

        try {

            $this->prestamo
                ->iniciarTransaccion();


            $idPrestamo =
                $this->prestamo->crear([

                    "id_solicitud" =>
                        $idSolicitud,

                    "id_cliente" =>
                        (int) $solicitud[
                            "id_usuario"
                        ],

                    "id_tipo_prestamo" =>
                        (int) $solicitud[
                            "id_tipo_prestamo"
                        ],

                    "id_estado_prestamo" =>
                        (int) $estadoPrestamo[
                            "id_estado_prestamo"
                        ],

                    "monto_aprobado" =>
                        $montoAprobado,

                    "tasa_interes" =>
                        $tasaInteres,

                    "total_intereses" =>
                        $totalIntereses,

                    "total_pagar" =>
                        $totalPagar,

                    "plazo_meses" =>
                        $plazoMeses,

                    "frecuencia_pago" =>
                        $frecuenciaPago,

                    "numero_cuotas" =>
                        $numeroCuotas,

                    "monto_cuota" =>
                        $montoCuota,

                    "requiere_aval" =>
                        $requiereAval,

                    "requiere_garantia" =>
                        $requiereGarantia,

                    "aprobado_por" =>
                        $idUsuarioAprobador,

                    "observaciones" =>
                        $observaciones
                ]);


            $resultadoSolicitud =
                $this->solicitud
                    ->cambiarEstado(
                        $idSolicitud,
                        (int) $estadoSolicitudAprobada[
                            "id_estado_solicitud"
                        ],
                        $idUsuarioAprobador,
                        $observaciones
                    );


            if (!$resultadoSolicitud) {
                throw new Exception(
                    "No fue posible actualizar la solicitud"
                );
            }


            $this->prestamo
                ->confirmarTransaccion();


            return [
                "success" => true,

                "message" =>
                    "Solicitud aprobada y préstamo generado correctamente",

                "id_prestamo" =>
                    $idPrestamo,

                "calculos" => [

                    "monto_aprobado" =>
                        $montoAprobado,

                    "tasa_interes" =>
                        $tasaInteres,

                    "total_intereses" =>
                        $totalIntereses,

                    "total_pagar" =>
                        $totalPagar,

                    "numero_cuotas" =>
                        $numeroCuotas,

                    "monto_cuota" =>
                        $montoCuota,

                    "frecuencia_pago" =>
                        $frecuenciaPago
                ]
            ];


        } catch (Throwable $e) {

            $this->prestamo
                ->cancelarTransaccion();


            return [
                "success" => false,
                "message" =>
                    "No fue posible generar el préstamo"
            ];
        }
    }


    // =========================================================
    // DESEMBOLSAR PRÉSTAMO
    // =========================================================

    public function desembolsar(
        $idPrestamo,
        $idUsuario,
        $datos
    )
    {
        if (
            !$idPrestamo ||
            !is_numeric($idPrestamo)
        ) {
            return [
                "success" => false,
                "message" => "Préstamo inválido"
            ];
        }


        if (
            !$idUsuario ||
            !is_numeric($idUsuario)
        ) {
            return [
                "success" => false,
                "message" =>
                    "No fue posible identificar al usuario"
            ];
        }


        $idPrestamo =
            (int) $idPrestamo;

        $idUsuario =
            (int) $idUsuario;


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


        $metodoPago =
            $this->prestamo
                ->obtenerMetodoPagoPorId(
                    (int) $idMetodoPago
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
        // FECHA DEL DESEMBOLSO
        // =====================================================

        $fechaRecibida =
            trim(
                (string) (
                    $datos["fecha_desembolso"] ?? ""
                )
            );


        try {

            if ($fechaRecibida === "") {

                $fechaDesembolso =
                    new DateTimeImmutable();

            } else {

                /*
                 * El frontend enviará normalmente:
                 * 2026-08-27
                 */

                $fechaBase =
                    DateTimeImmutable::createFromFormat(
                        "!Y-m-d",
                        $fechaRecibida
                    );


                $erroresFecha =
                    DateTimeImmutable::getLastErrors();


                if (
                    !$fechaBase ||
                    (
                        is_array($erroresFecha) &&
                        (
                            $erroresFecha[
                                "warning_count"
                            ] > 0 ||
                            $erroresFecha[
                                "error_count"
                            ] > 0
                        )
                    )
                ) {
                    return [
                        "success" => false,
                        "message" =>
                            "La fecha de desembolso no es válida"
                    ];
                }


                /*
                 * Guardamos también la hora actual.
                 */

                $ahora =
                    new DateTimeImmutable();


                $fechaDesembolso =
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
                    "La fecha de desembolso no es válida"
            ];
        }


        // =====================================================
        // TRANSACCIÓN
        // =====================================================

        try {

            $this->prestamo
                ->iniciarTransaccion();


            /*
             * Bloqueamos la fila del préstamo.
             * Esto ayuda a evitar un doble desembolso
             * si se presiona dos veces al mismo tiempo.
             */

            $prestamo =
                $this->prestamo
                    ->obtenerPorIdParaActualizar(
                        $idPrestamo
                    );


            if (!$prestamo) {

                throw new Exception(
                    "Préstamo no encontrado"
                );
            }


            // =================================================
            // VALIDAR ESTADO
            // =================================================

            if (
                $prestamo["estatus"] !==
                "pendiente"
            ) {

                throw new Exception(
                    "Este préstamo ya no está pendiente de desembolso"
                );
            }


            $estadoActual =
                $this->prestamo
                    ->obtenerEstadoPorNombre(
                        "pendiente"
                    );


            if (
                !$estadoActual ||
                (int) $prestamo[
                    "id_estado_prestamo"
                ] !==
                (int) $estadoActual[
                    "id_estado_prestamo"
                ]
            ) {

                throw new Exception(
                    "El préstamo no se encuentra pendiente de desembolso"
                );
            }


            // =================================================
            // EVITAR DOBLE DESEMBOLSO
            // =================================================

            if (
                $this->prestamo
                    ->tieneDesembolsoRealizado(
                        $idPrestamo
                    )
            ) {

                throw new Exception(
                    "Este préstamo ya tiene un desembolso registrado"
                );
            }


            // =================================================
            // EVITAR CUOTAS DUPLICADAS
            // =================================================

            if (
                $this->prestamo
                    ->contarCuotas(
                        $idPrestamo
                    ) > 0
            ) {

                throw new Exception(
                    "Este préstamo ya tiene cuotas generadas"
                );
            }


            // =================================================
            // DATOS DEL PRÉSTAMO
            // =================================================

            $montoAprobado =
                round(
                    (float) $prestamo[
                        "monto_aprobado"
                    ],
                    2
                );


            $totalIntereses =
                round(
                    (float) $prestamo[
                        "total_intereses"
                    ],
                    2
                );


            $totalPagar =
                round(
                    (float) $prestamo[
                        "total_pagar"
                    ],
                    2
                );


            $numeroCuotas =
                (int) $prestamo[
                    "numero_cuotas"
                ];


            $frecuencia =
                $prestamo[
                    "frecuencia_pago"
                ];


            if (
                $montoAprobado <= 0 ||
                $totalPagar <= 0 ||
                $numeroCuotas <= 0
            ) {

                throw new Exception(
                    "Los datos financieros del préstamo no son válidos"
                );
            }


            // =================================================
            // FECHA PRIMERA CUOTA
            // =================================================

            $fechaPrimeraCuota =
                $this->calcularFechaCuota(
                    $fechaDesembolso,
                    $frecuencia,
                    1
                );


            // =================================================
            // FECHA ÚLTIMA CUOTA
            // =================================================

            $fechaUltimaCuota =
                $this->calcularFechaCuota(
                    $fechaDesembolso,
                    $frecuencia,
                    $numeroCuotas
                );


            // =================================================
            // ESTADO ACTIVO
            // =================================================

            $estadoActivo =
                $this->prestamo
                    ->obtenerEstadoPorNombre(
                        "activo"
                    );


            if (!$estadoActivo) {

                throw new Exception(
                    "No existe el estado activo para préstamos"
                );
            }


            // =================================================
            // REGISTRAR DESEMBOLSO
            // =================================================

            $idDesembolso =
                $this->prestamo
                    ->crearDesembolso([

                        "id_prestamo" =>
                            $idPrestamo,

                        "id_metodo_pago" =>
                            (int) $idMetodoPago,

                        /*
                         * Se desembolsa el capital aprobado,
                         * no el total con intereses.
                         */
                        "monto" =>
                            $montoAprobado,

                        "referencia" =>
                            $referencia,

                        "comprobante" =>
                            $comprobante,

                        "fecha_desembolso" =>
                            $fechaDesembolso->format(
                                "Y-m-d H:i:s"
                            ),

                        "realizado_por" =>
                            $idUsuario,

                        "observaciones" =>
                            $observaciones
                    ]);


            // =================================================
            // GENERAR CUOTAS
            // =================================================

            $capitalAcumulado =
                0.00;

            $interesAcumulado =
                0.00;

            $totalAcumulado =
                0.00;


            $capitalBase =
                round(
                    $montoAprobado /
                    $numeroCuotas,
                    2
                );


            $interesBase =
                round(
                    $totalIntereses /
                    $numeroCuotas,
                    2
                );


            for (
                $numero = 1;
                $numero <= $numeroCuotas;
                $numero++
            ) {

                // ---------------------------------------------
                // ÚLTIMA CUOTA
                // ---------------------------------------------

                if (
                    $numero ===
                    $numeroCuotas
                ) {

                    /*
                     * La última cuota absorbe cualquier
                     * diferencia causada por redondeo.
                     */

                    $montoCapital =
                        round(
                            $montoAprobado -
                            $capitalAcumulado,
                            2
                        );


                    $montoInteres =
                        round(
                            $totalIntereses -
                            $interesAcumulado,
                            2
                        );


                } else {

                    $montoCapital =
                        $capitalBase;


                    $montoInteres =
                        $interesBase;
                }


                $montoTotal =
                    round(
                        $montoCapital +
                        $montoInteres,
                        2
                    );


                /*
                 * Seguridad extra:
                 * hacemos que la última cuota cierre
                 * exactamente el total a pagar.
                 */

                if (
                    $numero ===
                    $numeroCuotas
                ) {

                    $montoTotal =
                        round(
                            $totalPagar -
                            $totalAcumulado,
                            2
                        );


                    /*
                     * El interés se mantiene y el capital
                     * absorbe cualquier centavo final.
                     */

                    $montoCapital =
                        round(
                            $montoTotal -
                            $montoInteres,
                            2
                        );
                }


                // ---------------------------------------------
                // FECHA DE LA CUOTA
                // ---------------------------------------------

                $fechaCuota =
                    $this->calcularFechaCuota(
                        $fechaDesembolso,
                        $frecuencia,
                        $numero
                    );


                // ---------------------------------------------
                // INSERT
                // ---------------------------------------------

                $resultadoCuota =
                    $this->prestamo
                        ->crearCuota([

                            "id_prestamo" =>
                                $idPrestamo,

                            "numero_cuota" =>
                                $numero,

                            "fecha_vencimiento" =>
                                $fechaCuota->format(
                                    "Y-m-d"
                                ),

                            "monto_capital" =>
                                $montoCapital,

                            "monto_interes" =>
                                $montoInteres,

                            "monto_total" =>
                                $montoTotal
                        ]);


                if (!$resultadoCuota) {

                    throw new Exception(
                        "No fue posible generar la cuota #" .
                        $numero
                    );
                }


                $capitalAcumulado =
                    round(
                        $capitalAcumulado +
                        $montoCapital,
                        2
                    );


                $interesAcumulado =
                    round(
                        $interesAcumulado +
                        $montoInteres,
                        2
                    );


                $totalAcumulado =
                    round(
                        $totalAcumulado +
                        $montoTotal,
                        2
                    );
            }


            // =================================================
            // VALIDACIÓN FINAL
            // =================================================

            if (
                abs(
                    $totalAcumulado -
                    $totalPagar
                ) > 0.01
            ) {

                throw new Exception(
                    "El total de las cuotas no coincide con el total del préstamo"
                );
            }


            // =================================================
            // ACTIVAR PRÉSTAMO
            // =================================================

            $resultadoPrestamo =
                $this->prestamo
                    ->activarPrestamo(
                        $idPrestamo,

                        (int) $estadoActivo[
                            "id_estado_prestamo"
                        ],

                        $fechaDesembolso->format(
                            "Y-m-d H:i:s"
                        ),

                        $fechaPrimeraCuota->format(
                            "Y-m-d"
                        ),

                        $fechaUltimaCuota->format(
                            "Y-m-d"
                        )
                    );


            if (!$resultadoPrestamo) {

                throw new Exception(
                    "No fue posible activar el préstamo"
                );
            }


            // =================================================
            // COMMIT
            // =================================================

            $this->prestamo
                ->confirmarTransaccion();


            return [
                "success" => true,

                "message" =>
                    "Desembolso registrado y cuotas generadas correctamente",

                "data" => [

                    "id_prestamo" =>
                        $idPrestamo,

                    "id_desembolso" =>
                        $idDesembolso,

                    "monto_desembolsado" =>
                        $montoAprobado,

                    "numero_cuotas" =>
                        $numeroCuotas,

                    "fecha_desembolso" =>
                        $fechaDesembolso->format(
                            "Y-m-d H:i:s"
                        ),

                    "fecha_inicio_pago" =>
                        $fechaPrimeraCuota->format(
                            "Y-m-d"
                        ),

                    "fecha_vencimiento" =>
                        $fechaUltimaCuota->format(
                            "Y-m-d"
                        ),

                    "total_pagar" =>
                        $totalPagar
                ]
            ];


        } catch (Throwable $e) {

            $this->prestamo
                ->cancelarTransaccion();


            return [
                "success" => false,
                "message" =>
                    $e->getMessage()
            ];
        }
    }


    // =========================================================
    // CALCULAR NÚMERO DE CUOTAS
    // =========================================================

    private function calcularNumeroCuotas(
        $plazoMeses,
        $frecuenciaPago
    )
    {
        if ($plazoMeses <= 0) {
            return 0;
        }


        switch ($frecuenciaPago) {

            case "mensual":

                return $plazoMeses;


            case "quincenal":

                return $plazoMeses * 2;


            case "semanal":

                return max(
                    1,
                    (int) round(
                        $plazoMeses *
                        (52 / 12)
                    )
                );


            default:

                return 0;
        }
    }


    // =========================================================
    // CALCULAR FECHA DE UNA CUOTA
    // =========================================================

    private function calcularFechaCuota(
        DateTimeImmutable $fechaDesembolso,
        $frecuencia,
        $numeroCuota
    )
    {
        switch ($frecuencia) {

            // -------------------------------------------------
            // SEMANAL
            // -------------------------------------------------

            case "semanal":

                return $fechaDesembolso
                    ->modify(
                        "+" .
                        (7 * $numeroCuota) .
                        " days"
                    );


            // -------------------------------------------------
            // QUINCENAL
            // -------------------------------------------------

            case "quincenal":

                return $fechaDesembolso
                    ->modify(
                        "+" .
                        (15 * $numeroCuota) .
                        " days"
                    );


            // -------------------------------------------------
            // MENSUAL
            // -------------------------------------------------

            case "mensual":

                return $this->sumarMesesSeguro(
                    $fechaDesembolso,
                    $numeroCuota
                );


            default:

                throw new Exception(
                    "Frecuencia de pago no válida"
                );
        }
    }


    // =========================================================
    // SUMAR MESES SIN PROBLEMAS DE FIN DE MES
    //
    // Ejemplo:
    // 31/01 + 1 mes
    // se convierte en el último día válido de febrero.
    // =========================================================

    private function sumarMesesSeguro(
        DateTimeImmutable $fecha,
        $meses
    )
    {
        $diaOriginal =
            (int) $fecha->format("d");


        /*
         * Nos movemos al primer día del mes original.
         */

        $primerDia =
            $fecha->setDate(
                (int) $fecha->format("Y"),
                (int) $fecha->format("m"),
                1
            );


        /*
         * Después avanzamos la cantidad de meses.
         */

        $mesDestino =
            $primerDia->modify(
                "+" .
                (int) $meses .
                " months"
            );


        $ultimoDiaDestino =
            (int) $mesDestino->format("t");


        /*
         * Si el día original no existe en el mes destino,
         * usamos el último día del mes.
         */

        $diaDestino =
            min(
                $diaOriginal,
                $ultimoDiaDestino
            );


        return $mesDestino->setDate(
            (int) $mesDestino->format("Y"),
            (int) $mesDestino->format("m"),
            $diaDestino
        );
    }
}