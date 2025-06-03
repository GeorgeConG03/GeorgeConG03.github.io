<?php
// Archivo: foros/foro-4.php
// Foro: DCU
require_once "../includes/init.php";
require_once "../includes/foros_functions.php";

$id_foro = 4;
$titulo_foro = "DCU";
$descripcion_foro = "bnuovbuiabufdsaf";

// Obtener información del foro
$foro_info = obtenerInfoForo($id_foro);
if (!$foro_info) {
    show_message("El foro no existe.", "error");
    redirect("../index.php");
}

// Verificar si el usuario está unido al foro
$user_id = $_SESSION["user_id"] ?? null;
$es_miembro = $user_id ? esMiembroForo($id_foro, $user_id) : false;
$es_admin_foro = $user_id ? esAdminForo($id_foro, $user_id) : false;
$es_admin_sitio = $user_id && function_exists("esAdminDirecto") && esAdminDirecto($user_id);

$page_title = $titulo_foro . " - Foro";
$include_base_css = true;

include "../includes/header.php";
?>

<link rel="stylesheet" href="../assets/css/foros.css">
<script src="../assets/js/foros.js" defer></script>

<div class="foro-container">
    <!-- Header del foro -->
    <div class="foro-header">
        <div class="foro-portada">
            <img src="../<?php echo $foro_info["imagen_portada"]; ?>" alt="Portada del foro" class="portada-img">
        </div>
        <div class="foro-info">
            <h1 class="foro-titulo"><?php echo htmlspecialchars($foro_info["titulo"]); ?></h1>
            <p class="foro-descripcion"><?php echo htmlspecialchars($foro_info["descripcion"]); ?></p>
            <div class="foro-stats">
                <span class="stat-item">👥 <?php echo $foro_info["total_miembros"]; ?> miembros</span>
                <span class="stat-item">💬 <?php echo $foro_info["total_posts"]; ?> posts</span>
                <span class="stat-item">👤 Creado por: 
                    <a href="../perfil.php?id=<?php echo $foro_info["id_creador"]; ?>" class="creador-link">
                        <?php echo htmlspecialchars($foro_info["nombre_creador"]); ?>
                    </a>
                </span>
            </div>
            <div class="foro-actions">
                <?php if ($user_id): ?>
                    <?php if (!$es_miembro): ?>
                        <button onclick="unirseAlForo(<?php echo $id_foro; ?>)" class="btn btn-primary">
                            ➕ Unirse al Foro
                        </button>
                    <?php else: ?>
                        <button onclick="salirDelForo(<?php echo $id_foro; ?>)" class="btn btn-secondary">
                            ➖ Salir del Foro
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="../ini_sec.php" class="btn btn-primary">Iniciar Sesión para Unirse</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Formulario para crear post (solo miembros) -->
    <?php if ($es_miembro): ?>
    <div class="crear-post-section">
        <h2>Crear Nuevo Post</h2>
        <form action="../procesar-foro-post.php" method="post" enctype="multipart/form-data" class="crear-post-form">
            <input type="hidden" name="id_foro" value="<?php echo $id_foro; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="form-group">
                <label for="titulo">Título del Post (opcional):</label>
                <input type="text" id="titulo" name="titulo" maxlength="255" placeholder="Título de tu post...">
            </div>
            
            <div class="form-group">
                <label for="contenido">Contenido:</label>
                <textarea id="contenido" name="contenido" rows="4" required placeholder="¿Qué quieres compartir?"></textarea>
            </div>
            
            <div class="form-group">
                <label for="imagen">Imagen (opcional):</label>
                <input type="file" id="imagen" name="imagen" accept="image/*">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="es_hilo_principal" value="1">
                    Marcar como hilo principal
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary">📝 Publicar Post</button>
        </form>
    </div>
    <?php elseif ($user_id): ?>
    <div class="unirse-mensaje">
        <p>Debes unirte al foro para poder crear posts y comentar.</p>
    </div>
    <?php else: ?>
    <div class="login-mensaje">
        <p>Debes <a href="../ini_sec.php">iniciar sesión</a> para participar en este foro.</p>
    </div>
    <?php endif; ?>

    <!-- Lista de posts -->
    <div class="posts-section">
        <h2>Posts del Foro</h2>
        <?php
        $posts = obtenerPostsForo($id_foro);
        if (!empty($posts)) {
            echo "<div class=\"posts-list\">";
            foreach ($posts as $post) {
                renderizarPost($post, $es_miembro, $es_admin_foro, $es_admin_sitio);
            }
            echo "</div>";
        } else {
            echo "<div class=\"no-posts\">No hay posts en este foro aún. ¡Sé el primero en publicar!</div>";
        }
        ?>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
