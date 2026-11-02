// ============================================================
// ELEMENTOS GENERALES
// ============================================================

const nombreUsuario =
    document.getElementById("nombreUsuario");

const rolUsuario =
    document.getElementById("rolUsuario");

const avatarUsuario =
    document.getElementById("avatarUsuario");

const usuarioMenu =
    document.getElementById("usuarioMenu");

const usuarioDropdown =
    document.getElementById("usuarioDropdown");

const logoutButtonPerfil =
    document.getElementById("logoutButtonPerfil");

const tablaClientesBody =
    document.getElementById("tablaClientesBody");

const totalClientes =
    document.getElementById("totalClientes");

const clientesActivos =
    document.getElementById("clientesActivos");

const clientesInactivos =
    document.getElementById("clientesInactivos");

const buscadorClientes =
    document.getElementById("buscadorClientes");

const mensajeClientes =
    document.getElementById("mensajeClientes");


// ============================================================
// NUEVO CLIENTE
// ============================================================

const btnNuevoCliente =
    document.getElementById("btnNuevoCliente");

const modalCliente =
    document.getElementById("modalCliente");

const cerrarModal =
    document.getElementById("cerrarModal");

const cancelarCliente =
    document.getElementById("cancelarCliente");

const formCliente =
    document.getElementById("formCliente");

const guardarCliente =
    document.getElementById("guardarCliente");

const mensajeFormulario =
    document.getElementById("mensajeFormulario");


// ============================================================
// VER CLIENTE
// ============================================================

const modalVerCliente =
    document.getElementById("modalVerCliente");

const cerrarModalVer =
    document.getElementById("cerrarModalVer");

const cerrarDetalleCliente =
    document.getElementById("cerrarDetalleCliente");

const detalleClienteId =
    document.getElementById("detalleClienteId");

const detalleNombre =
    document.getElementById("detalleNombre");

const detalleApellidoPaterno =
    document.getElementById("detalleApellidoPaterno");

const detalleApellidoMaterno =
    document.getElementById("detalleApellidoMaterno");

const detalleFechaNacimiento =
    document.getElementById("detalleFechaNacimiento");

const detalleCorreo =
    document.getElementById("detalleCorreo");

const detalleTelefono =
    document.getElementById("detalleTelefono");

const detalleCurp =
    document.getElementById("detalleCurp");

const detalleRfc =
    document.getElementById("detalleRfc");

const detalleDireccion =
    document.getElementById("detalleDireccion");

const detalleCiudad =
    document.getElementById("detalleCiudad");

const detalleEstado =
    document.getElementById("detalleEstado");

const detalleCodigoPostal =
    document.getElementById("detalleCodigoPostal");

const detalleEstatus =
    document.getElementById("detalleEstatus");

const detalleFechaRegistro =
    document.getElementById("detalleFechaRegistro");

const detalleFechaActualizacion =
    document.getElementById("detalleFechaActualizacion");

const detalleUltimoAcceso =
    document.getElementById("detalleUltimoAcceso");


// ============================================================
// EDITAR CLIENTE
// ============================================================

const modalEditarCliente =
    document.getElementById("modalEditarCliente");

const cerrarModalEditar =
    document.getElementById("cerrarModalEditar");

const cancelarEditar =
    document.getElementById("cancelarEditar");

const formEditarCliente =
    document.getElementById("formEditarCliente");

const guardarEdicion =
    document.getElementById("guardarEdicion");

const mensajeEditar =
    document.getElementById("mensajeEditar");

const editarClienteId =
    document.getElementById("editarClienteId");

const editarIdCliente =
    document.getElementById("editarIdCliente");

const editarNombre =
    document.getElementById("editarNombre");

const editarApellidoPaterno =
    document.getElementById("editarApellidoPaterno");

const editarApellidoMaterno =
    document.getElementById("editarApellidoMaterno");

const editarFechaNacimiento =
    document.getElementById("editarFechaNacimiento");

const editarCorreo =
    document.getElementById("editarCorreo");

const editarTelefono =
    document.getElementById("editarTelefono");

const editarCurp =
    document.getElementById("editarCurp");

const editarRfc =
    document.getElementById("editarRfc");

const editarDireccion =
    document.getElementById("editarDireccion");

const editarCiudad =
    document.getElementById("editarCiudad");

const editarEstado =
    document.getElementById("editarEstado");

