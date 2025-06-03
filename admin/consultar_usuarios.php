<?php
// Formulario para consultar usuarios
require_once '../includes/functions.php';
require_once '../config/db.php';

// Verificar si el usuario es administrador
if (!is_admin()) {
    show_message('No tienes permisos para acceder a esta página.', 'error');
    redirect('../index.php');
}

// Inicializar variables de búsqueda
$username = '';
$email = '';
$fecha_desde = '';
$fecha_hasta = '';
$estado = '';
$resultados = [];
$total_resultados = 0;
$busqueda_realizada = false;

// Procesar formulario de búsqueda
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $busqueda_realizada = true;
    
    // Recoger y sanitizar parámetros de búsqueda
    $username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $fecha_desde = isset($_POST['fecha_desde']) ? sanitize($_POST['fecha_desde']) : '';
    $fecha_hasta = isset($_POST['fecha_hasta']) ? sanitize($_POST['fecha_hasta']) : '';
    $estado = isset($_POST['estado']) ? sanitize($_POST['estado']) : '';
    
    // Construir consulta SQL base
    $sql = "SELECT * FROM usuarios";
    
    // Iniciar condiciones WHERE
    $where_conditions = [];
    $params = [];
    
    // Añadir condiciones según los filtros
    if (!empty($username)) {
        $where_conditions[] = "username LIKE ?";
        $params[] = "%$username%";
    }
    
    if (!empty($email)) {
        $where_conditions[] = "email LIKE ?";
        $params[] = "%$email%";
    }
    
    if (!empty($fecha_desde)) {
        $where_conditions[] = "created_at >= ?";
        $params[] = $fecha_desde . ' 00:00:00';
    }
    
    if (!empty($fecha_hasta)) {
        $where_conditions[] = "created_at <= ?";
        $params[] = $fecha_hasta . ' 23:59:59';
    }
    
    if ($estado === 'activo') {
        $where_conditions[] = "is_active = 1";
    } elseif ($estado === 'inactivo') {
        $where_conditions[] = "is_active = 0";
    }
    
    // Añadir condiciones WHERE a la consulta
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    // Añadir orden
    $sql .= " ORDER BY username ASC";
    
    // Ejecutar consulta
    $result = query($sql, $params);
    $resultados = fetch_all($result);
    $total_resultados = count($resultados);
}

$page_title = "Consultar Usuarios - Panel de Administración";
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

.user-status {
    display: inline-block;
    padding: 0.3rem 0.6rem;
    border-radius: 3px;
    font-size: 0.8rem;
    text-transform: uppercase;
}

.status-active {
    background-color: #33cc33;
    color: white;
}

.status-inactive {
    background-color: #ff3366;
    color: white;
}

.user-role {
    display: inline-block;
    padding: 0.3rem 0.6rem;
    border-radius: 3px;
    font-size: 0.8rem;
    text-transform: uppercase;
}

.role-admin {
    background-color: #ffcc00;
    color: black;
}

.role-user {
    background-color: #0066cc;
    color: white;
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

.toggle-button {
    background-color: #0066cc;
    color: white;
    text-shadow: 1px 1px 0 black;
}
';

include '../includes/admin_header.php';
?>

<div class="admin-container">
    <h1 class="admin-title">CONSULTAR USUARIOS</h1>
    
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="search-form">
        <div class="form-row">
            <div class="form-group">
                <label for="username" class="form-label">NOMBRE DE USUARIO</label>
                <input type="text" id="username" name="username" class="form-input" value="<?php echo htmlspecialchars($username); ?>">
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">CORREO ELECTRÓNICO</label>
                <input type="text" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($email); ?>">
            </div>
            
            <div class="form-group">
                <label for="estado" class="form-label">ESTADO</label>
                <select id="estado" name="estado" class="form-input">
                    <option value="">Todos</option>
                    <option value="activo" <?php echo $estado === 'activo' ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactivo" <?php echo $estado === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
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
            Se encontraron <?php echo $total_resultados; ?> usuario(s) para tu búsqueda.
        </p>
        
        <?php if ($total_resultados > 0): ?>
        <table class="results-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Estado</th>
                    <th>Rol</th>
                    <th>Fecha Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados as $usuario): ?>
                <tr>
                    <td><?php echo $usuario['id']; ?></td>
                    <td><?php echo htmlspecialchars($usuario['username']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                    <td>
                        <span class="user-status <?php echo $usuario['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $usuario['is_active'] ? 'Activo' : 'Inactivo'; ?>
                        </span>
                    </td>
                    <td>
                        <span class="user-role <?php echo $usuario['is_admin'] ? 'role-admin' : 'role-user'; ?>">
                            <?php echo $usuario['is_admin'] ? 'Administrador' : 'Usuario'; ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($usuario['created_at'])); ?></td>
                    <td>
                        <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>" class="action-button edit-button">Editar</a>
                        <a href="toggle_usuario.php?id=<?php echo $usuario['id']; ?>&action=<?php echo $usuario['is_active'] ? 'desactivar' : 'activar'; ?>" class="action-button toggle-button" onclick="return confirm('¿Estás seguro de <?php echo $usuario['is_active'] ? 'desactivar' : 'activar'; ?> este usuario?')">
                            <?php echo $usuario['is_active'] ? 'Desactivar' : 'Activar'; ?>
                        </a>
                        <a href="eliminar_usuario.php?id=<?php echo $usuario['id']; ?>" class="action-button delete-button" onclick="return confirm('¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-results">
            <p>No se encontraron usuarios que coincidan con tu búsqueda.</p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/admin_footer.php'; ?>