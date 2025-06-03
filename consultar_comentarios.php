<?php
// Formulario para consultar comentarios
require_once 'includes/functions.php';
require_once 'config/db.php';

// Verificar si el usuario es administrador
if (!is_admin()) {
    show_message('No tienes permisos para acceder a esta página.', 'error');
    redirect('index.php');
}

// Inicializar variables de búsqueda
$contenido_id = '';
$usuario_id = '';
$calificacion_min = '';
$fecha_desde = '';
$fecha_hasta = '';
$resultados = [];
$total_resultados = 0;
$busqueda_realizada = false;

// Procesar formulario de búsqueda
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $busqueda_realizada = true;
    
    // Recoger y sanitizar parámetros de búsqueda
    $contenido_id = isset($_POST['contenido_id']) ? intval($_POST['contenido_id']) : 0;
    $usuario_id = isset($_POST['usuario_id']) ? intval($_POST['usuario_id']) : 0;
    $calificacion_min = isset($_POST['calificacion_min']) ? intval($_POST['calificacion_min']) : 0;
    $fecha_desde = isset($_POST['fecha_desde']) ? sanitize($_POST['fecha_desde']) : '';
    $fecha_hasta = isset($_POST['fecha_hasta']) ? sanitize($_POST['fecha_hasta']) : '';
    
    // Construir consulta SQL base
    $sql = "SELECT c.*, u.username, co.titulo as contenido_titulo, co.tipo as contenido_tipo 
            FROM comentarios c
            JOIN usuarios u ON c.usuario_id = u.id
            JOIN contenidos co ON c.contenido_id = co.id";
    
    // Iniciar condiciones WHERE
    $where_conditions = [];
    $params = [];
    
    // Añadir condiciones según los filtros
    if ($contenido_id > 0) {
        $where_conditions[] = "c.contenido_id = ?";
        $params[] = $contenido_id;
    }
    
    if ($usuario_id > 0) {
        $where_conditions[] = "c.usuario_id = ?";
        $params[] = $usuario_id;
    }
    
    if ($calificacion_min > 0) {
        $where_conditions[] = "c.calificacion >= ?";
        $params[] = $calificacion_min;
    }
    
    if (!empty($fecha_desde)) {
        $where_conditions[] = "c.created_at >= ?";
        $params[] = $fecha_desde . ' 00:00:00';
    }
    
    if (!empty($fecha_hasta)) {
        $where_conditions[] = "c.created_at <= ?";
        $params[] = $fecha_hasta . ' 23:59:59';
    }
    
    // Añadir condiciones WHERE a la consulta
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    // Añadir orden
    $sql .= " ORDER BY c.created_at DESC";
    
    // Ejecutar consulta
    $result = query($sql, $params);
    $resultados = fetch_all($result);
    $total_resultados = count($resultados);
}

// Obtener lista de contenidos para el selector
$sql_contenidos = "SELECT id, titulo, tipo FROM contenidos ORDER BY titulo ASC";
$result_contenidos = query($sql_contenidos);
$contenidos = fetch_all($result_contenidos);

// Obtener lista de usuarios para el selector
$sql_usuarios = "SELECT id, username FROM usuarios ORDER BY username ASC";
$result_usuarios = query($sql_usuarios);
$usuarios = fetch_all($result_usuarios);

$page_title = "Consultar Comentarios - Panel de Administración";
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

