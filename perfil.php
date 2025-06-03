<?php
// Página de perfil de usuario
// Incluir archivo de inicialización
require_once __DIR__ . '/includes/init.php';

// Verificar si el usuario está autenticado
if (!is_logged_in()) {
    // Guardar la URL actual para redirigir después del login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    // Mostrar mensaje de error
    show_message('Debes iniciar sesión para ver tu perfil', 'error');
    
    // Redirigir al login
    header('Location: ini_sec.php');
    exit;
}

// Obtener ID del usuario
$user_id = $_SESSION['user_id'];

// Verificar si se está viendo el perfil de otro usuario
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $profile_id = (int)$_GET['id'];
    // Si es diferente al usuario actual, verificar que exista
    if ($profile_id !== $user_id) {
        $user_exists = false;
        $query = "SELECT id_usuario FROM usuarios WHERE id_usuario = ?";
        $stmt = $conexion->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("i", $profile_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user_exists = true;
                $user_id = $profile_id;
            }
            
            $stmt->close();
        }
        
        if (!$user_exists) {
            show_message('El usuario no existe', 'error');
            header('Location: index.php');
            exit;
        }
    }
}

// Obtener datos del usuario
$user_data = get_user_data($user_id);

// Obtener estadísticas del usuario
$user_stats = get_user_stats($user_id);

// Verificar si se está editando el perfil
$editing = isset($_GET['edit']) && $_GET['edit'] === 'true' && $user_id === $_SESSION['user_id'];

// Procesar formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id === $_SESSION['user_id']) {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        show_message('Error de seguridad. Por favor, intenta de nuevo.', 'error');
        header('Location: perfil.php');
        exit;
    }
    
    // Procesar datos del formulario
    $update_data = [
        'nombre' => sanitize($_POST['nombre'] ?? $user_data['nombre']),
        'nombre_completo' => sanitize($_POST['nombre_completo'] ?? $user_data['nombre_completo']),
        'biografia' => sanitize($_POST['biografia'] ?? $user_data['biografia']),
        'telefono' => sanitize($_POST['telefono'] ?? $user_data['telefono'])
    ];
    
    // Procesar avatar si se ha subido
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $avatar_path = upload_avatar($_FILES['avatar'], $user_id);
        
        if ($avatar_path) {
            // Actualizar avatar en la base de datos
            $query = "UPDATE perfiles SET avatar = ? WHERE id_usuario = ?";
            $stmt = $conexion->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("si", $avatar_path, $user_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    
    // Actualizar perfil
    if (update_user_profile($user_id, $update_data)) {
        show_message('Perfil actualizado correctamente', 'success');
    } else {
        show_message('Error al actualizar el perfil', 'error');
    }
    
    // Redirigir para evitar reenvío del formulario
    header('Location: perfil.php?id=' . $user_id);
    exit;
}

// Título de la página
$page_title = "Perfil de " . ($user_data ? $user_data['nombre'] : 'Usuario');

// Incluir estilos base
$include_base_css = true;

// Estilos adicionales
$page_styles = '
/* Estilos específicos para la página de perfil */
.profile-container {
    background-color: rgba(51, 51, 51, 0.9);
    border-radius: 10px;
    padding: 2rem;
    margin: 2rem auto;
    max-width: 1000px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
    position: relative;
    z-index: 10;
    color: white;
}

.profile-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2rem;
    margin-bottom: 2rem;
    position: relative;
    text-align: center;
}

.profile-avatar-container {
    position: relative;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid black;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    background-color: #b3d4fc;
    margin: 0 auto;
}

.profile-avatar-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-avatar-edit {
    position: absolute;
    bottom: 20px;
    right: 20px;
    background-color: rgba(0, 0, 0, 0.7);
    color: white;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1.8rem;
    transition: all 0.2s ease;
    z-index: 10;
}

.profile-avatar-edit:hover {
    background-color: rgba(255, 51, 102, 0.8);
    transform: scale(1.1);
}

.profile-info {
    width: 100%;
    max-width: 600px;
}

.profile-row {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 0.8rem;
}

.profile-label {
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    color: white;
    text-shadow: 2px 2px 0 #000;
    margin-right: 0.5rem;
}

