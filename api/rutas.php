<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sql = "SELECT * FROM rutas ORDER BY created_at DESC";
    
    $result = $conn->query($sql);
    $rutas = [];
    
    while ($row = $result->fetch_assoc()) {
        $ruta_id = $row['id'];
        
        $sql_escalas = "SELECT * FROM escalas WHERE ruta_id = $ruta_id ORDER BY orden ASC";
        $result_escalas = $conn->query($sql_escalas);
        $escalas = [];
        
        while ($escala = $result_escalas->fetch_assoc()) {
            $escalas[] = $escala;
        }
        
        $row['escalas'] = $escalas;
        $rutas[] = $row;
    }
    
    echo json_encode($rutas);
    
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $nombre = $conn->real_escape_string($data['nombre']);
    $origen = $conn->real_escape_string($data['origen']);
    $destino = $conn->real_escape_string($data['destino']);
    
    $sql = "INSERT INTO rutas (nombre, origen, destino)
            VALUES ('$nombre', '$origen', '$destino')";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    
} elseif ($method === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = intval($data['id']);
    
    $sql = "DELETE FROM rutas WHERE id = $id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}

$conn->close();
?>
