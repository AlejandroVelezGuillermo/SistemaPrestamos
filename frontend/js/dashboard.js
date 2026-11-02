const nombreUsuario = document.getElementById("nombreUsuario");

const rolUsuario = document.getElementById("rolUsuario");

const nombreBienvenida = document.getElementById("nombreBienvenida");

const avatarUsuario = document.getElementById("avatarUsuario");

const usuarioMenu = document.getElementById("usuarioMenu");

const usuarioDropdown = document.getElementById("usuarioDropdown");

const logoutButtonPerfil = document.getElementById("logoutButtonPerfil");

const totalClientes = document.getElementById("totalClientes");

const prestamosActivos = document.getElementById("prestamosActivos");

const pagosDia = document.getElementById("pagosDia");

const saldoPendiente = document.getElementById("saldoPendiente");

const actividadReciente = document.getElementById("actividadReciente");

const menuConfiguracion = document.getElementById("menuConfiguracion");

// ============================================================
// INICIAR DASHBOARD
// ============================================================

async function iniciarDashboard() {
  const usuario = await comprobarSesion();

  if (!usuario) {
    return;
  }

  await cargarDashboard();
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

    // ----------------------------------------------------
    // NO HAY SESIÓN
    // ----------------------------------------------------

    if (!datos.sesion || !datos.usuario) {
      window.location.href = "../index.html";

      return null;
    }

    const usuario = datos.usuario;

    const rol = String(usuario.rol || "")
      .trim()
      .toLowerCase();

    // ====================================================
    // PROTEGER DASHBOARD
    //
    // LOS CLIENTES NO DEBEN ENTRAR AQUÍ
    // ====================================================

    if (rol === "cliente") {
      window.location.href = "portal_cliente.html";

      return null;
    }

    // ====================================================
    // SOLO ADMINISTRADOR Y GERENTE
    // ====================================================

    if (rol !== "administrador" && rol !== "gerente") {
      window.location.href = "../index.html";

      return null;
    }

    // ----------------------------------------------------
    // NOMBRE DEL USUARIO
    // ----------------------------------------------------

    const nombreCompleto = [usuario.nombre, usuario.apellidos]
      .filter(Boolean)
      .join(" ");

    nombreUsuario.textContent = nombreCompleto || "Usuario";

    nombreBienvenida.textContent = usuario.nombre || "Usuario";

    rolUsuario.textContent = capitalizar(rol);

    // ----------------------------------------------------
    // AVATAR
    // ----------------------------------------------------

    if (avatarUsuario) {
      avatarUsuario.textContent = String(usuario.nombre || "U")
        .trim()
        .charAt(0)
        .toUpperCase();
    }

    // ====================================================
    // CONFIGURACIÓN SOLO PARA ADMINISTRADOR
    // ====================================================

    if (menuConfiguracion && rol !== "administrador") {
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
// CARGAR DASHBOARD
// ============================================================

async function cargarDashboard() {
  await Promise.all([
    cargarResumen(),
    cargarPagosDia(),
    cargarActividadReciente(),
  ]);
}

// ============================================================
// RESUMEN GENERAL
// ============================================================

async function cargarResumen() {
  try {
    const respuesta = await fetch(
      "../../backend/routes/reportes.php?resumen=1",
      {
        method: "GET",
        credentials: "include",
      },
    );

    const resultado = await respuesta.json();

    if (!resultado.success) {
      console.error("Error cargando resumen:", resultado.message);

      return;
    }

    const datos = resultado.data || {};

    // ====================================================
    // CLIENTES
    // ====================================================

    totalClientes.textContent = obtenerNumero(
      datos.clientes_activos,
      datos.total_clientes,
      datos.clientes,
    );

    // ====================================================
    // PRÉSTAMOS ACTIVOS
    // ====================================================

    prestamosActivos.textContent = obtenerNumero(
      datos.prestamos_activos,
      datos.total_prestamos_activos,
      datos.activos,
    );

    // ====================================================
    // SALDO PENDIENTE
    // ====================================================

    saldoPendiente.textContent = formatoMoneda(
      obtenerValor(datos.saldo_pendiente, datos.total_pendiente, 0),
    );
  } catch (error) {
    console.error("Error cargando resumen:", error);
  }
}

// ============================================================
// PAGOS DEL DÍA
// ============================================================

async function cargarPagosDia() {
  try {
    const hoy = obtenerFechaHoy();

    const url =
      "../../backend/routes/reportes.php" +
      "?pagos_rango=1" +
      `&fecha_inicio=${hoy}` +
      `&fecha_fin=${hoy}`;

    const respuesta = await fetch(url, {
      method: "GET",
      credentials: "include",
    });

    const resultado = await respuesta.json();

    if (!resultado.success) {
      console.error("Error cargando pagos del día:", resultado.message);

      return;
    }

    const datos = resultado.data;

    let total = 0;

    // ====================================================
    // SI EL BACKEND REGRESA UN ARREGLO DE PAGOS
    // ====================================================

    if (Array.isArray(datos)) {
      datos.forEach((pago) => {
        total += Number(pago.monto || 0);
      });
    }

    // ====================================================
    // SI REGRESA UN OBJETO
    // ====================================================
    else if (datos && typeof datos === "object") {
      // Puede venir un total ya calculado

      if (datos.total_pagado !== undefined) {
        total = Number(datos.total_pagado || 0);
      } else if (datos.total !== undefined) {
        total = Number(datos.total || 0);
      } else if (datos.monto_total !== undefined) {
        total = Number(datos.monto_total || 0);
      }

      // O puede venir dentro de un arreglo llamado pagos
      else if (Array.isArray(datos.pagos)) {
        datos.pagos.forEach((pago) => {
          total += Number(pago.monto || 0);
        });
      }
    }

    pagosDia.textContent = formatoMoneda(total);
  } catch (error) {
    console.error("Error cargando pagos del día:", error);
  }
}

// ============================================================
// ACTIVIDAD RECIENTE
// ============================================================

async function cargarActividadReciente() {
  try {
    const respuesta = await fetch(
      "../../backend/routes/reportes.php?ultimos_pagos=1&limite=10",
      {
        method: "GET",
        credentials: "include",
      },
    );

    const resultado = await respuesta.json();

    if (!resultado.success) {
      mostrarActividadVacia();

      return;
    }

    const pagos = extraerPagos(resultado.data);

    if (!pagos.length) {
      mostrarActividadVacia();

      return;
    }

    actividadReciente.innerHTML = "";

    pagos.forEach((pago) => {
      const nombreCliente = obtenerNombreCliente(pago);

      const metodo = pago.metodo_pago || pago.metodo || "Pago";

      const estado = pago.estatus || "confirmado";

      const elemento = document.createElement("div");

      elemento.style.display = "flex";

      elemento.style.justifyContent = "space-between";

      elemento.style.alignItems = "center";

      elemento.style.gap = "20px";

      elemento.style.padding = "15px 18px";

      elemento.style.borderBottom = "1px solid #e2e8f0";

      elemento.innerHTML = `
                    <div
                        style="
                            display:flex;
                            align-items:center;
                            gap:12px;
                        "
                    >

                        <div
                            style="
                                width:38px;
                                height:38px;
                                border-radius:8px;
                                background:#eff6ff;
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                flex-shrink:0;
                            "
                        >
                            💵
                        </div>

                        <div>

                            <strong
                                class="actividad-nombre"
                                style="
                                    display:block;
                                    margin-bottom:4px;
                                    font-size:12px;
                                    font-size:12px;
                                "
                            >
                                ${escaparHTML(nombreCliente)}
                            </strong>

                            <span
                                style="
                                    font-size:11px;
                                    color:#64748b;
                                "
                            >
                                ${escaparHTML(capitalizar(metodo))}
                                ·
                                ${formatoFechaHora(pago.fecha_pago)}
                            </span>

                        </div>

                    </div>


                    <div
                        style="
                            text-align:right;
                        "
                    >

                        <strong
                        class="actividad-monto"
                            style="
                                display:block;
                                font-size:13px;
                                margin-bottom:4px;
                            "
                        >
                            ${formatoMoneda(pago.monto)}
                        </strong>

                        <span
                            style="
                                font-size:10px;
                                color:#16a34a;
                            "
                        >
                            ${escaparHTML(capitalizar(estado))}
                        </span>

                    </div>
                `;

      actividadReciente.appendChild(elemento);
    });
  } catch (error) {
    console.error("Error cargando actividad:", error);

    mostrarActividadVacia();
  }
}

// ============================================================
// CERRAR SESIÓN DESDE EL PERFIL
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
// EXTRAER PAGOS
// ============================================================

function extraerPagos(datos) {
  if (Array.isArray(datos)) {
    return datos;
  }

  if (datos && Array.isArray(datos.pagos)) {
    return datos.pagos;
  }

  if (datos && Array.isArray(datos.data)) {
    return datos.data;
  }

  return [];
}

// ============================================================
// ACTIVIDAD VACÍA
// ============================================================

function mostrarActividadVacia() {
  actividadReciente.innerHTML = `
        <div class="empty-state">

            <div>
                📋
            </div>

            <h4>
                Sin movimientos todavía
            </h4>

            <p>
                Cuando se registren pagos,
                aparecerán aquí.
            </p>

        </div>
    `;
}

// ============================================================
// OBTENER NOMBRE DEL CLIENTE
// ============================================================

function obtenerNombreCliente(pago) {
  if (pago.nombre_cliente) {
    return pago.nombre_cliente;
  }

  const nombre = [pago.nombre, pago.apellido_paterno, pago.apellido_materno]
    .filter(Boolean)
    .join(" ");

  if (nombre) {
    return nombre;
  }

  if (pago.cliente) {
    return pago.cliente;
  }

  return "Cliente";
}



// ============================================================
// FECHA ACTUAL PARA MYSQL
// ============================================================

function obtenerFechaHoy() {
  const fecha = new Date();

  const anio = fecha.getFullYear();

  const mes = String(fecha.getMonth() + 1).padStart(2, "0");

  const dia = String(fecha.getDate()).padStart(2, "0");

  return `${anio}-${mes}-${dia}`;
}

// ============================================================
// MONEDA
// ============================================================

function formatoMoneda(valor) {
  const numero = Number(valor || 0);

  return new Intl.NumberFormat("es-MX", {
    style: "currency",
    currency: "MXN",
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(numero);
}

// ============================================================
// FECHA Y HORA
// ============================================================

function formatoFechaHora(fecha) {
  if (!fecha) {
    return "-";
  }

  try {
    const texto = String(fecha).replace(" ", "T");

    const fechaObjeto = new Date(texto);

    if (Number.isNaN(fechaObjeto.getTime())) {
      return fecha;
    }

    return fechaObjeto.toLocaleString("es-MX", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch (error) {
    return fecha;
  }
}

// ============================================================
// OBTENER PRIMER VALOR EXISTENTE
// ============================================================

function obtenerValor(...valores) {
  for (const valor of valores) {
    if (valor !== undefined && valor !== null && valor !== "") {
      return valor;
    }
  }

  return 0;
}

// ============================================================
// OBTENER NÚMERO
// ============================================================

function obtenerNumero(...valores) {
  const valor = obtenerValor(...valores);

  const numero = Number(valor);

  if (Number.isNaN(numero)) {
    return 0;
  }

  return numero;
}

// ============================================================
// CAPITALIZAR
// ============================================================

function capitalizar(texto) {
  const valor = String(texto || "").trim();

  if (!valor) {
    return "";
  }

  return valor.charAt(0).toUpperCase() + valor.slice(1);
}

// ============================================================
// ESCAPAR HTML
// ============================================================

function escaparHTML(valor) {
  return String(valor ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
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

// ============================================================
// EVITAR QUE EL MENÚ SE CIERRE ANTES DEL CLIC
// ============================================================

if (usuarioDropdown) {
  usuarioDropdown.addEventListener("click", function (event) {
    event.stopPropagation();
  });
}

// ============================================================
// CERRAR MENÚ AL HACER CLIC FUERA
// ============================================================

document.addEventListener("click", function () {
  if (usuarioMenu) {
    usuarioMenu.classList.remove("activo");
  }
});

// ============================================================
// CERRAR SESIÓN DESDE EL PERFIL
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

iniciarDashboard();
