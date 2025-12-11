<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $ruta_id = isset($_GET['ruta_id']) ? intval($_GET['ruta_id']) : 0;
    
    if ($ruta_id > 0) {
        $sql = "SELECT * FROM escalas WHERE ruta_id = $ruta_id ORDER BY orden ASC";
    } else {
        $sql = "SELECT * FROM escalas ORDER BY ruta_id, orden ASC";
    }
    
    $result = $conn->query($sql);
    $escalas = [];
    
    while ($row = $result->fetch_assoc()) {
        $escalas[] = $row;
    }
    
    echo json_encode($escalas);
    
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $ruta_id = intval($data['ruta_id']);
    $puerto = $conn->real_escape_string($data['puerto']);
    $dias = intval($data['dias']);
    
    $sql_orden = "SELECT MAX(orden) as max_orden FROM escalas WHERE ruta_id = $ruta_id";
    $result_orden = $conn->query($sql_orden);
    $row_orden = $result_orden->fetch_assoc();
    $orden = ($row_orden['max_orden'] ?? 0) + 1;
    
    $sql = "INSERT INTO escalas (ruta_id, puerto, dias, orden)
            VALUES ($ruta_id, '$puerto', $dias, $orden)";
    
    if ($conn->query($sql)) {
        $sql_update = "UPDATE rutas SET dias_totales = (
                        SELECT SUM(dias) FROM escalas WHERE ruta_id = $ruta_id
                       ) WHERE id = $ruta_id";
        $conn->query($sql_update);
        
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    
} elseif ($method === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = intval($data['id']);
    
    $sql_get = "SELECT ruta_id FROM escalas WHERE id = $id";
    $result_get = $conn->query($sql_get);
    $row_get = $result_get->fetch_assoc();
    $ruta_id = $row_get['ruta_id'];
    
    $sql = "DELETE FROM escalas WHERE id = $id";
    
    if ($conn->query($sql)) {
        $sql_update = "UPDATE rutas SET dias_totales = (
                        SELECT COALESCE(SUM(dias), 0) FROM escalas WHERE ruta_id = $ruta_id
                       ) WHERE id = $ruta_id";
        $conn->query($sql_update);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}

$conn->close();
?>
