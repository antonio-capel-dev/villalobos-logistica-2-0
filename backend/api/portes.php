<?php
// backend/api/portes.php
header('Content-Type: application/json');
require_once '../conexion.php';
require_once '../auth.php';

// Verificar autenticación para cualquier operación
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet($pdo);
            break;
        case 'POST':
            handlePost($pdo);
            break;
        case 'PUT':
            handlePut($pdo);
            break;
        case 'DELETE':
            handleDelete($pdo);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]);
}

// --- Funciones ---

function handleGet($pdo) {
    if (isset($_GET['id'])) {
        // Obtener un solo porte
        $stmt = $pdo->prepare("SELECT * FROM portes WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $porte = $stmt->fetch();
        
        if ($porte) {
            echo json_encode($porte);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Porte no encontrado']);
        }
    } else {
        // Listar todos (opcional: filtros)
        $sql = "SELECT * FROM portes ORDER BY fecha_programada DESC";
        $stmt = $pdo->query($sql);
        $portes = $stmt->fetchAll();
        echo json_encode($portes);
    }
}

function handlePost($pdo) {
    // Leer JSON del body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos inválidos']);
        return;
    }

    // Validaciones básicas
    if (empty($input['origen']) || empty($input['destino']) || empty($input['fecha_programada'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan campos obligatorios']);
        return;
    }

    $sql = "INSERT INTO portes (fecha_programada, origen, destino, kms, peso, ingreso_estimado, coste_estimado, estado, conductor) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'recibido', ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $input['fecha_programada'],
        $input['origen'],
        $input['destino'],
        $input['kms'] ?? 0,
        $input['peso'] ?? 0,
        $input['ingreso_estimado'] ?? 0,
        $input['coste_estimado'] ?? 0,
        $input['conductor'] ?? null
    ]);

    echo json_encode(['id' => $pdo->lastInsertId(), 'message' => 'Porte creado correctamente']);
}

function handlePut($pdo) {
    // Leer JSON del body
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID es obligatorio para actualizar']);
        return;
    }

    // Construir query dinámica o fija según necesidad. 
    // Aquí actualizamos todo lo editable.
    $sql = "UPDATE portes SET 
            fecha_programada = ?, 
            origen = ?, 
            destino = ?, 
            kms = ?, 
            peso = ?, 
            ingreso_estimado = ?, 
            coste_estimado = ?, 
            estado = ?, 
            conductor = ? 
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $input['fecha_programada'],
        $input['origen'],
        $input['destino'],
        $input['kms'],
        $input['peso'],
        $input['ingreso_estimado'],
        $input['coste_estimado'],
        $input['estado'],
        $input['conductor'],
        $input['id']
    ]);

    echo json_encode(['message' => 'Porte actualizado correctamente']);
}

function handleDelete($pdo) {
    // Permitir borrar por ID en query param o body, usaremos query param por simplicidad
    // DELETE /api/portes.php?id=1
    
    if (empty($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID es obligatorio']);
        return;
    }

    // Opcional: Solo admin puede borrar
    requireAdmin(); 

    $stmt = $pdo->prepare("DELETE FROM portes WHERE id = ?");
    $stmt->execute([$_GET['id']]);

    echo json_encode(['message' => 'Porte eliminado correctamente']);
}
?>