// ============================================================
// ELEMENTOS GENERALES
// ============================================================

const nombreUsuario = document.getElementById("nombreUsuario");

const rolUsuario = document.getElementById("rolUsuario");

const avatarUsuario = document.getElementById("avatarUsuario");

const usuarioMenu = document.getElementById("usuarioMenu");

const usuarioDropdown = document.getElementById("usuarioDropdown");

const logoutButtonPerfil = document.getElementById("logoutButtonPerfil");

const mensajeConfiguracion = document.getElementById("mensajeConfiguracion");

// ============================================================
// TIPOS DE PRÉSTAMO
// ============================================================

const btnNuevoTipoPrestamo = document.getElementById("btnNuevoTipoPrestamo");

const tablaTiposPrestamoBody = document.getElementById(
  "tablaTiposPrestamoBody",
);

const modalTipoPrestamo = document.getElementById("modalTipoPrestamo");

const tituloModalTipoPrestamo = document.getElementById(
  "tituloModalTipoPrestamo",
);

const subtituloModalTipoPrestamo = document.getElementById(
  "subtituloModalTipoPrestamo",
);

const cerrarModalTipoPrestamo = document.getElementById(
  "cerrarModalTipoPrestamo",
);

const cancelarTipoPrestamo = document.getElementById("cancelarTipoPrestamo");

const guardarTipoPrestamo = document.getElementById("guardarTipoPrestamo");

const tipoPrestamoNombre = document.getElementById("tipoPrestamoNombre");

const tipoPrestamoDescripcion = document.getElementById(
  "tipoPrestamoDescripcion",
);

const tipoPrestamoMontoMinimo = document.getElementById(
  "tipoPrestamoMontoMinimo",
);

const tipoPrestamoMontoMaximo = document.getElementById(
  "tipoPrestamoMontoMaximo",
);

const tipoPrestamoPlazoMinimo = document.getElementById(
  "tipoPrestamoPlazoMinimo",
);

const tipoPrestamoPlazoMaximo = document.getElementById(
  "tipoPrestamoPlazoMaximo",
);

const tipoPrestamoEstatus = document.getElementById("tipoPrestamoEstatus");

const mensajeTipoPrestamo = document.getElementById("mensajeTipoPrestamo");

// ============================================================
// MÉTODOS DE PAGO
// ============================================================

const btnNuevoMetodoPago = document.getElementById("btnNuevoMetodoPago");

const tablaMetodosPagoBody = document.getElementById("tablaMetodosPagoBody");

const modalMetodoPago = document.getElementById("modalMetodoPago");

const tituloModalMetodoPago = document.getElementById("tituloModalMetodoPago");

const subtituloModalMetodoPago = document.getElementById(
  "subtituloModalMetodoPago",
);

const cerrarModalMetodoPago = document.getElementById("cerrarModalMetodoPago");

const cancelarMetodoPago = document.getElementById("cancelarMetodoPago");

const guardarMetodoPago = document.getElementById("guardarMetodoPago");

const metodoPagoNombre = document.getElementById("metodoPagoNombre");

const metodoPagoDescripcion = document.getElementById("metodoPagoDescripcion");

const metodoPagoEstatus = document.getElementById("metodoPagoEstatus");

const mensajeMetodoPago = document.getElementById("mensajeMetodoPago");

// ============================================================
// ESTADOS
// ============================================================

const listaEstadosPrestamo = document.getElementById("listaEstadosPrestamo");

const listaEstadosSolicitud = document.getElementById("listaEstadosSolicitud");

// ============================================================
// VARIABLES
// ============================================================

let tiposPrestamo = [];

let metodosPago = [];

let tipoPrestamoActual = null;

let metodoPagoActual = null;

let modoTipoPrestamo = "crear";

let modoMetodoPago = "crear";

let procesandoTipoPrestamo = false;

let procesandoMetodoPago = false;

// ============================================================
// SESIÓN
// ============================================================

async function comprobarSesion() {
  try {
    const respuesta = await fetch("../../backend/routes/sesion.php", {
      credentials: "include",
    });

    const datos = await respuesta.json();

    if (!datos.sesion) {
      window.location.href = "../index.html";

      return false;
    }

    const usuario = datos.usuario;

    if (usuario.rol !== "administrador") {
      window.location.href = "dashboard.html";

      return false;
    }

    nombreUsuario.textContent =
      usuario.nombre + " " + (usuario.apellidos || "");

    rolUsuario.textContent = capitalizar(usuario.rol);

    avatarUsuario.textContent = usuario.nombre
      ? usuario.nombre.charAt(0).toUpperCase()
      : "U";

    return true;
  } catch (error) {
    console.error(error);

    window.location.href = "../index.html";

    return false;
  }
}

