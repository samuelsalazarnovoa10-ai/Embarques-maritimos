<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sql = "SELECT e.*, r.nombre as ruta_nombre, COUNT(ec.contenedor_id) as contenedores_count
            FROM embarques e
            LEFT JOIN rutas r ON e.ruta_id = r.id
            LEFT JOIN embarque_contenedor ec ON e.id = ec.embarque_id
            GROUP BY e.id
            ORDER BY e.created_at DESC";
    
    $result = $conn->query($sql);
    $embarques = [];
    
    while ($row = $result->fetch_assoc()) {
        $embarque_id = $row['id'];
        
        $sql_contenedores = "SELECT c.* FROM contenedores c
                            INNER JOIN embarque_contenedor ec ON c.id = ec.contenedor_id
                            WHERE ec.embarque_id = $embarque_id";
        $result_contenedores = $conn->query($sql_contenedores);
        $contenedores = [];
        
        while ($cont = $result_contenedores->fetch_assoc()) {
            $contenedores[] = $cont;
        }
        
        $row['contenedores'] = $contenedores;
        $embarques[] = $row;
    }
    
    echo json_encode($embarques);
    
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $nombre = $conn->real_escape_string($data['nombre']);
    $barco = $conn->real_escape_string($data['barco']);
    $estado = $conn->real_escape_string($data['estado']);
    $fecha_salida = $conn->real_escape_string($data['fecha_salida']);
    $fecha_llegada = $conn->real_escape_string($data['fecha_llegada']);
    $ruta_id = isset($data['ruta_id']) && $data['ruta_id'] ? intval($data['ruta_id']) : 'NULL';
    
    $sql = "INSERT INTO embarques (nombre, barco, estado, fecha_salida, fecha_llegada, ruta_id)
            VALUES ('$nombre', '$barco', '$estado', '$fecha_salida', '$fecha_llegada', $ruta_id)";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    
} elseif ($method === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = intval($data['id']);
    
    $sql = "DELETE FROM embarques WHERE id = $id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}

$conn->close();
?>