const editarCodigoPostal =
    document.getElementById("editarCodigoPostal");

const editarEstatus =
    document.getElementById("editarEstatus");


// ============================================================
// DOCUMENTOS
// ============================================================

const modalDocumentos =
    document.getElementById("modalDocumentos");

const cerrarModalDocumentos =
    document.getElementById("cerrarModalDocumentos");

const cerrarDocumentos =
    document.getElementById("cerrarDocumentos");

const documentosClienteNombre =
    document.getElementById("documentosClienteNombre");

const documentosIdCliente =
    document.getElementById("documentosIdCliente");

const formDocumento =
    document.getElementById("formDocumento");

const tipoDocumento =
    document.getElementById("tipoDocumento");

const archivoDocumento =
    document.getElementById("archivoDocumento");

const observacionesDocumento =
    document.getElementById("observacionesDocumento");

const btnSubirDocumento =
    document.getElementById("btnSubirDocumento");

const mensajeDocumento =
    document.getElementById("mensajeDocumento");

const listaDocumentos =
    document.getElementById("listaDocumentos");


let clientes = [];


// ============================================================
// COMPROBAR SESIÓN
// ============================================================

async function comprobarSesion() {

    try {

        const respuesta = await fetch(
            "../../backend/routes/sesion.php",
            {
                method: "GET",
                credentials: "include"
            }
        );


        const datos =
            await respuesta.json();


        if (!datos.sesion) {

            window.location.href =
                "../index.html";

            return false;
        }


        const usuario =
            datos.usuario;


        if (
            usuario.rol !== "administrador" &&
            usuario.rol !== "gerente"
        ) {

            window.location.href =
                "dashboard.html";

            return false;
        }


        nombreUsuario.textContent =
            usuario.nombre +
            " " +
            (usuario.apellido_paterno || "");


        rolUsuario.textContent =
            usuario.rol;


        avatarUsuario.textContent =
            usuario.nombre
                ? usuario.nombre.charAt(0).toUpperCase()
                : "U";


        return true;


    } catch (error) {

        console.error(error);

        window.location.href =
            "../index.html";

        return false;
    }

}


// ============================================================
// CARGAR CLIENTES
// ============================================================

async function cargarClientes() {

    tablaClientesBody.innerHTML = `
        <tr>
            <td colspan="7" class="tabla-cargando">
                Cargando clientes...
            </td>
        </tr>
    `;


    try {

        const respuesta = await fetch(
            "../../backend/routes/clientes.php",
            {
                method: "GET",
                credentials: "include"
            }
        );


        const datos =
            await respuesta.json();


        if (
            !respuesta.ok ||
            !datos.success
        ) {

            throw new Error(
                datos.message ||
                "No fue posible cargar los clientes"
            );
        }


        clientes =
            datos.data || [];


        actualizarResumen();

        mostrarClientes(clientes);


    } catch (error) {

        console.error(error);


        tablaClientesBody.innerHTML = `
            <tr>
                <td colspan="7" class="tabla-vacia">
                    No fue posible cargar los clientes.
                </td>
            </tr>
        `;


        mostrarMensajeClientes(
            "No fue posible cargar los clientes.",
            "error"
        );
    }

}


// ============================================================
// MOSTRAR CLIENTES
// ============================================================