// ============================================================
// CARGAR TIPOS DE PRÉSTAMO
// ============================================================

async function cargarTiposPrestamo() {
  tablaTiposPrestamoBody.innerHTML = `
        <tr>
            <td colspan="7" class="tabla-cargando">
                Cargando tipos de préstamo...
            </td>
        </tr>
    `;

  try {
    const respuesta = await fetch(
      "../../backend/routes/configuracion.php?tipos_prestamo=1",
      {
        credentials: "include",
      },
    );

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(
        resultado.message || "No fue posible cargar los tipos de préstamo.",
      );
    }

    tiposPrestamo = resultado.data || [];

    mostrarTiposPrestamo(tiposPrestamo);
  } catch (error) {
    console.error(error);

    tablaTiposPrestamoBody.innerHTML = `
            <tr>
                <td colspan="7" class="tabla-vacia">
                    No fue posible cargar los tipos de préstamo.
                </td>
            </tr>
        `;
  }
}

// ============================================================
// MOSTRAR TIPOS DE PRÉSTAMO
// ============================================================

function mostrarTiposPrestamo(lista) {
  if (lista.length === 0) {
    tablaTiposPrestamoBody.innerHTML = `
            <tr>
                <td colspan="7" class="tabla-vacia">
                    No hay tipos de préstamo registrados.
                </td>
            </tr>
        `;

    return;
  }

  tablaTiposPrestamoBody.innerHTML = lista
    .map((tipo) => {
      const estatus = normalizar(tipo.estatus);

      return `
                <tr>

                    <td>
                        #${tipo.id_tipo_prestamo}
                    </td>

                    <td>

                        <strong>
                            ${escaparHTML(tipo.nombre)}
                        </strong>

                        ${
                          tipo.descripcion
                            ? `
                                    <br>
                                    <small>
                                        ${escaparHTML(tipo.descripcion)}
                                    </small>
                                `
                            : ""
                        }

                    </td>

                    <td>
                        ${formatearDinero(tipo.monto_minimo)}
                    </td>

                    <td>
                        ${formatearDinero(tipo.monto_maximo)}
                    </td>

                    <td>
                        ${tipo.plazo_minimo}
                        -
                        ${tipo.plazo_maximo}
                        meses
                    </td>

                    <td>

                        <span
                            class="estatus-config estatus-${estatus}"
                        >
                            ${capitalizar(estatus)}
                        </span>

                    </td>

                    <td>

                        <button
                            type="button"
                            class="btn-accion btn-editar-tipo"
                            data-id="${tipo.id_tipo_prestamo}"
                        >
                            Editar
                        </button>


                        ${
                          estatus === "activo"
                            ? `
                                    <button
                                        type="button"
                                        class="btn-accion btn-desactivar btn-estatus-tipo"
                                        data-id="${tipo.id_tipo_prestamo}"
                                        data-estatus="inactivo"
                                    >
                                        Desactivar
                                    </button>
                                `
                            : `
                                    <button
                                        type="button"
                                        class="btn-accion btn-activar btn-estatus-tipo"
                                        data-id="${tipo.id_tipo_prestamo}"
                                        data-estatus="activo"
                                    >
                                        Activar
                                    </button>
                                `
                        }

                    </td>

                </tr>
            `;
    })
    .join("");
}

// ============================================================
// NUEVO TIPO DE PRÉSTAMO
// ============================================================

btnNuevoTipoPrestamo.addEventListener("click", function () {
  modoTipoPrestamo = "crear";

  tipoPrestamoActual = null;

  limpiarFormularioTipoPrestamo();

  tituloModalTipoPrestamo.textContent = "Nuevo tipo de préstamo";

  subtituloModalTipoPrestamo.textContent =
    "Configura sus límites y disponibilidad.";

  guardarTipoPrestamo.textContent = "Guardar";

  limpiarMensajeTipoPrestamo();

  modalTipoPrestamo.classList.add("show");
});

// ============================================================
// EVENTOS TABLA TIPOS
// ============================================================

