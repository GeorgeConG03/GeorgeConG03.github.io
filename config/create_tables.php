<?php
// Incluir archivo de inicialización
require_once __DIR__ . '/db.php';

// Crear tabla contenidos si no existe
$sql_contenidos = "CREATE TABLE IF NOT EXISTS `contenidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text,
  `tipo` enum('serie','pelicula','libro') NOT NULL,
  `genero` varchar(100),
  `fecha_estreno` date,
  `calificacion` decimal(3,1) DEFAULT '0.0',
  `imagen` varchar(255),
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    db_query($sql_contenidos);
    echo "Tabla 'contenidos' creada o ya existente.<br>";
    
    // Insertar algunos datos de ejemplo si la tabla está vacía
    $result = db_query("SELECT COUNT(*) as count FROM contenidos");
    $row = db_fetch_assoc($result);
    
    if ($row['count'] == 0) {
        // Insertar series de ejemplo
        db_query("INSERT INTO contenidos (titulo, descripcion, tipo, genero, fecha_estreno, calificacion) VALUES 
            ('Stranger Things', 'Serie sobre niños que descubren fenómenos sobrenaturales', 'serie', 'Ciencia ficción', '2016-07-15', 4.8),
            ('Breaking Bad', 'Un profesor de química se convierte en fabricante de drogas', 'serie', 'Drama', '2008-01-20', 4.9),
            ('The Witcher', 'Basada en la saga de libros de fantasía', 'serie', 'Fantasía', '2019-12-20', 4.5)");
            
        // Insertar películas de ejemplo
        db_query("INSERT INTO contenidos (titulo, descripcion, tipo, genero, fecha_estreno, calificacion) VALUES 
            ('Interestelar', 'Viaje espacial a través de un agujero de gusano', 'pelicula', 'Ciencia ficción', '2014-11-07', 4.8),
            ('El Padrino', 'La historia de la familia mafiosa Corleone', 'pelicula', 'Drama', '1972-03-24', 4.9),
            ('El Señor de los Anillos', 'Adaptación de la novela de J.R.R. Tolkien', 'pelicula', 'Fantasía', '2001-12-19', 4.7)");
            
        // Insertar libros de ejemplo
        db_query("INSERT INTO contenidos (titulo, descripcion, tipo, genero, fecha_estreno, calificacion) VALUES 
            ('Cien años de soledad', 'Obra maestra de Gabriel García Márquez', 'libro', 'Realismo mágico', '1967-05-30', 4.9),
            ('1984', 'Novela distópica de George Orwell', 'libro', 'Ciencia ficción', '1949-06-08', 4.7),
            ('Harry Potter', 'Serie de novelas de fantasía de J.K. Rowling', 'libro', 'Fantasía', '1997-06-26', 4.8)");
            
        echo "Datos de ejemplo insertados en la tabla 'contenidos'.<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "Script de creación de tablas completado.";
?>