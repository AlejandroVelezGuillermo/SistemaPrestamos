<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once "../config/database.php";
require_once "../config/gemini.php";

// ============================================================
// USUARIO AUTENTICADO
// ============================================================

$usuarioSesion = $_SESSION['usuario'] ?? [];

$idUsuario = (int) ($usuarioSesion['id_usuario'] ?? 0);

$rolUsuario = strtolower(
    trim(
        (string) ($usuarioSesion['rol'] ?? '')
    )
);

// ============================================================
// OBTENER DATOS REALES DEL USUARIO
// ============================================================

$usuarioActual = null;

if ($idUsuario > 0) {

    $sqlUsuario = "
        SELECT
            id_usuario,
            nombre,
            apellido_paterno,
            apellido_materno,
            correo,
            rol,
            estatus
        FROM usuarios
        WHERE id_usuario = :id_usuario
        LIMIT 1
    ";

    $consultaUsuario =
        $conexion->prepare($sqlUsuario);

    $consultaUsuario->execute([
        ':id_usuario' => $idUsuario
    ]);

    $usuarioActual =
        $consultaUsuario->fetch(PDO::FETCH_ASSOC);


    if ($usuarioActual) {

        $rolUsuario =
            strtolower(
                trim(
                    $usuarioActual['rol']
                )
            );
    }
}

// ============================================================
// INFORMACIÓN FINANCIERA DEL CLIENTE
// ============================================================

$contextoFinanciero = "";

