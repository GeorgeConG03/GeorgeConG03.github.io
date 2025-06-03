<?php
// Archivo: index.php (versión corregida)
require_once __DIR__ . '/includes/init.php';

// Verificar si el archivo de autenticación existe y cargarlo
if (file_exists(__DIR__ . '/includes/auth_admin.php')) {
    require_once __DIR__ . '/includes/auth_admin.php';
} else {
    // Si no existe, crear funciones básicas
    function mostrarMenuAdmin() {
        global $conexion;
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        $sql = "SELECT es_admin FROM usuarios WHERE id_usuario = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['es_admin'] == 1;
        }
        return false;
    }
}

// Debug: Mostrar información de sesión (remover en producción)
if (isset($_GET['debug'])) {
    echo "<div style='background: yellow; padding: 10px; margin: 10px;'>";
    echo "<strong>DEBUG INFO:</strong><br>";
    
    $user_id = $_SESSION['user_id'] ?? $_SESSION['usuario_id'] ?? null;
    echo "Sesión activa: " . ($user_id ? 'SÍ (ID: ' . $user_id . ')' : 'NO') . "<br>";
    echo "Variable user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NO DEFINIDA') . "<br>";
    echo "Variable usuario_id: " . (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'NO DEFINIDA') . "<br>";
    
    if ($user_id) {
        echo "Es admin (mostrarMenuAdmin): " . (mostrarMenuAdmin() ? 'SÍ' : 'NO') . "<br>";
        echo "Es admin (esAdminDirecto): " . (esAdminDirecto($user_id) ? 'SÍ' : 'NO') . "<br>";
        
        // Verificar directamente en la base de datos
        $sql_check = "SELECT nombre, correo, es_admin FROM usuarios WHERE id_usuario = ?";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->bind_param("i", $user_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($row_check = $result_check->fetch_assoc()) {
            echo "Datos BD: " . $row_check['nombre'] . " - Admin: " . ($row_check['es_admin'] ? 'SÍ' : 'NO') . "<br>";
        }
    }
    echo "</div>";
}

// Obtener reseñas recientes de todas las categorías
$sql_recientes = "SELECT r.id_resena, r.titulo, r.calificacion, r.imagenes, r.categoria,
                  r.archivo_php, r.id_usuario, r.descripcion, u.nombre as autor,
                  r.temporadas, r.episodios, r.duracion, r.paginas
                  FROM resenas r 
                  LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario 
                  ORDER BY r.fecha_creacion DESC LIMIT 9";
$result_recientes = $conexion->query($sql_recientes);

// Obtener foros recientes
    require_once __DIR__ . '/includes/foros-function.php';
$foros_recientes = obtenerTodosLosForos(6);

$page_title = "Inicio - Full Moon, Full Life";
$include_base_css = true;

$page_styles = '
.inicio-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 1rem;
}

.inicio-header {
    text-align: center;
    margin-bottom: 3rem;
}

.inicio-title {
    font-family: "Bangers", cursive;
    font-size: 4rem;
    color: white;
    text-shadow: 3px 3px 0 #ff3366, 5px 5px 0 black;
    margin-bottom: 1rem;
    letter-spacing: 3px;
}

.inicio-subtitle {
    font-family: "Bangers", cursive;
    font-size: 1.5rem;
    color: white;
    text-shadow: 2px 2px 0 #0066cc;
    margin-bottom: 2rem;
}

.seccion-recientes {
    margin-top: 3rem;
}

.seccion-title {
    font-family: "Bangers", cursive;
    font-size: 2.5rem;
    color: white;
    text-shadow: 3px 3px 0 #ff3366;
    margin-bottom: 2rem;
    text-align: center;
    letter-spacing: 2px;
}

.listado-horizontal {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.contenido-card-horizontal {
    background-color: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    transition: all 0.3s ease;
    position: relative;
    border: 3px solid black;
    display: flex;
    height: 200px;
}

.contenido-card-horizontal:hover {
    transform: translateY(-5px);
    box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.7);
}

.contenido-link-horizontal {
    text-decoration: none;
    color: inherit;
    display: flex;
    width: 100%;
}

.contenido-imagen-container-horizontal {
    width: 150px;
    flex-shrink: 0;
    position: relative;
    background-color: #f0f0f0;
}

