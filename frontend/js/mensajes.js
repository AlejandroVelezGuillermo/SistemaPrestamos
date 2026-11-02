// ============================================================
// ELEMENTOS DEL DOM
// ============================================================

const nombreUsuario = document.getElementById("nombreUsuario");

const rolUsuario = document.getElementById("rolUsuario");

const avatarUsuario = document.getElementById("avatarUsuario");

const usuarioMenu = document.getElementById("usuarioMenuMensajes");

const usuarioDropdown = document.getElementById("usuarioDropdown");

const logoutButtonPerfil = document.getElementById("logoutButtonPerfil");

if (usuarioDropdown) {
    usuarioDropdown.style.display = "none";
}

const menuConfiguracion = document.getElementById("menuConfiguracion");

const totalClientes = document.getElementById("totalClientes");

const totalMensajes = document.getElementById("totalMensajes");

const totalLeidos = document.getElementById("totalLeidos");

const totalNoLeidos = document.getElementById("totalNoLeidos");

const tablaClientes = document.getElementById("tablaClientes");

const tablaMensajes = document.getElementById("tablaMensajes");

const buscarCliente = document.getElementById("buscarCliente");

// ============================================================
// MODAL ENVIAR
// ============================================================

const modalMensaje = document.getElementById("modalMensaje");

const cerrarModal = document.getElementById("cerrarModal");

const cancelarMensaje = document.getElementById("cancelarMensaje");

const formMensaje = document.getElementById("formMensaje");

const idCliente = document.getElementById("idCliente");

const modalNombreCliente = document.getElementById("modalNombreCliente");

const modalCorreoCliente = document.getElementById("modalCorreoCliente");

const modalAvatar = document.getElementById("modalAvatar");

const asunto = document.getElementById("asunto");

const mensaje = document.getElementById("mensaje");

const contadorAsunto = document.getElementById("contadorAsunto");

const mensajeFormulario = document.getElementById("mensajeFormulario");

const btnEnviarMensaje = document.getElementById("btnEnviarMensaje");

// ============================================================
// MODAL DETALLE
// ============================================================

const modalDetalle = document.getElementById("modalDetalle");

const cerrarDetalle = document.getElementById("cerrarDetalle");

const detalleAsunto = document.getElementById("detalleAsunto");

const detalleCliente = document.getElementById("detalleCliente");

const detalleEmisor = document.getElementById("detalleEmisor");

const detalleFecha = document.getElementById("detalleFecha");

const detalleEstado = document.getElementById("detalleEstado");

const detalleMensaje = document.getElementById("detalleMensaje");

const detalleConversacion =
    document.getElementById("detalleConversacion");

const formResponderMensaje =
    document.getElementById("formResponderMensaje");

const idMensajeRespuesta =
    document.getElementById("idMensajeRespuesta");

const textoRespuestaMensaje =
    document.getElementById("textoRespuestaMensaje");

const estadoRespuestaMensaje =
    document.getElementById("estadoRespuestaMensaje");

const btnEnviarRespuesta =
    document.getElementById("btnEnviarRespuesta");

// ============================================================
// VARIABLES
// ============================================================

let clientes = [];
let mensajesEnviados = [];

// ============================================================
// INICIAR
// ============================================================

async function iniciarMensajes() {
  const usuario = await comprobarSesion();

  if (!usuario) {
    return;
  }

  await Promise.all([cargarClientes(), cargarMensajesEnviados()]);
}

// ============================================================
// COMPROBAR SESIÓN
// ============================================================

