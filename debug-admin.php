<?php
// Archivo temporal para diagnosticar el problema de administrador
require_once __DIR__ . '/includes/init.php';

echo "<h2>Diagnóstico del Sistema de Administración</h2>";

// 1. Verificar si hay sesión activa
echo "<h3>1. Estado de la Sesión:</h3>";
if (isset($_SESSION['usuario_id'])) {
    echo "✓ Sesión activa - Usuario ID: " . $_SESSION['usuario_id'] . "<br>";
    echo "Datos de sesión: <pre>" . print_r($_SESSION, true) . "</pre>";
} else {
    echo "✗ No hay sesión activa<br>";
    echo "<strong>Problema:</strong> Necesitas iniciar sesión primero.<br>";
    echo "<a href='login.php'>Ir a Login</a><br>";
}

// 2. Verificar estructura de la tabla usuarios
echo "<h3>2. Estructura de la tabla usuarios:</h3>";
$describe = "DESCRIBE usuarios";
$result = $conexion->query($describe);
if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "✗ Error al obtener estructura: " . $conexion->error;
}

// 3. Verificar datos del usuario ID 1
echo "<h3>3. Datos del Usuario ID 1:</h3>";
$sql_user = "SELECT * FROM usuarios WHERE id_usuario = 1";
$result_user = $conexion->query($sql_user);
if ($result_user && $result_user->num_rows > 0) {
    $user = $result_user->fetch_assoc();
    echo "<table border='1'>";
    foreach ($user as $key => $value) {
        echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
    }
    echo "</table>";
    
    // Verificar específicamente el campo es_admin
    if (isset($user['es_admin'])) {
        echo "<br><strong>Campo es_admin:</strong> " . $user['es_admin'] . " (" . ($user['es_admin'] ? 'ES ADMIN' : 'NO ES ADMIN') . ")";
    } else {
        echo "<br><strong>⚠️ El campo 'es_admin' no existe en la tabla</strong>";
    }
} else {
    echo "✗ No se encontró el usuario ID 1";
}

// 4. Agregar columna es_admin si no existe
echo "<h3>4. Verificar/Agregar columna es_admin:</h3>";
$check_column = "SHOW COLUMNS FROM usuarios LIKE 'es_admin'";
$result_check = $conexion->query($check_column);

if ($result_check->num_rows == 0) {
    echo "⚠️ La columna es_admin no existe. Agregándola...<br>";
    $add_column = "ALTER TABLE usuarios ADD COLUMN es_admin TINYINT(1) DEFAULT 0";
    if ($conexion->query($add_column)) {
        echo "✓ Columna es_admin agregada exitosamente<br>";
        
        // Actualizar usuario ID 1
        $update_admin = "UPDATE usuarios SET es_admin = 1 WHERE id_usuario = 1";
        if ($conexion->query($update_admin)) {
            echo "✓ Usuario ID 1 actualizado como administrador<br>";
        } else {
            echo "✗ Error al actualizar usuario: " . $conexion->error . "<br>";
        }
    } else {
        echo "✗ Error al agregar columna: " . $conexion->error . "<br>";
    }
} else {
    echo "✓ La columna es_admin ya existe<br>";
    
    // Asegurar que el usuario ID 1 sea admin
    $update_admin = "UPDATE usuarios SET es_admin = 1 WHERE id_usuario = 1";
    if ($conexion->query($update_admin)) {
        echo "✓ Usuario ID 1 confirmado como administrador<br>";
    } else {
        echo "✗ Error al actualizar usuario: " . $conexion->error . "<br>";
    }
}

// 5. Probar función esAdmin si hay sesión
if (isset($_SESSION['usuario_id'])) {
    echo "<h3>5. Prueba de función esAdmin:</h3>";
    
    // Incluir el archivo de autenticación
    if (file_exists(__DIR__ . '/includes/auth_admin.php')) {
        require_once __DIR__ . '/includes/auth_admin.php';
        
        $es_admin = esAdmin($_SESSION['usuario_id']);
        echo "Resultado de esAdmin(" . $_SESSION['usuario_id'] . "): " . ($es_admin ? 'TRUE (ES ADMIN)' : 'FALSE (NO ES ADMIN)') . "<br>";
        
        $mostrar_menu = mostrarMenuAdmin();
        echo "Resultado de mostrarMenuAdmin(): " . ($mostrar_menu ? 'TRUE (MOSTRAR MENÚ)' : 'FALSE (NO MOSTRAR MENÚ)') . "<br>";
    } else {
        echo "✗ No se encontró el archivo includes/auth_admin.php<br>";
    }
}

// 6. Verificar archivos necesarios
echo "<h3>6. Verificación de archivos:</h3>";
$archivos_necesarios = [
    'includes/init.php',
    'includes/auth_admin.php',
    'includes/header.php',
    'includes/footer.php',
    'admin-usuarios.php',
    'admin-resenas.php',
    'admin-foros.php'
];

foreach ($archivos_necesarios as $archivo) {
    if (file_exists(__DIR__ . '/' . $archivo)) {
        echo "✓ $archivo existe<br>";
    } else {
        echo "✗ $archivo NO EXISTE<br>";
    }
}

echo "<br><hr>";
echo "<h3>Acciones recomendadas:</h3>";
echo "<ul>";
echo "<li><a href='index.php'>Ir al Index</a></li>";
echo "<li><a href='admin-usuarios.php'>Probar Admin Usuarios</a></li>";
echo "<li><a href='login.php'>Ir a Login</a></li>";
echo "</ul>";
?>
