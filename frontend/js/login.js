const loginForm = document.getElementById("loginForm");

const correoInput = document.getElementById("correo");

const passwordInput = document.getElementById("password");

const mostrarPassword =
    document.getElementById("mostrarPassword");

const loginButton =
    document.getElementById("loginButton");

const mensaje =
    document.getElementById("mensaje");


// ============================================================
// MOSTRAR / OCULTAR CONTRASEÑA
// ============================================================

mostrarPassword.addEventListener("click", function () {

    if (passwordInput.type === "password") {

        passwordInput.type = "text";

        mostrarPassword.textContent = "🙈";

    } else {

        passwordInput.type = "password";

        mostrarPassword.textContent = "👁";

    }

});


// ============================================================
// LOGIN
// ============================================================

loginForm.addEventListener("submit", async function (event) {

    event.preventDefault();


    mensaje.className = "mensaje";

    mensaje.textContent = "";


    const correo = correoInput.value.trim();

    const password = passwordInput.value;


    if (!correo || !password) {

        mensaje.textContent =
            "Por favor completa todos los campos.";

        mensaje.classList.add("error");

        return;
    }


    loginButton.disabled = true;

    loginButton.textContent =
        "Iniciando sesión...";


    try {

        const respuesta = await fetch(
            "../backend/routes/login.php",
            {
                method: "POST",

                credentials: "include",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({
                    correo: correo,
                    password: password
                })
            }
        );


        const datos = await respuesta.json();


        if (!datos.success) {

            mensaje.textContent =
                datos.message ||
                "No fue posible iniciar sesión.";

            mensaje.classList.add("error");

            loginButton.disabled = false;

            loginButton.textContent =
                "Iniciar sesión";

            return;
        }


        // ====================================================
        // LOGIN CORRECTO
        // ====================================================

        mensaje.textContent =
            "Inicio de sesión correcto.";

        mensaje.classList.add("exito");


        console.log(
            "Usuario:",
            datos.usuario
        );


        // ====================================================
        // OBTENER ROL DEL USUARIO
        // ====================================================

        const rol =
            String(
                datos.usuario?.rol || ""
            )
            .trim()
            .toLowerCase();


        // ====================================================
        // REDIRECCIÓN SEGÚN ROL
        // ====================================================

        setTimeout(function () {

            if (rol === "cliente") {

                window.location.href =
                    "pages/portal_cliente.html";

                return;
            }


            if (
                rol === "administrador" ||
                rol === "gerente"
            ) {

                window.location.href =
                    "pages/dashboard.html";

                return;
            }


            // =================================================
            // ROL NO RECONOCIDO
            // =================================================

            mensaje.textContent =
                "Tu usuario no tiene un rol válido.";

            mensaje.className =
                "mensaje error";


        }, 500);


    } catch (error) {

        console.error(error);

        mensaje.textContent =
            "No se pudo conectar con el servidor.";

        mensaje.classList.add("error");

    }


    loginButton.disabled = false;

    loginButton.textContent =
        "Iniciar sesión";

});