async function comprobarSesion() {
  try {
    const respuesta = await fetch("../../backend/routes/sesion.php", {
      method: "GET",
      credentials: "include",
    });

    const datos = await respuesta.json();

    if (!datos.sesion || !datos.usuario) {
      window.location.href = "../index.html";

      return null;
    }

    const usuario = datos.usuario;

    const rol = String(usuario.rol || "")
      .trim()
      .toLowerCase();

    // CLIENTE NO PUEDE ENTRAR AQUÍ

    if (rol === "cliente") {
      window.location.href = "portal_cliente.html";

      return null;
    }

    // SOLO ADMIN / GERENTE

    if (rol !== "administrador" && rol !== "gerente") {
      window.location.href = "../index.html";

      return null;
    }

    // DATOS DE USUARIO

    const nombreCompleto = [usuario.nombre, usuario.apellidos]
      .filter(Boolean)
      .join(" ");

    nombreUsuario.textContent = nombreCompleto;

    rolUsuario.textContent = capitalizar(rol);

    avatarUsuario.textContent = String(usuario.nombre || "U")
      .charAt(0)
      .toUpperCase();

    // GERENTE NO VE CONFIGURACIÓN

    if (rol === "gerente" && menuConfiguracion) {
      menuConfiguracion.style.display = "none";
    }

    return usuario;
  } catch (error) {
    console.error("Error comprobando sesión:", error);

    window.location.href = "../index.html";

    return null;
  }
}

// ============================================================
// CARGAR CLIENTES
// ============================================================

async function cargarClientes() {
  tablaClientes.innerHTML = `
        <tr>
            <td colspan="5" class="table-message">
                Cargando clientes...
            </td>
        </tr>
    `;

  try {
    const respuesta = await fetch(
      "../../backend/routes/mensajes.php?clientes=1",
      {
        method: "GET",
        credentials: "include",
      },
    );

    const datos = await respuesta.json();

    if (!datos.success) {
      throw new Error(datos.message || "No se pudieron cargar los clientes.");
    }

    clientes = Array.isArray(datos.data) ? datos.data : [];

    totalClientes.textContent = clientes.length;

    mostrarClientes(clientes);
  } catch (error) {
    console.error("Error cargando clientes:", error);

    tablaClientes.innerHTML = `
            <tr>
                <td colspan="5" class="table-message">
                    No se pudieron cargar los clientes.
                </td>
            </tr>
        `;
  }
}

// ============================================================
// MOSTRAR CLIENTES
// ============================================================

function mostrarClientes(lista) {
  if (!lista.length) {
    tablaClientes.innerHTML = `
            <tr>
                <td colspan="5" class="table-message">
                    No hay clientes disponibles.
                </td>
            </tr>
        `;

    return;
  }

  tablaClientes.innerHTML = lista
    .map((cliente) => {
      const nombreCompleto = [
        cliente.nombre,
        cliente.apellido_paterno,
        cliente.apellido_materno,
      ]
        .filter(Boolean)
        .join(" ");

      const inicial = String(cliente.nombre || "C")
        .charAt(0)
        .toUpperCase();

      return `
                <tr>

                    <td>
                        <div class="cliente-info">

                            <div class="cliente-avatar-small">
                                ${escaparHTML(inicial)}
                            </div>

                            <div>

                                <strong>
                                    ${escaparHTML(nombreCompleto)}
                                </strong>

                                <small>
                                    ID #${cliente.id_usuario}
                                </small>

                            </div>

                        </div>
                    </td>


                    <td>
                        ${escaparHTML(cliente.correo || "-")}
                    </td>


                    <td>
                        ${escaparHTML(cliente.telefono || "-")}
                    </td>


                    <td>
                        <span class="badge badge-activo">
                            Activo
                        </span>
                    </td>


                    <td>

                        <button
                            type="button"
                            class="btn-table btn-enviar"
                            onclick="abrirModalMensaje(${cliente.id_usuario})"
                        >
                            ✉️ Enviar mensaje
                        </button>

                    </td>

                </tr>
            `;
    })
    .join("");
}

// ============================================================
// BUSCAR CLIENTE
// ============================================================

