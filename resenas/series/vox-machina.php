<?php
// Incluir inicialización
require_once "../../includes/init.php";

// Datos de la reseña
$id_resena = 6;
$titulo = "Vox Machina";
$categoria = "serie";
$calificacion = 3;
$descripcion = "&quot;La leyenda de Vox Machina&quot; sigue a un grupo de siete mercenarios poco convencionales que, mientras intentan pagar una deuda en un bar, se ven envueltos en una misión para salvar el reino de Exandria de fuerzas oscuras. La serie está inspirada en la primera campaña de la serie web de Dungeons &amp; Dragons, &quot;Critical Role&quot;, y está ambientada en el mundo ficticio de Exandria.";
$pros = "Los protas";
$contras = "La animación y algunos momentos suelen ser malos";
$recomendacion = "si";
$imagen = "assets/img/portadas/vox-machina.jpg";
$fecha_creacion = "2025-05-23 22:03:03";
$id_usuario = 2; // ID del usuario que creó la reseña
$temporadas = 3;
$episodios = 36;
$duracion = null;
$paginas = null;

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
$page_title = htmlspecialchars($titulo) . " - Full Moon, Full Life";
$include_base_css = true;

// Estilos específicos para la reseña que complementan styles.css
$page_styles = '
/* Fondo cómic para la página de reseña */
body {
    background-color: #0066cc;
    background-image: 
        radial-gradient(circle at 25% 25%, #ff3366 2px, transparent 2px),
        radial-gradient(circle at 75% 75%, #ffcc00 1px, transparent 1px),
        linear-gradient(45deg, rgba(255, 255, 255, 0.1) 25%, transparent 25%),
        linear-gradient(-45deg, rgba(255, 255, 255, 0.1) 25%, transparent 25%);
    background-size: 60px 60px, 40px 40px, 20px 20px, 20px 20px;
    background-position: 0 0, 30px 30px, 0 0, 10px 10px;
    min-height: 100vh;
}

/* Estilos para reseña estilo comic que complementan styles.css */
.review-comic-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 2rem;
    position: relative;
    background-color: rgba(51, 51, 51, 0.9);
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
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
    transform: skew(-2deg);
}

.review-comic-title h1 {
    font-family: "Bangers", cursive;
    font-size: 2.5rem;
    color: black;
    text-shadow: 3px 3px 0 #0066cc;
    margin: 0;
    letter-spacing: 2px;
    transform: skew(2deg);
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
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    position: relative;
}

.review-text {
    font-size: 1.1rem;
    line-height: 1.6;
    color: black;
    text-align: justify;
    font-family: Arial, sans-serif;
}

.review-text h2 {
    font-family: "Bangers", cursive;
    font-size: 1.8rem;
    color: #0066cc;
    text-shadow: 2px 2px 0 black;
    margin: 1.5rem 0 0.8rem;
    letter-spacing: 1px;
    transform: skew(-2deg);
}

.review-text h2:first-child {
    margin-top: 0;
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
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    position: relative;
}

.review-cover img {
    width: 100%;
    height: auto;
    border-radius: 10px;
    display: block;
    border: 2px solid #ccc;
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
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.7);
    z-index: 10;
}

.review-stars {
    font-size: 1.5rem;
    color: #ffcc00;
    text-shadow: 1px 1px 0 black;
    text-align: center;
    margin: 1rem 0;
}

.review-metadata {
    background-color: white;
    border: 4px solid black;
    border-radius: 15px;
    padding: 1rem;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
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
    transform: skew(-2deg);
}

.metadata-item:last-child {
    margin-bottom: 0;
}

.metadata-item a {
    color: white;
    text-decoration: none;
}

.seccion-bueno, .seccion-malo, .seccion-recomendacion {
    background-color: rgba(0, 102, 204, 0.1);
    border: 3px solid #0066cc;
    border-radius: 10px;
    padding: 1rem;
    margin: 1rem 0;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.3);
}

.seccion-bueno {
    border-color: #00cc66;
    background-color: rgba(0, 204, 102, 0.1);
}

.seccion-malo {
    border-color: #cc0000;
    background-color: rgba(204, 0, 0, 0.1);
}

.review-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 1rem;
}

