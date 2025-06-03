<?php
// Archivo: crear-foro.php (versión corregida)
require_once 'includes/init.php';

// Verificar que el usuario esté logueado
if (!is_logged_in()) {
    show_message("Debes iniciar sesión para crear un foro.", "error");
    redirect("ini_sec.php");
}

$user_id = $_SESSION['user_id'];

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!verify_csrf_token($_POST['csrf_token'])) {
        show_message("Token de seguridad inválido.", "error");
        redirect("crear-foro.php");
    }
    
    $titulo = sanitize($_POST['titulo']);
    $descripcion = isset($_POST['descripcion']) ? sanitize($_POST['descripcion']) : '';
    $imagen_portada = null;
    
    // Validaciones
    if (empty($titulo)) {
        show_message("El título del foro es obligatorio.", "error");
        redirect("crear-foro.php");
    }
    
    if (strlen($titulo) > 255) {
        show_message("El título es demasiado largo (máximo 255 caracteres).", "error");
        redirect("crear-foro.php");
    }
    
    // Procesar imagen de portada
    if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] === 0) {
        $imagen_portada = subirImagenPortada($_FILES['imagen_portada']);
        if (!$imagen_portada) {
            show_message("Error al subir la imagen de portada.", "error");
            redirect("crear-foro.php");
        }
    } else {
        // Usar imagen por defecto
        $imagen_portada = "foros/portadas/default-foro.jpg";
    }
    
    // Verificar que las columnas existen antes de insertar
    $sql_check = "SHOW COLUMNS FROM foros LIKE 'descripcion'";
    $result_check = $conexion->query($sql_check);
    
    if ($result_check->num_rows == 0) {
        show_message("Error: La tabla foros no tiene la estructura correcta. Ejecuta el script de corrección primero.", "error");
        redirect("fix_foros_estructura.php");
    }
    
    // Insertar el foro en la base de datos
    $sql = "INSERT INTO foros (titulo, descripcion, imagen_portada, id_creador) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    
    if (!$stmt) {
        show_message("Error al preparar la consulta: " . $conexion->error, "error");
        redirect("crear-foro.php");
    }
    
    $stmt->bind_param("sssi", $titulo, $descripcion, $imagen_portada, $user_id);
    
    if ($stmt->execute()) {
        $id_foro = $conexion->insert_id;
        
        // Crear el archivo PHP del foro
        $nombre_archivo = "foro-" . $id_foro . ".php";
        $archivo_php = "foros/" . $nombre_archivo;
        
        // Actualizar la base de datos con el nombre del archivo
        $sql_update = "UPDATE foros SET archivo_php = ? WHERE id_foro = ?";
        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->bind_param("si", $archivo_php, $id_foro);
        $stmt_update->execute();
        
        // Crear el archivo PHP del foro
        if (crearArchivoForo($id_foro, $titulo, $descripcion, $archivo_php)) {
            // Verificar si existe la tabla foro_miembros
            $sql_check_table = "SHOW TABLES LIKE 'foro_miembros'";
            $result_check_table = $conexion->query($sql_check_table);
            
            if ($result_check_table->num_rows > 0) {
                // Unir automáticamente al creador al foro
                $sql_miembro = "INSERT INTO foro_miembros (id_foro, id_usuario, rol) VALUES (?, ?, 'admin')";
                $stmt_miembro = $conexion->prepare($sql_miembro);
                $stmt_miembro->bind_param("ii", $id_foro, $user_id);
                $stmt_miembro->execute();
                
                // Actualizar contador de miembros
                $sql_count = "UPDATE foros SET total_miembros = 1 WHERE id_foro = ?";
                $stmt_count = $conexion->prepare($sql_count);
                $stmt_count->bind_param("i", $id_foro);
                $stmt_count->execute();
            }
            
            show_message("Foro creado exitosamente.", "success");
            redirect($archivo_php);
        } else {
            show_message("Foro creado pero hubo un error al generar el archivo.", "warning");
            redirect("index.php");
        }
    } else {
        show_message("Error al crear el foro: " . $conexion->error, "error");
        redirect("crear-foro.php");
    }
}

