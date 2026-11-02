// ============================================================
// ELEMENTOS GENERALES
// ============================================================

const nombreUsuario = document.getElementById("nombreUsuario");

const rolUsuario = document.getElementById("rolUsuario");

const avatarUsuario = document.getElementById("avatarUsuario");

const usuarioMenu = document.getElementById("usuarioMenu");

const usuarioDropdown = document.getElementById("usuarioDropdown");

const logoutButtonPerfil = document.getElementById("logoutButtonPerfil");

const btnNuevoUsuario = document.getElementById("btnNuevoUsuario");

const buscadorUsuarios = document.getElementById("buscadorUsuarios");

const tablaUsuariosBody = document.getElementById("tablaUsuariosBody");

const mensajeUsuarios = document.getElementById("mensajeUsuarios");

// ============================================================
// RESUMEN
// ============================================================

const totalUsuarios = document.getElementById("totalUsuarios");

const totalAdministradores = document.getElementById("totalAdministradores");

const totalGerentes = document.getElementById("totalGerentes");

const totalClientes = document.getElementById("totalClientes");

const totalActivos = document.getElementById("totalActivos");

// ============================================================
// MODAL CREAR / EDITAR
// ============================================================

const modalUsuario = document.getElementById("modalUsuario");

const tituloModalUsuario = document.getElementById("tituloModalUsuario");

const subtituloModalUsuario = document.getElementById("subtituloModalUsuario");

const cerrarModalUsuario = document.getElementById("cerrarModalUsuario");

const cancelarUsuario = document.getElementById("cancelarUsuario");

const guardarUsuario = document.getElementById("guardarUsuario");

const grupoPasswordUsuario = document.getElementById("grupoPasswordUsuario");

const mensajeFormularioUsuario = document.getElementById(
  "mensajeFormularioUsuario",
);

// ============================================================
// CAMPOS CREAR / EDITAR
// ============================================================

const usuarioNombre = document.getElementById("usuarioNombre");

const usuarioApellidoPaterno = document.getElementById(
  "usuarioApellidoPaterno",
);

const usuarioApellidoMaterno = document.getElementById(
  "usuarioApellidoMaterno",
);

const usuarioTelefono = document.getElementById("usuarioTelefono");

const usuarioFechaNacimiento = document.getElementById(
  "usuarioFechaNacimiento",
);

const usuarioCurp = document.getElementById("usuarioCurp");

const usuarioRfc = document.getElementById("usuarioRfc");

const usuarioDireccion = document.getElementById("usuarioDireccion");

const usuarioCiudad = document.getElementById("usuarioCiudad");

const usuarioEstado = document.getElementById("usuarioEstado");

const usuarioCodigoPostal = document.getElementById("usuarioCodigoPostal");

const usuarioCorreo = document.getElementById("usuarioCorreo");

const usuarioPassword = document.getElementById("usuarioPassword");

const usuarioRol = document.getElementById("usuarioRol");

const usuarioEstatus = document.getElementById("usuarioEstatus");

// ============================================================
// MODAL VER
// ============================================================

const modalVerUsuario = document.getElementById("modalVerUsuario");

const cerrarModalVerUsuario = document.getElementById("cerrarModalVerUsuario");

const cerrarDetalleUsuario = document.getElementById("cerrarDetalleUsuario");

const detalleUsuarioId = document.getElementById("detalleUsuarioId");

const detalleUsuarioNombre = document.getElementById("detalleUsuarioNombre");

const detalleUsuarioCorreo = document.getElementById("detalleUsuarioCorreo");

const detalleUsuarioTelefono = document.getElementById(
  "detalleUsuarioTelefono",
);

const detalleUsuarioNacimiento = document.getElementById(
  "detalleUsuarioNacimiento",
);

const detalleUsuarioCurp = document.getElementById("detalleUsuarioCurp");

const detalleUsuarioRfc = document.getElementById("detalleUsuarioRfc");

const detalleUsuarioDireccion = document.getElementById(
  "detalleUsuarioDireccion",
);

