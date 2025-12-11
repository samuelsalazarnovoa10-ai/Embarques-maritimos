<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Instalación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            padding: 40px 20px;
        }
        .container-check {
            background: white;
            border-radius: 4px;
            padding: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            max-width: 600px;
            margin: 0 auto;
        }
        .check-item {
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 4px;
            background: #fafafa;
            border: 1px solid #e0e0e0;
        }
        .check-item.success {
            background: #e8f5e9;
            border-color: #c8e6c9;
        }
        .check-item.error {
            background: #ffebee;
            border-color: #ffcdd2;
        }
        .check-icon {
            font-size: 20px;
            margin-right: 12px;
            min-width: 24px;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 24px;
            text-align: center;
        }
        .status {
            text-align: center;
            margin-top: 24px;
            padding: 16px;
            border-radius: 4px;
        }
        .status.ready {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .status.not-ready {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .btn-link {
            color: #333;
            text-decoration: none;
            font-weight: 500;
        }
        .btn-link:hover {
            color: #1a1a1a;
        }
    </style>
</head>
<body>
    <div class="container-check">
        <h1>Verificación de Instalación</h1>
        
        <?php
        $checks = [];
        $all_ok = true;
        
        $checks[] = [
            'name' => 'PHP Version',
            'status' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'message' => 'PHP ' . PHP_VERSION
        ];
        
        $checks[] = [
            'name' => 'MySQLi Extension',
            'status' => extension_loaded('mysqli'),
            'message' => extension_loaded('mysqli') ? 'Instalada' : 'No instalada'
        ];
        
        $db_check = false;
        $db_message = '';
        
        $conn = @new mysqli('localhost', 'root', '', 'maritime_shipping');
        if ($conn->connect_error) {
            $db_message = 'Error: ' . $conn->connect_error;
        } else {
            $db_check = true;
            $db_message = 'Conectado correctamente';
            $conn->close();
        }
        
        $checks[] = [
            'name' => 'Base de Datos',
            'status' => $db_check,
            'message' => $db_message
        ];
        
        $files = [
            'config/database.php',
            'config/config.php',
            'api/embarques.php',
            'api/contenedores.php',
            'api/rutas.php',
            'api/documentos.php',
            'js/app.js'
        ];
        
        foreach ($files as $file) {
            $exists = file_exists($file);
            $checks[] = [
                'name' => 'Archivo: ' . $file,
                'status' => $exists,
                'message' => $exists ? 'Encontrado' : 'No encontrado'
            ];
            if (!$exists) $all_ok = false;
        }
        
        foreach ($checks as $check) {
            $class = $check['status'] ? 'success' : 'error';
            $icon = $check['status'] ? '✓' : '✗';
            echo "
            <div class='check-item $class'>
                <div class='check-icon'>$icon</div>
                <div>
                    <strong>{$check['name']}</strong><br>
                    <small>{$check['message']}</small>
                </div>
            </div>
            ";
            if (!$check['status']) $all_ok = false;
        }
        
        if ($all_ok && $db_check) {
            echo "
            <div class='status ready'>
                <h4>Instalación Correcta</h4>
                <p>El sistema está listo para usar.</p>
                <a href='index.html' class='btn-link'>Ir a la Aplicación</a>
            </div>
            ";
        } else {
            echo "
            <div class='status not-ready'>
                <h4>Problemas Detectados</h4>
                <p>Por favor, revisa los errores arriba.</p>
            </div>
            ";
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
