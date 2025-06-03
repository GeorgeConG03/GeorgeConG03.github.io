<?php
// Página para ver una reseña con estilo comic y sistema de comentarios
require_once __DIR__ . '/includes/init.php';

// Verificar si se proporcionó un ID de reseña
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    show_message('ID de reseña no válido', 'error');
    header('Location: resenas.php');
    exit;
}

$id_resena = (int)$_GET['id'];

// Obtener datos de la reseña
$resena = null;
$conexion = new mysqli("localhost", "root", "bri_gitte_03", "here'stoyou");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta para obtener la reseña
$query = "SELECT r.*, u.nombre as nombre_usuario, u.id as id_usuario 
          FROM resenas r 
          JOIN usuarios u ON r.id_usuario = u.id 
          WHERE r.id_resena = ?";
$stmt = $conexion->prepare($query);

if ($stmt) {
    $stmt->bind_param("i", $id_resena);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $resena = $result->fetch_assoc();
    } else {
        show_message('Reseña no encontrada', 'error');
        header('Location: resenas.php');
        exit;
    }
    
    $stmt->close();
} else {
    show_message('Error al cargar la reseña', 'error');
    header('Location: resenas.php');
    exit;
}

// Verificar si el usuario actual es administrador
$es_admin = false;
if (is_logged_in() && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM administradores WHERE id_admin = ?";
    $stmt = $conexion->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $es_admin = true;
        }
        
        $stmt->close();
    }
}

// Procesar acciones de comentarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    
    // Añadir comentario
    if (isset($_POST['action']) && $_POST['action'] === 'comentar') {
        $contenido = trim($_POST['contenido'] ?? '');
        
        if (!empty($contenido)) {
            $query = "INSERT INTO comentarios (id_usuario, id_resena, contenido, fecha) VALUES (?, ?, ?, NOW())";
            $stmt = $conexion->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("iis", $user_id, $id_resena, $contenido);
                
                if ($stmt->execute()) {
                    show_message('Comentario añadido correctamente', 'success');
                } else {
                    show_message('Error al añadir el comentario', 'error');
                }
                
                $stmt->close();
            }
        } else {
            show_message('El comentario no puede estar vacío', 'error');
        }
    }
    
    // Dar like/dislike
    if (isset($_POST['action']) && ($_POST['action'] === 'like' || $_POST['action'] === 'dislike')) {
        $id_comentario = (int)$_POST['id_comentario'];
        $tipo = $_POST['action'] === 'like' ? 1 : 0;
        
        // Verificar si ya existe un like/dislike del usuario
        $query = "SELECT * FROM likes_comentarios WHERE id_usuario = ? AND id_comentario = ?";
        $stmt = $conexion->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("ii", $user_id, $id_comentario);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Actualizar like/dislike existente
                $query = "UPDATE likes_comentarios SET tipo = ? WHERE id_usuario = ? AND id_comentario = ?";
                $stmt = $conexion->prepare($query);
                
                if ($stmt) {
                    $stmt->bind_param("iii", $tipo, $user_id, $id_comentario);
                    $stmt->execute();
                    $stmt->close();
                }
            } else {
                // Insertar nuevo like/dislike
                $query = "INSERT INTO likes_comentarios (id_usuario, id_comentario, tipo) VALUES (?, ?, ?)";
                $stmt = $conexion->prepare($query);
                
                if ($stmt) {
                    $stmt->bind_param("iii", $user_id, $id_comentario, $tipo);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            
            $stmt->close();
        }
    }
    
    // Borrar comentario (solo admin o propietario)
    if (isset($_POST['action']) && $_POST['action'] === 'borrar_comentario' && isset($_POST['id_comentario'])) {
        $id_comentario = (int)$_POST['id_comentario'];
        
        // Verificar si el usuario es admin o propietario del comentario
        $query = "SELECT id_usuario FROM comentarios WHERE id_comentario = ?";
        $stmt = $conexion->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("i", $id_comentario);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $comentario = $result->fetch_assoc();
                
                if ($es_admin || $comentario['id_usuario'] === $user_id) {
                    // Borrar likes del comentario
                    $query = "DELETE FROM likes_comentarios WHERE id_comentario = ?";
                    $stmt = $conexion->prepare($query);
                    
                    if ($stmt) {
                        $stmt->bind_param("i", $id_comentario);
                        $stmt->execute();
                        $stmt->close();
                    }
                    
                    // Borrar el comentario
                    $query = "DELETE FROM comentarios WHERE id_comentario = ?";
                    $stmt = $conexion->prepare($query);
                    
                    if ($stmt) {
                        $stmt->bind_param("i", $id_comentario);
                        
                        if ($stmt->execute()) {
                            show_message('Comentario eliminado correctamente', 'success');
                        } else {
                            show_message('Error al eliminar el comentario', 'error');
                        }
                        
                        $stmt->close();
                    }
                } else {
                    show_message('No tienes permiso para eliminar este comentario', 'error');
                }
            }
            
            $stmt->close();
        }
    }
    
    // Banear usuario (solo admin)
    if (isset($_POST['action']) && $_POST['action'] === 'banear_usuario' && isset($_POST['id_usuario']) && $es_admin) {
        $id_usuario_banear = (int)$_POST['id_usuario'];
        
        // Verificar que no sea un admin
        $query = "SELECT * FROM administradores WHERE id_admin = ?";
        $stmt = $conexion->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("i", $id_usuario_banear);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                // No es admin, se puede banear
                $query = "UPDATE usuarios SET estado = 'baneado' WHERE id = ?";
                $stmt = $conexion->prepare($query);
                
                if ($stmt) {
                    $stmt->bind_param("i", $id_usuario_banear);
                    
                    if ($stmt->execute()) {
                        show_message('Usuario baneado correctamente', 'success');
                    } else {
                        show_message('Error al banear al usuario', 'error');
                    }
                    
                    $stmt->close();
                }
            } else {
                show_message('No puedes banear a un administrador', 'error');
            }
            
            $stmt->close();
        }
    }
    
    // Redireccionar para evitar reenvío del formulario
    header("Location: ver-resena.php?id=" . $id_resena);
    exit;
}

