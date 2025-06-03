<?php
// Archivo: crear-resena.php
// Incluir el archivo de inicialización (esto incluye todo lo necesario)
require_once 'includes/init.php';

// Verificar que el usuario esté logueado antes de permitir crear reseñas
if (!is_logged_in()) {
    show_message("Debes iniciar sesión para crear una reseña.", "error");
    redirect("ini_sec.php");
}

// Función para sanitizar nombres de archivo
function sanitize_filename($filename) {
    // Convertir a minúsculas
    $filename = strtolower($filename);
    
    // Reemplazar caracteres especiales y espacios
    $filename = preg_replace('/[^a-z0-9\-_]/', '-', $filename);
    
    // Reemplazar múltiples guiones con uno solo
    $filename = preg_replace('/-+/', '-', $filename);
    
    // Eliminar guiones al inicio y final
    $filename = trim($filename, '-');
    
    // Limitar longitud
    $filename = substr($filename, 0, 50);
    
    return $filename;
}

// Función para escapar cadenas para PHP
function escape_for_php($string) {
    // Escapar comillas simples y barras invertidas
    return str_replace(['\\', "'"], ['\\\\', "\\'"], $string);
}

// Función para decodificar entidades HTML
function decode_html_entities($text) {
    return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar token CSRF
    if (!verify_csrf_token($_POST['csrf_token'])) {
        show_message("Token de seguridad inválido.", "error");
        redirect("crear-resena.php");
    }
    
    // Recoger datos del formulario y decodificar entidades HTML
    $titulo = decode_html_entities(sanitize($_POST['titulo']));
    $id_usuario = $_SESSION['user_id']; // Usar el ID del usuario logueado
    $categoria = sanitize($_POST['categoria']); // serie, libro o película
    $calificacion = (int)$_POST['calificacion'];
    $descripcion = decode_html_entities(sanitize($_POST['descripcion']));
    $pros = decode_html_entities(sanitize($_POST['pros']));
    $contras = decode_html_entities(sanitize($_POST['contras']));
    $recomendacion = isset($_POST['recomendacion']) ? decode_html_entities(sanitize($_POST['recomendacion'])) : 'no'; // Valor por defecto 'no'
    
    // Campos adicionales según la categoría
    $temporadas = ($categoria == 'serie') ? (int)$_POST['temporadas'] : NULL;
    $episodios = ($categoria == 'serie') ? (int)$_POST['episodios'] : NULL;
    $duracion = ($categoria == 'pelicula') ? (int)$_POST['duracion'] : NULL;
    $paginas = ($categoria == 'libro') ? (int)$_POST['paginas'] : NULL;
    
    // Fecha actual
    $fecha_creacion = date('Y-m-d H:i:s');
    $fecha_actualizacion = $fecha_creacion;
    
    // Validar que se haya seleccionado una imagen
    if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] == UPLOAD_ERR_NO_FILE) {
        show_message("Debes subir una imagen de portada.", "error");
        redirect("crear-resena.php");
    }
    
    // Procesar la imagen de portada
    $target_dir = "assets/img/portadas/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = pathinfo($_FILES["imagen"]["name"], PATHINFO_EXTENSION);
    $new_filename = sanitize_filename($titulo) . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    $imagenes_json = json_encode([$target_file]); // Convierte a formato JSON válido
    
    // Verificar que sea una imagen válida
    $check = getimagesize($_FILES["imagen"]["tmp_name"]);
    if($check === false) {
        show_message("El archivo no es una imagen válida.", "error");
        redirect("crear-resena.php");
    }
    
    // Limitar tipos de archivo
    if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg") {
        show_message("Solo se permiten archivos JPG, JPEG o PNG.", "error");
        redirect("crear-resena.php");
    }
    
    // Subir la imagen
    if (!move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
        show_message("Error al subir la imagen.", "error");
        redirect("crear-resena.php");
    }
    
    // Crear el nombre del archivo para la reseña usando la función de sanitización
    $filename = sanitize_filename($titulo);
    
    // Determinar la carpeta según la categoría
    switch ($categoria) {
        case 'serie':
            $folder = "series";
            break;
        case 'pelicula':
            $folder = "peliculas";
            break;
        case 'libro':
            $folder = "libros";
            break;
        default:
            $folder = "otros";
    }
    
    // Ruta completa del archivo
    $archivo_php = "resenas/" . $folder . "/" . $filename . ".php";
    
    // Insertar en la base de datos
    $sql = "INSERT INTO resenas (id_usuario, titulo, categoria, calificacion, pros, contras, descripcion, 
            recomendacion, imagenes, temporadas, episodios, duracion, paginas, archivo_php, fecha_creacion, fecha_actualizacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("issdsssssiiiisss", $id_usuario, $titulo, $categoria, $calificacion, $pros, $contras, $descripcion, 
                  $recomendacion, $imagenes_json, $temporadas, $episodios, $duracion, $paginas, $archivo_php, $fecha_creacion, $fecha_actualizacion);
    
    if ($stmt->execute()) {
        // Crear el archivo PHP de la reseña
        $id_resena = $conexion->insert_id;
        
        // Asegurarse de que la carpeta principal de reseñas existe
        if (!file_exists("resenas")) {
            mkdir("resenas", 0777, true);
        }
        
        // Asegurarse de que la carpeta de categoría existe
        $categoria_path = "resenas/" . $folder;
        if (!file_exists($categoria_path)) {
            mkdir($categoria_path, 0777, true);
        }
        
        // Verificar permisos de la carpeta
        if (!is_writable($categoria_path)) {
            chmod($categoria_path, 0777);
        }
        
        // Escapar todas las cadenas para PHP
        $titulo_escaped = escape_for_php($titulo);
        $categoria_escaped = escape_for_php($categoria);
        $descripcion_escaped = escape_for_php($descripcion);
        $pros_escaped = escape_for_php($pros);
        $contras_escaped = escape_for_php($contras);
        $recomendacion_escaped = escape_for_php($recomendacion);
        $imagen_escaped = escape_for_php($target_file);
        $fecha_escaped = escape_for_php($fecha_creacion);
        
        // Contenido del archivo de reseña basado en la plantilla
        $contenido = '<?php
// Incluir inicialización
require_once "../../includes/init.php";

// Datos de la reseña
$id_resena = ' . $id_resena . ';
$titulo = \'' . $titulo_escaped . '\';
$categoria = \'' . $categoria_escaped . '\';
$calificacion = ' . $calificacion . ';
$descripcion = \'' . $descripcion_escaped . '\';
$pros = \'' . $pros_escaped . '\';
$contras = \'' . $contras_escaped . '\';
$recomendacion = \'' . $recomendacion_escaped . '\';
$imagen = \'' . $imagen_escaped . '\';
$fecha_creacion = \'' . $fecha_escaped . '\';
$id_usuario = ' . $id_usuario . '; // ID del usuario que creó la reseña
$temporadas = ' . ($temporadas ? $temporadas : 'null') . ';
$episodios = ' . ($episodios ? $episodios : 'null') . ';
$duracion = ' . ($duracion ? $duracion : 'null') . ';
$paginas = ' . ($paginas ? $paginas : 'null') . ';

// Obtener datos del usuario
$sql = "SELECT nombre FROM usuarios WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $nombre_usuario = $usuario ? $usuario["nombre"] : "Usuario Anónimo";
    $stmt->close();
} else {
    $nombre_usuario = "Usuario Anónimo";
}

