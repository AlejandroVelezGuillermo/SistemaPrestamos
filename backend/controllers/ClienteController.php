<?php

require_once __DIR__ . "/../models/Cliente.php";

class ClienteController
{
    private $cliente;

    public function __construct()
    {
        $this->cliente = new Cliente();
    }


    public function listar()
    {
        return $this->cliente->obtenerTodos();
    }


    public function obtener($idCliente)
    {
        if (!$idCliente || !is_numeric($idCliente)) {

            return [
                "success" => false,
                "message" => "ID de cliente inválido"
            ];
        }


        $cliente =
            $this->cliente->obtenerPorId(
                (int) $idCliente
            );


        if (!$cliente) {

            return [
                "success" => false,
                "message" => "Cliente no encontrado"
            ];
        }


        return [
            "success" => true,
            "data" => $cliente
        ];
    }


    public function crear($datos)
    {
        $datosValidados =
            $this->validarDatos(
                $datos,
                null,
                true
            );


        if (!$datosValidados["success"]) {
            return $datosValidados;
        }


        $datosCliente =
            $datosValidados["data"];


        $passwordHash =
            password_hash(
                $datosCliente["password"],
                PASSWORD_DEFAULT
            );


        $datosCliente["password"] =
            $passwordHash;


        $idCliente =
            $this->cliente->crear(
                $datosCliente
            );


        return [
            "success" => true,
            "message" => "Cliente registrado correctamente",
            "id_cliente" => $idCliente
        ];
    }


    public function actualizar($idCliente, $datos)
    {
        if (!$idCliente || !is_numeric($idCliente)) {

            return [
                "success" => false,
                "message" => "ID de cliente inválido"
            ];
        }


        $idCliente =
            (int) $idCliente;


        $clienteActual =
            $this->cliente->obtenerPorId(
                $idCliente
            );


        if (!$clienteActual) {

            return [
                "success" => false,
                "message" => "Cliente no encontrado"
            ];
        }


        $datosValidados =
            $this->validarDatos(
                $datos,
                $idCliente,
                false
            );


        if (!$datosValidados["success"]) {
            return $datosValidados;
        }


        $this->cliente->actualizar(
            $idCliente,
            $datosValidados["data"]
        );


        return [
            "success" => true,
            "message" => "Cliente actualizado correctamente"
        ];
    }


    private function validarDatos(
        $datos,
        $ignorarId = null,
        $requierePassword = false
    ) {

        $nombre =
            trim($datos["nombre"] ?? "");

        $apellidoPaterno =
            trim($datos["apellido_paterno"] ?? "");

        $apellidoMaterno =
            trim($datos["apellido_materno"] ?? "");

        $correo =
            strtolower(
                trim($datos["correo"] ?? "")
            );

        $telefono =
            trim($datos["telefono"] ?? "");

        $fechaNacimiento =
            $datos["fecha_nacimiento"] ?? "";

        $curp =
            strtoupper(
                trim($datos["curp"] ?? "")
            );

        $rfc =
            strtoupper(
                trim($datos["rfc"] ?? "")
            );

        $direccion =
            trim($datos["direccion"] ?? "");

        $ciudad =
            trim($datos["ciudad"] ?? "");

        $estado =
            trim($datos["estado"] ?? "");

        $codigoPostal =
            trim($datos["codigo_postal"] ?? "");

        $estatus =
            trim($datos["estatus"] ?? "activo");

        $password =
            $datos["password"] ?? "";


        if (
            $nombre === "" ||
            $apellidoPaterno === "" ||
            $correo === "" ||
            $telefono === ""
        ) {

            return [
                "success" => false,
                "message" => "Completa todos los campos obligatorios"
            ];
        }


        if (
            $requierePassword &&
            $password === ""
        ) {

            return [
                "success" => false,
                "message" => "La contraseña es obligatoria"
            ];
        }


        if (
            $requierePassword &&
            strlen($password) < 8
        ) {

            return [
                "success" => false,
                "message" => "La contraseña debe tener al menos 8 caracteres"
            ];
        }


        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

            return [
                "success" => false,
                "message" => "El correo electrónico no es válido"
            ];
        }


        $estatusPermitidos = [
            "activo",
            "inactivo",
            "bloqueado"
        ];


        if (!in_array(
            $estatus,
            $estatusPermitidos,
            true
        )) {

            return [
                "success" => false,
                "message" => "Estatus de cliente inválido"
            ];
        }


        if (
            $this->cliente->existeCorreo(
                $correo,
                $ignorarId
            )
        ) {

            return [
                "success" => false,
                "message" => "Ya existe un usuario con ese correo"
            ];
        }


        if (
            $curp !== "" &&
            $this->cliente->existeCurp(
                $curp,
                $ignorarId
            )
        ) {

            return [
                "success" => false,
                "message" => "Ya existe un usuario con esa CURP"
            ];
        }


        if (
            $rfc !== "" &&
            $this->cliente->existeRfc(
                $rfc,
                $ignorarId
            )
        ) {

            return [
                "success" => false,
                "message" => "Ya existe un usuario con ese RFC"
            ];
        }


        return [
            "success" => true,

            "data" => [

                "nombre" =>
                    $nombre,

                "apellido_paterno" =>
                    $apellidoPaterno,

                "apellido_materno" =>
                    $apellidoMaterno !== ""
                        ? $apellidoMaterno
                        : null,

                "correo" =>
                    $correo,

                "telefono" =>
                    $telefono,

                "fecha_nacimiento" =>
                    $fechaNacimiento !== ""
                        ? $fechaNacimiento
                        : null,

                "curp" =>
                    $curp !== ""
                        ? $curp
                        : null,

                "rfc" =>
                    $rfc !== ""
                        ? $rfc
                        : null,

                "direccion" =>
                    $direccion !== ""
                        ? $direccion
                        : null,

                "ciudad" =>
                    $ciudad !== ""
                        ? $ciudad
                        : null,

                "estado" =>
                    $estado !== ""
                        ? $estado
                        : null,

                "codigo_postal" =>
                    $codigoPostal !== ""
                        ? $codigoPostal
                        : null,

                "estatus" =>
                    $estatus,

                "password" =>
                    $password
            ]
        ];
    }
}