.profile-value {
    font-family: "Bangers", cursive;
    font-size: 1.5rem;
    color: white;
    text-shadow: 2px 2px 0 #ff3366;
}

.profile-edit-button {
    margin-top: 1.5rem;
    text-align: center;
}

.btn-edit {
    background-color: white;
    color: black;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    padding: 0.8rem 1.5rem;
    border: 2px solid black;
    border-radius: 5px;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.7);
    transform: skew(-5deg);
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
    display: inline-block;
}

.btn-edit:hover {
    transform: skew(-5deg) scale(1.05);
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    background-color: #ff3366;
    color: white;
}

.profile-stats {
    display: flex;
    justify-content: space-around;
    margin: 2rem 0;
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-family: "Bangers", cursive;
    font-size: 2rem;
    color: white;
    text-shadow: 2px 2px 0 #ff3366;
}

.stat-label {
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    color: white;
    text-shadow: 1px 1px 0 #000;
}

.profile-section {
    margin-bottom: 2rem;
}

.section-title {
    font-family: "Bangers", cursive;
    font-size: 1.8rem;
    color: white;
    text-shadow: 2px 2px 0 #ff3366;
    margin-bottom: 1rem;
    border-bottom: 2px solid #ff3366;
    padding-bottom: 0.5rem;
    text-align: center;
}

.bio-text {
    background-color: rgba(0, 0, 0, 0.5);
    padding: 1rem;
    border-radius: 5px;
    font-family: Arial, sans-serif;
    line-height: 1.6;
    min-height: 100px;
}

.forums-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
}

.forum-item {
    background-color: white;
    border: 3px solid black;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    transition: transform 0.2s;
    display: flex;
    align-items: center;
}

.forum-item:hover {
    transform: translateY(-5px);
}

.forum-name {
    flex: 1;
    padding: 1rem;
    font-family: "Bangers", cursive;
    font-size: 1.5rem;
    color: black;
    text-align: center;
    text-shadow: 1px 1px 0 #666;
}

.forum-image {
    width: 80px;
    height: 120px;
    object-fit: cover;
}

.posts-list {
    list-style: none;
    padding: 0;
}

.post-item {
    background-color: rgba(0, 0, 0, 0.5);
    padding: 1rem;
    border-radius: 5px;
    margin-bottom: 1rem;
}

.post-title {
    font-family: "Bangers", cursive;
    font-size: 1.3rem;
    color: white;
    text-shadow: 1px 1px 0 #000;
    margin-bottom: 0.5rem;
}

.post-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    color: #ccc;
    margin-bottom: 0.5rem;
}

.post-excerpt {
    font-family: Arial, sans-serif;
    line-height: 1.4;
    color: #eee;
}

