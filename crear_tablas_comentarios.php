<?php
// Archivo: crear_tablas_comentarios.php
// Script para crear las tablas necesarias para el sistema de comentarios
require_once 'includes/init.php';

echo "<h1>Creación de Tablas para el Sistema de Comentarios</h1>";

// Función para ejecutar consultas SQL y mostrar resultados
function ejecutarSQL($conexion, $sql, $descripcion) {
    echo "<h3>$descripcion</h3>";
    echo "<pre>$sql</pre>";
    
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

// 1. Crear tabla de comentarios
$sql_comentarios = "
CREATE TABLE IF NOT EXISTS comentarios (
    id_comentario INT AUTO_INCREMENT PRIMARY KEY,
    id_resena INT NOT NULL,
    id_usuario INT NOT NULL,
    id_comentario_padre INT NULL,
    texto TEXT NOT NULL,
    calificacion INT DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estado ENUM('activo', 'eliminado', 'baneado') DEFAULT 'activo'
)";

ejecutarSQL($conexion, $sql_comentarios, "1. Crear tabla de comentarios");

// 2. Crear tabla de reacciones
$sql_reacciones = "
CREATE TABLE IF NOT EXISTS comentario_reacciones (
    id_reaccion INT AUTO_INCREMENT PRIMARY KEY,
    id_comentario INT NOT NULL,
    id_usuario INT NOT NULL,
    tipo ENUM('like', 'dislike') NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_comment_reaction (id_comentario, id_usuario)
)";

ejecutarSQL($conexion, $sql_reacciones, "2. Crear tabla de reacciones");

// 3. Crear tabla de usuarios baneados
$sql_baneados = "
CREATE TABLE IF NOT EXISTS usuarios_baneados (
    id_ban INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_admin INT NOT NULL,
    razon TEXT,
    fecha_ban TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NULL,
    activo BOOLEAN DEFAULT TRUE
)";

ejecutarSQL($conexion, $sql_baneados, "3. Crear tabla de usuarios baneados");

// 4. Agregar claves foráneas
$sql_foreign_keys = [
    "ALTER TABLE comentarios ADD CONSTRAINT fk_comentarios_resena FOREIGN KEY (id_resena) REFERENCES resenas(id_resena) ON DELETE CASCADE",
    "ALTER TABLE comentarios ADD CONSTRAINT fk_comentarios_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE",
    "ALTER TABLE comentarios ADD CONSTRAINT fk_comentarios_padre FOREIGN KEY (id_comentario_padre) REFERENCES comentarios(id_comentario) ON DELETE CASCADE",
    "ALTER TABLE comentario_reacciones ADD CONSTRAINT fk_reacciones_comentario FOREIGN KEY (id_comentario) REFERENCES comentarios(id_comentario) ON DELETE CASCADE",
    "ALTER TABLE comentario_reacciones ADD CONSTRAINT fk_reacciones_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE",
    "ALTER TABLE usuarios_baneados ADD CONSTRAINT fk_baneados_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE",
    "ALTER TABLE usuarios_baneados ADD CONSTRAINT fk_baneados_admin FOREIGN KEY (id_admin) REFERENCES usuarios(id_usuario) ON DELETE CASCADE"
];

echo "<h3>4. Agregar claves foráneas</h3>";
echo "<p>Nota: Es normal que algunas de estas consultas fallen si las claves foráneas ya existen.</p>";

foreach ($sql_foreign_keys as $index => $sql) {
    try {
        $result = $conexion->query($sql);
        if ($result) {
            echo "<p style='color: green;'>✅ Clave foránea #" . ($index + 1) . " agregada correctamente</p>";
        } else {
            // Verificar si el error es porque la clave ya existe
            if (strpos($conexion->error, 'Duplicate') !== false) {
                echo "<p style='color: orange;'>⚠️ Clave foránea #" . ($index + 1) . " ya existe</p>";
            } else {
                echo "<p style='color: red;'>❌ Error en clave foránea #" . ($index + 1) . ": " . $conexion->error . "</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Excepción en clave foránea #" . ($index + 1) . ": " . $e->getMessage() . "</p>";
    }
}

// 5. Crear índices para mejor rendimiento
$sql_indices = [
    "CREATE INDEX IF NOT EXISTS idx_comentarios_resena ON comentarios(id_resena)",
    "CREATE INDEX IF NOT EXISTS idx_comentarios_usuario ON comentarios(id_usuario)",
    "CREATE INDEX IF NOT EXISTS idx_comentarios_padre ON comentarios(id_comentario_padre)",
    "CREATE INDEX IF NOT EXISTS idx_reacciones_comentario ON comentario_reacciones(id_comentario)",
    "CREATE INDEX IF NOT EXISTS idx_reacciones_usuario ON comentario_reacciones(id_usuario)"
];

echo "<h3>5. Crear índices</h3>";

foreach ($sql_indices as $index => $sql) {
    try {
        $result = $conexion->query($sql);
        if ($result) {
            echo "<p style='color: green;'>✅ Índice #" . ($index + 1) . " creado correctamente</p>";
        } else {
            echo "<p style='color: red;'>❌ Error en índice #" . ($index + 1) . ": " . $conexion->error . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Excepción en índice #" . ($index + 1) . ": " . $e->getMessage() . "</p>";
    }
}

// 6. Verificar que las tablas se crearon correctamente
echo "<h3>6. Verificación final</h3>";

$tablas = ['comentarios', 'comentario_reacciones', 'usuarios_baneados'];
$todas_creadas = true;

foreach ($tablas as $tabla) {
    $sql = "SHOW TABLES LIKE '$tabla'";
    $result = $conexion->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Tabla <strong>$tabla</strong> creada correctamente</p>";
    } else {
        echo "<p style='color: red;'>❌ Tabla <strong>$tabla</strong> NO se creó correctamente</p>";
        $todas_creadas = false;
    }
}

if ($todas_creadas) {
    echo "<h2 style='color: green;'>¡Todas las tablas se crearon correctamente!</h2>";
} else {
    echo "<h2 style='color: red;'>Hubo problemas al crear algunas tablas. Revisa los errores anteriores.</h2>";
}

echo "<p><a href='debug_comentarios.php' style='display: inline-block; padding: 10px 20px; background-color: #0066cc; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Ejecutar diagnóstico</a>";
echo "<a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Volver al inicio</a></p>";
?>