.contenido-imagen-horizontal {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.contenido-calificacion-horizontal {
    position: absolute;
    top: 10px;
    left: 10px;
    background-color: rgba(0, 0, 0, 0.7);
    border-radius: 20px;
    padding: 5px 10px;
    z-index: 10;
}

.estrellas {
    color: #ffcc00;
    font-size: 1rem;
    text-shadow: 1px 1px 0 black;
}

.contenido-info-horizontal {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.contenido-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.contenido-titulo-horizontal {
    font-family: "Bangers", cursive;
    font-size: 1.8rem;
    color: black;
    text-shadow: 1px 1px 0 #0066cc;
    letter-spacing: 1px;
    flex: 1;
}

.contenido-categoria-pill {
    font-size: 0.8rem;
    color: white;
    background-color: #0066cc;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    text-transform: uppercase;
    font-family: "Bangers", cursive;
    letter-spacing: 1px;
    flex-shrink: 0;
    margin-left: 1rem;
}

.contenido-descripcion {
    color: #444;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    max-height: 60px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
}

.contenido-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.contenido-autor {
    font-size: 0.8rem;
    color: #666;
    font-family: Arial, sans-serif;
}

.contenido-metadata {
    font-size: 0.8rem;
    color: #666;
    font-family: Arial, sans-serif;
}

.no-contenido {
    text-align: center;
    background-color: rgba(255, 255, 255, 0.9);
    padding: 3rem;
    border-radius: 15px;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    border: 3px solid black;
}

.no-contenido p {
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
    color: black;
}

.comic-button {
    display: inline-block;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    color: white;
    background-color: #0066cc;
    padding: 0.7rem 1.5rem;
    border-radius: 10px;
    text-decoration: none;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.7);
    transition: all 0.2s;
    transform: skew(-5deg);
    border: 2px solid black;
}

.comic-button:hover {
    transform: skew(-5deg) translateY(-3px);
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
}

/* Estilos para el panel de administración */
.admin-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 2rem;
    background-color: rgba(255, 255, 255, 0.95);
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    border: 3px solid #ff3366;
}

.admin-title {
    font-family: "Bangers", cursive;
    font-size: 3rem;
    color: #ff3366;
    text-shadow: 2px 2px 0 #000;
    text-align: center;
    margin-bottom: 2rem;
}

.admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
}

.admin-card {
    background-color: white;
    border: 3px solid #ff3366;
    border-radius: 10px;
    padding: 2rem;
    text-align: center;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    transition: transform 0.2s;
}

.admin-card:hover {
    transform: translateY(-5px);
}

.card-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.admin-card h3 {
    font-family: "Bangers", cursive;
    font-size: 1.8rem;
    color: #ff3366;
    margin-bottom: 1rem;
}

.admin-card p {
    color: #666;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.card-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 0.8rem 1.5rem;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-family: "Bangers", cursive;
    font-size: 1.1rem;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
    display: inline-block;
}

.btn-primary {
    background-color: #ff3366;
    color: white;
}

.btn-secondary {
    background-color: #0066cc;
    color: white;
}

.btn:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

.admin-notice {
    background-color: #ff3366;
    color: white;
    padding: 1rem;
    text-align: center;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    margin-bottom: 2rem;
    border-radius: 10px;
    text-shadow: 1px 1px 0 black;
}

@media (max-width: 768px) {
    .inicio-title {
        font-size: 3rem;
    }
    
    .contenido-card-horizontal {
        flex-direction: column;
        height: auto;
    }
    
    .contenido-link-horizontal {
        flex-direction: column;
    }
    
    .contenido-imagen-container-horizontal {
        width: 100%;
        height: 180px;
    }
    
    .contenido-header {
        flex-direction: column;
    }
    
    .contenido-categoria-pill {
        margin: 0.5rem 0 0 0;
        display: inline-block;
    }
    
    .contenido-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .admin-grid {
        grid-template-columns: 1fr;
    }
    
    .card-actions {
        flex-direction: column;
    }
}

@media (max-width: 480px) {
    .inicio-title {
        font-size: 2.5rem;
    }
}
';

include 'includes/header.php';

// Verificar si el usuario actual es administrador
$es_usuario_admin = false;
if (isset($_SESSION['user_id']) || isset($_SESSION['usuario_id'])) {
    // Asegurar que tenemos la función disponible
    if (function_exists('mostrarMenuAdmin')) {
        $es_usuario_admin = mostrarMenuAdmin();
    } else {
        // Fallback usando la función directa
        $user_id = $_SESSION['user_id'] ?? $_SESSION['usuario_id'] ?? null;
        if ($user_id && function_exists('esAdminDirecto')) {
            $es_usuario_admin = esAdminDirecto($user_id);
        }
    }
}
?>

<?php if ($es_usuario_admin): ?>
<!-- Panel de Administración - Solo visible para administradores -->
<div class="admin-notice">
    🔧 MODO ADMINISTRADOR ACTIVADO 🔧
</div>