// Obtener comentarios de la reseña
$comentarios = [];
$query = "SELECT c.*, u.nombre as nombre_usuario, u.id as id_usuario, 
          (SELECT COUNT(*) FROM likes_comentarios WHERE id_comentario = c.id_comentario AND tipo = 1) as likes,
          (SELECT COUNT(*) FROM likes_comentarios WHERE id_comentario = c.id_comentario AND tipo = 0) as dislikes,
          (SELECT tipo FROM likes_comentarios WHERE id_comentario = c.id_comentario AND id_usuario = ?) as user_reaction
          FROM comentarios c 
          JOIN usuarios u ON c.id_usuario = u.id 
          WHERE c.id_resena = ? 
          ORDER BY c.fecha DESC";
$stmt = $conexion->prepare($query);

if ($stmt) {
    $user_id = is_logged_in() ? $_SESSION['user_id'] : 0;
    $stmt->bind_param("ii", $user_id, $id_resena);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $comentarios[] = $row;
    }
    
    $stmt->close();
}

// Obtener avatar de los usuarios para los comentarios
foreach ($comentarios as &$comentario) {
    $query = "SELECT avatar FROM perfiles WHERE id_usuario = ?";
    $stmt = $conexion->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $comentario['id_usuario']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $perfil = $result->fetch_assoc();
            $comentario['avatar'] = $perfil['avatar'] ?? 'assets/img/default-avatar.png';
        } else {
            $comentario['avatar'] = 'assets/img/default-avatar.png';
        }
        
        $stmt->close();
    }
}

// Cerrar conexión
$conexion->close();

// Título de la página
$page_title = $resena ? $resena['titulo'] . " - Full Moon, Full Life" : "Reseña - Full Moon, Full Life";

// Incluir estilos base
$include_base_css = true;

// Estilos adicionales
$page_styles = '
/* Estilos para reseña estilo comic en azul */
.review-comic-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 2rem;
    position: relative;
}