// Función para subir imagen de portada
function subirImagenPortada($file) {
    $target_dir = "foros/portadas/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = "portada_" . time() . "_" . rand(1000, 9999) . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Verificar si es una imagen
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return false;
    }
    
    // Verificar tamaño (máximo 5MB)
    if ($file["size"] > 5000000) {
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

// Función para crear el archivo PHP del foro
function crearArchivoForo($id_foro, $titulo, $descripcion, $archivo_php) {
    $contenido = '<?php
// Archivo: ' . $archivo_php . '
// Foro: ' . htmlspecialchars($titulo) . '
require_once "../includes/init.php";

// Verificar si existe el archivo de funciones de foros
if (file_exists("../includes/foros_functions.php")) {
    require_once "../includes/foros_functions.php";
} else {
    // Funciones básicas si no existe el archivo
    function obtenerInfoForo($id_foro) {
        global $conexion;
        $sql = "SELECT f.*, u.nombre as nombre_creador FROM foros f LEFT JOIN usuarios u ON f.id_creador = u.id_usuario WHERE f.id_foro = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_foro);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    function esMiembroForo($id_foro, $user_id) {
        global $conexion;
        $sql = "SELECT id_miembro FROM foro_miembros WHERE id_foro = ? AND id_usuario = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $id_foro, $user_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    function esAdminForo($id_foro, $user_id) {
        global $conexion;
        $sql = "SELECT rol FROM foro_miembros WHERE id_foro = ? AND id_usuario = ? AND rol IN (\'admin\', \'moderador\')";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $id_foro, $user_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}

$id_foro = ' . $id_foro . ';
$titulo_foro = "' . addslashes($titulo) . '";
$descripcion_foro = "' . addslashes($descripcion) . '";

// Obtener información del foro
$foro_info = obtenerInfoForo($id_foro);
if (!$foro_info) {
    show_message("El foro no existe.", "error");
    redirect("../index.php");
}

// Verificar si el usuario está unido al foro
$user_id = $_SESSION["user_id"] ?? null;
$es_miembro = false;
$es_admin_foro = false;

if ($user_id) {
    // Verificar si existe la tabla foro_miembros
    $sql_check = "SHOW TABLES LIKE \'foro_miembros\'";
    $result_check = $conexion->query($sql_check);
    
    if ($result_check->num_rows > 0) {
        $es_miembro = esMiembroForo($id_foro, $user_id);
        $es_admin_foro = esAdminForo($id_foro, $user_id);
    } else {
        // Si no existe la tabla, permitir acceso básico
        $es_miembro = true;
    }
}

$es_admin_sitio = $user_id && function_exists("esAdminDirecto") && esAdminDirecto($user_id);

$page_title = $titulo_foro . " - Foro";
$include_base_css = true;

include "../includes/header.php";
?>

<div class="foro-container" style="max-width: 1200px; margin: 2rem auto; padding: 1rem;">
    <!-- Header del foro -->
    <div class="foro-header" style="background-color: rgba(255, 255, 255, 0.95); border-radius: 15px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2); border: 3px solid #0066cc; display: flex; gap: 2rem; align-items: center;">
        <div class="foro-portada" style="flex-shrink: 0;">
            <img src="../<?php echo $foro_info["imagen_portada"]; ?>" alt="Portada del foro" style="width: 150px; height: 150px; object-fit: cover; border-radius: 15px; border: 3px solid #0066cc;">
        </div>
        <div class="foro-info" style="flex: 1;">
            <h1 style="font-family: \'Bangers\', cursive; font-size: 2.5rem; color: #0066cc; text-shadow: 2px 2px 0 #000; margin-bottom: 1rem;"><?php echo htmlspecialchars($foro_info["titulo"]); ?></h1>
            <p style="font-size: 1.1rem; color: #333; margin-bottom: 1rem; line-height: 1.6;"><?php echo htmlspecialchars($foro_info["descripcion"]); ?></p>
            <div style="display: flex; gap: 2rem; margin-bottom: 1rem; flex-wrap: wrap;">
                <span style="font-family: \'Bangers\', cursive; font-size: 1.1rem; color: #666;">👥 <?php echo $foro_info["total_miembros"]; ?> miembros</span>
                <span style="font-family: \'Bangers\', cursive; font-size: 1.1rem; color: #666;">💬 <?php echo $foro_info["total_posts"]; ?> posts</span>
                <span style="font-family: \'Bangers\', cursive; font-size: 1.1rem; color: #666;">👤 Creado por: 
                    <a href="../perfil.php?id=<?php echo $foro_info["id_creador"]; ?>" style="color: #0066cc; text-decoration: none; font-weight: bold;">
                        <?php echo htmlspecialchars($foro_info["nombre_creador"]); ?>
                    </a>
                </span>
            </div>
        </div>
    </div>

    <!-- Mensaje de bienvenida -->
    <div style="background-color: rgba(255, 255, 255, 0.95); border-radius: 15px; padding: 2rem; text-align: center; border: 3px solid #0066cc;">
        <h2 style="font-family: \'Bangers\', cursive; font-size: 2rem; color: #0066cc; margin-bottom: 1rem;">¡Bienvenido al Foro!</h2>
        <p style="font-size: 1.1rem; color: #333; margin-bottom: 1.5rem;">Este foro ha sido creado exitosamente. El sistema completo de posts y comentarios estará disponible una vez que se complete la configuración de las tablas.</p>
        
        <?php if ($user_id): ?>
            <p style="color: #666; margin-bottom: 1rem;">Estás logueado como: <strong><?php echo htmlspecialchars($_SESSION["username"] ?? "Usuario"); ?></strong></p>
            <?php if ($es_admin_sitio): ?>
                <p style="color: #ff3366; font-weight: bold;">👑 Eres administrador del sitio</p>
            <?php endif; ?>
        <?php else: ?>
            <p style="margin-bottom: 1rem;"><a href="../ini_sec.php" style="color: #0066cc; font-weight: bold;">Iniciar sesión</a> para participar en este foro.</p>
        <?php endif; ?>
        
        <div style="margin-top: 2rem;">
            <a href="../index.php" style="display: inline-block; padding: 1rem 2rem; background-color: #0066cc; color: white; text-decoration: none; border-radius: 8px; font-family: \'Bangers\', cursive; font-size: 1.2rem; margin-right: 1rem;">🏠 Volver al Inicio</a>
            <a href="../crear_foros_tablas.php" style="display: inline-block; padding: 1rem 2rem; background-color: #28a745; color: white; text-decoration: none; border-radius: 8px; font-family: \'Bangers\', cursive; font-size: 1.2rem;">⚙️ Completar Configuración</a>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
';

    return file_put_contents($archivo_php, $contenido) !== false;
}

$page_title = "Crear Foro - Full Moon, Full Life";
$include_base_css = true;

include 'includes/header.php';
?>

<div class="crear-foro-container">
    <h1 class="crear-foro-title">Crear Nuevo Foro</h1>
    
    <form action="crear-foro.php" method="post" enctype="multipart/form-data" class="crear-foro-form">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        
        <div class="form-group">
            <label for="titulo">Título del Foro:</label>
            <input type="text" id="titulo" name="titulo" required maxlength="255" 
                   placeholder="Ej: Discusión sobre Series de Terror">
        </div>
        
        <div class="form-group">
            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" rows="4" 
                      placeholder="Describe de qué trata tu foro..."></textarea>
        </div>
        
        <div class="form-group">
            <label for="imagen_portada">Imagen de Portada (opcional):</label>
            <input type="file" id="imagen_portada" name="imagen_portada" accept="image/*">
            <small>Si no subes una imagen, se usará una por defecto.</small>
        </div>
        
        <div class="form-actions">
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">🚀 Crear Foro</button>
        </div>
    </form>
</div>

<style>
.crear-foro-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 2rem;
    background-color: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    border: 3px solid #0066cc;
}

.crear-foro-title {
    font-family: "Bangers", cursive;
    font-size: 3rem;
    color: #0066cc;
    text-shadow: 2px 2px 0 #000;
    text-align: center;
    margin-bottom: 2rem;
}

.crear-foro-form {
    background-color: white;
    padding: 2rem;
    border-radius: 10px;
    border: 2px solid #0066cc;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-family: "Bangers", cursive;
    font-size: 1.3rem;
    color: #0066cc;
    margin-bottom: 0.5rem;
    text-shadow: 1px 1px 0 #000;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 1rem;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    font-family: Arial, sans-serif;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: #0066cc;
    outline: none;
    box-shadow: 0 0 5px rgba(0, 102, 204, 0.3);
}

.form-group small {
    display: block;
    color: #666;
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
}

.btn {
    padding: 1rem 2rem;
    border: none;
    border-radius: 8px;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: all 0.3s;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.3);
}

.btn-primary {
    background-color: #0066cc;
    color: white;
    text-shadow: 1px 1px 0 #000;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.3);
}

@media (max-width: 768px) {
    .crear-foro-container {
        margin: 1rem;
        padding: 1rem;
    }
    
    .crear-foro-title {
        font-size: 2.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