<div class="admin-container">
    <h1 class="admin-title">Panel de Administración</h1>
    
    <div class="admin-grid">
        <!-- Gestión de Usuarios -->
        <div class="admin-card">
            <div class="card-icon">👥</div>
            <h3>Gestión de Usuarios</h3>
            <p>Administrar cuentas de usuario, perfiles y permisos</p>
            <div class="card-actions">
                <a href="admin-usuarios.php" class="btn btn-primary">Gestionar Usuarios</a>
            </div>
        </div>

        <!-- Gestión de Reseñas -->
        <div class="admin-card">
            <div class="card-icon">📝</div>
            <h3>Gestión de Reseñas</h3>
            <p>Moderar y editar reseñas de series, películas y libros</p>
            <div class="card-actions">
                <a href="admin-resenas.php" class="btn btn-primary">Gestionar Reseñas</a>
            </div>
        </div>

        <!-- Gestión de Foros -->
        <div class="admin-card">
            <div class="card-icon">💬</div>
            <h3>Gestión de Foros</h3>
            <p>Administrar foros y publicaciones</p>
            <div class="card-actions">
                <a href="admin-foros.php" class="btn btn-primary">Gestionar Foros</a>
            </div>
        </div>

        <!-- Gestión de Contenidos -->
        <div class="admin-card">
            <div class="card-icon">🎬</div>
            <h3>Gestión de Contenidos</h3>
            <p>Administrar catálogo de series, películas y libros</p>
            <div class="card-actions">
                <a href="admin-contenidos.php" class="btn btn-primary">Gestionar Contenidos</a>
            </div>
        </div>

        <!-- Gestión de Comentarios -->
        <div class="admin-card">
            <div class="card-icon">💭</div>
            <h3>Gestión de Comentarios</h3>
            <p>Moderar comentarios y respuestas</p>
            <div class="card-actions">
                <a href="admin-comentarios.php" class="btn btn-primary">Gestionar Comentarios</a>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="admin-card">
            <div class="card-icon">📊</div>
            <h3>Estadísticas</h3>
            <p>Ver estadísticas del sitio y reportes</p>
            <div class="card-actions">
                <a href="admin-estadisticas.php" class="btn btn-primary">Ver Estadísticas</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Contenido principal del sitio -->
