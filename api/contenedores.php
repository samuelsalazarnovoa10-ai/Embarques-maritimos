<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sql = "SELECT c.*, COUNT(ec.embarque_id) as embarques_count
            FROM contenedores c
            LEFT JOIN embarque_contenedor ec ON c.id = ec.contenedor_id
            GROUP BY c.id
            ORDER BY c.created_at DESC";
    
    $result = $conn->query($sql);
    $contenedores = [];
    
    while ($row = $result->fetch_assoc()) {
        $contenedor_id = $row['id'];
        
        $sql_embarques = "SELECT e.* FROM embarques e
                         INNER JOIN embarque_contenedor ec ON e.id = ec.embarque_id
                         WHERE ec.contenedor_id = $contenedor_id";
        $result_embarques = $conn->query($sql_embarques);
        $embarques = [];
        
        while ($emb = $result_embarques->fetch_assoc()) {
            $embarques[] = $emb;
        }
        
        $row['embarques'] = $embarques;
        $contenedores[] = $row;
    }
    
    echo json_encode($contenedores);
    
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $numero = $conn->real_escape_string($data['numero']);
    $tipo = $conn->real_escape_string($data['tipo']);
    $capacidad = floatval($data['capacidad']);
    $contenido = $conn->real_escape_string($data['contenido']);
    
    $sql = "INSERT INTO contenedores (numero, tipo, capacidad, contenido)
            VALUES ('$numero', '$tipo', $capacidad, '$contenido')";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    
} elseif ($method === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = intval($data['id']);
    
    $sql = "DELETE FROM contenedores WHERE id = $id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}

$conn->close();
?>
