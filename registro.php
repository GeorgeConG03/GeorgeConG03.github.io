<?php
// Iniciar sesión (siempre al principio)
session_start();

// Título de la página
$pageTitle = "Registrarse - Full Moon, Full Life";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/stylesregis.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }

        .form-title {
            font-size: 2.5rem;
            color: white;
            text-shadow: 3px 3px 0 #ff3366;
            margin-bottom: 2rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 1.5rem;
            color: white;
            text-shadow: 2px 2px 0 #0066cc;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.8rem;
            font-family: Arial, sans-serif;
            font-size: 1rem;
            border: 3px solid #000;
            border-radius: 5px;
            background-color: white;
            box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.5);
        }

        .form-submit {
            display: block;
            width: 100%;
            background-color: white;
            color: black;
            font-family: 'Bangers', cursive;
            font-size: 1.8rem;
            padding: 0.8rem 1.5rem;
            margin: 2rem 0;
            text-align: center;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
            transform: skew(-5deg);
            cursor: pointer;
            letter-spacing: 1px;
            text-shadow: 2px 2px 0 #0066cc;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .form-submit:hover {
            transform: skew(-5deg) scale(1.05);
            box-shadow: 7px 7px 0 rgba(0, 0, 0, 0.7);
        }

        .form-footer {
            text-align: center;
            font-size: 1.2rem;
            color: white;
            text-shadow: 1px 1px 0 #0066cc;
            margin-top: 1rem;
        }

        .form-footer a {
            color: #ff3366;
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 0;
        }

        .checkbox-group input {
            width: 20px;
            height: 20px;
        }

        .checkbox-label {
            font-size: 1.2rem;
            color: white;
            text-shadow: 1px 1px 0 #0066cc;
        }
        
        /* Estilo del CAPTCHA para que coincida con el diseño */
        .captcha-container {
            margin: 1.5rem 0;
            padding: 1rem;
            background-color: white;
            border: 3px solid #000;
            border-radius: 5px;
            box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.5);
        }
        
        .captcha-title {
            font-size: 1.5rem;
            color: white;
            text-shadow: 2px 2px 0 #0066cc;
            margin-bottom: 0.5rem;
        }
        
        .captcha-display {
            font-size: 1.8rem;
            letter-spacing: 3px;
            padding: 0.8rem;
            margin-bottom: 1rem;
            background-color: #f0f0f0;
            border-radius: 5px;
            border: 2px solid #000;
            text-align: center;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            user-select: none;
        }
        
        .btn-refresh {
            background-color: #0066cc;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            font-family: Arial, sans-serif;
            margin-top: 0.5rem;
        }
        
        .btn-refresh:hover {
            background-color: #0055aa;
        }
        
        .error-message {
            color: #ff3366;
            font-size: 0.9rem;
            margin-top: 0.3rem;
            display: none;
        }
        
        .success-message {
            color: #00cc66;
            font-size: 1rem;
            margin: 1rem 0;
            text-align: center;
            display: none;
        }
    </style>
