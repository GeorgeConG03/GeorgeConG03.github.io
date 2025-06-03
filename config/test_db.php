<?php
// Archivo para probar la conexión a la base de datos
require_once 'config/db.php';

try {
    // Intentar ejecutar una consulta simple
    $result = query("SELECT 1");
    
    if ($result) {
        echo "<h2>¡Conexión exitosa a la base de datos!</h2>";
        echo "<p>La conexión a la base de datos 'fullmoon' se ha establecido correctamente.</p>";
        
        // Verificar si existen las tablas principales
        $tables = ['contenidos', 'plataformas', 'contenido_plataforma', 'comentarios', 'usuarios'];
        echo "<h3>Verificando tablas:</h3>";
        echo "<ul>";
        
        foreach ($tables as $table) {
            $result = query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows > 0) {
                echo "<li>Tabla '$table': <span style='color:green'>Existe</span></li>";
            } else {
                echo "<li>Tabla '$table': <span style='color:red'>No existe</span></li>";
            }
        }
        
        echo "</ul>";
        
        echo "<p>Si alguna tabla no existe, ejecuta el archivo <a href='config/db_structure.php'>db_structure.php</a> para crearlas.</p>";
        echo "<p>Para insertar datos de ejemplo, ejecuta <a href='config/sample_data.php'>sample_data.php</a>.</p>";
        echo "<p><a href='index.php'>Volver a la página principal</a></p>";
    }
} catch (Exception $e) {
    echo "<h2>Error de conexión</h2>";
    echo "<p>No se pudo conectar a la base de datos: " . $e->getMessage() . "</p>";
    echo "<p>Posibles soluciones:</p>";
    echo "<ul>";
    echo "<li>Verifica que el servidor MySQL esté en ejecución</li>";
    echo "<li>Comprueba que las credenciales en config.php sean correctas</li>";
    echo "<li>Asegúrate de que la base de datos 'fullmoon' exista. Puedes crearla ejecutando <a href='config/create_database.php'>create_database.php</a></li>";
    echo "</ul>";
}
?>