// Configurar variables para el header
$page_title = $titulo . " - Full Moon, Full Life";
$include_base_css = true;
$base_path = "../../"; // Ruta base para los recursos

// Incluir header
include "../../includes/header.php";
?>

<!-- Contenedor principal de la reseña -->
<div class="container">
    <div class="review-container">
        <!-- Título de la reseña -->
        <div class="review-header">
            <h1 class="review-title"><?php echo $titulo; ?></h1>
            <div class="review-meta">
                <span class="review-category"><?php echo ucfirst($categoria); ?></span>
                <span class="review-date">Publicado: <?php echo date("d/m/Y", strtotime($fecha_creacion)); ?></span>
                <span class="review-author">Por: <a href="../../perfil.php?id=<?php echo $id_usuario; ?>" class="author-link"><?php echo $nombre_usuario; ?></a></span>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="review-content">
            <!-- Sidebar con imagen y datos -->
            <div class="review-sidebar">
                <div class="review-image-container">
                    <img src="../../<?php echo $imagen; ?>" alt="Portada de <?php echo $titulo; ?>" class="review-image">
                    <div class="review-rating">
                        <span class="rating-number"><?php echo $calificacion; ?></span>
                    </div>
                </div>

                <!-- Calificación con estrellas -->
                <div class="review-stars-container">
                    <div class="rating-stars">
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $calificacion) {
                                echo "<span class=\"star filled\">★</span>";
                            } else {
                                echo "<span class=\"star\">☆</span>";
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="review-info">
                    <?php if ($categoria == "serie" && $temporadas && $episodios): ?>
                    <div class="info-item">
                        <strong>Temporadas:</strong> <?php echo $temporadas; ?>
                    </div>
                    <div class="info-item">
                        <strong>Episodios:</strong> <?php echo $episodios; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($categoria == "pelicula" && $duracion): ?>
                    <div class="info-item">
                        <strong>Duración:</strong> <?php echo $duracion; ?> minutos
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($categoria == "libro" && $paginas): ?>
                    <div class="info-item">
                        <strong>Páginas:</strong> <?php echo $paginas; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Botones de acción para el propietario -->
                <?php if (is_logged_in() && $_SESSION["user_id"] == $id_usuario): ?>
                <div class="review-actions">
                    <a href="../../editar-resena.php?id=<?php echo $id_resena; ?>" class="btn btn-edit">Editar</a>
                    <a href="../../eliminar-resena.php?id=<?php echo $id_resena; ?>&token=<?php echo generate_csrf_token(); ?>" 
                       class="btn btn-delete" 
                       onclick="return confirm(\'¿Estás seguro de que deseas eliminar esta reseña?\')">Eliminar</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Contenido de texto -->
            <div class="review-text">
                <section class="review-section">
                    <h2>Descripción</h2>
                    <p><?php echo nl2br($descripcion); ?></p>
                </section>

                <section class="review-section">
                    <h2>Lo Bueno</h2>
                    <div class="pros-section">
                        <?php echo nl2br($pros); ?>
                    </div>
                </section>

                <section class="review-section">
                    <h2>Lo Malo</h2>
                    <div class="cons-section">
                        <?php echo nl2br($contras); ?>
                    </div>
                </section>

                <?php if (!empty($recomendacion) && $recomendacion != "no"): ?>
                <section class="review-section">
                    <h2>Recomendación</h2>
                    <div class="recommendation-section">
                        <?php 
                        if ($recomendacion == "si") {
                            echo "¡Sí, definitivamente recomiendo esta obra!";
                        } else {
                            echo nl2br($recomendacion); 
                        }
                        ?>
                    </div>
                </section>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sección de comentarios -->
        <div class="comments-section">
            <h2>Comentarios</h2>
            
            <?php if (is_logged_in()): ?>
            <div class="comment-form">
                <form action="../../procesar-comentario.php" method="post">
                    <input type="hidden" name="id_resena" value="<?php echo $id_resena; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <textarea name="comentario" placeholder="Escribe tu comentario..." rows="4" required></textarea>
                    <div class="comment-form-footer">
                        <div class="rating-select">
                            <label for="rating">Tu calificación:</label>
                            <select name="rating" id="rating" required>
                                <option value="5">5 - Excelente</option>
                                <option value="4">4 - Bueno</option>
                                <option value="3">3 - Regular</option>
                                <option value="2">2 - Malo</option>
                                <option value="1">1 - Muy malo</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Comentar</button>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <div class="login-prompt">
                <p>Debes <a href="../../ini_sec.php">iniciar sesión</a> para comentar.</p>
            </div>
            <?php endif; ?>
            
            <?php
            // Obtener comentarios de la reseña
            $sql_comentarios = "SELECT c.*, u.nombre as nombre_usuario, u.avatar 
                               FROM comentarios c 
                               LEFT JOIN usuarios u ON c.id_usuario = u.id_usuario 
                               WHERE c.id_resena = ? 
                               ORDER BY c.fecha_creacion DESC";
            $stmt_comentarios = $conexion->prepare($sql_comentarios);
            
            if ($stmt_comentarios) {
                $stmt_comentarios->bind_param("i", $id_resena);
                $stmt_comentarios->execute();
                $result_comentarios = $stmt_comentarios->get_result();
                
                if ($result_comentarios->num_rows > 0) {
                    echo "<div class=\"comments-list\">";
                    while ($comentario = $result_comentarios->fetch_assoc()) {
                        $avatar = $comentario["avatar"] ? $comentario["avatar"] : "../../assets/img/avatars/default.png";
                        $nombre_comentador = $comentario["nombre_usuario"] ? $comentario["nombre_usuario"] : "Usuario Anónimo";
                        $calificacion_comentario = $comentario["calificacion"];
                        $texto_comentario = $comentario["texto"];
                        $id_comentador = $comentario["id_usuario"];
                        ?>
                        <div class="comment-item">
                            <div class="comment-header">
                                <img src="<?php echo $avatar; ?>" alt="Avatar" class="comment-avatar">
                                <div class="comment-info">
                                    <span class="comment-author">
                                        <a href="../../perfil.php?id=<?php echo $id_comentador; ?>" class="author-link">
                                            <?php echo $nombre_comentador; ?>
                                        </a>
                                    </span>
                                    <div class="comment-rating">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $calificacion_comentario) {
                                                echo "<span class=\"star filled\">★</span>";
                                            } else {
                                                echo "<span class=\"star\">☆</span>";
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="comment-content">
                                <?php echo nl2br($texto_comentario); ?>
                            </div>
                            <?php if (is_logged_in() && ($_SESSION["user_id"] == $comentario["id_usuario"] || is_admin())): ?>
                            <div class="comment-actions">
                                <a href="../../editar-comentario.php?id=<?php echo $comentario["id_comentario"]; ?>">Editar</a>
                                <a href="../../eliminar-comentario.php?id=<?php echo $comentario["id_comentario"]; ?>&token=<?php echo generate_csrf_token(); ?>" 
                                   onclick="return confirm(\'¿Estás seguro de que deseas eliminar este comentario?\')">Eliminar</a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php
                    }
                    echo "</div>";
                } else {
                    echo "<div class=\"no-comments\">No hay comentarios aún. ¡Sé el primero en comentar!</div>";
                }
                
                $stmt_comentarios->close();
            }
            ?>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para la página de reseña */
.review-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    background-color: rgba(255, 255, 255, 0.95);
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.review-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #0066cc;
}

