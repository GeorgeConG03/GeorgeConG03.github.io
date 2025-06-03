<?php
// Archivo: admin-resenas.php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/auth_admin.php';

verificarAdmin();

$page_title = "Administración de Reseñas";
$include_base_css = true;

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'actualizar':
                $id = $_POST['id_resena'];
                $titulo = $_POST['titulo'];
                $categoria = $_POST['categoria'];
                $calificacion = $_POST['calificacion'];
                $pros = $_POST['pros'];
                $contras = $_POST['contras'];
                $descripcion = $_POST['descripcion'];
                $recomendacion = $_POST['recomendacion'];
                
                $sql = "UPDATE resenas SET titulo = ?, categoria = ?, calificacion = ?, 
                        pros = ?, contras = ?, descripcion = ?, recomendacion = ? 
                        WHERE id_resena = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("sssssssi", $titulo, $categoria, $calificacion, 
                                $pros, $contras, $descripcion, $recomendacion, $id);
                
                if ($stmt->execute()) {
                    $mensaje = "Reseña actualizada exitosamente";
                } else {
                    $error = "Error al actualizar reseña: " . $conexion->error;
                }
                break;
                
            case 'eliminar':
                $id = $_POST['id_resena'];
                
                $sql = "DELETE FROM resenas WHERE id_resena = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $mensaje = "Reseña eliminada exitosamente";
                } else {
                    $error = "Error al eliminar reseña: " . $conexion->error;
                }
                break;
        }
    }
}

// Obtener todas las reseñas
$sql_resenas = "SELECT r.*, u.nombre as autor FROM resenas r 
                LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario 
                ORDER BY r.fecha_creacion DESC";
$result_resenas = $conexion->query($sql_resenas);

include 'includes/header.php';
?>

<div class="admin-container">
    <h1 class="admin-title">Administración de Reseñas</h1>
    
    <?php if (isset($mensaje)): ?>
        <div class="alert alert-success"><?php echo $mensaje; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Lista de reseñas existentes -->
    <div class="table-section">
        <h2>Reseñas Existentes</h2>
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Categoría</th>
                        <th>Calificación</th>
                        <th>Autor</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($resena = $result_resenas->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $resena['id_resena']; ?></td>
                        <td><?php echo htmlspecialchars($resena['titulo']); ?></td>
                        <td><?php echo ucfirst($resena['categoria']); ?></td>
                        <td>
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $resena['calificacion'] ? "★" : "☆";
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($resena['autor']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($resena['fecha_creacion'])); ?></td>
                        <td class="actions">
                            <button onclick="editarResena(<?php echo htmlspecialchars(json_encode($resena)); ?>)" 
                                    class="btn btn-edit">Editar</button>
                            <form method="POST" style="display: inline;" 
                                  onsubmit="return confirm('¿Estás seguro de eliminar esta reseña?')">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id_resena" value="<?php echo $resena['id_resena']; ?>">
                                <button type="submit" class="btn btn-delete">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para editar reseña -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Editar Reseña</h2>
        <form method="POST" id="editForm">
            <input type="hidden" name="accion" value="actualizar">
            <input type="hidden" name="id_resena" id="edit_id">
            
            <div class="form-group">
                <label for="edit_titulo">Título:</label>
                <input type="text" id="edit_titulo" name="titulo" required>
            </div>
            
            <div class="form-group">
                <label for="edit_categoria">Categoría:</label>
                <select id="edit_categoria" name="categoria" required>
                    <option value="serie">Serie</option>
                    <option value="pelicula">Película</option>
                    <option value="libro">Libro</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="edit_calificacion">Calificación:</label>
                <select id="edit_calificacion" name="calificacion" required>
                    <option value="1">1 Estrella</option>
                    <option value="2">2 Estrellas</option>
                    <option value="3">3 Estrellas</option>
                    <option value="4">4 Estrellas</option>
                    <option value="5">5 Estrellas</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="edit_pros">Pros:</label>
                <textarea id="edit_pros" name="pros" rows="3" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="edit_contras">Contras:</label>
                <textarea id="edit_contras" name="contras" rows="3" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="edit_descripcion">Descripción:</label>
                <textarea id="edit_descripcion" name="descripcion" rows="5" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="edit_recomendacion">Recomendación:</label>
                <select id="edit_recomendacion" name="recomendacion" required>
                    <option value="si">Sí</option>
                    <option value="no">No</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Actualizar Reseña</button>
        </form>
    </div>
</div>

<style>
/* Reutilizar estilos del archivo anterior */
.admin-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 2rem;
    background-color: rgba(255, 255, 255, 0.95);
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.admin-title {
    font-family: "Bangers", cursive;
    font-size: 3rem;
    color: #0066cc;
    text-shadow: 2px 2px 0 #000;
    text-align: center;
    margin-bottom: 2rem;
}

.table-section {
    margin-bottom: 3rem;
    background: white;
    padding: 2rem;
    border-radius: 10px;
    border: 2px solid #0066cc;
}

.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 1rem;
}

.form-group label {
    font-weight: bold;
    margin-bottom: 0.5rem;
    color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 0.8rem;
    border: 2px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #0066cc;
    outline: none;
}

.btn {
    padding: 0.8rem 1.5rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-family: "Bangers", cursive;
    font-size: 1.1rem;
    transition: all 0.2s;
}

.btn-primary {
    background-color: #0066cc;
    color: white;
}

.btn-edit {
    background-color: #28a745;
    color: white;
    margin-right: 0.5rem;
}

.btn-delete {
    background-color: #dc3545;
    color: white;
}

.btn:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

.table-container {
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.admin-table th,
.admin-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.admin-table th {
    background-color: #0066cc;
    color: white;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
}

.admin-table tr:hover {
    background-color: #f5f5f5;
}

.actions {
    white-space: nowrap;
}

.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 5px;
    font-weight: bold;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 2rem;
    border-radius: 10px;
    width: 80%;
    max-width: 600px;
    position: relative;
    max-height: 80vh;
    overflow-y: auto;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: black;
}
</style>

<script>
function editarResena(resena) {
    document.getElementById('edit_id').value = resena.id_resena;
    document.getElementById('edit_titulo').value = resena.titulo;
    document.getElementById('edit_categoria').value = resena.categoria;
    document.getElementById('edit_calificacion').value = resena.calificacion;
    document.getElementById('edit_pros').value = resena.pros;
    document.getElementById('edit_contras').value = resena.contras;
    document.getElementById('edit_descripcion').value = resena.descripcion;
    document.getElementById('edit_recomendacion').value = resena.recomendacion;
    
    document.getElementById('editModal').style.display = 'block';
}

// Cerrar modal
document.querySelector('.close').onclick = function() {
    document.getElementById('editModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == document.getElementById('editModal')) {
        document.getElementById('editModal').style.display = 'none';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
