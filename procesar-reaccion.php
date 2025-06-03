<?php
// Archivo: procesar-reaccion.php
require_once 'includes/init.php';

header('Content-Type: application/json');

// Verificar que el usuario esté logueado
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id_comentario = (int)$input['id_comentario'];
    $tipo = $input['tipo']; // 'like' o 'dislike'
    $user_id = $_SESSION['user_id'];
    
    // Validar tipo de reacción
    if (!in_array($tipo, ['like', 'dislike'])) {
        echo json_encode(['success' => false, 'message' => 'Tipo de reacción inválido']);
        exit;
    }
    
    // Verificar que el comentario existe
    $sql_check = "SELECT id_comentario FROM comentarios WHERE id_comentario = ? AND estado = 'activo'";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param("i", $id_comentario);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Comentario no encontrado']);
        exit;
    }
    
    // Verificar si el usuario ya reaccionó a este comentario
    $sql_existing = "SELECT tipo FROM comentario_reacciones WHERE id_comentario = ? AND id_usuario = ?";
    $stmt_existing = $conexion->prepare($sql_existing);
    $stmt_existing->bind_param("ii", $id_comentario, $user_id);
    $stmt_existing->execute();
    $result_existing = $stmt_existing->get_result();
    
    if ($result_existing->num_rows > 0) {
        $existing_reaction = $result_existing->fetch_assoc();
        
        if ($existing_reaction['tipo'] === $tipo) {
            // Si es la misma reacción, eliminarla (toggle)
            $sql_delete = "DELETE FROM comentario_reacciones WHERE id_comentario = ? AND id_usuario = ?";
            $stmt_delete = $conexion->prepare($sql_delete);
            $stmt_delete->bind_param("ii", $id_comentario, $user_id);
            $stmt_delete->execute();
            $action = 'removed';
        } else {
            // Si es diferente, actualizar la reacción
            $sql_update = "UPDATE comentario_reacciones SET tipo = ? WHERE id_comentario = ? AND id_usuario = ?";
            $stmt_update = $conexion->prepare($sql_update);
            $stmt_update->bind_param("sii", $tipo, $id_comentario, $user_id);
            $stmt_update->execute();
            $action = 'updated';
        }
    } else {
        // Insertar nueva reacción
        $sql_insert = "INSERT INTO comentario_reacciones (id_comentario, id_usuario, tipo) VALUES (?, ?, ?)";
        $stmt_insert = $conexion->prepare($sql_insert);
        $stmt_insert->bind_param("iis", $id_comentario, $user_id, $tipo);
        $stmt_insert->execute();
        $action = 'added';
    }
    
    // Obtener conteos actualizados
    $sql_counts = "SELECT 
                    SUM(CASE WHEN tipo = 'like' THEN 1 ELSE 0 END) as likes,
                    SUM(CASE WHEN tipo = 'dislike' THEN 1 ELSE 0 END) as dislikes
                   FROM comentario_reacciones 
                   WHERE id_comentario = ?";
    $stmt_counts = $conexion->prepare($sql_counts);
    $stmt_counts->bind_param("i", $id_comentario);
    $stmt_counts->execute();
    $result_counts = $stmt_counts->get_result();
    $counts = $result_counts->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'action' => $action,
        'likes' => (int)$counts['likes'],
        'dislikes' => (int)$counts['dislikes']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
