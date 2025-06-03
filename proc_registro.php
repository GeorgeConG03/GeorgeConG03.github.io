<?php
// Iniciar sesión (siempre al principio)
session_start();

// Configuración de la base de datos
$host = "localhost";     // Servidor de la base de datos
$usuario = "root";       // Usuario de la base de datos
$password = "bri_gitte_03";          // Contraseña de la base de datos
$basedatos = "here'stoyou"; // Nombre de la base de datos

// Conexión a la base de datos
$conexion = new mysqli($host, $usuario, $password, $basedatos);

// Verificar conexión
if ($conexion->connect_error) {
    $_SESSION['registro_error'] = "Error de conexión a la base de datos: " . $conexion->connect_error;
    header("Location: registro.php");
    exit();
}

// Establecer charset
$conexion->set_charset("utf8");

// Verificar si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Obtener datos del formulario
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm-password']);
    $captcha_input = isset($_POST['captcha']) ? strtoupper(trim($_POST['captcha'])) : '';
    $terms = isset($_POST['terms']) ? true : false;
    
    // Obtener el CAPTCHA almacenado en la cookie
    $captcha_cookie = isset($_COOKIE['captcha']) ? $_COOKIE['captcha'] : '';
    
    // Validación del lado del servidor
    $errores = [];
    
    // Validar nombre de usuario
    if (empty($username)) {
        $errores[] = "El nombre de usuario es obligatorio.";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errores[] = "El nombre de usuario debe tener entre 3 y 20 caracteres.";
    } else {
        // Verificar si el nombre de usuario ya existe
        $query = "SELECT id_usuario FROM usuarios WHERE nombre = ?";
        $stmt = $conexion->prepare($query);
        
        if ($stmt === false) {
            $errores[] = "Error en la consulta SQL: " . $conexion->error;
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $errores[] = "Este nombre de usuario ya está en uso.";
            }
            $stmt->close();
        }
    }
    
    // Validar email
    if (empty($email)) {
        $errores[] = "El correo electrónico es obligatorio.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Formato de correo electrónico inválido.";
    } else {
        // Verificar si el email ya existe
        $query = "SELECT id_usuario FROM usuarios WHERE correo = ?";
        $stmt = $conexion->prepare($query);
        
        if ($stmt === false) {
            $errores[] = "Error en la consulta SQL: " . $conexion->error;
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $errores[] = "Este correo electrónico ya está registrado.";
            }
            $stmt->close();
        }
    }
    
    // Validar contraseña
    if (empty($password)) {
        $errores[] = "La contraseña es obligatoria.";
    } elseif (strlen($password) < 6) {
        $errores[] = "La contraseña debe tener al menos 6 caracteres.";
    }
    
    // Validar confirmación de contraseña
    if ($password !== $confirm_password) {
        $errores[] = "Las contraseñas no coinciden.";
    }
    
    // Validar CAPTCHA
    if (empty($captcha_input)) {
        $errores[] = "Por favor, complete el CAPTCHA.";
    } elseif ($captcha_input !== $captcha_cookie) {
        $errores[] = "El código CAPTCHA es incorrecto.";
    }
    
    // Validar términos y condiciones
    if (!$terms) {
        $errores[] = "Debe aceptar los términos y condiciones.";
    }
    
    // Si hay errores, redirigir al formulario con mensajes de error
    if (!empty($errores)) {
        $_SESSION['registro_error'] = implode("<br>", $errores);
        header("Location: registro.php");
        exit();
    }
    
    // Si no hay errores, proceder con el registro
    
    // Generar hash de la contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Fecha de registro actual
    $fecha_registro = date("Y-m-d H:i:s");
    
    // Insertar usuario en la base de datos
    $query = "INSERT INTO usuarios (nombre, correo, contraseña, fecha_registro) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($query);
    
    if ($stmt === false) {
        $_SESSION['registro_error'] = "Error en la consulta SQL: " . $conexion->error;
        header("Location: registro.php");
        exit();
    }
    
    $stmt->bind_param("ssss", $username, $email, $password_hash, $fecha_registro);
    
    if ($stmt->execute()) {
        // Registro exitoso
        $_SESSION['registro_exito'] = "¡Registro exitoso! Ahora puedes iniciar sesión.";
        
        // Eliminar la cookie del CAPTCHA
        setcookie("captcha", "", time() - 3600, "/");
        
        // Redirigir a la página de inicio de sesión
        header("Location: ini_sec.php");
        exit();
    } else {
        // Error al registrar
        $_SESSION['registro_error'] = "Error al registrar: " . $conexion->error;
        header("Location: registro.php");
        exit();
    }
    
    $stmt->close();
}

// Si no se envió el formulario, redirigir a la página de registro
header("Location: registro.php");
exit();
?>