<?php
// Archivo: procesar-foro-post.php
require_once 'includes/init.php';
require_once 'includes/foros_functions.php';

// Verificar que el usuario esté logueado
if (!is_logged_in()) {
    show_message("Debes iniciar sesión para publicar.", "error");
    redirect("ini_sec.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!verify_csrf_token($_POST['csrf_token'])) {
        show_message("Token de seguridad inválido.", "error");
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
    }
    
    $user_id = $_SESSION['user_id'];
    $id_foro = (int)$_POST['id_foro'];
    $titulo = isset($_POST['titulo']) ? sanitize($_POST['titulo']) : null;
    $contenido = sanitize($_POST['contenido']);
    $id_post_padre = isset($_POST['id_post_padre']) ? (int)$_POST['id_post_padre'] : null;
    $es_hilo_principal = isset($_POST['es_hilo_principal']) ? 1 : 0;
    
    // Validaciones
    if (empty($contenido)) {
        show_message("El contenido del post no puede estar vacío.", "error");
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
    }
    
    if (strlen($contenido) > 2000) {
        show_message("El contenido es demasiado largo (máximo 2000 caracteres).", "error");
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
    }
    
    // Verificar que el usuario es miembro del foro
    if (!esMiembroForo($id_foro, $user_id)) {
        show_message("Debes ser miembro del foro para publicar.", "error");
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
    }
    
    // Procesar imagen si se subió
    $imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $imagen = subirImagenPost($_FILES['imagen']);
        if (!$imagen) {
            show_message("Error al subir la imagen.", "error");
            redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
        }
    }
    
    // Insertar el post
    $sql = "INSERT INTO foro_posts (id_foro, id_usuario, id_post_padre, titulo, contenido, imagen, es_hilo_principal) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    
    if (!$stmt) {
        show_message("Error al preparar la consulta: " . $conexion->error, "error");
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
    }
    
    $stmt->bind_param("iiisssi", $id_foro, $user_id, $id_post_padre, $titulo, $contenido, $imagen, $es_hilo_principal);
    
    if ($stmt->execute()) {
        // Actualizar contador de posts del foro
        $sql_update = "UPDATE foros SET total_posts = (SELECT COUNT(*) FROM foro_posts WHERE id_foro = ? AND estado = 'activo') WHERE id_foro = ?";
        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->bind_param("ii", $id_foro, $id_foro);
        $stmt_update->execute();
        
        // Actualizar contador de respuestas del post padre si es una respuesta
        if ($id_post_padre) {
            $sql_respuestas = "UPDATE foro_posts SET total_respuestas = (SELECT COUNT(*) FROM foro_posts WHERE id_post_padre = ? AND estado = 'activo') WHERE id_post = ?";
            $stmt_respuestas = $conexion->prepare($sql_respuestas);
            $stmt_respuestas->bind_param("ii", $id_post_padre, $id_post_padre);
            $stmt_respuestas->execute();
        }
        
        $mensaje = $id_post_padre ? "Respuesta publicada correctamente." : "Post publicado correctamente.";
        show_message($mensaje, "success");
    } else {
        show_message("Error al publicar el post: " . $conexion->error, "error");
    }
    
    $stmt->close();
}

// Función para subir imagen de post
function subirImagenPost($file) {
    $target_dir = "foros/img-comentario/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = "post_" . time() . "_" . rand(1000, 9999) . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Verificar si es una imagen
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return false;
    }
    
    // Verificar tamaño (máximo 3MB)
    if ($file["size"] > 3000000) {
        return false;
    }
    
    // Permitir ciertos formatos
    if (!in_array($file_extension, ["jpg", "png", "jpeg", "gif", "webp"])) {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    }
    
    return false;
}

// Redirigir de vuelta al foro
redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
?>
