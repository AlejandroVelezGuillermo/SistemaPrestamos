// ============================================================
// ELEMENTOS GENERALES
// ============================================================

const nombreUsuario = document.getElementById("nombreUsuario");

const rolUsuario = document.getElementById("rolUsuario");

const avatarUsuario = document.getElementById("avatarUsuario");

const usuarioMenu = document.getElementById("usuarioMenu");

const usuarioDropdown = document.getElementById("usuarioDropdown");

const logoutButtonPerfil = document.getElementById("logoutButtonPerfil");

const tablaSolicitudesBody = document.getElementById("tablaSolicitudesBody");

const buscadorSolicitudes = document.getElementById("buscadorSolicitudes");

const mensajeSolicitudes = document.getElementById("mensajeSolicitudes");

// ============================================================
// RESUMEN
// ============================================================

const totalSolicitudes = document.getElementById("totalSolicitudes");

const solicitudesPendientes = document.getElementById("solicitudesPendientes");

const solicitudesAprobadas = document.getElementById("solicitudesAprobadas");

const solicitudesRechazadas = document.getElementById("solicitudesRechazadas");

// ============================================================
// MODAL NUEVA SOLICITUD
// ============================================================

const btnNuevaSolicitud = document.getElementById("btnNuevaSolicitud");

const modalSolicitud = document.getElementById("modalSolicitud");

const cerrarModalSolicitud = document.getElementById("cerrarModalSolicitud");

const cancelarSolicitud = document.getElementById("cancelarSolicitud");

const formSolicitud = document.getElementById("formSolicitud");

const guardarSolicitud = document.getElementById("guardarSolicitud");

const mensajeFormularioSolicitud = document.getElementById(
  "mensajeFormularioSolicitud",
);

// ============================================================
// CAMPOS NUEVA SOLICITUD
// ============================================================

const clienteSolicitud = document.getElementById("clienteSolicitud");

const tipoPrestamo = document.getElementById("tipoPrestamo");

const montoSolicitado = document.getElementById("montoSolicitado");

const plazoMeses = document.getElementById("plazoMeses");

const frecuenciaPago = document.getElementById("frecuenciaPago");

const motivoSolicitud = document.getElementById("motivoSolicitud");

const observacionesSolicitud = document.getElementById(
  "observacionesSolicitud",
);

const infoTipoPrestamo = document.getElementById("infoTipoPrestamo");

// ============================================================
// MODAL VER SOLICITUD
// ============================================================

const modalVerSolicitud = document.getElementById("modalVerSolicitud");

const cerrarModalVer = document.getElementById("cerrarModalVer");

const cerrarDetalleSolicitud = document.getElementById(
  "cerrarDetalleSolicitud",
);

const detalleSolicitudId = document.getElementById("detalleSolicitudId");

const detalleCliente = document.getElementById("detalleCliente");

const detalleCorreo = document.getElementById("detalleCorreo");

const detalleTipo = document.getElementById("detalleTipo");

const detalleMonto = document.getElementById("detalleMonto");

const detallePlazo = document.getElementById("detallePlazo");

const detalleFrecuencia = document.getElementById("detalleFrecuencia");

const detalleEstadoSolicitud = document.getElementById(
  "detalleEstadoSolicitud",
);

const detalleFechaSolicitud = document.getElementById("detalleFechaSolicitud");

const detalleMotivo = document.getElementById("detalleMotivo");

const detalleObservaciones = document.getElementById("detalleObservaciones");

// ============================================================
// REVISIÓN
// ============================================================

const detalleEstadoRevision = document.getElementById("detalleEstadoRevision");

const detalleFechaRevision = document.getElementById("detalleFechaRevision");

const observacionesRevision = document.getElementById("observacionesRevision");

const btnIniciarRevision = document.getElementById("btnIniciarRevision");

const btnAprobarSolicitud = document.getElementById("btnAprobarSolicitud");