.review-title {
    font-family: "Bangers", cursive;
    font-size: 3rem;
    color: #0066cc;
    text-shadow: 2px 2px 0 #000;
    margin: 0 0 1rem 0;
    letter-spacing: 2px;
}

.review-meta {
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    color: #333;
}

.review-category {
    background-color: #0066cc;
    color: white;
    padding: 0.3rem 1rem;
    border-radius: 20px;
    text-transform: uppercase;
}

.author-link {
    color: #0066cc;
    text-decoration: none;
    transition: all 0.2s ease;
    font-weight: bold;
}

.author-link:hover {
    color: #ff3366;
    text-decoration: underline;
}

.review-content {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    margin-bottom: 3rem;
}

.review-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.review-image-container {
    position: relative;
    background-color: white;
    border: 3px solid #0066cc;
    border-radius: 10px;
    padding: 1rem;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.review-image {
    width: 100%;
    height: auto;
    border-radius: 5px;
    display: block;
}

.review-rating {
    position: absolute;
    top: -15px;
    right: -15px;
    background-color: #ff3366;
    color: white;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid white;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
}

.rating-number {
    font-family: "Bangers", cursive;
    font-size: 2rem;
    font-weight: bold;
    line-height: 1;
}

.review-stars-container {
    text-align: center;
    background-color: #f8f9fa;
    border: 2px solid #0066cc;
    border-radius: 10px;
    padding: 0.5rem;
}

.rating-stars {
    font-size: 1.5rem;
    line-height: 1;
}

.star {
    color: #ddd;
    margin: 0 2px;
}

.star.filled {
    color: #ffcc00;
}

.review-info {
    background-color: #f8f9fa;
    border: 2px solid #0066cc;
    border-radius: 10px;
    padding: 1rem;
}

.info-item {
    margin-bottom: 0.5rem;
    font-family: Arial, sans-serif;
}

.review-actions {
    display: flex;
    gap: 0.5rem;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-family: "Bangers", cursive;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}

.btn-edit {
    background-color: #0066cc;
    color: white;
}

.btn-delete {
    background-color: #dc3545;
    color: white;
}

.btn-primary {
    background-color: #0066cc;
    color: white;
}

.btn:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

.review-text {
    background-color: white;
    border: 2px solid #0066cc;
    border-radius: 10px;
    padding: 2rem;
}

.review-section {
    margin-bottom: 2rem;
}

.review-section h2 {
    font-family: "Bangers", cursive;
    font-size: 2rem;
    color: #0066cc;
    text-shadow: 1px 1px 0 #000;
    margin-bottom: 1rem;
    letter-spacing: 1px;
}

.pros-section {
    background-color: #d4edda;
    border-left: 4px solid #28a745;
    padding: 1rem;
    border-radius: 5px;
}

.cons-section {
    background-color: #f8d7da;
    border-left: 4px solid #dc3545;
    padding: 1rem;
    border-radius: 5px;
}

.recommendation-section {
    background-color: #d1ecf1;
    border-left: 4px solid #17a2b8;
    padding: 1rem;
    border-radius: 5px;
}

.comments-section {
    background-color: white;
    border: 2px solid #0066cc;
    border-radius: 10px;
    padding: 2rem;
}

.comments-section h2 {
    font-family: "Bangers", cursive;
    font-size: 2rem;
    color: #0066cc;
    text-shadow: 1px 1px 0 #000;
    margin-bottom: 1.5rem;
    text-align: center;
}

.comment-form {
    margin-bottom: 2rem;
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 10px;
}

.comment-form textarea {
    width: 100%;
    padding: 1rem;
    border: 2px solid #ddd;
    border-radius: 5px;
    font-family: Arial, sans-serif;
    resize: vertical;
    margin-bottom: 1rem;
}

.comment-form-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.rating-select {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.rating-select select {
    padding: 0.5rem;
    border: 2px solid #ddd;
    border-radius: 5px;
}

.login-prompt {
    text-align: center;
    padding: 2rem;
    background-color: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 2rem;
}

.login-prompt a {
    color: #0066cc;
    font-weight: bold;
}

.comments-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.comment-item {
    background-color: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 1rem;
}

.comment-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
}

.comment-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #0066cc;
    object-fit: cover;
}

