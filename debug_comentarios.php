<?php
// Archivo: debug_comentarios.php
// Script para diagnosticar problemas con el sistema de comentarios
require_once 'includes/init.php';

echo "<h1>Diagnóstico del Sistema de Comentarios</h1>";

// 1. Verificar si las tablas existen
echo "<h2>1. Verificación de tablas</h2>";

$tablas = [
    'comentarios',
    'comentario_reacciones',
    'usuarios_baneados'
];

foreach ($tablas as $tabla) {
    $sql = "SHOW TABLES LIKE '$tabla'";
    $result = $conexion->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "✅ Tabla <strong>$tabla</strong> existe<br>";
    } else {
        echo "❌ Tabla <strong>$tabla</strong> NO existe<br>";
        
        // Mostrar SQL para crear la tabla
        echo "<div style='background: #f8f9fa; padding: 10px; margin: 10px 0; border-left: 4px solid #0066cc;'>";
        switch ($tabla) {
            case 'comentarios':
                echo "<pre>
CREATE TABLE comentarios (
    id_comentario INT AUTO_INCREMENT PRIMARY KEY,
    id_resena INT NOT NULL,
    id_usuario INT NOT NULL,
    id_comentario_padre INT NULL,
    texto TEXT NOT NULL,
    calificacion INT DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estado ENUM('activo', 'eliminado', 'baneado') DEFAULT 'activo',
    FOREIGN KEY (id_resena) REFERENCES resenas(id_resena) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_comentario_padre) REFERENCES comentarios(id_comentario) ON DELETE CASCADE
);
                </pre>";
                break;
                
            case 'comentario_reacciones':
                echo "<pre>
CREATE TABLE comentario_reacciones (
    id_reaccion INT AUTO_INCREMENT PRIMARY KEY,
    id_comentario INT NOT NULL,
    id_usuario INT NOT NULL,
    tipo ENUM('like', 'dislike') NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_comment_reaction (id_comentario, id_usuario),
    FOREIGN KEY (id_comentario) REFERENCES comentarios(id_comentario) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);
                </pre>";
                break;
                
            case 'usuarios_baneados':
                echo "<pre>
CREATE TABLE usuarios_baneados (
    id_ban INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_admin INT NOT NULL,
    razon TEXT,
    fecha_ban TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NULL,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_admin) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);
                </pre>";
                break;
        }
        echo "</div>";
    }
}

// 2. Verificar estructura de las tablas existentes
echo "<h2>2. Estructura de tablas existentes</h2>";

foreach ($tablas as $tabla) {
    $sql = "SHOW TABLES LIKE '$tabla'";
    $result = $conexion->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $sql_describe = "DESCRIBE $tabla";
        $result_describe = $conexion->query($sql_describe);
        
        if ($result_describe) {
            echo "<h3>Estructura de la tabla <strong>$tabla</strong>:</h3>";
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Predeterminado</th><th>Extra</th></tr>";
            
            while ($row = $result_describe->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['Field']}</td>";
                echo "<td>{$row['Type']}</td>";
                echo "<td>{$row['Null']}</td>";
                echo "<td>{$row['Key']}</td>";
                echo "<td>{$row['Default']}</td>";
                echo "<td>{$row['Extra']}</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "❌ Error al obtener estructura de <strong>$tabla</strong>: " . $conexion->error . "<br>";
        }
    }
}

// 3. Verificar si las funciones están disponibles
echo "<h2>3. Verificación de funciones</h2>";

$funciones = [
    'obtenerComentarios',
    'obtenerReaccionUsuario',
    'puedeModerar',
    'renderizarComentario'
];

foreach ($funciones as $funcion) {
    if (function_exists($funcion)) {
        echo "✅ Función <strong>$funcion</strong> está disponible<br>";
    } else {
        echo "❌ Función <strong>$funcion</strong> NO está disponible<br>";
    }
}

// 4. Verificar archivos necesarios
echo "<h2>4. Verificación de archivos</h2>";

$archivos = [
    'procesar-comentario.php',
    'procesar-reaccion.php',
    'moderar-comentario.php',
    'includes/comentarios_functions.php',
    'assets/js/comentarios.js',
    'assets/css/comentarios.css'
];

foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        echo "✅ Archivo <strong>$archivo</strong> existe<br>";
    } else {
        echo "❌ Archivo <strong>$archivo</strong> NO existe<br>";
    }
}

// 5. Probar una consulta simple
echo "<h2>5. Prueba de consulta</h2>";

try {
    $sql_test = "SELECT 1 as test";
    $result_test = $conexion->query($sql_test);
    
    if ($result_test) {
        echo "✅ Consulta de prueba exitosa<br>";
    } else {
        echo "❌ Error en consulta de prueba: " . $conexion->error . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Excepción en consulta de prueba: " . $e->getMessage() . "<br>";
}

// 6. Verificar si hay reseñas en la base de datos
echo "<h2>6. Verificación de reseñas</h2>";

$sql_resenas = "SELECT COUNT(*) as total FROM resenas";
$result_resenas = $conexion->query($sql_resenas);

if ($result_resenas) {
    $row = $result_resenas->fetch_assoc();
    echo "Total de reseñas: <strong>{$row['total']}</strong><br>";
    
    if ($row['total'] > 0) {
        // Mostrar algunas reseñas
        $sql_sample = "SELECT id_resena, titulo FROM resenas LIMIT 5";
        $result_sample = $conexion->query($sql_sample);
        
        if ($result_sample && $result_sample->num_rows > 0) {
            echo "<h3>Ejemplos de reseñas:</h3>";
            echo "<ul>";
            while ($resena = $result_sample->fetch_assoc()) {
                echo "<li>ID: {$resena['id_resena']} - {$resena['titulo']}</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "❌ No hay reseñas en la base de datos<br>";
    }
} else {
    echo "❌ Error al verificar reseñas: " . $conexion->error . "<br>";
}

// 7. Instrucciones para solucionar problemas
echo "<h2>7. Solución de problemas</h2>";

echo "<ol>";
echo "<li>Asegúrate de ejecutar el script SQL para crear las tablas necesarias.</li>";
echo "<li>Verifica que los archivos PHP estén en las ubicaciones correctas.</li>";
echo "<li>Incluye el archivo de funciones en tu página de reseña: <code>include_once 'includes/comentarios_functions.php';</code></li>";
echo "<li>Agrega los archivos CSS y JS a tu header: 
<pre>
&lt;link rel=\"stylesheet\" href=\"assets/css/comentarios.css\"&gt;
&lt;script src=\"assets/js/comentarios.js\" defer&gt;&lt;/script&gt;
</pre>
</li>";
echo "<li>Si sigues teniendo problemas, verifica los permisos de la base de datos y los errores en el log de PHP.</li>";
echo "</ol>";

echo "<p><a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #0066cc; color: white; text-decoration: none; border-radius: 5px;'>Volver al inicio</a></p>";
?>
