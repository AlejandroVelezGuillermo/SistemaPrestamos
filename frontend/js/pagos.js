// ============================================================
// GENERALES
// ============================================================

const nombreUsuario = document.getElementById("nombreUsuario");

const rolUsuario = document.getElementById("rolUsuario");

const avatarUsuario = document.getElementById("avatarUsuario");

const usuarioMenu = document.getElementById("usuarioMenu");

const usuarioDropdown = document.getElementById("usuarioDropdown");

const logoutButtonPerfil = document.getElementById("logoutButtonPerfil");

const tablaPrestamosPagoBody = document.getElementById(
  "tablaPrestamosPagoBody",
);

const buscadorPagos = document.getElementById("buscadorPagos");

const mensajePagos = document.getElementById("mensajePagos");

const tablaHistorialPagosBody = document.getElementById(
  "tablaHistorialPagosBody",
);

// ============================================================
// RESUMEN GENERAL
// ============================================================

const totalPrestamosActivos = document.getElementById("totalPrestamosActivos");

const totalPorCobrar = document.getElementById("totalPorCobrar");

const totalPagadoGeneral = document.getElementById("totalPagadoGeneral");

// ============================================================
// MODAL PRÉSTAMO
// ============================================================

const modalPagos = document.getElementById("modalPagos");

const cerrarModalPagos = document.getElementById("cerrarModalPagos");

const cerrarDetallePagos = document.getElementById("cerrarDetallePagos");

const detallePrestamoPagoId = document.getElementById("detallePrestamoPagoId");

const detalleClientePago = document.getElementById("detalleClientePago");

const detalleTipoPago = document.getElementById("detalleTipoPago");

const detalleTotalPrestamo = document.getElementById("detalleTotalPrestamo");

const detalleMontoCuotaPago = document.getElementById("detalleMontoCuotaPago");

const resumenCuotasPagadas = document.getElementById("resumenCuotasPagadas");

const resumenCuotasPendientes = document.getElementById(
  "resumenCuotasPendientes",
);

const resumenTotalPagado = document.getElementById("resumenTotalPagado");

const resumenSaldoPendiente = document.getElementById("resumenSaldoPendiente");

const tablaCuotasPagoBody = document.getElementById("tablaCuotasPagoBody");

// ============================================================
// MODAL REGISTRAR PAGO
// ============================================================

const modalRegistrarPago = document.getElementById("modalRegistrarPago");

const cerrarModalRegistrarPago = document.getElementById(
  "cerrarModalRegistrarPago",
);

const cancelarRegistrarPago = document.getElementById("cancelarRegistrarPago");

const detalleCuotaSeleccionada = document.getElementById(
  "detalleCuotaSeleccionada",
);

const pagoTotalCuota = document.getElementById("pagoTotalCuota");

const pagoSaldoCuota = document.getElementById("pagoSaldoCuota");

const montoPago = document.getElementById("montoPago");

const metodoPago = document.getElementById("metodoPago");

const fechaPago = document.getElementById("fechaPago");

const referenciaPago = document.getElementById("referenciaPago");

const comprobantePago = document.getElementById("comprobantePago");

const observacionesPago = document.getElementById("observacionesPago");

const mensajeRegistrarPago = document.getElementById("mensajeRegistrarPago");

const btnRegistrarPago = document.getElementById("btnRegistrarPago");

// ============================================================
// VARIABLES
// ============================================================

let prestamos = [];

let metodosPago = [];

let prestamoActual = null;

let cuotaActual = null;

let procesandoPago = false;

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

    return false;
  }
}

// ============================================================
// CARGAR PRÉSTAMOS
// ============================================================

