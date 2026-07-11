/**
 * CraftLog - Módulo de Login
 * Maneja la autenticación de usuarios
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const usuarioOEmail = document.getElementById('loginUser').value.trim();
        const password = document.getElementById('loginPass').value.trim();

        if (!usuarioOEmail || !password) {
            alert('⚠️ Por favor, completa todos los campos');
            return;
        }

        autenticarUsuario(usuarioOEmail, password);
    });

    function autenticarUsuario(usuario, password) {
        const params = new URLSearchParams({
            usuario: usuario,
            password: password
        });

        // Cambiar la URL al archivo PHP
        fetch('/ProyectoM/api/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: params
        })
        .then(response => {
            if (response.status === 200) {
                return response.json().then(data => {
                    window.location.href = 'dashboard.html';
                });
            } else {
                return response.json().then(data => {
                    throw new Error(data.error || 'Error en el login');
                });
            }
        })
        .catch(error => {
            alert('❌ ' + error.message);
            console.error('Error en el login:', error);
        });
    }
});