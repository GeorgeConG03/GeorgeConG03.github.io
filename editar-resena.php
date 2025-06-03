<?php
// Formulario para editar reseñas
require_once __DIR__ . '/includes/init.php';

// Verificar si el usuario es administrador
if (!is_logged_in() || !is_admin()) {
    show_message("Acceso denegado. Debes ser administrador.", "error");
    redirect("../index.php");
    exit;
}

$resena = null;
$error = '';
$success = '';

// Obtener reseña si se proporciona ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_resena = (int)$_GET['id'];
    
    $query = "SELECT r.*, u.nombre as nombre_usuario 
              FROM resenas r 
              LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario 
              WHERE r.id_resena = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id_resena);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $resena = $result->fetch_assoc();
    } else {
        $error = "Reseña no encontrada.";
    }
    $stmt->close();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_resena'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = "Token de seguridad inválido.";
    } else {
        $id_resena = (int)$_POST['id_resena'];
        $titulo = sanitize($_POST['titulo']);
        $categoria = sanitize($_POST['categoria']);
        $calificacion = (int)$_POST['calificacion'];
        $descripcion = sanitize($_POST['descripcion']);
        $pros = sanitize($_POST['pros']);
        $contras = sanitize($_POST['contras']);
        $recomendacion = sanitize($_POST['recomendacion']);
        $temporadas = !empty($_POST['temporadas']) ? (int)$_POST['temporadas'] : null;
        $episodios = !empty($_POST['episodios']) ? (int)$_POST['episodios'] : null;
        $duracion = !empty($_POST['duracion']) ? sanitize($_POST['duracion']) : null;
        $paginas = !empty($_POST['paginas']) ? (int)$_POST['paginas'] : null;
        
        // Actualizar reseña
        $query = "UPDATE resenas SET 
                  titulo = ?, categoria = ?, calificacion = ?, descripcion = ?, 
                  pros = ?, contras = ?, recomendacion = ?, temporadas = ?, 
                  episodios = ?, duracion = ?, paginas = ?, fecha_actualizacion = NOW() 
                  WHERE id_resena = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ssissssiisii", $titulo, $categoria, $calificacion, $descripcion, 
                         $pros, $contras, $recomendacion, $temporadas, $episodios, 
                         $duracion, $paginas, $id_resena);
        
        if ($stmt->execute()) {
            $success = "Reseña actualizada correctamente.";
            
            // Recargar datos de la reseña
            $query = "SELECT r.*, u.nombre as nombre_usuario 
                      FROM resenas r 
                      LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario 
                      WHERE r.id_resena = ?";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("i", $id_resena);
            $stmt->execute();
            $result = $stmt->get_result();
            $resena = $result->fetch_assoc();
            $stmt->close();
        } else {
            $error = "Error al actualizar la reseña.";
        }
        $stmt->close();
    }
}

// Obtener lista de reseñas para el selector
$query_resenas = "SELECT r.id_resena, r.titulo, r.categoria, u.nombre as autor 
                  FROM resenas r 
                  LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario 
                  ORDER BY r.fecha_creacion DESC";
$result_resenas = $conexion->query($query_resenas);

$page_title = "Editar Reseña - Panel de Administración";
$include_base_css = true;