tablaTiposPrestamoBody.addEventListener("click", async function (event) {
  const botonEditar = event.target.closest(".btn-editar-tipo");

  if (botonEditar) {
    await editarTipoPrestamo(botonEditar.dataset.id);

    return;
  }

  const botonEstatus = event.target.closest(".btn-estatus-tipo");

  if (botonEstatus) {
    await cambiarEstatusTipoPrestamo(
      botonEstatus.dataset.id,
      botonEstatus.dataset.estatus,
    );
  }
});

// ============================================================
// EDITAR TIPO
// ============================================================

async function editarTipoPrestamo(idTipoPrestamo) {
  try {
    const respuesta = await fetch(
      "../../backend/routes/configuracion.php?tipos_prestamo=1&id=" +
        encodeURIComponent(idTipoPrestamo),
      {
        credentials: "include",
      },
    );

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(
        resultado.message || "No fue posible consultar el tipo de préstamo.",
      );
    }

    tipoPrestamoActual = resultado.data;

    modoTipoPrestamo = "editar";

    tituloModalTipoPrestamo.textContent = "Editar tipo de préstamo";

    subtituloModalTipoPrestamo.textContent =
      "Modifica la configuración del tipo #" +
      tipoPrestamoActual.id_tipo_prestamo +
      ".";

    tipoPrestamoNombre.value = tipoPrestamoActual.nombre || "";

    tipoPrestamoDescripcion.value = tipoPrestamoActual.descripcion || "";

    tipoPrestamoMontoMinimo.value = tipoPrestamoActual.monto_minimo || "";

    tipoPrestamoMontoMaximo.value = tipoPrestamoActual.monto_maximo || "";

    tipoPrestamoPlazoMinimo.value = tipoPrestamoActual.plazo_minimo || "";

    tipoPrestamoPlazoMaximo.value = tipoPrestamoActual.plazo_maximo || "";

    tipoPrestamoEstatus.value = tipoPrestamoActual.estatus || "activo";

    guardarTipoPrestamo.textContent = "Guardar cambios";

    limpiarMensajeTipoPrestamo();

    modalTipoPrestamo.classList.add("show");
  } catch (error) {
    console.error(error);

    mostrarMensajeGeneral(error.message, "error");
  }
}

// ============================================================
// GUARDAR TIPO
// ============================================================

