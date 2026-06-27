document.addEventListener("DOMContentLoaded", () => {
    const formLogin = document.getElementById('form-login');
    const mensajeError = document.getElementById('mensaje-error');
    const btnSubmit = document.getElementById('btn-submit');

    if (formLogin) {
        formLogin.addEventListener('submit', async (e) => {
            e.preventDefault();
            const usuario = document.getElementById('usuario').value.trim();
            const password = document.getElementById('password').value;

            mensajeError.classList.add('hidden');
            btnSubmit.textContent = 'Verificando...';
            btnSubmit.disabled = true;

            try {
                // Dentro de tu archivo login.js, asegúrate de que el fetch apunte a la acción "login":
                const respuesta = await fetch('controllers/LoginController.php?accion=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ usuario, password })
                });

                const data = await respuesta.json();
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    mensajeError.textContent = data.error || 'Credenciales inválidas';
                    mensajeError.classList.remove('hidden');
                }
            } catch (err) {
                mensajeError.textContent = 'Error de conexión con el servidor.';
                mensajeError.classList.remove('hidden');
            } finally {
                btnSubmit.textContent = 'Ingresar al Tablero';
                btnSubmit.disabled = false;
            }
        });
    }
});