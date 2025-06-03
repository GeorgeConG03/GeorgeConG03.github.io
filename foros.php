<?php
// Archivo: foros.php
require_once 'includes/init.php';

// Obtener todos los foros
$sql = "SELECT f.id_foro, f.titulo, f.descripcion, f.imagen, f.fecha_creacion, 
        f.id_usuario, u.nombre as autor, 
        (SELECT COUNT(*) FROM publicaciones_foro WHERE id_foro = f.id_foro) as num_publicaciones
        FROM foros f 
        LEFT JOIN usuarios u ON f.id_usuario = u.id_usuario 
        WHERE f.estado = 'activo'
        ORDER BY f.fecha_creacion DESC";
$result = $conexion->query($sql);

$page_title = "Foros - Full Moon, Full Life";
$include_base_css = true;

$page_styles = '
.listado-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 1rem;
}

.listado-header {
    text-align: center;
    margin-bottom: 2rem;
}

.listado-title {
    font-family: "Bangers", cursive;
    font-size: 3rem;
    color: white;
    text-shadow: 3px 3px 0 #ff3366;
    margin-bottom: 1rem;
    letter-spacing: 2px;
}

.buscador {
    max-width: 500px;
    margin: 0 auto 2rem;
}

.buscador input {
    width: 100%;
    padding: 0.8rem 1.5rem;
    font-size: 1.1rem;
    border: 3px solid #9933cc;
    border-radius: 30px;
    background-color: rgba(255, 255, 255, 0.9);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.buscador input:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 5px 20px rgba(0, 102, 204, 0.4);
}

.crear-foro-btn {
    display: block;
    width: 200px;
    margin: 0 auto 2rem;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    color: white;
    background-color: #9933cc;
    padding: 0.7rem 1.5rem;
    border-radius: 10px;
    text-decoration: none;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.7);
    transition: all 0.2s;
    transform: skew(-5deg);
    border: 2px solid black;
    text-align: center;
}

.crear-foro-btn:hover {
    transform: skew(-5deg) translateY(-3px);
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
}

.listado-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 2rem;
}

.contenido-card {
    background-color: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    transition: all 0.3s ease;
    position: relative;
    border: 3px solid black;
    height: 450px;
    display: flex;
    flex-direction: column;
}

.contenido-card:hover {
    transform: translateY(-10px);
    box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.7);
}

.contenido-link {
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.contenido-imagen-container {
    position: relative;
    flex: 1;
    overflow: hidden;
    background-color: #f0f0f0;
}

.contenido-imagen {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.contenido-publicaciones {
    position: absolute;
    top: 10px;
    left: 10px;
    background-color: rgba(0, 0, 0, 0.7);
    border-radius: 20px;
    padding: 5px 10px;
    z-index: 10;
    color: white;
    font-family: "Bangers", cursive;
    font-size: 0.9rem;
}

.contenido-info {
    padding: 1rem;
    background-color: white;
    flex-shrink: 0;
    text-align: center;
}

.contenido-titulo {
    font-family: "Bangers", cursive;
    font-size: 1.3rem;
    color: black;
    text-shadow: 1px 1px 0 #9933cc;
    margin-bottom: 0.5rem;
    letter-spacing: 1px;
}

.contenido-descripcion {
    font-size: 0.8rem;
    color: #444;
    margin-bottom: 0.5rem;
    font-family: Arial, sans-serif;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    height: 3.6em;
}

.contenido-autor {
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 0.3rem;
    font-family: Arial, sans-serif;
}

.contenido-fecha {
    font-size: 0.7rem;
    color: #666;
    font-family: Arial, sans-serif;
    text-transform: uppercase;
}

.contenido-acciones {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
    padding: 0 1rem 1rem;
}

.accion-btn {
    background-color: #9933cc;
    color: white;
    border: 1px solid black;
    border-radius: 5px;
    padding: 0.3rem 0.6rem;
    font-size: 0.7rem;
    cursor: pointer;
    transition: all 0.2s;
    font-family: "Bangers", cursive;
    text-decoration: none;
    box-shadow: 2px 2px 0 rgba(0, 0, 0, 0.5);
}

.accion-btn.eliminar {
    background-color: #cc0000;
}

.accion-btn:hover {
    transform: translateY(-2px);
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.5);
}

.no-contenido {
    text-align: center;
    background-color: rgba(255, 255, 255, 0.9);
    padding: 3rem;
    border-radius: 15px;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    border: 3px solid black;
}

.no-contenido p {
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
    color: black;
}

.comic-button {
    display: inline-block;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    color: white;
    background-color: #9933cc;
    padding: 0.7rem 1.5rem;
    border-radius: 10px;
    text-decoration: none;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.7);
    transition: all 0.2s;
    transform: skew(-5deg);
    border: 2px solid black;
}