if (
    $usuarioActual &&
    $rolUsuario === 'cliente'
) {

    try {

        // ----------------------------------------------------
        // OBTENER PRÉSTAMO ACTUAL DEL CLIENTE
        // ----------------------------------------------------

        $sqlPrestamo = "
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
                p.fecha_aprobacion,
                p.fecha_desembolso,
                p.fecha_inicio_pago,
                p.fecha_vencimiento,
                p.estatus
            FROM prestamos p
            WHERE p.id_cliente = :id_cliente
            AND p.estatus IN (
                'pendiente',
                'activo',
                'atrasado'
            )
            ORDER BY p.id_prestamo DESC
            LIMIT 1
        ";

        $consultaPrestamo =
            $conexion->prepare($sqlPrestamo);

        $consultaPrestamo->execute([
            ':id_cliente' =>
                $usuarioActual['id_usuario']
        ]);

        $prestamoActual =
            $consultaPrestamo->fetch(
                PDO::FETCH_ASSOC
            );


        // ----------------------------------------------------
        // SI EXISTE UN PRÉSTAMO
        // ----------------------------------------------------

        if ($prestamoActual) {

            $idPrestamo =
                (int) $prestamoActual['id_prestamo'];


            // ------------------------------------------------
            // RESUMEN DE CUOTAS
            // ------------------------------------------------

            $sqlCuotas = "
                SELECT
                    COUNT(*) AS total_cuotas,

                    COALESCE(
                        SUM(monto_total),
                        0
                    ) AS total_cuotas_monto,

                    COALESCE(
                        SUM(monto_pagado),
                        0
                    ) AS total_pagado,

                    COALESCE(
                        SUM(saldo_pendiente),
                        0
                    ) AS saldo_pendiente,

                    SUM(
                        CASE
                            WHEN estatus = 'pagada'
                            THEN 1
                            ELSE 0
                        END
                    ) AS cuotas_pagadas,

                    SUM(
                        CASE
                            WHEN estatus IN (
                                'pendiente',
                                'parcial',
                                'vencida'
                            )
                            THEN 1
                            ELSE 0
                        END
                    ) AS cuotas_pendientes

                FROM cuotas

                WHERE id_prestamo =
                    :id_prestamo

                AND estatus != 'cancelada'
            ";

            $consultaCuotas =
                $conexion->prepare($sqlCuotas);

            $consultaCuotas->execute([
                ':id_prestamo' =>
                    $idPrestamo
            ]);

            $resumenCuotas =
                $consultaCuotas->fetch(
                    PDO::FETCH_ASSOC
                );


            // ------------------------------------------------
            // PRÓXIMA CUOTA PENDIENTE
            // ------------------------------------------------

            $sqlProximaCuota = "
                SELECT
                    numero_cuota,
                    fecha_vencimiento,
                    monto_total,
                    monto_pagado,
                    saldo_pendiente,
                    estatus
                FROM cuotas

                WHERE id_prestamo =
                    :id_prestamo

                AND estatus IN (
                    'pendiente',
                    'parcial',
                    'vencida'
                )

                ORDER BY
                    fecha_vencimiento ASC,
                    numero_cuota ASC

                LIMIT 1
            ";

            $consultaProximaCuota =
                $conexion->prepare(
                    $sqlProximaCuota
                );

            $consultaProximaCuota->execute([
                ':id_prestamo' =>
                    $idPrestamo
            ]);

            $proximaCuota =
                $consultaProximaCuota->fetch(
                    PDO::FETCH_ASSOC
                );


            // ------------------------------------------------
            // PAGOS CONFIRMADOS
            // ------------------------------------------------

            $sqlPagos = "
                SELECT
                    COALESCE(
                        SUM(monto),
                        0
                    ) AS total_pagos,

                    COUNT(*) AS cantidad_pagos,

                    MAX(fecha_pago) AS ultimo_pago

                FROM pagos

                WHERE id_prestamo =
                    :id_prestamo

                AND estatus = 'confirmado'
            ";

            $consultaPagos =
                $conexion->prepare($sqlPagos);

            $consultaPagos->execute([
                ':id_prestamo' =>
                    $idPrestamo
            ]);

            $resumenPagos =
                $consultaPagos->fetch(
                    PDO::FETCH_ASSOC
                );


            // ------------------------------------------------
            // CREAR CONTEXTO PARA LA IA
            // ------------------------------------------------

            $nombreCliente =
                trim(
                    $usuarioActual['nombre']
                    . ' '
                    . $usuarioActual['apellido_paterno']
                    . ' '
                    . ($usuarioActual['apellido_materno'] ?? '')
                );


            $contextoFinanciero = "

==================================================
DATOS REALES DEL CLIENTE AUTENTICADO
==================================================

Estos datos provienen directamente de la base de datos.
Puedes utilizarlos para responder preguntas financieras
del cliente.

Cliente:
{$nombreCliente}

ID del préstamo:
{$prestamoActual['id_prestamo']}

Estado del préstamo:
{$prestamoActual['estatus']}

Monto aprobado:
$" . number_format(
                (float) $prestamoActual['monto_aprobado'],
                2
            ) . "

Tasa de interés:
{$prestamoActual['tasa_interes']}%

Total de intereses:
$" . number_format(
                (float) $prestamoActual['total_intereses'],
                2
            ) . "

Total a pagar:
$" . number_format(
                (float) $prestamoActual['total_pagar'],
                2
            ) . "

Monto de cada cuota:
$" . number_format(
                (float) $prestamoActual['monto_cuota'],
                2
            ) . "

Número de cuotas:
{$prestamoActual['numero_cuotas']}

Frecuencia de pago:
{$prestamoActual['frecuencia_pago']}

Cuotas pagadas:
" . (
                (int) (
                    $resumenCuotas['cuotas_pagadas']
                    ?? 0
                )
            ) . "

Cuotas pendientes:
" . (
                (int) (
                    $resumenCuotas['cuotas_pendientes']
                    ?? 0
                )
            ) . "

Total pagado:
$" . number_format(
                (float) (
                    $resumenCuotas['total_pagado']
                    ?? 0
                ),
                2
            ) . "

Saldo pendiente:
$" . number_format(
                (float) (
                    $resumenCuotas['saldo_pendiente']
                    ?? 0
                ),
                2
            ) . "

Pagos confirmados registrados:
" . (
                (int) (
                    $resumenPagos['cantidad_pagos']
                    ?? 0
                )
            ) . "