guardarTipoPrestamo.addEventListener("click", async function () {
  if (procesandoTipoPrestamo) {
    return;
  }

  const nombre = tipoPrestamoNombre.value.trim();

  const montoMinimo = Number(tipoPrestamoMontoMinimo.value);

  const montoMaximo = Number(tipoPrestamoMontoMaximo.value);

  const plazoMinimo = Number(tipoPrestamoPlazoMinimo.value);

  const plazoMaximo = Number(tipoPrestamoPlazoMaximo.value);

  if (!nombre) {
    mostrarMensajeTipoPrestamo(
      "Ingresa el nombre del tipo de préstamo.",
      "error",
    );

    return;
  }

  if (!Number.isFinite(montoMinimo) || montoMinimo < 0) {
    mostrarMensajeTipoPrestamo("Ingresa un monto mínimo válido.", "error");

    return;
  }

  if (!Number.isFinite(montoMaximo) || montoMaximo <= 0) {
    mostrarMensajeTipoPrestamo("Ingresa un monto máximo válido.", "error");

    return;
  }

  if (montoMaximo < montoMinimo) {
    mostrarMensajeTipoPrestamo(
      "El monto máximo no puede ser menor que el mínimo.",
      "error",
    );

    return;
  }

  if (!Number.isInteger(plazoMinimo) || plazoMinimo <= 0) {
    mostrarMensajeTipoPrestamo("Ingresa un plazo mínimo válido.", "error");

    return;
  }

  if (!Number.isInteger(plazoMaximo) || plazoMaximo <= 0) {
    mostrarMensajeTipoPrestamo("Ingresa un plazo máximo válido.", "error");

    return;
  }

  if (plazoMaximo < plazoMinimo) {
    mostrarMensajeTipoPrestamo(
      "El plazo máximo no puede ser menor que el mínimo.",
      "error",
    );

    return;
  }

  const datos = {
    accion:
      modoTipoPrestamo === "crear"
        ? "crear_tipo_prestamo"
        : "actualizar_tipo_prestamo",

    nombre: nombre,

    descripcion: tipoPrestamoDescripcion.value.trim(),

    monto_minimo: montoMinimo,

    monto_maximo: montoMaximo,

    plazo_minimo: plazoMinimo,

    plazo_maximo: plazoMaximo,

    estatus: tipoPrestamoEstatus.value,
  };

  if (modoTipoPrestamo === "editar") {
    datos.id_tipo_prestamo = tipoPrestamoActual.id_tipo_prestamo;
  }

  procesandoTipoPrestamo = true;

  bloquearTipoPrestamo(true);

  try {
    const respuesta = await fetch("../../backend/routes/configuracion.php", {
      method: "POST",

      credentials: "include",

      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify(datos),
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      mostrarMensajeTipoPrestamo(
        resultado.message || "No fue posible guardar el tipo de préstamo.",
        "error",
      );

      return;
    }

    mostrarMensajeTipoPrestamo(resultado.message, "exito");

    await cargarTiposPrestamo();

    setTimeout(cerrarTipoPrestamo, 700);
  } catch (error) {
    console.error(error);

    mostrarMensajeTipoPrestamo("No se pudo conectar con el servidor.", "error");
  } finally {
    procesandoTipoPrestamo = false;

    bloquearTipoPrestamo(false);
  }
});

// ============================================================
// CAMBIAR ESTATUS TIPO
// ============================================================

async function cambiarEstatusTipoPrestamo(idTipoPrestamo, estatus) {
  const tipo = tiposPrestamo.find(
    (item) => Number(item.id_tipo_prestamo) === Number(idTipoPrestamo),
  );

  if (!tipo) {
    return;
  }

  const confirmar = window.confirm(
    estatus === "activo"
      ? "¿Deseas activar este tipo de préstamo?"
      : "¿Deseas desactivar este tipo de préstamo?",
  );

  if (!confirmar) {
    return;
  }

  try {
    const respuesta = await fetch("../../backend/routes/configuracion.php", {
      method: "POST",

      credentials: "include",

      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify({
        accion: "cambiar_estatus_tipo_prestamo",

        id_tipo_prestamo: idTipoPrestamo,

        estatus: estatus,
      }),
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      mostrarMensajeGeneral(
        resultado.message || "No fue posible cambiar el estatus.",
        "error",
      );

      return;
    }

    mostrarMensajeGeneral(resultado.message, "exito");

    await cargarTiposPrestamo();
  } catch (error) {
    console.error(error);

    mostrarMensajeGeneral("No se pudo conectar con el servidor.", "error");
  }
}

// ============================================================
// CARGAR MÉTODOS DE PAGO
// ============================================================

async function cargarMetodosPago() {
  tablaMetodosPagoBody.innerHTML = `
        <tr>
            <td colspan="5" class="tabla-cargando">
                Cargando métodos de pago...
            </td>
        </tr>
    `;

  try {
    const respuesta = await fetch(
      "../../backend/routes/configuracion.php?metodos_pago=1",
      {
        credentials: "include",
      },
    );

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(
        resultado.message || "No fue posible cargar los métodos de pago.",
      );
    }

    metodosPago = resultado.data || [];

    mostrarMetodosPago(metodosPago);
  } catch (error) {
    console.error(error);

    tablaMetodosPagoBody.innerHTML = `
            <tr>
                <td colspan="5" class="tabla-vacia">
                    No fue posible cargar los métodos de pago.
                </td>
            </tr>
        `;
  }
}

// ============================================================
// MOSTRAR MÉTODOS
// ============================================================

function mostrarMetodosPago(lista) {
  if (lista.length === 0) {
    tablaMetodosPagoBody.innerHTML = `
            <tr>
                <td colspan="5" class="tabla-vacia">
                    No hay métodos de pago registrados.
                </td>
            </tr>
        `;

    return;
  }

  tablaMetodosPagoBody.innerHTML = lista
    .map((metodo) => {
      const estatus = normalizar(metodo.estatus);

      return `
                <tr>

                    <td>
                        #${metodo.id_metodo_pago}
                    </td>

                    <td>
                        <strong>
                            ${escaparHTML(metodo.nombre)}
                        </strong>
                    </td>

                    <td>
                        ${escaparHTML(metodo.descripcion || "Sin descripción")}
                    </td>

                    <td>

                        <span
                            class="estatus-config estatus-${estatus}"
                        >
                            ${capitalizar(estatus)}
                        </span>

                    </td>

                    <td>

                        <button
                            type="button"
                            class="btn-accion btn-editar-metodo"
                            data-id="${metodo.id_metodo_pago}"
                        >
                            Editar
                        </button>


                        ${
                          estatus === "activo"
                            ? `
                                    <button
                                        type="button"
                                        class="btn-accion btn-desactivar btn-estatus-metodo"
                                        data-id="${metodo.id_metodo_pago}"
                                        data-estatus="inactivo"
                                    >
                                        Desactivar
                                    </button>
                                `
                            : `
                                    <button
                                        type="button"
                                        class="btn-accion btn-activar btn-estatus-metodo"
                                        data-id="${metodo.id_metodo_pago}"
                                        data-estatus="activo"
                                    >
                                        Activar
                                    </button>
                                `
                        }

                    </td>

                </tr>
            `;
    })
    .join("");
}

// ============================================================
// NUEVO MÉTODO
// ============================================================

btnNuevoMetodoPago.addEventListener("click", function () {
  modoMetodoPago = "crear";

  metodoPagoActual = null;

  limpiarFormularioMetodoPago();

  tituloModalMetodoPago.textContent = "Nuevo método de pago";

  subtituloModalMetodoPago.textContent = "Configura una forma de pago.";

  guardarMetodoPago.textContent = "Guardar";

  limpiarMensajeMetodoPago();

  modalMetodoPago.classList.add("show");
});

// ============================================================
// EVENTOS TABLA MÉTODOS
// ============================================================

tablaMetodosPagoBody.addEventListener("click", async function (event) {
  const botonEditar = event.target.closest(".btn-editar-metodo");

  if (botonEditar) {
    await editarMetodoPago(botonEditar.dataset.id);

    return;
  }

  const botonEstatus = event.target.closest(".btn-estatus-metodo");

  if (botonEstatus) {
    await cambiarEstatusMetodoPago(
      botonEstatus.dataset.id,
      botonEstatus.dataset.estatus,
    );
  }
});

// ============================================================
// EDITAR MÉTODO
// ============================================================

async function editarMetodoPago(idMetodoPago) {
  try {
    const respuesta = await fetch(
      "../../backend/routes/configuracion.php?metodos_pago=1&id=" +
        encodeURIComponent(idMetodoPago),
      {
        credentials: "include",
      },
    );

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(
        resultado.message || "No fue posible consultar el método de pago.",
      );
    }

    metodoPagoActual = resultado.data;

    modoMetodoPago = "editar";

    tituloModalMetodoPago.textContent = "Editar método de pago";

    subtituloModalMetodoPago.textContent =
      "Modifica el método #" + metodoPagoActual.id_metodo_pago + ".";

    metodoPagoNombre.value = metodoPagoActual.nombre || "";

    metodoPagoDescripcion.value = metodoPagoActual.descripcion || "";

    metodoPagoEstatus.value = metodoPagoActual.estatus || "activo";

    guardarMetodoPago.textContent = "Guardar cambios";

    limpiarMensajeMetodoPago();

    modalMetodoPago.classList.add("show");
  } catch (error) {
    console.error(error);

    mostrarMensajeGeneral(error.message, "error");
  }
}