.comic-button:hover {
    transform: skew(-5deg) translateY(-3px);
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
}

@media (max-width: 768px) {
    .listado-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1.5rem;
    }
    
    .listado-title {
        font-size: 2.5rem;
    }
}

@media (max-width: 480px) {
    .listado-grid {
        grid-template-columns: 1fr;
        max-width: 300px;
        margin: 0 auto;
    }
}
';

include 'includes/header.php';
?>

<div class="listado-container">
    <div class="listado-header">
        <h1 class="listado-title">FOROS DE DISCUSIÓN</h1>
    </div>
    
    <div class="buscador">
        <input type="text" id="buscar-foro" placeholder="Buscar foro..." aria-label="Buscar foro">
    </div>
    
    <?php if (is_logged_in()): ?>
    <a href="crear-foro.php" class="crear-foro-btn">CREAR NUEVO FORO</a>
    <?php endif; ?>
    
    <?php if ($result && $result->num_rows > 0): ?>
    <div class="listado-grid">
        <?php while($row = $result->fetch_assoc()): ?>
            <?php
            $autor = $row['autor'] ? $row['autor'] : 'Usuario Anónimo';
            $es_propietario = is_logged_in() && $_SESSION['user_id'] == $row['id_usuario'];
            
            // Manejar la imagen del foro
            $imagen = !empty($row['imagen']) && file_exists($row['imagen']) ? $row['imagen'] : 'assets/img/foros/default.jpg';
            ?>
            <div class="contenido-card" data-titulo="<?php echo strtolower($row['titulo']); ?>" data-autor="<?php echo strtolower($autor); ?>">
                <a href="ver-foro.php?id=<?php echo $row['id_foro']; ?>" class="contenido-link">
                    <div class="contenido-imagen-container">
                        <div class="contenido-publicaciones">
                            <?php echo $row['num_publicaciones']; ?> publicaciones
                        </div>
                        <img src="<?php echo htmlspecialchars($imagen); ?>" alt="Imagen de <?php echo htmlspecialchars($row['titulo']); ?>" class="contenido-imagen">
                    </div>
                    <div class="contenido-info">
                        <div class="contenido-titulo"><?php echo strtoupper(htmlspecialchars($row['titulo'])); ?></div>
                        <div class="contenido-descripcion">
                            <?php echo htmlspecialchars($row['descripcion']); ?>
                        </div>
                        <div class="contenido-autor">
                            CREADO POR: <?php echo strtoupper(htmlspecialchars($autor)); ?>
                        </div>
                        <div class="contenido-fecha">
                            <?php echo date('d/m/Y', strtotime($row['fecha_creacion'])); ?>
                        </div>
                    </div>
                </a>
                <?php if ($es_propietario || is_admin()): ?>
                <div class="contenido-acciones">
                    <a href="editar-foro.php?id=<?php echo $row['id_foro']; ?>" class="accion-btn">EDITAR</a>
                    <a href="eliminar-foro.php?id=<?php echo $row['id_foro']; ?>&token=<?php echo generate_csrf_token(); ?>" 
                       class="accion-btn eliminar" 
                       onclick="return confirm('¿Estás seguro de que deseas eliminar este foro?')">ELIMINAR</a>
                </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="no-contenido">
        <p>No hay foros disponibles aún.</p>
        <?php if (is_logged_in()): ?>
        <a href="crear-foro.php" class="comic-button">CREAR PRIMER FORO</a>
        <?php else: ?>
        <p>Inicia sesión para crear el primer foro.</p>
        <a href="ini_sec.php" class="comic-button">INICIAR SESIÓN</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscarInput = document.getElementById('buscar-foro');
    const tarjetas = document.querySelectorAll('.contenido-card');
    
    buscarInput.addEventListener('input', function() {
        const busqueda = this.value.toLowerCase();
        
        tarjetas.forEach(function(tarjeta) {
            const titulo = tarjeta.dataset.titulo;
            const autor = tarjeta.dataset.autor;
            
            if (titulo.includes(busqueda) || autor.includes(busqueda)) {
                tarjeta.style.display = 'flex';
            } else {
                tarjeta.style.display = 'none';
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>