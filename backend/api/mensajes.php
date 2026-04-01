<?php
// backend/api/mensajes.php
// API privada para gestionar los mensajes de contacto recibidos

header("Content-Type: application/json; charset=UTF-8");

require_once '../conexion.php';

// Solo accesible si hay sesión activa
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autorizado.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        // Devuelve todos los mensajes, los no leídos primero
        try {
            $stmt = $pdo->query("SELECT * FROM mensajes_contacto ORDER BY leido ASC, creado_en DESC");
            $mensajes = $stmt->fetchAll();
            echo json_encode(['ok' => true, 'data' => $mensajes]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error al obtener los mensajes.']);
        }
        break;

    case 'PUT':
        // Marca un mensaje como leído
        $data = json_decode(file_get_contents("php://input"), true);
        $id   = isset($data['id']) ? $data['id'] : null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Falta el ID del mensaje.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE mensajes_contacto SET leido = 1 WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['ok' => true, 'mensaje' => 'Mensaje marcado como leído.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error al actualizar el mensaje.']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método no soportado.']);
        break;
}
?>
