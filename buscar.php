<?php
// Página de búsqueda
// Incluir archivo de inicialización
require_once __DIR__ . '/includes/init.php';

// Obtener término de búsqueda
$query = isset($_GET['q']) ? sanitize($_GET['q']) : '';

// Inicializar resultados
$usuarios = [];
$foros = [];
$resenas = [];

// Realizar búsqueda si hay un término
if (!empty($query)) {
    // Buscar usuarios
    $sql_usuarios = "SELECT u.id_usuario, u.nombre, u.correo, p.avatar 
                    FROM usuarios u 
                    LEFT JOIN perfiles p ON u.id_usuario = p.id_usuario 
                    WHERE u.nombre LIKE ? OR u.correo LIKE ? 
                    LIMIT 10";
    $stmt_usuarios = $conexion->prepare($sql_usuarios);
    $search_term = "%$query%";
    $stmt_usuarios->bind_param("ss", $search_term, $search_term);
    $stmt_usuarios->execute();
    $result_usuarios = $stmt_usuarios->get_result();
    
    while ($row = $result_usuarios->fetch_assoc()) {
        $usuarios[] = $row;
    }
    
    // Buscar foros
    $sql_foros = "SELECT * FROM foros WHERE titulo LIKE ? LIMIT 10";
    $stmt_foros = $conexion->prepare($sql_foros);
    $stmt_foros->bind_param("s", $search_term);
    $stmt_foros->execute();
    $result_foros = $stmt_foros->get_result();
    
    while ($row = $result_foros->fetch_assoc()) {
        $foros[] = $row;
    }
    
    // Buscar reseñas
    $sql_resenas = "SELECT r.*, u.nombre as autor_nombre 
                   FROM resenas r 
                   LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario 
                   WHERE r.titulo LIKE ? OR r.descripcion LIKE ? 
                   LIMIT 10";
    $stmt_resenas = $conexion->prepare($sql_resenas);
    $stmt_resenas->bind_param("ss", $search_term, $search_term);
    $stmt_resenas->execute();
    $result_resenas = $stmt_resenas->get_result();
    
    while ($row = $result_resenas->fetch_assoc()) {
        $resenas[] = $row;
    }
}

// Título de la página
$page_title = "Resultados de búsqueda para: " . htmlspecialchars($query);

// Incluir estilos base
$include_base_css = true;