async function cargarPrestamos() {
  try {
    const respuesta = await fetch("../../backend/routes/pagos.php", {
      credentials: "include",
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(
        resultado.message || "No fue posible cargar los préstamos.",
      );
    }

    prestamos = resultado.data || [];

    mostrarPrestamos(prestamos);

    actualizarResumenGeneral();
  } catch (error) {
    console.error(error);

    tablaPrestamosPagoBody.innerHTML = `
            <tr>
                <td colspan="8" class="tabla-vacia">
                    No fue posible cargar los préstamos.
                </td>
            </tr>
        `;
  }
}

// ============================================================
// MOSTRAR PRÉSTAMOS
// ============================================================

function mostrarPrestamos(lista) {
  if (lista.length === 0) {
    tablaPrestamosPagoBody.innerHTML = `
            <tr>
                <td colspan="8" class="tabla-vacia">
                    No hay préstamos activos.
                </td>
            </tr>
        `;

    return;
  }

  tablaPrestamosPagoBody.innerHTML = lista
    .map((prestamo) => {
      const nombreCompleto = [
        prestamo.nombre,
        prestamo.apellido_paterno,
        prestamo.apellido_materno,
      ]
        .filter(Boolean)
        .join(" ");

      return `
                <tr>

                    <td>
                        #${prestamo.id_prestamo}
                    </td>

                    <td>
                        <strong>
                            ${escaparHTML(nombreCompleto)}
                        </strong>

                        <br>

                        <small>
                            ${escaparHTML(prestamo.correo || "")}
                        </small>
                    </td>

                    <td>
                        ${formatearDinero(prestamo.total_pagar)}
                    </td>

                    <td>
                        ${prestamo.numero_cuotas}
                    </td>

                    <td>
                        ${capitalizar(prestamo.frecuencia_pago)}
                    </td>

                    <td>
                        ${formatearFecha(prestamo.fecha_inicio_pago)}
                    </td>

                    <td>
                        ${formatearFecha(prestamo.fecha_vencimiento)}
                    </td>

                    <td>

                        <button
                            type="button"
                            class="btn-accion btn-ver-pagos"
                            data-id="${prestamo.id_prestamo}"
                        >
                            Ver cuotas
                        </button>

                    </td>

                </tr>
            `;
    })
    .join("");
}

// ============================================================
// RESUMEN GENERAL DE PRÉSTAMOS
// ============================================================

function actualizarResumenGeneral() {
  totalPrestamosActivos.textContent = prestamos.length;
}

// ============================================================
// CARGAR RESUMEN REAL DE PAGOS
// ============================================================

async function cargarResumenGeneral() {
  try {
    const respuesta = await fetch("../../backend/routes/pagos.php?resumen=1", {
      credentials: "include",
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(resultado.message || "No fue posible cargar el resumen.");
    }

    // TOTAL PAGADO
    totalPagadoGeneral.textContent = formatearDinero(
      resultado.data.total_pagado,
    );

    // TOTAL REAL PENDIENTE POR COBRAR
    totalPorCobrar.textContent = formatearDinero(
      resultado.data.total_por_cobrar,
    );
  } catch (error) {
    console.error(error);

    totalPagadoGeneral.textContent = "$0.00";

    totalPorCobrar.textContent = "$0.00";
  }
}

// ============================================================
// CARGAR HISTORIAL
// ============================================================

async function cargarHistorialPagos() {
  if (!tablaHistorialPagosBody) {
    return;
  }

  tablaHistorialPagosBody.innerHTML = `
        <tr>
            <td colspan="8" class="tabla-cargando">
                Cargando historial...
            </td>
        </tr>
    `;

  try {
    const respuesta = await fetch(
      "../../backend/routes/pagos.php?historial=1",
      {
        credentials: "include",
      },
    );

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(
        resultado.message || "No fue posible cargar el historial.",
      );
    }

    mostrarHistorialPagos(resultado.data || []);
  } catch (error) {
    console.error(error);

    tablaHistorialPagosBody.innerHTML = `
            <tr>
                <td colspan="8" class="tabla-vacia">
                    No fue posible cargar el historial.
                </td>
            </tr>
        `;
  }
}

// ============================================================
// MOSTRAR HISTORIAL
// ============================================================

function mostrarHistorialPagos(historial) {
  if (!tablaHistorialPagosBody) {
    return;
  }

  if (historial.length === 0) {
    tablaHistorialPagosBody.innerHTML = `
            <tr>
                <td colspan="8" class="tabla-vacia">
                    Todavía no hay pagos registrados.
                </td>
            </tr>
        `;

    return;
  }

  tablaHistorialPagosBody.innerHTML = historial
    .map((pago) => {
      const nombreCompleto = [
        pago.nombre,
        pago.apellido_paterno,
        pago.apellido_materno,
      ]
        .filter(Boolean)
        .join(" ");

      const estado = String(pago.estatus || "")
        .trim()
        .toLowerCase();

      return `
                <tr>

                    <td>
                        <strong>
                            #${pago.id_pago}
                        </strong>
                    </td>


                    <td>

                        <span class="historial-cliente">
                            ${escaparHTML(nombreCompleto)}
                        </span>

                        <span class="historial-secundario">
                            ${escaparHTML(pago.correo || "")}
                        </span>

                    </td>


                    <td>
                        #${pago.id_prestamo}
                    </td>


                    <td>
                        #${pago.numero_cuota}
                    </td>


                    <td>
                        <strong>
                            ${formatearDinero(pago.monto)}
                        </strong>
                    </td>


                    <td>
                        ${capitalizar(pago.metodo_pago)}
                    </td>


                    <td>
                        ${formatearFechaHora(pago.fecha_pago)}
                    </td>


                    <td>

                        <span
                            class="estado-pago estado-${estado}"
                        >
                            ${capitalizar(estado)}
                        </span>

                    </td>

                </tr>
            `;
    })
    .join("");
}

// ============================================================
// MÉTODOS DE PAGO
// ============================================================

async function cargarMetodosPago() {
  try {
    const respuesta = await fetch("../../backend/routes/pagos.php?metodos=1", {
      credentials: "include",
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      return;
    }

    metodosPago = resultado.data || [];

    metodoPago.innerHTML = `
            <option value="">
                Selecciona...
            </option>
        `;

    metodosPago.forEach((metodo) => {
      const option = document.createElement("option");

      option.value = metodo.id_metodo_pago;

      option.textContent = capitalizar(metodo.nombre);

      metodoPago.appendChild(option);
    });
  } catch (error) {
    console.error(error);
  }
}

// ============================================================
// ABRIR PRÉSTAMO
// ============================================================

tablaPrestamosPagoBody.addEventListener("click", async function (event) {
  const boton = event.target.closest(".btn-ver-pagos");

  if (!boton) {
    return;
  }

  await abrirPrestamo(boton.dataset.id);
});

async function abrirPrestamo(idPrestamo) {
  try {
    const respuesta = await fetch(
      "../../backend/routes/pagos.php?cuotas=1&id_prestamo=" +
        encodeURIComponent(idPrestamo),
      {
        credentials: "include",
      },
    );

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(
        resultado.message || "No fue posible consultar el préstamo.",
      );
    }

    prestamoActual = resultado.data.prestamo;

    mostrarDetallePrestamo(resultado.data);

    modalPagos.classList.add("show");
  } catch (error) {
    console.error(error);
  }
}

// ============================================================
// MOSTRAR DETALLE DEL PRÉSTAMO
// ============================================================

function mostrarDetallePrestamo(data) {
  const prestamo = data.prestamo;

  const resumen = data.resumen;

  const nombreCompleto = [
    prestamo.nombre,
    prestamo.apellido_paterno,
    prestamo.apellido_materno,
  ]
    .filter(Boolean)
    .join(" ");

  detallePrestamoPagoId.textContent = "Préstamo #" + prestamo.id_prestamo;

  detalleClientePago.value = nombreCompleto;

  detalleTipoPago.value = prestamo.tipo_prestamo;

  detalleTotalPrestamo.value = formatearDinero(prestamo.total_pagar);

  detalleMontoCuotaPago.value = formatearDinero(prestamo.monto_cuota);

  resumenCuotasPagadas.textContent = resumen.cuotas_pagadas;

  resumenCuotasPendientes.textContent = resumen.cuotas_pendientes;

  resumenTotalPagado.textContent = formatearDinero(resumen.total_pagado);

  resumenSaldoPendiente.textContent = formatearDinero(resumen.saldo_pendiente);

  mostrarCuotas(data.cuotas);
}

// ============================================================
// MOSTRAR CUOTAS
// ============================================================

function mostrarCuotas(cuotas) {
  if (!cuotas || cuotas.length === 0) {
    tablaCuotasPagoBody.innerHTML = `
            <tr>
                <td colspan="7" class="tabla-vacia">
                    Sin cuotas disponibles.
                </td>
            </tr>
        `;

    return;
  }

  tablaCuotasPagoBody.innerHTML = cuotas
    .map((cuota) => {
      const estado = String(cuota.estatus || "").toLowerCase();

      const puedePagar =
        estado !== "pagada" && Number(cuota.saldo_pendiente) > 0;

      return `
                <tr>

                    <td>
                        ${cuota.numero_cuota}
                    </td>

                    <td>
                        ${formatearFecha(cuota.fecha_vencimiento)}
                    </td>

                    <td>
                        ${formatearDinero(cuota.monto_total)}
                    </td>

                    <td>
                        ${formatearDinero(cuota.monto_pagado)}
                    </td>

                    <td>
                        ${formatearDinero(cuota.saldo_pendiente)}
                    </td>

                    <td>

                        <span
                            class="estado-pago estado-${estado}"
                        >
                            ${capitalizar(estado)}
                        </span>

                    </td>

                    <td>

                        ${
                          puedePagar
                            ? `
                                    <button
                                        type="button"
                                        class="btn-accion btn-pagar-cuota"
                                        data-id="${cuota.id_cuota}"
                                        data-numero="${cuota.numero_cuota}"
                                        data-total="${cuota.monto_total}"
                                        data-saldo="${cuota.saldo_pendiente}"
                                    >
                                        Registrar pago
                                    </button>
                                `
                            : "Pagada"
                        }

                    </td>

                </tr>
            `;
    })
    .join("");
}

// ============================================================
// ABRIR MODAL REGISTRAR PAGO
// ============================================================

tablaCuotasPagoBody.addEventListener("click", function (event) {
  const boton = event.target.closest(".btn-pagar-cuota");

  if (!boton) {
    return;
  }

  cuotaActual = {
    id_cuota: Number(boton.dataset.id),

    numero_cuota: Number(boton.dataset.numero),

    monto_total: Number(boton.dataset.total),

    saldo_pendiente: Number(boton.dataset.saldo),
  };

  detalleCuotaSeleccionada.textContent = "Cuota #" + cuotaActual.numero_cuota;

  pagoTotalCuota.value = formatearDinero(cuotaActual.monto_total);

  pagoSaldoCuota.value = formatearDinero(cuotaActual.saldo_pendiente);

  montoPago.value = cuotaActual.saldo_pendiente.toFixed(2);

  montoPago.max = cuotaActual.saldo_pendiente;

  fechaPago.value = obtenerFechaHoy();

  metodoPago.value = "";

  referenciaPago.value = "";

  comprobantePago.value = "";

  observacionesPago.value = "";

  limpiarMensajePago();

  modalRegistrarPago.classList.add("show");
});

// ============================================================
// REGISTRAR PAGO
// ============================================================

btnRegistrarPago.addEventListener("click", async function () {
  if (!prestamoActual || !cuotaActual || procesandoPago) {
    return;
  }

  const monto = Number(montoPago.value);

  if (!Number.isFinite(monto) || monto <= 0) {
    mostrarMensajePago("Ingresa un monto válido.", "error");

    return;
  }

  if (monto > cuotaActual.saldo_pendiente) {
    mostrarMensajePago("El pago no puede superar el saldo pendiente.", "error");

    return;
  }

  if (!metodoPago.value) {
    mostrarMensajePago("Selecciona un método de pago.", "error");

    return;
  }

  if (!fechaPago.value) {
    mostrarMensajePago("Selecciona la fecha de pago.", "error");

    return;
  }

  const confirmar = window.confirm(
    "¿Registrar un pago de " +
      formatearDinero(monto) +
      " a la cuota #" +
      cuotaActual.numero_cuota +
      "?",
  );

  if (!confirmar) {
    return;
  }

  procesandoPago = true;

  bloquearPago(true);

  try {
    const respuesta = await fetch("../../backend/routes/pagos.php", {
      method: "POST",

      credentials: "include",

      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify({
        accion: "registrar_pago",

        id_prestamo: prestamoActual.id_prestamo,

        id_cuota: cuotaActual.id_cuota,

        id_metodo_pago: metodoPago.value,

        monto: monto,

        fecha_pago: fechaPago.value,

        referencia: referenciaPago.value.trim(),

        comprobante: comprobantePago.value.trim(),

        observaciones: observacionesPago.value.trim(),
      }),
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      mostrarMensajePago(
        resultado.message || "No fue posible registrar el pago.",
        "error",
      );

      return;
    }

    mostrarMensajePago(resultado.message, "exito");

    const idPrestamoActual = prestamoActual.id_prestamo;

    await abrirPrestamo(idPrestamoActual);

    await cargarPrestamos();

    await cargarResumenGeneral();

    await cargarHistorialPagos();

    setTimeout(cerrarRegistrarPago, 800);
  } catch (error) {
    console.error(error);

    mostrarMensajePago("No se pudo conectar con el servidor.", "error");
  } finally {
    procesandoPago = false;

    bloquearPago(false);
  }
});

// ============================================================
// CERRAR MODALES
// ============================================================

cerrarModalPagos.addEventListener("click", cerrarPagos);

cerrarDetallePagos.addEventListener("click", cerrarPagos);

function cerrarPagos() {
  modalPagos.classList.remove("show");

  prestamoActual = null;
}

cerrarModalRegistrarPago.addEventListener("click", cerrarRegistrarPago);

cancelarRegistrarPago.addEventListener("click", cerrarRegistrarPago);

function cerrarRegistrarPago() {
  modalRegistrarPago.classList.remove("show");

  cuotaActual = null;

  limpiarMensajePago();
}

// ============================================================
// BUSCADOR
// ============================================================

buscadorPagos.addEventListener("input", function () {
  const texto = buscadorPagos.value.trim().toLowerCase();

  const filtrados = prestamos.filter((prestamo) => {
    const valores = [
      prestamo.id_prestamo,
      prestamo.nombre,
      prestamo.apellido_paterno,
      prestamo.apellido_materno,
      prestamo.correo,
      prestamo.tipo_prestamo,
    ];

    return valores.some((valor) =>
      String(valor || "")
        .toLowerCase()
        .includes(texto),
    );
  });

  mostrarPrestamos(filtrados);
});

// ============================================================
// BLOQUEAR FORMULARIO DE PAGO
// ============================================================

function bloquearPago(bloquear) {
  btnRegistrarPago.disabled = bloquear;

  montoPago.disabled = bloquear;

  metodoPago.disabled = bloquear;

  fechaPago.disabled = bloquear;

  referenciaPago.disabled = bloquear;

  comprobantePago.disabled = bloquear;

  observacionesPago.disabled = bloquear;

  btnRegistrarPago.textContent = bloquear ? "Registrando..." : "Registrar pago";
}

// ============================================================
// MENSAJES
// ============================================================

function mostrarMensajePago(texto, tipo) {
  mensajeRegistrarPago.textContent = texto;

  mensajeRegistrarPago.className = "mensaje-pagos " + tipo;
}

function limpiarMensajePago() {
  mensajeRegistrarPago.textContent = "";

  mensajeRegistrarPago.className = "mensaje-pagos";
}

// ============================================================
// UTILIDADES
// ============================================================

function formatearDinero(valor) {
  return Number(valor || 0).toLocaleString("es-MX", {
    style: "currency",
    currency: "MXN",
    minimumFractionDigits: 2,
  });
}

function formatearFecha(fecha) {
  if (!fecha) {
    return "Sin registrar";
  }

  const partes = String(fecha).substring(0, 10).split("-");

  return partes.length === 3
    ? partes[2] + "/" + partes[1] + "/" + partes[0]
    : fecha;
}

// ============================================================
// FORMATEAR FECHA + HORA
// ============================================================

function formatearFechaHora(fecha) {
  if (!fecha) {
    return "Sin registrar";
  }

  const texto = String(fecha);

  const partes = texto.split(" ");

  const fechaPartes = partes[0].split("-");

  if (fechaPartes.length !== 3) {
    return texto;
  }

  let resultado = fechaPartes[2] + "/" + fechaPartes[1] + "/" + fechaPartes[0];

  if (partes[1]) {
    resultado += " " + partes[1].substring(0, 5);
  }

  return resultado;
}

function obtenerFechaHoy() {
  const hoy = new Date();

  return (
    hoy.getFullYear() +
    "-" +
    String(hoy.getMonth() + 1).padStart(2, "0") +
    "-" +
    String(hoy.getDate()).padStart(2, "0")
  );
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
// INICIAR
// ============================================================

async function iniciar() {
  const sesion = await comprobarSesion();

  if (!sesion) {
    return;
  }

  await Promise.all([
    cargarMetodosPago(),

    cargarPrestamos(),

    cargarResumenGeneral(),

    cargarHistorialPagos(),
  ]);
}

iniciar();