.review-comic-title {
    background-color: white;
    border: 4px solid black;
    border-radius: 20px;
    padding: 1rem 2rem;
    margin: 0 auto 2rem;
    max-width: 600px;
    text-align: center;
    box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.7);
    position: relative;
}

.review-comic-title::before {
    content: "";
    position: absolute;
    bottom: -20px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 20px solid transparent;
    border-right: 20px solid transparent;
    border-top: 20px solid white;
    filter: drop-shadow(4px 4px 0 rgba(0, 0, 0, 0.7));
}

.review-comic-title::after {
    content: "";
    position: absolute;
    bottom: -24px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 24px solid transparent;
    border-right: 24px solid transparent;
    border-top: 24px solid black;
    z-index: -1;
}

.review-comic-title h1 {
    font-family: "Bangers", cursive;
    font-size: 2.5rem;
    color: black;
    text-shadow: 3px 3px 0 #0066cc;
    margin: 0;
    letter-spacing: 2px;
}

.review-main-content {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 2rem;
    margin-top: 3rem;
}

.review-text-content {
    background-color: white;
    border: 4px solid #0066cc;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.7);
    position: relative;
}

.review-text-content::before {
    content: "";
    position: absolute;
    top: -8px;
    left: -8px;
    right: -8px;
    bottom: -8px;
    border: 4px solid black;
    border-radius: 20px;
    z-index: -1;
}

.review-text {
    font-size: 1.1rem;
    line-height: 1.6;
    color: black;
    text-align: justify;
}

.review-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.review-cover {
    background-color: white;
    border: 4px solid black;
    border-radius: 15px;
    padding: 1rem;
    box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.7);
    position: relative;
}

.review-cover img {
    width: 100%;
    height: auto;
    border-radius: 10px;
    display: block;
}

.review-score-container {
    position: absolute;
    top: -15px;
    right: -15px;
    background-color: #0066cc;
    color: white;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 4px solid black;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: "Bangers", cursive;
    font-size: 2rem;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    z-index: 10;
}

.review-rating {
    text-align: center;
    margin: 1rem 0;
}

.review-stars {
    font-size: 2rem;
    color: #ffcc00;
    text-shadow: 1px 1px 0 black;
}

.review-metadata {
    background-color: white;
    border: 4px solid black;
    border-radius: 15px;
    padding: 1rem;
    box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.7);
}

.metadata-item {
    background-color: #0066cc;
    color: white;
    font-family: "Bangers", cursive;
    font-size: 1.1rem;
    padding: 0.5rem 1rem;
    border: 2px solid black;
    border-radius: 10px;
    margin-bottom: 0.5rem;
    text-align: center;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.5);
    letter-spacing: 1px;
}

.metadata-item:last-child {
    margin-bottom: 0;
}

/* Estilos para la sección de comentarios */
.comments-section {
    margin-top: 3rem;
    background-color: white;
    border: 4px solid #0066cc;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.7);
    position: relative;
}

.comments-section::before {
    content: "";
    position: absolute;
    top: -8px;
    left: -8px;
    right: -8px;
    bottom: -8px;
    border: 4px solid black;
    border-radius: 20px;
    z-index: -1;
}

.comments-title {
    font-family: "Bangers", cursive;
    font-size: 2rem;
    color: black;
    text-shadow: 2px 2px 0 #0066cc;
    margin-bottom: 1.5rem;
    text-align: center;
}

.comment-form {
    margin-bottom: 2rem;
    background-color: rgba(0, 102, 204, 0.05);
    border: 2px solid #0066cc;
    border-radius: 10px;
    padding: 1.5rem;
}

.comment-form textarea {
    width: 100%;
    padding: 1rem;
    border: 2px solid black;
    border-radius: 8px;
    font-family: Arial, sans-serif;
    font-size: 1rem;
    min-height: 100px;
    margin-bottom: 1rem;
    resize: vertical;
}

.comment-form button {
    background-color: white;
    color: black;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    padding: 0.6rem 1.2rem;
    border: 2px solid black;
    border-radius: 5px;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.7);
    transform: skew(-5deg);
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    text-shadow: 1px 1px 0 #0066cc;
}

