<?php
// Archivo: admin-usuarios.php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/auth_admin.php';

verificarAdmin();

$page_title = "Administración de Usuarios";
$include_base_css = true;

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'crear':
                $nombre = $_POST['nombre'];
                $correo = $_POST['correo'];
                $contraseña = password_hash($_POST['contraseña'], PASSWORD_DEFAULT);
                $es_admin = isset($_POST['es_admin']) ? 1 : 0;
                
                $sql = "INSERT INTO usuarios (nombre, correo, contraseña, es_admin) VALUES (?, ?, ?, ?)";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("sssi", $nombre, $correo, $contraseña, $es_admin);
                
                if ($stmt->execute()) {
                    $mensaje = "Usuario creado exitosamente";
                } else {
                    $error = "Error al crear usuario: " . $conexion->error;
                }
                break;
                
            case 'actualizar':
                $id = $_POST['id_usuario'];
                $nombre = $_POST['nombre'];
                $correo = $_POST['correo'];
                $es_admin = isset($_POST['es_admin']) ? 1 : 0;
                
                $sql = "UPDATE usuarios SET nombre = ?, correo = ?, es_admin = ? WHERE id_usuario = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("ssii", $nombre, $correo, $es_admin, $id);
                
                if ($stmt->execute()) {
                    $mensaje = "Usuario actualizado exitosamente";
                } else {
                    $error = "Error al actualizar usuario: " . $conexion->error;
                }
                break;
                
            case 'eliminar':
                $id = $_POST['id_usuario'];
                
                $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $mensaje = "Usuario eliminado exitosamente";
                } else {
                    $error = "Error al eliminar usuario: " . $conexion->error;
                }
                break;
        }
    }
}

// Obtener todos los usuarios
$sql_usuarios = "SELECT u.*, p.biografia, p.telefono FROM usuarios u 
                 LEFT JOIN perfiles p ON u.id_usuario = p.id_usuario 
                 ORDER BY u.fecha_registro DESC";
$result_usuarios = $conexion->query($sql_usuarios);

include 'includes/header.php';
?>

<div class="admin-container">
    <h1 class="admin-title">Administración de Usuarios</h1>
    
    <?php if (isset($mensaje)): ?>
        <div class="alert alert-success"><?php echo $mensaje; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Formulario para crear nuevo usuario -->
    <div class="form-section">
        <h2>Crear Nuevo Usuario</h2>
        <form method="POST" class="admin-form">
            <input type="hidden" name="accion" value="crear">
            
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            
            <div class="form-group">
                <label for="correo">Correo:</label>
                <input type="email" id="correo" name="correo" required>
            </div>
            
            <div class="form-group">
                <label for="contraseña">Contraseña:</label>
                <input type="password" id="contraseña" name="contraseña" required>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="es_admin" value="1">
                    Es Administrador
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary">Crear Usuario</button>
        </form>
    </div>
    
    <!-- Lista de usuarios existentes -->
    <div class="table-section">
        <h2>Usuarios Existentes</h2>
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Fecha Registro</th>
                        <th>Admin</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($usuario = $result_usuarios->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $usuario['id_usuario']; ?></td>
                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></td>
                        <td><?php echo $usuario['es_admin'] ? 'Sí' : 'No'; ?></td>
                        <td class="actions">
                            <button onclick="editarUsuario(<?php echo htmlspecialchars(json_encode($usuario)); ?>)" 
                                    class="btn btn-edit">Editar</button>
                            <form method="POST" style="display: inline;" 
                                  onsubmit="return confirm('¿Estás seguro de eliminar este usuario?')">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
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

<!-- Modal para editar usuario -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Editar Usuario</h2>
        <form method="POST" id="editForm">
            <input type="hidden" name="accion" value="actualizar">
            <input type="hidden" name="id_usuario" id="edit_id">
            
            <div class="form-group">
                <label for="edit_nombre">Nombre:</label>
                <input type="text" id="edit_nombre" name="nombre" required>
            </div>
            
            <div class="form-group">
                <label for="edit_correo">Correo:</label>
                <input type="email" id="edit_correo" name="correo" required>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="edit_es_admin" name="es_admin" value="1">
                    Es Administrador
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
        </form>
    </div>
</div>

<style>
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
function editarUsuario(usuario) {
    document.getElementById('edit_id').value = usuario.id_usuario;
    document.getElementById('edit_nombre').value = usuario.nombre;
    document.getElementById('edit_correo').value = usuario.correo;
    document.getElementById('edit_es_admin').checked = usuario.es_admin == 1;
    
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
