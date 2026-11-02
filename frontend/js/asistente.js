// ============================================================
// CHATBOT IA - GLOBAL
// ============================================================

(function iniciarChatbot() {

  const chatbotScriptURL =
    new URL(document.currentScript.src);


  // ========================================================
  // RUTAS DEL BACKEND
  // ========================================================

  const chatbotBackendURL =
    new URL(
      "../../backend/routes/asistente.php",
      chatbotScriptURL
    ).href;


  const chatbotSesionURL =
    new URL(
      "../../backend/routes/sesion.php",
      chatbotScriptURL
    ).href;


  if (document.getElementById("chatbotButton")) {
    return;
  }


  // ========================================================
  // USUARIO ACTUAL
  // ========================================================

  let chatbotUsuarioId = null;

  let claveHistorialChat = null;

  let claveChatAbierto = null;


  // ========================================================
  // OBTENER USUARIO DE LA SESIÓN
  // ========================================================

  async function obtenerUsuarioChatbot() {

    try {

      const respuesta =
        await fetch(
          chatbotSesionURL,
          {
            method: "GET",
            credentials: "include"
          }
        );


      const data =
        await respuesta.json();


      if (
        data.success &&
        data.sesion &&
        data.usuario &&
        data.usuario.id_usuario
      ) {

        chatbotUsuarioId =
          String(data.usuario.id_usuario);


        // Cada usuario tiene sus propias claves

        claveHistorialChat =
          "chatbotHistorial_usuario_" +
          chatbotUsuarioId;


        claveChatAbierto =
          "chatbotAbierto_usuario_" +
          chatbotUsuarioId;


        return true;

      }


      return false;


    } catch (error) {

      console.error(
        "Error obteniendo sesión del chatbot:",
        error
      );

      return false;

    }

  }


  // ========================================================
  // BOTÓN FLOTANTE
  // ========================================================

  const chatbotButton =
    document.createElement("button");


  chatbotButton.type = "button";

  chatbotButton.id =
    "chatbotButton";

  chatbotButton.className =
    "chatbot-button";

  chatbotButton.innerHTML =
    "🤖";

  chatbotButton.title =
    "Asistente IA";


  // ========================================================
  // VENTANA DEL CHATBOT
  // ========================================================

  const chatbotWindow =
    document.createElement("div");


  chatbotWindow.id =
    "chatbotWindow";

  chatbotWindow.className =
    "chatbot-window";


  chatbotWindow.innerHTML = `

    <div class="chatbot-header">

      <div>
        <strong>Asistente IA</strong>
        <span>En línea</span>
      </div>

      <button
        type="button"
        class="chatbot-close"
        id="chatbotClose"
      >
        ×
      </button>

    </div>


    <div
      class="chatbot-messages"
      id="chatbotMessages"
    >

      <div class="chatbot-message bot">
        👋 Hola, soy tu asistente virtual.
      </div>

    </div>


    <div class="chatbot-input-area">

      <input
        type="text"
        placeholder="Escribe un mensaje..."
        id="chatbotInput"
        autocomplete="off"
      >

      <button
        type="button"
        id="chatbotSend"
      >
        ➤
      </button>

    </div>

  `;


  document.body.appendChild(
    chatbotWindow
  );


  document.body.appendChild(
    chatbotButton
  );


  // ========================================================
  // ELEMENTOS
  // ========================================================

  const chatbotClose =
    document.getElementById(
      "chatbotClose"
    );


  const chatbotInput =
    document.getElementById(
      "chatbotInput"
    );


  const chatbotSend =
    document.getElementById(
      "chatbotSend"
    );


  const chatbotMessages =
    document.getElementById(
      "chatbotMessages"
    );


  // ========================================================
  // MOSTRAR MENSAJE INICIAL
  // ========================================================

  function mostrarMensajeInicial() {

    chatbotMessages.innerHTML = `

      <div class="chatbot-message bot">
        👋 Hola, soy tu asistente virtual.
      </div>

    `;

  }


  // ========================================================
  // GUARDAR HISTORIAL
  // ========================================================

  function guardarHistorialChat() {

    if (!claveHistorialChat) {
      return;
    }


    const mensajes = [];


    chatbotMessages
      .querySelectorAll(
        ".chatbot-message"
      )
      .forEach(
        function (elemento) {

          // No guardar "Escribiendo..."

          if (
            elemento.classList.contains(
              "escribiendo"
            )
          ) {

            return;

          }


          mensajes.push({

            tipo:
              elemento.classList.contains(
                "usuario"
              )
                ? "usuario"
                : "bot",

            texto:
              elemento.textContent

          });

        }
      );


    localStorage.setItem(
      claveHistorialChat,
      JSON.stringify(mensajes)
    );

  }


  // ========================================================
  // CARGAR HISTORIAL DEL USUARIO
  // ========================================================

  function cargarHistorialChat() {

    if (!claveHistorialChat) {

      mostrarMensajeInicial();

      return;

    }


    const historialGuardado =
      localStorage.getItem(
        claveHistorialChat
      );


    if (!historialGuardado) {

      mostrarMensajeInicial();

      return;

    }


    try {

      const mensajes =
        JSON.parse(
          historialGuardado
        );


      if (
        !Array.isArray(mensajes) ||
        mensajes.length === 0
      ) {

        mostrarMensajeInicial();

        return;

      }


      chatbotMessages.innerHTML =
        "";


      mensajes.forEach(
        function (mensaje) {

          if (
            !mensaje ||
            !mensaje.texto
          ) {

            return;

          }


          const elemento =
            document.createElement(
              "div"
            );


          elemento.className =
            "chatbot-message " +
            (
              mensaje.tipo === "usuario"
                ? "usuario"
                : "bot"
            );


          elemento.textContent =
            mensaje.texto;


          chatbotMessages.appendChild(
            elemento
          );

        }
      );


      chatbotMessages.scrollTop =
        chatbotMessages.scrollHeight;


    } catch (error) {

      console.error(
        "Error cargando historial del chatbot:",
        error
      );


      mostrarMensajeInicial();

    }

  }


  // ========================================================
  // RESTAURAR ESTADO ABIERTO / CERRADO
  // ========================================================

  function restaurarEstadoChatbot() {

    if (!claveChatAbierto) {
      return;
    }


    const chatbotEstabaAbierto =
      localStorage.getItem(
        claveChatAbierto
      );


    if (
      chatbotEstabaAbierto === "si"
    ) {

      chatbotWindow.classList.add(
        "activo"
      );

    } else {

      chatbotWindow.classList.remove(
        "activo"
      );

    }

  }


  // ========================================================
  // INICIALIZAR DATOS DEL USUARIO
  // ========================================================

  async function inicializarChatbotUsuario() {

    const sesionValida =
      await obtenerUsuarioChatbot();


    if (!sesionValida) {

      console.warn(
        "El chatbot no encontró una sesión activa."
      );

      mostrarMensajeInicial();

      return;

    }


    cargarHistorialChat();

    restaurarEstadoChatbot();

  }


  // ========================================================
  // ABRIR CHAT
  // ========================================================

  chatbotButton.addEventListener(
    "click",
    function () {

      chatbotWindow.classList.add(
        "activo"
      );


      if (claveChatAbierto) {

        localStorage.setItem(
          claveChatAbierto,
          "si"
        );

      }


      chatbotInput.focus();

    }
  );


  // ========================================================
  // CERRAR CHAT
  // ========================================================

  chatbotClose.addEventListener(
    "click",
    function () {

      chatbotWindow.classList.remove(
        "activo"
      );


      if (claveChatAbierto) {

        localStorage.setItem(
          claveChatAbierto,
          "no"
        );

      }

    }
  );


  // ========================================================
  // ENVIAR MENSAJE
  // ========================================================

  async function enviarMensaje() {

    const mensaje =
      chatbotInput.value.trim();


    if (!mensaje) {
      return;
    }


    // ====================================================
    // VALIDAR QUE EXISTA USUARIO
    // ====================================================

    if (!chatbotUsuarioId) {

      const sesionValida =
        await obtenerUsuarioChatbot();


      if (!sesionValida) {

        const mensajeError =
          document.createElement(
            "div"
          );


        mensajeError.className =
          "chatbot-message bot";


        mensajeError.textContent =
          "Tu sesión no está disponible. Inicia sesión nuevamente.";


        chatbotMessages.appendChild(
          mensajeError
        );


        return;

      }


      cargarHistorialChat();

    }


    // ====================================================
    // MOSTRAR MENSAJE DEL USUARIO
    // ====================================================

    const mensajeUsuario =
      document.createElement(
        "div"
      );


    mensajeUsuario.className =
      "chatbot-message usuario";


    mensajeUsuario.textContent =
      mensaje;


    chatbotMessages.appendChild(
      mensajeUsuario
    );


    // Guardar únicamente en historial
    // del usuario actual

    guardarHistorialChat();


    chatbotInput.value =
      "";


    chatbotMessages.scrollTop =
      chatbotMessages.scrollHeight;


    // ====================================================
    // MOSTRAR "ESCRIBIENDO..."
    // ====================================================

    const mensajeEscribiendo =
      document.createElement(
        "div"
      );


    mensajeEscribiendo.className =
      "chatbot-message bot escribiendo";


    mensajeEscribiendo.textContent =
      "Escribiendo...";


    chatbotMessages.appendChild(
      mensajeEscribiendo
    );


    chatbotMessages.scrollTop =
      chatbotMessages.scrollHeight;


    // ====================================================
    // ENVIAR AL BACKEND
    // ====================================================

    try {

      const respuesta =
        await fetch(
          chatbotBackendURL,
          {

            method: "POST",

            headers: {

              "Content-Type":
                "application/json"

            },

            credentials:
              "include",

            body:
              JSON.stringify({

                mensaje:
                  mensaje,

                pagina:
                  window.location.pathname,

                tituloPagina:
                  document.title || "",

                encabezadoPagina:
                  document
                    .querySelector("h1")
                    ?.textContent
                    .trim() || "",

                seccionActiva:
                  document
                    .querySelector(
                      ".menu-item.active"
                    )
                    ?.textContent
                    .trim() || ""

              })

          }
        );


      const data =
        await respuesta.json();


      mensajeEscribiendo.remove();


      // ====================================================
      // MOSTRAR RESPUESTA
      // ====================================================

      const mensajeBot =
        document.createElement(
          "div"
        );


      mensajeBot.className =
        "chatbot-message bot";


      if (data.success) {

        mensajeBot.textContent =
          data.respuesta;

      } else {

        mensajeBot.textContent =
          data.message ||
          "Ocurrió un error.";

      }


      chatbotMessages.appendChild(
        mensajeBot
      );


      // Guardar respuesta solamente
      // para este usuario

      guardarHistorialChat();


    } catch (error) {

      mensajeEscribiendo.remove();


      const mensajeError =
        document.createElement(
          "div"
        );


      mensajeError.className =
        "chatbot-message bot";


      mensajeError.textContent =
        "No se pudo conectar con el servidor.";


      chatbotMessages.appendChild(
        mensajeError
      );


      guardarHistorialChat();


      console.error(
        "Error chatbot:",
        error
      );

    }


    chatbotMessages.scrollTop =
      chatbotMessages.scrollHeight;


    chatbotInput.focus();

  }


  // ========================================================
  // BOTÓN ENVIAR
  // ========================================================

  chatbotSend.addEventListener(
    "click",
    enviarMensaje
  );


  // ========================================================
  // ENTER PARA ENVIAR
  // ========================================================

  chatbotInput.addEventListener(
    "keydown",
    function (event) {

      if (event.key === "Enter") {

        event.preventDefault();

        enviarMensaje();

      }

    }
  );


  // ========================================================
  // INICIAR CHATBOT
  // ========================================================

  inicializarChatbotUsuario();

})();