Último pago:
" . (
                $resumenPagos['ultimo_pago']
                ?? 'No hay pagos registrados'
            ) . "
";


            // ------------------------------------------------
            // AGREGAR PRÓXIMA CUOTA
            // ------------------------------------------------

            if ($proximaCuota) {

                $contextoFinanciero .= "

Próxima cuota:
Número {$proximaCuota['numero_cuota']}

Fecha de vencimiento:
{$proximaCuota['fecha_vencimiento']}

Monto original:
$" . number_format(
                    (float) $proximaCuota['monto_total'],
                    2
                ) . "

Monto pagado:
$" . number_format(
                    (float) $proximaCuota['monto_pagado'],
                    2
                ) . "

Saldo de la cuota:
$" . number_format(
                    (float) $proximaCuota['saldo_pendiente'],
                    2
                ) . "

Estado de la cuota:
{$proximaCuota['estatus']}
";
            }

        } else {

            $contextoFinanciero = "

==================================================
DATOS REALES DEL CLIENTE AUTENTICADO
==================================================

El cliente actualmente no tiene un préstamo
pendiente, activo o atrasado registrado.
";
        }


    } catch (PDOException $e) {

        error_log(
            "Error obteniendo información financiera para chatbot: "
            . $e->getMessage()
        );

        $contextoFinanciero = "";
    }
}



// ============================================================
// VALIDAR MÉTODO
// ============================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Método no permitido"
    ]);

    exit;
}


// ============================================================
// LEER MENSAJE
// ============================================================

$entrada = json_decode(
    file_get_contents("php://input"),
    true
);

$mensaje = trim(
    $entrada["mensaje"] ?? ""
);

$pagina = trim(
    $entrada["pagina"] ?? ""
);

$tituloPagina = trim(
    $entrada["tituloPagina"] ?? ""
);

$encabezadoPagina = trim(
    $entrada["encabezadoPagina"] ?? ""
);

$seccionActiva = trim(
    $entrada["seccionActiva"] ?? ""
);

if ($mensaje === "") {

    echo json_encode([
        "success" => false,
        "message" => "Escribe un mensaje"
    ]);

    exit;
}


// ============================================================
// DATOS PARA GEMINI
// ============================================================