.review-action-btn {
    background-color: #0066cc;
    color: white;
    font-family: "Bangers", cursive;
    font-size: 1rem;
    padding: 0.5rem 1rem;
    border: 2px solid black;
    border-radius: 8px;
    text-decoration: none;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.5);
    transition: all 0.2s;
    transform: skew(-2deg);
}

.review-action-btn.eliminar {
    background-color: #cc0000;
}

.review-action-btn:hover {
    transform: skew(-2deg) translateY(-2px);
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.5);
}

.comments-section {
    margin-top: 3rem;
    background-color: white;
    border: 4px solid #0066cc;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
}

.comments-title {
    font-family: "Bangers", cursive;
    font-size: 2rem;
    color: black;
    text-shadow: 2px 2px 0 #0066cc;
    margin-bottom: 1.5rem;
    text-align: center;
    transform: skew(-2deg);
}

.comment-form textarea {
    width: 100%;
    padding: 1rem;
    border: 2px solid #0066cc;
    border-radius: 10px;
    font-size: 1rem;
    margin-bottom: 1rem;
    background-color: rgba(255, 255, 255, 0.9);
    font-family: Arial, sans-serif;
}

.comment-form button {
    background-color: #0066cc;
    color: white;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    padding: 0.5rem 1.5rem;
    border: 2px solid black;
    border-radius: 10px;
    cursor: pointer;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.5);
    transition: transform 0.2s;
    transform: skew(-2deg);
}

.comment-item {
    background-color: rgba(0, 102, 204, 0.1);
    border: 2px solid #0066cc;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.3);
}

.comment-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
}

.comment-user {
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    color: #0066cc;
}

.comment-stars {
    color: #ffcc00;
    font-size: 1rem;
    text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.3);
}

