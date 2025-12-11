<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sql = "SELECT d.*, e.nombre as embarque_nombre, c.numero as contenedor_numero
            FROM documentos d
            LEFT JOIN embarques e ON d.embarque_id = e.id
            LEFT JOIN contenedores c ON d.contenedor_id = c.id
            ORDER BY d.created_at DESC";
    
    $result = $conn->query($sql);
    $documentos = [];
    
    while ($row = $result->fetch_assoc()) {
        $documentos[] = $row;
    }
    
    echo json_encode($documentos);
    
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $tipo = $conn->real_escape_string($data['tipo']);
    $numero = $conn->real_escape_string($data['numero']);
    $embarque_id = intval($data['embarque_id']);
    $contenedor_id = isset($data['contenedor_id']) && $data['contenedor_id'] ? intval($data['contenedor_id']) : 'NULL';
    $descripcion = $conn->real_escape_string($data['descripcion']);
    $fecha_emision = $conn->real_escape_string($data['fecha_emision']);
    
    $sql = "INSERT INTO documentos (tipo, numero, embarque_id, contenedor_id, descripcion, fecha_emision)
            VALUES ('$tipo', '$numero', $embarque_id, $contenedor_id, '$descripcion', '$fecha_emision')";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    
} elseif ($method === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = intval($data['id']);
    
    $sql = "DELETE FROM documentos WHERE id = $id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}

$conn->close();
?>
