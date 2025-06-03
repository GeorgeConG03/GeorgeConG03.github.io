<?php
// Archivo: crear_foros_tablas_completo.php
// Script completo para configurar el sistema de foros
require_once 'includes/init.php';

echo "<h1>Configuración Completa del Sistema de Foros</h1>";

// Función para ejecutar consultas SQL
function ejecutarSQL($conexion, $sql, $descripcion) {
    echo "<h3>$descripcion</h3>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>$sql</pre>";
    
    try {
        $result = $conexion->query($sql);
        if ($result) {
            echo "<p style='color: green;'>✅ Consulta ejecutada correctamente</p>";
            return true;
        } else {
            echo "<p style='color: red;'>❌ Error: " . $conexion->error . "</p>";
            return false;
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Excepción: " . $e->getMessage() . "</p>";
        return false;
    }
}

// 1. Crear todas las tablas necesarias
echo "<h2>1. Creando tablas del sistema de foros</h2>";

// Tabla de miembros de foros
$sql_foro_miembros = "
CREATE TABLE IF NOT EXISTS foro_miembros (
    id_miembro INT AUTO_INCREMENT PRIMARY KEY,
    id_foro INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha_union TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rol ENUM('miembro', 'moderador', 'admin') DEFAULT 'miembro',
    UNIQUE KEY unique_foro_usuario (id_foro, id_usuario),
    FOREIGN KEY (id_foro) REFERENCES foros(id_foro) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
)";
ejecutarSQL($conexion, $sql_foro_miembros, "Crear tabla foro_miembros");

// Tabla de posts en foros
$sql_foro_posts = "
CREATE TABLE IF NOT EXISTS foro_posts (
    id_post INT AUTO_INCREMENT PRIMARY KEY,
    id_foro INT NOT NULL,
    id_usuario INT NOT NULL,
    id_post_padre INT NULL,
    titulo VARCHAR(255) DEFAULT NULL,
    contenido TEXT NOT NULL,
    imagen VARCHAR(255) DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estado ENUM('activo', 'eliminado', 'oculto') DEFAULT 'activo',
    total_likes INT DEFAULT 0,
    total_respuestas INT DEFAULT 0,
    es_hilo_principal BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_foro) REFERENCES foros(id_foro) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_post_padre) REFERENCES foro_posts(id_post) ON DELETE CASCADE
)";
ejecutarSQL($conexion, $sql_foro_posts, "Crear tabla foro_posts");

// Tabla de likes en posts
$sql_foro_post_likes = "
CREATE TABLE IF NOT EXISTS foro_post_likes (
    id_like INT AUTO_INCREMENT PRIMARY KEY,
    id_post INT NOT NULL,
    id_usuario INT NOT NULL,
    tipo ENUM('like', 'dislike') NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_post_like (id_post, id_usuario),
    FOREIGN KEY (id_post) REFERENCES foro_posts(id_post) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
)";
ejecutarSQL($conexion, $sql_foro_post_likes, "Crear tabla foro_post_likes");

// 2. Crear índices
echo "<h2>2. Creando índices</h2>";
$indices = [
    "CREATE INDEX IF NOT EXISTS idx_foro_miembros_foro ON foro_miembros(id_foro)",
    "CREATE INDEX IF NOT EXISTS idx_foro_miembros_usuario ON foro_miembros(id_usuario)",
    "CREATE INDEX IF NOT EXISTS idx_foro_posts_foro ON foro_posts(id_foro)",
    "CREATE INDEX IF NOT EXISTS idx_foro_posts_usuario ON foro_posts(id_usuario)",
    "CREATE INDEX IF NOT EXISTS idx_foro_posts_padre ON foro_posts(id_post_padre)",
    "CREATE INDEX IF NOT EXISTS idx_foro_post_likes_post ON foro_post_likes(id_post)",
    "CREATE INDEX IF NOT EXISTS idx_foro_post_likes_usuario ON foro_post_likes(id_usuario)"
];

