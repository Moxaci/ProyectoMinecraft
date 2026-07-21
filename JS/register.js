/**
 * CraftLog - Módulo de Registro
 * Maneja el registro de nuevos usuarios
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const errorElement = document.getElementById('formError');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Obtener valores
        const user = document.getElementById('regUser').value.trim();
        const email = document.getElementById('regEmail').value.trim();
        const pass = document.getElementById('regPass').value;
        const confirmPass = document.getElementById('regPassConfirm').value;

        // Limpiar error anterior
        errorElement.style.display = 'none';
        errorElement.textContent = '';

        // Validaciones
        if (user.length < 4) {
            mostrarError('El nombre debe tener al menos 4 caracteres.');
            return;
        }

        if (pass.length < 6) {
            mostrarError('La contraseña debe tener al menos 6 caracteres.');
            return;
        }

        if (pass !== confirmPass) {
            mostrarError('Las contraseñas no coinciden.');
            return;
        }

        // Enviar al servidor (PHP)
        registrarUsuario(user, email, pass);
    });

    function mostrarError(mensaje) {
        errorElement.textContent = mensaje;
        errorElement.style.display = 'block';
    }

    function registrarUsuario(nombre, correo, password) {
        const params = new URLSearchParams({
            nombre: nombre,
            correo: correo,
            password: password
        });

        // Cambiar la URL al archivo PHP
        fetch('/ProyectoMinecraft/api/register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: params
        })
        .then(response => {
            if (response.status === 201) {
                window.location.href = 'login.html';
            } else {
                return response.json().then(data => {
                    throw new Error(data.error || 'Error en el registro');
                });
            }
        })
        .catch(error => {
            mostrarError(error.message);
            console.error('Error en el registro:', error);
        });
    }
});