// ============================================================
// GUARDAR MÉTODO
// ============================================================

guardarMetodoPago.addEventListener("click", async function () {
  if (procesandoMetodoPago) {
    return;
  }

  const nombre = metodoPagoNombre.value.trim();

  if (!nombre) {
    mostrarMensajeMetodoPago("Ingresa el nombre del método de pago.", "error");

    return;
  }

  const datos = {
    accion:
      modoMetodoPago === "crear"
        ? "crear_metodo_pago"
        : "actualizar_metodo_pago",

    nombre: nombre,

    descripcion: metodoPagoDescripcion.value.trim(),

    estatus: metodoPagoEstatus.value,
  };

  if (modoMetodoPago === "editar") {
    datos.id_metodo_pago = metodoPagoActual.id_metodo_pago;
  }

  procesandoMetodoPago = true;

  bloquearMetodoPago(true);

  try {
    const respuesta = await fetch("../../backend/routes/configuracion.php", {
      method: "POST",

      credentials: "include",

      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify(datos),
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      mostrarMensajeMetodoPago(
        resultado.message || "No fue posible guardar el método.",
        "error",
      );

      return;
    }

    mostrarMensajeMetodoPago(resultado.message, "exito");

    await cargarMetodosPago();

    setTimeout(cerrarMetodoPago, 700);
  } catch (error) {
    console.error(error);

    mostrarMensajeMetodoPago("No se pudo conectar con el servidor.", "error");
  } finally {
    procesandoMetodoPago = false;

    bloquearMetodoPago(false);
  }
});

// ============================================================
// CAMBIAR ESTATUS MÉTODO
// ============================================================

async function cambiarEstatusMetodoPago(idMetodoPago, estatus) {
  const confirmar = window.confirm(
    estatus === "activo"
      ? "¿Deseas activar este método de pago?"
      : "¿Deseas desactivar este método de pago?",
  );

  if (!confirmar) {
    return;
  }

  try {
    const respuesta = await fetch("../../backend/routes/configuracion.php", {
      method: "POST",

      credentials: "include",

      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify({
        accion: "cambiar_estatus_metodo_pago",

        id_metodo_pago: idMetodoPago,

        estatus: estatus,
      }),
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      mostrarMensajeGeneral(
        resultado.message || "No fue posible cambiar el estatus.",
        "error",
      );

      return;
    }

    mostrarMensajeGeneral(resultado.message, "exito");

    await cargarMetodosPago();
  } catch (error) {
    console.error(error);

    mostrarMensajeGeneral("No se pudo conectar con el servidor.", "error");
  }
}

// ============================================================
// ESTADOS DE PRÉSTAMO
// ============================================================

async function cargarEstadosPrestamo() {
  try {
    const respuesta = await fetch(
      "../../backend/routes/configuracion.php?estados_prestamo=1",
      {
        credentials: "include",
      },
    );

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error("No fue posible cargar los estados.");
    }

    mostrarEstados(listaEstadosPrestamo, resultado.data || []);
  } catch (error) {
    console.error(error);

    listaEstadosPrestamo.innerHTML = "No fue posible cargar los estados.";
  }
}

// ============================================================
// ESTADOS DE SOLICITUD
// ============================================================

async function cargarEstadosSolicitud() {
  try {
    const respuesta = await fetch(
      "../../backend/routes/configuracion.php?estados_solicitud=1",
      {
        credentials: "include",
      },
    );

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error("No fue posible cargar los estados.");
    }

    mostrarEstados(listaEstadosSolicitud, resultado.data || []);
  } catch (error) {
    console.error(error);

    listaEstadosSolicitud.innerHTML = "No fue posible cargar los estados.";
  }
}

