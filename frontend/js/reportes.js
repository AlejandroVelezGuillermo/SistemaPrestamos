// ============================================================
// ELEMENTOS GENERALES
// ============================================================

const nombreUsuario = document.getElementById("nombreUsuario");

const rolUsuario = document.getElementById("rolUsuario");

const avatarUsuario = document.getElementById("avatarUsuario");

const usuarioMenu = document.getElementById("usuarioMenu");

const usuarioDropdown = document.getElementById("usuarioDropdown");

const logoutButtonPerfil = document.getElementById("logoutButtonPerfil");

// ============================================================
// RESUMEN GENERAL
// ============================================================

const reporteClientes = document.getElementById("reporteClientes");

const reportePrestamosActivos = document.getElementById(
  "reportePrestamosActivos",
);

const reporteCapitalColocado = document.getElementById(
  "reporteCapitalColocado",
);

const reporteTotalCobrado = document.getElementById("reporteTotalCobrado");

const reporteSaldoPendiente = document.getElementById("reporteSaldoPendiente");

const reporteTotalPagos = document.getElementById("reporteTotalPagos");

// ============================================================
// ESTADOS
// ============================================================

const listaSolicitudesReporte = document.getElementById(
  "listaSolicitudesReporte",
);

const listaPrestamosReporte = document.getElementById("listaPrestamosReporte");

// ============================================================
// RANGO DE FECHAS
// ============================================================

const fechaInicioReporte = document.getElementById("fechaInicioReporte");

const fechaFinReporte = document.getElementById("fechaFinReporte");

const btnGenerarReporte = document.getElementById("btnGenerarReporte");

const mensajeReporte = document.getElementById("mensajeReporte");

const rangoTotalPagos = document.getElementById("rangoTotalPagos");

const rangoTotalCobrado = document.getElementById("rangoTotalCobrado");

const rangoPromedioPago = document.getElementById("rangoPromedioPago");

const tablaPagosReporteBody = document.getElementById("tablaPagosReporteBody");

// ============================================================
// ÚLTIMOS PAGOS
// ============================================================

const tablaUltimosPagosBody = document.getElementById("tablaUltimosPagosBody");

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

    window.location.href = "../index.html";

    return false;
  }
}

// ============================================================
// CARGAR RESUMEN GENERAL
// ============================================================

async function cargarResumenGeneral() {
  try {
    const respuesta = await fetch(
      "../../backend/routes/reportes.php?resumen=1",
      {
        credentials: "include",
      },
    );

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(resultado.message || "No fue posible cargar el resumen.");
    }

    const data = resultado.data;

    reporteClientes.textContent = data.total_clientes;

    reportePrestamosActivos.textContent = data.prestamos_activos;

    reporteCapitalColocado.textContent = formatearDinero(data.capital_colocado);

    reporteTotalCobrado.textContent = formatearDinero(data.total_cobrado);

    reporteSaldoPendiente.textContent = formatearDinero(data.saldo_pendiente);

    reporteTotalPagos.textContent = data.total_pagos;
  } catch (error) {
    console.error(error);
  }
}

// ============================================================
// SOLICITUDES POR ESTADO
// ============================================================

async function cargarSolicitudesPorEstado() {
  listaSolicitudesReporte.innerHTML = "Cargando...";

  try {
    const respuesta = await fetch(
      "../../backend/routes/reportes.php?solicitudes=1",
      {
        credentials: "include",
      },
    );

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(
        resultado.message || "No fue posible cargar las solicitudes.",
      );
    }

    mostrarSolicitudesPorEstado(resultado.data || []);
  } catch (error) {
    console.error(error);

    listaSolicitudesReporte.innerHTML = `
            <div class="tabla-vacia">
                No fue posible cargar las solicitudes.
            </div>
        `;
  }
}

// ============================================================
// MOSTRAR SOLICITUDES
// ============================================================

