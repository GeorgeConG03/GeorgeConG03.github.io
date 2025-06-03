<?php
// Archivo: eliminar-resena.php
require_once 'includes/init.php';

// Verificar que el usuario esté logueado
if (!is_logged_in()) {
    show_message("Debes iniciar sesión para eliminar una reseña.", "error");
    redirect("ini_sec.php");
    exit();
}

// Verificar que sea una petición GET
if ($_SERVER["REQUEST_METHOD"] != "GET") {
    redirect("index.php");
    exit();
}

// Verificar token CSRF
if (!verify_csrf_token($_GET['token'])) {
    show_message("Token de seguridad inválido.", "error");
    redirect("index.php");
    exit();
}

// Obtener ID de la reseña
$id_resena = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_resena <= 0) {
    show_message("ID de reseña inválido.", "error");
    redirect("index.php");
    exit();
}

// Verificar que la reseña existe y pertenece al usuario
$sql_check = "SELECT id_usuario, archivo_php, categoria, imagenes FROM resenas WHERE id_resena = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("i", $id_resena);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    show_message("La reseña no existe.", "error");
    redirect("index.php");
    exit();
}

$resena = $result_check->fetch_assoc();
$stmt_check->close();

// Verificar que el usuario es el propietario de la reseña
if ($_SESSION['user_id'] != $resena['id_usuario'] && !is_admin()) {
    show_message("No tienes permiso para eliminar esta reseña.", "error");
    redirect("index.php");
    exit();
}

// Eliminar el archivo físico de la reseña si existe
if (!empty($resena['archivo_php']) && file_exists($resena['archivo_php'])) {
    unlink($resena['archivo_php']);
}

// Eliminar la imagen de la reseña si existe
if (!empty($resena['imagenes']) && file_exists($resena['imagenes']) && strpos($resena['imagenes'], 'default') === false) {
    unlink($resena['imagenes']);
}

// Eliminar comentarios asociados a la reseña
$sql_delete_comments = "DELETE FROM comentarios WHERE id_resena = ?";
$stmt_delete_comments = $conexion->prepare($sql_delete_comments);
$stmt_delete_comments->bind_param("i", $id_resena);
$stmt_delete_comments->execute();
$stmt_delete_comments->close();

// Eliminar la reseña de la base de datos
$sql_delete = "DELETE FROM resenas WHERE id_resena = ?";
$stmt_delete = $conexion->prepare($sql_delete);
$stmt_delete->bind_param("i", $id_resena);

if ($stmt_delete->execute()) {
    show_message("Reseña eliminada correctamente.", "success");
} else {
    show_message("Error al eliminar la reseña: " . $stmt_delete->error, "error");
}

$stmt_delete->close();

// Redirigir según la categoría
switch ($resena['categoria']) {
    case 'pelicula':
        redirect("pelis.php");
        break;
    case 'serie':
        redirect("series.php");
        break;
    case 'libro':
        redirect("libros.php");
        break;
    default:
        redirect("index.php");
}
?>