<?php

session_start();

require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/../config/database.php";

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION["usuario"])) {
    die("Sesión no iniciada.");
}

$usuario = $_SESSION["usuario"];

if (($usuario["rol"] ?? "") !== "cliente") {
    die("Acceso no autorizado.");
}

$idCliente = $usuario["id_usuario"] ?? null;
$idPrestamo = isset($_GET["id_prestamo"]) ? (int) $_GET["id_prestamo"] : 0;

if (!$idCliente || $idPrestamo <= 0) {
    die("Datos inválidos.");
}

global $conexion;

// Datos del cliente y préstamo
$sql = "
    SELECT
        p.id_prestamo,
        p.monto_aprobado,
        p.tasa_interes,
        p.total_intereses,
        p.total_pagar,
        p.plazo_meses,
        p.frecuencia_pago,
        p.numero_cuotas,
        p.monto_cuota,
        p.fecha_desembolso,
        p.fecha_inicio_pago,
        p.fecha_vencimiento,
        p.estatus,

        u.nombre,
        u.apellido_paterno,
        u.apellido_materno,
        u.correo,
        u.telefono

    FROM prestamos p
    INNER JOIN usuarios u ON u.id_usuario = p.id_cliente

    WHERE p.id_prestamo = :id_prestamo
    AND p.id_cliente = :id_cliente

    LIMIT 1
";

$stmt = $conexion->prepare($sql);
$stmt->execute([
    ":id_prestamo" => $idPrestamo,
    ":id_cliente" => $idCliente
]);

$prestamo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prestamo) {
    die("Préstamo no encontrado.");
}

// Cuotas
$sqlCuotas = "
    SELECT
        numero_cuota,
        fecha_vencimiento,
        monto_capital,
        monto_interes,
        monto_total,
        monto_pagado,
        saldo_pendiente,
        estatus
    FROM cuotas
    WHERE id_prestamo = :id_prestamo
    ORDER BY numero_cuota ASC
";

$stmtCuotas = $conexion->prepare($sqlCuotas);
$stmtCuotas->execute([
    ":id_prestamo" => $idPrestamo
]);

$cuotas = $stmtCuotas->fetchAll(PDO::FETCH_ASSOC);

// Total pagado
$sqlPagado = "
    SELECT COALESCE(SUM(monto), 0) AS total_pagado
    FROM pagos
    WHERE id_prestamo = :id_prestamo
    AND id_cliente = :id_cliente
    AND estatus = 'confirmado'
";

$stmtPagado = $conexion->prepare($sqlPagado);
$stmtPagado->execute([
    ":id_prestamo" => $idPrestamo,
    ":id_cliente" => $idCliente
]);

$totalPagado = (float) $stmtPagado->fetchColumn();

$saldoPendiente = max(
    0,
    (float) $prestamo["total_pagar"] - $totalPagado
);

$nombreCompleto = trim(
    $prestamo["nombre"] . " " .
    $prestamo["apellido_paterno"] . " " .
    ($prestamo["apellido_materno"] ?? "")
);

function dinero($cantidad)
{
    return "$" . number_format((float) $cantidad, 2);
}

function fechaFormato($fecha)
{
    if (!$fecha) {
        return "-";
    }

    return date("d/m/Y", strtotime($fecha));
}

// HTML del PDF
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">

<style>

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
    color: #222;
}

.encabezado {
    border-bottom: 2px solid #222;
    padding-bottom: 12px;
    margin-bottom: 20px;
}

.encabezado h1 {
    margin: 0;
    font-size: 24px;
}

.encabezado p {
    margin: 4px 0 0;
    color: #666;
}

.titulo {
    font-size: 16px;
    margin-top: 20px;
    margin-bottom: 8px;
}

.info {
    width: 100%;
    border-collapse: collapse;
}

.info td {
    padding: 6px;
    border-bottom: 1px solid #ddd;
}

.resumen {
    width: 100%;
    margin-top: 15px;
    border-collapse: collapse;
}

.resumen td {
    width: 25%;
    padding: 10px;
    border: 1px solid #ddd;
    text-align: center;
}

.resumen strong {
    display: block;
    font-size: 15px;
    margin-top: 4px;
}