function mostrarClientes(lista) {

    if (lista.length === 0) {

        tablaClientesBody.innerHTML = `
            <tr>
                <td colspan="7" class="tabla-vacia">
                    No hay clientes registrados.
                </td>
            </tr>
        `;

        return;
    }


    tablaClientesBody.innerHTML =
        lista.map(cliente => {


            const nombreCompleto = [
                cliente.nombre,
                cliente.apellido_paterno,
                cliente.apellido_materno
            ]
                .filter(Boolean)
                .join(" ");


            const ubicacion = [
                cliente.ciudad,
                cliente.estado
            ]
                .filter(Boolean)
                .join(", ") || "Sin registrar";


            const estatusClase =
                obtenerClaseEstatus(
                    cliente.estatus
                );


            return `
                <tr>

                    <td>

                        <span class="cliente-nombre">
                            ${escaparHTML(nombreCompleto)}
                        </span>

                        <span class="cliente-id">
                            Cliente #${cliente.id_usuario}
                        </span>

                    </td>


                    <td class="cliente-contacto">

                        <span>
                            ${escaparHTML(cliente.correo)}
                        </span>

                        <small>
                            ${escaparHTML(
                                cliente.telefono ||
                                "Sin teléfono"
                            )}
                        </small>

                    </td>


                    <td>
                        ${escaparHTML(
                            cliente.curp ||
                            "Sin registrar"
                        )}
                    </td>


                    <td>
                        ${escaparHTML(ubicacion)}
                    </td>


                    <td>

                        <span
                            class="estado-badge ${estatusClase}"
                        >
                            ${escaparHTML(cliente.estatus)}
                        </span>

                    </td>


                    <td>
                        ${formatearFecha(
                            cliente.fecha_creacion
                        )}
                    </td>


                    <td>

                        <div class="acciones">

                            <button
                                type="button"
                                class="btn-accion btn-ver-cliente"
                                data-id="${cliente.id_usuario}"
                            >
                                Ver
                            </button>


                            <button
                                type="button"
                                class="btn-accion btn-editar-cliente"
                                data-id="${cliente.id_usuario}"
                            >
                                Editar
                            </button>


                            <button
                                type="button"
                                class="btn-accion btn-documentos-cliente"
                                data-id="${cliente.id_usuario}"
                            >
                                Documentos
                            </button>

                        </div>

                    </td>

                </tr>
            `;

        }).join("");

}


// ============================================================
// ACCIONES TABLA
// ============================================================

tablaClientesBody.addEventListener(
    "click",
    function (event) {

        const botonVer =
            event.target.closest(
                ".btn-ver-cliente"
            );


        if (botonVer) {

            verCliente(
                botonVer.dataset.id
            );

            return;
        }


        const botonEditar =
            event.target.closest(
                ".btn-editar-cliente"
            );


        if (botonEditar) {

            abrirEditarCliente(
                botonEditar.dataset.id
            );

            return;
        }


        const botonDocumentos =
            event.target.closest(
                ".btn-documentos-cliente"
            );


        if (botonDocumentos) {

            abrirDocumentosCliente(
                botonDocumentos.dataset.id
            );

        }

    }
);


// ============================================================
// VER CLIENTE
// ============================================================

async function verCliente(idCliente) {

    try {

        const resultado =
            await obtenerCliente(idCliente);


        if (!resultado) {
            return;
        }


        const cliente =
            resultado;


        detalleClienteId.textContent =
            "Cliente #" + cliente.id_usuario;


        detalleNombre.value =
            cliente.nombre || "Sin registrar";


        detalleApellidoPaterno.value =
            cliente.apellido_paterno ||
            "Sin registrar";


        detalleApellidoMaterno.value =
            cliente.apellido_materno ||
            "Sin registrar";


        detalleFechaNacimiento.value =
            formatearFecha(
                cliente.fecha_nacimiento
            );


        detalleCorreo.value =
            cliente.correo ||
            "Sin registrar";


        detalleTelefono.value =
            cliente.telefono ||
            "Sin registrar";


        detalleCurp.value =
            cliente.curp ||
            "Sin registrar";


        detalleRfc.value =
            cliente.rfc ||
            "Sin registrar";


        detalleDireccion.value =
            cliente.direccion ||
            "Sin registrar";


        detalleCiudad.value =
            cliente.ciudad ||
            "Sin registrar";


        detalleEstado.value =
            cliente.estado ||
            "Sin registrar";


        detalleCodigoPostal.value =
            cliente.codigo_postal ||
            "Sin registrar";


        detalleEstatus.value =
            capitalizar(
                cliente.estatus || ""
            );


        detalleFechaRegistro.value =
            formatearFechaHora(
                cliente.fecha_creacion
            );


        detalleFechaActualizacion.value =
            formatearFechaHora(
                cliente.fecha_actualizacion
            );


        detalleUltimoAcceso.value =
            cliente.ultimo_acceso
                ? formatearFechaHora(
                    cliente.ultimo_acceso
                )
                : "Nunca";


        modalVerCliente.classList.add(
            "show"
        );


    } catch (error) {

        console.error(error);


        mostrarMensajeClientes(
            "No fue posible consultar el cliente.",
            "error"
        );
    }

}


// ============================================================
// OBTENER CLIENTE
// ============================================================

