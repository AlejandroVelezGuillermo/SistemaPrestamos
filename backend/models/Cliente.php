<?php

require_once __DIR__ . "/../config/database.php";

class Cliente
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
                    estatus,
                    fecha_creacion,
                    fecha_actualizacion
                FROM usuarios
                WHERE rol = 'cliente'
                ORDER BY id_usuario DESC";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute();

        return $consulta->fetchAll();
    }


    // =========================================================
    // OBTENER POR ID
    // =========================================================

    public function obtenerPorId($idCliente)
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
                    estatus,
                    ultimo_acceso,
                    fecha_creacion,
                    fecha_actualizacion
                FROM usuarios
                WHERE id_usuario = :id_cliente
                AND rol = 'cliente'
                LIMIT 1";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_cliente" => $idCliente
        ]);

        return $consulta->fetch();
    }


    // =========================================================
    // VALIDACIONES DE DUPLICADOS
    // =========================================================

    public function existeCorreo($correo, $ignorarId = null)
    {
        $sql = "SELECT id_usuario
                FROM usuarios
                WHERE correo = :correo";

        if ($ignorarId !== null) {
            $sql .= " AND id_usuario != :ignorar_id";
        }

        $sql .= " LIMIT 1";


        $consulta = $this->conexion->prepare($sql);


        $parametros = [
            ":correo" => $correo
        ];


        if ($ignorarId !== null) {
            $parametros[":ignorar_id"] = $ignorarId;
        }


        $consulta->execute($parametros);


        return $consulta->fetch() ? true : false;
    }


    public function existeCurp($curp, $ignorarId = null)
    {
        if (!$curp) {
            return false;
        }


        $sql = "SELECT id_usuario
                FROM usuarios
                WHERE curp = :curp";

        if ($ignorarId !== null) {
            $sql .= " AND id_usuario != :ignorar_id";
        }

        $sql .= " LIMIT 1";


        $consulta = $this->conexion->prepare($sql);


        $parametros = [
            ":curp" => $curp
        ];


        if ($ignorarId !== null) {
            $parametros[":ignorar_id"] = $ignorarId;
        }


        $consulta->execute($parametros);


        return $consulta->fetch() ? true : false;
    }


    public function existeRfc($rfc, $ignorarId = null)
    {
        if (!$rfc) {
            return false;
        }


        $sql = "SELECT id_usuario
                FROM usuarios
                WHERE rfc = :rfc";

        if ($ignorarId !== null) {
            $sql .= " AND id_usuario != :ignorar_id";
        }

        $sql .= " LIMIT 1";


        $consulta = $this->conexion->prepare($sql);


        $parametros = [
            ":rfc" => $rfc
        ];


        if ($ignorarId !== null) {
            $parametros[":ignorar_id"] = $ignorarId;
        }


        $consulta->execute($parametros);


        return $consulta->fetch() ? true : false;
    }


    // =========================================================
    // CREAR
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
                    'cliente',
                    'activo'
                )";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ":nombre" => $datos["nombre"],
            ":apellido_paterno" => $datos["apellido_paterno"],
            ":apellido_materno" => $datos["apellido_materno"],
            ":correo" => $datos["correo"],
            ":password" => $datos["password"],
            ":telefono" => $datos["telefono"],
            ":fecha_nacimiento" => $datos["fecha_nacimiento"],
            ":curp" => $datos["curp"],
            ":rfc" => $datos["rfc"],
            ":direccion" => $datos["direccion"],
            ":ciudad" => $datos["ciudad"],
            ":estado" => $datos["estado"],
            ":codigo_postal" => $datos["codigo_postal"]
        ]);

        return $this->conexion->lastInsertId();
    }


    // =========================================================
    // ACTUALIZAR CLIENTE
    // =========================================================

    public function actualizar($idCliente, $datos)
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
                    estatus = :estatus
                WHERE id_usuario = :id_cliente
                AND rol = 'cliente'";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":nombre" => $datos["nombre"],
            ":apellido_paterno" => $datos["apellido_paterno"],
            ":apellido_materno" => $datos["apellido_materno"],
            ":correo" => $datos["correo"],
            ":telefono" => $datos["telefono"],
            ":fecha_nacimiento" => $datos["fecha_nacimiento"],
            ":curp" => $datos["curp"],
            ":rfc" => $datos["rfc"],
            ":direccion" => $datos["direccion"],
            ":ciudad" => $datos["ciudad"],
            ":estado" => $datos["estado"],
            ":codigo_postal" => $datos["codigo_postal"],
            ":estatus" => $datos["estatus"],
            ":id_cliente" => $idCliente
        ]);
    }
}