const btnRechazarSolicitud = document.getElementById("btnRechazarSolicitud");

const mensajeRevisionSolicitud = document.getElementById(
  "mensajeRevisionSolicitud",
);

// ============================================================
// APROBACIÓN DEL PRÉSTAMO
// ============================================================

const seccionAprobacionPrestamo = document.getElementById(
  "seccionAprobacionPrestamo",
);

const tasaInteresAprobacion = document.getElementById("tasaInteresAprobacion");

const requiereAval = document.getElementById("requiereAval");

const requiereGarantia = document.getElementById("requiereGarantia");

const resumenMontoAprobado = document.getElementById("resumenMontoAprobado");

const resumenIntereses = document.getElementById("resumenIntereses");

const resumenTotalPagar = document.getElementById("resumenTotalPagar");

const resumenNumeroCuotas = document.getElementById("resumenNumeroCuotas");

const resumenMontoCuota = document.getElementById("resumenMontoCuota");

// ============================================================
// VARIABLES
// ============================================================

let solicitudes = [];

let clientes = [];

let tiposPrestamo = [];

let solicitudActualId = null;

let solicitudActual = null;

let procesandoRevision = false;

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

    if (!datos.sesion) {
      window.location.href = "../index.html";

      return false;
    }

    const usuario = datos.usuario;

    if (usuario.rol !== "administrador" && usuario.rol !== "gerente") {
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
// CARGAR SOLICITUDES
// ============================================================

async function cargarSolicitudes() {
  tablaSolicitudesBody.innerHTML = `
        <tr>
            <td colspan="9" class="tabla-cargando">
                Cargando solicitudes...
            </td>
        </tr>
    `;

  try {
    const respuesta = await fetch("../../backend/routes/solicitudes.php", {
      method: "GET",
      credentials: "include",
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(
        resultado.message || "No fue posible cargar las solicitudes.",
      );
    }

    solicitudes = resultado.data || [];

    actualizarResumen();

    mostrarSolicitudes(solicitudes);
  } catch (error) {
    console.error(error);

    tablaSolicitudesBody.innerHTML = `
            <tr>
                <td colspan="9" class="tabla-vacia">
                    No fue posible cargar las solicitudes.
                </td>
            </tr>
        `;

    mostrarMensajeSolicitudes(
      error.message || "No fue posible cargar las solicitudes.",
      "error",
    );
  }
}

// ============================================================
// MOSTRAR SOLICITUDES
// ============================================================

function mostrarSolicitudes(lista) {
  if (lista.length === 0) {
    tablaSolicitudesBody.innerHTML = `
            <tr>
                <td colspan="9" class="tabla-vacia">
                    No hay solicitudes registradas.
                </td>
            </tr>
        `;

    return;
  }

  tablaSolicitudesBody.innerHTML = lista
    .map((solicitud) => {
      const nombreCompleto = [
        solicitud.nombre,
        solicitud.apellido_paterno,
        solicitud.apellido_materno,
      ]
        .filter(Boolean)
        .join(" ");

      const estado = normalizarEstado(solicitud.estado_solicitud);

      return `
                <tr>

                    <td>
                        <span class="solicitud-id">
                            #${solicitud.id_solicitud}
                        </span>
                    </td>


                    <td>

                        <span class="cliente-nombre">
                            ${escaparHTML(nombreCompleto)}
                        </span>

                        <span class="cliente-correo">
                            ${escaparHTML(solicitud.correo || "")}
                        </span>

                    </td>


                    <td>
                        ${escaparHTML(solicitud.tipo_prestamo)}
                    </td>


                    <td>
                        ${formatearDinero(solicitud.monto_solicitado)}
                    </td>


                    <td>
                        ${solicitud.plazo_meses} meses
                    </td>


                    <td>
                        ${capitalizar(solicitud.frecuencia_pago)}
                    </td>


                    <td>

                        <span
                            class="estado-solicitud ${obtenerClaseEstado(estado)}"
                        >
                            ${formatearEstado(estado)}
                        </span>

                    </td>


                    <td>
                        ${formatearFechaHora(solicitud.fecha_solicitud)}
                    </td>


                    <td>

                        <button
                            type="button"
                            class="btn-accion btn-ver-solicitud"
                            data-id="${solicitud.id_solicitud}"
                        >
                            Ver
                        </button>

                    </td>

                </tr>
            `;
    })
    .join("");
}

// ============================================================
// ACTUALIZAR RESUMEN
// ============================================================

function actualizarResumen() {
  totalSolicitudes.textContent = solicitudes.length;

  solicitudesPendientes.textContent = solicitudes.filter(
    (solicitud) => normalizarEstado(solicitud.estado_solicitud) === "pendiente",
  ).length;

  solicitudesAprobadas.textContent = solicitudes.filter(
    (solicitud) => normalizarEstado(solicitud.estado_solicitud) === "aprobada",
  ).length;

  solicitudesRechazadas.textContent = solicitudes.filter(
    (solicitud) => normalizarEstado(solicitud.estado_solicitud) === "rechazada",
  ).length;
}

// ============================================================
// CARGAR CLIENTES
// ============================================================

async function cargarClientes() {
  try {
    const respuesta = await fetch("../../backend/routes/clientes.php", {
      method: "GET",
      credentials: "include",
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(
        resultado.message || "No fue posible cargar los clientes.",
      );
    }

    clientes = (resultado.data || []).filter(
      (cliente) => cliente.estatus === "activo",
    );

    llenarClientes();
  } catch (error) {
    console.error(error);
  }
}

// ============================================================
// LLENAR CLIENTES
// ============================================================

function llenarClientes() {
  clienteSolicitud.innerHTML = `
        <option value="">
            Selecciona un cliente...
        </option>
    `;

  clientes.forEach((cliente) => {
    const nombreCompleto = [
      cliente.nombre,
      cliente.apellido_paterno,
      cliente.apellido_materno,
    ]
      .filter(Boolean)
      .join(" ");

    const option = document.createElement("option");

    option.value = cliente.id_usuario;

    option.textContent = nombreCompleto + " - " + cliente.correo;

    clienteSolicitud.appendChild(option);
  });
}

// ============================================================
// CARGAR TIPOS DE PRÉSTAMO
// ============================================================

async function cargarTiposPrestamo() {
  try {
    const respuesta = await fetch(
      "../../backend/routes/solicitudes.php?tipos=1",
      {
        method: "GET",
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

    llenarTiposPrestamo();
  } catch (error) {
    console.error(error);
  }
}

// ============================================================
// LLENAR TIPOS
// ============================================================

function llenarTiposPrestamo() {
  tipoPrestamo.innerHTML = `
        <option value="">
            Selecciona...
        </option>
    `;

  tiposPrestamo.forEach((tipo) => {
    const option = document.createElement("option");

    option.value = tipo.id_tipo_prestamo;

    option.textContent = tipo.nombre;

    tipoPrestamo.appendChild(option);
  });
}

// ============================================================
// MOSTRAR LÍMITES DEL TIPO
// ============================================================

tipoPrestamo.addEventListener("change", function () {
  const id = tipoPrestamo.value;

  const tipo = tiposPrestamo.find(
    (item) => String(item.id_tipo_prestamo) === String(id),
  );

  if (!tipo) {
    infoTipoPrestamo.textContent =
      "Selecciona un tipo de préstamo para consultar sus límites.";

    montoSolicitado.removeAttribute("min");

    montoSolicitado.removeAttribute("max");

    plazoMeses.removeAttribute("min");

    plazoMeses.removeAttribute("max");

    return;
  }

  montoSolicitado.min = tipo.monto_minimo;

  montoSolicitado.max = tipo.monto_maximo;

  plazoMeses.min = tipo.plazo_minimo;

  plazoMeses.max = tipo.plazo_maximo;

  infoTipoPrestamo.innerHTML = `
            <strong>
                ${escaparHTML(tipo.nombre)}
            </strong>

            <br>

            Monto permitido:
            ${formatearDinero(tipo.monto_minimo)}
            a
            ${formatearDinero(tipo.monto_maximo)}

            ·

            Plazo:
            ${tipo.plazo_minimo}
            a
            ${tipo.plazo_maximo}
            meses.
        `;
});

// ============================================================
// NUEVA SOLICITUD
// ============================================================

btnNuevaSolicitud.addEventListener("click", function () {
  formSolicitud.reset();

  limpiarMensajeFormulario();

  infoTipoPrestamo.textContent =
    "Selecciona un tipo de préstamo para consultar sus límites.";

  modalSolicitud.classList.add("show");
});

// ============================================================
// CERRAR NUEVA SOLICITUD
// ============================================================

cerrarModalSolicitud.addEventListener("click", cerrarNuevaSolicitud);

cancelarSolicitud.addEventListener("click", cerrarNuevaSolicitud);

function cerrarNuevaSolicitud() {
  modalSolicitud.classList.remove("show");

  formSolicitud.reset();

  limpiarMensajeFormulario();
}

// ============================================================
// REGISTRAR SOLICITUD
// ============================================================

formSolicitud.addEventListener("submit", async function (event) {
  event.preventDefault();

  limpiarMensajeFormulario();

  const datos = {
    id_usuario: clienteSolicitud.value,

    id_tipo_prestamo: tipoPrestamo.value,

    monto_solicitado: montoSolicitado.value,

    plazo_meses: plazoMeses.value,

    frecuencia_pago: frecuenciaPago.value,

    motivo: motivoSolicitud.value.trim(),

    observaciones: observacionesSolicitud.value.trim(),
  };

  guardarSolicitud.disabled = true;

  guardarSolicitud.textContent = "Registrando...";

  try {
    const respuesta = await fetch("../../backend/routes/solicitudes.php", {
      method: "POST",

      credentials: "include",

      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify(datos),
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      mostrarMensajeFormulario(
        resultado.message || "No fue posible registrar la solicitud.",
        "error",
      );

      return;
    }

    mostrarMensajeFormulario(resultado.message, "exito");

    await cargarSolicitudes();

    setTimeout(cerrarNuevaSolicitud, 800);
  } catch (error) {
    console.error(error);

    mostrarMensajeFormulario("No se pudo conectar con el servidor.", "error");
  } finally {
    guardarSolicitud.disabled = false;

    guardarSolicitud.textContent = "Registrar solicitud";
  }
});

// ============================================================
// VER SOLICITUD DESDE TABLA
// ============================================================

tablaSolicitudesBody.addEventListener("click", async function (event) {
  const boton = event.target.closest(".btn-ver-solicitud");

  if (!boton) {
    return;
  }

  await verSolicitud(boton.dataset.id);
});

// ============================================================
// OBTENER UNA SOLICITUD
// ============================================================

async function obtenerSolicitud(idSolicitud) {
  const respuesta = await fetch(
    "../../backend/routes/solicitudes.php?id=" +
      encodeURIComponent(idSolicitud),
    {
      method: "GET",
      credentials: "include",
    },
  );

  const resultado = await respuesta.json();

  if (!respuesta.ok || !resultado.success) {
    throw new Error(
      resultado.message || "No fue posible consultar la solicitud.",
    );
  }

  return resultado.data;
}

// ============================================================
// VER SOLICITUD
// ============================================================

async function verSolicitud(idSolicitud) {
  limpiarMensajeRevision();

  try {
    const solicitud = await obtenerSolicitud(idSolicitud);

    solicitudActualId = solicitud.id_solicitud;

    solicitudActual = solicitud;

    mostrarDetalleSolicitud(solicitud);

    modalVerSolicitud.classList.add("show");
  } catch (error) {
    console.error(error);

    mostrarMensajeSolicitudes(
      error.message || "No se pudo consultar la solicitud.",
      "error",
    );
  }
}

// ============================================================
// MOSTRAR DETALLE
// ============================================================

function mostrarDetalleSolicitud(solicitud) {
  solicitudActual = solicitud;

  const nombreCompleto = [
    solicitud.nombre,
    solicitud.apellido_paterno,
    solicitud.apellido_materno,
  ]
    .filter(Boolean)
    .join(" ");

  const estado = normalizarEstado(solicitud.estado_solicitud);

  detalleSolicitudId.textContent = "Solicitud #" + solicitud.id_solicitud;

  detalleCliente.value = nombreCompleto;

  detalleCorreo.value = solicitud.correo || "Sin registrar";

  detalleTipo.value = solicitud.tipo_prestamo || "";

  detalleMonto.value = formatearDinero(solicitud.monto_solicitado);

  detallePlazo.value = solicitud.plazo_meses + " meses";

  detalleFrecuencia.value = capitalizar(solicitud.frecuencia_pago);

  detalleEstadoSolicitud.value = formatearEstado(estado);

  detalleFechaSolicitud.value = formatearFechaHora(solicitud.fecha_solicitud);

  detalleMotivo.value = solicitud.motivo || "Sin registrar";

  detalleObservaciones.value = solicitud.observaciones || "Sin observaciones";

  detalleEstadoRevision.value = formatearEstado(estado);

  detalleFechaRevision.value = solicitud.fecha_revision
    ? formatearFechaHora(solicitud.fecha_revision)
    : "Sin revisar";

  observacionesRevision.value = solicitud.observaciones_revision || "";

  configurarAccionesRevision(estado);
}

// ============================================================
// CONFIGURAR ACCIONES
// ============================================================

function configurarAccionesRevision(estado) {
  btnIniciarRevision.style.display = "none";

  btnAprobarSolicitud.style.display = "none";

  btnRechazarSolicitud.style.display = "none";

  seccionAprobacionPrestamo.style.display = "none";

  observacionesRevision.readOnly = false;

  if (estado === "pendiente") {
    btnIniciarRevision.style.display = "inline-flex";

    limpiarCamposAprobacion();

    return;
  }

  if (estado === "en_revision") {
    seccionAprobacionPrestamo.style.display = "block";

    btnAprobarSolicitud.style.display = "inline-flex";

    btnRechazarSolicitud.style.display = "inline-flex";

    prepararResumenAprobacion();

    return;
  }

  // ---------------------------------------------------------
  // APROBADA / RECHAZADA / CANCELADA
  // ---------------------------------------------------------

  observacionesRevision.readOnly = true;

  limpiarCamposAprobacion();
}

// ============================================================
// PREPARAR RESUMEN
// ============================================================

function prepararResumenAprobacion() {
  if (!solicitudActual) {
    return;
  }

  resumenMontoAprobado.value = formatearDinero(
    solicitudActual.monto_solicitado,
  );

  calcularResumenPrestamo();
}

// ============================================================
// CAMBIOS EN TASA
// ============================================================

tasaInteresAprobacion.addEventListener("input", calcularResumenPrestamo);

// ============================================================
// CALCULAR RESUMEN EN TIEMPO REAL
// ============================================================

function calcularResumenPrestamo() {
  if (!solicitudActual) {
    return;
  }

  const monto = Number(solicitudActual.monto_solicitado);

  const tasa = Number(tasaInteresAprobacion.value);

  const plazo = Number(solicitudActual.plazo_meses);

  const frecuencia = solicitudActual.frecuencia_pago;

  resumenMontoAprobado.value = formatearDinero(monto);

  if (
    tasaInteresAprobacion.value === "" ||
    Number.isNaN(tasa) ||
    tasa < 0 ||
    tasa > 100
  ) {
    resumenIntereses.value = "$0.00";

    resumenTotalPagar.value = "$0.00";

    resumenNumeroCuotas.value = "0";

    resumenMontoCuota.value = "$0.00";

    return;
  }

  // ========================================================
  // INTERÉS SIMPLE
  // ========================================================

  const intereses = redondearDinero(monto * (tasa / 100));

  const totalPagar = redondearDinero(monto + intereses);

  const numeroCuotas = calcularNumeroCuotas(plazo, frecuencia);

  const montoCuota =
    numeroCuotas > 0 ? redondearDinero(totalPagar / numeroCuotas) : 0;

  resumenIntereses.value = formatearDinero(intereses);

  resumenTotalPagar.value = formatearDinero(totalPagar);

  resumenNumeroCuotas.value = String(numeroCuotas);

  resumenMontoCuota.value = formatearDinero(montoCuota);
}

// ============================================================
// CALCULAR NÚMERO DE CUOTAS
// DEBE COINCIDIR CON EL BACKEND
// ============================================================

function calcularNumeroCuotas(plazo, frecuencia) {
  if (!Number.isFinite(plazo) || plazo <= 0) {
    return 0;
  }

  if (frecuencia === "mensual") {
    return Math.round(plazo);
  }

  if (frecuencia === "quincenal") {
    return Math.round(plazo * 2);
  }

  if (frecuencia === "semanal") {
    return Math.max(1, Math.round(plazo * (52 / 12)));
  }

  return 0;
}

// ============================================================
// INICIAR REVISIÓN
// ============================================================

btnIniciarRevision.addEventListener("click", async function () {
  if (!solicitudActualId || procesandoRevision) {
    return;
  }

  await procesarRevision("poner_en_revision");
});

// ============================================================
// APROBAR Y GENERAR PRÉSTAMO
// ============================================================

btnAprobarSolicitud.addEventListener("click", async function () {
  if (!solicitudActualId || procesandoRevision) {
    return;
  }

  const tasa = Number(tasaInteresAprobacion.value);

  if (tasaInteresAprobacion.value === "" || Number.isNaN(tasa)) {
    mostrarMensajeRevision("Ingresa la tasa de interés.", "error");

    tasaInteresAprobacion.focus();

    return;
  }

  if (tasa < 0 || tasa > 100) {
    mostrarMensajeRevision(
      "La tasa de interés debe estar entre 0% y 100%.",
      "error",
    );

    tasaInteresAprobacion.focus();

    return;
  }

  const confirmar = window.confirm(
    "¿Deseas aprobar esta solicitud y generar el préstamo?",
  );

  if (!confirmar) {
    return;
  }

  await procesarRevision("aprobar");
});

// ============================================================
// RECHAZAR SOLICITUD
// ============================================================

btnRechazarSolicitud.addEventListener("click", async function () {
  if (!solicitudActualId || procesandoRevision) {
    return;
  }

  const observacion = observacionesRevision.value.trim();

  if (observacion === "") {
    mostrarMensajeRevision("Debes indicar el motivo del rechazo.", "error");

    observacionesRevision.focus();

    return;
  }

  const confirmar = window.confirm("¿Deseas rechazar esta solicitud?");

  if (!confirmar) {
    return;
  }

  await procesarRevision("rechazar");
});

// ============================================================
// PROCESAR REVISIÓN
// ============================================================

async function procesarRevision(accion) {
  procesandoRevision = true;

  bloquearBotonesRevision(true);

  limpiarMensajeRevision();

  const observaciones = observacionesRevision.value.trim();

  const datos = {
    accion: accion,

    id_solicitud: solicitudActualId,

    observaciones: observaciones,
  };

  // ---------------------------------------------------------
  // DATOS EXTRA AL APROBAR
  // ---------------------------------------------------------

  if (accion === "aprobar") {
    datos.tasa_interes = tasaInteresAprobacion.value;

    datos.requiere_aval = requiereAval.value;

    datos.requiere_garantia = requiereGarantia.value;
  }

  try {
    const respuesta = await fetch("../../backend/routes/solicitudes.php", {
      method: "POST",

      credentials: "include",

      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify(datos),
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      mostrarMensajeRevision(
        resultado.message || "No fue posible procesar la solicitud.",
        "error",
      );

      return;
    }

    mostrarMensajeRevision(resultado.message, "exito");

    // -----------------------------------------------------
    // SI SE APROBÓ, MOSTRAR CÁLCULOS DEL SERVIDOR
    // -----------------------------------------------------

    if (accion === "aprobar" && resultado.calculos) {
      resumenMontoAprobado.value = formatearDinero(
        resultado.calculos.monto_aprobado,
      );

      resumenIntereses.value = formatearDinero(
        resultado.calculos.total_intereses,
      );

      resumenTotalPagar.value = formatearDinero(resultado.calculos.total_pagar);

      resumenNumeroCuotas.value = String(resultado.calculos.numero_cuotas);

      resumenMontoCuota.value = formatearDinero(resultado.calculos.monto_cuota);
    }

    // -----------------------------------------------------
    // RECARGAR TABLA
    // -----------------------------------------------------

    await cargarSolicitudes();

    // -----------------------------------------------------
    // RECARGAR SOLICITUD ABIERTA
    // -----------------------------------------------------

    const solicitudActualizada = await obtenerSolicitud(solicitudActualId);

    solicitudActual = solicitudActualizada;

    mostrarDetalleSolicitud(solicitudActualizada);
  } catch (error) {
    console.error(error);

    mostrarMensajeRevision("No se pudo conectar con el servidor.", "error");
  } finally {
    procesandoRevision = false;

    bloquearBotonesRevision(false);
  }
}

// ============================================================
// BLOQUEAR BOTONES
// ============================================================

function bloquearBotonesRevision(bloquear) {
  btnIniciarRevision.disabled = bloquear;

  btnAprobarSolicitud.disabled = bloquear;

  btnRechazarSolicitud.disabled = bloquear;

  tasaInteresAprobacion.disabled = bloquear;

  requiereAval.disabled = bloquear;

  requiereGarantia.disabled = bloquear;

  if (bloquear) {
    if (btnIniciarRevision.style.display !== "none") {
      btnIniciarRevision.textContent = "Procesando...";
    }

    if (btnAprobarSolicitud.style.display !== "none") {
      btnAprobarSolicitud.textContent = "Generando préstamo...";
    }

    if (btnRechazarSolicitud.style.display !== "none") {
      btnRechazarSolicitud.textContent = "Procesando...";
    }
  } else {
    btnIniciarRevision.textContent = "Iniciar revisión";

    btnAprobarSolicitud.textContent = "Aprobar y generar préstamo";

    btnRechazarSolicitud.textContent = "Rechazar solicitud";
  }
}

// ============================================================
// LIMPIAR APROBACIÓN
// ============================================================

function limpiarCamposAprobacion() {
  tasaInteresAprobacion.value = "";

  requiereAval.value = "0";

  requiereGarantia.value = "0";

  resumenMontoAprobado.value = "$0.00";

  resumenIntereses.value = "$0.00";

  resumenTotalPagar.value = "$0.00";

  resumenNumeroCuotas.value = "0";

  resumenMontoCuota.value = "$0.00";
}

// ============================================================
// CERRAR DETALLE
// ============================================================

cerrarModalVer.addEventListener("click", cerrarVerSolicitud);

cerrarDetalleSolicitud.addEventListener("click", cerrarVerSolicitud);

function cerrarVerSolicitud() {
  modalVerSolicitud.classList.remove("show");

  solicitudActualId = null;

  solicitudActual = null;

  observacionesRevision.value = "";

  limpiarCamposAprobacion();

  limpiarMensajeRevision();
}

// ============================================================
// BUSCADOR
// ============================================================

buscadorSolicitudes.addEventListener("input", function () {
  const texto = buscadorSolicitudes.value.trim().toLowerCase();

  if (!texto) {
    mostrarSolicitudes(solicitudes);

    return;
  }

  const filtradas = solicitudes.filter((solicitud) => {
    const valores = [
      solicitud.id_solicitud,

      solicitud.nombre,

      solicitud.apellido_paterno,

      solicitud.apellido_materno,

      solicitud.correo,

      solicitud.tipo_prestamo,

      solicitud.estado_solicitud,

      solicitud.frecuencia_pago,
    ];

    return valores.some((valor) =>
      String(valor || "")
        .toLowerCase()
        .includes(texto),
    );
  });

  mostrarSolicitudes(filtradas);
});

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
// UTILIDADES
// ============================================================

function normalizarEstado(estado) {
  return String(estado || "")
    .trim()
    .toLowerCase();
}

function formatearEstado(estado) {
  const valor = normalizarEstado(estado);

  const nombres = {
    pendiente: "Pendiente",

    en_revision: "En revisión",

    aprobada: "Aprobada",

    rechazada: "Rechazada",

    cancelada: "Cancelada",
  };

  return nombres[valor] || capitalizar(valor);
}

function obtenerClaseEstado(estado) {
  const valor = normalizarEstado(estado);

  if (valor === "aprobada") {
    return "estado-aprobada";
  }

  if (valor === "rechazada") {
    return "estado-rechazada";
  }

  if (valor === "cancelada") {
    return "estado-cancelada";
  }

  return "estado-pendiente";
}

function formatearDinero(valor) {
  const numero = Number(valor || 0);

  return numero.toLocaleString("es-MX", {
    style: "currency",
    currency: "MXN",
    minimumFractionDigits: 2,
  });
}

function redondearDinero(valor) {
  return Math.round((Number(valor) + Number.EPSILON) * 100) / 100;
}

function formatearFechaHora(fecha) {
  if (!fecha) {
    return "Sin registrar";
  }

  const valor = String(fecha).includes(" ")
    ? String(fecha).replace(" ", "T")
    : fecha;

  const objeto = new Date(valor);

  if (Number.isNaN(objeto.getTime())) {
    return fecha;
  }

  return objeto.toLocaleString("es-MX", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

function capitalizar(texto) {
  if (!texto) {
    return "";
  }

  const valor = String(texto);

  return valor.charAt(0).toUpperCase() + valor.slice(1);
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
// MENSAJES GENERALES
// ============================================================

function mostrarMensajeSolicitudes(texto, tipo) {
  mensajeSolicitudes.textContent = texto;

  mensajeSolicitudes.className = "mensaje-solicitudes " + tipo;
}

// ============================================================
// MENSAJES FORMULARIO
// ============================================================

function mostrarMensajeFormulario(texto, tipo) {
  mensajeFormularioSolicitud.textContent = texto;

  mensajeFormularioSolicitud.className = "mensaje-formulario " + tipo;
}

function limpiarMensajeFormulario() {
  mensajeFormularioSolicitud.textContent = "";

  mensajeFormularioSolicitud.className = "mensaje-formulario";
}

// ============================================================
// MENSAJES REVISIÓN
// ============================================================

function mostrarMensajeRevision(texto, tipo) {
  mensajeRevisionSolicitud.textContent = texto;

  mensajeRevisionSolicitud.className = "mensaje-formulario " + tipo;
}

function limpiarMensajeRevision() {
  mensajeRevisionSolicitud.textContent = "";

  mensajeRevisionSolicitud.className = "mensaje-formulario";
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
    cargarClientes(),

    cargarTiposPrestamo(),

    cargarSolicitudes(),
  ]);
}

iniciar();
