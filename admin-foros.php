<?php
// Archivo: admin-foros.php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/auth_admin.php';

verificarAdmin();

$page_title = "Administración de Foros";
$include_base_css = true;

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'crear':
                $titulo = $_POST['titulo'];
                
                $sql = "INSERT INTO foros (titulo) VALUES (?)";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("s", $titulo);
                
                if ($stmt->execute()) {
                    $mensaje = "Foro creado exitosamente";
                } else {
                    $error = "Error al crear foro: " . $conexion->error;
                }
                break;
                
            case 'actualizar':
                $id = $_POST['id_foro'];
                $titulo = $_POST['titulo'];
                
                $sql = "UPDATE foros SET titulo = ? WHERE id_foro = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("si", $titulo, $id);
                
                if ($stmt->execute()) {
                    $mensaje = "Foro actualizado exitosamente";
                } else {
                    $error = "Error al actualizar foro: " . $conexion->error;
                }
                break;
                
            case 'eliminar':
                $id = $_POST['id_foro'];
                
                $sql = "DELETE FROM foros WHERE id_foro = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $mensaje = "Foro eliminado exitosamente";
                } else {
                    $error = "Error al eliminar foro: " . $conexion->error;
                }
                break;
        }
    }
}

// Obtener todos los foros con estadísticas
$sql_foros = "SELECT f.*, u.nombre as nombre_creador,
              f.total_miembros,
              f.total_posts
              FROM foros f 
              LEFT JOIN usuarios u ON f.id_creador = u.id_usuario
              WHERE f.activo = 1
              ORDER BY f.fecha_creacion DESC";
$result_foros = $conexion->query($sql_foros);

include 'includes/header.php';
?>

<div class="admin-container">
    <h1 class="admin-title">Administración de Foros</h1>
    
    <?php if (isset($mensaje)): ?>
        <div class="alert alert-success"><?php echo $mensaje; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Formulario para crear nuevo foro -->
    <div class="form-section">
        <h2>Crear Nuevo Foro</h2>
        <form method="POST" class="admin-form">
            <input type="hidden" name="accion" value="crear">
            
            <div class="form-group">
                <label for="titulo">Título del Foro:</label>
                <input type="text" id="titulo" name="titulo" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Crear Foro</button>
        </form>
    </div>
    
    <!-- Lista de foros existentes -->
    <div class="table-section">
        <h2>Foros Existentes</h2>
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Creador</th>
                        <th>Miembros</th>
                        <th>Posts</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($foro = $result_foros->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $foro['id_foro']; ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars($foro['archivo_php']); ?>" target="_blank">
                                <?php echo htmlspecialchars($foro['titulo']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($foro['nombre_creador'] ?: 'Usuario'); ?></td>
                        <td><?php echo $foro['total_miembros']; ?></td>
                        <td><?php echo $foro['total_posts']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($foro['fecha_creacion'])); ?></td>
                        <td class="actions">
                            <button onclick="editarForo(<?php echo htmlspecialchars(json_encode($foro)); ?>)" 
                                    class="btn btn-edit">Editar</button>
                            <form method="POST" style="display: inline;" 
                                  onsubmit="return confirm('¿Estás seguro de eliminar este foro? Se eliminarán todos los posts asociados.')">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id_foro" value="<?php echo $foro['id_foro']; ?>">
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

<!-- Modal para editar foro -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Editar Foro</h2>
        <form method="POST" id="editForm">
            <input type="hidden" name="accion" value="actualizar">
            <input type="hidden" name="id_foro" id="edit_id">
            
            <div class="form-group">
                <label for="edit_titulo">Título del Foro:</label>
                <input type="text" id="edit_titulo" name="titulo" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Actualizar Foro</button>
        </form>
    </div>
</div>

<style>
/* Reutilizar estilos anteriores */
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

.form-section, .table-section {
    margin-bottom: 3rem;
    background: white;
    padding: 2rem;
    border-radius: 10px;
    border: 2px solid #0066cc;
}

.admin-form {
    display: grid;
    gap: 1rem;
    max-width: 500px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: bold;
    margin-bottom: 0.5rem;
    color: #333;
}

.form-group input {
    padding: 0.8rem;
    border: 2px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

.form-group input:focus {
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
    margin: 15% auto;
    padding: 2rem;
    border-radius: 10px;
    width: 80%;
    max-width: 500px;
    position: relative;
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
function editarForo(foro) {
    document.getElementById('edit_id').value = foro.id_foro;
    document.getElementById('edit_titulo').value = foro.titulo;
    
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
