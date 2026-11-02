<?php

require_once __DIR__ . "/../models/Usuario.php";

class UsuarioController
{
    private $usuario;

    public function __construct()
    {
        $this->usuario =
            new Usuario();
    }


    // =========================================================
    // LISTAR USUARIOS
    // =========================================================

    public function listar()
    {
        return [
            "success" => true,
            "data" =>
                $this->usuario
                    ->obtenerTodos()
        ];
    }


    // =========================================================
    // OBTENER USUARIO
    // =========================================================

    public function obtener($idUsuario)
    {
        if (
            !$idUsuario ||
            !is_numeric($idUsuario)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Usuario inválido"
            ];
        }


        $usuario =
            $this->usuario
                ->obtenerPorId(
                    (int) $idUsuario
                );


        if (!$usuario) {

            return [
                "success" => false,
                "message" =>
                    "Usuario no encontrado"
            ];
        }


        return [
            "success" => true,
            "data" => $usuario
        ];
    }


    // =========================================================
    // CREAR USUARIO
    // =========================================================

    public function crear($datos)
    {
        $nombre =
            trim(
                (string) (
                    $datos["nombre"] ?? ""
                )
            );


        $apellidoPaterno =
            trim(
                (string) (
                    $datos["apellido_paterno"] ?? ""
                )
            );


        $apellidoMaterno =
            trim(
                (string) (
                    $datos["apellido_materno"] ?? ""
                )
            );


        $correo =
            strtolower(
                trim(
                    (string) (
                        $datos["correo"] ?? ""
                    )
                )
            );


        $password =
            (string) (
                $datos["password"] ?? ""
            );


        $telefono =
            trim(
                (string) (
                    $datos["telefono"] ?? ""
                )
            );


        $fechaNacimiento =
            trim(
                (string) (
                    $datos["fecha_nacimiento"] ?? ""
                )
            );


        $curp =
            strtoupper(
                trim(
                    (string) (
                        $datos["curp"] ?? ""
                    )
                )
            );


        $rfc =
            strtoupper(
                trim(
                    (string) (
                        $datos["rfc"] ?? ""
                    )
                )
            );


        $direccion =
            trim(
                (string) (
                    $datos["direccion"] ?? ""
                )
            );


        $ciudad =
            trim(
                (string) (
                    $datos["ciudad"] ?? ""
                )
            );


        $estado =
            trim(
                (string) (
                    $datos["estado"] ?? ""
                )
            );


        $codigoPostal =
            trim(
                (string) (
                    $datos["codigo_postal"] ?? ""
                )
            );


        $rol =
            strtolower(
                trim(
                    (string) (
                        $datos["rol"] ?? ""
                    )
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


        // =====================================================
        // VALIDACIONES BÁSICAS
        // =====================================================

        if ($nombre === "") {

            return [
                "success" => false,
                "message" =>
                    "Ingresa el nombre"
            ];
        }


        if ($apellidoPaterno === "") {

            return [
                "success" => false,
                "message" =>
                    "Ingresa el apellido paterno"
            ];
        }


        if (
            $correo === "" ||
            !filter_var(
                $correo,
                FILTER_VALIDATE_EMAIL
            )
        ) {

            return [
                "success" => false,
                "message" =>
                    "Ingresa un correo válido"
            ];
        }


        if (
            strlen($password) < 6
        ) {

            return [
                "success" => false,
                "message" =>
                    "La contraseña debe tener al menos 6 caracteres"
            ];
        }


        $rolesPermitidos = [
            "administrador",
            "gerente",
            "cliente"
        ];


        if (
            !in_array(
                $rol,
                $rolesPermitidos,
                true
            )
        ) {

            return [
                "success" => false,
                "message" =>
                    "Selecciona un rol válido"
            ];
        }


        $estatusPermitidos = [
            "activo",
            "inactivo",
            "bloqueado"
        ];


        if (
            !in_array(
                $estatus,
                $estatusPermitidos,
                true
            )
        ) {

            return [
                "success" => false,
                "message" =>
                    "Selecciona un estatus válido"
            ];
        }


        // =====================================================
        // CORREO DUPLICADO
        // =====================================================

        $correoExistente =
            $this->usuario
                ->obtenerPorCorreo(
                    $correo
                );


        if ($correoExistente) {

            return [
                "success" => false,
                "message" =>
                    "Ya existe un usuario con ese correo"
            ];
        }


        // =====================================================
        // HASH DE CONTRASEÑA
        // =====================================================

        $passwordHash =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );


        // =====================================================
        // CREAR
        // =====================================================

        $idUsuario =
            $this->usuario
                ->crear([

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

                    "password" =>
                        $passwordHash,

                    "telefono" =>
                        $telefono !== ""
                            ? $telefono
                            : null,

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

                    "rol" =>
                        $rol,

                    "estatus" =>
                        $estatus
                ]);


        return [
            "success" => true,
            "message" =>
                "Usuario registrado correctamente",
            "id_usuario" =>
                $idUsuario
        ];
    }


    // =========================================================
    // ACTUALIZAR USUARIO
    // =========================================================

    public function actualizar(
        $idUsuario,
        $datos
    )
    {
        if (
            !$idUsuario ||
            !is_numeric($idUsuario)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Usuario inválido"
            ];
        }


        $idUsuario =
            (int) $idUsuario;


        $usuarioActual =
            $this->usuario
                ->obtenerPorId(
                    $idUsuario
                );


        if (!$usuarioActual) {

            return [
                "success" => false,
                "message" =>
                    "Usuario no encontrado"
            ];
        }


        $nombre =
            trim(
                (string) (
                    $datos["nombre"] ?? ""
                )
            );


        $apellidoPaterno =
            trim(
                (string) (
                    $datos["apellido_paterno"] ?? ""
                )
            );


        $apellidoMaterno =
            trim(
                (string) (
                    $datos["apellido_materno"] ?? ""
                )
            );


        $correo =
            strtolower(
                trim(
                    (string) (
                        $datos["correo"] ?? ""
                    )
                )
            );


        $telefono =
            trim(
                (string) (
                    $datos["telefono"] ?? ""
                )
            );


        $fechaNacimiento =
            trim(
                (string) (
                    $datos["fecha_nacimiento"] ?? ""
                )
            );


        $curp =
            strtoupper(
                trim(
                    (string) (
                        $datos["curp"] ?? ""
                    )
                )
            );


        $rfc =
            strtoupper(
                trim(
                    (string) (
                        $datos["rfc"] ?? ""
                    )
                )
            );


        $direccion =
            trim(
                (string) (
                    $datos["direccion"] ?? ""
                )
            );


        $ciudad =
            trim(
                (string) (
                    $datos["ciudad"] ?? ""
                )
            );


        $estado =
            trim(
                (string) (
                    $datos["estado"] ?? ""
                )
            );


        $codigoPostal =
            trim(
                (string) (
                    $datos["codigo_postal"] ?? ""
                )
            );


        $rol =
            strtolower(
                trim(
                    (string) (
                        $datos["rol"] ?? ""
                    )
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


        if (
            $nombre === "" ||
            $apellidoPaterno === ""
        ) {

            return [
                "success" => false,
                "message" =>
                    "Nombre y apellido paterno son obligatorios"
            ];
        }


        if (
            $correo === "" ||
            !filter_var(
                $correo,
                FILTER_VALIDATE_EMAIL
            )
        ) {

            return [
                "success" => false,
                "message" =>
                    "Ingresa un correo válido"
            ];
        }


        $rolesPermitidos = [
            "administrador",
            "gerente",
            "cliente"
        ];


        if (
            !in_array(
                $rol,
                $rolesPermitidos,
                true
            )
        ) {

            return [
                "success" => false,
                "message" =>
                    "Rol inválido"
            ];
        }


        $estatusPermitidos = [
            "activo",
            "inactivo",
            "bloqueado"
        ];


        if (
            !in_array(
                $estatus,
                $estatusPermitidos,
                true
            )
        ) {

            return [
                "success" => false,
                "message" =>
                    "Estatus inválido"
            ];
        }


        // =====================================================
        // VALIDAR CORREO
        // =====================================================

        $correoExistente =
            $this->usuario
                ->obtenerPorCorreo(
                    $correo
                );


        if (
            $correoExistente &&
            (int) $correoExistente[
                "id_usuario"
            ] !==
            $idUsuario
        ) {

            return [
                "success" => false,
                "message" =>
                    "Ya existe otro usuario con ese correo"
            ];
        }


        $resultado =
            $this->usuario
                ->actualizar(
                    $idUsuario,
                    [

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
                            $telefono !== ""
                                ? $telefono
                                : null,

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

                        "rol" =>
                            $rol,

                        "estatus" =>
                            $estatus
                    ]
                );


        if (!$resultado) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible actualizar el usuario"
            ];
        }


        return [
            "success" => true,
            "message" =>
                "Usuario actualizado correctamente"
        ];
    }


    // =========================================================
    // CAMBIAR CONTRASEÑA
    // =========================================================

    public function cambiarPassword(
        $idUsuario,
        $password
    )
    {
        if (
            !$idUsuario ||
            !is_numeric($idUsuario)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Usuario inválido"
            ];
        }


        $password =
            (string) $password;


        if (
            strlen($password) < 6
        ) {

            return [
                "success" => false,
                "message" =>
                    "La contraseña debe tener al menos 6 caracteres"
            ];
        }


        $usuario =
            $this->usuario
                ->obtenerPorId(
                    (int) $idUsuario
                );


        if (!$usuario) {

            return [
                "success" => false,
                "message" =>
                    "Usuario no encontrado"
            ];
        }


        $hash =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );


        $resultado =
            $this->usuario
                ->actualizarPassword(
                    (int) $idUsuario,
                    $hash
                );


        if (!$resultado) {

            return [
                "success" => false,
                "message" =>
                    "No fue posible cambiar la contraseña"
            ];
        }


        return [
            "success" => true,
            "message" =>
                "Contraseña actualizada correctamente"
        ];
    }


    // =========================================================
    // CAMBIAR ESTATUS
    // =========================================================

    public function cambiarEstatus(
        $idUsuario,
        $estatus
    )
    {
        if (
            !$idUsuario ||
            !is_numeric($idUsuario)
        ) {

            return [
                "success" => false,
                "message" =>
                    "Usuario inválido"
            ];
        }


        $estatus =
            strtolower(
                trim(
                    (string) $estatus
                )
            );


        $permitidos = [
            "activo",
            "inactivo",
            "bloqueado"
        ];


        if (
            !in_array(
                $estatus,
                $permitidos,
                true
            )
        ) {

            return [
                "success" => false,
                "message" =>
                    "Estatus inválido"
            ];
        }


        $usuario =
            $this->usuario
                ->obtenerPorId(
                    (int) $idUsuario
                );


        if (!$usuario) {

            return [
                "success" => false,
                "message" =>
                    "Usuario no encontrado"
            ];
        }


        $resultado =
            $this->usuario
                ->cambiarEstatus(
                    (int) $idUsuario,
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
}