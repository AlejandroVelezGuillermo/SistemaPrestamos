<?php

require_once __DIR__ . "/../models/DocumentoCliente.php";
require_once __DIR__ . "/../models/Cliente.php";

class DocumentoClienteController
{
    private $documento;
    private $cliente;

    public function __construct()
    {
        $this->documento = new DocumentoCliente();
        $this->cliente = new Cliente();
    }


    // =========================================================
    // LISTAR DOCUMENTOS
    // =========================================================

    public function listar($idUsuario)
    {
        if (!$idUsuario || !is_numeric($idUsuario)) {

            return [
                "success" => false,
                "message" => "Cliente inválido"
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


        $documentos =
            $this->documento->obtenerPorCliente(
                (int) $idUsuario
            );


        return [
            "success" => true,
            "data" => $documentos
        ];
    }


    // =========================================================
    // SUBIR DOCUMENTO
    // =========================================================

    public function subir(
        $idUsuario,
        $tipoDocumento,
        $archivo,
        $observaciones = null
    ) {

        if (!$idUsuario || !is_numeric($idUsuario)) {

            return [
                "success" => false,
                "message" => "Cliente inválido"
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


        $tiposPermitidos = [
            "INE",
            "CURP",
            "RFC",
            "comprobante_domicilio",
            "comprobante_ingresos",
            "otro"
        ];


        if (
            !in_array(
                $tipoDocumento,
                $tiposPermitidos,
                true
            )
        ) {

            return [
                "success" => false,
                "message" => "Tipo de documento inválido"
            ];
        }


        if (
            !$archivo ||
            !isset($archivo["error"]) ||
            $archivo["error"] !== UPLOAD_ERR_OK
        ) {

            return [
                "success" => false,
                "message" => "No se recibió un archivo válido"
            ];
        }


        // Máximo 5 MB

        $tamanoMaximo =
            5 * 1024 * 1024;


        if ($archivo["size"] > $tamanoMaximo) {

            return [
                "success" => false,
                "message" => "El archivo no puede superar los 5 MB"
            ];
        }


        // Extensiones permitidas

        $extensionesPermitidas = [
            "pdf",
            "jpg",
            "jpeg",
            "png"
        ];


        $extension =
            strtolower(
                pathinfo(
                    $archivo["name"],
                    PATHINFO_EXTENSION
                )
            );


        if (
            !in_array(
                $extension,
                $extensionesPermitidas,
                true
            )
        ) {

            return [
                "success" => false,
                "message" => "Formato no permitido. Usa PDF, JPG, JPEG o PNG"
            ];
        }


        // Validación MIME

        $finfo =
            new finfo(FILEINFO_MIME_TYPE);


        $mime =
            $finfo->file(
                $archivo["tmp_name"]
            );


        $mimesPermitidos = [
            "application/pdf",
            "image/jpeg",
            "image/png"
        ];


        if (
            !in_array(
                $mime,
                $mimesPermitidos,
                true
            )
        ) {

            return [
                "success" => false,
                "message" => "El contenido del archivo no es válido"
            ];
        }


        // =====================================================
        // CREAR CARPETA DEL CLIENTE
        // =====================================================

        $directorioBase =
            __DIR__ . "/../uploads/clientes";


        $directorioCliente =
            $directorioBase .
            "/" .
            (int) $idUsuario;


        if (!is_dir($directorioCliente)) {

            mkdir(
                $directorioCliente,
                0775,
                true
            );
        }


        // =====================================================
        // NOMBRE SEGURO
        // =====================================================

        $nombreSeguro =
            bin2hex(
                random_bytes(16)
            ) .
            "." .
            $extension;


        $rutaFisica =
            $directorioCliente .
            "/" .
            $nombreSeguro;


        if (
            !move_uploaded_file(
                $archivo["tmp_name"],
                $rutaFisica
            )
        ) {

            return [
                "success" => false,
                "message" => "No fue posible guardar el archivo"
            ];
        }


        // Ruta que guardamos en MySQL

        $rutaBD =
            "uploads/clientes/" .
            (int) $idUsuario .
            "/" .
            $nombreSeguro;


        $idDocumento =
            $this->documento->crear([

                "id_usuario" =>
                    (int) $idUsuario,

                "tipo_documento" =>
                    $tipoDocumento,

                "nombre_archivo" =>
                    basename(
                        $archivo["name"]
                    ),

                "ruta_archivo" =>
                    $rutaBD,

                "observaciones" =>
                    $observaciones !== ""
                        ? $observaciones
                        : null
            ]);


        return [
            "success" => true,
            "message" => "Documento subido correctamente",
            "id_documento" => $idDocumento
        ];
    }


    // =========================================================
    // CAMBIAR ESTATUS
    // =========================================================

    public function cambiarEstatus(
        $idDocumento,
        $estatus,
        $observaciones = null
    ) {

        if (!$idDocumento || !is_numeric($idDocumento)) {

            return [
                "success" => false,
                "message" => "Documento inválido"
            ];
        }


        $documento =
            $this->documento->obtenerPorId(
                (int) $idDocumento
            );


        if (!$documento) {

            return [
                "success" => false,
                "message" => "Documento no encontrado"
            ];
        }


        $estatusPermitidos = [
            "pendiente",
            "aprobado",
            "rechazado",
            "eliminado"
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
                "message" => "Estatus inválido"
            ];
        }


        $this->documento->actualizarEstatus(
            (int) $idDocumento,
            $estatus,
            $observaciones
        );


        return [
            "success" => true,
            "message" => "Estatus actualizado correctamente"
        ];
    }
}