.comment-info {
    flex: 1;
}

.comment-author {
    font-weight: bold;
    color: #0066cc;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
}

.comment-rating {
    font-size: 1rem;
}

.comment-content {
    margin: 0.5rem 0;
    line-height: 1.6;
}

.comment-actions {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
}

.comment-actions a {
    color: #0066cc;
    text-decoration: none;
    font-size: 0.9rem;
}

.comment-actions a:hover {
    text-decoration: underline;
}

.no-comments {
    text-align: center;
    padding: 2rem;
    color: #666;
    font-style: italic;
}

@media (max-width: 768px) {
    .review-content {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .review-title {
        font-size: 2rem;
    }
    
    .review-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .comment-form-footer {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
}
</style>

<?php include "../../includes/footer.php"; ?>';
        
        // Guardar el archivo
        if (!file_put_contents($archivo_php, $contenido)) {
            show_message("Error al crear el archivo de la reseña. Verifica los permisos de escritura.", "error");
            redirect("crear-resena.php");
        }
        
        show_message("Reseña creada correctamente.", "success");
        redirect($archivo_php);
    } else {
        show_message("Error al crear la reseña: " . $stmt->error, "error");
        redirect("crear-resena.php");
    }
    
    $stmt->close();
}

// Configurar variables para el header
$page_title = "Crear Nueva Reseña - Full Moon, Full Life";
$include_base_css = true;
$additional_css = ["crear-resena.css", "stylesregis.css"];

