<?php
// Formulario para consultar plataformas
require_once '../includes/functions.php';
require_once '../config/db.php';

// Verificar si el usuario es administrador
if (!is_admin()) {
    show_message('No tienes permisos para acceder a esta página.', 'error');
    redirect('../index.php');
}

// Inicializar variables de búsqueda
$nombre = '';
$resultados = [];
$total_resultados = 0;
$busqueda_realizada = false;

// Procesar formulario de búsqueda
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $busqueda_realizada = true;
    
    // Recoger y sanitizar parámetros de búsqueda
    $nombre = isset($_POST['nombre']) ? sanitize($_POST['nombre']) : '';
    
    // Construir consulta SQL base
    $sql = "SELECT p.*, COUNT(cp.contenido_id) as total_contenidos 
            FROM plataformas p
            LEFT JOIN contenido_plataforma cp ON p.id = cp.plataforma_id";
    
    // Iniciar condiciones WHERE
    $where_conditions = [];
    $params = [];
    
    // Añadir condiciones según los filtros
    if (!empty($nombre)) {
        $where_conditions[] = "p.nombre LIKE ?";
        $params[] = "%$nombre%";
    }
    
    // Añadir condiciones WHERE a la consulta
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    // Agrupar por plataforma
    $sql .= " GROUP BY p.id";
    
    // Añadir orden
    $sql .= " ORDER BY p.nombre ASC";
    
    // Ejecutar consulta
    $result = query($sql, $params);
    $resultados = fetch_all($result);
    $total_resultados = count($resultados);
}

$page_title = "Consultar Plataformas - Panel de Administración";
$page_styles = '
/* Estilos para el formulario de consulta */
.admin-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 1rem;
}

.admin-title {
    font-size: 2.5rem;
    color: white;
    text-shadow: 3px 3px 0 #ff3366;
    margin-bottom: 2rem;
    text-align: center;
}

.search-form {
    background-color: rgba(0, 0, 0, 0.7);
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.5);
    margin-bottom: 2rem;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    font-size: 1.3rem;
    color: white;
    text-shadow: 2px 2px 0 #0066cc;
    margin-bottom: 0.5rem;
}

.form-input {
    width: 100%;
    padding: 0.8rem;
    font-family: Arial, sans-serif;
    font-size: 1rem;
    border: 3px solid #000;
    border-radius: 5px;
    background-color: white;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.5);
}

.search-buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 1.5rem;
}

.search-button {
    display: inline-block;
    background-color: white;
    color: black;
    font-family: "Bangers", cursive;
    font-size: 1.5rem;
    padding: 0.8rem 1.5rem;
    text-align: center;
    text-decoration: none;
    border-radius: 5px;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    transform: skew(-5deg);
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
    cursor: pointer;
    letter-spacing: 1px;
    text-shadow: 2px 2px 0 #0066cc;
}

.search-button:hover {
    transform: skew(-5deg) scale(1.05);
    box-shadow: 7px 7px 0 rgba(0, 0, 0, 0.7);
}

.reset-button {
    background-color: #ff3366;
    color: white;
    text-shadow: 2px 2px 0 black;
}

.add-button {
    background-color: #33cc33;
    color: white;
    text-shadow: 2px 2px 0 black;
    margin-bottom: 2rem;
}

/* Resultados de búsqueda */
.results-container {
    background-color: rgba(0, 0, 0, 0.7);
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.5);
}

.results-title {
    font-size: 2rem;
    color: white;
    text-shadow: 2px 2px 0 #ff3366;
    margin-bottom: 1.5rem;
    text-align: center;
}

.results-count {
    font-size: 1.2rem;
    color: white;
    text-align: center;
    margin-bottom: 1.5rem;
    font-family: Arial, sans-serif;
}

.results-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1.5rem;
}

