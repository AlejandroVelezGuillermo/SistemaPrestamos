<?php

require_once __DIR__ . "/../config/database.php";

class DocumentoCliente
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }


    // =========================================================
    // LISTAR DOCUMENTOS DE UN CLIENTE
    // =========================================================

    public function obtenerPorCliente($idUsuario)
    {
        $sql = "SELECT
                    id_documento,
                    id_usuario,
                    tipo_documento,
                    nombre_archivo,
                    ruta_archivo,
                    fecha_subida,
                    estatus,
                    observaciones
                FROM clientes_documentos
                WHERE id_usuario = :id_usuario
                ORDER BY id_documento DESC";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_usuario" => $idUsuario
        ]);

        return $consulta->fetchAll();
    }


    // =========================================================
    // OBTENER DOCUMENTO POR ID
    // =========================================================

    public function obtenerPorId($idDocumento)
    {
        $sql = "SELECT
                    id_documento,
                    id_usuario,
                    tipo_documento,
                    nombre_archivo,
                    ruta_archivo,
                    fecha_subida,
                    estatus,
                    observaciones
                FROM clientes_documentos
                WHERE id_documento = :id_documento
                LIMIT 1";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_documento" => $idDocumento
        ]);

        return $consulta->fetch();
    }


    // =========================================================
    // CREAR
    // =========================================================

    public function crear($datos)
    {
        $sql = "INSERT INTO clientes_documentos
                (
                    id_usuario,
                    tipo_documento,
                    nombre_archivo,
                    ruta_archivo,
                    estatus,
                    observaciones
                )
                VALUES
                (
                    :id_usuario,
                    :tipo_documento,
                    :nombre_archivo,
                    :ruta_archivo,
                    'pendiente',
                    :observaciones
                )";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ":id_usuario" => $datos["id_usuario"],
            ":tipo_documento" => $datos["tipo_documento"],
            ":nombre_archivo" => $datos["nombre_archivo"],
            ":ruta_archivo" => $datos["ruta_archivo"],
            ":observaciones" => $datos["observaciones"]
        ]);

        return $this->conexion->lastInsertId();
    }


    // =========================================================
    // CAMBIAR ESTATUS
    // =========================================================

    public function actualizarEstatus(
        $idDocumento,
        $estatus,
        $observaciones = null
    ) {
        $sql = "UPDATE clientes_documentos
                SET
                    estatus = :estatus,
                    observaciones = :observaciones
                WHERE id_documento = :id_documento";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":estatus" => $estatus,
            ":observaciones" => $observaciones,
            ":id_documento" => $idDocumento
        ]);
    }
}