buscarCliente.addEventListener("input", function () {
  const texto = this.value.trim().toLowerCase();

  if (texto === "") {
    mostrarClientes(clientes);
    return;
  }

  const filtrados = clientes.filter((cliente) => {
    const nombreCompleto = [
      cliente.nombre,
      cliente.apellido_paterno,
      cliente.apellido_materno,
    ]
      .filter(Boolean)
      .join(" ")
      .toLowerCase();

    const correo = String(cliente.correo || "").toLowerCase();

    const telefono = String(cliente.telefono || "").toLowerCase();

    return (
      nombreCompleto.includes(texto) ||
      correo.includes(texto) ||
      telefono.includes(texto)
    );
  });

  mostrarClientes(filtrados);
});

// ============================================================
// ABRIR MODAL DE ENVÍO
// ============================================================

function abrirModalMensaje(id) {
  const cliente = clientes.find(
    (item) => Number(item.id_usuario) === Number(id),
  );

  if (!cliente) {
    return;
  }

  const nombreCompleto = [
    cliente.nombre,
    cliente.apellido_paterno,
    cliente.apellido_materno,
  ]
    .filter(Boolean)
    .join(" ");

  idCliente.value = cliente.id_usuario;

  modalNombreCliente.textContent = nombreCompleto;

  modalCorreoCliente.textContent = cliente.correo || "-";

  modalAvatar.textContent = String(cliente.nombre || "C")
    .charAt(0)
    .toUpperCase();

  asunto.value = "";
  mensaje.value = "";

  contadorAsunto.textContent = "0 / 150";

  limpiarAlerta();

  modalMensaje.classList.add("show");

  setTimeout(() => asunto.focus(), 100);
}

// ============================================================
// CERRAR MODAL ENVÍO
// ============================================================

function cerrarModalMensaje() {
  modalMensaje.classList.remove("show");

  formMensaje.reset();

  idCliente.value = "";

  contadorAsunto.textContent = "0 / 150";

  limpiarAlerta();
}

cerrarModal.addEventListener("click", cerrarModalMensaje);

cancelarMensaje.addEventListener("click", cerrarModalMensaje);

// ============================================================
// CONTADOR ASUNTO
// ============================================================

asunto.addEventListener("input", function () {
  contadorAsunto.textContent = `${this.value.length} / 150`;
});

// ============================================================
// ENVIAR MENSAJE
// ============================================================

formMensaje.addEventListener("submit", async function (event) {
  event.preventDefault();

  const cliente = Number(idCliente.value);

  const asuntoTexto = asunto.value.trim();

  const mensajeTexto = mensaje.value.trim();

  if (!cliente) {
    mostrarAlerta("Selecciona un cliente válido.", "error");

    return;
  }

  if (asuntoTexto === "") {
    mostrarAlerta("Escribe el asunto del mensaje.", "error");

    asunto.focus();

    return;
  }

  if (mensajeTexto === "") {
    mostrarAlerta("Escribe el contenido del mensaje.", "error");

    mensaje.focus();

    return;
  }

  btnEnviarMensaje.disabled = true;

  btnEnviarMensaje.innerHTML = "Enviando...";

  try {
    const respuesta = await fetch("../../backend/routes/mensajes.php", {
      method: "POST",

      credentials: "include",

      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify({
        accion: "enviar",
        id_cliente: cliente,
        asunto: asuntoTexto,
        mensaje: mensajeTexto,
      }),
    });

    const datos = await respuesta.json();

    if (!datos.success) {
      throw new Error(datos.message || "No se pudo enviar el mensaje.");
    }

    mostrarAlerta("Mensaje enviado correctamente.", "success");

    await cargarMensajesEnviados();

    setTimeout(cerrarModalMensaje, 900);
  } catch (error) {
    console.error("Error enviando mensaje:", error);

    mostrarAlerta(
      error.message || "Ocurrió un error al enviar el mensaje.",
      "error",
    );
  } finally {
    btnEnviarMensaje.disabled = false;

    btnEnviarMensaje.innerHTML = "<span>✉️</span> Enviar mensaje";
  }
});