async function obtenerCliente(idCliente) {

    const respuesta = await fetch(
        "../../backend/routes/clientes.php?id=" +
        encodeURIComponent(idCliente),
        {
            method: "GET",
            credentials: "include"
        }
    );


    const resultado =
        await respuesta.json();


    if (
        !respuesta.ok ||
        !resultado.success
    ) {

        mostrarMensajeClientes(
            resultado.message ||
            "No fue posible consultar el cliente.",
            "error"
        );

        return null;
    }


    return resultado.data;
}


// ============================================================
// ABRIR EDITAR CLIENTE
// ============================================================

async function abrirEditarCliente(idCliente) {

    limpiarMensajeEditar();


    try {

        const cliente =
            await obtenerCliente(idCliente);


        if (!cliente) {
            return;
        }


        editarIdCliente.value =
            cliente.id_usuario;


        editarClienteId.textContent =
            "Cliente #" + cliente.id_usuario;


        editarNombre.value =
            cliente.nombre || "";


        editarApellidoPaterno.value =
            cliente.apellido_paterno || "";


        editarApellidoMaterno.value =
            cliente.apellido_materno || "";


        editarFechaNacimiento.value =
            cliente.fecha_nacimiento || "";


        editarCorreo.value =
            cliente.correo || "";


        editarTelefono.value =
            cliente.telefono || "";


        editarCurp.value =
            cliente.curp || "";


        editarRfc.value =
            cliente.rfc || "";


        editarDireccion.value =
            cliente.direccion || "";


        editarCiudad.value =
            cliente.ciudad || "";


        editarEstado.value =
            cliente.estado || "";


        editarCodigoPostal.value =
            cliente.codigo_postal || "";


        editarEstatus.value =
            cliente.estatus || "activo";


        modalEditarCliente.classList.add(
            "show"
        );


    } catch (error) {

        console.error(error);


        mostrarMensajeClientes(
            "No fue posible cargar el cliente.",
            "error"
        );
    }

}


// ============================================================
// GUARDAR EDICIÓN
// ============================================================

formEditarCliente.addEventListener(
    "submit",
    async function (event) {

        event.preventDefault();


        limpiarMensajeEditar();


        const idCliente =
            editarIdCliente.value;


        const datos = {

            nombre:
                editarNombre.value.trim(),

            apellido_paterno:
                editarApellidoPaterno.value.trim(),

            apellido_materno:
                editarApellidoMaterno.value.trim(),

            fecha_nacimiento:
                editarFechaNacimiento.value,

            correo:
                editarCorreo.value.trim(),

            telefono:
                editarTelefono.value.trim(),

            curp:
                editarCurp.value.trim(),

            rfc:
                editarRfc.value.trim(),

            direccion:
                editarDireccion.value.trim(),

            ciudad:
                editarCiudad.value.trim(),

            estado:
                editarEstado.value.trim(),

            codigo_postal:
                editarCodigoPostal.value.trim(),

            estatus:
                editarEstatus.value
        };


        guardarEdicion.disabled = true;

        guardarEdicion.textContent =
            "Guardando...";


        try {

            const respuesta = await fetch(
                "../../backend/routes/clientes.php?id=" +
                encodeURIComponent(idCliente),
                {
                    method: "PUT",

                    credentials: "include",

                    headers: {
                        "Content-Type":
                            "application/json"
                    },

                    body: JSON.stringify(datos)
                }
            );


            const resultado =
                await respuesta.json();


            if (
                !respuesta.ok ||
                !resultado.success
            ) {

                mostrarMensajeEditar(
                    resultado.message ||
                    "No fue posible actualizar el cliente.",
                    "error"
                );

                return;
            }


            mostrarMensajeEditar(
                resultado.message,
                "exito"
            );


            await cargarClientes();


            setTimeout(
                cerrarModalEditarCliente,
                700
            );


        } catch (error) {

            console.error(error);


            mostrarMensajeEditar(
                "No se pudo conectar con el servidor.",
                "error"
            );


        } finally {

            guardarEdicion.disabled = false;

            guardarEdicion.textContent =
                "Guardar cambios";

        }

    }
);


// ============================================================
// ABRIR DOCUMENTOS
// ============================================================