.results-table th,
.results-table td {
    padding: 0.8rem;
    text-align: left;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.results-table th {
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    font-size: 1.2rem;
    text-shadow: 1px 1px 0 black;
}

.results-table tr:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.results-table td {
    color: white;
    font-family: Arial, sans-serif;
}

.no-results {
    text-align: center;
    color: white;
    font-size: 1.2rem;
    padding: 2rem;
    font-family: Arial, sans-serif;
}

.platform-logo {
    width: 40px;
    height: 40px;
    object-fit: contain;
    border-radius: 5px;
    background-color: white;
    padding: 3px;
}

.platform-count {
    background-color: #0066cc;
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 3px;
    font-size: 0.9rem;
}

.action-button {
    display: inline-block;
    background-color: white;
    color: black;
    font-family: "Bangers", cursive;
    font-size: 0.9rem;
    padding: 0.3rem 0.6rem;
    text-align: center;
    text-decoration: none;
    border-radius: 3px;
    box-shadow: 2px 2px 0 rgba(0, 0, 0, 0.7);
    transform: skew(-5deg);
    transition: transform 0.2s, box-shadow 0.2s;
}

.action-button:hover {
    transform: skew(-5deg) scale(1.05);
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.7);
}

.edit-button {
    background-color: #ffcc00;
    color: black;
}

.delete-button {
    background-color: #ff3366;
    color: white;
    text-shadow: 1px 1px 0 black;
}

.view-button {
    background-color: #0066cc;
    color: white;
    text-shadow: 1px 1px 0 black;
}
';

include '../includes/admin_header.php';
?>

<div class="admin-container">
    <h1 class="admin-title">CONSULTAR PLATAFORMAS</h1>
    
    <a href="agregar_plataforma.php" class="search-button add-button">AGREGAR NUEVA PLATAFORMA</a>
    
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="search-form">
        <div class="form-row">
            <div class="form-group">
                <label for="nombre" class="form-label">NOMBRE DE PLATAFORMA</label>
                <input type="text" id="nombre" name="nombre" class="form-input" value="<?php echo htmlspecialchars($nombre); ?>">
            </div>
        </div>
        
        <div class="search-buttons">
            <button type="submit" class="search-button">BUSCAR</button>
            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="search-button reset-button">LIMPIAR</a>
        </div>
    </form>
    
    <?php if ($busqueda_realizada || true): // Siempre mostrar resultados ?>
    <div class="results-container">
        <h2 class="results-title">PLATAFORMAS DISPONIBLES</h2>
        
        <?php 
        // Si no se ha realizado búsqueda, obtener todas las plataformas
        if (!$busqueda_realizada) {
            $sql = "SELECT p.*, COUNT(cp.contenido_id) as total_contenidos 
                    FROM plataformas p
                    LEFT JOIN contenido_plataforma cp ON p.id = cp.plataforma_id
                    GROUP BY p.id
                    ORDER BY p.nombre ASC";
            $result = query($sql);
            $resultados = fetch_all($result);
            $total_resultados = count($resultados);
        }
        ?>
        
        <p class="results-count">
            Se encontraron <?php echo $total_resultados; ?> plataforma(s).
        </p>
        
        <?php if ($total_resultados > 0): ?>
        <table class="results-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Logo</th>
                    <th>Nombre</th>
                    <th>URL</th>
                    <th>Contenidos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados as $plataforma): ?>
                <tr>
                    <td><?php echo $plataforma['id']; ?></td>
                    <td>
                        <img src="<?php echo htmlspecialchars($plataforma['logo']); ?>" alt="<?php echo htmlspecialchars($plataforma['nombre']); ?>" class="platform-logo">
                    </td>
                    <td><?php echo htmlspecialchars($plataforma['nombre']); ?></td>
                    <td>
                        <a href="<?php echo htmlspecialchars($plataforma['url']); ?>" target="_blank" style="color: #0066cc;">
                            <?php echo htmlspecialchars($plataforma['url']); ?>
                        </a>
                    </td>
                    <td>
                        <span class="platform-count">
                            <?php echo $plataforma['total_contenidos']; ?> contenido(s)
                        </span>
                    </td>
                    <td>
                        <a href="ver_plataforma.php?id=<?php echo $plataforma['id']; ?>" class="action-button view-button">Ver</a>
                        <a href="editar_plataforma.php?id=<?php echo $plataforma['id']; ?>" class="action-button edit-button">Editar</a>
                        <a href="eliminar_plataforma.php?id=<?php echo $plataforma['id']; ?>" class="action-button delete-button" onclick="return confirm('¿Estás seguro de eliminar esta plataforma? Esta acción no se puede deshacer.')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-results">
            <p>No se encontraron plataformas que coincidan con tu búsqueda.</p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/admin_footer.php'; ?>