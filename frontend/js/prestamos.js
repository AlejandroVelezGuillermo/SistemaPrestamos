// ============================================================
// ELEMENTOS GENERALES
// ============================================================

const nombreUsuario = document.getElementById("nombreUsuario");

const rolUsuario = document.getElementById("rolUsuario");

const avatarUsuario = document.getElementById("avatarUsuario");

const usuarioMenu = document.getElementById("usuarioMenu");

const usuarioDropdown = document.getElementById("usuarioDropdown");

const logoutButtonPerfil = document.getElementById("logoutButtonPerfil");

const tablaPrestamosBody = document.getElementById("tablaPrestamosBody");

const buscadorPrestamos = document.getElementById("buscadorPrestamos");

const mensajePrestamos = document.getElementById("mensajePrestamos");

// ============================================================
// RESUMEN
// ============================================================

const totalPrestamos = document.getElementById("totalPrestamos");

const prestamosPendientes = document.getElementById("prestamosPendientes");

const prestamosActivos = document.getElementById("prestamosActivos");

const capitalColocado = document.getElementById("capitalColocado");

// ============================================================
// MODAL
// ============================================================

const modalVerPrestamo = document.getElementById("modalVerPrestamo");

const cerrarModalPrestamo = document.getElementById("cerrarModalPrestamo");

const cerrarDetallePrestamo = document.getElementById("cerrarDetallePrestamo");

// ============================================================
// DETALLES DEL PRÉSTAMO
// ============================================================

const detallePrestamoId = document.getElementById("detallePrestamoId");

const detalleCliente = document.getElementById("detalleCliente");

const detalleCorreo = document.getElementById("detalleCorreo");

const detalleTelefono = document.getElementById("detalleTelefono");

const detalleTipoPrestamo = document.getElementById("detalleTipoPrestamo");

const detalleMontoAprobado = document.getElementById("detalleMontoAprobado");

const detalleTasaInteres = document.getElementById("detalleTasaInteres");

const detalleTotalIntereses = document.getElementById("detalleTotalIntereses");

const detalleTotalPagar = document.getElementById("detalleTotalPagar");

const detallePlazo = document.getElementById("detallePlazo");

const detalleFrecuencia = document.getElementById("detalleFrecuencia");

const detalleNumeroCuotas = document.getElementById("detalleNumeroCuotas");

const detalleMontoCuota = document.getElementById("detalleMontoCuota");

const detalleAval = document.getElementById("detalleAval");

const detalleGarantia = document.getElementById("detalleGarantia");

const detalleEstadoPrestamo = document.getElementById("detalleEstadoPrestamo");

const detalleFechaAprobacion = document.getElementById(
  "detalleFechaAprobacion",
);

const detalleFechaDesembolso = document.getElementById(
  "detalleFechaDesembolso",
);

const detalleFechaInicioPago = document.getElementById(
  "detalleFechaInicioPago",
);

const detalleFechaVencimiento = document.getElementById(
  "detalleFechaVencimiento",
);

const detalleAprobadoPor = document.getElementById("detalleAprobadoPor");

const detalleObservaciones = document.getElementById("detalleObservaciones");

// ============================================================
// DESEMBOLSO
// ============================================================

const seccionDesembolso = document.getElementById("seccionDesembolso");

const metodoPagoDesembolso = document.getElementById("metodoPagoDesembolso");

const montoDesembolso = document.getElementById("montoDesembolso");

const fechaDesembolso = document.getElementById("fechaDesembolso");

const referenciaDesembolso = document.getElementById("referenciaDesembolso");

const comprobanteDesembolso = document.getElementById("comprobanteDesembolso");

const observacionesDesembolso = document.getElementById(
  "observacionesDesembolso",
);

const mensajeDesembolso = document.getElementById("mensajeDesembolso");

const btnDesembolsar = document.getElementById("btnDesembolsar");

// ============================================================
// DESEMBOLSO REALIZADO
// ============================================================

const seccionDesembolsoRealizado = document.getElementById(
  "seccionDesembolsoRealizado",
);

const detalleMetodoDesembolso = document.getElementById(
  "detalleMetodoDesembolso",
);

const detalleMontoDesembolso = document.getElementById(
  "detalleMontoDesembolso",
);

const detalleReferenciaDesembolso = document.getElementById(
  "detalleReferenciaDesembolso",
);