foreach ($indices as $index => $sql) {
    try {
        $result = $conexion->query($sql);
        if ($result) {
            echo "<p style='color: green;'>✅ Índice #" . ($index + 1) . " creado</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Índice #" . ($index + 1) . ": " . $conexion->error . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en índice #" . ($index + 1) . ": " . $e->getMessage() . "</p>";
    }
}

// 3. Unir automáticamente a los creadores de foros existentes
echo "<h2>3. Configurando membresías de foros existentes</h2>";

$sql_foros_existentes = "SELECT id_foro, id_creador FROM foros WHERE id_creador IS NOT NULL";
$result_foros = $conexion->query($sql_foros_existentes);

if ($result_foros && $result_foros->num_rows > 0) {
    while ($foro = $result_foros->fetch_assoc()) {
        // Verificar si ya es miembro
        $sql_check = "SELECT id_miembro FROM foro_miembros WHERE id_foro = ? AND id_usuario = ?";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->bind_param("ii", $foro['id_foro'], $foro['id_creador']);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows == 0) {
            // Agregar como admin del foro
            $sql_add = "INSERT INTO foro_miembros (id_foro, id_usuario, rol) VALUES (?, ?, 'admin')";
            $stmt_add = $conexion->prepare($sql_add);
            $stmt_add->bind_param("ii", $foro['id_foro'], $foro['id_creador']);
            
            if ($stmt_add->execute()) {
                echo "<p style='color: green;'>✅ Creador del foro {$foro['id_foro']} agregado como admin</p>";
                
                // Actualizar contador de miembros
                $sql_count = "UPDATE foros SET total_miembros = (SELECT COUNT(*) FROM foro_miembros WHERE id_foro = ?) WHERE id_foro = ?";
                $stmt_count = $conexion->prepare($sql_count);
                $stmt_count->bind_param("ii", $foro['id_foro'], $foro['id_foro']);
                $stmt_count->execute();
            } else {
                echo "<p style='color: red;'>❌ Error al agregar creador del foro {$foro['id_foro']}</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ Creador del foro {$foro['id_foro']} ya es miembro</p>";
        }
    }
} else {
    echo "<p style='color: orange;'>⚠️ No hay foros existentes con creadores</p>";
}

// 4. Regenerar archivos de foros existentes con funcionalidad completa
echo "<h2>4. Regenerando archivos de foros</h2>";

$sql_foros_regen = "SELECT * FROM foros WHERE activo = 1";
$result_foros_regen = $conexion->query($sql_foros_regen);

if ($result_foros_regen && $result_foros_regen->num_rows > 0) {
    while ($foro = $result_foros_regen->fetch_assoc()) {
        $archivo_php = $foro['archivo_php'];
        if ($archivo_php && file_exists($archivo_php)) {
            // Regenerar el archivo con funcionalidad completa
            if (regenerarArchivoForo($foro)) {
                echo "<p style='color: green;'>✅ Archivo {$archivo_php} regenerado</p>";
            } else {
                echo "<p style='color: red;'>❌ Error al regenerar {$archivo_php}</p>";
            }
        }
    }
}

// 5. Verificación final
echo "<h2>5. Verificación final</h2>";
$tablas = ['foros', 'foro_miembros', 'foro_posts', 'foro_post_likes'];
$todas_ok = true;

foreach ($tablas as $tabla) {
    $sql = "SHOW TABLES LIKE '$tabla'";
    $result = $conexion->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Tabla <strong>$tabla</strong> existe</p>";
    } else {
        echo "<p style='color: red;'>❌ Tabla <strong>$tabla</strong> NO existe</p>";
        $todas_ok = false;
    }
}

// Verificar archivos necesarios
$archivos = [
    'includes/foros_functions.php',
    'procesar-foro-post.php',
    'procesar-foro-accion.php',
    'assets/css/foros.css',
    'assets/js/foros.js'
];

foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        echo "<p style='color: green;'>✅ Archivo <strong>$archivo</strong> existe</p>";
    } else {
        echo "<p style='color: red;'>❌ Archivo <strong>$archivo</strong> NO existe</p>";
        $todas_ok = false;
    }
}

if ($todas_ok) {
    echo "<h2 style='color: green;'>🎉 ¡Sistema de foros configurado completamente!</h2>";
    echo "<p>Ahora puedes:</p>";
    echo "<ul>";
    echo "<li>Crear posts en los foros</li>";
    echo "<li>Responder a posts</li>";
    echo "<li>Dar likes y dislikes</li>";
    echo "<li>Unirte y salir de foros</li>";
    echo "<li>Moderar contenido (si eres admin)</li>";
    echo "</ul>";
} else {
    echo "<h2 style='color: red;'>❌ Faltan algunos componentes</h2>";
}

echo "<p><a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #0066cc; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Ir al Inicio</a>";
echo "<a href='foros/foro-4.php' style='display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Probar Foro DCU</a></p>";

// Función para regenerar archivo de foro con funcionalidad completa
function regenerarArchivoForo($foro) {
    $contenido = '<?php
// Archivo: ' . $foro['archivo_php'] . '
// Foro: ' . htmlspecialchars($foro['titulo']) . '
require_once "../includes/init.php";
require_once "../includes/foros_functions.php";

$id_foro = ' . $foro['id_foro'] . ';
$titulo_foro = "' . addslashes($foro['titulo']) . '";
$descripcion_foro = "' . addslashes($foro['descripcion']) . '";

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
';

    return file_put_contents($foro['archivo_php'], $contenido) !== false;
}
?>
