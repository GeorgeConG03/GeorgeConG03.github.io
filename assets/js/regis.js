document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const registerForm = document.getElementById('registerForm');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm-password');
    const termsCheckbox = document.getElementById('terms');
    
    // Elementos de error
    const usernameError = document.getElementById('usernameError');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const confirmPasswordError = document.getElementById('confirmPasswordError');
    const termsError = document.getElementById('termsError');
    const successMessage = document.getElementById('successMessage');

    // Estilos de mensajes (con diseño cómic)
    const messageStyles = {
        error: {
            emptyUsername: '<span style="font-size: 1.8rem; color: #FF0000;"></span><br>¡El nombre de usuario no puede estar vacío!',
            emptyEmail: '<span style="font-size: 1.8rem; color: #FF0000;"></span><br>¡El correo no puede estar vacío!',
            invalidEmail: '<span style="font-size: 1.8rem; color: #FF0000;"></span><br>Formato de correo inválido',
            emptyPassword: '<span style="font-size: 1.8rem; color: #FF0000;"></span><br>¡La contraseña no puede estar vacía!',
            shortPassword: '<span style="font-size: 1.8rem; color: #FF0000;"></span><br>Mínimo 6 caracteres requeridos',
            passwordMismatch: '<span style="font-size: 1.8rem; color: #FF0000;"></span><br>Las contraseñas no coinciden',
            termsNotAccepted: '<span style="font-size: 1.8rem; color: #FF0000;"></span><br>Debes aceptar los términos'
        },
        success: '<span style="font-size: 1.8rem; color: #00FF00;">¡ÉXITO!</span><br>¡Registro completado! Redirigiendo...'
    };

    // Validación en tiempo real
    emailInput.addEventListener('input', validarEmail);
    passwordInput.addEventListener('input', validarPassword);
    confirmPasswordInput.addEventListener('input', validarConfirmacionPassword);

    // Submit del formulario
    registerForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        resetErrores();

        const isValid = validarFormularioCompleto();

        if (isValid) {
            await registrarUsuario();
        } else {
            mostrarPrimerError();
        }
    });

    // Funciones auxiliares
    function resetErrores() {
        [usernameError, emailError, passwordError, confirmPasswordError, termsError].forEach(el => {
            el.style.display = 'none';
        });
        registerForm.classList.remove('error-shake');
    }

    function validarFormularioCompleto() {
        let isValid = true;

        // Validación de nombre de usuario
        if (!usernameInput.value.trim()) {
            usernameError.innerHTML = messageStyles.error.emptyUsername;
            usernameError.style.display = 'block';
            isValid = false;
        }

        // Validación de email
        validarEmail();

        // Validación de contraseña
        if (!validarPassword()) isValid = false;

        // Validación de confirmación
        if (!validarConfirmacionPassword()) isValid = false;

        // Validación de términos
        if (!termsCheckbox.checked) {
            termsError.innerHTML = messageStyles.error.termsNotAccepted;
            termsError.style.display = 'block';
            isValid = false;
        }

        return isValid;
    }

    function validarEmail() {
        const email = emailInput.value.trim();
        if (!email) {
            emailError.innerHTML = messageStyles.error.emptyEmail;
            emailError.style.display = 'block';
            return false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            emailError.innerHTML = messageStyles.error.invalidEmail;
            emailError.style.display = 'block';
            return false;
        }
        return true;
    }

    function validarPassword() {
        const password = passwordInput.value.trim();
        if (!password) {
            passwordError.innerHTML = messageStyles.error.emptyPassword;
            passwordError.style.display = 'block';
            return false;
        } else if (password.length < 6) {
            passwordError.innerHTML = messageStyles.error.shortPassword;
            passwordError.style.display = 'block';
            return false;
        }
        return true;
    }

    function validarConfirmacionPassword() {
        const password = passwordInput.value.trim();
        const confirmPassword = confirmPasswordInput.value.trim();
        
        if (password !== confirmPassword) {
            confirmPasswordError.innerHTML = messageStyles.error.passwordMismatch;
            confirmPasswordError.style.display = 'block';
            return false;
        }
        return true;
    }

    function mostrarPrimerError() {
        registerForm.classList.add('error-shake');
        setTimeout(() => registerForm.classList.remove('error-shake'), 500);

        const firstErrorField = 
            !usernameInput.value.trim() ? usernameInput :
            !validarEmail() ? emailInput :
            !validarPassword() ? passwordInput :
            !validarConfirmacionPassword() ? confirmPasswordInput :
            !termsCheckbox.checked ? termsCheckbox : null;
            
        if (firstErrorField) firstErrorField.focus();
    }

    async function registrarUsuario() {
        const submitButton = registerForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Registrando...';

        try {
            const response = await fetch('http://localhost:3000/api/registro', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    nombre: usernameInput.value.trim(),
                    email: emailInput.value.trim(),
                    password: passwordInput.value.trim()
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Error en el registro');
            }

            // Mostrar éxito
            successMessage.innerHTML = messageStyles.success;
            successMessage.style.display = 'block';
            registerForm.reset();

            // Redirección después de 2 segundos
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 2000);

        } catch (error) {
            console.error('Error en el registro:', error);
            emailError.textContent = error.message.includes('Duplicate') 
                ? 'Este correo ya está registrado' 
                : error.message;
            emailError.style.display = 'block';
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = '¡REGISTRARME!';
        }
    }

    // Efecto visual al completar campos
    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('change', () => {
            if (input.value.trim()) {
                input.style.animation = 'comicBoom 0.5s';
                setTimeout(() => input.style.animation = '', 500);
            }
        });
    });
});