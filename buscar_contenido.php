<?php
// Formulario para buscar contenidos (series, películas, libros)
require_once 'includes/functions.php';
require_once 'config/db.php';

// Inicializar variables de búsqueda
$titulo = '';
$tipo = '';
$genero = '';
$calificacion_min = '';
$fecha_desde = '';
$fecha_hasta = '';
$plataforma = '';
$resultados = [];
$total_resultados = 0;
$busqueda_realizada = false;

// Procesar formulario de búsqueda
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $busqueda_realizada = true;
    
    // Recoger y sanitizar parámetros de búsqueda
    $titulo = isset($_POST['titulo']) ? sanitize($_POST['titulo']) : '';
    $tipo = isset($_POST['tipo']) ? sanitize($_POST['tipo']) : '';
    $genero = isset($_POST['genero']) ? sanitize($_POST['genero']) : '';
    $calificacion_min = isset($_POST['calificacion_min']) ? floatval($_POST['calificacion_min']) : 0;
    $fecha_desde = isset($_POST['fecha_desde']) ? sanitize($_POST['fecha_desde']) : '';
    $fecha_hasta = isset($_POST['fecha_hasta']) ? sanitize($_POST['fecha_hasta']) : '';
    $plataforma = isset($_POST['plataforma']) ? intval($_POST['plataforma']) : 0;
    
    // Construir consulta SQL base
    $sql = "SELECT c.* FROM contenidos c";
    
    // Añadir join si se filtra por plataforma
    if ($plataforma > 0) {
        $sql .= " JOIN contenido_plataforma cp ON c.id = cp.contenido_id";
    }
    
    // Iniciar condiciones WHERE
    $where_conditions = [];
    $params = [];
    
    // Añadir condiciones según los filtros
    if (!empty($titulo)) {
        $where_conditions[] = "c.titulo LIKE ?";
        $params[] = "%$titulo%";
    }
    
    if (!empty($tipo)) {
        $where_conditions[] = "c.tipo = ?";
        $params[] = $tipo;
    }
    
    if (!empty($genero)) {
        $where_conditions[] = "c.generos LIKE ?";
        $params[] = "%$genero%";
    }
    
    if ($calificacion_min > 0) {
        $where_conditions[] = "c.calificacion >= ?";
        $params[] = $calificacion_min;
    }
    
    if (!empty($fecha_desde)) {
        $where_conditions[] = "c.fecha_estreno >= ?";
        $params[] = $fecha_desde;
    }
    
    if (!empty($fecha_hasta)) {
        $where_conditions[] = "c.fecha_estreno <= ?";
        $params[] = $fecha_hasta;
    }
    
    if ($plataforma > 0) {
        $where_conditions[] = "cp.plataforma_id = ?";
        $params[] = $plataforma;
    }
    
    // Añadir condiciones WHERE a la consulta
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    // Añadir orden
    $sql .= " ORDER BY c.titulo ASC";
    
    // Ejecutar consulta
    $result = query($sql, $params);
    $resultados = fetch_all($result);
    $total_resultados = count($resultados);
}

// Obtener lista de plataformas para el selector
$sql_plataformas = "SELECT * FROM plataformas ORDER BY nombre ASC";
$result_plataformas = query($sql_plataformas);
$plataformas = fetch_all($result_plataformas);

// Obtener géneros únicos para el selector
$sql_generos = "SELECT DISTINCT generos FROM contenidos";
$result_generos = query($sql_generos);
$generos_data = fetch_all($result_generos);

// Procesar géneros (separar por comas y eliminar duplicados)
$generos_unicos = [];
foreach ($generos_data as $row) {
    $generos_lista = explode(',', $row['generos']);
    foreach ($generos_lista as $g) {
        $g = trim($g);
        if (!empty($g) && !in_array($g, $generos_unicos)) {
            $generos_unicos[] = $g;
        }
    }
}
sort($generos_unicos);

$page_title = "Buscar Contenido - Full Moon, Full Life";
$page_styles = '
/* Estilos para el formulario de búsqueda */
.search-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 1rem;
}

