<?php
// Funciones de utilidad para el sitio (versión corregida sin duplicados)

// Verificar si el usuario está logueado
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
}

// Verificar si el usuario es administrador
if (!function_exists('is_admin')) {
    function is_admin() {
        return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}

// Mostrar mensaje
if (!function_exists('show_message')) {
    function show_message($message, $type = 'success') {
        if ($type === 'error') {
            $_SESSION['error_message'] = $message;
        } else {
            $_SESSION['success_message'] = $message;
        }
    }
}

// Obtener mensaje
if (!function_exists('get_message')) {
    function get_message() {
        $message = null;
        
        if (isset($_SESSION['error_message'])) {
            $message = [
                'message' => $_SESSION['error_message'],
                'type' => 'error'
            ];
            unset($_SESSION['error_message']);
        } elseif (isset($_SESSION['success_message'])) {
            $message = [
                'message' => $_SESSION['success_message'],
                'type' => 'success'
            ];
            unset($_SESSION['success_message']);
        }
        
        return $message;
    }
}

// Redireccionar
if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit();
    }
}

// Sanitizar entrada
if (!function_exists('sanitize')) {
    function sanitize($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}

// Generar token CSRF
if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

// Verificar token CSRF
if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token($token) {
        return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
    }
}

// Función para implementar "Recordarme" con cookies
if (!function_exists('remember_me')) {
    function remember_me($user_id, $remember_token) {
        $cookie_name = 'fullmoon_remember';
        $cookie_time = time() + SESSION_LIFETIME;
        
        // Crear un token seguro
        $token_value = $user_id . ':' . $remember_token;
        
        // Establecer la cookie
        setcookie(
            $cookie_name,
            $token_value,
            $cookie_time,
            '/',
            '',
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            true
        );
    }
}

// Función para verificar la cookie "Recordarme"
if (!function_exists('check_remember_cookie')) {
    function check_remember_cookie() {
        global $conexion;
        
        if (isset($_COOKIE['fullmoon_remember']) && !is_logged_in()) {
            $cookie_value = $_COOKIE['fullmoon_remember'];
            $parts = explode(':', $cookie_value);
            
            if (count($parts) === 2) {
                list($user_id, $token) = $parts;
                
                // Validar el token en la base de datos
                $query = "SELECT * FROM usuarios WHERE id_usuario = ? AND remember_token = ?";
                $stmt = $conexion->prepare($query);
                
                if ($stmt) {
                    $stmt->bind_param("is", $user_id, $token);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows === 1) {
                        $user = $result->fetch_assoc();
                        
                        // Iniciar sesión para el usuario
                        $_SESSION['logged_in'] = true;
                        $_SESSION['user_id'] = $user['id_usuario'];
                        $_SESSION['username'] = $user['nombre'];
                        
                        // Actualizar el token para mayor seguridad
                        $new_token = bin2hex(random_bytes(32));
                        $update_query = "UPDATE usuarios SET remember_token = ? WHERE id_usuario = ?";
                        $update_stmt = $conexion->prepare($update_query);
                        
                        if ($update_stmt) {
                            $update_stmt->bind_param("si", $new_token, $user_id);
                            $update_stmt->execute();
                            $update_stmt->close();
                            
                            // Actualizar la cookie con el nuevo token
                            remember_me($user_id, $new_token);
                        }
                        
                        return true;
                    }
                    
                    $stmt->close();
                }
            }
            
            // Si llegamos aquí, la cookie no es válida, eliminarla
            setcookie('fullmoon_remember', '', time() - 3600, '/');
        }
        
        return false;
    }
}

// Obtener datos del perfil del usuario
if (!function_exists('get_user_profile')) {
    function get_user_profile($user_id, $conn = null) {
        global $conexion;
        
        // Usar la conexión pasada como parámetro o la global si no se proporciona
        $db_conn = $conn ? $conn : $conexion;
        
        if (!$db_conn) {
            return false;
        }
        
        $query = "SELECT * FROM perfiles WHERE id_usuario = ?";
        $stmt = $db_conn->prepare($query);
        
        if ($stmt === false) {
            return false;
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 1) {
            return $resultado->fetch_assoc();
        } else {
            // Si no existe un perfil, crear uno por defecto
            create_default_profile($user_id, $db_conn);
            return get_user_profile($user_id, $db_conn); // Llamada recursiva
        }
    }
}