/* Estilos para las reseñas */
.reviews-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.review-card {
    background-color: white;
    border: 3px solid black;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    transition: transform 0.2s;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.review-card:hover {
    transform: translateY(-5px);
}

.review-card-image {
    position: relative;
    height: 150px;
    overflow: hidden;
}

.review-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.review-card-rating {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: #ff3366;
    color: white;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: "Bangers", cursive;
    font-size: 1.5rem;
    border: 2px solid white;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
}

.review-card-content {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.review-card-title {
    font-family: "Bangers", cursive;
    font-size: 1.3rem;
    color: #0066cc;
    margin: 0 0 0.5rem 0;
    text-shadow: 1px 1px 0 #ccc;
}

.review-card-meta {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.review-card-category {
    background-color: #0066cc;
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 10px;
    font-size: 0.8rem;
    text-transform: uppercase;
}

.review-card-date {
    color: #666;
}

.review-card-link {
    margin-top: auto;
    background-color: #0066cc;
    color: white;
    text-align: center;
    padding: 0.5rem;
    border-radius: 5px;
    text-decoration: none;
    font-family: "Bangers", cursive;
    transition: background-color 0.2s;
}

.review-card-link:hover {
    background-color: #ff3366;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    color: white;
    text-shadow: 1px 1px 0 #000;
    margin-bottom: 0.5rem;
}

.form-input, .form-textarea {
    width: 100%;
    padding: 0.8rem;
    border: 2px solid black;
    border-radius: 5px;
    font-family: Arial, sans-serif;
    font-size: 1rem;
    background-color: rgba(255, 255, 255, 0.9);
}

.form-textarea {
    min-height: 100px;
    resize: vertical;
}

.form-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
}

.btn-cancel {
    background-color: white;
    color: black;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    padding: 0.8rem 1.5rem;
    border: 2px solid black;
    border-radius: 5px;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.7);
    transform: skew(-5deg);
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
    display: inline-block;
}

.btn-save {
    background-color: white;
    color: black;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    padding: 0.8rem 1.5rem;
    border: 2px solid black;
    border-radius: 5px;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    transform: skew(-5deg);
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    text-shadow: 2px 2px 0 #0066cc;
}

.btn-cancel:hover, .btn-save:hover {
    transform: skew(-5deg) scale(1.05);
    box-shadow: 7px 7px 0 rgba(0, 0, 0, 0.7);
}

.avatar-upload {
    display: none;
}

@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .profile-avatar-container {
        width: 200px;
        height: 200px;
    }
    
    .profile-avatar-edit {
        bottom: 10px;
        right: 10px;
        width: 40px;
        height: 40px;
        font-size: 1.5rem;
    }
    
    .profile-row {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .edit-button {
        margin-left: 0;
    }
    
    .profile-stats {
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .stat-item {
        flex: 1;
        min-width: 100px;
    }
    
    .reviews-grid {
        grid-template-columns: 1fr;
    }
}
';

// JavaScript adicional
$additional_js = ['menu.js'];

// Incluir el encabezado
include 'includes/header.php';
?>

<!-- Contenido principal -->
<main class="profile-container">
    <?php if ($editing): ?>
    <!-- Formulario de edición de perfil -->
    <form action="perfil.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        
        <div class="profile-header">
            <div class="profile-avatar-container">
                <img src="<?php echo $user_data['avatar'] ?? DEFAULT_AVATAR; ?>" alt="Avatar" class="profile-avatar-image">
                <label for="avatar-upload" class="profile-avatar-edit" title="Cambiar avatar">📷</label>
                <input type="file" id="avatar-upload" name="avatar" class="avatar-upload" accept="image/*">
            </div>
            
            <div class="profile-info">
                <div class="form-group">
                    <label for="nombre" class="form-label">Nombre de usuario:</label>
                    <input type="text" id="nombre" name="nombre" class="form-input" value="<?php echo $user_data['nombre']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="nombre_completo" class="form-label">Nombre completo:</label>
                    <input type="text" id="nombre_completo" name="nombre_completo" class="form-input" value="<?php echo $user_data['nombre_completo'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="telefono" class="form-label">Número de contacto:</label>
                    <input type="text" id="telefono" name="telefono" class="form-input" value="<?php echo $user_data['telefono'] ?? ''; ?>">
                </div>
            </div>
        </div>
        
        <div class="profile-section">
            <h2 class="section-title">Biografía</h2>
            <div class="form-group">
                <textarea id="biografia" name="biografia" class="form-textarea"><?php echo $user_data['biografia'] ?? ''; ?></textarea>
            </div>
        </div>
        
        <div class="form-buttons">
            <a href="perfil.php?id=<?php echo $user_id; ?>" class="btn-cancel">Cancelar</a>
            <button type="submit" class="btn-save">Guardar cambios</button>
        </div>
    </form>
    <?php else: ?>
    <!-- Vista de perfil -->
    <div class="profile-header">
        <div class="profile-avatar-container">
            <img src="<?php echo $user_data['avatar'] ?? DEFAULT_AVATAR; ?>" alt="Avatar" class="profile-avatar-image">
            <?php if ($user_id === $_SESSION['user_id']): ?>
            <a href="perfil.php?edit=true" class="profile-avatar-edit" title="Editar perfil">✏️</a>
            <?php endif; ?>
        </div>
        
        <div class="profile-info">
            <div class="profile-row">
                <span class="profile-label">Nombre de usuario:</span>
                <span class="profile-value"><?php echo $user_data['nombre']; ?></span>
            </div>
            
            <div class="profile-row">
                <span class="profile-label">Correo de contacto:</span>
                <span class="profile-value"><?php echo $user_data['correo']; ?></span>
            </div>
            
            <?php if ($user_id === $_SESSION['user_id']): ?>
            <div class="profile-edit-button">
                <a href="perfil.php?edit=true" class="btn-edit">Editar Perfil</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
        
    <div class="profile-stats">
        
        <div class="stat-item">
            <div class="stat-value"><?php echo $user_stats['posts']; ?></div>
            <div class="stat-label">PUBLICACIONES</div>
        </div>
    </div>
    
    <div class="profile-section">
        <h2 class="section-title">BIBLIOGRAFÍA</h2>
        <div class="bio-text">
            <?php echo nl2br($user_data['biografia'] ?? 'Este usuario no ha añadido una biografía.'); ?>
        </div>
    </div>
    
    <!-- Sección de reseñas publicadas -->
    <?php if (!empty($user_stats['resenas'])): ?>
    <div class="profile-section">
        <h2 class="section-title">RESEÑAS PUBLICADAS</h2>
        <div class="reviews-grid">
            <?php foreach ($user_stats['resenas'] as $resena): ?>
            <div class="review-item">
                <?php 
                // Decodificar las imágenes (están en formato JSON)
                $imagenes = json_decode($resena['imagenes'], true);
                $imagen = !empty($imagenes) && is_array($imagenes) ? $imagenes[0] : 'assets/img/portadas/default.jpg';
                ?>
                <div class="review-card">
                    <div class="review-card-image">
                        <img src="<?php echo $imagen; ?>" alt="<?php echo htmlspecialchars($resena['titulo']); ?>">
                        <div class="review-card-rating"><?php echo $resena['calificacion']; ?></div>
                    </div>
                    <div class="review-card-content">
                        <h3 class="review-card-title"><?php echo htmlspecialchars($resena['titulo']); ?></h3>
                        <div class="review-card-meta">
                            <span class="review-card-category"><?php echo ucfirst($resena['categoria']); ?></span>
                            <span class="review-card-date"><?php echo date('d/m/Y', strtotime($resena['fecha_creacion'])); ?></span>
                        </div>
                        <a href="<?php echo $resena['archivo_php']; ?>" class="review-card-link">Ver reseña</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="profile-section">
        <h2 class="section-title">FOROS SEGUIDOS</h2>
        <?php if (empty($user_stats['foros'])): ?>
        <p>Este usuario no sigue ningún foro.</p>
        <?php else: ?>
        <div class="forums-grid">
            <?php foreach ($user_stats['foros'] as $foro): ?>
            <div class="forum-item">
                <div class="forum-name"><?php echo htmlspecialchars($foro['nombre']); ?></div>
                <?php if (!empty($foro['imagen'])): ?>
                <img src="<?php echo $foro['imagen']; ?>" alt="<?php echo htmlspecialchars($foro['nombre']); ?>" class="forum-image">
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($user_stats['ultimos_posts'])): ?>
    <div class="profile-section">
        <h2 class="section-title">ÚLTIMAS PUBLICACIONES EN FOROS</h2>
        <ul class="posts-list">
            <?php foreach ($user_stats['ultimos_posts'] as $post): ?>
            <li class="post-item">
                <h3 class="post-title"><?php echo htmlspecialchars($post['titulo']); ?></h3>
                <div class="post-meta">
                    <span>Foro: <?php echo htmlspecialchars($post['foro_nombre']); ?></span>
                    <span>Fecha: <?php echo date('d/m/Y H:i', strtotime($post['fecha_creacion'])); ?></span>
                </div>
                <div class="post-excerpt">
                    <?php echo substr(strip_tags($post['contenido']), 0, 150) . '...'; ?>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</main>

<!-- JavaScript para previsualizar la imagen de avatar -->
<?php if ($editing): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const avatarUpload = document.getElementById('avatar-upload');
    const avatarImage = document.querySelector('.profile-avatar-image');
    
    avatarUpload.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                avatarImage.src = e.target.result;
            };
            
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>