// Incluir header
include 'includes/header.php';
?>

<div class="form-container">
    <h1 class="form-title">Crear Nueva Reseña</h1>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <!-- Token CSRF para seguridad -->
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        
        <div class="form-section">
            <h2 class="form-section-title">Información Básica</h2>
            
            <div class="form-group">
                <label for="titulo" class="form-label">Título de la Obra:</label>
                <input type="text" id="titulo" name="titulo" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="categoria" class="form-label">Categoría:</label>
                <select id="categoria" name="categoria" class="form-select" required onchange="mostrarCamposAdicionales()">
                    <option value="">Selecciona una categoría</option>
                    <option value="pelicula">Película</option>
                    <option value="serie">Serie</option>
                    <option value="libro">Libro</option>
                </select>
            </div>
            
            <!-- Campos específicos por categoría -->
            <div id="campos-serie" class="category-fields">
                <div class="form-row">
                    <div class="form-col">
                        <label for="temporadas" class="form-label">Temporadas:</label>
                        <input type="number" id="temporadas" name="temporadas" class="form-input" min="1">
                    </div>
                    <div class="form-col">
                        <label for="episodios" class="form-label">Episodios:</label>
                        <input type="number" id="episodios" name="episodios" class="form-input" min="1">
                    </div>
                </div>
            </div>
            
            <div id="campos-pelicula" class="category-fields">
                <div class="form-group">
                    <label for="duracion" class="form-label">Duración (minutos):</label>
                    <input type="number" id="duracion" name="duracion" class="form-input" min="1">
                </div>
            </div>
            
            <div id="campos-libro" class="category-fields">
                <div class="form-group">
                    <label for="paginas" class="form-label">Número de páginas:</label>
                    <input type="number" id="paginas" name="paginas" class="form-input" min="1">
                </div>
            </div>
            
            <div class="form-group">
                <label for="imagen" class="form-label">Imagen de Portada:</label>
                <input type="file" id="imagen" name="imagen" class="form-input" accept="image/jpeg,image/png" required>
                <p style="font-size: 0.9rem; color: #666; margin-top: 0.5rem;">Solo se permite una imagen en formato JPG, JPEG o PNG.</p>
            </div>
        </div>
        
        <div class="form-section">
            <h2 class="form-section-title">Contenido de la Reseña</h2>
            
            <div class="form-group">
                <label for="descripcion" class="form-label">Descripción:</label>
                <textarea id="descripcion" name="descripcion" class="form-textarea" rows="4" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="pros" class="form-label">Lo Bueno:</label>
                <textarea id="pros" name="pros" class="form-textarea" rows="4" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="contras" class="form-label">Lo Malo:</label>
                <textarea id="contras" name="contras" class="form-textarea" rows="4" required></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">¿Recomendarías esta obra?</label>
                <div class="recomendacion-options">
                    <label class="radio-option">
                        <input type="radio" name="recomendacion" value="si" checked> Sí
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="recomendacion" value="no"> No
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="calificacion" class="form-label">Calificación:</label>
                <div class="star-rating">
                    <div class="star-container">
                        <input type="radio" id="star5" name="calificacion" value="5" required>
                        <label for="star5" title="5 estrellas">★</label>
                        <input type="radio" id="star4" name="calificacion" value="4">
                        <label for="star4" title="4 estrellas">★</label>
                        <input type="radio" id="star3" name="calificacion" value="3">
                        <label for="star3" title="3 estrellas">★</label>
                        <input type="radio" id="star2" name="calificacion" value="2">
                        <label for="star2" title="2 estrellas">★</label>
                        <input type="radio" id="star1" name="calificacion" value="1">
                        <label for="star1" title="1 estrella">★</label>
                    </div>
                    <span id="rating-text">Selecciona una calificación</span>
                </div>
            </div>
        </div>
        
        <div class="form-buttons">
            <a href="index.php" class="btn-cancelar">Cancelar</a>
            <button type="submit" class="btn-publicar">Crear Reseña</button>
        </div>
    </form>
