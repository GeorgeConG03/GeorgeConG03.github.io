<?php
// Página de listado de reseñas
// Incluir archivo de inicialización
require_once __DIR__ . '/includes/init.php';

// Conexión a la base de datos
$host = "localhost";
$usuario = "root";
$password = "bri_gitte_03";
$database = "here'stoyou";

$conexion = new mysqli($host, $usuario, $password, $database);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Verificar si la tabla existe
$tabla_existe = false;
$result = $conexion->query("SHOW TABLES LIKE 'resenas'");
if ($result->num_rows > 0) {
    $tabla_existe = true;
}

// Obtener reseñas si la tabla existe
$resenas = [];
if ($tabla_existe) {
    $sql = "SELECT * FROM resenas ORDER BY fecha_creacion DESC";
    $result = $conexion->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $resenas[] = $row;
        }
    }
}

// Título de la página
$page_title = "Reseñas - Full Moon, Full Life";

// Estilos adicionales
$page_styles = '
<link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
<style>
/* Estilos específicos para la página de reseñas */
.resenas-container {
    background-color: rgba(51, 51, 51, 0.9);
    border-radius: 10px;
    padding: 2rem;
    margin: 2rem auto;
    max-width: 1200px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
}

.resenas-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.resenas-title {
    font-size: 2.5rem;
    color: white;
    text-shadow: 3px 3px 0 #ff3366;
    font-family: "Bangers", cursive;
}

.btn-crear {
    background-color: white;
    color: black;
    font-family: "Bangers", cursive;
    font-size: 1.2rem;
    padding: 0.8rem 1.5rem;
    border: 2px solid black;
    border-radius: 5px;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    transform: skew(-5deg);
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
    display: inline-block;
    text-shadow: 2px 2px 0 #0066cc;
}

.btn-crear:hover {
    transform: skew(-5deg) scale(1.05);
    box-shadow: 7px 7px 0 rgba(0, 0, 0, 0.7);
}

.no-resenas {
    text-align: center;
    padding: 3rem;
    color: white;
}

.no-resenas p {
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
}

.btn-primera-resena {
    background-color: white;
    color: black;
    font-family: "Bangers", cursive;
    font-size: 1.5rem;
    padding: 1rem 2rem;
    border: 3px solid black;
    border-radius: 5px;
    box-shadow: 7px 7px 0 rgba(0, 0, 0, 0.7);
    transform: skew(-5deg);
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
    display: inline-block;
    text-shadow: 2px 2px 0 #0066cc;
}

.btn-primera-resena:hover {
    transform: skew(-5deg) scale(1.05);
    box-shadow: 9px 9px 0 rgba(0, 0, 0, 0.7);
}

.resenas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
}

.resena-card {
    background-color: white;
    border: 3px solid black;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.7);
    transition: transform 0.2s, box-shadow 0.2s;
}

.resena-card:hover {
    transform: translateY(-5px);
    box-shadow: 7px 7px 0 rgba(0, 0, 0, 0.7);
}

.resena-imagen {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.resena-contenido {
    padding: 1.5rem;
}

.resena-titulo {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: black;
    text-shadow: 1px 1px 0 #0066cc;
    font-family: "Bangers", cursive;
}

.resena-categoria {
    display: inline-block;
    background-color: #ff3366;
    color: white;
    font-size: 0.8rem;
    padding: 0.3rem 0.6rem;
    border-radius: 3px;
    margin-bottom: 0.8rem;
    text-transform: uppercase;
}

.resena-calificacion {
    color: #ffcc00;
    font-size: 1.2rem;
    margin-bottom: 0.8rem;
    text-shadow: 1px 1px 0 #666;
}

.resena-descripcion {
    font-family: Arial, sans-serif;
    font-size: 0.9rem;
    color: #333;
    margin-bottom: 1rem;
    line-height: 1.4;
}

.resena-autor {
    font-size: 0.8rem;
    color: #666;
    text-align: right;
    font-style: italic;
}

.resena-fecha {
    font-size: 0.8rem;
    color: #999;
    text-align: right;
}

.resena-link {
    display: block;
    background-color: #f5f5f5;
    color: black;
    text-align: center;
    padding: 0.8rem;
    text-decoration: none;
    font-family: "Bangers", cursive;
    font-size: 1.1rem;
    border-top: 2px solid black;
    transition: background-color 0.2s;
    text-shadow: 1px 1px 0 #0066cc;
}

.resena-link:hover {
    background-color: #e0e0e0;
}

@media (max-width: 768px) {
    .resenas-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .resenas-grid {
        grid-template-columns: 1fr;
    }
}
</style>
';

// JavaScript adicional
$additional_js = ['menu.js'];

// Incluir el encabezado
include 'includes/header.php';
?>

<!-- Contenido principal -->
<main class="container resenas-container">
    <div class="resenas-header">
        <h1 class="resenas-title">Reseñas</h1>
        <a href="crear-resena.php" class="btn-crear">Crear nueva reseña</a>
    </div>
    
    <?php if (!$tabla_existe): ?>
    <div class="no-resenas">
        <p>La tabla de reseñas no existe en la base de datos. Por favor, crea la tabla primero.</p>
    </div>
    <?php elseif (empty($resenas)): ?>
    <div class="no-resenas">
        <p>Aún no hay reseñas publicadas. ¡Sé el primero en crear una!</p>
        <a href="crear-resena.php" class="btn-primera-resena">Crear mi primera reseña</a>
    </div>
    <?php else: ?>
    <div class="resenas-grid">
        <?php foreach ($resenas as $resena): ?>
            <?php 
            // Obtener la primera imagen si hay imágenes
            $imagenes = json_decode($resena['imagenes'], true);
            $imagen_principal = !empty($imagenes) ? $imagenes[0] : 'placeholder.jpg';
            
            // Formatear la fecha
            $fecha = new DateTime($resena['fecha_creacion']);
            $fecha_formateada = $fecha->format('d/m/Y');
            
            // Generar estrellas para la calificación
            $estrellas = '';
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $resena['calificacion']) {
                    $estrellas .= '★';
                } else {
                    $estrellas .= '☆';
                }
            }
            ?>
            <div class="resena-card">
                <img src="<?php echo $imagen_principal; ?>" alt="Imagen de <?php echo htmlspecialchars($resena['titulo']); ?>" class="resena-imagen">
                <div class="resena-contenido">
                    <h2 class="resena-titulo"><?php echo htmlspecialchars($resena['titulo']); ?></h2>
                    <span class="resena-categoria"><?php echo htmlspecialchars($resena['categoria']); ?></span>
                    <div class="resena-calificacion"><?php echo $estrellas; ?></div>
                    <p class="resena-descripcion"><?php echo substr(htmlspecialchars($resena['descripcion']), 0, 150) . '...'; ?></p>
                    <p class="resena-autor">Por: <?php echo htmlspecialchars($resena['autor']); ?></p>
                    <p class="resena-fecha">Publicado: <?php echo $fecha_formateada; ?></p>
                </div>
                <a href="detalle-resena.php?id=<?php echo $resena['id']; ?>" class="resena-link">Ver reseña completa</a>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