@media (max-width: 768px) {
    .review-main-content {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .review-comic-title h1 {
        font-size: 2rem;
    }
}
';

// Incluir header
include "../../includes/header.php";
?>

<div class="review-comic-container">
    <!-- Título estilo globo de cómic -->
    <div class="review-comic-title">
        <h1><?php echo strtoupper(htmlspecialchars($titulo)); ?></h1>
    </div>
    
    <!-- Contenido principal -->
    <div class="review-main-content">
        <!-- Texto de la reseña -->
        <div class="review-text-content">
            <div class="review-text">
                <h2>Descripción</h2>
                <p><?php echo nl2br(htmlspecialchars($descripcion)); ?></p>
                
                <h2>Lo Bueno</h2>
                <div class="seccion-bueno">
                    <?php echo nl2br(htmlspecialchars($pros)); ?>
                </div>
                
                <h2>Lo Malo</h2>
                <div class="seccion-malo">
                    <?php echo nl2br(htmlspecialchars($contras)); ?>
                </div>
                
                <?php if (!empty($recomendacion)): ?>
                <h2>Recomendación</h2>
                <div class="seccion-recomendacion">
                    <?php echo nl2br(htmlspecialchars($recomendacion)); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Sidebar con imagen y metadatos -->
        <div class="review-sidebar">
            <!-- Portada con puntuación -->
            <div class="review-cover">
                <div class="review-score-container"><?php echo $calificacion; ?></div>
                <img src="../../<?php echo htmlspecialchars($imagen); ?>" alt="Portada de <?php echo htmlspecialchars($titulo); ?>">
                <div class="review-stars">
                    <?php
                    // Mostrar estrellas según puntuación
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $calificacion) {
                            echo "★";
                        } else {
                            echo "☆";
                        }
                    }
                    ?>
                </div>
            </div>
            
            <!-- Metadatos -->
            <div class="review-metadata">
                <div class="metadata-item">Reseña por: <?php echo htmlspecialchars($nombre_usuario); ?></div>
                <div class="metadata-item"><?php echo ucfirst($categoria); ?></div>
                <div class="metadata-item">Publicado: <?php echo date("d/m/Y", strtotime($fecha_creacion)); ?></div>
                
                <?php if ($categoria == "serie" && $temporadas && $episodios): ?>
                <div class="metadata-item">Temporadas: <?php echo $temporadas; ?></div>
                <div class="metadata-item">Episodios: <?php echo $episodios; ?></div>
                <?php endif; ?>
                
                <?php if ($categoria == "pelicula" && $duracion): ?>
                <div class="metadata-item">Duración: <?php echo $duracion; ?> min</div>
                <?php endif; ?>
                
                <?php if ($categoria == "libro" && $paginas): ?>
                <div class="metadata-item">Páginas: <?php echo $paginas; ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Botones de acción para el propietario -->
            <?php if (is_logged_in() && $_SESSION["user_id"] == $id_usuario): ?>
            <div class="review-actions">
                <a href="../../editar-resena.php?id=<?php echo $id_resena; ?>" class="review-action-btn">EDITAR</a>
                <a href="../../eliminar-resena.php?id=<?php echo $id_resena; ?>&token=<?php echo generate_csrf_token(); ?>" 
                   class="review-action-btn eliminar" 
                   onclick="return confirm('¿Estás seguro de que deseas eliminar esta reseña?')">ELIMINAR</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Sección de comentarios -->
    <div class="comments-section">
        <h2 class="comments-title">COMENTARIOS</h2>
        
        <?php if (is_logged_in()): ?>
        <div class="comment-form">
            <form action="../../procesar-comentario.php" method="post">
                <input type="hidden" name="id_resena" value="<?php echo $id_resena; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <textarea name="comentario" placeholder="Escribe tu comentario..." rows="3" required></textarea>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <label for="rating" style="font-weight: bold;">Calificación:</label>
                        <select name="rating" id="rating" required style="padding: 0.5rem; border: 2px solid #0066cc; border-radius: 5px;">
                            <option value="5">5 - Excelente</option>
                            <option value="4">4 - Bueno</option>
                            <option value="3">3 - Regular</option>
                            <option value="2">2 - Malo</option>
                            <option value="1">1 - Muy malo</option>
                        </select>
                    </div>
                    <button type="submit">COMENTAR</button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div style="text-align: center; padding: 1.5rem; background-color: rgba(255, 255, 255, 0.8); border-radius: 10px; margin-bottom: 2rem;">
            <p>Debes <a href="../../ini_sec.php" style="color: #0066cc; font-weight: bold;">iniciar sesión</a> para comentar.</p>
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
                while ($comentario = $result_comentarios->fetch_assoc()) {
                    $avatar = $comentario["avatar"] ? $comentario["avatar"] : "../../assets/img/avatars/default.png";
                    $nombre_comentador = $comentario["nombre_usuario"] ? $comentario["nombre_usuario"] : "Usuario Anónimo";
                    $calificacion_comentario = $comentario["calificacion"];
                    ?>
                    <div class="comment-item">
                        <div class="comment-header">
                            <img src="<?php echo $avatar; ?>" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid #0066cc; object-fit: cover;">
                            <span class="comment-user"><?php echo htmlspecialchars($nombre_comentador); ?>:</span>
                            <div class="comment-stars">
                                <?php
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $calificacion_comentario) {
                                        echo "★";
                                    } else {
                                        echo "☆";
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div style="font-size: 1rem; line-height: 1.4; color: black; background-color: rgba(255, 255, 255, 0.7); padding: 0.8rem; border-radius: 8px; margin-top: 0.5rem;">
                            <?php echo nl2br(htmlspecialchars($comentario["texto"])); ?>
                        </div>
                        <?php if (is_logged_in() && ($_SESSION["user_id"] == $comentario["id_usuario"] || is_admin())): ?>
                        <div style="margin-top: 0.5rem; display: flex; gap: 1rem;">
                            <a href="../../editar-comentario.php?id=<?php echo $comentario["id_comentario"]; ?>" style="font-family: Bangers, cursive; font-size: 0.9rem; color: #0066cc; text-decoration: none; padding: 0.2rem 0.5rem; border-radius: 5px;">EDITAR</a>
                            <a href="../../eliminar-comentario.php?id=<?php echo $comentario["id_comentario"]; ?>&token=<?php echo generate_csrf_token(); ?>" style="font-family: Bangers, cursive; font-size: 0.9rem; color: #0066cc; text-decoration: none; padding: 0.2rem 0.5rem; border-radius: 5px;" onclick="return confirm('¿Estás seguro de que deseas eliminar este comentario?')">BORRAR</a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php
                }
            } else {
                echo "<div style=\"text-align: center; padding: 1.5rem; background-color: rgba(255, 255, 255, 0.8); border-radius: 10px; font-style: italic; color: #666;\">No hay comentarios aún. ¡Sé el primero en comentar!</div>";
            }
            
            $stmt_comentarios->close();
        }
        ?>
    </div>
</div>

<?php include "../../includes/footer.php"; ?>