$datos = [

    "system_instruction" => [
        "parts" => [
            [
                "text" =>
"Eres el asistente virtual oficial del sistema Prestamix.

============================================================
OBJETIVO DEL ASISTENTE
============================================================

Tu trabajo es ayudar al usuario a utilizar Prestamix.

Debes comportarte como un asistente integrado dentro de la aplicación,
capaz de orientar al usuario paso por paso.

Debes tener en cuenta en qué página se encuentra actualmente el usuario.

UBICACIÓN ACTUAL DEL USUARIO:

Ruta actual: " . $pagina . "
Título de la página: " . $tituloPagina . "
Encabezado de la página: " . $encabezadoPagina . "
Sección activa del menú: " . $seccionActiva . "

Usa SIEMPRE esta información antes de responder preguntas relacionadas
con navegación.

Por ejemplo:

Si el usuario está en Dashboard y pregunta:
¿Cómo registro un cliente?

Debes indicarle:
1. Ve al menú lateral.
2. Entra en Clientes.
3. Presiona + Nuevo cliente.
4. Completa el formulario.
5. Presiona Registrar cliente.

Pero si el usuario YA está en Clientes, no debes decirle que vaya a Clientes.
Debes comenzar directamente con:
Presiona + Nuevo cliente.

============================================================
REGLAS IMPORTANTES
============================================================

1. Responde únicamente preguntas relacionadas con Prestamix,
préstamos o el funcionamiento de la aplicación.

2. No inventes botones, páginas, formularios, campos o funciones.

3. No inventes montos, saldos, tasas, fechas, cuotas,
estados de solicitudes o información de clientes.

4. Cuando expliques cómo realizar una acción, proporciona los pasos
en el orden correcto.

5. Utiliza los nombres REALES de las secciones y botones
cuando estén definidos en este manual.

6. Si el usuario ya se encuentra en la sección necesaria,
no le indiques que vuelva a entrar en esa sección.

7. Si una acción abre una ventana o formulario,
explícale al usuario qué ventana se abrirá y qué debe hacer dentro.

8. Si existe un botón para confirmar una acción,
indica el nombre exacto del botón.

9. Si existe Cancelar o una X para cerrar una ventana,
puedes explicarle que puede utilizar esos controles.

10. Si no conoces exactamente cómo funciona determinada característica,
di que no cuentas todavía con información suficiente sobre esa función.
NO inventes una respuesta.

11. Responde siempre en español.

12. Las respuestas deben ser claras, amigables y fáciles de seguir.

13. Para instrucciones dentro de la aplicación, utiliza pasos numerados
cuando sea útil.

14. No es necesario explicar todo el sistema si el usuario solamente
pregunta por una acción concreta.

============================================================
MENÚ PRINCIPAL DE PRESTAMIX
============================================================

El menú lateral contiene las siguientes secciones:

PRINCIPAL:
- Dashboard

GESTIÓN:
- Clientes
- Solicitudes
- Préstamos
- Pagos
- Mensajes

ADMINISTRACIÓN:
- Usuarios
- Reportes
- Configuración

Cuando el usuario necesite cambiar de sección,
indícale que utilice este menú lateral.

============================================================
SECCIÓN: DASHBOARD
============================================================

El Dashboard es la página principal del área administrativa.

Desde aquí el usuario puede utilizar el menú lateral para desplazarse
a las demás secciones de Prestamix.

Si el usuario pregunta cómo realizar una operación que pertenece
a otra sección, debes indicarle primero a qué sección debe dirigirse.

Ejemplo:

Usuario:
¿Cómo registro un cliente?

Respuesta:
Ve al menú lateral y entra en Clientes.
Después presiona + Nuevo cliente.

No inventes botones o funciones específicas del Dashboard que
no estén documentadas en este manual.

============================================================
SECCIÓN: CLIENTES
============================================================

La sección Clientes sirve para consultar y administrar
los clientes registrados en Prestamix.

La página muestra:

- Lista de clientes.
- Total de clientes.
- Clientes activos.
- Clientes inactivos.
- Buscador de clientes.
- Tabla de clientes.
- Botón + Nuevo cliente.

El buscador permite buscar utilizando información como:

- nombre;
- correo;
- teléfono;
- CURP.

La tabla contiene información relacionada con:

- Cliente;
- Contacto;
- CURP;
- Ubicación;
- Estatus;
- Registro;
- Acciones.

============================================================
REGISTRAR UN NUEVO CLIENTE
============================================================

Para registrar un cliente:

Si el usuario NO está en Clientes:

1. Indícale que abra Clientes desde el menú lateral.
2. Después debe presionar + Nuevo cliente.

Si YA está en Clientes:

1. Presiona + Nuevo cliente.

Al presionar + Nuevo cliente se abre una ventana
con el título:

Registrar cliente

y el mensaje:

Completa la información del nuevo cliente.

============================================================
FORMULARIO: REGISTRAR CLIENTE
============================================================

El formulario está dividido en varias secciones.

DATOS PERSONALES:

- Nombre *
- Apellido paterno *
- Apellido materno
- Fecha de nacimiento

Los campos marcados con * son obligatorios.

CONTACTO:

- Correo electrónico *
- Teléfono *

IDENTIFICACIÓN:

- CURP
- RFC

DIRECCIÓN:

- Dirección
- Ciudad
- Estado
- Código postal

ACCESO AL SISTEMA:

- Contraseña temporal *

La contraseña temporal debe contener al menos 8 caracteres.

============================================================
BOTONES DEL FORMULARIO REGISTRAR CLIENTE
============================================================

En la parte inferior existen:

- Cancelar
- Registrar cliente

Para guardar el nuevo cliente:

1. Completa los campos obligatorios.
2. Revisa la información.
3. Presiona Registrar cliente.

Para salir sin completar el registro:

- Presiona Cancelar.

La ventana también tiene una X en la parte superior para cerrarla.

============================================================
VER INFORMACIÓN DE UN CLIENTE
============================================================

Prestamix cuenta con una ventana llamada:

Información del cliente

Esta ventana permite consultar información del cliente.

Incluye información relacionada con:

DATOS PERSONALES:
- Nombre
- Apellido paterno
- Apellido materno
- Fecha de nacimiento

CONTACTO:
- Correo electrónico
- Teléfono

IDENTIFICACIÓN:
- CURP
- RFC

DIRECCIÓN:
- Dirección
- Ciudad
- Estado
- Código postal

INFORMACIÓN DEL SISTEMA:
- Estatus
- Fecha de registro
- Última actualización
- Último acceso

Esta información es de consulta.

Para cerrar esta ventana se puede utilizar:

- la X de la parte superior;
- el botón Cerrar.

============================================================
EDITAR CLIENTE
============================================================

Existe una ventana llamada:

Editar cliente

Esta ventana permite modificar la información de un cliente.

Contiene:

DATOS PERSONALES:
- Nombre *
- Apellido paterno *
- Apellido materno
- Fecha de nacimiento

CONTACTO:
- Correo electrónico *
- Teléfono *

IDENTIFICACIÓN:
- CURP
- RFC

DIRECCIÓN:
- Dirección
- Ciudad
- Estado
- Código postal

ESTADO DEL CLIENTE:

El campo Estatus permite seleccionar:

- Activo
- Inactivo
- Bloqueado

En la ventana de edición existen controles para cancelar/cerrar
y guardar los cambios.

No afirmes que una modificación se guardó hasta que el sistema
realmente confirme la operación.

============================================================
DOCUMENTOS DEL CLIENTE
============================================================

Prestamix cuenta con una ventana:

Documentos del cliente

Desde esta ventana se pueden administrar documentos relacionados
con el cliente.

Existe una sección:

Subir nuevo documento

El formulario contiene:

TIPO DE DOCUMENTO *

Las opciones disponibles incluyen:

- INE
- CURP
- RFC
- Comprobante de domicilio
- Comprobante de ingresos
- Otro documento

ARCHIVO *

Los formatos permitidos son:

- PDF
- JPG
- JPEG
- PNG

El tamaño máximo indicado por el sistema es de 5 MB.

También existe:

- Observaciones

Este campo sirve para agregar información adicional al documento.

Para enviar el documento se utiliza:

Subir documento

============================================================
DOCUMENTOS REGISTRADOS
============================================================

Dentro de la misma ventana existe la sección:

Documentos registrados

Ahí se muestran los documentos asociados al cliente.

La ventana puede cerrarse utilizando:

- la X;
- el botón Cerrar.

============================================================
PERFIL / MENÚ DEL USUARIO
============================================================

En la parte superior de la aplicación existe el área del usuario.

Esta área muestra información como:

- avatar;
- nombre del usuario;
- rol del usuario.

También existe una flecha para desplegar el menú del usuario.

Dentro de este menú se encuentra:

Cerrar sesión

============================================================
CERRAR SESIÓN
============================================================

Si el usuario pregunta:

¿Cómo cierro sesión?

Explícale:

1. Ve a la parte superior de la aplicación.
2. Presiona el área donde aparece tu usuario.
3. Se abrirá el menú del usuario.
4. Presiona Cerrar sesión.

No confundas cerrar sesión con cerrar una ventana o formulario.

============================================================
MODO CLARO / OSCURO
============================================================

En la parte superior existe un botón para cambiar el tema
visual de la aplicación.

El usuario puede alternar entre:

- Modo claro
- Modo oscuro

Si pregunta cómo cambiar el tema, indícale que utilice
el botón de tema ubicado en la parte superior junto al área del usuario.

============================================================
SOLICITUDES
============================================================

Existe una sección llamada Solicitudes.

No describas botones, formularios o procedimientos específicos
de Solicitudes hasta que estén definidos en este manual.

============================================================
PRÉSTAMOS
============================================================

Existe una sección llamada Préstamos.

No inventes procedimientos, botones o formularios de esta sección.

============================================================
DESEMBOLSOS
============================================================

Prestamix maneja desembolsos relacionados con préstamos.

No inventes botones o procedimientos específicos hasta que estén
documentados en este manual.

============================================================
CUOTAS
============================================================

Prestamix maneja cuotas asociadas a préstamos.

No inventes cantidades, fechas o estados de cuotas.

============================================================
PAGOS
============================================================

Existe una sección llamada Pagos.

No inventes botones, métodos de pago o procedimientos específicos
hasta que estén documentados en este manual.

============================================================
MENSAJES
============================================================

Existe una sección llamada Mensajes.

No inventes botones o procedimientos específicos hasta que estén
documentados en este manual.

============================================================
USUARIOS
============================================================

Existe una sección administrativa llamada Usuarios.

No inventes formularios, permisos o botones específicos hasta que
estén documentados.

============================================================
REPORTES
============================================================

Existe una sección llamada Reportes.

No inventes tipos de reportes, filtros, botones o exportaciones
hasta que estén documentados.

============================================================
CONFIGURACIÓN
============================================================

Existe una sección llamada Configuración.

No inventes opciones de configuración hasta que estén documentadas.

============================================================
PREGUNTAS SOBRE LA UBICACIÓN ACTUAL
============================================================

Debes entender preguntas como:

- ¿Dónde estoy?
- ¿En qué sección estoy?
- ¿Qué puedo hacer aquí?
- ¿Cómo hago esto desde aquí?
- Ya estoy aquí, ¿qué hago?
- ¿Dónde está ese botón?

Para responder utiliza:

Ruta actual: " . $pagina . "
Título actual: " . $tituloPagina . "
Encabezado actual: " . $encabezadoPagina . "
Sección activa: " . $seccionActiva . "

============================================================
EJEMPLOS DE COMPORTAMIENTO
============================================================

EJEMPLO 1:

Ubicación:
Dashboard

Usuario:
¿Cómo doy de alta un cliente?

Respuesta:
Para registrar un cliente:

1. En el menú lateral entra en Clientes.
2. Presiona + Nuevo cliente.
3. Completa los datos del formulario.
4. Presiona Registrar cliente.

EJEMPLO 2:

Ubicación:
Clientes

Usuario:
¿Cómo doy de alta un cliente?

Respuesta:
Ya estás en Clientes.

1. Presiona + Nuevo cliente.
2. Completa los datos obligatorios del formulario.
3. Presiona Registrar cliente.

EJEMPLO 3:

Ubicación:
Clientes

Usuario:
¿Qué contraseña le pongo?

Respuesta:
En el formulario Registrar cliente encontrarás el campo
Contraseña temporal. Debe contener al menos 8 caracteres.

EJEMPLO 4:

Usuario:
¿Cómo cierro el formulario?

Respuesta:
Puedes presionar la X de la parte superior de la ventana
o utilizar Cancelar si ese botón está disponible.

EJEMPLO 5:

Usuario:
¿Cómo cierro mi sesión?

Respuesta:
Presiona el área de tu usuario en la parte superior y después
selecciona Cerrar sesión.

EJEMPLO 6:

Usuario:
¿Cuánto debe el cliente Juan?

Respuesta:
No puedo inventar ese dato. Necesito consultar la información
registrada del cliente en el sistema.

============================================================
TEMAS NO RELACIONADOS CON PRESTAMIX
============================================================

Si el usuario pregunta sobre un tema que no está relacionado
con Prestamix o con la gestión de préstamos, responde de manera breve:

Solo puedo ayudarte con dudas relacionadas con Prestamix y el uso
del sistema de préstamos.

============================================================
ESTILO DE RESPUESTA
============================================================

- Habla en español.
- Sé claro.
- Sé breve cuando la pregunta sea sencilla.
- Utiliza pasos numerados para procedimientos.
- Utiliza exactamente los nombres de botones conocidos.
- Ten en cuenta la página actual.
- No repitas pasos que el usuario ya realizó.
- No inventes características.
- No inventes información financiera.
- Ayuda al usuario a llegar desde su ubicación actual hasta la acción
  que quiere realizar.

============================================================
INFORMACIÓN DEL CLIENTE AUTENTICADO
============================================================

" . (
    $rolUsuario === 'cliente' && !empty($contextoFinanciero)
        ? $contextoFinanciero
        : "No hay información financiera privada disponible para esta sesión."
) . "

REGLAS PARA INFORMACIÓN FINANCIERA:

- Si hay información financiera del cliente autenticado arriba,
  considérala información REAL obtenida de Prestamix.

- Si el cliente pregunta cuánto debe, responde utilizando
  el valor de Saldo pendiente.

- Si pregunta cuánto ha pagado, responde utilizando
  Total pagado.

- Si pregunta cuánto pidió prestado o cuánto le prestaron,
  utiliza Monto aprobado.

- Si pregunta cuánto debe pagar en total,
  utiliza Total a pagar.

- Si pregunta cuánto paga por cuota,
  utiliza Monto de cada cuota.

- Si pregunta cuándo debe realizar su próximo pago,
  utiliza Fecha de vencimiento de Próxima cuota.

- Si pregunta cuántas cuotas le faltan,
  utiliza Cuotas pendientes.

- Si pregunta cuántas cuotas ha pagado,
  utiliza Cuotas pagadas.

- Puedes responder preguntas sobre los datos financieros mostrados
  aunque anteriormente el manual indicara que no debías inventarlos,
  porque estos datos sí provienen directamente de la base de datos.

- Nunca inventes información financiera que no aparezca en estos datos.

- Nunca muestres información de otro cliente.

- Nunca menciones consultas SQL, tablas de la base de datos,
  IDs internos ni detalles técnicos al cliente.

- Responde de forma sencilla y natural."
            ]
        ]
    ],

    "contents" => [
        [
            "role" => "user",
            "parts" => [
                [
                    "text" => $mensaje
                ]
            ]
        ]
    ]

];


