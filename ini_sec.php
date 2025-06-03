<?php
// Página de inicio de sesión (versión corregida)
// Incluir archivo de inicialización
require_once __DIR__ . '/includes/init.php';

// Redirigir si ya está autenticado
if (is_logged_in()) {
    // Redirigir a la página guardada en la sesión o a la página principal
    $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
    unset($_SESSION['redirect_after_login']); // Limpiar la variable de sesión
    header("Location: $redirect");
    exit;
}

// Inicializar variables
$user_input = ''; // Puede ser nombre o correo
$password = '';
$remember_me = false;
$errores = [];
$exito = false;

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar entrada de usuario (nombre o correo)
    if (empty($_POST['user_input'])) {
        $errores['user_input'] = 'El nombre o correo es obligatorio';
    } else {
        $user_input = htmlspecialchars($_POST['user_input']);
    }

    // Validar contraseña
    if (empty($_POST['password'])) {
        $errores['password'] = 'La contraseña es obligatoria';
    } else {
        $password = $_POST['password'];
    }

    // Verificar "Recordarme"
    $remember_me = isset($_POST['remember_me']) && $_POST['remember_me'] === 'on';

    // Si no hay errores, intentar iniciar sesión
    if (empty($errores)) {
        try {
            // Buscar usuario por nombre o correo
            $query = "SELECT * FROM usuarios WHERE nombre = ? OR correo = ?";
            $stmt = $conexion->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
            }
            
            $stmt->bind_param("ss", $user_input, $user_input);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verificar contraseña
                if (password_verify($password, $user['contraseña'])) {
                    // Iniciar sesión
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id'] = $user['id_usuario'];
                    $_SESSION['username'] = $user['nombre'];
                    
                    // Si se seleccionó "Recordarme", crear cookie
                    if ($remember_me) {
                        // Generar token seguro
                        $remember_token = bin2hex(random_bytes(32));
                        
                        // Guardar token en la base de datos
                        // Primero verificamos si la columna remember_token existe
                        $check_column = "SHOW COLUMNS FROM usuarios LIKE 'remember_token'";
                        $column_result = $conexion->query($check_column);
                        
                        if ($column_result->num_rows === 0) {
                            // La columna no existe, la creamos
                            $add_column = "ALTER TABLE usuarios ADD COLUMN remember_token VARCHAR(255) DEFAULT NULL";
                            $conexion->query($add_column);
                        }
                        
                        // Ahora actualizamos el token
                        $update_query = "UPDATE usuarios SET remember_token = ? WHERE id_usuario = ?";
                        $update_stmt = $conexion->prepare($update_query);
                        
                        if ($update_stmt) {
                            $update_stmt->bind_param("si", $remember_token, $user['id_usuario']);
                            $update_stmt->execute();
                            $update_stmt->close();
                            
                            // Crear cookie
                            remember_me($user['id_usuario'], $remember_token);
                        }
                    }
                    
                    // Mostrar mensaje de éxito
                    show_message('Has iniciado sesión correctamente', 'success');
                    
                    // Redirigir a la página guardada en la sesión o a la página principal
                    $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
                    unset($_SESSION['redirect_after_login']); // Limpiar la variable de sesión
                    
                    header("Location: $redirect");
                    exit;
                } else {
                    $errores['login'] = 'Nombre/correo o contraseña incorrectos';
                }
            } else {
                $errores['login'] = 'Nombre/correo o contraseña incorrectos';
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            $errores['db'] = 'Error en la base de datos: ' . $e->getMessage();
        }
    }
}

// Título de la página
$page_title = "Iniciar Sesión - Full Moon, Full Life";

// Incluir estilos base
$include_base_css = true;

// Estilos adicionales
$page_styles = '
/* Estilos específicos para la página de inicio de sesión */
.login-container {
    background-color: rgba(51, 51, 51, 0.9);
    border-radius: 10px;
    padding: 2rem;
    margin: 4rem auto;
    max-width: 500px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
    position: relative;
    z-index: 10;
}

.login-title {
    font-size: 2.5rem;
    color: white;
    text-shadow: 3px 3px 0 #ff3366;
    text-align: center;
    margin-bottom: 1.5rem;
    font-family: "Bangers", cursive;
}

.login-form {
    background-color: white;
    border: 4px solid black;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    transform: skew(-5deg);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    color: black;
    text-shadow: 1px 1px 0 #0066cc;
    font-family: "Bangers", cursive;
}

.form-input {
    width: 100%;
    padding: 0.8rem;
    border: 2px solid black;
    border-radius: 5px;
    font-family: Arial, sans-serif;
    font-size: 1rem;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.remember-me input {
    width: 20px;
    height: 20px;
}

.remember-me label {
    font-size: 1rem;
    cursor: pointer;
}

.login-buttons {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn-login {
    background-color: white;
    color: black;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    padding: 0.8rem 1.5rem;
    border: 2px solid black;
    border-radius: 5px;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    transform: skew(-5deg);
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    text-shadow: 2px 2px 0 #0066cc;
}

.btn-login:hover {
    transform: skew(-5deg) scale(1.05);
    box-shadow: 7px 7px 0 rgba(0, 0, 0, 0.7);
}

.register-link {
    color: #0066cc;
    text-decoration: none;
    font-weight: bold;
    transition: color 0.2s;
}

.register-link:hover {
    color: #ff3366;
    text-decoration: underline;
}

.error-message {
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    color: #FF0000;
    text-shadow: 1px 1px 0 #000;
    background-color: #FFF;
    border: 2px solid #000;
    padding: 8px 12px;
    margin-top: 5px;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.7);
    transform: skew(-5deg);
    display: inline-block;
}

/* Animaciones */
@keyframes shake {
    0%, 100% { transform: translateX(0) skew(-5deg); }
    20%, 60% { transform: translateX(-8px) skew(-5deg); }
    40%, 80% { transform: translateX(8px) skew(-5deg); }
}

.error-shake {
    animation: shake 0.4s ease-in-out;
}
';

// Incluir el encabezado
include 'includes/header.php';
?>

<!-- Contenido principal -->
<main class="login-container">
    <h1 class="login-title">Iniciar Sesión</h1>
    
    <?php if (isset($errores['login']) || isset($errores['db'])): ?>
    <div class="error-message error-shake">
        <?php echo isset($errores['login']) ? $errores['login'] : $errores['db']; ?>
    </div>
    <?php endif; ?>
    
    <form action="ini_sec.php" method="post" class="login-form">
        <div class="form-group">
            <label for="user_input" class="form-label">Nombre o Correo</label>
            <input type="text" id="user_input" name="user_input" class="form-input" value="<?php echo $user_input; ?>" required>
            <?php if (isset($errores['user_input'])): ?>
            <div class="error-message error-shake"><?php echo $errores['user_input']; ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" id="password" name="password" class="form-input" required>
            <?php if (isset($errores['password'])): ?>
            <div class="error-message error-shake"><?php echo $errores['password']; ?></div>
            <?php endif; ?>
        </div>
        
        <div class="remember-me">
            <input type="checkbox" id="remember_me" name="remember_me">
            <label for="remember_me">Recordarme</label>
        </div>
        
        <div class="login-buttons">
            <button type="submit" class="btn-login">Iniciar Sesión</button>
            <a href="registro.php" class="register-link">¿No tienes cuenta? Regístrate</a>
        </div>
    </form>
</main>

<?php include 'includes/footer.php'; ?>
