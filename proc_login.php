<?php
// Iniciar sesión (siempre al principio)
session_start();

// Configuración de la base de datos
$host = "localhost";     // Servidor de la base de datos
$usuario = "root";       // Usuario de la base de datos
$password = "bri_gitte_03";  // Contraseña actualizada
$basedatos = "here'stoyou";  // Nombre de la base de datos actualizado

// Conexión a la base de datos
$conexion = new mysqli($host, $usuario, $password, $basedatos);

// Verificar conexión
if ($conexion->connect_error) {
    $_SESSION['login_error'] = "Error de conexión a la base de datos: " . $conexion->connect_error;
    header("Location: ini_sec.php");
    exit();
}

// Establecer charset
$conexion->set_charset("utf8");

// Verificar si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Obtener datos del formulario
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Validación básica
    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Por favor, completa todos los campos.";
        header("Location: ini_sec.php");
        exit();
    }
    
    // Buscar el usuario en la base de datos
    $query = "SELECT id_usuario, nombre, correo, contraseña FROM usuarios WHERE correo = ?";
    $stmt = $conexion->prepare($query);
    
    if ($stmt === false) {
        $_SESSION['login_error'] = "Error en la consulta SQL: " . $conexion->error;
        header("Location: ini_sec.php");
        exit();
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 1) {
        // Usuario encontrado
        $usuario = $resultado->fetch_assoc();
        
        // Verificar la contraseña
        if (password_verify($password, $usuario['contraseña'])) {
            // Contraseña correcta, iniciar sesión
            $_SESSION['user_id'] = $usuario['id_usuario'];
            $_SESSION['username'] = $usuario['nombre'];
            $_SESSION['email'] = $usuario['correo'];
            $_SESSION['logged_in'] = true;
            
            // Actualizar la fecha de último acceso
            $fecha_actual = date("Y-m-d H:i:s");
            $update_query = "UPDATE usuarios SET ultimo_acceso = ? WHERE id_usuario = ?";
            $update_stmt = $conexion->prepare($update_query);
            
            if ($update_stmt !== false) {
                $update_stmt->bind_param("si", $fecha_actual, $usuario['id_usuario']);
                $update_stmt->execute();
                $update_stmt->close();
            }
            
            // Redirigir a la página de inicio
            header("Location: index.php");
            exit();
        } else {
            // Contraseña incorrecta
            $_SESSION['login_error'] = "Correo electrónico o contraseña incorrectos.";
            header("Location: ini_sec.php");
            exit();
        }
    } else {
        // Usuario no encontrado
        $_SESSION['login_error'] = "Correo electrónico o contraseña incorrectos.";
        header("Location: ini_sec.php");
        exit();
    }
    
    $stmt->close();
}

// Si no se envió el formulario, redirigir a la página de inicio de sesión
header("Location: ini_sec.php");
exit();
?>