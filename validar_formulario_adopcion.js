document.addEventListener("DOMContentLoaded", () => {
    const formulario = document.getElementById("formulario-adopcion");
    if (!formulario) return;

    const campos = {
        nombre: {
            input: document.getElementById("nombre"),
            error: document.getElementById("error-nombre"),
            validar: (valor) =>
                /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]{2,50}$/u.test(valor.trim()),
            mensaje: "Introduce un nombre válido (solo letras y espacios).",
        },
        apellidos: {
            input: document.getElementById("apellidos"),
            error: document.getElementById("error-apellidos"),
            validar: (valor) =>
                /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]{2,80}$/u.test(valor.trim()),
            mensaje: "Introduce apellidos válidos (solo letras y espacios).",
        },
        dni: {
            input: document.getElementById("dni"),
            error: document.getElementById("error-dni"),
            validar: (valor) => /^[0-9]{8}[A-Za-z]$/.test(valor.trim()),
            mensaje: "El DNI debe tener 8 números y 1 letra.",
            normalizar: (valor) => valor.trim().toUpperCase(),
        },
        telefono: {
            input: document.getElementById("telefono"),
            error: document.getElementById("error-telefono"),
            validar: (valor) => /^[0-9]{9}$/.test(valor.trim()),
            mensaje: "El teléfono debe tener exactamente 9 números.",
        },
        correo: {
            input: document.getElementById("correo"),
            error: document.getElementById("error-correo"),
            validar: (valor) =>
                /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor.trim()),
            mensaje: "Introduce un correo electrónico válido.",
        },
        mensaje: {
            input: document.getElementById("mensaje"),
            error: document.getElementById("error-mensaje"),
            validar: (valor) => valor.trim().length <= 300,
            mensaje: "El mensaje no puede superar los 300 caracteres.",
        },
    };

    function marcarCampo(input, error, valido, mensaje = "") {
        input.classList.remove("input-valido", "input-invalido");
        error.classList.remove("mensaje-campo-ok", "mensaje-campo-error");
        error.textContent = "";

        if (valido) {
            input.classList.add("input-valido");
            if (input.value.trim() !== "") {
                error.textContent = "Correcto";
                error.classList.add("mensaje-campo-ok");
            }
        } else {
            input.classList.add("input-invalido");
            error.textContent = mensaje;
            error.classList.add("mensaje-campo-error");
        }
    }

    function validarCampo(campo) {
        const config = campos[campo];
        let valor = config.input.value;

        if (config.normalizar) {
            valor = config.normalizar(valor);
            config.input.value = valor;
        }

        const valido = config.validar(valor);
        marcarCampo(config.input, config.error, valido, config.mensaje);
        return valido;
    }

    Object.keys(campos).forEach((campo) => {
        const input = campos[campo].input;
        input.addEventListener("blur", () => validarCampo(campo));
        input.addEventListener("input", () => {
            if (input.classList.contains("input-invalido") || input.classList.contains("input-valido")) {
                validarCampo(campo);
            }
        });
    });

    formulario.addEventListener("submit", (e) => {
        let formularioValido = true;

        Object.keys(campos).forEach((campo) => {
            const valido = validarCampo(campo);
            if (!valido) formularioValido = false;
        });

        if (!formularioValido) {
            e.preventDefault();
        }
    });
});