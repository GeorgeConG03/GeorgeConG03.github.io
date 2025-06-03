<?php
// Archivo: crear-perfiles-automaticos.php
// Script para crear automáticamente perfiles de usuario

// Incluir archivo de inicialización
require_once 'includes/init.php';

// Verificar si el usuario es administrador
if (!is_logged_in() || !is_admin()) {
    show_message("Acceso denegado. Debes ser administrador para ejecutar este script.", "error");
    redirect("index.php");
    exit;
}

// Función para crear el archivo de perfil
function crear_archivo_perfil($id_usuario) {
    $perfil_dir = "perfiles";
    $perfil_file = $perfil_dir . "/perfil-" . $id_usuario . ".php";
    
    // Verificar si ya existe el archivo
    if (file_exists($perfil_file)) {
        return false; // El archivo ya existe
    }
    
    // Asegurarse de que la carpeta existe
    if (!file_exists($perfil_dir)) {
        mkdir($perfil_dir, 0777, true);
    }
    
    // Contenido del archivo de perfil
    $contenido = '<?php
// Perfil de usuario generado automáticamente
// Redirigir al perfil centralizado con el ID del usuario
header("Location: ../perfil.php?id=' . $id_usuario . '");
exit;
';
    
    // Guardar el archivo
    return file_put_contents($perfil_file, $contenido) !== false;
}

// Función para actualizar la tabla de perfiles
function actualizar_tabla_perfiles($conexion, $id_usuario) {
    // Verificar si ya existe un registro para este usuario
    $query = "SELECT id_perfil FROM perfiles WHERE id_usuario = ?";
    $stmt = $conexion->prepare($query);
    
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    if ($result->num_rows > 0) {
        // Ya existe un perfil para este usuario
        return true;
    }
    
    // Crear un nuevo registro en la tabla de perfiles
    $query = "INSERT INTO perfiles (id_usuario, biografia, avatar, fecha_creacion) VALUES (?, '', 'assets/img/avatars/default.png', NOW())";
    $stmt = $conexion->prepare($query);
    
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("i", $id_usuario);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

// Verificar si existe la tabla de perfiles, si no, crearla
$query = "SHOW TABLES LIKE 'perfiles'";
$result = $conexion->query($query);

if ($result->num_rows === 0) {
    // La tabla no existe, crearla
    $query = "CREATE TABLE perfiles (
        id_perfil INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        biografia TEXT,
        avatar VARCHAR(255),
        telefono VARCHAR(20),
        fecha_creacion DATETIME,
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
    )";
    
    if (!$conexion->query($query)) {
        show_message("Error al crear la tabla de perfiles: " . $conexion->error, "error");
        redirect("index.php");
        exit;
    }
}

// Verificar si existe la tabla de seguidores, si no, crearla
$query = "SHOW TABLES LIKE 'seguidores'";
$result = $conexion->query($query);

if ($result->num_rows === 0) {
    // La tabla no existe, crearla
    $query = "CREATE TABLE seguidores (
        id_seguidor INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        id_seguido INT NOT NULL,
        fecha_seguimiento DATETIME,
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
        FOREIGN KEY (id_seguido) REFERENCES usuarios(id_usuario),
        UNIQUE KEY unique_seguidor (id_usuario, id_seguido)
    )";
    
    if (!$conexion->query($query)) {
        show_message("Error al crear la tabla de seguidores: " . $conexion->error, "error");
        redirect("index.php");
        exit;
    }
}

// Obtener todos los usuarios
$query = "SELECT id_usuario FROM usuarios";
$result = $conexion->query($query);

if (!$result) {
    show_message("Error al obtener usuarios: " . $conexion->error, "error");
    redirect("index.php");
    exit;
}

$total_usuarios = $result->num_rows;
$perfiles_creados = 0;
$perfiles_actualizados = 0;
$errores = 0;

// Procesar cada usuario
while ($row = $result->fetch_assoc()) {
    $id_usuario = $row['id_usuario'];
    
    // Crear archivo de perfil
    $archivo_creado = crear_archivo_perfil($id_usuario);
    
    // Actualizar tabla de perfiles
    $perfil_actualizado = actualizar_tabla_perfiles($conexion, $id_usuario);
    
    if ($archivo_creado) {
        $perfiles_creados++;
    }
    
    if ($perfil_actualizado) {
        $perfiles_actualizados++;
    } else {
        $errores++;
    }
}

// Mostrar resultados
$mensaje = "Proceso completado. Total de usuarios: $total_usuarios. ";
$mensaje .= "Perfiles creados: $perfiles_creados. ";
$mensaje .= "Perfiles actualizados en la base de datos: $perfiles_actualizados. ";
$mensaje .= "Errores: $errores.";

show_message($mensaje, $errores > 0 ? "warning" : "success");
redirect("index.php");
?>