async function abrirDocumentosCliente(
    idCliente
) {

    limpiarMensajeDocumento();

    formDocumento.reset();


    try {

        const cliente =
            await obtenerCliente(idCliente);


        if (!cliente) {
            return;
        }


        documentosIdCliente.value =
            cliente.id_usuario;


        const nombreCompleto = [
            cliente.nombre,
            cliente.apellido_paterno,
            cliente.apellido_materno
        ]
            .filter(Boolean)
            .join(" ");


        documentosClienteNombre.textContent =
            "Cliente #" +
            cliente.id_usuario +
            " - " +
            nombreCompleto;


        modalDocumentos.classList.add(
            "show"
        );


        await cargarDocumentos(
            cliente.id_usuario
        );


    } catch (error) {

        console.error(error);


        mostrarMensajeClientes(
            "No fue posible abrir los documentos.",
            "error"
        );
    }

}


// ============================================================
// CARGAR DOCUMENTOS
// ============================================================

async function cargarDocumentos(idCliente) {

    listaDocumentos.innerHTML = `
        <div class="tabla-vacia">
            Cargando documentos...
        </div>
    `;


    try {

        const respuesta = await fetch(
            "../../backend/routes/documentos_clientes.php?id_usuario=" +
            encodeURIComponent(idCliente),
            {
                method: "GET",
                credentials: "include"
            }
        );


        const resultado =
            await respuesta.json();


        if (
            !respuesta.ok ||
            !resultado.success
        ) {

            throw new Error(
                resultado.message ||
                "No fue posible cargar los documentos."
            );
        }


        mostrarDocumentos(
            resultado.data || []
        );


    } catch (error) {

        console.error(error);


        listaDocumentos.innerHTML = `
            <div class="tabla-vacia">
                No fue posible cargar los documentos.
            </div>
        `;
    }

}


// ============================================================
// MOSTRAR DOCUMENTOS
// ============================================================

function mostrarDocumentos(documentos) {

    if (documentos.length === 0) {

        listaDocumentos.innerHTML = `
            <div class="tabla-vacia">
                El cliente todavía no tiene documentos.
            </div>
        `;

        return;
    }


    listaDocumentos.innerHTML =
        documentos.map(documento => {


            const claseEstatus =
                obtenerClaseDocumento(
                    documento.estatus
                );


            const rutaArchivo =
                "../../backend/" +
                documento.ruta_archivo;


            return `
                <div class="documento-card">

                    <div class="documento-info">

                        <strong>
                            ${escaparHTML(
                                mostrarTipoDocumento(
                                    documento.tipo_documento
                                )
                            )}
                        </strong>

                        <span>
                            ${escaparHTML(
                                documento.nombre_archivo
                            )}
                        </span>

                        <small>
                            Subido:
                            ${formatearFechaHora(
                                documento.fecha_subida
                            )}
                        </small>

                        ${
                            documento.observaciones
                                ? `
                                    <small>
                                        Observaciones:
                                        ${escaparHTML(
                                            documento.observaciones
                                        )}
                                    </small>
                                `
                                : ""
                        }

                    </div>


                    <div class="documento-acciones">

                        <span
                            class="documento-estatus ${claseEstatus}"
                        >
                            ${capitalizar(
                                documento.estatus
                            )}
                        </span>


                        <a
                            href="${escaparHTML(rutaArchivo)}"
                            target="_blank"
                            rel="noopener"
                            class="btn-accion"
                        >
                            Ver archivo
                        </a>


                        ${
                            documento.estatus !== "aprobado"
                                ? `
                                    <button
                                        type="button"
                                        class="btn-accion btn-aprobar-documento"
                                        data-id="${documento.id_documento}"
                                    >
                                        Aprobar
                                    </button>
                                `
                                : ""
                        }


                        ${
                            documento.estatus !== "rechazado"
                                ? `
                                    <button
                                        type="button"
                                        class="btn-accion btn-rechazar-documento"
                                        data-id="${documento.id_documento}"
                                    >
                                        Rechazar
                                    </button>
                                `
                                : ""
                        }

                    </div>

                </div>
            `;

        }).join("");

}


// ============================================================
// SUBIR DOCUMENTO
// ============================================================

