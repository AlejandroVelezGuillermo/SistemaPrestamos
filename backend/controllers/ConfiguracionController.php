<?php

require_once __DIR__ . "/../models/Configuracion.php";

class ConfiguracionController
{
    private $configuracion;

    public function __construct()
    {
        $this->configuracion =
            new Configuracion();
    }


    // =========================================================
    // LISTAR TIPOS DE PRÉSTAMO
    // =========================================================

    public function listarTiposPrestamo()
    {
        try {

            return [
                "success" => true,
                "data" =>
                    $this->configuracion
                        ->obtenerTiposPrestamo()
            ];

        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible obtener los tipos de préstamo"
            ];
        }
    }


    // =========================================================
    // OBTENER TIPO DE PRÉSTAMO
    // =========================================================

    public function obtenerTipoPrestamo(
        $idTipoPrestamo
    )
    {
        if (
            !$idTipoPrestamo ||
            !is_numeric($idTipoPrestamo)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Tipo de préstamo inválido"
            ];
        }


        $tipo =
            $this->configuracion
                ->obtenerTipoPrestamoPorId(
                    (int) $idTipoPrestamo
                );


        if (!$tipo) {

            return [
                "success" => false,
                "message" =>
                    "Tipo de préstamo no encontrado"
            ];
        }


        return [
            "success" => true,
            "data" => $tipo
        ];
    }


    // =========================================================
    // CREAR TIPO DE PRÉSTAMO
    // =========================================================

    public function crearTipoPrestamo(
        $datos
    )
    {
        $nombre =
            trim(
                (string) (
                    $datos["nombre"] ?? ""
                )
            );


        $descripcion =
            trim(
                (string) (
                    $datos["descripcion"] ?? ""
                )
            );


        $montoMinimo =
            $datos["monto_minimo"] ?? null;


        $montoMaximo =
            $datos["monto_maximo"] ?? null;


        $plazoMinimo =
            $datos["plazo_minimo"] ?? null;


        $plazoMaximo =
            $datos["plazo_maximo"] ?? null;


        $estatus =
            strtolower(
                trim(
                    (string) (
                        $datos["estatus"] ?? "activo"
                    )
                )
            );


        if ($nombre === "") {

            return [
                "success" => false,
                "message" =>
                    "Ingresa el nombre del tipo de préstamo"
            ];
        }


        if (
            !is_numeric($montoMinimo) ||
            (float) $montoMinimo < 0
        ) {

            return [
                "success" => false,
                "message" =>
                    "Ingresa un monto mínimo válido"
            ];
        }


        if (
            !is_numeric($montoMaximo) ||
            (float) $montoMaximo <= 0
        ) {

            return [
                "success" => false,
                "message" =>
                    "Ingresa un monto máximo válido"
            ];
        }


        $montoMinimo =
            round(
                (float) $montoMinimo,
                2
            );


        $montoMaximo =
            round(
                (float) $montoMaximo,
                2
            );


        if (
            $montoMaximo <
            $montoMinimo
        ) {

            return [
                "success" => false,
                "message" =>
                    "El monto máximo no puede ser menor que el monto mínimo"
            ];
        }


        if (
            !is_numeric($plazoMinimo) ||
            (int) $plazoMinimo <= 0
        ) {

            return [
                "success" => false,
                "message" =>
                    "Ingresa un plazo mínimo válido"
            ];
        }


        if (
            !is_numeric($plazoMaximo) ||
            (int) $plazoMaximo <= 0
        ) {

            return [
                "success" => false,
                "message" =>
                    "Ingresa un plazo máximo válido"
            ];
        }


        $plazoMinimo =
            (int) $plazoMinimo;


        $plazoMaximo =
            (int) $plazoMaximo;


        if (
            $plazoMaximo <
            $plazoMinimo
        ) {

            return [
                "success" => false,
                "message" =>
                    "El plazo máximo no puede ser menor que el plazo mínimo"
            ];
        }


        if (
            !in_array(
                $estatus,
                [
                    "activo",
                    "inactivo"
                ],
                true
            )
        ) {

            return [
                "success" => false,
                "message" =>
                    "Estatus inválido"
            ];
        }


        $idTipoPrestamo =
            $this->configuracion
                ->crearTipoPrestamo([

                    "nombre" =>
                        $nombre,

                    "descripcion" =>
                        $descripcion !== ""
                            ? $descripcion
                            : null,

                    "monto_minimo" =>
                        $montoMinimo,

                    "monto_maximo" =>
                        $montoMaximo,

                    "plazo_minimo" =>
                        $plazoMinimo,

                    "plazo_maximo" =>
                        $plazoMaximo,

                    "estatus" =>
                        $estatus
                ]);


        return [
            "success" => true,
            "message" =>
                "Tipo de préstamo registrado correctamente",
            "id_tipo_prestamo" =>
                $idTipoPrestamo
        ];
    }


    // =========================================================
    // ACTUALIZAR TIPO DE PRÉSTAMO
    // =========================================================

    public function actualizarTipoPrestamo(
        $idTipoPrestamo,
        $datos
    )
    {
        if (
            !$idTipoPrestamo ||
            !is_numeric($idTipoPrestamo)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Tipo de préstamo inválido"
            ];
        }


        $actual =
            $this->configuracion
                ->obtenerTipoPrestamoPorId(
                    (int) $idTipoPrestamo
                );


        if (!$actual) {

            return [
                "success" => false,
                "message" =>
                    "Tipo de préstamo no encontrado"
            ];
        }


        $resultado =
            $this->crearDatosTipoPrestamo(
                $datos
            );


        if (!$resultado["success"]) {

            return $resultado;
        }


        $actualizado =
            $this->configuracion
                ->actualizarTipoPrestamo(
                    (int) $idTipoPrestamo,
                    $resultado["data"]
                );


        if (!$actualizado) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible actualizar el tipo de préstamo"
            ];
        }


        return [
            "success" => true,
            "message" =>
                "Tipo de préstamo actualizado correctamente"
        ];
    }


    // =========================================================
    // CAMBIAR ESTATUS TIPO DE PRÉSTAMO
    // =========================================================

    public function cambiarEstatusTipoPrestamo(
        $idTipoPrestamo,
        $estatus
    )
    {
        if (
            !$idTipoPrestamo ||
            !is_numeric($idTipoPrestamo)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Tipo de préstamo inválido"
            ];
        }


        $estatus =
            strtolower(
                trim(
                    (string) $estatus
                )
            );


        if (
            !in_array(
                $estatus,
                [
                    "activo",
                    "inactivo"
                ],
                true
            )
        ) {

            return [
                "success" => false,
                "message" =>
                    "Estatus inválido"
            ];
        }


        $tipo =
            $this->configuracion
                ->obtenerTipoPrestamoPorId(
                    (int) $idTipoPrestamo
                );


        if (!$tipo) {

            return [
                "success" => false,
                "message" =>
                    "Tipo de préstamo no encontrado"
            ];
        }


        $resultado =
            $this->configuracion
                ->cambiarEstatusTipoPrestamo(
                    (int) $idTipoPrestamo,
                    $estatus
                );


        if (!$resultado) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible cambiar el estatus"
            ];
        }


        return [
            "success" => true,
            "message" =>
                "Estatus actualizado correctamente"
        ];
    }


    // =========================================================
    // LISTAR MÉTODOS DE PAGO
    // =========================================================

    public function listarMetodosPago()
    {
        try {

            return [
                "success" => true,
                "data" =>
                    $this->configuracion
                        ->obtenerMetodosPago()
            ];

        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible obtener los métodos de pago"
            ];
        }
    }


    // =========================================================
    // OBTENER MÉTODO DE PAGO
    // =========================================================

    public function obtenerMetodoPago(
        $idMetodoPago
    )
    {
        if (
            !$idMetodoPago ||
            !is_numeric($idMetodoPago)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Método de pago inválido"
            ];
        }


        $metodo =
            $this->configuracion
                ->obtenerMetodoPagoPorId(
                    (int) $idMetodoPago
                );


        if (!$metodo) {

            return [
                "success" => false,
                "message" =>
                    "Método de pago no encontrado"
            ];
        }


        return [
            "success" => true,
            "data" => $metodo
        ];
    }


    // =========================================================
    // CREAR MÉTODO DE PAGO
    // =========================================================

    public function crearMetodoPago(
        $datos
    )
    {
        $nombre =
            trim(
                (string) (
                    $datos["nombre"] ?? ""
                )
            );


        $descripcion =
            trim(
                (string) (
                    $datos["descripcion"] ?? ""
                )
            );


        $estatus =
            strtolower(
                trim(
                    (string) (
                        $datos["estatus"] ?? "activo"
                    )
                )
            );


        if ($nombre === "") {

            return [
                "success" => false,
                "message" =>
                    "Ingresa el nombre del método de pago"
            ];
        }


        if (
            !in_array(
                $estatus,
                [
                    "activo",
                    "inactivo"
                ],
                true
            )
        ) {

            return [
                "success" => false,
                "message" =>
                    "Estatus inválido"
            ];
        }


        $idMetodoPago =
            $this->configuracion
                ->crearMetodoPago([

                    "nombre" =>
                        $nombre,

                    "descripcion" =>
                        $descripcion !== ""
                            ? $descripcion
                            : null,

                    "estatus" =>
                        $estatus
                ]);


        return [
            "success" => true,
            "message" =>
                "Método de pago registrado correctamente",
            "id_metodo_pago" =>
                $idMetodoPago
        ];
    }


    // =========================================================
    // ACTUALIZAR MÉTODO DE PAGO
    // =========================================================

    public function actualizarMetodoPago(
        $idMetodoPago,
        $datos
    )
    {
        if (
            !$idMetodoPago ||
            !is_numeric($idMetodoPago)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Método de pago inválido"
            ];
        }


        $metodo =
            $this->configuracion
                ->obtenerMetodoPagoPorId(
                    (int) $idMetodoPago
                );


        if (!$metodo) {

            return [
                "success" => false,
                "message" =>
                    "Método de pago no encontrado"
            ];
        }


        $nombre =
            trim(
                (string) (
                    $datos["nombre"] ?? ""
                )
            );


        $descripcion =
            trim(
                (string) (
                    $datos["descripcion"] ?? ""
                )
            );


        $estatus =
            strtolower(
                trim(
                    (string) (
                        $datos["estatus"] ?? ""
                    )
                )
            );


        if ($nombre === "") {

            return [
                "success" => false,
                "message" =>
                    "Ingresa el nombre del método de pago"
            ];
        }


        if (
            !in_array(
                $estatus,
                [
                    "activo",
                    "inactivo"
                ],
                true
            )
        ) {

            return [
                "success" => false,
                "message" =>
                    "Estatus inválido"
            ];
        }


        $resultado =
            $this->configuracion
                ->actualizarMetodoPago(
                    (int) $idMetodoPago,
                    [

                        "nombre" =>
                            $nombre,

                        "descripcion" =>
                            $descripcion !== ""
                                ? $descripcion
                                : null,

                        "estatus" =>
                            $estatus
                    ]
                );


        if (!$resultado) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible actualizar el método de pago"
            ];
        }


        return [
            "success" => true,
            "message" =>
                "Método de pago actualizado correctamente"
        ];
    }


    // =========================================================
    // CAMBIAR ESTATUS MÉTODO DE PAGO
    // =========================================================

    public function cambiarEstatusMetodoPago(
        $idMetodoPago,
        $estatus
    )
    {
        if (
            !$idMetodoPago ||
            !is_numeric($idMetodoPago)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Método de pago inválido"
            ];
        }


        $estatus =
            strtolower(
                trim(
                    (string) $estatus
                )
            );


        if (
            !in_array(
                $estatus,
                [
                    "activo",
                    "inactivo"
                ],
                true
            )
        ) {

            return [
                "success" => false,
                "message" =>
                    "Estatus inválido"
            ];
        }


        $metodo =
            $this->configuracion
                ->obtenerMetodoPagoPorId(
                    (int) $idMetodoPago
                );


        if (!$metodo) {

            return [
                "success" => false,
                "message" =>
                    "Método de pago no encontrado"
            ];
        }


        $resultado =
            $this->configuracion
                ->cambiarEstatusMetodoPago(
                    (int) $idMetodoPago,
                    $estatus
                );


        if (!$resultado) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible cambiar el estatus"
            ];
        }


        return [
            "success" => true,
            "message" =>
                "Estatus actualizado correctamente"
        ];
    }


    // =========================================================
    // ESTADOS DE PRÉSTAMO
    // =========================================================

    public function listarEstadosPrestamo()
    {
        try {

            return [
                "success" => true,
                "data" =>
                    $this->configuracion
                        ->obtenerEstadosPrestamo()
            ];

        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible obtener los estados de préstamo"
            ];
        }
    }


    // =========================================================
    // ESTADOS DE SOLICITUD
    // =========================================================

    public function listarEstadosSolicitud()
    {
        try {

            return [
                "success" => true,
                "data" =>
                    $this->configuracion
                        ->obtenerEstadosSolicitud()
            ];

        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible obtener los estados de solicitud"
            ];
        }
    }


    // =========================================================
    // VALIDACIÓN INTERNA DE TIPO DE PRÉSTAMO
    // =========================================================

    private function crearDatosTipoPrestamo(
        $datos
    )
    {
        $nombre =
            trim(
                (string) (
                    $datos["nombre"] ?? ""
                )
            );


        $descripcion =
            trim(
                (string) (
                    $datos["descripcion"] ?? ""
                )
            );


        $montoMinimo =
            $datos["monto_minimo"] ?? null;


        $montoMaximo =
            $datos["monto_maximo"] ?? null;


        $plazoMinimo =
            $datos["plazo_minimo"] ?? null;


        $plazoMaximo =
            $datos["plazo_maximo"] ?? null;


        $estatus =
            strtolower(
                trim(
                    (string) (
                        $datos["estatus"] ?? ""
                    )
                )
            );


        if ($nombre === "") {

            return [
                "success" => false,
                "message" =>
                    "Ingresa el nombre del tipo de préstamo"
            ];
        }


        if (
            !is_numeric($montoMinimo) ||
            (float) $montoMinimo < 0
        ) {

            return [
                "success" => false,
                "message" =>
                    "Ingresa un monto mínimo válido"
            ];
        }


        if (
            !is_numeric($montoMaximo) ||
            (float) $montoMaximo <= 0
        ) {

            return [
                "success" => false,
                "message" =>
                    "Ingresa un monto máximo válido"
            ];
        }


        $montoMinimo =
            round(
                (float) $montoMinimo,
                2
            );


        $montoMaximo =
            round(
                (float) $montoMaximo,
                2
            );


        if (
            $montoMaximo <
            $montoMinimo
        ) {

            return [
                "success" => false,
                "message" =>
                    "El monto máximo no puede ser menor que el monto mínimo"
            ];
        }


        if (
            !is_numeric($plazoMinimo) ||
            (int) $plazoMinimo <= 0
        ) {

            return [
                "success" => false,
                "message" =>
                    "Ingresa un plazo mínimo válido"
            ];
        }


        if (
            !is_numeric($plazoMaximo) ||
            (int) $plazoMaximo <= 0
        ) {

            return [
                "success" => false,
                "message" =>
                    "Ingresa un plazo máximo válido"
            ];
        }


        $plazoMinimo =
            (int) $plazoMinimo;


        $plazoMaximo =
            (int) $plazoMaximo;


        if (
            $plazoMaximo <
            $plazoMinimo
        ) {

            return [
                "success" => false,
                "message" =>
                    "El plazo máximo no puede ser menor que el plazo mínimo"
            ];
        }


        if (
            !in_array(
                $estatus,
                [
                    "activo",
                    "inactivo"
                ],
                true
            )
        ) {

            return [
                "success" => false,
                "message" =>
                    "Estatus inválido"
            ];
        }


        return [
            "success" => true,

            "data" => [

                "nombre" =>
                    $nombre,

                "descripcion" =>
                    $descripcion !== ""
                        ? $descripcion
                        : null,

                "monto_minimo" =>
                    $montoMinimo,

                "monto_maximo" =>
                    $montoMaximo,

                "plazo_minimo" =>
                    $plazoMinimo,

                "plazo_maximo" =>
                    $plazoMaximo,

                "estatus" =>
                    $estatus
            ]
        ];
    }
}