// ============================================================
// CARGAR MENSAJES ENVIADOS
// ============================================================

async function cargarMensajesEnviados() {
  tablaMensajes.innerHTML = `
        <tr>
            <td colspan="6" class="table-message">
                Cargando mensajes...
            </td>
        </tr>
    `;

  try {
    const respuesta = await fetch(
      "../../backend/routes/mensajes.php?enviados=1",
      {
        method: "GET",
        credentials: "include",
      },
    );

    const datos = await respuesta.json();

    if (!datos.success) {
      throw new Error(datos.message || "No se pudieron cargar los mensajes.");
    }

    mensajesEnviados = Array.isArray(datos.data) ? datos.data : [];

    actualizarEstadisticas();

    mostrarMensajes(mensajesEnviados);
  } catch (error) {
    console.error("Error cargando mensajes:", error);

    tablaMensajes.innerHTML = `
            <tr>
                <td colspan="6" class="table-message">
                    No se pudieron cargar los mensajes.
                </td>
            </tr>
        `;
  }
}

// ============================================================
// ESTADÍSTICAS
// ============================================================

function actualizarEstadisticas() {
  const enviados = mensajesEnviados.length;

  const leidos = mensajesEnviados.filter(
    (item) => Number(item.leido) === 1,
  ).length;

  const noLeidos = enviados - leidos;

  totalMensajes.textContent = enviados;

  totalLeidos.textContent = leidos;

  totalNoLeidos.textContent = noLeidos;
}

// ============================================================
// MOSTRAR MENSAJES
// ============================================================

function mostrarMensajes(lista) {
  if (!lista.length) {
    tablaMensajes.innerHTML = `
            <tr>
                <td colspan="6" class="table-message">
                    Todavía no se han enviado mensajes.
                </td>
            </tr>
        `;

    return;
  }

  tablaMensajes.innerHTML = lista
    .map((item) => {
      const fueLeido = Number(item.leido) === 1;

      const respuestasNuevas =
        Number(item.respuestas_no_leidas || 0);

      const badge = fueLeido
        ? `
            <span class="badge badge-leido">
                ✓ Leído
            </span>
          `
        : `
            <span class="badge badge-no-leido">
                ● Sin leer
            </span>
          `;

      const avisoRespuesta =
        respuestasNuevas > 0
          ? `
              <span class="badge-respuesta-nueva">
                  🔴 ${respuestasNuevas}
                  ${
                    respuestasNuevas === 1
                      ? "nueva"
                      : "nuevas"
                  }
              </span>
            `
          : "";

      return `
            <tr>

                <td>
                    <div class="cliente-info">

                        <div class="cliente-avatar-small">
                            ${escaparHTML(
                              String(item.nombre_cliente || "C")
                                .charAt(0)
                                .toUpperCase(),
                            )}
                        </div>

                        <div>

                            <strong>
                                ${escaparHTML(item.nombre_cliente || "-")}
                            </strong>

                            <small>
                                ${escaparHTML(item.correo_cliente || "-")}
                            </small>

                        </div>

                    </div>
                </td>


                <td>
                    ${escaparHTML(item.asunto || "-")}
                </td>


                <td>
                    ${escaparHTML(item.nombre_emisor || "-")}
                </td>


                <td>
                    ${formatearFecha(item.fecha_envio)}
                </td>


                <td>
                    ${badge}
                </td>


                <td>

                    <div class="acciones-mensaje">

                        ${avisoRespuesta}

                        <button
                            type="button"
                            class="btn-table btn-ver"
                            onclick="verDetalleMensaje(${item.id_mensaje})"
                        >
                            👁️ Ver
                        </button>

                    </div>

                </td>

            </tr>
        `;
    })
    .join("");
}
// ============================================================
// VER DETALLE
// ============================================================

