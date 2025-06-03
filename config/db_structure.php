<?php
// Estructura de la base de datos para series y películas
require_once 'db.php';

// Tabla para series/películas
$sql_contenido = "CREATE TABLE IF NOT EXISTS contenidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    tipo ENUM('serie', 'pelicula', 'libro') NOT NULL,
    imagen VARCHAR(255) NOT NULL,
    sinopsis TEXT NOT NULL,
    fecha_estreno DATE NOT NULL,
    calificacion DECIMAL(3,1) NOT NULL DEFAULT 0,
    temporadas INT DEFAULT 1,
    episodios INT DEFAULT NULL,
    generos VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

// Tabla para plataformas
$sql_plataformas = "CREATE TABLE IF NOT EXISTS plataformas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    logo VARCHAR(255) NOT NULL,
    url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Tabla de relación entre contenidos y plataformas
$sql_contenido_plataforma = "CREATE TABLE IF NOT EXISTS contenido_plataforma (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contenido_id INT NOT NULL,
    plataforma_id INT NOT NULL,
    url_especifica VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (contenido_id) REFERENCES contenidos(id) ON DELETE CASCADE,
    FOREIGN KEY (plataforma_id) REFERENCES plataformas(id) ON DELETE CASCADE,
    UNIQUE KEY (contenido_id, plataforma_id)
)";

// Tabla para comentarios
$sql_comentarios = "CREATE TABLE IF NOT EXISTS comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contenido_id INT NOT NULL,
    usuario_id INT NOT NULL,
    comentario TEXT NOT NULL,
    calificacion INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contenido_id) REFERENCES contenidos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
)";

// Ejecutar las consultas SQL para crear las tablas
try {
    $conexion->query($sql_contenido);
    $conexion->query($sql_plataformas);
    $conexion->query($sql_contenido_plataforma);
    $conexion->query($sql_comentarios);
    echo "Tablas creadas correctamente.";
} catch (Exception $e) {
    echo "Error al crear las tablas: " . $e->getMessage();
}
?>