<?php
// Formulario para agregar contenido (series, películas, libros)
require_once '../includes/functions.php';
require_once '../config/db.php';

// Verificar si el usuario es administrador
if (!is_admin()) {
    show_message('No tienes permisos para acceder a esta página.', 'error');
    redirect('../index.php');
}

// Obtener todas las plataformas
$sql = "SELECT * FROM plataformas ORDER BY nombre";
$result = query($sql);
$plataformas = fetch_all($result);

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar datos
    $titulo = sanitize($_POST['titulo']);
    $tipo = sanitize($_POST['tipo']);
    $sinopsis = sanitize($_POST['sinopsis']);
    $fecha_estreno = sanitize($_POST['fecha_estreno']);
    $calificacion = floatval($_POST['calificacion']);
    $temporadas = intval($_POST['temporadas'] ?? 1);
    $episodios = !empty($_POST['episodios']) ? intval($_POST['episodios']) : NULL;
    $generos = sanitize($_POST['generos']);
    $plataformas_seleccionadas = $_POST['plataformas'] ?? [];
    
    // Validar imagen
    $imagen = '';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen_tmp = $_FILES['imagen']['tmp_name'];
        $imagen_nombre = $_FILES['imagen']['name'];
        $imagen_ext = strtolower(pathinfo($imagen_nombre, PATHINFO_EXTENSION));
        
        // Validar extensión
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imagen_ext, $extensiones_permitidas)) {
            // Generar nombre único
            $imagen_nuevo_nombre = uniqid() . '.' . $imagen_ext;
            $imagen_destino = '../uploads/contenido/' . $imagen_nuevo_nombre;
            
            // Mover archivo
            if (move_uploaded_file($imagen_tmp, $imagen_destino)) {
                $imagen = 'uploads/contenido/' . $imagen_nuevo_nombre;
            } else {
                show_message('Error al subir la imagen.', 'error');
            }
        } else {
            show_message('Formato de imagen no permitido. Use JPG, PNG o GIF.', 'error');
        }
    } else {
        show_message('Debes subir una imagen.', 'error');
    }
    
    // Si todo está bien, insertar en la base de datos
    if (!empty($imagen)) {
        try {
            // Iniciar transacción
            $conexion->begin_transaction();
            
            // Insertar contenido
            $sql = "INSERT INTO contenidos (titulo, tipo, imagen, sinopsis, fecha_estreno, calificacion, temporadas, episodios, generos) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("sssssdiis", $titulo, $tipo, $imagen, $sinopsis, $fecha_estreno, $calificacion, $temporadas, $episodios, $generos);
            $stmt->execute();
            
            $contenido_id = $conexion->insert_id;
            
            // Insertar relaciones con plataformas
            if (!empty($plataformas_seleccionadas)) {
                $sql = "INSERT INTO contenido_plataforma (contenido_id, plataforma_id) VALUES (?, ?)";
                $stmt = $conexion->prepare($sql);
                
                foreach ($plataformas_seleccionadas as $plataforma_id) {
                    $stmt->bind_param("ii", $contenido_id, $plataforma_id);
                    $stmt->execute();
                }
            }
            
            // Confirmar transacción
            $conexion->commit();
            
            show_message('Contenido agregado correctamente.');
            redirect('admin/listar_contenidos.php');
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $conexion->rollback();
            show_message('Error al agregar el contenido: ' . $e->getMessage(), 'error');
        }
    }
}

$page_title = "Agregar Contenido - Panel de Administración";
$page_styles = '
/* Estilos para el panel de administración */
.admin-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

.admin-title {
    font-size: 2.5rem;
    color: white;
    text-shadow: 3px 3px 0 #ff3366;
    margin-bottom: 2rem;
    text-align: center;
}

.admin-form {
    background-color: rgba(0, 0, 0, 0.7);
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.5);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-size: 1.5rem;
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

.checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 0.5rem;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.checkbox-item input {
    width: 20px;
    height: 20px;
}

.checkbox-item label {
    font-size: 1rem;
    color: white;
}

small {
    display: block;
    margin-top: 0.3rem;
    font-size: 0.8rem;
    color: #ccc;
    font-family: Arial, sans-serif;
}
';

include '../includes/admin_header.php';
?>

<div class="admin-container">
    <h1 class="admin-title">AGREGAR NUEVO CONTENIDO</h1>
    
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data" class="admin-form">
        <div class="form-group">
            <label for="titulo" class="form-label">TÍTULO</label>
            <input type="text" id="titulo" name="titulo" class="form-input" required>
        </div>
        
        <div class="form-group">
            <label for="tipo" class="form-label">TIPO</label>
            <select id="tipo" name="tipo" class="form-input" required>
                <option value="serie">Serie</option>
                <option value="pelicula">Película</option>
                <option value="libro">Libro</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="imagen" class="form-label">IMAGEN DE PORTADA</label>
            <input type="file" id="imagen" name="imagen" class="form-input" accept="image/*" required>
            <small>Tamaño recomendado: 300x450 píxeles</small>
        </div>
        
        <div class="form-group">
            <label for="sinopsis" class="form-label">SINOPSIS</label>
            <textarea id="sinopsis" name="sinopsis" class="form-input" rows="6" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="fecha_estreno" class="form-label">FECHA DE ESTRENO</label>
            <input type="date" id="fecha_estreno" name="fecha_estreno" class="form-input" required>
        </div>
        
        <div class="form-group">
            <label for="calificacion" class="form-label">CALIFICACIÓN (0-10)</label>
            <input type="number" id="calificacion" name="calificacion" class="form-input" min="0" max="10" step="0.1" required>
        </div>
        
        <div class="form-group serie-fields">
            <label for="temporadas" class="form-label">TEMPORADAS</label>
            <input type="number" id="temporadas" name="temporadas" class="form-input" min="1" value="1">
        </div>
        
        <div class="form-group serie-fields">
            <label for="episodios" class="form-label">EPISODIOS</label>
            <input type="number" id="episodios" name="episodios" class="form-input" min="1">
        </div>
        
        <div class="form-group">
            <label for="generos" class="form-label">GÉNEROS</label>
            <input type="text" id="generos" name="generos" class="form-input" placeholder="Acción, Comedia, Drama..." required>
            <small>Separar géneros con comas</small>
        </div>
        
        <div class="form-group">
            <label class="form-label">PLATAFORMAS DISPONIBLES</label>
            <div class="checkbox-group">
                <?php foreach ($plataformas as $plataforma): ?>
                <div class="checkbox-item">
                    <input type="checkbox" id="plataforma_<?php echo $plataforma['id']; ?>" name="plataformas[]" value="<?php echo $plataforma['id']; ?>">
                    <label for="plataforma_<?php echo $plataforma['id']; ?>"><?php echo htmlspecialchars($plataforma['nombre']); ?></label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <button type="submit" class="comic-button">GUARDAR CONTENIDO</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoSelect = document.getElementById('tipo');
    const serieFields = document.querySelectorAll('.serie-fields');
    
    // Función para mostrar/ocultar campos según el tipo
    function toggleFields() {
        if (tipoSelect.value === 'serie') {
            serieFields.forEach(field => field.style.display = 'block');
        } else {
            serieFields.forEach(field => field.style.display = 'none');
        }
    }
    
    // Ejecutar al cargar la página
    toggleFields();
    
    // Ejecutar cuando cambie el tipo
    tipoSelect.addEventListener('change', toggleFields);
});
</script>

<?php include '../includes/admin_footer.php'; ?>