<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $embarque_id = isset($_GET['embarque_id']) ? intval($_GET['embarque_id']) : 0;
    
    if ($embarque_id > 0) {
        $sql = "SELECT c.* FROM contenedores c
                INNER JOIN embarque_contenedor ec ON c.id = ec.contenedor_id
                WHERE ec.embarque_id = $embarque_id";
    } else {
        $sql = "SELECT * FROM embarque_contenedor ORDER BY created_at DESC";
    }
    
    $result = $conn->query($sql);
    $asignaciones = [];
    
    while ($row = $result->fetch_assoc()) {
        $asignaciones[] = $row;
    }
    
    echo json_encode($asignaciones);
    
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $embarque_id = intval($data['embarque_id']);
    $contenedor_id = intval($data['contenedor_id']);
    
    $sql_check = "SELECT id FROM embarque_contenedor 
                  WHERE embarque_id = $embarque_id AND contenedor_id = $contenedor_id";
    $result_check = $conn->query($sql_check);
    
    if ($result_check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Esta asignación ya existe']);
        exit;
    }
    
    $sql = "INSERT INTO embarque_contenedor (embarque_id, contenedor_id)
            VALUES ($embarque_id, $contenedor_id)";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    
} elseif ($method === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $embarque_id = intval($data['embarque_id']);
    $contenedor_id = intval($data['contenedor_id']);
    
    $sql = "DELETE FROM embarque_contenedor 
            WHERE embarque_id = $embarque_id AND contenedor_id = $contenedor_id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}

$conn->close();
?>