.comment-text {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.comment-rating {
    display: flex;
}

.star {
    color: #ffcc00;
    margin-right: 2px;
}

.content-type {
    text-transform: uppercase;
    font-size: 0.8rem;
    background-color: #0066cc;
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 3px;
    display: inline-block;
}

.content-type.serie {
    background-color: #0066cc;
}

.content-type.pelicula {
    background-color: #ff3366;
}

.content-type.libro {
    background-color: #33cc33;
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

.view-button {
    background-color: #0066cc;
    color: white;
    text-shadow: 1px 1px 0 black;
}

.delete-button {
    background-color: #ff3366;
    color: white;
    text-shadow: 1px 1px 0 black;
}
';

include 'includes/admin_header.php';
?>

<div class="admin-container">
    <h1 class="admin-title">CONSULTAR COMENTARIOS</h1>
    
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="search-form">
        <div class="form-row">
            <div class="form-group">
                <label for="contenido_id" class="form-label">CONTENIDO</label>
                <select id="contenido_id" name="contenido_id" class="form-input">
                    <option value="0">Todos</option>
                    <?php foreach ($contenidos as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo $contenido_id == $c['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['titulo']) . ' (' . ucfirst($c['tipo']) . ')'; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="usuario_id" class="form-label">USUARIO</label>
                <select id="usuario_id" name="usuario_id" class="form-input">
                    <option value="0">Todos</option>
                    <?php foreach ($usuarios as $u): ?>
                    <option value="<?php echo $u['id']; ?>" <?php echo $usuario_id == $u['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($u['username']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="calificacion_min" class="form-label">CALIFICACIÓN MÍNIMA</label>
                <select id="calificacion_min" name="calificacion_min" class="form-input">
                    <option value="0">Cualquiera</option>
                    <option value="1" <?php echo $calificacion_min == 1 ? 'selected' : ''; ?>>1+</option>
                    <option value="2" <?php echo $calificacion_min == 2 ? 'selected' : ''; ?>>2+</option>
                    <option value="3" <?php echo $calificacion_min == 3 ? 'selected' : ''; ?>>3+</option>
                    <option value="4" <?php echo $calificacion_min == 4 ? 'selected' : ''; ?>>4+</option>
                    <option value="5" <?php echo $calificacion_min == 5 ? 'selected' : ''; ?>>5</option>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="fecha_desde" class="form-label">DESDE FECHA</label>
                <input type="date" id="fecha_desde" name="fecha_desde" class="form-input" value="<?php echo htmlspecialchars($fecha_desde); ?>">
            </div>
            
            <div class="form-group">
                <label for="fecha_hasta" class="form-label">HASTA FECHA</label>
                <input type="date" id="fecha_hasta" name="fecha_hasta" class="form-input" value="<?php echo htmlspecialchars($fecha_hasta); ?>">
            </div>
        </div>
        
        <div class="search-buttons">
            <button type="submit" class="search-button">BUSCAR</button>
            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="search-button reset-button">LIMPIAR</a>
        </div>
    </form>
    
    <?php if ($busqueda_realizada): ?>
    <div class="results-container">
        <h2 class="results-title">RESULTADOS DE BÚSQUEDA</h2>
        
        <p class="results-count">
            Se encontraron <?php echo $total_resultados; ?> comentario(s) para tu búsqueda.
        </p>
        
        <?php if ($total_resultados > 0): ?>
        <table class="results-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Contenido</th>
                    <th>Usuario</th>
                    <th>Comentario</th>
                    <th>Calificación</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados as $comentario): ?>
                <tr>
                    <td><?php echo $comentario['id']; ?></td>
                    <td>
                        <?php echo htmlspecialchars($comentario['contenido_titulo']); ?>
                        <span class="content-type <?php echo $comentario['contenido_tipo']; ?>">
                            <?php echo ucfirst($comentario['contenido_tipo']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($comentario['username']); ?></td>
                    <td class="comment-text" title="<?php echo htmlspecialchars($comentario['comentario']); ?>">
                        <?php echo htmlspecialchars(substr($comentario['comentario'], 0, 50) . (strlen($comentario['comentario']) > 50 ? '...' : '')); ?>
                    </td>
                    <td>
                        <div class="comment-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $comentario['calificacion']): ?>
                                    <span class="star">★</span>
                                <?php else: ?>
                                    <span class="star" style="color: #666;">☆</span>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($comentario['created_at'])); ?></td>
                    <td>
                        <a href="detalle.php?id=<?php echo $comentario['contenido_id']; ?>" class="action-button view-button">Ver</a>
                        <a href="admin/eliminar_comentario.php?id=<?php echo $comentario['id']; ?>" class="action-button delete-button" onclick="return confirm('¿Estás seguro de eliminar este comentario?')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-results">
            <p>No se encontraron comentarios que coincidan con tu búsqueda.</p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/admin_footer.php'; ?>