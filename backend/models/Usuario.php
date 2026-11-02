<?php

require_once __DIR__ . "/../config/database.php";

class Usuario
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }


    // =========================================================
    // OBTENER TODOS
    // =========================================================

    public function obtenerTodos()
    {
        $sql = "SELECT
                    id_usuario,
                    nombre,
                    apellido_paterno,
                    apellido_materno,
                    correo,
                    telefono,
                    fecha_nacimiento,
                    curp,
                    rfc,
                    direccion,
                    ciudad,
                    estado,
                    codigo_postal,
                    rol,
                    estatus,
                    ultimo_acceso,
                    fecha_creacion,
                    fecha_actualizacion

                FROM usuarios

                ORDER BY id_usuario DESC";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute();

        return $consulta->fetchAll();
    }


    // =========================================================
    // OBTENER POR ID
    // =========================================================

    public function obtenerPorId($idUsuario)
    {
        $sql = "SELECT
                    id_usuario,
                    nombre,
                    apellido_paterno,
                    apellido_materno,
                    correo,
                    telefono,
                    fecha_nacimiento,
                    curp,
                    rfc,
                    direccion,
                    ciudad,
                    estado,
                    codigo_postal,
                    rol,
                    estatus,
                    ultimo_acceso,
                    fecha_creacion,
                    fecha_actualizacion

                FROM usuarios

                WHERE id_usuario = :id_usuario

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_usuario" => $idUsuario
        ]);

        return $consulta->fetch();
    }


    // =========================================================
    // OBTENER POR CORREO
    // =========================================================

    public function obtenerPorCorreo($correo)
    {
        $sql = "SELECT
                    id_usuario,
                    correo

                FROM usuarios

                WHERE correo = :correo

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([
            ":correo" => $correo
        ]);

        return $consulta->fetch();
    }


    // =========================================================
    // CREAR USUARIO
    // =========================================================

    public function crear($datos)
    {
        $sql = "INSERT INTO usuarios
                (
                    nombre,
                    apellido_paterno,
                    apellido_materno,
                    correo,
                    password,
                    telefono,
                    fecha_nacimiento,
                    curp,
                    rfc,
                    direccion,
                    ciudad,
                    estado,
                    codigo_postal,
                    rol,
                    estatus
                )
                VALUES
                (
                    :nombre,
                    :apellido_paterno,
                    :apellido_materno,
                    :correo,
                    :password,
                    :telefono,
                    :fecha_nacimiento,
                    :curp,
                    :rfc,
                    :direccion,
                    :ciudad,
                    :estado,
                    :codigo_postal,
                    :rol,
                    :estatus
                )";

        $consulta =
            $this->conexion->prepare($sql);

        $consulta->execute([

            ":nombre" =>
                $datos["nombre"],

            ":apellido_paterno" =>
                $datos["apellido_paterno"],

            ":apellido_materno" =>
                $datos["apellido_materno"],

            ":correo" =>
                $datos["correo"],

            ":password" =>
                $datos["password"],

            ":telefono" =>
                $datos["telefono"],

            ":fecha_nacimiento" =>
                $datos["fecha_nacimiento"],

            ":curp" =>
                $datos["curp"],

            ":rfc" =>
                $datos["rfc"],

            ":direccion" =>
                $datos["direccion"],

            ":ciudad" =>
                $datos["ciudad"],

            ":estado" =>
                $datos["estado"],

            ":codigo_postal" =>
                $datos["codigo_postal"],

            ":rol" =>
                $datos["rol"],

            ":estatus" =>
                $datos["estatus"]
        ]);

        return $this->conexion->lastInsertId();
    }


    // =========================================================
    // ACTUALIZAR USUARIO
    // =========================================================

    public function actualizar(
        $idUsuario,
        $datos
    )
    {
        $sql = "UPDATE usuarios

                SET
                    nombre = :nombre,
                    apellido_paterno = :apellido_paterno,
                    apellido_materno = :apellido_materno,
                    correo = :correo,
                    telefono = :telefono,
                    fecha_nacimiento = :fecha_nacimiento,
                    curp = :curp,
                    rfc = :rfc,
                    direccion = :direccion,
                    ciudad = :ciudad,
                    estado = :estado,
                    codigo_postal = :codigo_postal,
                    rol = :rol,
                    estatus = :estatus

                WHERE id_usuario = :id_usuario

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        return $consulta->execute([

            ":nombre" =>
                $datos["nombre"],

            ":apellido_paterno" =>
                $datos["apellido_paterno"],

            ":apellido_materno" =>
                $datos["apellido_materno"],

            ":correo" =>
                $datos["correo"],

            ":telefono" =>
                $datos["telefono"],

            ":fecha_nacimiento" =>
                $datos["fecha_nacimiento"],

            ":curp" =>
                $datos["curp"],

            ":rfc" =>
                $datos["rfc"],

            ":direccion" =>
                $datos["direccion"],

            ":ciudad" =>
                $datos["ciudad"],

            ":estado" =>
                $datos["estado"],

            ":codigo_postal" =>
                $datos["codigo_postal"],

            ":rol" =>
                $datos["rol"],

            ":estatus" =>
                $datos["estatus"],

            ":id_usuario" =>
                $idUsuario
        ]);
    }


    // =========================================================
    // ACTUALIZAR CONTRASEÑA
    // =========================================================

    public function actualizarPassword(
        $idUsuario,
        $password
    )
    {
        $sql = "UPDATE usuarios

                SET password = :password

                WHERE id_usuario = :id_usuario

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        return $consulta->execute([

            ":password" =>
                $password,

            ":id_usuario" =>
                $idUsuario
        ]);
    }


    // =========================================================
    // CAMBIAR ESTATUS
    // =========================================================

    public function cambiarEstatus(
        $idUsuario,
        $estatus
    )
    {
        $sql = "UPDATE usuarios

                SET estatus = :estatus

                WHERE id_usuario = :id_usuario

                LIMIT 1";

        $consulta =
            $this->conexion->prepare($sql);

        return $consulta->execute([

            ":estatus" =>
                $estatus,

            ":id_usuario" =>
                $idUsuario
        ]);
    }
}