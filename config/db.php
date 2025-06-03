<?php
/**
 * Funciones para interactuar con la base de datos
 */

// Ejecutar consulta SQL con parámetros
function db_query($sql, $params = []) {
    global $conexion;
    
    if (!$conexion) {
        return false;
    }
    
    if (empty($params)) {
        // Consulta simple sin parámetros
        $result = $conexion->query($sql);
        return $result;
    } else {
        // Consulta preparada con parámetros
        $stmt = $conexion->prepare($sql);
        
        if ($stmt === false) {
            return false;
        }
        
        // Determinar tipos de parámetros
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
        }
        
        // Vincular parámetros
        if (!empty($params)) {
            $bind_params = array_merge([$types], $params);
            $bind_params_refs = [];
            
            foreach ($bind_params as $key => $value) {
                $bind_params_refs[$key] = &$bind_params[$key];
            }
            
            call_user_func_array([$stmt, 'bind_param'], $bind_params_refs);
        }
        
        // Ejecutar consulta
        $stmt->execute();
        
        // Obtener resultados
        $result = $stmt->get_result();
        
        // Cerrar statement
        $stmt->close();
        
        return $result;
    }
}

// Obtener todos los resultados de una consulta
function db_fetch_all($result) {
    if ($result === false) {
        return [];
    }
    
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    
    return $rows;
}

// Obtener una fila de resultados
function db_fetch_assoc($result) {
    if ($result === false) {
        return null;
    }
    
    return $result->fetch_assoc();
}

// Obtener el último ID insertado
function db_last_insert_id() {
    global $conexion;
    return $conexion->insert_id;
}

// Escapar string para prevenir inyección SQL
function db_escape($string) {
    global $conexion;
    return $conexion->real_escape_string($string);
}

// Cerrar conexión a la base de datos
function db_close() {
    global $conexion;
    if ($conexion) {
        $conexion->close();
    }
}
?>