// Crear perfil por defecto
if (!function_exists('create_default_profile')) {
    function create_default_profile($user_id, $conn) {
        $default_avatar = DEFAULT_AVATAR;
        
        // Verificar que el archivo de avatar por defecto existe
        if (!file_exists($default_avatar)) {
            $default_avatar = 'assets/img/placeholder.jpg';
        }
        
        $query = "INSERT INTO perfiles (id_usuario, biografia, avatar) VALUES (?, '', ?)";
        $stmt = $conn->prepare($query);
        
        if ($stmt !== false) {
            $stmt->bind_param("is", $user_id, $default_avatar);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Subir avatar
if (!function_exists('upload_avatar')) {
    function upload_avatar($file, $user_id) {
        global $conexion;
        
        $target_dir = "assets/img/avatars/";
        
        // Crear el directorio si no existe
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = "avatar_" . $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Verificar si es una imagen real
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            return false;
        }
        
        // Verificar tamaño (máximo 2MB)
        if ($file["size"] > 2000000) {
            return false;
        }
        
        // Permitir ciertos formatos de archivo
        if ($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg" && $file_extension != "gif") {
            return false;
        }
        
        // Intentar subir el archivo
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            // Verificar si existe un perfil para este usuario
            $check_profile = "SELECT id_usuario FROM perfiles WHERE id_usuario = ?";
            $stmt_check = $conexion->prepare($check_profile);
            
            if ($stmt_check) {
                $stmt_check->bind_param("i", $user_id);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();
                
                if ($result_check->num_rows === 0) {
                    // Crear perfil si no existe
                    $create_profile = "INSERT INTO perfiles (id_usuario, avatar, biografia) VALUES (?, ?, '')";
                    $stmt_create = $conexion->prepare($create_profile);
                    
                    if ($stmt_create) {
                        $stmt_create->bind_param("is", $user_id, $target_file);
                        $stmt_create->execute();
                        $stmt_create->close();
                    }
                } else {
                    // Actualizar avatar existente
                    $update_avatar = "UPDATE perfiles SET avatar = ? WHERE id_usuario = ?";
                    $stmt_update = $conexion->prepare($update_avatar);
                    
                    if ($stmt_update) {
                        $stmt_update->bind_param("si", $target_file, $user_id);
                        $stmt_update->execute();
                        $stmt_update->close();
                    }
                }
                
                $stmt_check->close();
            }
            
            return $target_file;
        } else {
            return false;
        }
    }
}

// Función para obtener el avatar del usuario
if (!function_exists('get_user_avatar')) {
    function get_user_avatar($conn = null) {
        global $conexion;
        
        // Usar la conexión pasada como parámetro o la global si no se proporciona
        $db_conn = $conn ? $conn : $conexion;
        
        if (!is_logged_in() || !isset($_SESSION['user_id'])) {
            return DEFAULT_AVATAR;
        }
        
        $user_id = $_SESSION['user_id'];
        
        // Si no hay conexión disponible, devolver el avatar por defecto
        if (!$db_conn) {
            return DEFAULT_AVATAR;
        }
        
        // Verificar si existe un perfil para el usuario
        $query = "SELECT avatar FROM perfiles WHERE id_usuario = ?";
        $stmt = $db_conn->prepare($query);
        
        if ($stmt === false) {
            return DEFAULT_AVATAR;
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 1) {
            $perfil = $resultado->fetch_assoc();
            return $perfil['avatar'] ? $perfil['avatar'] : DEFAULT_AVATAR;
        }
        
        return DEFAULT_AVATAR;
    }
}

// Cerrar sesión
if (!function_exists('logout')) {
    function logout() {
        // Destruir todas las variables de sesión
        $_SESSION = array();
        
        // Eliminar la cookie "Recordarme" si existe
        if (isset($_COOKIE['fullmoon_remember'])) {
            setcookie('fullmoon_remember', '', time() - 3600, '/');
            setcookie('fullmoon_remember', '', time() - 3600, '/', '', false, true);
        }
        
        // Si se desea destruir la sesión completamente, borrar también la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }
        
        // Finalmente, destruir la sesión
        session_destroy();
        
        return true;
    }
}

// Obtener datos del usuario
if (!function_exists('get_user_data')) {
    function get_user_data($user_id) {
        global $conexion;
        
        $query = "SELECT u.*, p.biografia, p.avatar, p.telefono 
                  FROM usuarios u 
                  LEFT JOIN perfiles p ON u.id_usuario = p.id_usuario 
                  WHERE u.id_usuario = ?";
        $stmt = $conexion->prepare($query);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return false;
        }
        
        $user_data = $result->fetch_assoc();
        $stmt->close();
        
        // Si no tiene avatar, usar uno por defecto
        if (empty($user_data['avatar'])) {
            $user_data['avatar'] = DEFAULT_AVATAR;
        }
        
        return $user_data;
    }
}