</head>
<body>
    <div class="background-container">
        <div class="background-overlay" style="background-color: rgba(0, 102, 204, 0.5);"></div>
        <img src="assets/rec/comicfondo4.jpg" alt="Fondo cómic" class="background-image">
    </div>

    <div class="skyline"></div>

    <!-- Barra de navegación -->
    <nav class="navbar">
        <div class="logo">
            <img src="assets/rec/fullmoon.png" alt="Full Moon Logo">
            <a href="index.php" class="logo-text">FULL MOON, FULL LIFE</a>
        </div>
        <div class="nav-links">
            <a href="foros.php" class="nav-link">FOROS</a>
            <a href="series.php" class="nav-link">SERIES</a>
            <a href="pelis.php" class="nav-link">PELÍCULAS</a>
            <a href="libros.php" class="nav-link">LIBROS</a>
            <a href="perfil.php" class="nav-link">MI CUENTA</a>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container">
        <div class="form-container">
            <h2 class="form-title">¡ÚNETE A NUESTRA COMUNIDAD!</h2>
            
            <!-- Mensajes de feedback (errores o éxito) -->
            <?php
            // Mostrar mensajes de error si existen
            if (!empty($_SESSION['registro_error'])) {
                echo '<div class="error-message" style="display: block; font-size: 1.1rem; padding: 0.8rem; background-color: rgba(255, 51, 102, 0.2); border-radius: 5px; margin-bottom: 1.5rem;">' . $_SESSION['registro_error'] . '</div>';
                // Limpiamos la variable de sesión
                unset($_SESSION['registro_error']);
            }
            
            // Mostrar mensaje de éxito si existe
            if (!empty($_SESSION['registro_exito'])) {
                echo '<div class="success-message" style="display: block; font-size: 1.1rem; padding: 0.8rem; background-color: rgba(0, 204, 102, 0.2); border-radius: 5px; margin-bottom: 1.5rem;">' . $_SESSION['registro_exito'] . '</div>';
                // Limpiamos la variable de sesión
                unset($_SESSION['registro_exito']);
            }
            ?>
            
            <form id="registerForm" action="proc_registro.php" method="post">
                <div class="form-group">
                    <label for="username" class="form-label">NOMBRE DE USUARIO</label>
                    <input type="text" id="username" name="username" class="form-input" required>
                    <div id="usernameError" class="error-message"></div>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">CORREO ELECTRÓNICO</label>
                    <input type="email" id="email" name="email" class="form-input" required>
                    <div id="emailError" class="error-message"></div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">CONTRASEÑA</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                    <div id="passwordError" class="error-message"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm-password" class="form-label">CONFIRMAR CONTRASEÑA</label>
                    <input type="password" id="confirm-password" name="confirm-password" class="form-input" required>
                    <div id="confirmPasswordError" class="error-message"></div>
                </div>
                
                <div class="captcha-container">
                    <div class="captcha-title">Verificación de Seguridad</div>
                    <div class="captcha-display" id="captchaDisplay"></div>
                    <input type="text" id="captchaInput" name="captcha" class="form-input" placeholder="Ingrese el texto mostrado" required>
                    <button type="button" id="refreshCaptcha" class="btn-refresh">Actualizar Código</button>
                    <div id="captchaError" class="error-message"></div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms" class="checkbox-label">ACEPTO LOS TÉRMINOS Y CONDICIONES</label>
                    <div id="termsError" class="error-message"></div>
                </div>
                
                <div id="successMessage" class="success-message"></div>
                <button type="submit" class="form-submit">¡REGISTRARME!</button>
            </form>
            
            <div class="form-footer">
                ¿YA TIENES UNA CUENTA? <a href="ini_sec.php">INICIA SESIÓN AQUÍ</a>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Generador de CAPTCHA
        function generateCaptcha() {
            const chars = "0123456789ABCDEFGHJKLMNPQRSTUVWXYZ";
            let captcha = "";
            
            // Generar 6 caracteres aleatorios
            for(let i = 0; i < 6; i++) {
                captcha += chars[Math.floor(Math.random() * chars.length)];
            }
            
            // Aplicar transformaciones visuales
            let distortedCaptcha = "";
            for(let i = 0; i < captcha.length; i++) {
                // Alternar mayúsculas y minúsculas aleatoriamente
                distortedCaptcha += Math.random() > 0.5 ? captcha[i].toLowerCase() : captcha[i];
            }
            
            document.getElementById('captchaDisplay').textContent = distortedCaptcha;
            // Guardamos el captcha en una sesión para verificación del lado del servidor
            document.cookie = "captcha=" + captcha.toUpperCase() + "; path=/";
            return captcha.toUpperCase(); // Guardamos la versión canónica para validar
        }

        // Variables para el CAPTCHA
        let currentCaptcha = generateCaptcha();
        const refreshBtn = document.getElementById('refreshCaptcha');
        
        // Evento para refrescar el CAPTCHA
        refreshBtn.addEventListener('click', function() {
            currentCaptcha = generateCaptcha();
            document.getElementById('captchaInput').value = "";
            document.getElementById('captchaError').style.display = 'none';
        });

        // Validación del formulario en el cliente
        const registerForm = document.getElementById('registerForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm-password');
        
        // Elementos de error
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');
        const confirmPasswordError = document.getElementById('confirmPasswordError');
        
        // Validación en tiempo real
        emailInput.addEventListener('input', function() {
            const email = this.value.trim();
            if (!email) {
                emailError.textContent = '¡El correo no puede estar vacío!';
                emailError.style.display = 'block';
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                emailError.textContent = 'Formato de correo inválido';
                emailError.style.display = 'block';
            } else {
                emailError.style.display = 'none';
            }
        });
        
        passwordInput.addEventListener('input', function() {
            const password = this.value.trim();
            if (!password) {
                passwordError.textContent = '¡La contraseña no puede estar vacía!';
                passwordError.style.display = 'block';
            } else if (password.length < 6) {
                passwordError.textContent = 'Mínimo 6 caracteres requeridos';
                passwordError.style.display = 'block';
            } else {
                passwordError.style.display = 'none';
            }
        });
        
        confirmPasswordInput.addEventListener('input', function() {
            const password = passwordInput.value.trim();
            const confirmPassword = this.value.trim();
            
            if (password !== confirmPassword) {
                confirmPasswordError.textContent = 'Las contraseñas no coinciden';
                confirmPasswordError.style.display = 'block';
            } else {
                confirmPasswordError.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html>