</div>

<style>
/* Estilos para el sistema de calificación con estrellas */
.star-rating {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.star-container {
    display: inline-flex;
    flex-direction: row-reverse;
    margin-bottom: 10px;
}

.star-container input {
    display: none;
}

.star-container label {
    font-size: 2.5rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}

.star-container label:hover,
.star-container label:hover ~ label,
.star-container input:checked ~ label {
    color: #ffcc00;
    text-shadow: 0 0 5px rgba(255, 204, 0, 0.5);
}

#rating-text {
    font-size: 1rem;
    color: #666;
}

/* Estilos para los campos específicos por categoría */
.category-fields {
    display: none;
    margin-top: 1rem;
    padding: 1rem;
    background-color: rgba(0, 102, 204, 0.1);
    border-radius: 10px;
    border: 2px solid #0066cc;
}

.category-fields.active {
    display: block;
}

.form-row {
    display: flex;
    gap: 1rem;
}

.form-col {
    flex: 1;
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        gap: 1rem;
    }
    
    .form-buttons {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn-cancelar, .btn-publicar {
        width: 100%;
        text-align: center;
    }
}
</style>

<script>
function mostrarCamposAdicionales() {
    // Ocultar todos los campos específicos
    document.getElementById('campos-serie').classList.remove('active');
    document.getElementById('campos-pelicula').classList.remove('active');
    document.getElementById('campos-libro').classList.remove('active');
    
    // Mostrar campos según la categoría seleccionada
    var categoria = document.getElementById('categoria').value;
    
    if (categoria === 'serie') {
        document.getElementById('campos-serie').classList.add('active');
    } else if (categoria === 'pelicula') {
        document.getElementById('campos-pelicula').classList.add('active');
    } else if (categoria === 'libro') {
        document.getElementById('campos-libro').classList.add('active');
    }
}

// Script para actualizar el texto de calificación
document.addEventListener('DOMContentLoaded', function() {
    const ratingInputs = document.querySelectorAll('input[name="calificacion"]');
    const ratingText = document.getElementById('rating-text');
    const ratingTexts = {
        '5': '5 - Excelente',
        '4': '4 - Bueno',
        '3': '3 - Regular',
        '2': '2 - Malo',
        '1': '1 - Muy malo'
    };
    
    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            ratingText.textContent = ratingTexts[this.value];
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>