function mostrarSolicitudesPorEstado(lista) {
  if (lista.length === 0) {
    listaSolicitudesReporte.innerHTML = `
            <div class="tabla-vacia">
                No hay solicitudes registradas.
            </div>
        `;

    return;
  }

  listaSolicitudesReporte.innerHTML = lista
    .map((item) => {
      return `
                <div class="estado-reporte-item">

                    <div class="estado-reporte-info">

                        <strong>
                            ${formatearEstado(item.estado)}
                        </strong>

                        <span>
                            Solicitudes registradas
                        </span>

                    </div>


                    <div class="estado-reporte-valor">

                        <strong>
                            ${Number(item.total || 0)}
                        </strong>

                        <span>
                            solicitud(es)
                        </span>

                    </div>

                </div>
            `;
    })
    .join("");
}

// ============================================================
// PRÉSTAMOS POR ESTADO
// ============================================================

async function cargarPrestamosPorEstado() {
  listaPrestamosReporte.innerHTML = "Cargando...";

  try {
    const respuesta = await fetch(
      "../../backend/routes/reportes.php?prestamos=1",
      {
        credentials: "include",
      },
    );

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(
        resultado.message || "No fue posible cargar los préstamos.",
      );
    }

    mostrarPrestamosPorEstado(resultado.data || []);
  } catch (error) {
    console.error(error);

    listaPrestamosReporte.innerHTML = `
            <div class="tabla-vacia">
                No fue posible cargar los préstamos.
            </div>
        `;
  }
}

// ============================================================
// MOSTRAR PRÉSTAMOS
// ============================================================

function mostrarPrestamosPorEstado(lista) {
  if (lista.length === 0) {
    listaPrestamosReporte.innerHTML = `
            <div class="tabla-vacia">
                No hay préstamos registrados.
            </div>
        `;

    return;
  }

  listaPrestamosReporte.innerHTML = lista
    .map((item) => {
      return `
                <div class="estado-reporte-item">

                    <div class="estado-reporte-info">

                        <strong>
                            ${formatearEstado(item.estado)}
                        </strong>

                        <span>
                            ${formatearDinero(item.monto)}
                        </span>

                    </div>


                    <div class="estado-reporte-valor">

                        <strong>
                            ${Number(item.total || 0)}
                        </strong>

                        <span>
                            préstamo(s)
                        </span>

                    </div>

                </div>
            `;
    })
    .join("");
}

// ============================================================
// ÚLTIMOS PAGOS
// ============================================================

async function cargarUltimosPagos() {
  tablaUltimosPagosBody.innerHTML = `
        <tr>
            <td colspan="7" class="tabla-cargando">
                Cargando pagos...
            </td>
        </tr>
    `;

  try {
    const respuesta = await fetch(
      "../../backend/routes/reportes.php?ultimos_pagos=1&limite=10",
      {
        credentials: "include",
      },
    );

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(resultado.message || "No fue posible cargar los pagos.");
    }

    mostrarPagosEnTabla(tablaUltimosPagosBody, resultado.data || []);
  } catch (error) {
    console.error(error);

    tablaUltimosPagosBody.innerHTML = `
            <tr>
                <td colspan="7" class="tabla-vacia">
                    No fue posible cargar los pagos.
                </td>
            </tr>
        `;
  }
}

// ============================================================
// GENERAR REPORTE POR RANGO
// ============================================================

