<?php

require_once __DIR__ . "/../models/Solicitud.php";
require_once __DIR__ . "/../models/Cliente.php";

class SolicitudController
{
    private $solicitud;
    private $cliente;

    public function __construct()
    {
        $this->solicitud =
            new Solicitud();

        $this->cliente =
            new Cliente();
    }


    // =========================================================
    // LISTAR
    // =========================================================

    public function listar()
    {
        return [
            "success" => true,
            "data" =>
                $this->solicitud->obtenerTodas()
        ];
    }


    // =========================================================
    // OBTENER POR ID
    // =========================================================

    public function obtener($idSolicitud)
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


        $solicitud =
            $this->solicitud->obtenerPorId(
                (int) $idSolicitud
            );


        if (!$solicitud) {

            return [
                "success" => false,
                "message" => "Solicitud no encontrada"
            ];
        }


        return [
            "success" => true,
            "data" => $solicitud
        ];
    }


    // =========================================================
    // LISTAR TIPOS
    // =========================================================

    public function listarTipos()
    {
        return [
            "success" => true,
            "data" =>
                $this->solicitud
                    ->obtenerTiposPrestamo()
        ];
    }


    // =========================================================
    // CREAR SOLICITUD
    // =========================================================

    public function crear($datos)
    {
        $idUsuario =
            $datos["id_usuario"] ?? null;

        $idTipoPrestamo =
            $datos["id_tipo_prestamo"] ?? null;

        $montoSolicitado =
            $datos["monto_solicitado"] ?? null;

        $plazoMeses =
            $datos["plazo_meses"] ?? null;

        $frecuenciaPago =
            trim(
                $datos["frecuencia_pago"] ?? ""
            );

        $motivo =
            trim(
                $datos["motivo"] ?? ""
            );

        $observaciones =
            trim(
                $datos["observaciones"] ?? ""
            );


        // =====================================================
        // VALIDAR CLIENTE
        // =====================================================

        if (
            !$idUsuario ||
            !is_numeric($idUsuario)
        ) {

            return [
                "success" => false,
                "message" => "Selecciona un cliente válido"
            ];
        }


        $cliente =
            $this->cliente->obtenerPorId(
                (int) $idUsuario
            );


        if (!$cliente) {

            return [
                "success" => false,
                "message" => "Cliente no encontrado"
            ];
        }


        if ($cliente["estatus"] !== "activo") {

            return [
                "success" => false,
                "message" => "El cliente debe estar activo para solicitar un préstamo"
            ];
        }


        // =====================================================
        // VALIDAR TIPO DE PRÉSTAMO
        // =====================================================

        if (
            !$idTipoPrestamo ||
            !is_numeric($idTipoPrestamo)
        ) {

            return [
                "success" => false,
                "message" => "Selecciona un tipo de préstamo"
            ];
        }


        $tipoPrestamo =
            $this->solicitud
                ->obtenerTipoPrestamoPorId(
                    (int) $idTipoPrestamo
                );


        if (
            !$tipoPrestamo ||
            $tipoPrestamo["estatus"] !== "activo"
        ) {

            return [
                "success" => false,
                "message" => "El tipo de préstamo no está disponible"
            ];
        }


        // =====================================================
        // VALIDAR MONTO
        // =====================================================

        if (
            !is_numeric($montoSolicitado) ||
            (float) $montoSolicitado <= 0
        ) {

            return [
                "success" => false,
                "message" => "Ingresa un monto válido"
            ];
        }


        $montoSolicitado =
            round(
                (float) $montoSolicitado,
                2
            );


        if (
            $montoSolicitado <
            (float) $tipoPrestamo["monto_minimo"]
        ) {

            return [
                "success" => false,
                "message" =>
                    "El monto mínimo permitido es $" .
                    number_format(
                        $tipoPrestamo["monto_minimo"],
                        2
                    )
            ];
        }


        if (
            $montoSolicitado >
            (float) $tipoPrestamo["monto_maximo"]
        ) {

            return [
                "success" => false,
                "message" =>
                    "El monto máximo permitido es $" .
                    number_format(
                        $tipoPrestamo["monto_maximo"],
                        2
                    )
            ];
        }


        // =====================================================
        // VALIDAR PLAZO
        // =====================================================

        if (
            !is_numeric($plazoMeses) ||
            (int) $plazoMeses <= 0
        ) {

            return [
                "success" => false,
                "message" => "Ingresa un plazo válido"
            ];
        }


        $plazoMeses =
            (int) $plazoMeses;


        if (
            $plazoMeses <
            (int) $tipoPrestamo["plazo_minimo"]
        ) {

            return [
                "success" => false,
                "message" =>
                    "El plazo mínimo es de " .
                    $tipoPrestamo["plazo_minimo"] .
                    " mes(es)"
            ];
        }


        if (
            $plazoMeses >
            (int) $tipoPrestamo["plazo_maximo"]
        ) {

            return [
                "success" => false,
                "message" =>
                    "El plazo máximo es de " .
                    $tipoPrestamo["plazo_maximo"] .
                    " meses"
            ];
        }


        // =====================================================
        // VALIDAR FRECUENCIA
        // =====================================================

        $frecuenciasPermitidas = [
            "semanal",
            "quincenal",
            "mensual"
        ];


        if (
            !in_array(
                $frecuenciaPago,
                $frecuenciasPermitidas,
                true
            )
        ) {

            return [
                "success" => false,
                "message" => "Selecciona una frecuencia de pago válida"
            ];
        }


        // =====================================================
        // ESTADO PENDIENTE
        // =====================================================

        $estadoPendiente =
            $this->solicitud
                ->obtenerEstadoPendiente();


        if (!$estadoPendiente) {

            return [
                "success" => false,
                "message" => "No existe el estado pendiente en la configuración"
            ];
        }


        // =====================================================
        // CREAR
        // =====================================================

        $idSolicitud =
            $this->solicitud->crear([

                "id_usuario" =>
                    (int) $idUsuario,

                "id_tipo_prestamo" =>
                    (int) $idTipoPrestamo,

                "id_estado_solicitud" =>
                    (int) $estadoPendiente[
                        "id_estado_solicitud"
                    ],

                "monto_solicitado" =>
                    $montoSolicitado,

                "plazo_meses" =>
                    $plazoMeses,

                "frecuencia_pago" =>
                    $frecuenciaPago,

                "motivo" =>
                    $motivo !== ""
                        ? $motivo
                        : null,

                "observaciones" =>
                    $observaciones !== ""
                        ? $observaciones
                        : null
            ]);


        return [
            "success" => true,
            "message" => "Solicitud registrada correctamente",
            "id_solicitud" => $idSolicitud
        ];
    }


    // =========================================================
    // PONER SOLICITUD EN REVISIÓN
    // =========================================================

    public function ponerEnRevision(
        $idSolicitud,
        $idUsuarioRevisor,
        $observaciones = null
    )
    {
        // -----------------------------------------------------
        // VALIDAR ID DE SOLICITUD
        // -----------------------------------------------------

        if (
            !$idSolicitud ||
            !is_numeric($idSolicitud)
        ) {

            return [
                "success" => false,
                "message" => "Solicitud inválida"
            ];
        }


        // -----------------------------------------------------
        // VALIDAR USUARIO REVISOR
        // -----------------------------------------------------

        if (
            !$idUsuarioRevisor ||
            !is_numeric($idUsuarioRevisor)
        ) {

            return [
                "success" => false,
                "message" => "Usuario revisor inválido"
            ];
        }


        // -----------------------------------------------------
        // OBTENER SOLICITUD
        // -----------------------------------------------------

        $solicitud =
            $this->solicitud->obtenerPorId(
                (int) $idSolicitud
            );


        if (!$solicitud) {

            return [
                "success" => false,
                "message" => "Solicitud no encontrada"
            ];
        }


        if ($solicitud["estatus"] !== "activo") {

            return [
                "success" => false,
                "message" => "La solicitud no se encuentra activa"
            ];
        }


        // -----------------------------------------------------
        // SOLO PENDIENTES
        // -----------------------------------------------------

        if (
            $solicitud["estado_solicitud"] !==
            "pendiente"
        ) {

            return [
                "success" => false,
                "message" =>
                    "Solo las solicitudes pendientes pueden pasar a revisión"
            ];
        }


        // -----------------------------------------------------
        // OBTENER ESTADO EN REVISIÓN
        // -----------------------------------------------------

        $estadoRevision =
            $this->solicitud
                ->obtenerEstadoPorNombre(
                    "en_revision"
                );


        if (!$estadoRevision) {

            return [
                "success" => false,
                "message" =>
                    "No existe el estado en_revision en la configuración"
            ];
        }


        // -----------------------------------------------------
        // CAMBIAR ESTADO
        // -----------------------------------------------------

        $resultado =
            $this->solicitud->cambiarEstado(
                (int) $idSolicitud,
                (int) $estadoRevision[
                    "id_estado_solicitud"
                ],
                (int) $idUsuarioRevisor,
                $observaciones
            );


        if (!$resultado) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible poner la solicitud en revisión"
            ];
        }


        return [
            "success" => true,
            "message" =>
                "Solicitud puesta en revisión correctamente"
        ];
    }


    // =========================================================
    // APROBAR SOLICITUD
    // =========================================================

    public function aprobar(
        $idSolicitud,
        $idUsuarioRevisor,
        $observaciones = null
    )
    {
        // -----------------------------------------------------
        // VALIDAR DATOS
        // -----------------------------------------------------

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
            !$idUsuarioRevisor ||
            !is_numeric($idUsuarioRevisor)
        ) {

            return [
                "success" => false,
                "message" => "Usuario revisor inválido"
            ];
        }


        // -----------------------------------------------------
        // OBTENER SOLICITUD
        // -----------------------------------------------------

        $solicitud =
            $this->solicitud->obtenerPorId(
                (int) $idSolicitud
            );


        if (!$solicitud) {

            return [
                "success" => false,
                "message" => "Solicitud no encontrada"
            ];
        }


        if ($solicitud["estatus"] !== "activo") {

            return [
                "success" => false,
                "message" => "La solicitud no se encuentra activa"
            ];
        }


        // -----------------------------------------------------
        // VALIDAR ESTADO
        // -----------------------------------------------------

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


        // -----------------------------------------------------
        // OBTENER ESTADO APROBADA
        // -----------------------------------------------------

        $estadoAprobada =
            $this->solicitud
                ->obtenerEstadoPorNombre(
                    "aprobada"
                );


        if (!$estadoAprobada) {

            return [
                "success" => false,
                "message" =>
                    "No existe el estado aprobada en la configuración"
            ];
        }


        // -----------------------------------------------------
        // CAMBIAR ESTADO
        // -----------------------------------------------------

        $resultado =
            $this->solicitud->cambiarEstado(
                (int) $idSolicitud,
                (int) $estadoAprobada[
                    "id_estado_solicitud"
                ],
                (int) $idUsuarioRevisor,
                $observaciones
            );


        if (!$resultado) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible aprobar la solicitud"
            ];
        }


        return [
            "success" => true,
            "message" =>
                "Solicitud aprobada correctamente"
        ];
    }


    // =========================================================
    // RECHAZAR SOLICITUD
    // =========================================================

    public function rechazar(
        $idSolicitud,
        $idUsuarioRevisor,
        $observaciones = null
    )
    {
        // -----------------------------------------------------
        // VALIDAR DATOS
        // -----------------------------------------------------

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
            !$idUsuarioRevisor ||
            !is_numeric($idUsuarioRevisor)
        ) {

            return [
                "success" => false,
                "message" => "Usuario revisor inválido"
            ];
        }


        // -----------------------------------------------------
        // OBTENER SOLICITUD
        // -----------------------------------------------------

        $solicitud =
            $this->solicitud->obtenerPorId(
                (int) $idSolicitud
            );


        if (!$solicitud) {

            return [
                "success" => false,
                "message" => "Solicitud no encontrada"
            ];
        }


        if ($solicitud["estatus"] !== "activo") {

            return [
                "success" => false,
                "message" => "La solicitud no se encuentra activa"
            ];
        }


        // -----------------------------------------------------
        // SOLO EN REVISIÓN
        // -----------------------------------------------------

        if (
            $solicitud["estado_solicitud"] !==
            "en_revision"
        ) {

            return [
                "success" => false,
                "message" =>
                    "La solicitud debe estar en revisión antes de rechazarla"
            ];
        }


        // -----------------------------------------------------
        // OBSERVACIÓN OBLIGATORIA AL RECHAZAR
        // -----------------------------------------------------

        $observaciones =
            trim((string) $observaciones);


        if ($observaciones === "") {

            return [
                "success" => false,
                "message" =>
                    "Debes indicar el motivo del rechazo"
            ];
        }


        // -----------------------------------------------------
        // OBTENER ESTADO RECHAZADA
        // -----------------------------------------------------

        $estadoRechazada =
            $this->solicitud
                ->obtenerEstadoPorNombre(
                    "rechazada"
                );


        if (!$estadoRechazada) {

            return [
                "success" => false,
                "message" =>
                    "No existe el estado rechazada en la configuración"
            ];
        }


        // -----------------------------------------------------
        // CAMBIAR ESTADO
        // -----------------------------------------------------

        $resultado =
            $this->solicitud->cambiarEstado(
                (int) $idSolicitud,
                (int) $estadoRechazada[
                    "id_estado_solicitud"
                ],
                (int) $idUsuarioRevisor,
                $observaciones
            );


        if (!$resultado) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible rechazar la solicitud"
            ];
        }


        return [
            "success" => true,
            "message" =>
                "Solicitud rechazada correctamente"
        ];
    }
}