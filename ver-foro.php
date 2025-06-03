<?php
// Archivo: ver-foro.php
require_once 'includes/init.php';

// Verificar que se proporcionó un ID de foro
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    show_message("ID de foro no válido", "error");
    redirect("foros.php");
    exit;
}

$id_foro = (int)$_GET['id'];

// Obtener información del foro
$sql_foro = "SELECT f.*, u.nombre as autor_nombre 
             FROM foros f 
             LEFT JOIN usuarios u ON f.id_usuario = u.id_usuario 
             WHERE f.id_foro = ? AND f.estado = 'activo'";
$stmt_foro = $conexion->prepare($sql_foro);
$stmt_foro->bind_param("i", $id_foro);
$stmt_foro->execute();
$result_foro = $stmt_foro->get_result();

// Verificar si el foro existe
if ($result_foro->num_rows === 0) {
    show_message("El foro solicitado no existe o ha sido eliminado", "error");
    redirect("foros.php");
    exit;
}

$foro = $result_foro->fetch_assoc();
$stmt_foro->close();

// Obtener publicaciones del foro
$sql_publicaciones = "SELECT p.*, u.nombre as autor_nombre, u.avatar as autor_avatar,
                     (SELECT COUNT(*) FROM respuestas_foro WHERE id_publicacion = p.id_publicacion) as num_respuestas
                     FROM publicaciones_foro p
                     LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
                     WHERE p.id_foro = ? AND p.estado = 'activo'
                     ORDER BY p.es_destacado DESC, p.fecha_creacion DESC";
$stmt_publicaciones = $conexion->prepare($sql_publicaciones);
$stmt_publicaciones->bind_param("i", $id_foro);
$stmt_publicaciones->execute();
$result_publicaciones = $stmt_publicaciones->get_result();
$stmt_publicaciones->close();

// Configurar variables para el header
$page_title = htmlspecialchars($foro['titulo']) . " - Foros - Full Moon, Full Life";
$include_base_css = true;

$page_styles = '
.foro-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 1rem;
}

.foro-header {
    background-color: white;
    border: 3px solid black;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    position: relative;
    overflow: hidden;
}

.foro-header-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    opacity: 0.1;
    z-index: 0;
}

.foro-header-content {
    position: relative;
    z-index: 1;
}

.foro-title {
    font-family: "Bangers", cursive;
    font-size: 3rem;
    color: #9933cc;
    text-shadow: 2px 2px 0 black;
    margin-bottom: 1rem;
    text-align: center;
    letter-spacing: 2px;
}

.foro-meta {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.foro-meta-item {
    font-family: "Bangers", cursive;
    font-size: 1.1rem;
    color: #333;
}

.foro-descripcion {
    font-size: 1.1rem;
    line-height: 1.6;
    color: #333;
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
}

.foro-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 1.5rem;
}

.foro-btn {
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    color: white;
    background-color: #9933cc;
    padding: 0.7rem 1.5rem;
    border-radius: 10px;
    text-decoration: none;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.7);
    transition: all 0.2s;
    transform: skew(-5deg);
    border: 2px solid black;
}

.foro-btn:hover {
    transform: skew(-5deg) translateY(-3px);
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
}

.foro-btn.eliminar {
    background-color: #cc0000;
}

.publicaciones-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.publicaciones-title {
    font-family: "Bangers", cursive;
    font-size: 2rem;
    color: white;
    text-shadow: 2px 2px 0 #9933cc;
    letter-spacing: 1px;
}

.nueva-publicacion-btn {
    font-family: "Bangers", cursive;
    font-size: 1.1rem;
    color: white;
    background-color: #9933cc;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    text-decoration: none;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.7);
    transition: all 0.2s;
    transform: skew(-5deg);
    border: 2px solid black;
}

.nueva-publicacion-btn:hover {
    transform: skew(-5deg) translateY(-2px);
    box-shadow: 4px 4px 0 rgba(0, 0, 0, 0.7);
}

.publicaciones-lista {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.publicacion-card {
    background-color: white;
    border: 3px solid black;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    transition: all 0.3s ease;
}

.publicacion-card:hover {
    transform: translateY(-5px);
    box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.7);
}

.publicacion-destacada {
    border-color: #9933cc;
    background-color: rgba(153, 51, 204, 0.05);
}

.publicacion-destacada::before {
    content: "DESTACADO";
    position: absolute;
    top: 10px;
    right: -30px;
    background-color: #9933cc;
    color: white;
    font-family: "Bangers", cursive;
    padding: 5px 40px;
    transform: rotate(45deg);
    font-size: 0.8rem;
    z-index: 10;
}

.publicacion-link {
    display: block;
    text-decoration: none;
    color: inherit;
    padding: 1.5rem;
    position: relative;
}

.publicacion-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.publicacion-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: 2px solid #9933cc;
    object-fit: cover;
}

.publicacion-info {
    flex: 1;
}

.publicacion-titulo {
    font-family: "Bangers", cursive;
    font-size: 1.5rem;
    color: #9933cc;
    text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.3);
    margin-bottom: 0.3rem;
    letter-spacing: 1px;
}

.publicacion-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.8rem;
    color: #666;
}

