document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    // 1. Validación mejorada
    loginForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        // Resetear mensajes
        document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
        
        // Validación básica (igual que tienes)
        if (!validarFormulario()) return;

        try {
            // 2. Autenticación con el backend
            const response = await fetch('http://localhost:3000/api/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    email: emailInput.value,
                    password: passwordInput.value
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Error en credenciales');
            }

            // 3. Almacenar sesión
            localStorage.setItem('authToken', data.token);
            localStorage.setItem('userId', data.userId);
            localStorage.setItem('userName', data.nombre);

            // Redirección
            window.location.href = 'perfil.html';

        } catch (error) {
            // 4. Manejo de errores
            emailError.textContent = '¡BANG! Credenciales incorrectas';
            emailError.style.display = 'block';
            loginForm.classList.add('error-shake');
            setTimeout(() => loginForm.classList.remove('error-shake'), 500);
        }
    });

    // Función de validación (puedes mantener la tuya)
    function validarFormulario() {
        let isValid = true;
        // ... tu lógica actual de validación
        return isValid;
    }
});