// Estilos adicionales
$page_styles = '
<link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
<style>
/* Estilos específicos para la página de búsqueda */
.search-container {
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

.search-header {
    text-align: center;
    margin-bottom: 2rem;
}

.search-title {
    font-family: "Bangers", cursive;
    font-size: 2.5rem;
    color: white;
    text-shadow: 3px 3px 0 #ff3366;
    margin-bottom: 1rem;
}

.search-form {
    display: flex;
    max-width: 600px;
    margin: 0 auto 2rem;
}

.search-input {
    flex: 1;
    padding: 0.8rem 1rem;
    border: 2px solid black;
    border-right: none;
    border-radius: 5px 0 0 5px;
    font-size: 1rem;
}

.search-button {
    background-color: white;
    color: black;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    padding: 0.8rem 1.5rem;
    border: 2px solid black;
    border-radius: 0 5px 5px 0;
    cursor: pointer;
    transition: background-color 0.2s;
}

.search-button:hover {
    background-color: #ff3366;
    color: white;
}

.search-section {
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
}

.search-results {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.result-card {
    background-color: white;
    border: 3px solid black;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    transition: transform 0.2s;
    color: black;
    text-decoration: none;
    display: block;
}

.result-card:hover {
    transform: translateY(-5px);
}

.user-card {
    display: flex;
    align-items: center;
    padding: 1rem;
}

.user-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid black;
    margin-right: 1rem;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-info {
    flex: 1;
}

.user-name {
    font-family: "Bangers", cursive;
    font-size: 1.3rem;
    color: black;
    margin-bottom: 0.3rem;
}

.user-email {
    font-size: 0.9rem;
    color: #666;
}

.forum-card {
    padding: 1rem;
}

.forum-title {
    font-family: "Bangers", cursive;
    font-size: 1.5rem;
    color: black;
    text-align: center;
    margin-bottom: 0.5rem;
}

.forum-description {
    font-size: 0.9rem;
    color: #666;
    text-align: center;
}

.review-card {
    padding: 1rem;
}

.review-title {
    font-family: "Bangers", cursive;
    font-size: 1.3rem;
    color: black;
    margin-bottom: 0.5rem;
}

.review-author {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0.5rem;
}

.review-excerpt {
    font-size: 0.9rem;
    color: #333;
    line-height: 1.4;
}

.no-results {
    text-align: center;
    padding: 2rem;
    background-color: rgba(0, 0, 0, 0.3);
    border-radius: 10px;
}

@media (max-width: 768px) {
    .search-form {
        flex-direction: column;
    }
    
    .search-input {
        border-right: 2px solid black;
        border-radius: 5px;
        margin-bottom: 0.5rem;
    }
    
    .search-button {
        border-radius: 5px;
    }
    
    .search-results {
        grid-template-columns: 1fr;
    }
}
</style>
';

// JavaScript adicional
$additional_js = ['menu.js'];

// Incluir el encabezado
include 'includes/header.php';
?>

<!-- Contenido principal -->
<main class="search-container">
    <div class="search-header">
        <h1 class="search-title">Resultados de búsqueda</h1>
        
        <form action="buscar.php" method="get" class="search-form">
            <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Buscar usuarios, foros o reseñas..." class="search-input">
            <button type="submit" class="search-button">Buscar</button>
        </form>
    </div>
    
    <?php if (empty($query)): ?>
    <div class="no-results">
        <p>Ingresa un término de búsqueda para encontrar usuarios, foros o reseñas.</p>
    </div>
    <?php elseif (empty($usuarios) && empty($foros) && empty($resenas)): ?>
    <div class="no-results">
        <p>No se encontraron resultados para "<?php echo htmlspecialchars($query); ?>".</p>
    </div>
    <?php else: ?>
        <?php if (!empty($usuarios)): ?>
        <div class="search-section">
            <h2 class="section-title">Usuarios</h2>
            <div class="search-results">
                <?php foreach ($usuarios as $usuario): ?>
                <a href="perfil.php?id=<?php echo $usuario['id_usuario']; ?>" class="result-card user-card">
                    <div class="user-avatar">
                        <img src="<?php echo !empty($usuario['avatar']) ? $usuario['avatar'] : 'assets/img/placeholder.jpg'; ?>" alt="Avatar">
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($usuario['nombre']); ?></div>
                        <div class="user-email"><?php echo htmlspecialchars($usuario['correo']); ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($foros)): ?>
        <div class="search-section">
            <h2 class="section-title">Foros</h2>
            <div class="search-results">
                <?php foreach ($foros as $foro): ?>
                <a href="foro.php?id=<?php echo $foro['id_foro']; ?>" class="result-card forum-card">
                    <div class="forum-title"><?php echo htmlspecialchars($foro['titulo']); ?></div>
                    <?php if (!empty($foro['descripcion'])): ?>
                    <div class="forum-description"><?php echo htmlspecialchars($foro['descripcion']); ?></div>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($resenas)): ?>
        <div class="search-section">
            <h2 class="section-title">Reseñas</h2>
            <div class="search-results">
                <?php foreach ($resenas as $resena): ?>
                <a href="detalle-resena.php?id=<?php echo $resena['id']; ?>" class="result-card review-card">
                    <div class="review-title"><?php echo htmlspecialchars($resena['titulo']); ?></div>
                    <div class="review-author">Por: <?php echo htmlspecialchars($resena['autor_nombre'] ?? $resena['autor']); ?></div>
                    <div class="review-excerpt"><?php echo substr(htmlspecialchars($resena['descripcion']), 0, 100) . '...'; ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
