<?php
// backend/api/portes.php
require_once __DIR__ . '/../conexion.php';

$origenPermitido = env('CORS_ORIGIN', 'https://www.villaloboslogistica.com');
$origenSolicitud = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origenSolicitud === $origenPermitido || str_starts_with($origenSolicitud, 'http://localhost')) {
    header("Access-Control-Allow-Origin: $origenSolicitud");
    header("Vary: Origin");
}
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Content-Type: application/json; charset=UTF-8");


if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autorizado. Debes iniciar sesión.']);
    exit;
}

$response = array();

function esGestor() {
    $rol = $_SESSION['rol'] ?? '';
    return $rol === 'admin' || $rol === 'editor';
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        try {
            if (esGestor()) {
                $query = "
                    SELECT p.*,
                           c.nombre as cliente_nombre,
                           d.nombre as conductor_nombre
                    FROM portes p
                    LEFT JOIN usuarios c ON p.cliente_id = c.id
                    LEFT JOIN usuarios d ON p.conductor_id = d.id
                    ORDER BY p.id DESC
                ";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
            } else {
                $query = "
                    SELECT p.*,
                           c.nombre as cliente_nombre,
                           d.nombre as conductor_nombre
                    FROM portes p
                    LEFT JOIN usuarios c ON p.cliente_id = c.id
                    LEFT JOIN usuarios d ON p.conductor_id = d.id
                    WHERE p.cliente_id = :uid
                    ORDER BY p.id DESC
                ";
                $stmt = $pdo->prepare($query);
                $stmt->execute([':uid' => $_SESSION['user_id']]);
            }

            $portes = $stmt->fetchAll();
            echo json_encode(['ok' => true, 'data' => $portes]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error al recuperar los portes.']);
        }
        break;

    case 'POST':
        if (!esGestor()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'No tienes permiso para crear portes.']);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $cliente_id   = !empty($data['cliente_id'])        ? $data['cliente_id']               : null;
        $conductor_id = !empty($data['conductor_id'])       ? $data['conductor_id']              : null;
        $fecha        = isset($data['fecha_programada'])    ? trim($data['fecha_programada'])    : '';
        $origen       = isset($data['origen'])              ? trim($data['origen'])              : '';
        $destino      = isset($data['destino'])             ? trim($data['destino'])             : '';
        $kms          = isset($data['kms'])                 ? $data['kms']                       : 0;
        $peso         = isset($data['peso'])                ? $data['peso']                      : 0;
        $precio       = isset($data['precio'])              ? $data['precio']                    : 0;
        $estado       = !empty($data['estado'])             ? trim($data['estado'])              : 'pendiente';

        if (empty($fecha) || empty($origen) || empty($destino)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Los campos Fecha, Origen y Destino son obligatorios.']);
            exit;
        }

        try {
            $query = "INSERT INTO portes (cliente_id, conductor_id, fecha_programada, origen, destino, kms, peso, precio, estado)
                      VALUES (:cliente_id, :conductor_id, :fecha, :origen, :destino, :kms, :peso, :precio, :estado)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':cliente_id'   => $cliente_id,
                ':conductor_id' => $conductor_id,
                ':fecha'        => $fecha,
                ':origen'       => $origen,
                ':destino'      => $destino,
                ':kms'          => $kms,
                ':peso'         => $peso,
                ':precio'       => $precio,
                ':estado'       => $estado
            ]);

            http_response_code(201);
            echo json_encode([
                'ok'      => true,
                'mensaje' => 'Porte creado con éxito.',
                'id'      => $pdo->lastInsertId()
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error al crear el porte en la base de datos.']);
        }
        break;

    case 'PUT':
        if (!esGestor()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'No tienes permiso para editar portes.']);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $id   = isset($data['id']) ? $data['id'] : null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Falta el ID del porte a actualizar.']);
            exit;
        }

        $cliente_id   = !empty($data['cliente_id'])        ? $data['cliente_id']               : null;
        $conductor_id = !empty($data['conductor_id'])       ? $data['conductor_id']              : null;
        $fecha        = isset($data['fecha_programada'])    ? trim($data['fecha_programada'])    : '';
        $origen       = isset($data['origen'])              ? trim($data['origen'])              : '';
        $destino      = isset($data['destino'])             ? trim($data['destino'])             : '';
        $kms          = isset($data['kms'])                 ? $data['kms']                       : 0;
        $peso         = isset($data['peso'])                ? $data['peso']                      : 0;
        $precio       = isset($data['precio'])              ? $data['precio']                    : 0;
        $estado       = !empty($data['estado'])             ? trim($data['estado'])              : 'pendiente';

        if (empty($fecha) || empty($origen) || empty($destino)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Los campos Fecha, Origen y Destino son obligatorios.']);
            exit;
        }

        try {
            $query = "UPDATE portes SET
                        cliente_id = :cliente_id, conductor_id = :conductor_id,
                        fecha_programada = :fecha, origen = :origen, destino = :destino,
                        kms = :kms, peso = :peso, precio = :precio, estado = :estado
                      WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':cliente_id'   => $cliente_id,
                ':conductor_id' => $conductor_id,
                ':fecha'        => $fecha,
                ':origen'       => $origen,
                ':destino'      => $destino,
                ':kms'          => $kms,
                ':peso'         => $peso,
                ':precio'       => $precio,
                ':estado'       => $estado,
                ':id'           => $id
            ]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['ok' => true, 'mensaje' => 'Porte actualizado con éxito.']);
            } else {
                echo json_encode(['ok' => true, 'mensaje' => 'Sin cambios detectados.']);
            }

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error al actualizar el porte.']);
        }
        break;

    case 'DELETE':
        if (!esGestor()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'No tienes permiso para eliminar portes.']);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $id   = isset($data['id']) ? $data['id'] : (isset($_GET['id']) ? $_GET['id'] : null);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Falta el ID del porte a eliminar.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM portes WHERE id = :id");
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['ok' => true, 'mensaje' => 'Porte eliminado correctamente.']);
            } else {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'No se ha encontrado el porte para borrar.']);
            }

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error al eliminar el porte.']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método HTTP no soportado en esta ruta.']);
        break;
}
?>