// ============================================================
// MOSTRAR ESTADOS
// ============================================================

function mostrarEstados(contenedor, estados) {
  if (estados.length === 0) {
    contenedor.innerHTML = "No hay estados registrados.";

    return;
  }

  contenedor.innerHTML = estados
    .map((estado) => {
      const estatus = normalizar(estado.estatus);

      return `
                <div class="estado-config-item">

                    <div class="estado-config-info">

                        <strong>
                            ${formatearEstado(estado.nombre)}
                        </strong>

                        <span>
                            ${escaparHTML(
                              estado.descripcion || "Sin descripción",
                            )}
                        </span>

                    </div>


                    <span
                        class="estatus-config estatus-${estatus}"
                    >
                        ${capitalizar(estatus)}
                    </span>

                </div>
            `;
    })
    .join("");
}

// ============================================================
// CERRAR TIPO
// ============================================================

cerrarModalTipoPrestamo.addEventListener("click", cerrarTipoPrestamo);

cancelarTipoPrestamo.addEventListener("click", cerrarTipoPrestamo);

function cerrarTipoPrestamo() {
  modalTipoPrestamo.classList.remove("show");

  tipoPrestamoActual = null;

  modoTipoPrestamo = "crear";

  limpiarFormularioTipoPrestamo();

  limpiarMensajeTipoPrestamo();
}

// ============================================================
// CERRAR MÉTODO
// ============================================================

cerrarModalMetodoPago.addEventListener("click", cerrarMetodoPago);

cancelarMetodoPago.addEventListener("click", cerrarMetodoPago);

function cerrarMetodoPago() {
  modalMetodoPago.classList.remove("show");

  metodoPagoActual = null;

  modoMetodoPago = "crear";

  limpiarFormularioMetodoPago();

  limpiarMensajeMetodoPago();
}

// ============================================================
// LIMPIAR FORMULARIOS
// ============================================================

