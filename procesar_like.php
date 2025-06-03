<?php
// Archivo para procesar likes/dislikes en comentarios
require_once __DIR__ . '/includes/init.php';

// Verificar si el usuario está autenticado
if (!is_logged_in()) {
    show_message('Debes iniciar sesión para dar like/dislike', 'error');
    header('Location: ini_sec.php');
    exit;
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    show_message('Método no permitido', 'error');
    header('Location: index.php');
    exit;
}

// Obtener datos del formulario
$action = isset($_POST['action']) ? $_POST['action'] : '';
$id_comentario = isset($_POST['id_comentario']) ? (int)$_POST['id_comentario'] : 0;
$ruta_actual = isset($_POST['ruta_actual']) ? $_POST['ruta_actual'] : 'index.php';

// Validar datos
if (!in_array($action, ['like', 'dislike'])) {
    show_message('Acción no válida', 'error');
    header('Location: ' . $ruta_actual);
    exit;
}

if ($id_comentario <= 0) {
    show_message('ID de comentario no válido', 'error');
    header('Location: ' . $ruta_actual);
    exit;
}

// Conexión a la base de datos
$host = "localhost";
$usuario = "root";
$password = "bri_gitte_03";
$database = "here'stoyou";

try {
    $conexion = new mysqli($host, $usuario, $password, $database);
    
    if ($conexion->connect_error) {
        throw new Exception("Error de conexión: " . $conexion->connect_error);
    }
    
    $user_id = $_SESSION['user_id'];
    $tipo = ($action === 'like') ? 1 : 0;
    
    // Verificar que el comentario existe
    $query_verificar = "SELECT id_comentario FROM comentarios WHERE id_comentario = ?";
    $stmt_verificar = $conexion->prepare($query_verificar);
    
    if (!$stmt_verificar) {
        throw new Exception("Error al preparar la consulta de verificación: " . $conexion->error);
    }
    
    $stmt_verificar->bind_param("i", $id_comentario);
    $stmt_verificar->execute();
    $result_verificar = $stmt_verificar->get_result();
    
    if ($result_verificar->num_rows === 0) {
        throw new Exception("El comentario no existe");
    }
    
    $stmt_verificar->close();
    
    // Verificar si ya existe un like/dislike del usuario para este comentario
    $query_existente = "SELECT id_like, tipo FROM likes_comentarios WHERE id_usuario = ? AND id_comentario = ?";
    $stmt_existente = $conexion->prepare($query_existente);
    
    if (!$stmt_existente) {
        throw new Exception("Error al preparar la consulta de verificación: " . $conexion->error);
    }
    
    $stmt_existente->bind_param("ii", $user_id, $id_comentario);
    $stmt_existente->execute();
    $result_existente = $stmt_existente->get_result();
    
    if ($result_existente->num_rows > 0) {
        // Ya existe un like/dislike, actualizar o eliminar
        $like_existente = $result_existente->fetch_assoc();
        
        if ($like_existente['tipo'] == $tipo) {
            // Mismo tipo, eliminar el like/dislike
            $query_eliminar = "DELETE FROM likes_comentarios WHERE id_usuario = ? AND id_comentario = ?";
            $stmt_eliminar = $conexion->prepare($query_eliminar);
            
            if ($stmt_eliminar) {
                $stmt_eliminar->bind_param("ii", $user_id, $id_comentario);
                $stmt_eliminar->execute();
                $stmt_eliminar->close();
            }
        } else {
            // Diferente tipo, actualizar
            $query_actualizar = "UPDATE likes_comentarios SET tipo = ? WHERE id_usuario = ? AND id_comentario = ?";
            $stmt_actualizar = $conexion->prepare($query_actualizar);
            
            if ($stmt_actualizar) {
                $stmt_actualizar->bind_param("iii", $tipo, $user_id, $id_comentario);
                $stmt_actualizar->execute();
                $stmt_actualizar->close();
            }
        }
    } else {
        // No existe, insertar nuevo like/dislike
        $query_insertar = "INSERT INTO likes_comentarios (id_usuario, id_comentario, tipo) VALUES (?, ?, ?)";
        $stmt_insertar = $conexion->prepare($query_insertar);
        
        if (!$stmt_insertar) {
            throw new Exception("Error al preparar la consulta de inserción: " . $conexion->error);
        }
        
        $stmt_insertar->bind_param("iii", $user_id, $id_comentario, $tipo);
        $stmt_insertar->execute();
        $stmt_insertar->close();
    }
    
    $stmt_existente->close();
    $conexion->close();
    
} catch (Exception $e) {
    show_message('Error: ' . $e->getMessage(), 'error');
}

// Redirigir de vuelta a la página de la reseña
header('Location: ' . $ruta_actual);
exit;
?>