include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1 class="admin-title">Editar Reseña</h1>
        <a href="index.php" class="btn btn-back">← Volver al Panel</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- Selector de reseña -->
    <div class="form-section">
        <h2>Seleccionar Reseña</h2>
        <form method="GET" class="selector-form">
            <div class="form-group">
                <label for="id">Reseña:</label>
                <select name="id" id="id" class="form-select" onchange="this.form.submit()">
                    <option value="">Selecciona una reseña</option>
                    <?php while ($rev = $result_resenas->fetch_assoc()): ?>
                        <option value="<?php echo $rev['id_resena']; ?>" 
                                <?php echo (isset($_GET['id']) && $_GET['id'] == $rev['id_resena']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($rev['titulo']) . ' (' . ucfirst($rev['categoria']) . ') - ' . htmlspecialchars($rev['autor']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </form>
    </div>

    <!-- Formulario de edición -->
    <?php if ($resena): ?>
    <div class="form-section">
        <h2>Datos de la Reseña</h2>
        <form method="POST" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="id_resena" value="<?php echo $resena['id_resena']; ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="titulo">Título:</label>
                    <input type="text" id="titulo" name="titulo" class="form-input" 
                           value="<?php echo htmlspecialchars($resena['titulo']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="categoria">Categoría:</label>
                    <select id="categoria" name="categoria" class="form-select" required onchange="mostrarCamposCategoria()">
                        <option value="serie" <?php echo $resena['categoria'] === 'serie' ? 'selected' : ''; ?>>Serie</option>
                        <option value="pelicula" <?php echo $resena['categoria'] === 'pelicula' ? 'selected' : ''; ?>>Película</option>
                        <option value="libro" <?php echo $resena['categoria'] === 'libro' ? 'selected' : ''; ?>>Libro</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="calificacion">Calificación (1-5):</label>
                    <select id="calificacion" name="calificacion" class="form-select" required>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $resena['calificacion'] == $i ? 'selected' : ''; ?>>
                                <?php echo $i; ?> - <?php echo ['Muy malo', 'Malo', 'Regular', 'Bueno', 'Excelente'][$i-1]; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="recomendacion">Recomendación:</label>
                    <select id="recomendacion" name="recomendacion" class="form-select" required>
                        <option value="si" <?php echo $resena['recomendacion'] === 'si' ? 'selected' : ''; ?>>Sí</option>
                        <option value="no" <?php echo $resena['recomendacion'] === 'no' ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
            </div>

            <!-- Campos específicos por categoría -->
            <div id="campos-serie" class="category-fields" style="<?php echo $resena['categoria'] === 'serie' ? 'display: block;' : 'display: none;'; ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="temporadas">Temporadas:</label>
                        <input type="number" id="temporadas" name="temporadas" class="form-input" min="1" 
                               value="<?php echo $resena['temporadas']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="episodios">Episodios:</label>
                        <input type="number" id="episodios" name="episodios" class="form-input" min="1" 
                               value="<?php echo $resena['episodios']; ?>">
                    </div>
                </div>
            </div>

            <div id="campos-pelicula" class="category-fields" style="<?php echo $resena['categoria'] === 'pelicula' ? 'display: block;' : 'display: none;'; ?>">
                <div class="form-group">
                    <label for="duracion">Duración:</label>
                    <input type="text" id="duracion" name="duracion" class="form-input" 
                           value="<?php echo htmlspecialchars($resena['duracion']); ?>" placeholder="Ej: 120 minutos">
                </div>
            </div>

            <div id="campos-libro" class="category-fields" style="<?php echo $resena['categoria'] === 'libro' ? 'display: block;' : 'display: none;'; ?>">
                <div class="form-group">
                    <label for="paginas">Páginas:</label>
                    <input type="number" id="paginas" name="paginas" class="form-input" min="1" 
                           value="<?php echo $resena['paginas']; ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" class="form-textarea" rows="4" required><?php echo htmlspecialchars($resena['descripcion']); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="pros">Lo Bueno:</label>
                    <textarea id="pros" name="pros" class="form-textarea" rows="3" required><?php echo htmlspecialchars($resena['pros']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="contras">Lo Malo:</label>
                    <textarea id="contras" name="contras" class="form-textarea" rows="3" required><?php echo htmlspecialchars($resena['contras']); ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="actualizar_resena" class="btn btn-primary">Actualizar Reseña</button>
                <a href="resenas.php" class="btn btn-secondary">Ver Todas las Reseñas</a>
            </div>
        </form>
    </div>

    <!-- Información adicional -->
    <div class="form-section">
        <h2>Información Adicional</h2>
        <div class="info-grid">
            <div class="info-item">
                <strong>ID de Reseña:</strong>
                <span><?php echo $resena['id_resena']; ?></span>
            </div>
            <div class="info-item">
                <strong>Autor:</strong>
                <span><?php echo htmlspecialchars($resena['nombre_usuario']); ?></span>
            </div>
            <div class="info-item">
                <strong>Fecha de Creación:</strong>
                <span><?php echo date('d/m/Y H:i', strtotime($resena['fecha_creacion'])); ?></span>
            </div>
            <div class="info-item">
                <strong>Última Actualización:</strong>
                <span><?php echo date('d/m/Y H:i', strtotime($resena['fecha_actualizacion'])); ?></span>
            </div>
            <div class="info-item">
                <strong>Archivo PHP:</strong>
                <span><?php echo $resena['archivo_php'] ? htmlspecialchars($resena['archivo_php']) : 'No generado'; ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function mostrarCamposCategoria() {
    // Ocultar todos los campos específicos
    document.getElementById('campos-serie').style.display = 'none';
    document.getElementById('campos-pelicula').style.display = 'none';
    document.getElementById('campos-libro').style.display = 'none';
    
    // Mostrar campos según la categoría seleccionada
    var categoria = document.getElementById('categoria').value;
    
    if (categoria === 'serie') {
        document.getElementById('campos-serie').style.display = 'block';
    } else if (categoria === 'pelicula') {
        document.getElementById('campos-pelicula').style.display = 'block';
    } else if (categoria === 'libro') {
        document.getElementById('campos-libro').style.display = 'block';
    }
}
</script>

<style>
/* Reutilizar estilos del formulario anterior */
.admin-container {
    max-width: 1000px;
    margin: 2rem auto;
    padding: 2rem;
    background-color: rgba(255, 255, 255, 0.95);
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #0066cc;
}

.admin-title {
    font-family: "Bangers", cursive;
    font-size: 2.5rem;
    color: #0066cc;
    text-shadow: 2px 2px 0 #000;
    margin: 0;
}

.btn-back {
    background-color: #666;
    color: white;
    padding: 0.8rem 1.5rem;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-family: "Bangers", cursive;
    font-size: 1.1rem;
}

.form-section {
    background-color: white;
    border: 2px solid #0066cc;
    border-radius: 10px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.form-section h2 {
    font-family: "Bangers", cursive;
    font-size: 1.8rem;
    color: #0066cc;
    margin-bottom: 1.5rem;
    text-align: center;
}

.selector-form {
    max-width: 500px;
    margin: 0 auto;
}

.admin-form {
    max-width: 800px;
    margin: 0 auto;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    color: #0066cc;
    margin-bottom: 0.5rem;
}

.form-input, .form-select, .form-textarea {
    width: 100%;
    padding: 0.8rem;
    border: 2px solid #ddd;
    border-radius: 5px;
    font-family: Arial, sans-serif;
    font-size: 1rem;
    transition: border-color 0.2s;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
    border-color: #0066cc;
    outline: none;
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

.category-fields {
    background-color: rgba(0, 102, 204, 0.1);
    border: 2px solid #0066cc;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
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
    background-color: #0066cc;
    color: white;
}

.btn-secondary {
    background-color: #ff3366;
    color: white;
}

.btn:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

.alert {
    padding: 1rem;
    border-radius: 5px;
    margin-bottom: 1.5rem;
    text-align: center;
    font-weight: bold;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.info-item {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 5px;
    border-left: 4px solid #0066cc;
}

.info-item strong {
    display: block;
    color: #0066cc;
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .admin-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<?php include '../includes/footer.php'; ?>