formDocumento.addEventListener(
    "submit",
    async function (event) {

        event.preventDefault();


        limpiarMensajeDocumento();


        const idCliente =
            documentosIdCliente.value;


        const archivo =
            archivoDocumento.files[0];


        if (!archivo) {

            mostrarMensajeDocumento(
                "Selecciona un archivo.",
                "error"
            );

            return;
        }


        const formData =
            new FormData();


        formData.append(
            "id_usuario",
            idCliente
        );


        formData.append(
            "tipo_documento",
            tipoDocumento.value
        );


        formData.append(
            "archivo",
            archivo
        );


        formData.append(
            "observaciones",
            observacionesDocumento.value.trim()
        );


        btnSubirDocumento.disabled = true;

        btnSubirDocumento.textContent =
            "Subiendo...";


        try {

            const respuesta = await fetch(
                "../../backend/routes/documentos_clientes.php",
                {
                    method: "POST",
                    credentials: "include",
                    body: formData
                }
            );


            const resultado =
                await respuesta.json();


            if (
                !respuesta.ok ||
                !resultado.success
            ) {

                mostrarMensajeDocumento(
                    resultado.message ||
                    "No fue posible subir el documento.",
                    "error"
                );

                return;
            }


            mostrarMensajeDocumento(
                resultado.message,
                "exito"
            );


            formDocumento.reset();


            await cargarDocumentos(
                idCliente
            );


        } catch (error) {

            console.error(error);


            mostrarMensajeDocumento(
                "No se pudo conectar con el servidor.",
                "error"
            );


        } finally {

            btnSubirDocumento.disabled = false;

            btnSubirDocumento.textContent =
                "Subir documento";

        }

    }
);


// ============================================================
// APROBAR / RECHAZAR DOCUMENTO
// ============================================================

listaDocumentos.addEventListener(
    "click",
    async function (event) {

        const botonAprobar =
            event.target.closest(
                ".btn-aprobar-documento"
            );


        if (botonAprobar) {

            await cambiarEstatusDocumento(
                botonAprobar.dataset.id,
                "aprobado"
            );

            return;
        }


        const botonRechazar =
            event.target.closest(
                ".btn-rechazar-documento"
            );


        if (botonRechazar) {

            const observacion =
                prompt(
                    "Motivo del rechazo (opcional):"
                ) || "";


            await cambiarEstatusDocumento(
                botonRechazar.dataset.id,
                "rechazado",
                observacion
            );

        }

    }
);


// ============================================================
// CAMBIAR ESTATUS DOCUMENTO
// ============================================================

async function cambiarEstatusDocumento(
    idDocumento,
    estatus,
    observaciones = ""
) {

    try {

        const respuesta = await fetch(
            "../../backend/routes/documentos_clientes.php?id_documento=" +
            encodeURIComponent(idDocumento),
            {
                method: "PUT",

                credentials: "include",

                headers: {
                    "Content-Type":
                        "application/json"
                },

                body: JSON.stringify({
                    estatus: estatus,
                    observaciones: observaciones
                })
            }
        );


        const resultado =
            await respuesta.json();


        if (
            !respuesta.ok ||
            !resultado.success
        ) {

            mostrarMensajeDocumento(
                resultado.message ||
                "No fue posible actualizar el documento.",
                "error"
            );

            return;
        }


        mostrarMensajeDocumento(
            resultado.message,
            "exito"
        );


        await cargarDocumentos(
            documentosIdCliente.value
        );


    } catch (error) {

        console.error(error);


        mostrarMensajeDocumento(
            "No se pudo actualizar el documento.",
            "error"
        );
    }

}


// ============================================================
// RESUMEN
// ============================================================

function actualizarResumen() {

    totalClientes.textContent =
        clientes.length;


    const activos =
        clientes.filter(
            cliente =>
                cliente.estatus === "activo"
        ).length;


    const inactivos =
        clientes.filter(
            cliente =>
                cliente.estatus !== "activo"
        ).length;


    clientesActivos.textContent =
        activos;


    clientesInactivos.textContent =
        inactivos;

}


// ============================================================
// BUSCADOR
// ============================================================

buscadorClientes.addEventListener(
    "input",
    function () {

        const texto =
            buscadorClientes.value
                .trim()
                .toLowerCase();


        if (!texto) {

            mostrarClientes(clientes);

            return;
        }


        const filtrados =
            clientes.filter(cliente => {

                const valores = [
                    cliente.nombre,
                    cliente.apellido_paterno,
                    cliente.apellido_materno,
                    cliente.correo,
                    cliente.telefono,
                    cliente.curp,
                    cliente.rfc,
                    cliente.ciudad,
                    cliente.estado
                ];


                return valores.some(valor =>

                    String(valor || "")
                        .toLowerCase()
                        .includes(texto)

                );

            });


        mostrarClientes(filtrados);

    }
);


