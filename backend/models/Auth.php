<?php

require_once __DIR__ . "/../config/database.php";

class Auth
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }

    public function buscarPorCorreo($correo)
    {
        $sql = "SELECT
                    id_usuario,
                    nombre,
                    apellido_paterno,
                    apellido_materno,
                    correo,
                    password,
                    telefono,
                    rol,
                    estatus
                FROM usuarios
                WHERE correo = :correo
                LIMIT 1";

        $consulta = $this->conexion->prepare($sql);

        $consulta->bindParam(":correo", $correo);

        $consulta->execute();

        return $consulta->fetch();
    }
}