.search-title {
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

.content-image {
    width: 50px;
    height: 75px;
    object-fit: cover;
    border-radius: 5px;
    box-shadow: 2px 2px 0 rgba(0, 0, 0, 0.5);
}

.content-title {
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    text-shadow: 1px 1px 0 black;
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

.content-rating {
    font-weight: bold;
    color: #ffcc00;
    text-shadow: 1px 1px 0 black;
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

.edit-button {
    background-color: #ffcc00;
    color: black;
    text-shadow: none;
}

.delete-button {
    background-color: #ff3366;
    color: white;
    text-shadow: 1px 1px 0 black;
}
';

include 'includes/header.php';
?>

<div class="search-container">
    <h1 class="search-title">BUSCAR CONTENIDO</h1>
    
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="search-form">
        <div class="form-row">
            <div class="form-group">
                <label for="titulo" class="form-label">TÍTULO</label>
                <input type="text" id="titulo" name="titulo" class="form-input" value="<?php echo htmlspecialchars($titulo); ?>">
            </div>
            
            <div class="form-group">
                <label for="tipo" class="form-label">TIPO</label>
                <select id="tipo" name="tipo" class="form-input">
                    <option value="">Todos</option>
                    <option value="serie" <?php echo $tipo === 'serie' ? 'selected' : ''; ?>>Series</option>
                    <option value="pelicula" <?php echo $tipo === 'pelicula' ? 'selected' : ''; ?>>Películas</option>
                    <option value="libro" <?php echo $tipo === 'libro' ? 'selected' : ''; ?>>Libros</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="genero" class="form-label">GÉNERO</label>
                <select id="genero" name="genero" class="form-input">
                    <option value="">Todos</option>
                    <?php foreach ($generos_unicos as $g): ?>
                    <option value="<?php echo htmlspecialchars($g); ?>" <?php echo $genero === $g ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($g); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="calificacion_min" class="form-label">CALIFICACIÓN MÍNIMA</label>
                <select id="calificacion_min" name="calificacion_min" class="form-input">
                    <option value="0">Cualquiera</option>
                    <option value="5" <?php echo $calificacion_min == 5 ? 'selected' : ''; ?>>5+</option>
                    <option value="6" <?php echo $calificacion_min == 6 ? 'selected' : ''; ?>>6+</option>
                    <option value="7" <?php echo $calificacion_min == 7 ? 'selected' : ''; ?>>7+</option>
                    <option value="8" <?php echo $calificacion_min == 8 ? 'selected' : ''; ?>>8+</option>
                    <option value="9" <?php echo $calificacion_min == 9 ? 'selected' : ''; ?>>9+</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="fecha_desde" class="form-label">DESDE FECHA</label>
                <input type="date" id="fecha_desde" name="fecha_desde" class="form-input" value="<?php echo htmlspecialchars($fecha_desde); ?>">
            </div>
            
            <div class="form-group">
                <label for="fecha_hasta" class="form-label">HASTA FECHA</label>
                <input type="date" id="fecha_hasta" name="fecha_hasta" class="form-input" value="<?php echo htmlspecialchars($fecha_hasta); ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="plataforma" class="form-label">PLATAFORMA</label>
                <select id="plataforma" name="plataforma" class="form-input">
                    <option value="0">Todas</option>
                    <?php foreach ($plataformas as $p): ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo $plataforma == $p['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['nombre']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
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
            Se encontraron <?php echo $total_resultados; ?> resultado(s) para tu búsqueda.
        </p>
        
        <?php if ($total_resultados > 0): ?>
        <table class="results-table">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Géneros</th>
                    <th>Calificación</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados as $contenido): ?>
                <tr>
                    <td>
                        <img src="<?php echo htmlspecialchars($contenido['imagen']); ?>" alt="<?php echo htmlspecialchars($contenido['titulo']); ?>" class="content-image">
                    </td>
                    <td class="content-title"><?php echo htmlspecialchars($contenido['titulo']); ?></td>
                    <td>
                        <span class="content-type <?php echo $contenido['tipo']; ?>">
                            <?php echo ucfirst($contenido['tipo']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($contenido['generos']); ?></td>
                    <td class="content-rating"><?php echo number_format($contenido['calificacion'], 1); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($contenido['fecha_estreno'])); ?></td>
                    <td>
                        <a href="detalle.php?id=<?php echo $contenido['id']; ?>" class="action-button view-button">Ver</a>
                        <?php if (is_admin()): ?>
                        <a href="admin/editar_contenido.php?id=<?php echo $contenido['id']; ?>" class="action-button edit-button">Editar</a>
                        <a href="admin/eliminar_contenido.php?id=<?php echo $contenido['id']; ?>" class="action-button delete-button" onclick="return confirm('¿Estás seguro de eliminar este contenido?')">Eliminar</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-results">
            <p>No se encontraron resultados que coincidan con tu búsqueda.</p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>