<?php
// Incluir inicialización
require_once "../../includes/init.php";

// Datos de la reseña
$id_resena = 9;
$titulo = "Un show más";
$categoria = "serie";
$calificacion = 4;
$descripcion = "Un show más&quot; (Regular Show en inglés) es una serie animada de comedia que sigue las aventuras de Mordecai, un arrendajo azul, y Rigby, un mapache, mientras trabajan en un parque como jardineros. La serie se centra en sus problemas, que suelen ser bizarras y surrealistas.";
$pros = "Todo";
$contras = "Mordekai";
$recomendacion = "si";
$imagen = "assets/img/portadas/un-show-más.jpg";
$fecha_creacion = "2025-05-23 23:35:29";
$id_usuario = 1; // ID del usuario que creó la reseña
$temporadas = 8;
$episodios = 244;
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
$base_path = "../../"; // Ruta base para los recursos

// Incluir header
include "../../includes/header.php";
?>

<!-- Contenedor principal de la reseña -->
<div class="container">
    <div class="review-container">
        <!-- Título de la reseña -->
        <div class="review-header">
            <h1 class="review-title"><?php echo htmlspecialchars($titulo); ?></h1>
            <div class="review-meta">
                <span class="review-category"><?php echo ucfirst($categoria); ?></span>
                <span class="review-date">Publicado: <?php echo date("d/m/Y", strtotime($fecha_creacion)); ?></span>
                <span class="review-author">Por: <?php echo htmlspecialchars($nombre_usuario); ?></span>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="review-content">
            <!-- Sidebar con imagen y datos -->
            <div class="review-sidebar">
                <div class="review-image-container">
                    <img src="../../<?php echo htmlspecialchars($imagen); ?>" alt="Portada de <?php echo htmlspecialchars($titulo); ?>" class="review-image">
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
                       onclick="return confirm('¿Estás seguro de que deseas eliminar esta reseña?')">Eliminar</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Contenido de texto -->
            <div class="review-text">
                <section class="review-section">
                    <h2>Descripción</h2>
                    <p><?php echo nl2br(htmlspecialchars($descripcion)); ?></p>
                </section>

                <section class="review-section">
                    <h2>Lo Bueno</h2>
                    <div class="pros-section">
                        <?php echo nl2br(htmlspecialchars($pros)); ?>
                    </div>
                </section>

                <section class="review-section">
                    <h2>Lo Malo</h2>
                    <div class="cons-section">
                        <?php echo nl2br(htmlspecialchars($contras)); ?>
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
                            echo nl2br(htmlspecialchars($recomendacion)); 
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
                        ?>
                        <div class="comment-item">
                            <div class="comment-header">
                                <img src="<?php echo $avatar; ?>" alt="Avatar" class="comment-avatar">
                                <div class="comment-info">
                                    <span class="comment-author"><?php echo htmlspecialchars($nombre_comentador); ?></span>
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
                                <?php echo nl2br(htmlspecialchars($comentario["texto"])); ?>
                            </div>
                            <?php if (is_logged_in() && ($_SESSION["user_id"] == $comentario["id_usuario"] || is_admin())): ?>
                            <div class="comment-actions">
                                <a href="../../editar-comentario.php?id=<?php echo $comentario["id_comentario"]; ?>">Editar</a>
                                <a href="../../eliminar-comentario.php?id=<?php echo $comentario["id_comentario"]; ?>&token=<?php echo generate_csrf_token(); ?>" 
                                   onclick="return confirm('¿Estás seguro de que deseas eliminar este comentario?')">Eliminar</a>
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

<?php include "../../includes/footer.php"; ?>