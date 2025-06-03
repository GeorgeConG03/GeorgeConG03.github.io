<?php
// Archivo: procesar-comentario.php
require_once 'includes/init.php';

// Verificar que el usuario esté logueado
if (!is_logged_in()) {
    show_message("Debes iniciar sesión para comentar.", "error");
    redirect("ini_sec.php");
}

// Verificar si el usuario está baneado
function verificarUsuarioBaneado($user_id) {
    global $conexion;
    
    $sql = "SELECT * FROM usuarios_baneados 
            WHERE id_usuario = ? AND activo = 1 
            AND (fecha_expiracion IS NULL OR fecha_expiracion > NOW())";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!verify_csrf_token($_POST['csrf_token'])) {
        show_message("Token de seguridad inválido.", "error");
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Verificar si el usuario está baneado
    if (verificarUsuarioBaneado($user_id)) {
        show_message("Tu cuenta ha sido suspendida y no puedes comentar.", "error");
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
    }
    
    $id_resena = (int)$_POST['id_resena'];
    $comentario = sanitize($_POST['comentario']);
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;
    $id_comentario_padre = isset($_POST['id_comentario_padre']) ? (int)$_POST['id_comentario_padre'] : null;
    
    // Validaciones
    if (empty($comentario)) {
        show_message("El comentario no puede estar vacío.", "error");
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
    }
    
    if (strlen($comentario) > 1000) {
        show_message("El comentario es demasiado largo (máximo 1000 caracteres).", "error");
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
    }
    
    // Verificar que la reseña existe
    $sql_check = "SELECT id_resena FROM resenas WHERE id_resena = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param("i", $id_resena);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows === 0) {
        show_message("La reseña no existe.", "error");
        redirect("index.php");
    }
    
    // Si es una respuesta, verificar que el comentario padre existe
    if ($id_comentario_padre) {
        $sql_parent = "SELECT id_comentario FROM comentarios WHERE id_comentario = ? AND id_resena = ?";
        $stmt_parent = $conexion->prepare($sql_parent);
        $stmt_parent->bind_param("ii", $id_comentario_padre, $id_resena);
        $stmt_parent->execute();
        $result_parent = $stmt_parent->get_result();
        
        if ($result_parent->num_rows === 0) {
            show_message("El comentario al que intentas responder no existe.", "error");
            redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
        }
    }
    
    // Insertar el comentario
    $sql = "INSERT INTO comentarios (id_resena, id_usuario, id_comentario_padre, texto, calificacion) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        // Si la preparación falla, mostrar el error y redirigir
        show_message("Error en la consulta: " . $conexion->error, "error");
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
        exit;
    }

    $stmt->bind_param("iiisi", $id_resena, $user_id, $id_comentario_padre, $comentario, $rating);
    
    if ($stmt->execute()) {
        $mensaje = $id_comentario_padre ? "Respuesta publicada correctamente." : "Comentario publicado correctamente.";
        show_message($mensaje, "success");
    } else {
        show_message("Error al publicar el comentario: " . $conexion->error, "error");
    }
    
    $stmt->close();
}

// Redirigir de vuelta a la página de la reseña
redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
?>