// ============================================================
// NUEVO CLIENTE
// ============================================================

btnNuevoCliente.addEventListener(
    "click",
    function () {

        limpiarMensajeFormulario();

        formCliente.reset();

        modalCliente.classList.add(
            "show"
        );

    }
);


cerrarModal.addEventListener(
    "click",
    cerrarModalCliente
);


cancelarCliente.addEventListener(
    "click",
    cerrarModalCliente
);


function cerrarModalCliente() {

    modalCliente.classList.remove(
        "show"
    );

    formCliente.reset();

    limpiarMensajeFormulario();

}


// ============================================================
// REGISTRAR CLIENTE
// ============================================================

formCliente.addEventListener(
    "submit",
    async function (event) {

        event.preventDefault();


        limpiarMensajeFormulario();


        const datos = {

            nombre:
                document.getElementById("nombre")
                    .value.trim(),

            apellido_paterno:
                document.getElementById("apellidoPaterno")
                    .value.trim(),

            apellido_materno:
                document.getElementById("apellidoMaterno")
                    .value.trim(),

            fecha_nacimiento:
                document.getElementById("fechaNacimiento")
                    .value,

            correo:
                document.getElementById("correoCliente")
                    .value.trim(),

            telefono:
                document.getElementById("telefono")
                    .value.trim(),

            curp:
                document.getElementById("curp")
                    .value.trim(),

            rfc:
                document.getElementById("rfc")
                    .value.trim(),

            direccion:
                document.getElementById("direccion")
                    .value.trim(),

            ciudad:
                document.getElementById("ciudad")
                    .value.trim(),

            estado:
                document.getElementById("estado")
                    .value.trim(),

            codigo_postal:
                document.getElementById("codigoPostal")
                    .value.trim(),

            password:
                document.getElementById("passwordCliente")
                    .value

        };


        guardarCliente.disabled = true;

        guardarCliente.textContent =
            "Registrando...";


        try {

            const respuesta = await fetch(
                "../../backend/routes/clientes.php",
                {
                    method: "POST",

                    credentials: "include",

                    headers: {
                        "Content-Type":
                            "application/json"
                    },

                    body: JSON.stringify(datos)
                }
            );


            const resultado =
                await respuesta.json();


            if (
                !respuesta.ok ||
                !resultado.success
            ) {

                mostrarMensajeFormulario(
                    resultado.message ||
                    "No fue posible registrar el cliente.",
                    "error"
                );

                return;
            }


            mostrarMensajeFormulario(
                resultado.message,
                "exito"
            );


            await cargarClientes();


            setTimeout(
                cerrarModalCliente,
                800
            );


        } catch (error) {

            console.error(error);


            mostrarMensajeFormulario(
                "No se pudo conectar con el servidor.",
                "error"
            );


        } finally {

            guardarCliente.disabled = false;

            guardarCliente.textContent =
                "Registrar cliente";

        }

    }
);


// ============================================================
// CERRAR MODALES
// ============================================================

cerrarModalVer.addEventListener(
    "click",
    cerrarFichaCliente
);


cerrarDetalleCliente.addEventListener(
    "click",
    cerrarFichaCliente
);


function cerrarFichaCliente() {

    modalVerCliente.classList.remove(
        "show"
    );

}


cerrarModalEditar.addEventListener(
    "click",
    cerrarModalEditarCliente
);


cancelarEditar.addEventListener(
    "click",
    cerrarModalEditarCliente
);


function cerrarModalEditarCliente() {

    modalEditarCliente.classList.remove(
        "show"
    );

    formEditarCliente.reset();

    limpiarMensajeEditar();

}


cerrarModalDocumentos.addEventListener(
    "click",
    cerrarModalDocumentosCliente
);


cerrarDocumentos.addEventListener(
    "click",
    cerrarModalDocumentosCliente
);


function cerrarModalDocumentosCliente() {

    modalDocumentos.classList.remove(
        "show"
    );

    formDocumento.reset();

    limpiarMensajeDocumento();

}


// ============================================================
// MENÚ DE USUARIO - ADMINISTRADOR / GERENTE
// ============================================================

if (usuarioMenu) {

    usuarioMenu.addEventListener("click", function (event) {

        event.stopPropagation();

        usuarioMenu.classList.toggle("activo");

    });

}