<div class="inicio-container">
    <div class="inicio-header">
        <h1 class="inicio-title">FULL MOON, FULL LIFE</h1>
        <?php if (isset($_SESSION['user_id'])): ?>
            <p style="color: white; font-family: 'Bangers', cursive; font-size: 1.2rem;">
                Bienvenido, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?>!
                <?php if ($es_usuario_admin): ?>
                    <span style="color: #ff3366;">⭐ ADMINISTRADOR ⭐</span>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
    
    <div class="seccion-recientes">
        <h2 class="seccion-title">RESEÑAS RECIENTES</h2>
        
        <?php if ($result_recientes && $result_recientes->num_rows > 0): ?>
        <div class="listado-horizontal">
            <?php while($row = $result_recientes->fetch_assoc()): ?>
                <?php
                $autor = $row['autor'] ? $row['autor'] : 'Usuario Anónimo';
                $archivo_path = $row['archivo_php'] ? $row['archivo_php'] : '#';
                $categoria_color = '';
                switch($row['categoria']) {
                    case 'pelicula':
                        $categoria_color = '#ff3366';
                        break;
                    case 'serie':
                        $categoria_color = '#0066cc';
                        break;
                    case 'libro':
                        $categoria_color = '#00cc66';
                        break;
                }
                
                // Extraer el nombre de la imagen (para asegurar que se carga correctamente)
                $imagen = '';
                if (isset($row['imagenes'])) {
                    // Si es un string JSON, decodificarlo
                    if (substr($row['imagenes'], 0, 1) === '[') {
                        $img_array = json_decode($row['imagenes'], true);
                        $imagen = isset($img_array[0]) ? $img_array[0] : '';
                    } else {
                        $imagen = $row['imagenes'];
                    }
                }
                
                // Si no hay imagen o la ruta no existe, usar imagen por defecto
                if (empty($imagen) || !file_exists($imagen)) {
                    // Intentar buscar la imagen por el nombre en minúsculas
                    $nombre_archivo = strtolower(str_replace(" ", "-", $row['titulo']));
                    $posible_imagen = "assets/img/portadas/{$nombre_archivo}.jpg";
                    
                    if (file_exists($posible_imagen)) {
                        $imagen = $posible_imagen;
                    } else {
                        // Comprobar extensiones alternativas
                        $extensiones = ['png', 'jpeg', 'webp'];
                        foreach ($extensiones as $ext) {
                            $posible_imagen = "assets/img/portadas/{$nombre_archivo}.{$ext}";
                            if (file_exists($posible_imagen)) {
                                $imagen = $posible_imagen;
                                break;
                            }
                        }
                        
                        // Si aún no se encuentra, usar imagen por defecto
                        if (empty($imagen) || !file_exists($imagen)) {
                            $imagen = "assets/img/portadas/default.jpg";
                        }
                    }
                }
                ?>
                <div class="contenido-card-horizontal">
                    <a href="<?php echo htmlspecialchars($archivo_path); ?>" class="contenido-link-horizontal">
                        <div class="contenido-imagen-container-horizontal">
                            <div class="contenido-calificacion-horizontal">
                                <div class="estrellas">
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $row['calificacion']) {
                                            echo "★";
                                        } else {
                                            echo "☆";
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <img src="<?php echo htmlspecialchars($imagen); ?>" alt="Portada de <?php echo htmlspecialchars($row['titulo']); ?>" class="contenido-imagen-horizontal">
                        </div>
                        <div class="contenido-info-horizontal">
                            <div>
                                <div class="contenido-header">
                                    <div class="contenido-titulo-horizontal"><?php echo strtoupper(htmlspecialchars($row['titulo'])); ?></div>
                                    <div class="contenido-categoria-pill" style="background-color: <?php echo $categoria_color; ?>">
                                        <?php echo strtoupper($row['categoria']); ?>
                                    </div>
                                </div>
                                <div class="contenido-descripcion">
                                    <?php echo htmlspecialchars(substr($row['descripcion'], 0, 200)) . (strlen($row['descripcion']) > 200 ? '...' : ''); ?>
                                </div>
                            </div>
                            <div class="contenido-footer">
                                <div class="contenido-autor">
                                    POR: <?php echo strtoupper(htmlspecialchars($autor)); ?>
                                </div>
                                <?php if ($row['categoria'] == 'serie' && $row['temporadas'] && $row['episodios']): ?>
                                <div class="contenido-metadata">
                                    TEMPORADAS: <?php echo $row['temporadas']; ?> | EPISODIOS: <?php echo $row['episodios']; ?>
                                </div>
                                <?php elseif ($row['categoria'] == 'pelicula' && $row['duracion']): ?>
                                <div class="contenido-metadata">
                                    DURACIÓN: <?php echo $row['duracion']; ?> min
                                </div>
                                <?php elseif ($row['categoria'] == 'libro' && $row['paginas']): ?>
                                <div class="contenido-metadata">
                                    PÁGINAS: <?php echo $row['paginas']; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="no-contenido">
            <p>No hay reseñas disponibles aún.</p>
            <a href="crear-resena.php" class="comic-button">CREAR PRIMERA RESEÑA</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sección de foros recientes -->
    <div class="seccion-recientes">
        <h2 class="seccion-title">FOROS RECIENTES</h2>
        
        <?php if (!empty($foros_recientes)): ?>
        <div class="listado-horizontal">
            <?php foreach($foros_recientes as $foro): ?>
                <?php
                $imagen_portada = $foro['imagen_portada'] ?: 'foros/portadas/default-foro.jpg';
                if (!file_exists($imagen_portada)) {
                    $imagen_portada = 'assets/img/portadas/default.jpg';
                }
                ?>
                <div class="contenido-card-horizontal">
                    <a href="<?php echo htmlspecialchars($foro['archivo_php']); ?>" class="contenido-link-horizontal">
                        <div class="contenido-imagen-container-horizontal">
                            <div class="contenido-calificacion-horizontal">
                                <div class="estrellas">
                                    🏛️ FORO
                                </div>
                            </div>
                            <img src="<?php echo htmlspecialchars($imagen_portada); ?>" alt="Portada de <?php echo htmlspecialchars($foro['titulo']); ?>" class="contenido-imagen-horizontal">
                        </div>
                        <div class="contenido-info-horizontal">
                            <div>
                                <div class="contenido-header">
                                    <div class="contenido-titulo-horizontal"><?php echo strtoupper(htmlspecialchars($foro['titulo'])); ?></div>
                                    <div class="contenido-categoria-pill" style="background-color: #28a745;">
                                        FORO
                                    </div>
                                </div>
                                <div class="contenido-descripcion">
                                    <?php echo htmlspecialchars(substr($foro['descripcion'] ?: 'Únete a la discusión en este foro', 0, 200)) . (strlen($foro['descripcion'] ?: '') > 200 ? '...' : ''); ?>
                                </div>
                            </div>
                            <div class="contenido-footer">
                                <div class="contenido-autor">
                                    CREADO POR: <?php echo strtoupper(htmlspecialchars($foro['nombre_creador'] ?: 'Usuario')); ?>
                                </div>
                                <div class="contenido-metadata">
                                    👥 <?php echo $foro['total_miembros']; ?> MIEMBROS | 💬 <?php echo $foro['total_posts']; ?> POSTS
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="no-contenido">
            <p>No hay foros disponibles aún.</p>
            <a href="crear-foro.php" class="comic-button">CREAR PRIMER FORO</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_SESSION['user_id'])): ?>
<div style="text-align: center; margin: 2rem; color: white; font-family: Arial, sans-serif; font-size: 0.9rem;">
    <a href="?debug=1" style="color: #ccc;">Ver información de debug</a>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
