<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivo de funciones
require_once __DIR__ . '/functions.php';
// Configuración BASE_URL (¡Añade esto al inicio del header.php!)
$base_url = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . '/FmFl/';
define('BASE_URL', $base_url); // Opcional: para usar constante
// Configuración de la base de datos
$host = "localhost";
$usuario = "root";
$password = "bri_gitte_03";  // Contraseña actualizada
$basedatos = "here'stoyou";  // Nombre de la base de datos actualizado

// Conexión a la base de datos
$conexion = new mysqli($host, $usuario, $password, $basedatos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión a la base de datos: " . $conexion->connect_error);
}

// Establecer charset
$conexion->set_charset("utf8");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Full Moon, Full Life'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    
    <?php if (isset($include_base_css) && $include_base_css): ?>
        <link rel="stylesheet" href="/FmFl/assets/css/styles.css">
    <?php endif; ?>
    
    <?php if (isset($additional_css) && is_array($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="/FmFl/assets/css/<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($page_styles)): ?>
        <style>
            <?php echo $page_styles; ?>
        </style>
    <?php endif; ?>
    
    <!-- Estilos adicionales para el menú responsivo -->
    <style>
        /* Estilos para el menú de navegación */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background-color: rgba(0, 0, 0, 0.7);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 1000;
        }
        
        /* Estilos para los enlaces de navegación */
        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            font-size: 1.3rem;
            text-shadow: 2px 2px 0 #0066cc;
            transition: transform 0.2s;
            padding: 0.5rem 1rem;
            position: relative;
        }
        
        .nav-link:hover {
            transform: scale(1.1);
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            background-color: #ff3366;
            transition: width 0.3s ease-in-out;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        /* Estilos para el avatar y menú de usuario */
        .user-menu {
            position: relative;
            margin-left: 1rem;
        }
        
        .avatar-container {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid #ff3366;
            transition: transform 0.2s;
        }
        
        .avatar-container:hover {
            transform: scale(1.1);
        }
        
        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: rgba(0, 0, 0, 0.8);
            border-radius: 5px;
            padding: 0.5rem 0;
            min-width: 150px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 1001;
            margin-top: 0.5rem;
        }
        
        .user-dropdown.active {
            display: block;
        }
        
        .user-dropdown-link {
            display: block;
            color: white;
            text-decoration: none;
            padding: 0.7rem 1.5rem;
            font-size: 1.1rem;
            position: relative;
            overflow: hidden;
        }
        
        .user-dropdown-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 51, 102, 0.2);
            transition: left 0.3s ease-in-out;
            z-index: -1;
        }
        
        .user-dropdown-link:hover::before {
            left: 0;
        }
        
        /* Ocultar el botón hamburguesa por defecto */
        .hamburger-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.8rem;
            cursor: pointer;
            z-index: 1001;
            padding: 0.5rem;
        }
        
        .hamburger-icon {
            display: block;
            width: 30px;
            height: 20px;
            position: relative;
        }
        
        .hamburger-icon span {
            display: block;
            position: absolute;
            height: 3px;
            width: 100%;
            background: white;
            border-radius: 3px;
            opacity: 1;
            left: 0;
            transform: rotate(0deg);
            transition: .25s ease-in-out;
        }
        
        .hamburger-icon span:nth-child(1) {
            top: 0px;
        }
        
        .hamburger-icon span:nth-child(2) {
            top: 8px;
        }
        
        .hamburger-icon span:nth-child(3) {
            top: 16px;
        }
        
        .hamburger-btn.active .hamburger-icon span:nth-child(1) {
            top: 8px;
            transform: rotate(135deg);
        }
        
        .hamburger-btn.active .hamburger-icon span:nth-child(2) {
            opacity: 0;
            left: -60px;
        }
        
        .hamburger-btn.active .hamburger-icon span:nth-child(3) {
            top: 8px;
            transform: rotate(-135deg);
        }
        
        /* Menú móvil */
        .mobile-menu {
            position: fixed;
            top: 0;
            right: -100%;
            width: 80%;
            max-width: 300px;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            transition: right 0.3s ease-in-out;
            padding: 5rem 1rem 2rem;
            overflow-y: auto;
        }
        
        .mobile-menu.active {
            right: 0;
        }
        
        .mobile-nav-link {
            display: block;
            color: white;
            text-decoration: none;
            padding: 1rem 1.5rem;
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            border-radius: 5px;
            position: relative;
            overflow: hidden;
        }
        
        .mobile-nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 51, 102, 0.2);
            transition: left 0.3s ease-in-out;
            z-index: -1;
        }
        
        .mobile-nav-link:hover::before {
            left: 0;
        }
        
        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }
        
        .mobile-menu-overlay.active {
            display: block;
        }
        
        /* Estilos responsivos */
        @media (max-width: 768px) {
            /* Mostrar el botón hamburguesa en pantallas pequeñas */
            .hamburger-btn {
                display: block;
            }
            
            /* Ocultar los enlaces de navegación en pantallas pequeñas */
            .nav-links .nav-link {
                display: none;
            }
            
            /* Mantener visible el avatar en pantallas pequeñas */
            .user-menu {
                display: flex;
                align-items: center;
            }
            
            /* Ajustar el menú de usuario en móvil */
            .user-dropdown {
                right: -50px;
            }
        }
    </style>
</head>
<body>
    <div class="background-container">
        <div class="background-overlay" style="background-color: rgba(0, 102, 204, 0.5);"></div>
        <img src="/FmFl/assets/rec/comicfondo4.jpg" alt="Fondo cómic" class="background-image">
    </div>

    <div class="skyline"></div>

    <?php include __DIR__ . '/navbar.php'; ?>

    <!-- Contenedor principal -->
    <div class="container">
        <?php
        // Mostrar mensajes si existen
        $message = get_message();
        if ($message): 
        ?>
        <div class="message <?php echo $message['type']; ?>">
            <?php echo $message['message']; ?>
        </div>
        <?php endif; ?>
