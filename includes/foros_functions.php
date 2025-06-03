<?php
// Archivo: includes/foros_functions.php
// Funciones básicas para el sistema de foros

if (!function_exists("obtenerTodosLosForos")) {
    function obtenerTodosLosForos($limite = 10) {
        global $conexion;
        
        $sql = "SELECT f.*, u.nombre as nombre_creador
                FROM foros f 
                LEFT JOIN usuarios u ON f.id_creador = u.id_usuario
                WHERE f.activo = 1
                ORDER BY f.fecha_creacion DESC
                LIMIT ?";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("i", $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $foros = [];
        while ($row = $result->fetch_assoc()) {
            $foros[] = $row;
        }
        
        return $foros;
    }
}

if (!function_exists("obtenerInfoForo")) {
    function obtenerInfoForo($id_foro) {
        global $conexion;
        
        $sql = "SELECT f.*, u.nombre as nombre_creador 
                FROM foros f 
                LEFT JOIN usuarios u ON f.id_creador = u.id_usuario 
                WHERE f.id_foro = ? AND f.activo = 1";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("i", $id_foro);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
}

if (!function_exists("esMiembroForo")) {
    function esMiembroForo($id_foro, $user_id) {
        global $conexion;
        
        $sql = "SELECT id_miembro FROM foro_miembros WHERE id_foro = ? AND id_usuario = ?";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("ii", $id_foro, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
}

if (!function_exists("esAdminForo")) {
    function esAdminForo($id_foro, $user_id) {
        global $conexion;
        
        $sql = "SELECT rol FROM foro_miembros WHERE id_foro = ? AND id_usuario = ? AND rol IN ('admin', 'moderador')";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("ii", $id_foro, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
}

if (!function_exists("obtenerPostsForo")) {
    function obtenerPostsForo($id_foro, $id_post_padre = null) {
        global $conexion;
        
        $sql = "SELECT p.*, u.nombre as nombre_usuario, 
                       (SELECT avatar FROM perfiles WHERE id_usuario = p.id_usuario LIMIT 1) as avatar,
                       (SELECT COUNT(*) FROM foro_post_likes WHERE id_post = p.id_post AND tipo = 'like') as likes,
                       (SELECT COUNT(*) FROM foro_post_likes WHERE id_post = p.id_post AND tipo = 'dislike') as dislikes,
                       fm.rol as rol_foro
                FROM foro_posts p 
                LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario 
                LEFT JOIN foro_miembros fm ON p.id_foro = fm.id_foro AND p.id_usuario = fm.id_usuario
                WHERE p.id_foro = ? AND p.estado = 'activo'";
        
        if ($id_post_padre === null) {
            $sql .= " AND p.id_post_padre IS NULL";
        } else {
            $sql .= " AND p.id_post_padre = ?";
        }
        
        $sql .= " ORDER BY p.es_hilo_principal DESC, p.fecha_creacion DESC";
        
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            return [];
        }
        
        if ($id_post_padre === null) {
            $stmt->bind_param("i", $id_foro);
        } else {
            $stmt->bind_param("ii", $id_foro, $id_post_padre);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $posts = [];
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
        
        return $posts;
    }
}

if (!function_exists("renderizarPost")) {
    function renderizarPost($post, $es_miembro, $es_admin_foro, $es_admin_sitio, $nivel = 0) {
        // Función básica de renderizado
        echo "<div class=\"post-item\">";
        echo "<h3>" . htmlspecialchars($post["titulo"] ?: "Post sin título") . "</h3>";
        echo "<p>" . htmlspecialchars($post["contenido"]) . "</p>";
        echo "<small>Por: " . htmlspecialchars($post["nombre_usuario"]) . " - " . $post["fecha_creacion"] . "</small>";
        echo "</div>";
    }
}
?>