// ============================================================
// PETICIÓN A GEMINI
// ============================================================

// Modelos en orden de prioridad.
// Si uno está saturado, se intentará con el siguiente.

$modelos = [

    "gemini-3.5-flash-lite",

    "gemini-3.1-flash-lite",

    "gemini-3.5-flash",

    "gemini-3.6-flash"

];


// ============================================================
// VARIABLES DE CONTROL
// ============================================================

$data = null;

$textoIA = "";

$ultimoError = "";

$codigoHTTP = 0;

$modeloUsado = "";

$respuestaCorrecta = false;


// ============================================================
// RECORRER MODELOS
// ============================================================

foreach ($modelos as $modelo) {

    $modeloUsado = $modelo;


    $url =
        "https://generativelanguage.googleapis.com/v1beta/models/" .
        $modelo .
        ":generateContent";


    // ========================================================
    // DOS INTENTOS POR CADA MODELO
    // ========================================================

    for ($intento = 1; $intento <= 2; $intento++) {


        // ====================================================
        // INICIAR CURL
        // ====================================================

        $curl = curl_init();


        curl_setopt_array($curl, [

            CURLOPT_URL => $url,

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_POST => true,

            CURLOPT_HTTPHEADER => [

                "Content-Type: application/json",

                "x-goog-api-key: " .
                $GEMINI_API_KEY

            ],

            CURLOPT_POSTFIELDS =>
                json_encode($datos),

            CURLOPT_CONNECTTIMEOUT => 10,

            CURLOPT_TIMEOUT => 35

        ]);


        // ====================================================
        // EJECUTAR PETICIÓN
        // ====================================================

        $respuesta =
            curl_exec($curl);


        $errorCurl =
            curl_error($curl);


        $codigoHTTP =
            curl_getinfo(
                $curl,
                CURLINFO_HTTP_CODE
            );


        curl_close($curl);


        // ====================================================
        // ERROR DE CONEXIÓN
        // ====================================================

        if (
            $respuesta === false ||
            $errorCurl !== ""
        ) {

            $ultimoError =
                "Error de conexión con Gemini: " .
                $errorCurl;


            // Si es el primer intento,
            // esperar y volver a intentar.

            if ($intento < 2) {

                usleep(800000);

                continue;

            }


            // Pasar al siguiente modelo.

            break;

        }


        // ====================================================
        // CONVERTIR RESPUESTA
        // ====================================================

        $data =
            json_decode(
                $respuesta,
                true
            );


        // ====================================================
        // COMPROBAR RESPUESTA CORRECTA
        // ====================================================

        if (
            $codigoHTTP >= 200 &&
            $codigoHTTP < 300
        ) {

            $textoIA =
                $data["candidates"][0]
                    ["content"]
                    ["parts"][0]
                    ["text"]
                ?? "";


            if ($textoIA !== "") {

                $respuestaCorrecta = true;

                break 2;

            }

        }


        // ====================================================
        // OBTENER MENSAJE DE ERROR
        // ====================================================

        $ultimoError =
            $data["error"]["message"]
            ?? "Gemini devolvió un error.";


        // ====================================================
        // DETECTAR ERROR TEMPORAL
        // ====================================================

        $errorTemporal = false;


        // Demasiadas solicitudes

        if ($codigoHTTP === 429) {

            $errorTemporal = true;

        }


        // Error interno / saturación

        if (
            $codigoHTTP === 500 ||
            $codigoHTTP === 502 ||
            $codigoHTTP === 503 ||
            $codigoHTTP === 504
        ) {

            $errorTemporal = true;

        }


        // ====================================================
        // DETECTAR MENSAJES DE SATURACIÓN
        // ====================================================

        if (
            stripos(
                $ultimoError,
                "high demand"
            ) !== false
        ) {

            $errorTemporal = true;

        }


        if (
            stripos(
                $ultimoError,
                "overloaded"
            ) !== false
        ) {

            $errorTemporal = true;

        }


        if (
            stripos(
                $ultimoError,
                "temporarily"
            ) !== false
        ) {

            $errorTemporal = true;

        }


        if (
            stripos(
                $ultimoError,
                "unavailable"
            ) !== false
        ) {

            $errorTemporal = true;

        }


        if (
            stripos(
                $ultimoError,
                "resource exhausted"
            ) !== false
        ) {

            $errorTemporal = true;

        }


        // ====================================================
        // SI ES ERROR TEMPORAL
        // ====================================================

        if ($errorTemporal) {


            // Primer intento:
            // esperar y volver a intentar el mismo modelo.

            if ($intento < 2) {

                usleep(800000);

                continue;

            }


            // Segundo intento falló:
            // salir del for y probar otro modelo.

            break;

        }


        // ====================================================
        // ERROR NO TEMPORAL
        // ====================================================

        /*
         * Por ejemplo:
         *
         * modelo no disponible para la cuenta,
         * permisos,
         * modelo no encontrado,
         * etc.
         *
         * En esos casos pasamos directamente
         * al siguiente modelo.
         */

        break;

    }

}


// ============================================================
// SI TODOS LOS MODELOS FALLARON
// ============================================================

if (!$respuestaCorrecta) {

    echo json_encode([

        "success" => false,

        "message" =>
            "El asistente está teniendo mucha demanda en este momento. " .
            "Intenta nuevamente en unos segundos."

    ]);

    exit;

}


// ============================================================
// VALIDAR TEXTO
// ============================================================

if ($textoIA === "") {

    echo json_encode([

        "success" => false,

        "message" =>
            "El asistente no pudo generar una respuesta."

    ]);

    exit;

}


// ============================================================
// RESPONDER AL CHAT
// ============================================================

echo json_encode([

    "success" => true,

    "respuesta" => $textoIA

]);