btnGenerarReporte.addEventListener("click", async function () {
  limpiarMensajeReporte();

  const fechaInicio = fechaInicioReporte.value;

  const fechaFin = fechaFinReporte.value;

  if (!fechaInicio || !fechaFin) {
    mostrarMensajeReporte(
      "Selecciona la fecha inicial y la fecha final.",
      "error",
    );

    return;
  }

  if (fechaInicio > fechaFin) {
    mostrarMensajeReporte(
      "La fecha inicial no puede ser mayor que la fecha final.",
      "error",
    );

    return;
  }

  bloquearFiltro(true);

  try {
    const url =
      "../../backend/routes/reportes.php" +
      "?pagos_rango=1" +
      "&fecha_inicio=" +
      encodeURIComponent(fechaInicio) +
      "&fecha_fin=" +
      encodeURIComponent(fechaFin);

    const respuesta = await fetch(url, {
      credentials: "include",
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(
        resultado.message || "No fue posible generar el reporte.",
      );
    }

    const resumen = resultado.data.resumen;

    rangoTotalPagos.textContent = resumen.total_pagos;

    rangoTotalCobrado.textContent = formatearDinero(resumen.total_cobrado);

    rangoPromedioPago.textContent = formatearDinero(resumen.promedio_pago);

    mostrarPagosEnTabla(tablaPagosReporteBody, resultado.data.pagos || []);

    mostrarMensajeReporte("Reporte generado correctamente.", "exito");
  } catch (error) {
    console.error(error);

    mostrarMensajeReporte(
      error.message || "No fue posible generar el reporte.",
      "error",
    );
  } finally {
    bloquearFiltro(false);
  }
});

// ============================================================
// MOSTRAR PAGOS EN TABLA
// ============================================================

function mostrarPagosEnTabla(cuerpoTabla, pagos) {
  if (pagos.length === 0) {
    cuerpoTabla.innerHTML = `
            <tr>
                <td colspan="7" class="tabla-vacia">
                    No hay pagos en este periodo.
                </td>
            </tr>
        `;

    return;
  }

  cuerpoTabla.innerHTML = pagos
    .map((pago) => {
      const nombreCompleto = [
        pago.nombre,
        pago.apellido_paterno,
        pago.apellido_materno,
      ]
        .filter(Boolean)
        .join(" ");

      return `
                <tr>

                    <td>
                        #${pago.id_pago}
                    </td>

                    <td>

                        <strong>
                            ${escaparHTML(nombreCompleto)}
                        </strong>

                        <br>

                        <small>
                            ${escaparHTML(pago.correo || "")}
                        </small>

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

                </tr>
            `;
    })
    .join("");
}

// ============================================================
// FECHAS INICIALES
// ============================================================

function configurarFechasIniciales() {
  const hoy = new Date();

  const fechaFin = formatearFechaInput(hoy);

  const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);

  const fechaInicio = formatearFechaInput(inicioMes);

  fechaInicioReporte.value = fechaInicio;

  fechaFinReporte.value = fechaFin;
}

// ============================================================
// BLOQUEAR FILTRO
// ============================================================

function bloquearFiltro(bloquear) {
  fechaInicioReporte.disabled = bloquear;

  fechaFinReporte.disabled = bloquear;

  btnGenerarReporte.disabled = bloquear;

  btnGenerarReporte.textContent = bloquear ? "Generando..." : "Generar reporte";
}

// ============================================================
// MENSAJES
// ============================================================

function mostrarMensajeReporte(texto, tipo) {
  mensajeReporte.textContent = texto;

  mensajeReporte.className = "mensaje-reportes " + tipo;
}

function limpiarMensajeReporte() {
  mensajeReporte.textContent = "";

  mensajeReporte.className = "mensaje-reportes";
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

function capitalizar(texto) {
  if (!texto) {
    return "";
  }

  const valor = String(texto);

  return valor.charAt(0).toUpperCase() + valor.slice(1);
}

function formatearEstado(estado) {
  const texto = String(estado || "").replaceAll("_", " ");

  return capitalizar(texto);
}

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

function formatearFechaInput(fecha) {
  const anio = fecha.getFullYear();

  const mes = String(fecha.getMonth() + 1).padStart(2, "0");

  const dia = String(fecha.getDate()).padStart(2, "0");

  return anio + "-" + mes + "-" + dia;
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
  const sesion = await comprobarSesion();

  if (!sesion) {
    return;
  }

  configurarFechasIniciales();

  await Promise.all([
    cargarResumenGeneral(),

    cargarSolicitudesPorEstado(),

    cargarPrestamosPorEstado(),

    cargarUltimosPagos(),
  ]);
}

iniciar();