async function verDetalleMensaje(id) {

    const item =
        mensajesEnviados.find(
            mensajeItem =>
                Number(mensajeItem.id_mensaje) ===
                Number(id)
        );


    if (!item) {
        return;
    }


    detalleAsunto.textContent =
        item.asunto || "Sin asunto";

    detalleCliente.textContent =
        item.nombre_cliente || "-";

    detalleEmisor.textContent =
        item.nombre_emisor || "-";

    detalleFecha.textContent =
        formatearFecha(
            item.fecha_envio
        );

    detalleEstado.textContent =
        Number(item.leido) === 1
            ? "Leído"
            : "Sin leer";

    detalleMensaje.textContent =
        item.mensaje || "-";


    // ID DEL MENSAJE QUE SE RESPONDERÁ

    idMensajeRespuesta.value =
        item.id_mensaje;


    // LIMPIAR FORMULARIO

    textoRespuestaMensaje.value = "";

    estadoRespuestaMensaje.textContent = "";


    // ABRIR MODAL

    modalDetalle.classList.add(
        "show"
    );


    // CARGAR CONVERSACIÓN

    await cargarConversacionAdministrador(
        item.id_mensaje
    );


    // MARCAR RESPUESTAS NUEVAS DEL CLIENTE COMO LEÍDAS

    if (
        Number(item.respuestas_no_leidas || 0) > 0
    ) {

        await marcarRespuestasLeidasAdministrador(
            item.id_mensaje
        );
    }
}

// ============================================================
// CERRAR DETALLE
// ============================================================

function cerrarModalDetalle() {

    modalDetalle.classList.remove("show");

    idMensajeRespuesta.value = "";

    textoRespuestaMensaje.value = "";

    estadoRespuestaMensaje.textContent = "";

    detalleConversacion.innerHTML = "";
}


// ============================================================
// CERRAR DETALLE CON LA X
// ============================================================

if (cerrarDetalle) {

    cerrarDetalle.addEventListener(
        "click",
        cerrarModalDetalle
    );
}


// ============================================================
// CERRAR DETALLE HACIENDO CLIC FUERA
// ============================================================

if (modalDetalle) {

    modalDetalle.addEventListener(
        "click",
        function (event) {

            if (event.target === modalDetalle) {

                cerrarModalDetalle();
            }
        }
    );
}


// ============================================================
// CERRAR DETALLE CON ESC
// ============================================================

document.addEventListener(
    "keydown",
    function (event) {

        if (
            event.key === "Escape" &&
            modalDetalle.classList.contains("show")
        ) {

            cerrarModalDetalle();
        }
    }
);


// ============================================================
// MARCAR RESPUESTAS COMO LEÍDAS
// ADMINISTRADOR / GERENTE
// ============================================================

async function marcarRespuestasLeidasAdministrador(
    idMensaje
) {

    try {

        const respuesta =
            await fetch(
                "../../backend/routes/mensajes.php",
                {
                    method: "POST",

                    credentials: "include",

                    headers: {
                        "Content-Type":
                            "application/json"
                    },

                    body: JSON.stringify({
                        accion:
                            "marcar_respuestas_leidas",

                        id_mensaje:
                            idMensaje
                    })
                }
            );


        const datos =
            await respuesta.json();


        if (!datos.success) {

            throw new Error(
                datos.message ||
                "No se pudieron marcar las respuestas como leídas."
            );
        }


        const item =
            mensajesEnviados.find(
                mensajeItem =>
                    Number(mensajeItem.id_mensaje) ===
                    Number(idMensaje)
            );


        if (item) {

            item.respuestas_no_leidas = 0;
        }


        mostrarMensajes(
            mensajesEnviados
        );


    } catch (error) {

        console.error(
            "Error marcando respuestas como leídas:",
            error
        );
    }
}



// ============================================================
// CARGAR CONVERSACIÓN
// ADMINISTRADOR / GERENTE
// ============================================================