.publicacion-extracto {
    font-size: 1rem;
    color: #333;
    margin-bottom: 1rem;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.publicacion-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
    color: #666;
}

.publicacion-respuestas {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.publicacion-respuestas-icono {
    font-size: 1.2rem;
}

.publicacion-fecha {
    font-style: italic;
}

.no-publicaciones {
    text-align: center;
    background-color: rgba(255, 255, 255, 0.9);
    padding: 3rem;
    border-radius: 15px;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    border: 3px solid black;
}

.no-publicaciones p {
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
    color: black;
}

@media (max-width: 768px) {
    .foro-title {
        font-size: 2.5rem;
    }
    
    .foro-meta {
        flex-direction: column;
        gap: 0.5rem;
        align-items: center;
    }
    
    .publicaciones-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .nueva-publicacion-btn {
        align-self: flex-end;
    }
}
';

include 'includes/header.php';
?>

<div class="foro-container">
    <!-- Cabecera del foro -->
    <div class="foro-header">
        <?php if (!empty($foro['imagen']) && file_exists($foro['imagen'])): ?>
        <div class="foro-header-bg" style="background-image: url('<?php echo htmlspecialchars($foro['imagen']); ?>');"></div>
        <?php endif; ?>
        
        <div class="foro-header-content">
            <h1 class="foro-title"><?php echo htmlspecialchars($foro['titulo']); ?></h1>
            
            <div class="foro-meta">
                <div class="foro-meta-item">
                    Creado por: <?php echo htmlspecialchars($foro['autor_nombre'] ?: 'Usuario Anónimo'); ?>
                </div>
                <div class="foro-meta-item">
                    Fecha: <?php echo date('d/m/Y', strtotime($foro['fecha_creacion'])); ?>
                </div>
                <div class="foro-meta-item">
                    Publicaciones: <?php echo $result_publicaciones->num_rows; ?>
                </div>
            </div>
            
            <div class="foro-descripcion">
                <?php echo nl2br(htmlspecialchars($foro['descripcion'])); ?>
            </div>
            
            <?php if (is_logged_in() && ($_SESSION['user_id'] == $foro['id_usuario'] || is_admin())): ?>
            <div class="foro-actions">
                <a href="editar-foro.php?id=<?php echo $foro['id_foro']; ?>" class="foro-btn">EDITAR FORO</a>
                <a href="eliminar-foro.php?id=<?php echo $foro['id_foro']; ?>&token=<?php echo generate_csrf_token(); ?>" 
                   class="foro-btn eliminar" 
                   onclick="return confirm('¿Estás seguro de que deseas eliminar este foro?')">ELIMINAR FORO</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Publicaciones del foro -->
    <div class="publicaciones-header">
        <h2 class="publicaciones-title">PUBLICACIONES</h2>
        
        <?php if (is_logged_in()): ?>
        <a href="crear-publicacion.php?foro=<?php echo $id_foro; ?>" class="nueva-publicacion-btn">NUEVA PUBLICACIÓN</a>
        <?php endif; ?>
    </div>
    
    <?php if ($result_publicaciones && $result_publicaciones->num_rows > 0): ?>
    <div class="publicaciones-lista">
        <?php while($publicacion = $result_publicaciones->fetch_assoc()): ?>
            <?php
            $es_destacada = $publicacion['es_destacado'] == 1;
            $avatar = !empty($publicacion['autor_avatar']) ? $publicacion['autor_avatar'] : 'assets/img/avatars/default.png';
            ?>
            <div class="publicacion-card <?php echo $es_destacada ? 'publicacion-destacada' : ''; ?>">
                <a href="ver-publicacion.php?id=<?php echo $publicacion['id_publicacion']; ?>" class="publicacion-link">
                    <div class="publicacion-header">
                        <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="publicacion-avatar">
                        <div class="publicacion-info">
                            <div class="publicacion-titulo"><?php echo htmlspecialchars($publicacion['titulo']); ?></div>
                            <div class="publicacion-meta">
                                <span>Por: <?php echo htmlspecialchars($publicacion['autor_nombre'] ?: 'Usuario Anónimo'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="publicacion-extracto">
                        <?php echo htmlspecialchars(substr(strip_tags($publicacion['contenido']), 0, 200)) . (strlen(strip_tags($publicacion['contenido'])) > 200 ? '...' : ''); ?>
                    </div>
                    
                    <div class="publicacion-footer">
                        <div class="publicacion-respuestas">
                            <span class="publicacion-respuestas-icono">💬</span>
                            <span><?php echo $publicacion['num_respuestas']; ?> respuestas</span>
                        </div>
                        <div class="publicacion-fecha">
                            <?php echo date('d/m/Y H:i', strtotime($publicacion['fecha_creacion'])); ?>
                        </div>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="no-publicaciones">
        <p>No hay publicaciones en este foro todavía.</p>
        <?php if (is_logged_in()): ?>
        <a href="crear-publicacion.php?foro=<?php echo $id_foro; ?>" class="comic-button">CREAR PRIMERA PUBLICACIÓN</a>
        <?php else: ?>
        <p>Inicia sesión para crear la primera publicación.</p>
        <a href="ini_sec.php" class="comic-button">INICIAR SESIÓN</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>