.tabla {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.tabla th {
    background: #eeeeee;
    padding: 7px;
    border: 1px solid #ccc;
    font-size: 10px;
}

.tabla td {
    padding: 6px;
    border: 1px solid #ddd;
    font-size: 10px;
    text-align: center;
}

.pie {
    margin-top: 30px;
    font-size: 9px;
    color: #777;
    text-align: center;
}

</style>
</head>

<body>

<div class="encabezado">
    <h1>Estado de cuenta</h1>
    <p>Sistema de Gestión de Préstamos</p>
</div>

<div class="titulo">Datos del cliente</div>

<table class="info">

<tr>
<td><strong>Cliente:</strong></td>
<td>' . htmlspecialchars($nombreCompleto) . '</td>

<td><strong>Correo:</strong></td>
<td>' . htmlspecialchars($prestamo["correo"]) . '</td>
</tr>

<tr>
<td><strong>Teléfono:</strong></td>
<td>' . htmlspecialchars($prestamo["telefono"] ?? "-") . '</td>

<td><strong>Préstamo:</strong></td>
<td>#' . $prestamo["id_prestamo"] . '</td>
</tr>

</table>

<div class="titulo">Resumen del préstamo</div>

<table class="resumen">

<tr>

<td>
Monto aprobado
<strong>' . dinero($prestamo["monto_aprobado"]) . '</strong>
</td>

<td>
Total a pagar
<strong>' . dinero($prestamo["total_pagar"]) . '</strong>
</td>

<td>
Total pagado
<strong>' . dinero($totalPagado) . '</strong>
</td>

<td>
Saldo pendiente
<strong>' . dinero($saldoPendiente) . '</strong>
</td>

</tr>

</table>

<table class="info" style="margin-top:15px">

<tr>

<td><strong>Tasa:</strong></td>
<td>' . $prestamo["tasa_interes"] . '%</td>

<td><strong>Plazo:</strong></td>
<td>' . $prestamo["plazo_meses"] . ' meses</td>

</tr>

<tr>

<td><strong>Frecuencia:</strong></td>
<td>' . ucfirst($prestamo["frecuencia_pago"]) . '</td>

<td><strong>Número de cuotas:</strong></td>
<td>' . $prestamo["numero_cuotas"] . '</td>

</tr>

<tr>

<td><strong>Fecha desembolso:</strong></td>
<td>' . fechaFormato($prestamo["fecha_desembolso"]) . '</td>

<td><strong>Vencimiento:</strong></td>
<td>' . fechaFormato($prestamo["fecha_vencimiento"]) . '</td>

</tr>

</table>

<div class="titulo">Calendario de cuotas</div>

<table class="tabla">

<thead>

<tr>
<th>#</th>
<th>Vencimiento</th>
<th>Capital</th>
<th>Interés</th>
<th>Total</th>
<th>Pagado</th>
<th>Pendiente</th>
<th>Estado</th>
</tr>

</thead>

<tbody>
';

foreach ($cuotas as $cuota) {

    $html .= '

    <tr>

    <td>' . $cuota["numero_cuota"] . '</td>

    <td>' . fechaFormato($cuota["fecha_vencimiento"]) . '</td>

    <td>' . dinero($cuota["monto_capital"]) . '</td>

    <td>' . dinero($cuota["monto_interes"]) . '</td>

    <td>' . dinero($cuota["monto_total"]) . '</td>

    <td>' . dinero($cuota["monto_pagado"]) . '</td>

    <td>' . dinero($cuota["saldo_pendiente"]) . '</td>

    <td>' . ucfirst($cuota["estatus"]) . '</td>

    </tr>

    ';
}

$html .= '

</tbody>

</table>

<div class="pie">
Documento generado automáticamente por el Sistema de Gestión de Préstamos.
</div>

</body>
</html>
';

// Configuración Dompdf
$options = new Options();

$options->set("isRemoteEnabled", true);
$options->set("defaultFont", "DejaVu Sans");

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper("A4", "portrait");

$dompdf->render();

$nombreArchivo = "estado_cuenta_prestamo_" . $idPrestamo . ".pdf";

$dompdf->stream(
    $nombreArchivo,
    [
        "Attachment" => true
    ]
);