if (usuarioDropdown) {

    usuarioDropdown.addEventListener("click", function (event) {

        event.stopPropagation();

    });

}


// CERRAR EL MENÚ AL HACER CLIC FUERA

document.addEventListener("click", function () {

    if (usuarioMenu) {
        usuarioMenu.classList.remove("activo");
    }

});


// ============================================================
// CERRAR SESIÓN
// ============================================================

if (logoutButtonPerfil) {

    logoutButtonPerfil.addEventListener("click", async function () {

        try {

            await fetch(
                "../../backend/routes/logout.php",
                {
                    method: "POST",
                    credentials: "include"
                }
            );

        } catch (error) {

            console.error("Error cerrando sesión:", error);

        }

        window.location.href = "../index.html";

    });

}


// ============================================================
// UTILIDADES
// ============================================================

function obtenerClaseEstatus(estatus) {

    if (estatus === "activo") {
        return "estado-activo";
    }


    if (estatus === "bloqueado") {
        return "estado-bloqueado";
    }


    return "estado-inactivo";
}


function obtenerClaseDocumento(estatus) {

    if (estatus === "aprobado") {
        return "documento-aprobado";
    }


    if (estatus === "rechazado") {
        return "documento-rechazado";
    }


    if (estatus === "eliminado") {
        return "documento-eliminado";
    }


    return "documento-pendiente";
}


function mostrarTipoDocumento(tipo) {

    const nombres = {

        INE:
            "INE",

        CURP:
            "CURP",

        RFC:
            "RFC",

        comprobante_domicilio:
            "Comprobante de domicilio",

        comprobante_ingresos:
            "Comprobante de ingresos",

        otro:
            "Otro documento"

    };


    return nombres[tipo] || tipo;
}


function formatearFecha(fecha) {

    if (!fecha) {
        return "Sin registrar";
    }


    const partes =
        String(fecha)
            .substring(0, 10)
            .split("-");


    if (partes.length !== 3) {
        return fecha;
    }


    return (
        partes[2] +
        "/" +
        partes[1] +
        "/" +
        partes[0]
    );
}


function formatearFechaHora(fecha) {

    if (!fecha) {
        return "Sin registrar";
    }


    const valor =
        fecha.includes(" ")
            ? fecha.replace(" ", "T")
            : fecha;


    const objetoFecha =
        new Date(valor);


    if (
        Number.isNaN(
            objetoFecha.getTime()
        )
    ) {

        return fecha;
    }


    return objetoFecha.toLocaleString(
        "es-MX",
        {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit"
        }
    );
}


function capitalizar(texto) {

    if (!texto) {
        return "";
    }


    return (
        texto.charAt(0).toUpperCase() +
        texto.slice(1)
    );
}


function escaparHTML(valor) {

    return String(valor ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}


// ============================================================
// MENSAJES
// ============================================================

function mostrarMensajeClientes(
    texto,
    tipo
) {

    mensajeClientes.textContent =
        texto;

    mensajeClientes.className =
        "mensaje-clientes " + tipo;
}


function mostrarMensajeFormulario(
    texto,
    tipo
) {

    mensajeFormulario.textContent =
        texto;

    mensajeFormulario.className =
        "mensaje-formulario " + tipo;
}


function limpiarMensajeFormulario() {

    mensajeFormulario.textContent = "";

    mensajeFormulario.className =
        "mensaje-formulario";
}


function mostrarMensajeEditar(
    texto,
    tipo
) {

    mensajeEditar.textContent =
        texto;

    mensajeEditar.className =
        "mensaje-formulario " + tipo;
}


function limpiarMensajeEditar() {

    mensajeEditar.textContent = "";

    mensajeEditar.className =
        "mensaje-formulario";
}


function mostrarMensajeDocumento(
    texto,
    tipo
) {

    mensajeDocumento.textContent =
        texto;

    mensajeDocumento.className =
        "mensaje-formulario " + tipo;
}


function limpiarMensajeDocumento() {

    mensajeDocumento.textContent = "";

    mensajeDocumento.className =
        "mensaje-formulario";
}


// ============================================================
// INICIAR
// ============================================================

async function iniciar() {

    const sesionValida =
        await comprobarSesion();


    if (!sesionValida) {
        return;
    }


    await cargarClientes();

}


iniciar();