async function cargarConversacionAdministrador(
    idMensaje
) {

    detalleConversacion.innerHTML = `
        <div class="conversacion-vacia">
            Cargando conversación...
        </div>
    `;


    try {

        const respuesta =
            await fetch(
                "../../backend/routes/mensajes.php?conversacion=1&id_mensaje=" +
                encodeURIComponent(idMensaje),
                {
                    method: "GET",
                    credentials: "include"
                }
            );


        const datos =
            await respuesta.json();


        if (!datos.success) {

            throw new Error(
                datos.message ||
                "No se pudo cargar la conversación."
            );
        }


        const respuestas =
            Array.isArray(
                datos.data?.respuestas
            )
                ? datos.data.respuestas
                : [];


        if (!respuestas.length) {

            detalleConversacion.innerHTML = `
                <div class="conversacion-vacia">
                    Todavía no hay respuestas.
                </div>
            `;

            return;
        }


        detalleConversacion.innerHTML =
            respuestas
                .map(
                    respuestaItem => {

                        const rol =
                            String(
                                respuestaItem.rol || ""
                            )
                            .trim()
                            .toLowerCase();


                        const esCliente =
                            rol === "cliente";


                        const nombre =
                            respuestaItem.nombre_usuario ||
                            (
                                esCliente
                                    ? "Cliente"
                                    : "Administración"
                            );


                        return `
                            <div
                                class="respuesta-admin ${
                                    esCliente
                                        ? "respuesta-admin-cliente"
                                        : "respuesta-admin-personal"
                                }"
                            >

                                <div class="respuesta-admin-header">

                                    <strong>
                                        ${escaparHTML(nombre)}
                                    </strong>

                                    <span>
                                        ${escaparHTML(
                                            capitalizar(rol)
                                        )}
                                    </span>

                                </div>


                                <div class="respuesta-admin-texto">
                                    ${escaparHTML(
                                        respuestaItem.mensaje || ""
                                    )}
                                </div>


                                <small>
                                    ${formatearFecha(
                                        respuestaItem.fecha_envio
                                    )}
                                </small>

                            </div>
                        `;
                    }
                )
                .join("");


        detalleConversacion.scrollTop =
            detalleConversacion.scrollHeight;


    } catch (error) {

        console.error(
            "Error cargando conversación:",
            error
        );


        detalleConversacion.innerHTML = `
            <div class="conversacion-error">
                ${escaparHTML(
                    error.message ||
                    "No se pudo cargar la conversación."
                )}
            </div>
        `;
    }
}


// ============================================================
// ENVIAR RESPUESTA
// ADMINISTRADOR / GERENTE
// ============================================================

formResponderMensaje.addEventListener(
    "submit",
    async function (event) {

        event.preventDefault();


        const idMensaje =
            Number(
                idMensajeRespuesta.value
            );


        const texto =
            textoRespuestaMensaje
                .value
                .trim();


        if (!idMensaje) {

            estadoRespuestaMensaje.textContent =
                "No se pudo identificar la conversación.";

            return;
        }


        if (texto === "") {

            estadoRespuestaMensaje.textContent =
                "Escribe una respuesta.";

            textoRespuestaMensaje.focus();

            return;
        }


        btnEnviarRespuesta.disabled = true;

        btnEnviarRespuesta.innerHTML =
            "Enviando...";

        estadoRespuestaMensaje.textContent = "";


        try {

            const respuesta =
                await fetch(
                    "../../backend/routes/mensajes.php",
                    {
                        method: "POST",

                        credentials: "include",

                        headers: {
                            "Content-Type":
                                "application/json"
                        },

                        body: JSON.stringify({
                            accion:
                                "responder",

                            id_mensaje:
                                idMensaje,

                            mensaje:
                                texto
                        })
                    }
                );


            const datos =
                await respuesta.json();


            if (!datos.success) {

                throw new Error(
                    datos.message ||
                    "No se pudo enviar la respuesta."
                );
            }


            textoRespuestaMensaje.value = "";


            estadoRespuestaMensaje.textContent =
                "Respuesta enviada correctamente.";


            await cargarConversacionAdministrador(
                idMensaje
            );


        } catch (error) {

            console.error(
                "Error enviando respuesta:",
                error
            );


            estadoRespuestaMensaje.textContent =
                error.message ||
                "No se pudo enviar la respuesta.";

        } finally {

            btnEnviarRespuesta.disabled = false;

            btnEnviarRespuesta.innerHTML = `
                <span>↩️</span>
                Enviar respuesta
            `;
        }
    }
);