const detalleUsuarioCiudad = document.getElementById("detalleUsuarioCiudad");

const detalleUsuarioEstado = document.getElementById("detalleUsuarioEstado");

const detalleUsuarioCodigoPostal = document.getElementById(
  "detalleUsuarioCodigoPostal",
);

const detalleUsuarioRol = document.getElementById("detalleUsuarioRol");

const detalleUsuarioEstatus = document.getElementById("detalleUsuarioEstatus");

const detalleUsuarioUltimoAcceso = document.getElementById(
  "detalleUsuarioUltimoAcceso",
);

const detalleUsuarioCreacion = document.getElementById(
  "detalleUsuarioCreacion",
);

// ============================================================
// MODAL CONTRASEÑA
// ============================================================

const modalPasswordUsuario = document.getElementById("modalPasswordUsuario");

const passwordUsuarioNombre = document.getElementById("passwordUsuarioNombre");

const cerrarModalPassword = document.getElementById("cerrarModalPassword");

const cancelarPasswordUsuario = document.getElementById(
  "cancelarPasswordUsuario",
);

const guardarPasswordUsuario = document.getElementById(
  "guardarPasswordUsuario",
);

const nuevaPasswordUsuario = document.getElementById("nuevaPasswordUsuario");

const confirmarPasswordUsuario = document.getElementById(
  "confirmarPasswordUsuario",
);

const mensajePasswordUsuario = document.getElementById(
  "mensajePasswordUsuario",
);

// ============================================================
// VARIABLES
// ============================================================

let usuarios = [];

let usuarioActual = null;

let modoFormulario = "crear";

let procesandoUsuario = false;

let procesandoPassword = false;

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
// CARGAR USUARIOS
// ============================================================