const detalleRealizadoPor = document.getElementById("detalleRealizadoPor");

const detalleObservacionesDesembolso = document.getElementById(
  "detalleObservacionesDesembolso",
);

// ============================================================
// CUOTAS
// ============================================================

const seccionCuotas = document.getElementById("seccionCuotas");

const tablaCuotasBody = document.getElementById("tablaCuotasBody");

// ============================================================
// VARIABLES
// ============================================================

let prestamos = [];

let metodosPago = [];

let prestamoActual = null;

let procesandoDesembolso = false;

// ============================================================
// SESIÓN
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
// CARGAR PRÉSTAMOS
// ============================================================

async function cargarPrestamos() {
  tablaPrestamosBody.innerHTML = `
        <tr>
            <td colspan="9" class="tabla-cargando">
                Cargando préstamos...
            </td>
        </tr>
    `;

  try {
    const respuesta = await fetch("../../backend/routes/prestamos.php", {
      method: "GET",
      credentials: "include",
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(
        resultado.message || "No fue posible cargar los préstamos.",
      );
    }

    prestamos = resultado.data || [];

    actualizarResumen();

    mostrarPrestamos(prestamos);
  } catch (error) {
    console.error(error);

    tablaPrestamosBody.innerHTML = `
            <tr>
                <td colspan="9" class="tabla-vacia">
                    No fue posible cargar los préstamos.
                </td>
            </tr>
        `;

    mostrarMensajePrestamos(
      error.message || "No fue posible cargar los préstamos.",
      "error",
    );
  }
}

// ============================================================
// MOSTRAR PRÉSTAMOS
// ============================================================

function mostrarPrestamos(lista) {
  if (lista.length === 0) {
    tablaPrestamosBody.innerHTML = `
            <tr>
                <td colspan="9" class="tabla-vacia">
                    No hay préstamos registrados.
                </td>
            </tr>
        `;

    return;
  }

  tablaPrestamosBody.innerHTML = lista
    .map((prestamo) => {
      const nombreCompleto = [
        prestamo.nombre,
        prestamo.apellido_paterno,
        prestamo.apellido_materno,
      ]
        .filter(Boolean)
        .join(" ");

      const estado = normalizar(prestamo.estado_prestamo);

      return `
                <tr>

                    <td>

                        <span class="prestamo-id">
                            #${prestamo.id_prestamo}
                        </span>

                        <span class="cliente-correo">
                            Solicitud #${prestamo.id_solicitud}
                        </span>

                    </td>


                    <td>

                        <span class="cliente-nombre">
                            ${escaparHTML(nombreCompleto)}
                        </span>

                        <span class="cliente-correo">
                            ${escaparHTML(prestamo.correo || "")}
                        </span>

                    </td>


                    <td>
                        ${formatearDinero(prestamo.monto_aprobado)}
                    </td>


                    <td>
                        ${formatearPorcentaje(prestamo.tasa_interes)}
                    </td>


                    <td>
                        ${formatearDinero(prestamo.total_pagar)}
                    </td>


                    <td>
                        ${prestamo.numero_cuotas}
                        ×
                        ${formatearDinero(prestamo.monto_cuota)}
                    </td>


                    <td>

                        <span
                            class="estado-prestamo ${obtenerClaseEstado(estado)}"
                        >
                            ${formatearEstado(estado)}
                        </span>

                    </td>


                    <td>
                        ${formatearFechaHora(prestamo.fecha_aprobacion)}
                    </td>


                    <td>

                        <button
                            type="button"
                            class="btn-accion btn-ver-prestamo"
                            data-id="${prestamo.id_prestamo}"
                            title="Ver préstamo"
                        >
                            Ver
                        </button>
                        
                        <button
                            type="button"
                            class="btn-accion btn-estado-cuenta"
                            data-id="${prestamo.id_prestamo}"
                            title="Descargar estado de cuenta"
                        >
                            📄
                        </button>

                    </td>

                </tr>
            `;
    })
    .join("");
}

// ============================================================
// RESUMEN
// ============================================================

function actualizarResumen() {
  totalPrestamos.textContent = prestamos.length;

  prestamosPendientes.textContent = prestamos.filter(
    (prestamo) => normalizar(prestamo.estado_prestamo) === "pendiente",
  ).length;

  prestamosActivos.textContent = prestamos.filter(
    (prestamo) => normalizar(prestamo.estado_prestamo) === "activo",
  ).length;

  const capital = prestamos
    .filter((prestamo) => normalizar(prestamo.estado_prestamo) !== "cancelado")
    .reduce(
      (total, prestamo) => total + Number(prestamo.monto_aprobado || 0),
      0,
    );

  capitalColocado.textContent = formatearDinero(capital);
}

// ============================================================
// CARGAR MÉTODOS DE PAGO
// ============================================================

async function cargarMetodosPago() {
  try {
    const respuesta = await fetch(
      "../../backend/routes/prestamos.php?metodos=1",
      {
        method: "GET",
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

    llenarMetodosPago();
  } catch (error) {
    console.error(error);
  }
}

// ============================================================
// LLENAR MÉTODOS
// ============================================================

function llenarMetodosPago() {
  metodoPagoDesembolso.innerHTML = `
        <option value="">
            Selecciona...
        </option>
    `;

  metodosPago.forEach((metodo) => {
    const option = document.createElement("option");

    option.value = metodo.id_metodo_pago;

    option.textContent = capitalizar(metodo.nombre);

    metodoPagoDesembolso.appendChild(option);
  });
}

// ============================================================
// CLICK EN VER
// ============================================================

tablaPrestamosBody.addEventListener("click", async function (event) {
  const boton = event.target.closest(".btn-ver-prestamo");

  if (!boton) {
    return;
  }

  await verPrestamo(boton.dataset.id);
});

// ============================================================
// DESCARGAR ESTADO DE CUENTA
// ============================================================

tablaPrestamosBody.addEventListener("click", function (event) {
  const boton = event.target.closest(".btn-estado-cuenta");

  if (!boton) {
    return;
  }

  const idPrestamo = boton.dataset.id;

  window.open(
    "../../backend/routes/estado_cuenta_admin_pdf.php?id_prestamo=" +
      encodeURIComponent(idPrestamo),
    "_blank",
  );
});

// ============================================================
// OBTENER PRÉSTAMO
// ============================================================

async function obtenerPrestamo(idPrestamo) {
  const respuesta = await fetch(
    "../../backend/routes/prestamos.php?id=" + encodeURIComponent(idPrestamo),
    {
      method: "GET",
      credentials: "include",
    },
  );

  const resultado = await respuesta.json();

  if (!respuesta.ok || !resultado.success) {
    throw new Error(
      resultado.message || "No fue posible consultar el préstamo.",
    );
  }

  return resultado.data;
}

// ============================================================
// VER PRÉSTAMO
// ============================================================

async function verPrestamo(idPrestamo) {
  limpiarMensajeDesembolso();

  try {
    const prestamo = await obtenerPrestamo(idPrestamo);

    prestamoActual = prestamo;

    mostrarDetallePrestamo(prestamo);

    await configurarDesembolso(prestamo);

    modalVerPrestamo.classList.add("show");
  } catch (error) {
    console.error(error);

    mostrarMensajePrestamos(
      error.message || "No se pudo consultar el préstamo.",
      "error",
    );
  }
}

// ============================================================
// MOSTRAR DETALLE
// ============================================================

function mostrarDetallePrestamo(prestamo) {
  const nombreCompleto = [
    prestamo.nombre,
    prestamo.apellido_paterno,
    prestamo.apellido_materno,
  ]
    .filter(Boolean)
    .join(" ");

  detallePrestamoId.textContent =
    "Préstamo #" +
    prestamo.id_prestamo +
    " · Solicitud #" +
    prestamo.id_solicitud;

  detalleCliente.value = nombreCompleto;

  detalleCorreo.value = prestamo.correo || "Sin registrar";

  detalleTelefono.value = prestamo.telefono || "Sin registrar";

  detalleTipoPrestamo.value = prestamo.tipo_prestamo || "Sin registrar";

  detalleMontoAprobado.value = formatearDinero(prestamo.monto_aprobado);

  detalleTasaInteres.value = formatearPorcentaje(prestamo.tasa_interes);

  detalleTotalIntereses.value = formatearDinero(prestamo.total_intereses);

  detalleTotalPagar.value = formatearDinero(prestamo.total_pagar);

  detallePlazo.value = prestamo.plazo_meses + " meses";

  detalleFrecuencia.value = capitalizar(prestamo.frecuencia_pago);

  detalleNumeroCuotas.value = prestamo.numero_cuotas;

  detalleMontoCuota.value = formatearDinero(prestamo.monto_cuota);

  detalleAval.value =
    Number(prestamo.requiere_aval) === 1
      ? "Sí requiere aval"
      : "No requiere aval";

  detalleGarantia.value =
    Number(prestamo.requiere_garantia) === 1
      ? "Sí requiere garantía"
      : "No requiere garantía";

  detalleEstadoPrestamo.value = formatearEstado(prestamo.estado_prestamo);

  detalleFechaAprobacion.value = formatearFechaHora(prestamo.fecha_aprobacion);

  detalleFechaDesembolso.value = prestamo.fecha_desembolso
    ? formatearFechaHora(prestamo.fecha_desembolso)
    : "Pendiente";

  detalleFechaInicioPago.value = prestamo.fecha_inicio_pago
    ? formatearFecha(prestamo.fecha_inicio_pago)
    : "Pendiente";

  detalleFechaVencimiento.value = prestamo.fecha_vencimiento
    ? formatearFecha(prestamo.fecha_vencimiento)
    : "Pendiente";

  detalleAprobadoPor.value = prestamo.nombre_aprobador || "Sin registrar";

  detalleObservaciones.value = prestamo.observaciones || "Sin observaciones";
}

// ============================================================
// CONFIGURAR DESEMBOLSO
// ============================================================

async function configurarDesembolso(prestamo) {
  seccionDesembolso.style.display = "none";

  seccionDesembolsoRealizado.style.display = "none";

  seccionCuotas.style.display = "none";

  tablaCuotasBody.innerHTML = `
        <tr>
            <td colspan="8" class="tabla-vacia">
                Sin cuotas generadas.
            </td>
        </tr>
    `;

  const estado = normalizar(prestamo.estado_prestamo);

  // ========================================================
  // PENDIENTE = MOSTRAR FORMULARIO
  // ========================================================

  if (estado === "pendiente") {
    seccionDesembolso.style.display = "block";

    limpiarFormularioDesembolso();

    montoDesembolso.value = formatearDinero(prestamo.monto_aprobado);

    fechaDesembolso.value = obtenerFechaHoy();

    return;
  }

  // ========================================================
  // ACTIVO / ATRASADO / LIQUIDADO
  // MOSTRAR DESEMBOLSO Y CUOTAS
  // ========================================================

  if (estado === "activo" || estado === "atrasado" || estado === "liquidado") {
    await Promise.all([
      cargarDesembolsoRealizado(prestamo.id_prestamo),

      cargarCuotas(prestamo.id_prestamo),
    ]);
  }
}

// ============================================================
// LIMPIAR FORMULARIO DE DESEMBOLSO
// ============================================================

function limpiarFormularioDesembolso() {
  metodoPagoDesembolso.value = "";

  montoDesembolso.value = "";

  fechaDesembolso.value = "";

  referenciaDesembolso.value = "";

  comprobanteDesembolso.value = "";

  observacionesDesembolso.value = "";

  limpiarMensajeDesembolso();
}

// ============================================================
// DESEMBOLSAR
// ============================================================

btnDesembolsar.addEventListener("click", async function () {
  if (!prestamoActual || procesandoDesembolso) {
    return;
  }

  const idMetodo = metodoPagoDesembolso.value;

  const fecha = fechaDesembolso.value;

  if (!idMetodo) {
    mostrarMensajeDesembolso("Selecciona un método de pago.", "error");

    metodoPagoDesembolso.focus();

    return;
  }

  if (!fecha) {
    mostrarMensajeDesembolso("Selecciona la fecha de desembolso.", "error");

    fechaDesembolso.focus();

    return;
  }

  const confirmar = window.confirm(
    "¿Deseas confirmar el desembolso de " +
      formatearDinero(prestamoActual.monto_aprobado) +
      "?\n\n" +
      "Esta acción activará el préstamo y generará todas las cuotas.",
  );

  if (!confirmar) {
    return;
  }

  procesandoDesembolso = true;

  bloquearDesembolso(true);

  limpiarMensajeDesembolso();

  const datos = {
    accion: "desembolsar",

    id_prestamo: prestamoActual.id_prestamo,

    id_metodo_pago: idMetodo,

    fecha_desembolso: fecha,

    referencia: referenciaDesembolso.value.trim(),

    comprobante: comprobanteDesembolso.value.trim(),

    observaciones: observacionesDesembolso.value.trim(),
  };

  try {
    const respuesta = await fetch("../../backend/routes/prestamos.php", {
      method: "POST",

      credentials: "include",

      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify(datos),
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      mostrarMensajeDesembolso(
        resultado.message || "No fue posible realizar el desembolso.",
        "error",
      );

      return;
    }

    mostrarMensajeDesembolso(resultado.message, "exito");

    // -------------------------------------------------
    // ACTUALIZAR LISTA
    // -------------------------------------------------

    await cargarPrestamos();

    // -------------------------------------------------
    // RECARGAR PRÉSTAMO
    // -------------------------------------------------

    const prestamoActualizado = await obtenerPrestamo(
      prestamoActual.id_prestamo,
    );

    prestamoActual = prestamoActualizado;

    mostrarDetallePrestamo(prestamoActualizado);

    await configurarDesembolso(prestamoActualizado);
  } catch (error) {
    console.error(error);

    mostrarMensajeDesembolso("No se pudo conectar con el servidor.", "error");
  } finally {
    procesandoDesembolso = false;

    bloquearDesembolso(false);
  }
});

// ============================================================
// BLOQUEAR DESEMBOLSO
// ============================================================

function bloquearDesembolso(bloquear) {
  btnDesembolsar.disabled = bloquear;

  metodoPagoDesembolso.disabled = bloquear;

  fechaDesembolso.disabled = bloquear;

  referenciaDesembolso.disabled = bloquear;

  comprobanteDesembolso.disabled = bloquear;

  observacionesDesembolso.disabled = bloquear;

  btnDesembolsar.textContent = bloquear
    ? "Procesando desembolso..."
    : "Confirmar desembolso";
}

// ============================================================
// CARGAR DESEMBOLSO REALIZADO
// ============================================================

async function cargarDesembolsoRealizado(idPrestamo) {
  try {
    const respuesta = await fetch(
      "../../backend/routes/prestamos.php?desembolso=1&id=" +
        encodeURIComponent(idPrestamo),
      {
        method: "GET",
        credentials: "include",
      },
    );

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(
        resultado.message || "No fue posible consultar el desembolso.",
      );
    }

    const desembolso = resultado.data;

    if (!desembolso) {
      return;
    }

    seccionDesembolsoRealizado.style.display = "block";

    detalleMetodoDesembolso.value = capitalizar(desembolso.metodo_pago);

    detalleMontoDesembolso.value = formatearDinero(desembolso.monto);

    detalleReferenciaDesembolso.value =
      desembolso.referencia || "Sin referencia";

    detalleRealizadoPor.value =
      desembolso.nombre_realizado_por || "Sin registrar";

    detalleObservacionesDesembolso.value =
      desembolso.observaciones || "Sin observaciones";
  } catch (error) {
    console.error(error);
  }
}

// ============================================================
// CARGAR CUOTAS
// ============================================================

async function cargarCuotas(idPrestamo) {
  try {
    const respuesta = await fetch(
      "../../backend/routes/prestamos.php?cuotas=1&id=" +
        encodeURIComponent(idPrestamo),
      {
        method: "GET",
        credentials: "include",
      },
    );

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(
        resultado.message || "No fue posible consultar las cuotas.",
      );
    }

    const cuotas = resultado.data || [];

    if (cuotas.length === 0) {
      return;
    }

    seccionCuotas.style.display = "block";

    mostrarCuotas(cuotas);
  } catch (error) {
    console.error(error);
  }
}

// ============================================================
// MOSTRAR CUOTAS
// ============================================================

function mostrarCuotas(cuotas) {
  tablaCuotasBody.innerHTML = cuotas
    .map((cuota) => {
      const estado = normalizar(cuota.estatus);

      return `
                    <tr>

                        <td>
                            ${cuota.numero_cuota}
                        </td>

                        <td>
                            ${formatearFecha(cuota.fecha_vencimiento)}
                        </td>

                        <td>
                            ${formatearDinero(cuota.monto_capital)}
                        </td>

                        <td>
                            ${formatearDinero(cuota.monto_interes)}
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
                                class="estado-prestamo ${obtenerClaseEstadoCuota(estado)}"
                            >
                                ${formatearEstadoCuota(estado)}
                            </span>

                        </td>

                    </tr>
                `;
    })
    .join("");
}

// ============================================================
// BUSCADOR
// ============================================================

buscadorPrestamos.addEventListener("input", function () {
  const texto = buscadorPrestamos.value.trim().toLowerCase();

  if (!texto) {
    mostrarPrestamos(prestamos);

    return;
  }

  const filtrados = prestamos.filter((prestamo) => {
    const valores = [
      prestamo.id_prestamo,
      prestamo.id_solicitud,
      prestamo.nombre,
      prestamo.apellido_paterno,
      prestamo.apellido_materno,
      prestamo.correo,
      prestamo.telefono,
      prestamo.tipo_prestamo,
      prestamo.estado_prestamo,
      prestamo.frecuencia_pago,
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
// CERRAR MODAL
// ============================================================

cerrarModalPrestamo.addEventListener("click", cerrarPrestamo);

cerrarDetallePrestamo.addEventListener("click", cerrarPrestamo);

function cerrarPrestamo() {
  modalVerPrestamo.classList.remove("show");

  prestamoActual = null;

  limpiarFormularioDesembolso();
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

};

// ============================================================
// UTILIDADES
// ============================================================

function normalizar(valor) {
  return String(valor || "")
    .trim()
    .toLowerCase();
}

function formatearEstado(estado) {
  const valor = normalizar(estado);

  const nombres = {
    pendiente: "Pendiente",

    activo: "Activo",

    atrasado: "Atrasado",

    liquidado: "Liquidado",

    cancelado: "Cancelado",
  };

  return nombres[valor] || capitalizar(valor);
}

function obtenerClaseEstado(estado) {
  const valor = normalizar(estado);

  if (valor === "activo") {
    return "estado-activo";
  }

  if (valor === "atrasado") {
    return "estado-atrasado";
  }

  if (valor === "liquidado") {
    return "estado-liquidado";
  }

  if (valor === "cancelado") {
    return "estado-cancelado";
  }

  return "estado-pendiente";
}

function formatearEstadoCuota(estado) {
  const valor = normalizar(estado);

  const nombres = {
    pendiente: "Pendiente",

    parcial: "Parcial",

    pagada: "Pagada",

    vencida: "Vencida",

    cancelada: "Cancelada",
  };

  return nombres[valor] || capitalizar(valor);
}

function obtenerClaseEstadoCuota(estado) {
  const valor = normalizar(estado);

  if (valor === "pagada") {
    return "estado-activo";
  }

  if (valor === "parcial") {
    return "estado-pendiente";
  }

  if (valor === "vencida") {
    return "estado-atrasado";
  }

  if (valor === "cancelada") {
    return "estado-cancelado";
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

function formatearPorcentaje(valor) {
  const numero = Number(valor || 0);

  return (
    numero.toLocaleString("es-MX", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }) + "%"
  );
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

function formatearFecha(fecha) {
  if (!fecha) {
    return "Sin registrar";
  }

  const partes = String(fecha).substring(0, 10).split("-");

  if (partes.length !== 3) {
    return fecha;
  }

  return partes[2] + "/" + partes[1] + "/" + partes[0];
}

function obtenerFechaHoy() {
  const ahora = new Date();

  const year = ahora.getFullYear();

  const month = String(ahora.getMonth() + 1).padStart(2, "0");

  const day = String(ahora.getDate()).padStart(2, "0");

  return year + "-" + month + "-" + day;
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

function mostrarMensajePrestamos(texto, tipo) {
  mensajePrestamos.textContent = texto;

  mensajePrestamos.className = "mensaje-prestamos " + tipo;
}

// ============================================================
// MENSAJES DESEMBOLSO
// ============================================================

function mostrarMensajeDesembolso(texto, tipo) {
  mensajeDesembolso.textContent = texto;

  mensajeDesembolso.className = "mensaje-prestamos " + tipo;
}

function limpiarMensajeDesembolso() {
  mensajeDesembolso.textContent = "";

  mensajeDesembolso.className = "mensaje-prestamos";
}

// ============================================================
// INICIAR
// ============================================================

async function iniciar() {
  const sesionValida = await comprobarSesion();

  if (!sesionValida) {
    return;
  }

  await Promise.all([cargarMetodosPago(), cargarPrestamos()]);
}

iniciar();