// ============================================================
// CERRAR AL DAR CLICK FUERA
// ============================================================

modalMensaje.addEventListener("click", function (event) {
  if (event.target === modalMensaje) {
    cerrarModalMensaje();
  }
});

modalDetalle.addEventListener("click", function (event) {
  if (event.target === modalDetalle) {
    cerrarModalDetalle();
  }
});

// ============================================================
// TECLA ESC
// ============================================================

document.addEventListener("keydown", function (event) {
  if (event.key === "Escape") {
    if (modalMensaje.classList.contains("show")) {
      cerrarModalMensaje();
    }

    if (modalDetalle.classList.contains("show")) {
      cerrarModalDetalle();
    }
  }
});

// ============================================================
// ALERTAS
// ============================================================

function mostrarAlerta(texto, tipo) {
  mensajeFormulario.textContent = texto;

  mensajeFormulario.className = `form-alert ${tipo}`;
}

function limpiarAlerta() {
  mensajeFormulario.textContent = "";

  mensajeFormulario.className = "form-alert";
}

// ============================================================
// FORMATEAR FECHA
// ============================================================

function formatearFecha(fecha) {
  if (!fecha) {
    return "-";
  }

  const fechaNormalizada = String(fecha).replace(" ", "T");

  const objetoFecha = new Date(fechaNormalizada);

  if (Number.isNaN(objetoFecha.getTime())) {
    return fecha;
  }

  return new Intl.DateTimeFormat("es-MX", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  }).format(objetoFecha);
}

// ============================================================
// CAPITALIZAR
// ============================================================

function capitalizar(texto) {
  if (!texto) {
    return "";
  }

  return texto.charAt(0).toUpperCase() + texto.slice(1);
}

// ============================================================
// ESCAPAR HTML
// ============================================================

function escaparHTML(valor) {
  return String(valor ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

// ============================================================
// MENÚ DE USUARIO - ADMINISTRADOR / GERENTE
// ============================================================

if (usuarioMenu) {
  usuarioMenu.addEventListener("click", function (event) {
    event.stopPropagation();

    const abierto = usuarioDropdown.style.display === "block";

    usuarioDropdown.style.display = abierto ? "none" : "block";

    usuarioMenu.classList.toggle("activo", !abierto);
  });
}

if (usuarioDropdown) {
  usuarioDropdown.addEventListener("click", function (event) {
    event.stopPropagation();
  });
}

// CERRAR EL MENÚ AL HACER CLIC FUERA

document.addEventListener("click", function () {

    if (usuarioMenu && usuarioDropdown) {

        usuarioDropdown.style.display = "none";

        usuarioMenu.classList.remove("activo");

    }

});

// ============================================================
// CERRAR SESIÓN
// ============================================================

if (logoutButtonPerfil) {
  logoutButtonPerfil.addEventListener("click", async function () {
    try {
      await fetch("../../backend/routes/logout.php", {
        method: "POST",
        credentials: "include",
      });
    } catch (error) {
      console.error("Error cerrando sesión:", error);
    }

    window.location.href = "../index.html";
  });
}

// ============================================================
// INICIAR
// ============================================================

iniciarMensajes();