// Obtener estadísticas del usuario incluyendo reseñas
if (!function_exists('get_user_stats')) {
    function get_user_stats($user_id) {
        global $conexion;
        
        $stats = [
            'seguidores' => 0,
            'seguidos' => 0,
            'posts' => 0,
            'foros' => [],
            'ultimos_posts' => [],
            'resenas' => []
        ];
        
        // Contar seguidores (si existe la tabla)
        $query = "SHOW TABLES LIKE 'seguidores'";
        $result = $conexion->query($query);
        if ($result && $result->num_rows > 0) {
            $query = "SELECT COUNT(*) as total FROM seguidores WHERE id_seguido = ?";
            $stmt = $conexion->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    $stats['seguidores'] = $row['total'];
                }
                
                $stmt->close();
            }
        }
        
        // Contar seguidos (si existe la tabla)
        $query = "SHOW TABLES LIKE 'seguidores'";
        $result = $conexion->query($query);
        if ($result && $result->num_rows > 0) {
            $query = "SELECT COUNT(*) as total FROM seguidores WHERE id_usuario = ?";
            $stmt = $conexion->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    $stats['seguidos'] = $row['total'];
                }
                
                $stmt->close();
            }
        }
        
        // Contar publicaciones en foros (si existe la tabla)
        $query = "SHOW TABLES LIKE 'posts_foro'";
        $result = $conexion->query($query);
        if ($result && $result->num_rows > 0) {
            $query = "SELECT COUNT(*) as total FROM posts_foro WHERE id_usuario = ?";
            $stmt = $conexion->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    $stats['posts'] = $row['total'];
                }
                
                $stmt->close();
            }
        }
        
        // Contar reseñas
        $query = "SELECT COUNT(*) as total FROM resenas WHERE id_usuario = ?";
        $stmt = $conexion->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $stats['posts'] += $row['total']; // Sumar reseñas al total de publicaciones
            }
            
            $stmt->close();
        }
        
        // Obtener foros seguidos (si existe la tabla)
        $query = "SHOW TABLES LIKE 'suscripciones_foro'";
        $result = $conexion->query($query);
        if ($result && $result->num_rows > 0) {
            $query = "SELECT f.* 
                      FROM foros f 
                      INNER JOIN suscripciones_foro sf ON f.id_foro = sf.id_foro 
                      WHERE sf.id_usuario = ?
                      LIMIT 10";
            $stmt = $conexion->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $stats['foros'][] = $row;
                }
                
                $stmt->close();
            }
        }
        
        // Obtener últimas publicaciones en foros (si existe la tabla)
        $query = "SHOW TABLES LIKE 'posts_foro'";
        $result = $conexion->query($query);
        if ($result && $result->num_rows > 0) {
            $query = "SELECT pf.*, f.titulo as foro_nombre 
                      FROM posts_foro pf 
                      INNER JOIN foros f ON pf.id_foro = f.id_foro 
                      WHERE pf.id_usuario = ? 
                      ORDER BY pf.fecha_creacion DESC 
                      LIMIT 5";
            $stmt = $conexion->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $stats['ultimos_posts'][] = $row;
                }
                
                $stmt->close();
            }
        }
        
        // Obtener reseñas del usuario
        $query = "SELECT * FROM resenas 
                  WHERE id_usuario = ? 
                  ORDER BY fecha_creacion DESC 
                  LIMIT 10";
        $stmt = $conexion->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $stats['resenas'][] = $row;
            }
            
            $stmt->close();
        }
        
        return $stats;
    }
}

// Actualizar el perfil del usuario
if (!function_exists('update_user_profile')) {
    function update_user_profile($user_id, $data) {
        global $conexion;
        
        // Actualizar datos básicos del usuario
        $query = "UPDATE usuarios SET nombre = ? WHERE id_usuario = ?";
        $stmt = $conexion->prepare($query);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("si", $data['nombre'], $user_id);
        $result1 = $stmt->execute();
        $stmt->close();
        
        // Verificar si existe un perfil para este usuario
        $query = "SELECT id_usuario FROM perfiles WHERE id_usuario = ?";
        $stmt = $conexion->prepare($query);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows === 0) {
            // Crear un nuevo perfil
            $query = "INSERT INTO perfiles (id_usuario, biografia, telefono) VALUES (?, ?, ?)";
            $stmt = $conexion->prepare($query);
            
            if (!$stmt) {
                return false;
            }
            
            $stmt->bind_param("iss", $user_id, $data['biografia'], $data['telefono']);
            $result2 = $stmt->execute();
            $stmt->close();
        } else {
            // Actualizar perfil existente
            $query = "UPDATE perfiles SET biografia = ?, telefono = ? WHERE id_usuario = ?";
            $stmt = $conexion->prepare($query);
            
            if (!$stmt) {
                return false;
            }
            
            $stmt->bind_param("ssi", $data['biografia'], $data['telefono'], $user_id);
            $result2 = $stmt->execute();
            $stmt->close();
        }
        
        return $result1 && $result2;
    }
}

// Calcular tiempo transcurrido
if (!function_exists('time_elapsed_string')) {
    function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'año',
            'm' => 'mes',
            'w' => 'semana',
            'd' => 'día',
            'h' => 'hora',
            'i' => 'minuto',
            's' => 'segundo',
        );
        
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? ($k == 'm' ? 'es' : 's') : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) : 'justo ahora';
    }
}
?>