function limpiarFormularioTipoPrestamo() {
  tipoPrestamoNombre.value = "";

  tipoPrestamoDescripcion.value = "";

  tipoPrestamoMontoMinimo.value = "";

  tipoPrestamoMontoMaximo.value = "";

  tipoPrestamoPlazoMinimo.value = "";

  tipoPrestamoPlazoMaximo.value = "";

  tipoPrestamoEstatus.value = "activo";
}

function limpiarFormularioMetodoPago() {
  metodoPagoNombre.value = "";

  metodoPagoDescripcion.value = "";

  metodoPagoEstatus.value = "activo";
}

// ============================================================
// BLOQUEAR FORMULARIOS
// ============================================================

function bloquearTipoPrestamo(bloquear) {
  guardarTipoPrestamo.disabled = bloquear;

  tipoPrestamoNombre.disabled = bloquear;

  tipoPrestamoDescripcion.disabled = bloquear;

  tipoPrestamoMontoMinimo.disabled = bloquear;

  tipoPrestamoMontoMaximo.disabled = bloquear;

  tipoPrestamoPlazoMinimo.disabled = bloquear;

  tipoPrestamoPlazoMaximo.disabled = bloquear;

  tipoPrestamoEstatus.disabled = bloquear;

  guardarTipoPrestamo.textContent = bloquear
    ? "Guardando..."
    : modoTipoPrestamo === "crear"
      ? "Guardar"
      : "Guardar cambios";
}

function bloquearMetodoPago(bloquear) {
  guardarMetodoPago.disabled = bloquear;

  metodoPagoNombre.disabled = bloquear;

  metodoPagoDescripcion.disabled = bloquear;

  metodoPagoEstatus.disabled = bloquear;

  guardarMetodoPago.textContent = bloquear
    ? "Guardando..."
    : modoMetodoPago === "crear"
      ? "Guardar"
      : "Guardar cambios";
}

// ============================================================
// MENSAJES
// ============================================================

function mostrarMensajeGeneral(texto, tipo) {
  mensajeConfiguracion.textContent = texto;

  mensajeConfiguracion.className = "mensaje-configuracion " + tipo;

  setTimeout(function () {
    mensajeConfiguracion.textContent = "";

    mensajeConfiguracion.className = "mensaje-configuracion";
  }, 4000);
}

function mostrarMensajeTipoPrestamo(texto, tipo) {
  mensajeTipoPrestamo.textContent = texto;

  mensajeTipoPrestamo.className = "mensaje-configuracion " + tipo;
}

function limpiarMensajeTipoPrestamo() {
  mensajeTipoPrestamo.textContent = "";

  mensajeTipoPrestamo.className = "mensaje-configuracion";
}

function mostrarMensajeMetodoPago(texto, tipo) {
  mensajeMetodoPago.textContent = texto;

  mensajeMetodoPago.className = "mensaje-configuracion " + tipo;
}

function limpiarMensajeMetodoPago() {
  mensajeMetodoPago.textContent = "";

  mensajeMetodoPago.className = "mensaje-configuracion";
}

// ============================================================
// UTILIDADES
// ============================================================

function normalizar(valor) {
  return String(valor || "")
    .trim()
    .toLowerCase();
}

function capitalizar(texto) {
  if (!texto) {
    return "";
  }

  const valor = String(texto);

  return valor.charAt(0).toUpperCase() + valor.slice(1);
}

function formatearEstado(estado) {
  return capitalizar(String(estado || "").replaceAll("_", " "));
}

function formatearDinero(valor) {
  return Number(valor || 0).toLocaleString("es-MX", {
    style: "currency",
    currency: "MXN",
    minimumFractionDigits: 2,
  });
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
// MENÚ DE USUARIO - ADMINISTRADOR
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

    logoutButtonPerfil.addEventListener("click", async function (event) {

        event.preventDefault();
        event.stopPropagation();

        try {

            await fetch(
                "../../backend/routes/logout.php",
                {
                    method: "POST",
                    credentials: "include",
                    cache: "no-store"
                }
            );

        } catch (error) {

            console.error(
                "Error cerrando sesión:",
                error
            );

        }

        window.location.replace("../index.html");

    });

}

// ============================================================
// INICIAR
// ============================================================

async function iniciar() {
  const sesionValida = await comprobarSesion();

  if (!sesionValida) {
    return;
  }

  await Promise.all([
    cargarTiposPrestamo(),

    cargarMetodosPago(),

    cargarEstadosPrestamo(),

    cargarEstadosSolicitud(),
  ]);
}

iniciar();