async function cargarUsuarios() {
  tablaUsuariosBody.innerHTML = `
        <tr>
            <td colspan="8" class="tabla-cargando">
                Cargando usuarios...
            </td>
        </tr>
    `;

  try {
    const respuesta = await fetch("../../backend/routes/usuarios.php", {
      credentials: "include",
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      throw new Error(
        resultado.message || "No fue posible cargar los usuarios.",
      );
    }

    usuarios = resultado.data || [];

    mostrarUsuarios(usuarios);

    actualizarResumen();
  } catch (error) {
    console.error(error);

    tablaUsuariosBody.innerHTML = `
            <tr>
                <td colspan="8" class="tabla-vacia">
                    No fue posible cargar los usuarios.
                </td>
            </tr>
        `;

    mostrarMensajeGeneral(
      error.message || "No fue posible cargar los usuarios.",
      "error",
    );
  }
}

// ============================================================
// MOSTRAR USUARIOS
// ============================================================

function mostrarUsuarios(lista) {
  if (lista.length === 0) {
    tablaUsuariosBody.innerHTML = `
            <tr>
                <td colspan="8" class="tabla-vacia">
                    No hay usuarios registrados.
                </td>
            </tr>
        `;

    return;
  }

  tablaUsuariosBody.innerHTML = lista
    .map((usuario) => {
      const nombreCompleto = [
        usuario.nombre,
        usuario.apellido_paterno,
        usuario.apellido_materno,
      ]
        .filter(Boolean)
        .join(" ");

      const rol = normalizar(usuario.rol);

      const estatus = normalizar(usuario.estatus);

      return `
                <tr>

                    <td>
                        #${usuario.id_usuario}
                    </td>


                    <td>

                        <span class="usuario-nombre">
                            ${escaparHTML(nombreCompleto)}
                        </span>

                        <span class="usuario-secundario">
                            ${escaparHTML(usuario.ciudad || "")}
                        </span>

                    </td>


                    <td>
                        ${escaparHTML(usuario.correo || "")}
                    </td>


                    <td>
                        ${escaparHTML(usuario.telefono || "Sin registrar")}
                    </td>


                    <td>

                        <span
                            class="rol-usuario rol-${rol}"
                        >
                            ${capitalizar(rol)}
                        </span>

                    </td>


                    <td>

                        <span
                            class="estatus-usuario estatus-${estatus}"
                        >
                            ${capitalizar(estatus)}
                        </span>

                    </td>


                    <td>
                        ${formatearFechaHora(usuario.fecha_creacion)}
                    </td>


                    <td>

                        <button
                            type="button"
                            class="btn-accion btn-ver"
                            data-id="${usuario.id_usuario}"
                        >
                            Ver
                        </button>


                        <button
                            type="button"
                            class="btn-accion btn-editar"
                            data-id="${usuario.id_usuario}"
                        >
                            Editar
                        </button>


                        <button
                            type="button"
                            class="btn-accion btn-password"
                            data-id="${usuario.id_usuario}"
                        >
                            Contraseña
                        </button>


                        ${
                          estatus === "activo"
                            ? `
                                    <button
                                        type="button"
                                        class="btn-accion btn-bloquear"
                                        data-id="${usuario.id_usuario}"
                                        data-estatus="bloqueado"
                                    >
                                        Bloquear
                                    </button>
                                `
                            : `
                                    <button
                                        type="button"
                                        class="btn-accion btn-activar"
                                        data-id="${usuario.id_usuario}"
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
// RESUMEN
// ============================================================

function actualizarResumen() {
  totalUsuarios.textContent = usuarios.length;

  totalAdministradores.textContent = usuarios.filter(
    (usuario) => normalizar(usuario.rol) === "administrador",
  ).length;

  totalGerentes.textContent = usuarios.filter(
    (usuario) => normalizar(usuario.rol) === "gerente",
  ).length;

  totalClientes.textContent = usuarios.filter(
    (usuario) => normalizar(usuario.rol) === "cliente",
  ).length;

  totalActivos.textContent = usuarios.filter(
    (usuario) => normalizar(usuario.estatus) === "activo",
  ).length;
}

// ============================================================
// BUSCADOR
// ============================================================

buscadorUsuarios.addEventListener("input", function () {
  const texto = buscadorUsuarios.value.trim().toLowerCase();

  if (!texto) {
    mostrarUsuarios(usuarios);

    return;
  }

  const filtrados = usuarios.filter((usuario) => {
    const valores = [
      usuario.id_usuario,

      usuario.nombre,

      usuario.apellido_paterno,

      usuario.apellido_materno,

      usuario.correo,

      usuario.telefono,

      usuario.curp,

      usuario.rfc,

      usuario.ciudad,

      usuario.estado,

      usuario.rol,

      usuario.estatus,
    ];

    return valores.some((valor) =>
      String(valor || "")
        .toLowerCase()
        .includes(texto),
    );
  });

  mostrarUsuarios(filtrados);
});

// ============================================================
// OBTENER USUARIO DESDE API
// ============================================================

async function obtenerUsuario(idUsuario) {
  const respuesta = await fetch(
    "../../backend/routes/usuarios.php?id=" + encodeURIComponent(idUsuario),
    {
      credentials: "include",
    },
  );

  const resultado = await respuesta.json();

  if (!respuesta.ok || !resultado.success) {
    throw new Error(
      resultado.message || "No fue posible consultar el usuario.",
    );
  }

  return resultado.data;
}

// ============================================================
// EVENTOS DE LA TABLA
// ============================================================

tablaUsuariosBody.addEventListener("click", async function (event) {
  const botonVer = event.target.closest(".btn-ver");

  if (botonVer) {
    await verUsuario(botonVer.dataset.id);

    return;
  }

  const botonEditar = event.target.closest(".btn-editar");

  if (botonEditar) {
    await editarUsuario(botonEditar.dataset.id);

    return;
  }

  const botonPassword = event.target.closest(".btn-password");

  if (botonPassword) {
    await abrirPasswordUsuario(botonPassword.dataset.id);

    return;
  }

  const botonEstatus = event.target.closest(".btn-bloquear, .btn-activar");

  if (botonEstatus) {
    await cambiarEstatusUsuario(
      botonEstatus.dataset.id,
      botonEstatus.dataset.estatus,
    );
  }
});

// ============================================================
// NUEVO USUARIO
// ============================================================

btnNuevoUsuario.addEventListener("click", function () {
  modoFormulario = "crear";

  usuarioActual = null;

  limpiarFormularioUsuario();

  tituloModalUsuario.textContent = "Nuevo usuario";

  subtituloModalUsuario.textContent = "Ingresa los datos de la nueva cuenta.";

  grupoPasswordUsuario.style.display = "flex";

  usuarioPassword.value = "";

  usuarioRol.value = "";

  usuarioEstatus.value = "activo";

  guardarUsuario.textContent = "Guardar usuario";

  limpiarMensajeFormulario();

  modalUsuario.classList.add("show");
});

// ============================================================
// VER USUARIO
// ============================================================

async function verUsuario(idUsuario) {
  try {
    const usuario = await obtenerUsuario(idUsuario);

    const nombreCompleto = [
      usuario.nombre,
      usuario.apellido_paterno,
      usuario.apellido_materno,
    ]
      .filter(Boolean)
      .join(" ");

    detalleUsuarioId.textContent = "Usuario #" + usuario.id_usuario;

    detalleUsuarioNombre.value = nombreCompleto;

    detalleUsuarioCorreo.value = usuario.correo || "Sin registrar";

    detalleUsuarioTelefono.value = usuario.telefono || "Sin registrar";

    detalleUsuarioNacimiento.value = usuario.fecha_nacimiento
      ? formatearFecha(usuario.fecha_nacimiento)
      : "Sin registrar";

    detalleUsuarioCurp.value = usuario.curp || "Sin registrar";

    detalleUsuarioRfc.value = usuario.rfc || "Sin registrar";

    detalleUsuarioDireccion.value = usuario.direccion || "Sin registrar";

    detalleUsuarioCiudad.value = usuario.ciudad || "Sin registrar";

    detalleUsuarioEstado.value = usuario.estado || "Sin registrar";

    detalleUsuarioCodigoPostal.value = usuario.codigo_postal || "Sin registrar";

    detalleUsuarioRol.value = capitalizar(usuario.rol);

    detalleUsuarioEstatus.value = capitalizar(usuario.estatus);

    detalleUsuarioUltimoAcceso.value = usuario.ultimo_acceso
      ? formatearFechaHora(usuario.ultimo_acceso)
      : "Sin acceso registrado";

    detalleUsuarioCreacion.value = formatearFechaHora(usuario.fecha_creacion);

    modalVerUsuario.classList.add("show");
  } catch (error) {
    console.error(error);

    mostrarMensajeGeneral(error.message, "error");
  }
}

// ============================================================
// EDITAR USUARIO
// ============================================================

async function editarUsuario(idUsuario) {
  try {
    const usuario = await obtenerUsuario(idUsuario);

    usuarioActual = usuario;

    modoFormulario = "editar";

    limpiarFormularioUsuario();

    tituloModalUsuario.textContent = "Editar usuario";

    subtituloModalUsuario.textContent =
      "Modifica los datos de la cuenta #" + usuario.id_usuario + ".";

    grupoPasswordUsuario.style.display = "none";

    usuarioNombre.value = usuario.nombre || "";

    usuarioApellidoPaterno.value = usuario.apellido_paterno || "";

    usuarioApellidoMaterno.value = usuario.apellido_materno || "";

    usuarioTelefono.value = usuario.telefono || "";

    usuarioFechaNacimiento.value = usuario.fecha_nacimiento || "";

    usuarioCurp.value = usuario.curp || "";

    usuarioRfc.value = usuario.rfc || "";

    usuarioDireccion.value = usuario.direccion || "";

    usuarioCiudad.value = usuario.ciudad || "";

    usuarioEstado.value = usuario.estado || "";

    usuarioCodigoPostal.value = usuario.codigo_postal || "";

    usuarioCorreo.value = usuario.correo || "";

    usuarioRol.value = usuario.rol || "";

    usuarioEstatus.value = usuario.estatus || "activo";

    guardarUsuario.textContent = "Guardar cambios";

    limpiarMensajeFormulario();

    modalUsuario.classList.add("show");
  } catch (error) {
    console.error(error);

    mostrarMensajeGeneral(error.message, "error");
  }
}

// ============================================================
// GUARDAR USUARIO
// ============================================================

guardarUsuario.addEventListener("click", async function () {
  if (procesandoUsuario) {
    return;
  }

  const nombre = usuarioNombre.value.trim();

  const apellidoPaterno = usuarioApellidoPaterno.value.trim();

  const correo = usuarioCorreo.value.trim();

  const rol = usuarioRol.value;

  const estatus = usuarioEstatus.value;

  if (!nombre) {
    mostrarMensajeFormulario("Ingresa el nombre.", "error");

    usuarioNombre.focus();

    return;
  }

  if (!apellidoPaterno) {
    mostrarMensajeFormulario("Ingresa el apellido paterno.", "error");

    usuarioApellidoPaterno.focus();

    return;
  }

  if (!correo) {
    mostrarMensajeFormulario("Ingresa el correo.", "error");

    usuarioCorreo.focus();

    return;
  }

  if (!rol) {
    mostrarMensajeFormulario("Selecciona un rol.", "error");

    usuarioRol.focus();

    return;
  }

  if (modoFormulario === "crear" && usuarioPassword.value.length < 6) {
    mostrarMensajeFormulario(
      "La contraseña debe tener al menos 6 caracteres.",
      "error",
    );

    usuarioPassword.focus();

    return;
  }

  const datos = {
    accion: modoFormulario === "crear" ? "crear" : "actualizar",

    nombre: nombre,

    apellido_paterno: apellidoPaterno,

    apellido_materno: usuarioApellidoMaterno.value.trim(),

    correo: correo,

    telefono: usuarioTelefono.value.trim(),

    fecha_nacimiento: usuarioFechaNacimiento.value,

    curp: usuarioCurp.value.trim(),

    rfc: usuarioRfc.value.trim(),

    direccion: usuarioDireccion.value.trim(),

    ciudad: usuarioCiudad.value.trim(),

    estado: usuarioEstado.value.trim(),

    codigo_postal: usuarioCodigoPostal.value.trim(),

    rol: rol,

    estatus: estatus,
  };

  if (modoFormulario === "crear") {
    datos.password = usuarioPassword.value;
  } else {
    datos.id_usuario = usuarioActual.id_usuario;
  }

  procesandoUsuario = true;

  bloquearFormularioUsuario(true);

  try {
    const respuesta = await fetch("../../backend/routes/usuarios.php", {
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
        resultado.message || "No fue posible guardar el usuario.",
        "error",
      );

      return;
    }

    mostrarMensajeFormulario(resultado.message, "exito");

    await cargarUsuarios();

    setTimeout(cerrarFormularioUsuario, 700);
  } catch (error) {
    console.error(error);

    mostrarMensajeFormulario("No se pudo conectar con el servidor.", "error");
  } finally {
    procesandoUsuario = false;

    bloquearFormularioUsuario(false);
  }
});

// ============================================================
// MODAL CONTRASEÑA
// ============================================================

async function abrirPasswordUsuario(idUsuario) {
  try {
    const usuario = await obtenerUsuario(idUsuario);

    usuarioActual = usuario;

    const nombreCompleto = [
      usuario.nombre,
      usuario.apellido_paterno,
      usuario.apellido_materno,
    ]
      .filter(Boolean)
      .join(" ");

    passwordUsuarioNombre.textContent = nombreCompleto;

    nuevaPasswordUsuario.value = "";

    confirmarPasswordUsuario.value = "";

    limpiarMensajePassword();

    modalPasswordUsuario.classList.add("show");
  } catch (error) {
    console.error(error);

    mostrarMensajeGeneral(error.message, "error");
  }
}

// ============================================================
// GUARDAR CONTRASEÑA
// ============================================================

guardarPasswordUsuario.addEventListener("click", async function () {
  if (!usuarioActual || procesandoPassword) {
    return;
  }

  const password = nuevaPasswordUsuario.value;

  const confirmar = confirmarPasswordUsuario.value;

  if (password.length < 6) {
    mostrarMensajePassword(
      "La contraseña debe tener al menos 6 caracteres.",
      "error",
    );

    return;
  }

  if (password !== confirmar) {
    mostrarMensajePassword("Las contraseñas no coinciden.", "error");

    return;
  }

  const aceptar = window.confirm(
    "¿Deseas cambiar la contraseña de este usuario?",
  );

  if (!aceptar) {
    return;
  }

  procesandoPassword = true;

  bloquearFormularioPassword(true);

  try {
    const respuesta = await fetch("../../backend/routes/usuarios.php", {
      method: "POST",

      credentials: "include",

      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify({
        accion: "cambiar_password",

        id_usuario: usuarioActual.id_usuario,

        password: password,
      }),
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
      mostrarMensajePassword(
        resultado.message || "No fue posible cambiar la contraseña.",
        "error",
      );

      return;
    }

    mostrarMensajePassword(resultado.message, "exito");

    setTimeout(cerrarPassword, 700);
  } catch (error) {
    console.error(error);

    mostrarMensajePassword("No se pudo conectar con el servidor.", "error");
  } finally {
    procesandoPassword = false;

    bloquearFormularioPassword(false);
  }
});

// ============================================================
// CAMBIAR ESTATUS
// ============================================================

async function cambiarEstatusUsuario(idUsuario, nuevoEstatus) {
  const usuario = usuarios.find(
    (item) => Number(item.id_usuario) === Number(idUsuario),
  );

  if (!usuario) {
    return;
  }

  const nombreCompleto = [usuario.nombre, usuario.apellido_paterno]
    .filter(Boolean)
    .join(" ");

  let mensajeConfirmacion;

  if (nuevoEstatus === "activo") {
    mensajeConfirmacion =
      "¿Deseas activar la cuenta de " + nombreCompleto + "?";
  } else {
    mensajeConfirmacion =
      "¿Deseas bloquear la cuenta de " + nombreCompleto + "?";
  }

  const confirmar = window.confirm(mensajeConfirmacion);

  if (!confirmar) {
    return;
  }

  try {
    const respuesta = await fetch("../../backend/routes/usuarios.php", {
      method: "POST",

      credentials: "include",

      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify({
        accion: "cambiar_estatus",

        id_usuario: idUsuario,

        estatus: nuevoEstatus,
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

    await cargarUsuarios();
  } catch (error) {
    console.error(error);

    mostrarMensajeGeneral("No se pudo conectar con el servidor.", "error");
  }
}

// ============================================================
// CERRAR MODAL CREAR / EDITAR
// ============================================================

cerrarModalUsuario.addEventListener("click", cerrarFormularioUsuario);

cancelarUsuario.addEventListener("click", cerrarFormularioUsuario);

function cerrarFormularioUsuario() {
  modalUsuario.classList.remove("show");

  usuarioActual = null;

  modoFormulario = "crear";

  limpiarFormularioUsuario();

  limpiarMensajeFormulario();
}

// ============================================================
// CERRAR MODAL VER
// ============================================================

cerrarModalVerUsuario.addEventListener("click", cerrarVerUsuario);

cerrarDetalleUsuario.addEventListener("click", cerrarVerUsuario);

function cerrarVerUsuario() {
  modalVerUsuario.classList.remove("show");
}

// ============================================================
// CERRAR MODAL CONTRASEÑA
// ============================================================

cerrarModalPassword.addEventListener("click", cerrarPassword);

cancelarPasswordUsuario.addEventListener("click", cerrarPassword);

function cerrarPassword() {
  modalPasswordUsuario.classList.remove("show");

  nuevaPasswordUsuario.value = "";

  confirmarPasswordUsuario.value = "";

  usuarioActual = null;

  limpiarMensajePassword();
}

// ============================================================
// LIMPIAR FORMULARIO
// ============================================================

function limpiarFormularioUsuario() {
  usuarioNombre.value = "";

  usuarioApellidoPaterno.value = "";

  usuarioApellidoMaterno.value = "";

  usuarioTelefono.value = "";

  usuarioFechaNacimiento.value = "";

  usuarioCurp.value = "";

  usuarioRfc.value = "";

  usuarioDireccion.value = "";

  usuarioCiudad.value = "";

  usuarioEstado.value = "";

  usuarioCodigoPostal.value = "";

  usuarioCorreo.value = "";

  usuarioPassword.value = "";

  usuarioRol.value = "";

  usuarioEstatus.value = "activo";
}

// ============================================================
// BLOQUEAR FORMULARIO
// ============================================================

function bloquearFormularioUsuario(bloquear) {
  guardarUsuario.disabled = bloquear;

  btnNuevoUsuario.disabled = bloquear;

  usuarioNombre.disabled = bloquear;

  usuarioApellidoPaterno.disabled = bloquear;

  usuarioApellidoMaterno.disabled = bloquear;

  usuarioTelefono.disabled = bloquear;

  usuarioFechaNacimiento.disabled = bloquear;

  usuarioCurp.disabled = bloquear;

  usuarioRfc.disabled = bloquear;

  usuarioDireccion.disabled = bloquear;

  usuarioCiudad.disabled = bloquear;

  usuarioEstado.disabled = bloquear;

  usuarioCodigoPostal.disabled = bloquear;

  usuarioCorreo.disabled = bloquear;

  usuarioPassword.disabled = bloquear;

  usuarioRol.disabled = bloquear;

  usuarioEstatus.disabled = bloquear;

  guardarUsuario.textContent = bloquear
    ? "Guardando..."
    : modoFormulario === "crear"
      ? "Guardar usuario"
      : "Guardar cambios";
}

// ============================================================
// BLOQUEAR PASSWORD
// ============================================================

function bloquearFormularioPassword(bloquear) {
  nuevaPasswordUsuario.disabled = bloquear;

  confirmarPasswordUsuario.disabled = bloquear;

  guardarPasswordUsuario.disabled = bloquear;

  guardarPasswordUsuario.textContent = bloquear
    ? "Actualizando..."
    : "Cambiar contraseña";
}

// ============================================================
// MENSAJES
// ============================================================

function mostrarMensajeGeneral(texto, tipo) {
  mensajeUsuarios.textContent = texto;

  mensajeUsuarios.className = "mensaje-usuarios " + tipo;

  setTimeout(function () {
    mensajeUsuarios.textContent = "";

    mensajeUsuarios.className = "mensaje-usuarios";
  }, 4000);
}

function mostrarMensajeFormulario(texto, tipo) {
  mensajeFormularioUsuario.textContent = texto;

  mensajeFormularioUsuario.className = "mensaje-usuarios " + tipo;
}

function limpiarMensajeFormulario() {
  mensajeFormularioUsuario.textContent = "";

  mensajeFormularioUsuario.className = "mensaje-usuarios";
}

function mostrarMensajePassword(texto, tipo) {
  mensajePasswordUsuario.textContent = texto;

  mensajePasswordUsuario.className = "mensaje-usuarios " + tipo;
}

function limpiarMensajePassword() {
  mensajePasswordUsuario.textContent = "";

  mensajePasswordUsuario.className = "mensaje-usuarios";
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

function formatearFechaHora(fecha) {
  if (!fecha) {
    return "Sin registrar";
  }

  const texto = String(fecha);

  const partes = texto.split(" ");

  const fechaBase = partes[0].split("-");

  if (fechaBase.length !== 3) {
    return texto;
  }

  let resultado = fechaBase[2] + "/" + fechaBase[1] + "/" + fechaBase[0];

  if (partes[1]) {
    resultado += " " + partes[1].substring(0, 5);
  }

  return resultado;
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

  await cargarUsuarios();
}

iniciar();
