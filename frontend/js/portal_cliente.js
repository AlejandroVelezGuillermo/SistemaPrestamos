document.addEventListener("DOMContentLoaded", () => {
  // =========================================================
  // ELEMENTOS DE PERFIL
  // =========================================================

  const clienteUsuarioMenu = document.getElementById("clienteUsuarioMenu");

  const clienteDropdown = document.getElementById("clienteDropdown");

  const btnMiPerfil = document.getElementById("btnMiPerfil");

  const btnCambiarPassword = document.getElementById("btnCambiarPassword");

  const btnCerrarSesionPerfil = document.getElementById(
    "btnCerrarSesionPerfil",
  );

  const modalPerfil = document.getElementById("modalPerfil");

  const btnCerrarPerfil = document.getElementById("btnCerrarPerfil");

  const perfilAvatar = document.getElementById("perfilAvatar");

  const perfilNombre = document.getElementById("perfilNombre");

  const perfilCorreo = document.getElementById("perfilCorreo");

  const perfilTelefono = document.getElementById("perfilTelefono");

  const modalCambiarPassword =
    document.getElementById("modalCambiarPassword");

  const btnCerrarCambiarPassword =
    document.getElementById("btnCerrarCambiarPassword");

  const formCambiarPassword =
    document.getElementById("formCambiarPassword");

  const passwordActual =
    document.getElementById("passwordActual");

  const passwordNueva =
    document.getElementById("passwordNueva");

  const passwordConfirmar =
    document.getElementById("passwordConfirmar");

  const mensajeCambiarPassword =
    document.getElementById("mensajeCambiarPassword");

  // =========================================================
  // ELEMENTOS GENERALES
  // =========================================================

  const selectorPrestamo = document.getElementById("selectorPrestamo");
  const btnEstadoCuenta = document.getElementById("btnEstadoCuenta");

  const textoPrestamoSeleccionado = document.getElementById(
    "textoPrestamoSeleccionado",
  );

  const contenedorSelectorPrestamo = document.getElementById(
    "contenedorSelectorPrestamo",
  );

  const mensajePortal = document.getElementById("mensajePortal");

  const nombreCliente = document.getElementById("nombreCliente");

  const avatarCliente = document.getElementById("avatarCliente");

  const btnCerrarSesion = document.getElementById("btnCerrarSesion");

  const seccionSinPrestamos = document.getElementById("seccionSinPrestamos");

  // =========================================================
  // ELEMENTOS DE MENSAJES
  // =========================================================

  const contadorMensajes = document.getElementById("contadorMensajes");

  const totalMensajesSinLeer = document.getElementById("totalMensajesSinLeer");

  const listaMensajesCliente = document.getElementById("listaMensajesCliente");

  // =========================================================
  // VARIABLES
  // =========================================================

  let prestamosCliente = [];

  let prestamoActual = null;

  let mensajesCliente = [];

  // =========================================================
  // btnEstadoCuenta
  // =========================================================

  if (btnEstadoCuenta) {
    btnEstadoCuenta.addEventListener("click", function () {
      if (!prestamoActual || !prestamoActual.id_prestamo) {
        alert("No hay un préstamo seleccionado.");
        return;
      }

      const idPrestamo = prestamoActual.id_prestamo;

      window.location.href =
        "../../backend/routes/estado_cuenta_pdf.php?id_prestamo=" +
        encodeURIComponent(idPrestamo);
    });
  }

  // =========================================================
  // INICIAR PORTAL
  // =========================================================

  iniciarPortal();

  async function iniciarPortal() {
    ocultarMensaje();

    await verificarSesion();

    configurarMenu();

    configurarEventos();

    await Promise.all([cargarPrestamos(), cargarMensajesCliente()]);
  }

  // =========================================================
  // VERIFICAR SESIÓN
  // =========================================================

  async function verificarSesion() {
    try {
      const respuesta = await fetch("../../backend/routes/sesion.php", {
        method: "GET",
        credentials: "include",
      });

      const resultado = await respuesta.json();

      if (!resultado.success || !resultado.usuario) {
        window.location.href = "../index.html";

        return;
      }

      const usuario = resultado.usuario;

      window.usuarioClienteActual = usuario;

      const rol = String(usuario.rol || "")
        .trim()
        .toLowerCase();

      // =============================================
      // ESTA PÁGINA ES SOLO PARA CLIENTES
      // =============================================

      if (rol !== "cliente") {
        window.location.href = "dashboard.html";

        return;
      }

      const nombreCompleto = [
        usuario.nombre,
        usuario.apellido_paterno,
        usuario.apellido_materno,
      ]
        .filter(Boolean)
        .join(" ");

      nombreCliente.textContent = nombreCompleto || "Cliente";

      avatarCliente.textContent = obtenerInicial(
        usuario.nombre || nombreCompleto || "C",
      );
    } catch (error) {
      console.error("Error verificando sesión:", error);

      window.location.href = "../index.html";
    }
  }

  // =========================================================
  // CARGAR PRÉSTAMOS
  // =========================================================

  async function cargarPrestamos() {
    try {
      selectorPrestamo.innerHTML = `
                <option value="">
                    Cargando préstamos...
                </option>
            `;

      const respuesta = await fetch(
        "../../backend/routes/portal_cliente.php?prestamos=1",
        {
          method: "GET",
          credentials: "include",
        },
      );

      const resultado = await respuesta.json();

      if (!resultado.success) {
        mostrarError(
          resultado.message || "No fue posible cargar tus préstamos.",
        );

        return;
      }

      prestamosCliente = Array.isArray(resultado.data) ? resultado.data : [];

      if (prestamosCliente.length === 0) {
        mostrarSinPrestamos();

        return;
      }

      ocultarSinPrestamos();

      llenarSelectorPrestamos();

      // =============================================
      // SELECCIONAR PRÉSTAMO PRINCIPAL
      // =============================================

      const prestamoPrincipal = obtenerPrestamoPrincipal(prestamosCliente);

      if (prestamoPrincipal) {
        selectorPrestamo.value = String(prestamoPrincipal.id_prestamo);

        await cargarPrestamoCompleto(prestamoPrincipal.id_prestamo);
      }
    } catch (error) {
      console.error("Error cargando préstamos:", error);

      mostrarError("No fue posible conectarse con el servidor.");
    }
  }

  // =========================================================
  // ELEGIR PRÉSTAMO PRINCIPAL
  // =========================================================

  function obtenerPrestamoPrincipal(prestamos) {
    if (!prestamos.length) {
      return null;
    }

    // Primero buscamos un préstamo activo

    let prestamo = prestamos.find(
      (item) => normalizarTexto(item.estatus) === "activo",
    );

    if (prestamo) {
      return prestamo;
    }

    // Después uno atrasado

    prestamo = prestamos.find(
      (item) => normalizarTexto(item.estatus) === "atrasado",
    );

    if (prestamo) {
      return prestamo;
    }

    // Después pendiente

    prestamo = prestamos.find(
      (item) => normalizarTexto(item.estatus) === "pendiente",
    );

    if (prestamo) {
      return prestamo;
    }

    // Si no, mostramos el más reciente

    return prestamos[0];
  }

  // =========================================================
  // LLENAR SELECTOR
  // =========================================================

  function llenarSelectorPrestamos() {
    selectorPrestamo.innerHTML = "";

    prestamosCliente.forEach((prestamo) => {
      const option = document.createElement("option");

      option.value = prestamo.id_prestamo;

      const estado = capitalizar(
        prestamo.estado_prestamo || prestamo.estatus || "",
      );

      const total = formatoMoneda(prestamo.total_pagar);

      option.textContent = `Préstamo #${prestamo.id_prestamo} - ${estado} - ${total}`;

      selectorPrestamo.appendChild(option);
    });
  }

  // =========================================================
  // CARGAR PRÉSTAMO COMPLETO
  // =========================================================

  async function cargarPrestamoCompleto(idPrestamo) {
    if (!idPrestamo) {
      return;
    }

    mostrarCargandoPrestamo();

    try {
      const respuesta = await fetch(
        `../../backend/routes/portal_cliente.php?completo=1&id_prestamo=${encodeURIComponent(idPrestamo)}`,
        {
          method: "GET",
          credentials: "include",
        },
      );

      const resultado = await respuesta.json();

      if (!resultado.success) {
        mostrarError(resultado.message || "No fue posible cargar el préstamo.");

        return;
      }

      const data = resultado.data || {};

      prestamoActual = data.prestamo || null;

      const cuotas = Array.isArray(data.cuotas) ? data.cuotas : [];

      const pagos = Array.isArray(data.pagos) ? data.pagos : [];

      if (!prestamoActual) {
        mostrarError("No se encontró la información del préstamo.");

        return;
      }

      mostrarResumen(prestamoActual);

      mostrarDetallePrestamo(prestamoActual);

      mostrarCuotas(cuotas);

      mostrarPagos(pagos);

      textoPrestamoSeleccionado.textContent = `Préstamo #${prestamoActual.id_prestamo} · ${capitalizar(
        prestamoActual.estado_prestamo || prestamoActual.estatus,
      )}`;

      ocultarMensaje();
    } catch (error) {
      console.error("Error cargando préstamo:", error);

      mostrarError("No fue posible cargar la información del préstamo.");
    }
  }

  // =========================================================
  // MOSTRAR RESUMEN
  // =========================================================

  function mostrarResumen(prestamo) {
    asignarTexto("resumenTotalPrestamo", formatoMoneda(prestamo.total_pagar));

    asignarTexto("resumenTotalPagado", formatoMoneda(prestamo.total_pagado));

    asignarTexto(
      "resumenSaldoPendiente",
      formatoMoneda(prestamo.saldo_pendiente),
    );

    asignarTexto("resumenCuotasPagadas", prestamo.cuotas_pagadas || 0);

    asignarTexto("resumenCuotasPendientes", prestamo.cuotas_pendientes || 0);

    asignarTexto("resumenCuotasTotales", prestamo.numero_cuotas || 0);

    asignarTexto("resumenFechaFinal", formatoFecha(prestamo.fecha_vencimiento));

    asignarTexto(
      "textoPagosRestantes",
      prestamo.texto_restante ||
      crearTextoPagosRestantes(
        prestamo.cuotas_pendientes,
        prestamo.frecuencia_pago,
      ),
    );

    // =============================================
    // PROGRESO
    // =============================================

    const porcentaje = limitarNumero(
      Number(prestamo.progreso_porcentaje || 0),
      0,
      100,
    );

    asignarTexto("porcentajePagado", `${porcentaje.toFixed(2)}%`);

    const barra = document.getElementById("barraProgresoValor");

    if (barra) {
      barra.style.width = `${porcentaje}%`;
    }

    // =============================================
    // PRÓXIMA CUOTA
    // =============================================

    const proximaCuota = prestamo.proxima_cuota;

    if (proximaCuota) {
      asignarTexto(
        "resumenProximoPago",
        formatoFecha(proximaCuota.fecha_vencimiento),
      );

      asignarTexto("proximaCuotaNumero", `#${proximaCuota.numero_cuota}`);

      asignarTexto(
        "proximaCuotaFecha",
        formatoFecha(proximaCuota.fecha_vencimiento),
      );

      asignarTexto(
        "proximaCuotaMonto",
        formatoMoneda(proximaCuota.saldo_pendiente),
      );
    } else {
      asignarTexto("resumenProximoPago", "Sin pagos pendientes");

      asignarTexto("proximaCuotaNumero", "-");

      asignarTexto("proximaCuotaFecha", "-");

      asignarTexto("proximaCuotaMonto", "$0.00");
    }
  }

  // =========================================================
  // MOSTRAR DETALLE
  // =========================================================

  function mostrarDetallePrestamo(prestamo) {
    asignarTexto("detalleIdPrestamo", `#${prestamo.id_prestamo}`);

    asignarTexto("detalleTipoPrestamo", prestamo.tipo_prestamo || "-");

    asignarTexto(
      "detalleEstadoPrestamo",
      capitalizar(prestamo.estado_prestamo || prestamo.estatus || "-"),
    );

    asignarTexto(
      "detalleMontoSolicitado",
      formatoMoneda(prestamo.monto_solicitado),
    );

    asignarTexto(
      "detalleMontoAprobado",
      formatoMoneda(prestamo.monto_aprobado),
    );

    asignarTexto(
      "detalleTasaInteres",
      `${formatoNumero(prestamo.tasa_interes, 2)}%`,
    );

    asignarTexto("detalleIntereses", formatoMoneda(prestamo.total_intereses));

    asignarTexto("detalleTotalPagar", formatoMoneda(prestamo.total_pagar));

    asignarTexto("detalleTotalPagado", formatoMoneda(prestamo.total_pagado));

    asignarTexto(
      "detalleSaldoPendiente",
      formatoMoneda(prestamo.saldo_pendiente),
    );

    asignarTexto("detallePlazo", `${prestamo.plazo_meses || 0} meses`);

    asignarTexto(
      "detalleFrecuencia",
      capitalizar(prestamo.frecuencia_pago || "-"),
    );

    asignarTexto("detalleNumeroCuotas", prestamo.numero_cuotas || 0);

    asignarTexto("detalleMontoCuota", formatoMoneda(prestamo.monto_cuota));

    asignarTexto(
      "detalleFechaDesembolso",
      formatoFecha(prestamo.fecha_desembolso),
    );

    asignarTexto("detalleInicioPago", formatoFecha(prestamo.fecha_inicio_pago));

    asignarTexto(
      "detalleFechaVencimiento",
      formatoFecha(prestamo.fecha_vencimiento),
    );
  }

  // =========================================================
  // MOSTRAR CUOTAS
  // =========================================================

  function mostrarCuotas(cuotas) {
    const tbody = document.getElementById("tablaCuotasBody");

    if (!tbody) {
      return;
    }

    tbody.innerHTML = "";

    if (!cuotas.length) {
      tbody.innerHTML = `
                <tr>
                    <td
                        colspan="8"
                        class="tabla-mensaje"
                    >                        Este préstamo todavía no tiene cuotas registradas.
                    </td>
                </tr>
            `;

      return;
    }

    cuotas.forEach((cuota) => {
      const tr = document.createElement("tr");

      tr.innerHTML = `
                    <td>
                        #${escaparHTML(cuota.numero_cuota)}
                    </td>

                    <td>
                        ${formatoFecha(cuota.fecha_vencimiento)}
                    </td>

                    <td>
                        ${formatoMoneda(cuota.monto_capital)}
                    </td>

                    <td>
                        ${formatoMoneda(cuota.monto_interes)}
                    </td>

                    <td>
                        <strong>
                            ${formatoMoneda(cuota.monto_total)}
                        </strong>
                    </td>

                    <td>
                        ${formatoMoneda(cuota.monto_pagado)}
                    </td>

                    <td>
                        ${formatoMoneda(cuota.saldo_pendiente)}
                    </td>

                    <td>
                        ${crearBadgeEstado(cuota.estatus)}
                    </td>
                `;

      tbody.appendChild(tr);
    });
  }

  // =========================================================
  // MOSTRAR PAGOS
  // =========================================================

  function mostrarPagos(pagos) {
    const tbody = document.getElementById("tablaPagosBody");

    if (!tbody) {
      return;
    }

    tbody.innerHTML = "";

    if (!pagos.length) {
      tbody.innerHTML = `
                <tr>
                    <td
                        colspan="8"
                        class="tabla-mensaje"
                    >
                        Todavía no tienes pagos registrados.
                    </td>
                </tr>
            `;

      return;
    }

    pagos.forEach((pago) => {
      const tr = document.createElement("tr");

      tr.innerHTML = `
                    <td>
                        #${escaparHTML(pago.id_pago)}
                    </td>

                    <td>
                        ${formatoFechaHora(pago.fecha_pago)}
                    </td>

                    <td>
                        ${pago.numero_cuota
          ? `#${escaparHTML(pago.numero_cuota)}`
          : "-"
        }
                    </td>

                    <td>
                        ${escaparHTML(pago.metodo_pago || "-")}
                    </td>

                    <td>
                        <strong>
                            ${formatoMoneda(pago.monto)}
                        </strong>
                    </td>

                    <td>
                        ${escaparHTML(pago.referencia || "-")}
                    </td>

                    <td>
                        ${escaparHTML(pago.comprobante || "-")}
                    </td>

                    <td>
                        ${crearBadgeEstado(pago.estatus)}
                    </td>
                `;

      tbody.appendChild(tr);
    });
  }

  // =========================================================
  // CARGAR MENSAJES DEL CLIENTE
  // =========================================================

  async function cargarMensajesCliente() {
    if (!listaMensajesCliente) {
      return;
    }

    listaMensajesCliente.innerHTML = `
            <div class="mensaje-vacio">

                <div>
                    📩
                </div>

                <strong>
                    Cargando mensajes...
                </strong>

                <p>
                    Espera un momento.
                </p>

            </div>
        `;

    try {
      const respuesta = await fetch(
        "../../backend/routes/mensajes.php?mis_mensajes=1",
        {
          method: "GET",
          credentials: "include",
        },
      );

      const resultado = await respuesta.json();

      if (!resultado.success) {
        throw new Error(
          resultado.message || "No fue posible cargar tus mensajes.",
        );
      }

      mensajesCliente = Array.isArray(resultado.data) ? resultado.data : [];

      actualizarContadorMensajes();

      mostrarMensajesCliente(mensajesCliente);
    } catch (error) {
      console.error("Error cargando mensajes:", error);

      listaMensajesCliente.innerHTML = `
                <div class="mensaje-vacio">

                    <div>
                        ⚠️
                    </div>

                    <strong>
                        No fue posible cargar tus mensajes
                    </strong>

                    <p>
                        Intenta nuevamente más tarde.
                    </p>

                </div>
            `;
    }
  }

  // =========================================================
  // MOSTRAR MENSAJES DEL CLIENTE
  // =========================================================

  function mostrarMensajesCliente(mensajes) {

    if (!listaMensajesCliente) {
      return;
    }


    if (!mensajes.length) {

      listaMensajesCliente.innerHTML = `
            <div class="mensaje-vacio">

                <div>
                    📭
                </div>

                <strong>
                    No tienes mensajes
                </strong>

                <p>
                    Cuando recibas un aviso,
                    aparecerá aquí.
                </p>

            </div>
        `;

      return;
    }


    listaMensajesCliente.innerHTML = "";


    mensajes.forEach(
      item => {

        const leido =
          Number(item.leido) === 1;


        const respuestasNuevas =
          Number(
            item.respuestas_no_leidas || 0
          );


        const avisoRespuesta =
          respuestasNuevas > 0
            ? `
                        <span class="mensaje-respuesta-nueva-badge">
                            🔴 ${respuestasNuevas}
                            ${respuestasNuevas === 1
              ? "respuesta nueva"
              : "respuestas nuevas"
            }
                        </span>
                    `
            : "";


        const tarjeta =
          document.createElement(
            "article"
          );


        tarjeta.className =
          leido
            ? "mensaje-cliente-card leido"
            : "mensaje-cliente-card no-leido";


        tarjeta.dataset.idMensaje =
          item.id_mensaje;


        tarjeta.innerHTML = `
                <button
                    type="button"
                    class="mensaje-cliente-cabecera"
                >

                    <div class="mensaje-cliente-icono">
                        ${leido
            ? "📨"
            : "📩"
          }
                    </div>


                    <div class="mensaje-cliente-info">

                        <div class="mensaje-cliente-titulo">

                            <strong>
                                ${escaparHTML(
            item.asunto ||
            "Sin asunto"
          )}
                            </strong>

                            ${!leido
            ? `
                                        <span class="mensaje-nuevo-badge">
                                            NUEVO
                                        </span>
                                    `
            : ""
          }

                            ${avisoRespuesta}

                        </div>


                        <p>
                            Enviado por
                            ${escaparHTML(
            item.enviado_por_nombre ||
            "Administración"
          )}
                        </p>


                        <small>
                            ${formatoFechaHora(
            item.fecha_envio
          )}
                        </small>

                    </div>


                    <div class="mensaje-cliente-flecha">
                        ▼
                    </div>

                </button>


                <div class="mensaje-cliente-contenido">

                    <div class="mensaje-cliente-texto">
                        ${escaparHTML(
            item.mensaje || ""
          )}
                    </div>


                    <div
                        class="mensaje-conversacion"
                        data-conversacion="${item.id_mensaje}"
                    ></div>


                    <form
                        class="form-respuesta-mensaje"
                        data-id-mensaje="${item.id_mensaje}"
                    >

                        <textarea
                            class="input-respuesta-mensaje"
                            placeholder="Escribe una respuesta..."
                            maxlength="5000"
                            rows="3"
                            required
                        ></textarea>

                        <button
                            type="submit"
                            class="btn-responder-mensaje"
                        >
                            Enviar respuesta
                        </button>

                        <div
                            class="estado-respuesta-mensaje"
                        ></div>

                    </form>


                    <div class="mensaje-cliente-footer">

                        <span>
                            ${leido
            ? "✓ Leído"
            : "● Sin leer"
          }
                        </span>

                    </div>

                </div>
            `;


        const boton =
          tarjeta.querySelector(
            ".mensaje-cliente-cabecera"
          );


        const formulario =
          tarjeta.querySelector(
            ".form-respuesta-mensaje"
          );


        boton.addEventListener(
          "click",
          async () => {

            const estabaAbierto =
              tarjeta.classList.contains(
                "abierto"
              );


            document
              .querySelectorAll(
                ".mensaje-cliente-card.abierto"
              )
              .forEach(
                elemento => {

                  if (
                    elemento !== tarjeta
                  ) {

                    elemento.classList.remove(
                      "abierto"
                    );
                  }
                }
              );


            tarjeta.classList.toggle(
              "abierto",
              !estabaAbierto
            );


            if (!estabaAbierto) {

              await cargarConversacionMensaje(
                item.id_mensaje,
                tarjeta
              );


              // Marcar como leídas las respuestas nuevas
              // del administrador o gerente.
              if (
                Number(item.respuestas_no_leidas || 0) > 0
              ) {

                await marcarRespuestasLeidasCliente(
                  item.id_mensaje
                );

                item.respuestas_no_leidas = 0;

                const badgeRespuesta =
                  tarjeta.querySelector(
                    ".mensaje-respuesta-nueva-badge"
                  );

                if (badgeRespuesta) {
                  badgeRespuesta.remove();
                }
              }


              if (
                Number(item.leido) === 0
              ) {

                await marcarMensajeComoLeido(
                  item.id_mensaje
                );
              }
            }
          }
        );


        formulario.addEventListener(
          "submit",
          async event => {

            event.preventDefault();


            const textarea =
              formulario.querySelector(
                ".input-respuesta-mensaje"
              );


            const estado =
              formulario.querySelector(
                ".estado-respuesta-mensaje"
              );


            const botonEnviar =
              formulario.querySelector(
                ".btn-responder-mensaje"
              );


            const texto =
              textarea.value.trim();


            if (texto === "") {

              estado.textContent =
                "Escribe una respuesta.";

              return;
            }


            botonEnviar.disabled = true;

            botonEnviar.textContent =
              "Enviando...";

            estado.textContent = "";


            try {

              const respuesta =
                await fetch(
                  "../../backend/routes/mensajes.php",
                  {
                    method: "POST",

                    credentials:
                      "include",

                    headers: {
                      "Content-Type":
                        "application/json"
                    },

                    body:
                      JSON.stringify({
                        accion:
                          "responder",

                        id_mensaje:
                          item.id_mensaje,

                        mensaje:
                          texto
                      })
                  }
                );


              const resultado =
                await respuesta.json();


              if (!resultado.success) {

                throw new Error(
                  resultado.message ||
                  "No se pudo enviar la respuesta."
                );
              }


              textarea.value = "";

              estado.textContent =
                "Respuesta enviada correctamente.";


              await cargarConversacionMensaje(
                item.id_mensaje,
                tarjeta
              );


            } catch (error) {

              console.error(
                "Error enviando respuesta:",
                error
              );


              estado.textContent =
                error.message ||
                "No se pudo enviar la respuesta.";

            } finally {

              botonEnviar.disabled = false;

              botonEnviar.textContent =
                "Enviar respuesta";
            }
          }
        );


        listaMensajesCliente.appendChild(
          tarjeta
        );
      }
    );
  }


  // =========================================================
  // CARGAR CONVERSACIÓN DEL MENSAJE
  // =========================================================

  async function cargarConversacionMensaje(
    idMensaje,
    tarjeta
  ) {

    const contenedor =
      tarjeta.querySelector(
        ".mensaje-conversacion"
      );


    if (!contenedor) {
      return;
    }


    contenedor.innerHTML = `
        <div class="mensaje-conversacion-cargando">
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


      const resultado =
        await respuesta.json();


      if (!resultado.success) {

        throw new Error(
          resultado.message ||
          "No se pudo cargar la conversación."
        );
      }


      const respuestas =
        Array.isArray(
          resultado.data?.respuestas
        )
          ? resultado.data.respuestas
          : [];


      if (!respuestas.length) {

        contenedor.innerHTML = `
                <div class="mensaje-conversacion-vacia">
                    Todavía no hay respuestas.
                </div>
            `;

        return;
      }


      contenedor.innerHTML =
        respuestas
          .map(
            respuestaItem => {

              const esCliente =
                String(
                  respuestaItem.rol || ""
                ).toLowerCase() ===
                "cliente";


              return `
                            <div
                                class="respuesta-conversacion ${esCliente
                  ? "respuesta-cliente"
                  : "respuesta-administracion"
                }"
                            >

                                <div class="respuesta-cabecera">

                                    <strong>
                                        ${escaparHTML(
                  respuestaItem.nombre_usuario ||
                  (
                    esCliente
                      ? "Tú"
                      : "Administración"
                  )
                )}
                                    </strong>

                                    <span>
                                        ${escaparHTML(
                  respuestaItem.rol ||
                  ""
                )}
                                    </span>

                                </div>


                                <div class="respuesta-texto">
                                    ${escaparHTML(
                  respuestaItem.mensaje ||
                  ""
                )}
                                </div>


                                <small>
                                    ${formatoFechaHora(
                  respuestaItem.fecha_envio
                )}
                                </small>

                            </div>
                        `;
            }
          )
          .join("");


    } catch (error) {

      console.error(
        "Error cargando conversación:",
        error
      );


      contenedor.innerHTML = `
            <div class="mensaje-conversacion-error">
                ${escaparHTML(
        error.message ||
        "No se pudo cargar la conversación."
      )
        }
            </div>
        `;
    }
  }

// =========================================================
// MARCAR RESPUESTAS COMO LEÍDAS
// CLIENTE
// =========================================================

async function marcarRespuestasLeidasCliente(idMensaje) {

  try {

    const respuesta = await fetch(
      "../../backend/routes/mensajes.php",
      {
        method: "POST",

        credentials: "include",

        headers: {
          "Content-Type": "application/json"
        },

        body: JSON.stringify({
          accion: "marcar_respuestas_leidas",
          id_mensaje: idMensaje
        })
      }
    );


    const resultado = await respuesta.json();


    if (!resultado.success) {

      console.error(
        resultado.message ||
        "No fue posible marcar las respuestas como leídas."
      );

      return false;
    }


    return true;


  } catch (error) {

    console.error(
      "Error marcando respuestas como leídas:",
      error
    );

    return false;
  }
}

  // =========================================================
  // MARCAR MENSAJE COMO LEÍDO
  // =========================================================

  async function marcarMensajeComoLeido(idMensaje) {
    try {
      const respuesta = await fetch("../../backend/routes/mensajes.php", {
        method: "POST",

        credentials: "include",

        headers: {
          "Content-Type": "application/json",
        },

        body: JSON.stringify({
          accion: "marcar_leido",

          id_mensaje: idMensaje,
        }),
      });

      const resultado = await respuesta.json();

      if (!resultado.success) {
        console.error(
          resultado.message || "No fue posible marcar el mensaje como leído.",
        );

        return;
      }

      const mensajeEncontrado = mensajesCliente.find(
        (item) => Number(item.id_mensaje) === Number(idMensaje),
      );

      if (mensajeEncontrado) {
        mensajeEncontrado.leido = 1;

        mensajeEncontrado.fecha_lectura = new Date().toISOString();
      }

      actualizarContadorMensajes();

      mostrarMensajesCliente(mensajesCliente);

      const tarjetaActualizada = document.querySelector(
        `.mensaje-cliente-card[data-id-mensaje="${idMensaje}"]`,
      );

      if (tarjetaActualizada) {
        tarjetaActualizada.classList.add("abierto");
      }
    } catch (error) {
      console.error("Error marcando mensaje como leído:", error);
    }
  }

  // =========================================================
  // CONTADOR DE MENSAJES
  // =========================================================

  function actualizarContadorMensajes() {
    const total = mensajesCliente.filter(
      (item) => Number(item.leido) === 0,
    ).length;

    if (totalMensajesSinLeer) {
      totalMensajesSinLeer.textContent = total;
    }

    if (!contadorMensajes) {
      return;
    }

    contadorMensajes.textContent = total;

    if (total > 0) {
      contadorMensajes.style.display = "inline-flex";
    } else {
      contadorMensajes.style.display = "none";
    }
  }

  // =========================================================
  // MENÚ
  // =========================================================

  function configurarMenu() {
    const botones = document.querySelectorAll(".menu-item[data-seccion]");

    botones.forEach((boton) => {
      boton.addEventListener("click", async () => {
        const seccion = boton.dataset.seccion;

        if (seccion === "mensajes") {
          await cargarMensajesCliente();
        }

        cambiarSeccion(seccion);
      });
    });
  }

  function cambiarSeccion(seccion) {
    if (seccion === "mensajes" && seccionSinPrestamos) {
      seccionSinPrestamos.classList.remove("show");
    }

    document
      .querySelectorAll(".menu-item[data-seccion]")
      .forEach((boton) => boton.classList.remove("active"));

    document
      .querySelectorAll(".seccion-cliente")
      .forEach((elemento) => elemento.classList.remove("active"));

    const botonActivo = document.querySelector(
      `.menu-item[data-seccion="${seccion}"]`,
    );

    const seccionActiva = document.getElementById(
      `seccion${capitalizarPrimera(seccion)}`,
    );

    if (botonActivo) {
      botonActivo.classList.add("active");
    }

    if (seccionActiva) {
      seccionActiva.classList.add("active");
    }

    // =============================================
    // MOSTRAR / OCULTAR SELECTOR DE PRÉSTAMO
    // =============================================

    if (contenedorSelectorPrestamo) {
      if (seccion === "mensajes") {
        contenedorSelectorPrestamo.style.display = "none";
      } else if (prestamosCliente.length > 0) {
        contenedorSelectorPrestamo.style.display = "flex";
      }
    }

    actualizarTitulo(seccion);
  }

  // =========================================================
  // TÍTULOS
  // =========================================================

  function actualizarTitulo(seccion) {
    const titulo = document.getElementById("tituloPagina");

    const subtitulo = document.getElementById("subtituloPagina");

    const datos = {
      resumen: {
        titulo: "Mi resumen",

        subtitulo: "Consulta el estado general de tu préstamo.",
      },

      prestamo: {
        titulo: "Mi préstamo",

        subtitulo: "Consulta los detalles de tu financiamiento.",
      },

      cuotas: {
        titulo: "Mis cuotas",

        subtitulo: "Consulta tu calendario completo de pagos.",
      },

      pagos: {
        titulo: "Mis pagos",

        subtitulo: "Consulta tu historial de pagos registrados.",
      },

      mensajes: {
        titulo: "Mis mensajes",

        subtitulo: "Consulta los avisos y comunicaciones que has recibido.",
      },
    };

    const informacion = datos[seccion] || datos.resumen;

    if (titulo) {
      titulo.textContent = informacion.titulo;
    }

    if (subtitulo) {
      subtitulo.textContent = informacion.subtitulo;
    }
  }

  // =========================================================
  // EVENTOS
  // =========================================================

  function configurarEventos() {

    // CAMBIAR PRÉSTAMO
    if (selectorPrestamo) {

      selectorPrestamo.addEventListener("change", async () => {

        const idPrestamo = selectorPrestamo.value;

        if (!idPrestamo) {
          return;
        }

        await cargarPrestamoCompleto(idPrestamo);

      });

    }


    // CERRAR SESIÓN - BOTÓN LATERAL
    if (btnCerrarSesion) {

      btnCerrarSesion.addEventListener(
        "click",
        cerrarSesion
      );

    }


    // CERRAR SESIÓN - MENÚ DEL PERFIL
    if (btnCerrarSesionPerfil) {

      btnCerrarSesionPerfil.addEventListener(
        "click",
        cerrarSesion
      );

    }

  }

  // =========================================================
  // CERRAR SESIÓN
  // =========================================================

  async function cerrarSesion() {
    try {
      await fetch("../../backend/routes/logout.php", {
        method: "POST",
        credentials: "include",
      });
    } catch (error) {
      console.error("Error cerrando sesión:", error);
    }

    window.location.href = "../index.html";
  }

  // =========================================================
  // SIN PRÉSTAMOS
  // =========================================================

  function mostrarSinPrestamos() {
    if (contenedorSelectorPrestamo) {
      contenedorSelectorPrestamo.style.display = "none";
    }

    // Ocultamos solo las secciones relacionadas con préstamos
    const seccionesPrestamo = [
      "seccionResumen",
      "seccionPrestamo",
      "seccionCuotas",
      "seccionPagos",
    ];

    seccionesPrestamo.forEach((id) => {
      const elemento = document.getElementById(id);

      if (elemento) {
        elemento.classList.remove("active");

        elemento.style.display = "none";
      }
    });

    // Mostramos el aviso de que no tiene préstamos
    if (seccionSinPrestamos) {
      seccionSinPrestamos.classList.add("show");
    }
  }

  function ocultarSinPrestamos() {
    if (contenedorSelectorPrestamo) {
      contenedorSelectorPrestamo.style.display = "flex";
    }

    if (seccionSinPrestamos) {
      seccionSinPrestamos.classList.remove("show");
    }

    const seccionesPrestamo = [
      "seccionResumen",
      "seccionPrestamo",
      "seccionCuotas",
      "seccionPagos",
    ];

    seccionesPrestamo.forEach((id) => {
      const elemento = document.getElementById(id);

      if (elemento) {
        elemento.style.display = "";
      }
    });

    // Mensajes siempre debe poder mostrarse
    const seccionMensajes = document.getElementById("seccionMensajes");

    if (seccionMensajes) {
      seccionMensajes.style.display = "";
    }
  }

  // =========================================================
  // CARGANDO
  // =========================================================

  function mostrarCargandoPrestamo() {
    textoPrestamoSeleccionado.textContent = "Cargando información...";

    asignarTexto("resumenTotalPrestamo", "Cargando...");

    asignarTexto("resumenTotalPagado", "Cargando...");

    asignarTexto("resumenSaldoPendiente", "Cargando...");

    asignarTexto("resumenProximoPago", "Cargando...");
  }

  // =========================================================
  // MENSAJES DE ERROR
  // =========================================================

  function mostrarError(mensaje) {
    if (!mensajePortal) {
      return;
    }

    mensajePortal.className = "mensaje-portal error";

    mensajePortal.textContent = mensaje;
  }

  function ocultarMensaje() {
    if (!mensajePortal) {
      return;
    }

    mensajePortal.className = "mensaje-portal";

    mensajePortal.textContent = "";
  }

  // =========================================================
  // BADGES
  // =========================================================

  function crearBadgeEstado(estado) {
    const estadoNormalizado = normalizarTexto(estado);

    const texto = capitalizar(estado || "-");

    return `
            <span
                class="estado-badge estado-${escaparHTML(estadoNormalizado)}"
            >
                ${escaparHTML(texto)}
            </span>
        `;
  }

  // =========================================================
  // TEXTO RESTANTE
  // =========================================================

  function crearTextoPagosRestantes(pendientes, frecuencia) {
    const cantidad = Number(pendientes || 0);

    if (cantidad <= 0) {
      return "Préstamo liquidado";
    }

    const tipo = normalizarTexto(frecuencia);

    if (tipo === "semanal") {
      return cantidad === 1
        ? "1 pago semanal restante"
        : `${cantidad} pagos semanales restantes`;
    }

    if (tipo === "quincenal") {
      return cantidad === 1
        ? "1 pago quincenal restante"
        : `${cantidad} pagos quincenales restantes`;
    }

    if (tipo === "mensual") {
      return cantidad === 1
        ? "1 pago mensual restante"
        : `${cantidad} pagos mensuales restantes`;
    }

    return cantidad === 1 ? "1 cuota restante" : `${cantidad} cuotas restantes`;
  }
  // =========================================================
  // FORMATO MONEDA
  // =========================================================

  function formatoMoneda(valor) {
    const numero = Number(valor || 0);

    return new Intl.NumberFormat("es-MX", {
      style: "currency",
      currency: "MXN",
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(numero);
  }

  // =========================================================
  // FORMATO NÚMERO
  // =========================================================

  function formatoNumero(valor, decimales = 2) {
    const numero = Number(valor || 0);

    return new Intl.NumberFormat("es-MX", {
      minimumFractionDigits: decimales,

      maximumFractionDigits: decimales,
    }).format(numero);
  }

  // =========================================================
  // FECHA
  // =========================================================

  function formatoFecha(fecha) {
    if (!fecha || fecha === "0000-00-00") {
      return "-";
    }

    try {
      const soloFecha = String(fecha).split(" ")[0].split("T")[0];

      const partes = soloFecha.split("-");

      if (partes.length !== 3) {
        return fecha;
      }

      const anio = Number(partes[0]);

      const mes = Number(partes[1]) - 1;

      const dia = Number(partes[2]);

      const fechaLocal = new Date(anio, mes, dia);

      return fechaLocal.toLocaleDateString("es-MX", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
      });
    } catch (error) {
      return fecha;
    }
  }

  // =========================================================
  // FECHA Y HORA
  // =========================================================

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

  // =========================================================
  // UTILIDADES
  // =========================================================

  function asignarTexto(id, valor) {
    const elemento = document.getElementById(id);

    if (elemento) {
      elemento.textContent = valor;
    }
  }

  function obtenerInicial(texto) {
    const valor = String(texto || "C").trim();

    if (!valor) {
      return "C";
    }

    return valor.charAt(0).toUpperCase();
  }

  function capitalizar(texto) {
    const valor = String(texto || "").trim();

    if (!valor) {
      return "";
    }

    return valor.charAt(0).toUpperCase() + valor.slice(1);
  }

  function capitalizarPrimera(texto) {
    const valor = String(texto || "");

    return valor.charAt(0).toUpperCase() + valor.slice(1);
  }

  function normalizarTexto(texto) {
    return String(texto || "")
      .trim()
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/\s+/g, "-");
  }

  function limitarNumero(numero, minimo, maximo) {
    if (Number.isNaN(numero)) {
      return minimo;
    }

    return Math.min(Math.max(numero, minimo), maximo);
  }

  function escaparHTML(valor) {
    const texto = String(valor ?? "");

    return texto
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  // ============================================================
  // MENÚ DE PERFIL
  // ============================================================

  if (clienteUsuarioMenu) {
    clienteUsuarioMenu.addEventListener("click", function (event) {
      event.stopPropagation();

      clienteUsuarioMenu.classList.toggle("activo");
    });
  }

  // CERRAR AL HACER CLIC FUERA
  document.addEventListener("click", function () {
    if (clienteUsuarioMenu) {
      clienteUsuarioMenu.classList.remove("activo");
    }
  });

  // EVITAR QUE LOS BOTONES CIERREN ANTES DE EJECUTARSE
  if (clienteDropdown) {
    clienteDropdown.addEventListener("click", function (event) {
      event.stopPropagation();
    });
  }

  // ============================================================
  // ABRIR MI PERFIL
  // ============================================================

  if (btnMiPerfil) {

    btnMiPerfil.addEventListener("click", function () {

      const usuario = window.usuarioClienteActual;

      if (!usuario) {
        return;
      }

      const nombreCompleto = [
        usuario.nombre,
        usuario.apellido_paterno,
        usuario.apellido_materno
      ]
        .filter(Boolean)
        .join(" ");

      perfilNombre.textContent =
        nombreCompleto || "Cliente";

      perfilCorreo.textContent =
        usuario.correo || "Sin registrar";

      perfilTelefono.textContent =
        usuario.telefono || "Sin registrar";

      perfilAvatar.textContent =
        usuario.nombre
          ? usuario.nombre.charAt(0).toUpperCase()
          : "C";

      modalPerfil.classList.add("activo");

      clienteUsuarioMenu.classList.remove("activo");
    });

  }


  // ============================================================
  // CERRAR MI PERFIL CON X
  // ============================================================

  if (btnCerrarPerfil) {

    btnCerrarPerfil.addEventListener("click", function () {

      modalPerfil.classList.remove("activo");

    });

  }


  // ============================================================
  // CERRAR HACIENDO CLIC FUERA
  // ============================================================

  if (modalPerfil) {

    modalPerfil.addEventListener("click", function (event) {

      if (event.target === modalPerfil) {

        modalPerfil.classList.remove("activo");

      }

    });

  }


  // ============================================================
  // CERRAR CON ESC
  // ============================================================

  document.addEventListener("keydown", function (event) {

    if (
      event.key === "Escape" &&
      modalPerfil
    ) {

      modalPerfil.classList.remove("activo");

    }

  });

  // ============================================================
  // ABRIR CAMBIAR CONTRASEÑA
  // ============================================================

  if (btnCambiarPassword) {

    btnCambiarPassword.addEventListener("click", function () {

      if (formCambiarPassword) {
        formCambiarPassword.reset();
      }

      if (mensajeCambiarPassword) {
        mensajeCambiarPassword.className = "";
        mensajeCambiarPassword.textContent = "";
      }

      clienteUsuarioMenu.classList.remove("activo");

      modalCambiarPassword.classList.add("activo");

      setTimeout(() => {
        passwordActual?.focus();
      }, 100);
    });

  }


  // ============================================================
  // CERRAR CAMBIAR CONTRASEÑA
  // ============================================================

  if (btnCerrarCambiarPassword) {

    btnCerrarCambiarPassword.addEventListener("click", function () {

      modalCambiarPassword.classList.remove("activo");

    });

  }


  // CERRAR HACIENDO CLIC EN EL FONDO
  if (modalCambiarPassword) {

    modalCambiarPassword.addEventListener("click", function (event) {

      if (event.target === modalCambiarPassword) {

        modalCambiarPassword.classList.remove("activo");

      }

    });

  }


  // CERRAR CON ESC
  document.addEventListener("keydown", function (event) {

    if (
      event.key === "Escape" &&
      modalCambiarPassword
    ) {

      modalCambiarPassword.classList.remove("activo");

    }

  });

  // ============================================================
  // GUARDAR NUEVA CONTRASEÑA
  // ============================================================

  if (formCambiarPassword) {

    formCambiarPassword.addEventListener("submit", async function (event) {

      event.preventDefault();

      // Limpiar mensaje anterior
      if (mensajeCambiarPassword) {
        mensajeCambiarPassword.className = "";
        mensajeCambiarPassword.textContent = "";
      }

      const actual = passwordActual.value;
      const nueva = passwordNueva.value;
      const confirmar = passwordConfirmar.value;


      // ========================================================
      // VALIDACIONES
      // ========================================================

      if (!actual || !nueva || !confirmar) {

        mostrarMensajePassword(
          "Todos los campos son obligatorios.",
          "error"
        );

        return;
      }


      if (nueva.length < 6) {

        mostrarMensajePassword(
          "La nueva contraseña debe tener al menos 6 caracteres.",
          "error"
        );

        return;
      }


      if (nueva !== confirmar) {

        mostrarMensajePassword(
          "Las nuevas contraseñas no coinciden.",
          "error"
        );

        return;
      }


      if (actual === nueva) {

        mostrarMensajePassword(
          "La nueva contraseña debe ser diferente a la actual.",
          "error"
        );

        return;
      }


      // ========================================================
      // DESACTIVAR BOTÓN MIENTRAS GUARDA
      // ========================================================

      const botonGuardar =
        formCambiarPassword.querySelector(".btn-guardar-password");

      const textoOriginal =
        botonGuardar ? botonGuardar.textContent : "";

      if (botonGuardar) {
        botonGuardar.disabled = true;
        botonGuardar.textContent = "Guardando...";
      }


      try {

        // ====================================================
        // ENVIAR AL BACKEND
        // ====================================================

        const respuesta = await fetch(
          "../../backend/routes/cambiar_password_cliente.php",
          {
            method: "POST",

            credentials: "include",

            headers: {
              "Content-Type": "application/json"
            },

            body: JSON.stringify({

              password_actual: actual,

              password_nueva: nueva,

              password_confirmar: confirmar

            })
          }
        );


        const resultado = await respuesta.json();


        // ====================================================
        // ERROR DEL SERVIDOR
        // ====================================================

        if (!resultado.success) {

          mostrarMensajePassword(
            resultado.message ||
            "No fue posible cambiar la contraseña.",
            "error"
          );

          return;
        }


        // ====================================================
        // CONTRASEÑA ACTUALIZADA
        // ====================================================

        mostrarMensajePassword(
          resultado.message ||
          "Contraseña actualizada correctamente.",
          "exito"
        );


        formCambiarPassword.reset();


        // Cerrar modal después de mostrar el mensaje
        setTimeout(function () {

          modalCambiarPassword.classList.remove("activo");

        }, 1500);


      } catch (error) {

        console.error(
          "Error cambiando contraseña:",
          error
        );

        mostrarMensajePassword(
          "No fue posible conectarse con el servidor.",
          "error"
        );


      } finally {

        // ====================================================
        // REACTIVAR BOTÓN
        // ====================================================

        if (botonGuardar) {

          botonGuardar.disabled = false;

          botonGuardar.textContent =
            textoOriginal || "Guardar nueva contraseña";

        }

      }

    });

  }


  // ============================================================
  // MOSTRAR MENSAJE DE CAMBIO DE CONTRASEÑA
  // ============================================================

  function mostrarMensajePassword(mensaje, tipo) {

    if (!mensajeCambiarPassword) {
      return;
    }

    mensajeCambiarPassword.className = tipo;

    mensajeCambiarPassword.textContent = mensaje;
  }
});
