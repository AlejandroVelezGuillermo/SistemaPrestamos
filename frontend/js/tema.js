// ============================================================
// TEMA GLOBAL - CLARO / OSCURO
// ============================================================

const themeToggle =
    document.getElementById("themeToggle");

const themeIcon =
    document.getElementById("themeIcon");

const themeText =
    document.getElementById("themeText");


// ============================================================
// CARGAR TEMA GUARDADO
// ============================================================

const temaGuardado =
    localStorage.getItem("tema");

if (temaGuardado === "oscuro") {

    document.body.classList.add("dark-mode");

} else {

    document.body.classList.remove("dark-mode");

}


// ============================================================
// ACTUALIZAR BOTÓN
// ============================================================

function actualizarBotonTema() {

    const oscuro =
        document.body.classList.contains("dark-mode");

    if (themeIcon) {

        themeIcon.textContent =
            oscuro ? "☀️" : "🌙";

    }

    if (themeText) {

        themeText.textContent =
            oscuro ? "Modo claro" : "Modo oscuro";

    }

}


// ============================================================
// CAMBIAR TEMA
// ============================================================

if (themeToggle) {

    themeToggle.addEventListener(
        "click",
        function (event) {

            event.preventDefault();
            event.stopPropagation();

            document.body.classList.toggle(
                "dark-mode"
            );

            const oscuro =
                document.body.classList.contains(
                    "dark-mode"
                );

            localStorage.setItem(
                "tema",
                oscuro ? "oscuro" : "claro"
            );

            actualizarBotonTema();

        }
    );

}


// ============================================================
// CARGAR CHATBOT GLOBAL
// ============================================================

(function cargarChatbotGlobal() {

    // ========================================================
    // ENCONTRAR TEMA.JS
    // ========================================================

    let temaScript =
        document.currentScript;


    // Por si currentScript no está disponible
    if (!temaScript) {

        const scripts =
            document.querySelectorAll("script");

        for (const script of scripts) {

            if (
                script.src &&
                script.src.includes("tema.js")
            ) {

                temaScript = script;
                break;

            }

        }

    }


    if (!temaScript || !temaScript.src) {

        console.error(
            "No se pudo localizar tema.js"
        );

        return;

    }


    const temaURL =
        new URL(temaScript.src);


    // ========================================================
    // CARGAR CSS DEL CHATBOT
    // ========================================================

    if (
        !document.getElementById(
            "chatbotGlobalCSS"
        )
    ) {

        const chatbotCSS =
            document.createElement("link");

        chatbotCSS.id =
            "chatbotGlobalCSS";

        chatbotCSS.rel =
            "stylesheet";

chatbotCSS.href =
    "/sistema_prestamos/frontend/css/asistente.css?v=9";

        document.head.appendChild(
            chatbotCSS
        );

    }


    // ========================================================
    // CARGAR JAVASCRIPT DEL CHATBOT
    // ========================================================

    if (
        !document.getElementById(
            "chatbotGlobalJS"
        )
    ) {

        const chatbotJS =
            document.createElement("script");

        chatbotJS.id =
            "chatbotGlobalJS";

        chatbotJS.src =
            new URL(
                "asistente.js?v=9",
                temaURL
            ).href;


        chatbotJS.onload = function () {

            console.log(
                "Chatbot global cargado correctamente"
            );

        };


        chatbotJS.onerror = function () {

            console.error(
                "No se pudo cargar chatbot.js"
            );

        };


        document.body.appendChild(
            chatbotJS
        );

    }

})();


// ============================================================
// INICIAR
// ============================================================

actualizarBotonTema();