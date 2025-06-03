<?php
// Página para listar series, películas o libros
require_once 'includes/functions.php';
require_once 'config/db.php';

// Determinar el tipo de contenido a mostrar
$tipo = isset($_GET['tipo']) ? sanitize($_GET['tipo']) : 'serie';

// Validar el tipo
if (!in_array($tipo, ['serie', 'pelicula', 'libro'])) {
    $tipo = 'serie';
}

// Obtener parámetros de paginación
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$por_pagina = 12;
$offset = ($pagina - 1) * $por_pagina;

// Obtener el contenido según el tipo
$sql = "SELECT * FROM contenidos WHERE tipo = ? ORDER BY calificacion DESC, titulo ASC LIMIT ? OFFSET ?";
$result = query($sql, [$tipo, $por_pagina, $offset]);
$contenidos = fetch_all($result);

// Obtener el total de contenidos para la paginación
$sql = "SELECT COUNT(*) as total FROM contenidos WHERE tipo = ?";
$result = query($sql, [$tipo]);
$total = fetch_assoc($result)['total'];
$total_paginas = ceil($total / $por_pagina);

// Determinar el título de la página según el tipo
$titulos = [
    'serie' => 'Series',
    'pelicula' => 'Películas',
    'libro' => 'Libros'
];

$page_title = $titulos[$tipo] . " - Full Moon, Full Life";
$additional_css = ['listado.css'];
include 'includes/header.php';
?>

<div class="listado-container">
    <div class="listado-header">
        <h1 class="listado-title"><?php echo strtoupper($titulos[$tipo]); ?></h1>
        
        <div class="tipo-selector">
            <a href="?tipo=serie" class="tipo-link <?php echo $tipo === 'serie' ? 'active' : ''; ?>">SERIES</a>
            <a href="?tipo=pelicula" class="tipo-link <?php echo $tipo === 'pelicula' ? 'active' : ''; ?>">PELÍCULAS</a>
            <a href="?tipo=libro" class="tipo-link <?php echo $tipo === 'libro' ? 'active' : ''; ?>">LIBROS</a>
        </div>
    </div>
    
    <?php if (empty($contenidos)): ?>
    <div class="no-contenido">
        <p>No hay <?php echo $titulos[$tipo]; ?> disponibles en este momento.</p>
    </div>
    <?php else: ?>
    <div class="listado-grid">
        <?php foreach ($contenidos as $item): ?>
        <div class="contenido-card">
            <a href="detalle.php?id=<?php echo $item['id']; ?>" class="contenido-link">
                <div class="contenido-imagen-container">
                    <img src="<?php echo htmlspecialchars($item['imagen']); ?>" alt="<?php echo htmlspecialchars($item['titulo']); ?>" class="contenido-imagen">
                    <div class="contenido-calificacion"><?php echo number_format($item['calificacion'], 1); ?></div>
                </div>
                <h3 class="contenido-titulo"><?php echo htmlspecialchars($item['titulo']); ?></h3>
                <div class="contenido-generos"><?php echo htmlspecialchars($item['generos']); ?></div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if ($total_paginas > 1): ?>
    <div class="paginacion">
        <?php if ($pagina > 1): ?>
        <a href="?tipo=<?php echo $tipo; ?>&pagina=<?php echo $pagina - 1; ?>" class="pagina-link">&laquo; Anterior</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
        <a href="?tipo=<?php echo $tipo; ?>&pagina=<?php echo $i; ?>" class="pagina-link <?php echo $i === $pagina ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        
        <?php if ($pagina < $total_paginas): ?>
        <a href="?tipo=<?php echo $tipo; ?>&pagina=<?php echo $pagina + 1; ?>" class="pagina-link">Siguiente &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>