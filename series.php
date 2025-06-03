<?php
// Archivo: series.php
require_once 'includes/init.php';

// Obtener todas las reseñas de series
$sql = "SELECT r.id_resena, r.titulo, r.calificacion, r.imagenes, r.temporadas, r.episodios, 
        r.archivo_php, r.id_usuario, r.descripcion, u.nombre as autor 
        FROM resenas r 
        LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario 
        WHERE r.categoria = 'serie' 
        ORDER BY r.fecha_creacion DESC";
$result = $conexion->query($sql);

$page_title = "Reseñas de Series - Full Moon, Full Life";
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
    text-shadow: 3px 3px 0 #0066cc;
    margin-bottom: 1rem;
    letter-spacing: 2px;
}

.buscador {
    max-width: 500px;
    margin: 0 auto 2rem;
}
    
.crear-serie-btn {
    display: block;
    width: 200px;
    margin: 0 auto 2rem;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    color: white;
    background-color:#0066cc;
    padding: 0.7rem 1.5rem;
    border-radius: 10px;
    text-decoration: none;
    box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.7);
    transition: all 0.2s;
    transform: skew(-5deg);
    border: 2px solid black;
    text-align: center;
}

.crear-serie-btn:hover {
    transform: skew(-5deg) translateY(-3px);
    box-shadow: 5px 5px 0 #0066cc;
}

.buscador input {
    width: 100%;
    padding: 0.8rem 1.5rem;
    font-size: 1.1rem;
    border: 3px solid #0066cc;
    border-radius: 30px;
    background-color: rgba(255, 255, 255, 0.9);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.buscador input:focus {
    outline: none;
    border-color: #ff3366;
    box-shadow: 0 5px 20px rgba(255, 51, 102, 0.4);
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

.contenido-calificacion {
    position: absolute;
    top: 10px;
    left: 10px;
    background-color: rgba(0, 0, 0, 0.7);
    border-radius: 20px;
    padding: 5px 10px;
    z-index: 10;
}

.estrellas {
    color: #ffcc00;
    font-size: 1rem;
    text-shadow: 1px 1px 0 black;
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
    text-shadow: 1px 1px 0 #0066cc;
    margin-bottom: 0.5rem;
    letter-spacing: 1px;
}

.contenido-autor {
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 0.3rem;
    font-family: Arial, sans-serif;
}

.contenido-metadata {
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
    background-color: #0066cc;
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
        <h1 class="listado-title">RESEÑAS DE SERIES</h1>
    </div>
    
    <div class="buscador">
        <input type="text" id="buscar-serie" placeholder="Buscar serie..." aria-label="Buscar serie">
    </div>
        
    <?php if (is_logged_in()): ?>
    <a href="crear-resena.php" class="crear-serie-btn">CREAR NUEVA RESEÑA DE UNA SERIE</a>
    <?php endif; ?>
    
    <?php if ($result && $result->num_rows > 0): ?>
    <div class="listado-grid">
        <?php while($row = $result->fetch_assoc()): ?>
            <?php
            $autor = $row['autor'] ? $row['autor'] : 'Usuario Anónimo';
            $archivo_path = $row['archivo_php'] ? $row['archivo_php'] : '#';
            $es_propietario = is_logged_in() && $_SESSION['user_id'] == $row['id_usuario'];
            
            // Extraer el nombre de la imagen (para asegurar que se carga correctamente)
            $imagen = '';
            if (isset($row['imagenes'])) {
                // Si es un string JSON, decodificarlo
                if (substr($row['imagenes'], 0, 1) === '[') {
                    $img_array = json_decode($row['imagenes'], true);
                    $imagen = isset($img_array[0]) ? $img_array[0] : '';
                } else {
                    $imagen = $row['imagenes'];
                }
            }
            
            // Si no hay imagen o la ruta no existe, usar imagen por defecto
            if (empty($imagen) || !file_exists($imagen)) {
                // Intentar buscar la imagen por el nombre en minúsculas
                $nombre_archivo = strtolower(str_replace(" ", "-", $row['titulo']));
                $posible_imagen = "assets/img/portadas/{$nombre_archivo}.jpg";
                
                if (file_exists($posible_imagen)) {
                    $imagen = $posible_imagen;
                } else {
                    // Comprobar extensiones alternativas
                    $extensiones = ['png', 'jpeg', 'webp'];
                    foreach ($extensiones as $ext) {
                        $posible_imagen = "assets/img/portadas/{$nombre_archivo}.{$ext}";
                        if (file_exists($posible_imagen)) {
                            $imagen = $posible_imagen;
                            break;
                        }
                    }
                    
                    // Si aún no se encuentra, usar imagen por defecto
                    if (empty($imagen) || !file_exists($imagen)) {
                        $imagen = "assets/img/portadas/default.jpg";
                    }
                }
            }
            ?>
            <div class="contenido-card" data-titulo="<?php echo strtolower($row['titulo']); ?>" data-autor="<?php echo strtolower($autor); ?>">
                <a href="<?php echo htmlspecialchars($archivo_path); ?>" class="contenido-link">
                    <div class="contenido-imagen-container">
                        <div class="contenido-calificacion">
                            <div class="estrellas">
                                <?php
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $row['calificacion']) {
                                        echo "★";
                                    } else {
                                        echo "☆";
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <img src="<?php echo htmlspecialchars($imagen); ?>" alt="Portada de <?php echo htmlspecialchars($row['titulo']); ?>" class="contenido-imagen">
                    </div>
                    <div class="contenido-info">
                        <div class="contenido-titulo"><?php echo strtoupper(htmlspecialchars($row['titulo'])); ?></div>
                        <!-- Eliminada la calificación duplicada aquí -->
                        <div class="contenido-autor">
                            POR: <?php echo strtoupper(htmlspecialchars($autor)); ?>
                        </div>
                        <?php if ($row['temporadas'] && $row['episodios']): ?>
                        <div class="contenido-metadata">
                            TEMPORADAS: <?php echo $row['temporadas']; ?> | EPISODIOS: <?php echo $row['episodios']; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </a>
                <?php if ($es_propietario): ?>
                <div class="contenido-acciones">
                    <a href="editar-resena.php?id=<?php echo $row['id_resena']; ?>" class="accion-btn">EDITAR</a>
                    <a href="eliminar-resena.php?id=<?php echo $row['id_resena']; ?>&token=<?php echo generate_csrf_token(); ?>" 
                       class="accion-btn eliminar" 
                       onclick="return confirm('¿Estás seguro de que deseas eliminar esta reseña?')">ELIMINAR</a>
                </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="no-contenido">
        <p>No hay reseñas de series disponibles.</p>
        <a href="crear-resena.php" class="comic-button">CREAR PRIMERA RESEÑA</a>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscarInput = document.getElementById('buscar-serie');
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