.comment-form button:hover {
    transform: skew(-5deg) scale(1.05);
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
}

.comment-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.comment-item {
    background-color: rgba(0, 102, 204, 0.05);
    border: 2px solid #0066cc;
    border-radius: 10px;
    padding: 1.5rem;
    position: relative;
}

.comment-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.comment-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: 2px solid #0066cc;
    overflow: hidden;
    background-color: white;
}

.comment-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.comment-user-info {
    display: flex;
    flex-direction: column;
}

.comment-username {
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    color: #0066cc;
    text-decoration: none;
}

.comment-username:hover {
    text-decoration: underline;
}

.comment-userid {
    font-size: 0.8rem;
    color: #666;
}

.comment-date {
    margin-left: auto;
    font-size: 0.9rem;
    color: #666;
}

.comment-content {
    font-size: 1rem;
    line-height: 1.5;
    color: black;
    margin-bottom: 1rem;
}

.comment-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.comment-reactions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.reaction-btn {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    color: #666;
    transition: color 0.2s;
}

.reaction-btn:hover {
    color: #0066cc;
}

.reaction-btn.active-like {
    color: #0066cc;
}

.reaction-btn.active-dislike {
    color: #cc0000;
}

.reaction-count {
    font-size: 0.9rem;
}

.comment-admin-actions {
    margin-left: auto;
    display: flex;
    gap: 0.5rem;
}

.admin-action-btn {
    background-color: rgba(0, 0, 0, 0.1);
    border: 1px solid #666;
    border-radius: 4px;
    padding: 0.3rem 0.6rem;
    font-size: 0.8rem;
    color: #666;
    cursor: pointer;
    transition: background-color 0.2s, color 0.2s;
}

.admin-action-btn:hover {
    background-color: #0066cc;
    color: white;
    border-color: #0066cc;
}

.admin-action-btn.delete-btn:hover {
    background-color: #cc0000;
    border-color: #cc0000;
}

.admin-action-btn.ban-btn:hover {
    background-color: #cc0000;
    border-color: #cc0000;
}

.comment-images {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1rem;
}

.comment-image {
    width: 100px;
    height: 100px;
    border-radius: 8px;
    border: 2px solid #0066cc;
    overflow: hidden;
    cursor: pointer;
}

.comment-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.comment-image-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s, visibility 0.3s;
}

.comment-image-modal.active {
    opacity: 1;
    visibility: visible;
}

.comment-image-modal img {
    max-width: 90%;
    max-height: 90%;
    border: 4px solid white;
    border-radius: 8px;
}

.comment-image-modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    color: white;
    font-size: 2rem;
    cursor: pointer;
}

.no-comments-message {
    text-align: center;
    padding: 2rem;
    color: #666;
    font-style: italic;
}

