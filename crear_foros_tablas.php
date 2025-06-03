<?php
// Archivo: crear_foros_tablas.php
// Script para crear las tablas del sistema de foros
require_once 'includes/init.php';

echo "<h1>Creación de Tablas para el Sistema de Foros</h1>";

// Función para ejecutar consultas SQL
function ejecutarSQL($conexion, $sql, $descripcion) {
    echo "<h3>$descripcion</h3>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>$sql</pre>";
    
    try {
        $result = $conexion->query($sql);
        if ($result) {
            echo "<p style='color: green;'>✅ Consulta ejecutada correctamente</p>";
            return true;
        } else {
            echo "<p style='color: red;'>❌ Error: " . $conexion->error . "</p>";
            return false;
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Excepción: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Crear directorio de foros si no existe
$foros_dir = "foros";
if (!file_exists($foros_dir)) {
    mkdir($foros_dir, 0755, true);
    echo "<p style='color: green;'>✅ Directorio 'foros' creado</p>";
}

$img_comentario_dir = "foros/img-comentario";
if (!file_exists($img_comentario_dir)) {
    mkdir($img_comentario_dir, 0755, true);
    echo "<p style='color: green;'>✅ Directorio 'foros/img-comentario' creado</p>";
}

$portadas_dir = "foros/portadas";
if (!file_exists($portadas_dir)) {
    mkdir($portadas_dir, 0755, true);
    echo "<p style='color: green;'>✅ Directorio 'foros/portadas' creado</p>";
}

// 1. Actualizar tabla foros existente
$sql_update_foros = "
ALTER TABLE foros 
ADD COLUMN IF NOT EXISTS descripcion TEXT AFTER titulo,
ADD COLUMN IF NOT EXISTS imagen_portada VARCHAR(255) DEFAULT NULL AFTER descripcion,
ADD COLUMN IF NOT EXISTS id_creador INT AFTER imagen_portada,
ADD COLUMN IF NOT EXISTS fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER id_creador,
ADD COLUMN IF NOT EXISTS activo BOOLEAN DEFAULT TRUE AFTER fecha_creacion,
ADD COLUMN IF NOT EXISTS total_miembros INT DEFAULT 0 AFTER activo,
ADD COLUMN IF NOT EXISTS total_posts INT DEFAULT 0 AFTER total_miembros,
ADD COLUMN IF NOT EXISTS archivo_php VARCHAR(255) DEFAULT NULL AFTER total_posts
";

ejecutarSQL($conexion, $sql_update_foros, "1. Actualizar tabla foros");

// 2. Crear tabla de miembros de foros
$sql_foro_miembros = "
CREATE TABLE IF NOT EXISTS foro_miembros (
    id_miembro INT AUTO_INCREMENT PRIMARY KEY,
    id_foro INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha_union TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rol ENUM('miembro', 'moderador', 'admin') DEFAULT 'miembro',
    UNIQUE KEY unique_foro_usuario (id_foro, id_usuario),
    FOREIGN KEY (id_foro) REFERENCES foros(id_foro) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
)";

ejecutarSQL($conexion, $sql_foro_miembros, "2. Crear tabla foro_miembros");

// 3. Crear tabla de posts en foros
$sql_foro_posts = "
CREATE TABLE IF NOT EXISTS foro_posts (
    id_post INT AUTO_INCREMENT PRIMARY KEY,
    id_foro INT NOT NULL,
    id_usuario INT NOT NULL,
    id_post_padre INT NULL,
    titulo VARCHAR(255) DEFAULT NULL,
    contenido TEXT NOT NULL,
    imagen VARCHAR(255) DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estado ENUM('activo', 'eliminado', 'oculto') DEFAULT 'activo',
    total_likes INT DEFAULT 0,
    total_respuestas INT DEFAULT 0,
    es_hilo_principal BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_foro) REFERENCES foros(id_foro) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_post_padre) REFERENCES foro_posts(id_post) ON DELETE CASCADE
)";

ejecutarSQL($conexion, $sql_foro_posts, "3. Crear tabla foro_posts");

// 4. Crear tabla de likes en posts
$sql_foro_post_likes = "
CREATE TABLE IF NOT EXISTS foro_post_likes (
    id_like INT AUTO_INCREMENT PRIMARY KEY,
    id_post INT NOT NULL,
    id_usuario INT NOT NULL,
    tipo ENUM('like', 'dislike') NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_post_like (id_post, id_usuario),
    FOREIGN KEY (id_post) REFERENCES foro_posts(id_post) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
)";

ejecutarSQL($conexion, $sql_foro_post_likes, "4. Crear tabla foro_post_likes");

// 5. Crear índices
$indices = [
    "CREATE INDEX IF NOT EXISTS idx_foros_creador ON foros(id_creador)",
    "CREATE INDEX IF NOT EXISTS idx_foro_miembros_foro ON foro_miembros(id_foro)",
    "CREATE INDEX IF NOT EXISTS idx_foro_miembros_usuario ON foro_miembros(id_usuario)",
    "CREATE INDEX IF NOT EXISTS idx_foro_posts_foro ON foro_posts(id_foro)",
    "CREATE INDEX IF NOT EXISTS idx_foro_posts_usuario ON foro_posts(id_usuario)",
    "CREATE INDEX IF NOT EXISTS idx_foro_posts_padre ON foro_posts(id_post_padre)",
    "CREATE INDEX IF NOT EXISTS idx_foro_post_likes_post ON foro_post_likes(id_post)",
    "CREATE INDEX IF NOT EXISTS idx_foro_post_likes_usuario ON foro_post_likes(id_usuario)"
];

echo "<h3>5. Crear índices</h3>";
foreach ($indices as $index => $sql) {
    try {
        $result = $conexion->query($sql);
        if ($result) {
            echo "<p style='color: green;'>✅ Índice #" . ($index + 1) . " creado</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Índice #" . ($index + 1) . ": " . $conexion->error . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en índice #" . ($index + 1) . ": " . $e->getMessage() . "</p>";
    }
}

// 6. Verificación final
echo "<h3>6. Verificación final</h3>";
$tablas = ['foros', 'foro_miembros', 'foro_posts', 'foro_post_likes'];
$todas_ok = true;

foreach ($tablas as $tabla) {
    $sql = "SHOW TABLES LIKE '$tabla'";
    $result = $conexion->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Tabla <strong>$tabla</strong> existe</p>";
    } else {
        echo "<p style='color: red;'>❌ Tabla <strong>$tabla</strong> NO existe</p>";
        $todas_ok = false;
    }
}

if ($todas_ok) {
    echo "<h2 style='color: green;'>🎉 ¡Sistema de foros configurado correctamente!</h2>";
} else {
    echo "<h2 style='color: red;'>❌ Hubo problemas. Revisa los errores anteriores.</h2>";
}

echo "<p><a href='crear-foro.php' style='display: inline-block; padding: 10px 20px; background-color: #0066cc; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Crear Primer Foro</a>";
echo "<a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Volver al Inicio</a></p>";
?>