@media (max-width: 768px) {
    .review-main-content {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .review-comic-title h1 {
        font-size: 2rem;
    }
    
    .review-text-content,
    .comments-section {
        padding: 1.5rem;
    }
    
    .comment-header {
        flex-wrap: wrap;
    }
    
    .comment-date {
        margin-left: 0;
        width: 100%;
        margin-top: 0.5rem;
    }
    
    .comment-actions {
        flex-wrap: wrap;
    }
    
    .comment-admin-actions {
        margin-left: 0;
        margin-top: 0.5rem;
        width: 100%;
    }
}
';

include 'includes/header.php';
?>

<?php if ($resena): ?>
<div class="review-comic-container">
    <!-- Título estilo globo de cómic -->
    <div class="review-comic-title">
        <h1><?php echo htmlspecialchars(strtoupper($resena['titulo'])); ?></h1>
    </div>
    
    <!-- Contenido principal -->
    <div class="review-main-content">
        <!-- Texto de la reseña -->
        <div class="review-text-content">
            <div class="review-text">
                <p><?php echo nl2br(htmlspecialchars($resena['descripcion'])); ?></p>
                
                <h3>LO QUE ME  ?></p>
                
                <h3>LO QUE ME GUSTÓ:</h3>
                <p><?php echo nl2br(htmlspecialchars($resena['pros'])); ?></p>
                
                <h3>LO QUE NO ME GUSTÓ:</h3>
                <p><?php echo nl2br(htmlspecialchars($resena['contras'])); ?></p>
                
                <h3>CONCLUSIÓN:</h3>
                <p>
                    <?php if ($resena['recomendacion'] === 'si'): ?>
                        <strong>RECOMIENDO</strong> esta <?php echo $resena['categoria']; ?> a todos los fans del género.
                    <?php else: ?>
                        <strong>NO RECOMIENDO</strong> esta <?php echo $resena['categoria']; ?> a pesar de algunos aspectos positivos.
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <!-- Sidebar con imagen y metadatos -->
        <div class="review-sidebar">
            <!-- Portada con puntuación -->
            <div class="review-cover">
                <div class="review-score-container"><?php echo $resena['calificacion']; ?></div>
                <?php 
                $imagenes = json_decode($resena['imagenes'], true);
                if (!empty($imagenes) && is_array($imagenes)): 
                ?>
                <img src="<?php echo htmlspecialchars($imagenes[0]); ?>" alt="<?php echo htmlspecialchars($resena['titulo']); ?>">
                <?php else: ?>
                <img src="/placeholder.svg?height=400&width=300" alt="<?php echo htmlspecialchars($resena['titulo']); ?>">
                <?php endif; ?>
                <div class="review-rating">
                    <div class="review-stars">
                        <?php for ($i = 0; $i < $resena['calificacion']; $i++): ?>★<?php endfor; ?>
                    </div>
                </div>
            </div>
            
            <!-- Metadatos -->
            <div class="review-metadata">
                <?php 
                // Determinar categorías/géneros
                $categorias = explode(',', $resena['categoria']);
                foreach ($categorias as $cat): 
                ?>
                <div class="metadata-item"><?php echo strtoupper(trim($cat)); ?></div>
                <?php endforeach; ?>
                
                <?php if ($resena['categoria'] === 'serie' && !empty($resena['temporadas']) && !empty($resena['episodios'])): ?>
                <div class="metadata-item"><?php echo $resena['temporadas']; ?> TEMPORADAS, <?php echo $resena['episodios']; ?> EPISODIOS</div>
                <?php elseif ($resena['categoria'] === 'pelicula' && !empty($resena['duracion'])): ?>
                <div class="metadata-item">DURACIÓN: <?php echo htmlspecialchars($resena['duracion']); ?></div>
                <?php elseif ($resena['categoria'] === 'libro' && !empty($resena['paginas'])): ?>
                <div class="metadata-item"><?php echo $resena['paginas']; ?> PÁGINAS</div>
                <?php endif; ?>
                
                <div class="metadata-item">POR: <?php echo htmlspecialchars($resena['nombre_usuario']); ?></div>
            </div>
        </div>
    </div>
    
    <!-- Sección de comentarios -->
    <div class="comments-section">
        <h2 class="comments-title">COMENTARIOS:</h2>
        
        <?php if (is_logged_in()): ?>
        <!-- Formulario para añadir comentario -->
        <form class="comment-form" method="post" action="ver-resena.php?id=<?php echo $id_resena; ?>">
            <input type="hidden" name="action" value="comentar">
            <textarea name="contenido" placeholder="Escribe tu comentario aquí..." required></textarea>
            <button type="submit">Enviar comentario</button>
        </form>
        <?php else: ?>
        <div class="comment-form">
            <p>Debes <a href="ini_sec.php">iniciar sesión</a> para comentar.</p>
        </div>
        <?php endif; ?>
        
        <!-- Lista de comentarios -->
        <div class="comment-list">
            <?php if (empty($comentarios)): ?>
            <div class="no-comments-message">
                <p>No hay comentarios todavía. ¡Sé el primero en comentar!</p>
            </div>
            <?php else: ?>
                <?php foreach ($comentarios as $comentario): ?>
                <div class="comment-item">
                    <div class="comment-header">
                        <div class="comment-avatar">
                            <img src="<?php echo htmlspecialchars($comentario['avatar']); ?>" alt="Avatar">
                        </div>
                        <div class="comment-user-info">
                            <a href="perfil.php?id=<?php echo $comentario['id_usuario']; ?>" class="comment-username"><?php echo htmlspecialchars($comentario['nombre_usuario']); ?></a>
                            <span class="comment-userid">@<?php echo $comentario['id_usuario']; ?></span>
                        </div>
                        <span class="comment-date"><?php echo date('d/m/Y H:i', strtotime($comentario['fecha'])); ?></span>
                    </div>
                    <div class="comment-content">
                        <?php echo nl2br(htmlspecialchars($comentario['contenido'])); ?>
                    </div>
                    <div class="comment-actions">
                        <?php if (is_logged_in()): ?>
                        <div class="comment-reactions">
                            <form method="post" action="ver-resena.php?id=<?php echo $id_resena; ?>" style="display: inline;">
                                <input type="hidden" name="action" value="like">
                                <input type="hidden" name="id_comentario" value="<?php echo $comentario['id_comentario']; ?>">
                                <button type="submit" class="reaction-btn <?php echo $comentario['user_reaction'] === '1' ? 'active-like' : ''; ?>">
                                    👍 <span class="reaction-count"><?php echo $comentario['likes']; ?></span>
                                </button>
                            </form>
                            <form method="post" action="ver-resena.php?id=<?php echo $id_resena; ?>" style="display: inline;">
                                <input type="hidden" name="action" value="dislike">
                                <input type="hidden" name="id_comentario" value="<?php echo $comentario['id_comentario']; ?>">
                                <button type="submit" class="reaction-btn <?php echo $comentario['user_reaction'] === '0' ? 'active-dislike' : ''; ?>">
                                    👎 <span class="reaction-count"><?php echo $comentario['dislikes']; ?></span>
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                        
                        <?php 
                        // Mostrar opciones de administrador o propietario
                        if (is_logged_in() && ($es_admin || $_SESSION['user_id'] == $comentario['id_usuario'])): 
                        ?>
                        <div class="comment-admin-actions">
                            <form method="post" action="ver-resena.php?id=<?php echo $id_resena; ?>" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este comentario?');">
                                <input type="hidden" name="action" value="borrar_comentario">
                                <input type="hidden" name="id_comentario" value="<?php echo $comentario['id_comentario']; ?>">
                                <button type="submit" class="admin-action-btn delete-btn">Eliminar</button>
                            </form>
                            
                            <?php if ($es_admin && $_SESSION['user_id'] != $comentario['id_usuario']): ?>
                            <form method="post" action="ver-resena.php?id=<?php echo $id_resena; ?>" onsubmit="return confirm('¿Estás seguro de que deseas banear a este usuario?');">
                                <input type="hidden" name="action" value="banear_usuario">
                                <input type="hidden" name="id_usuario" value="<?php echo $comentario['id_usuario']; ?>">
                                <button type="submit" class="admin-action-btn ban-btn">Banear usuario</button>
                            </form>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para imágenes de comentarios -->
<div class="comment-image-modal" id="imageModal">
    <span class="comment-image-modal-close">&times;</span>
    <img id="modalImage" src="/placeholder.svg" alt="Imagen ampliada">
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad para ampliar imágenes
    const commentImages = document.querySelectorAll('.comment-image');
    const imageModal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const modalClose = document.querySelector('.comment-image-modal-close');
    
    commentImages.forEach(function(image) {
        image.addEventListener('click', function() {
            const imgSrc = this.querySelector('img').src;
            modalImage.src = imgSrc;
            imageModal.classList.add('active');
        });
    });
    
    modalClose.addEventListener('click', function() {
        imageModal.classList.remove('active');
    });
    
    imageModal.addEventListener('click', function(e) {
        if (e.target === imageModal) {
            imageModal.classList.remove('active');
        }
    });
});
</script>
<?php else: ?>
<div class="review-comic-container">
    <div class="review-comic-title">
        <h1>RESEÑA NO ENCONTRADA</h1>
    </div>
    <div class="review-text-content">
        <p>Lo sentimos, la reseña que estás buscando no existe o ha sido eliminada.</p>
        <p><a href="resenas.php">Volver a la lista de reseñas</a></p>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
