<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once '../conexion.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido. Usa POST.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$email    = isset($data['email'])    ? trim($data['email'])    : '';
$password = isset($data['password']) ? trim($data['password']) : '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'El email y la contraseña son obligatorios.']);
    exit;
}

try {
    $query = "SELECT id, nombre, email, password_hash, rol FROM usuarios WHERE email = :email LIMIT 1";
    $stmt  = $pdo->prepare($query);
    $stmt->execute([':email' => $email]);

    if ($stmt->rowCount() > 0) {
        $usuario = $stmt->fetch();

        if (password_verify($password, $usuario['password_hash'])) {
            // Regenerar ID de sesión para evitar fijación de sesión
            session_regenerate_id(true);

            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['nombre']  = $usuario['nombre'];
            $_SESSION['email']   = $usuario['email'];
            $_SESSION['rol']     = $usuario['rol'];

            $response = array(
                'ok'      => true,
                'mensaje' => 'Login correcto',
                'usuario' => array(
                    'id'     => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'rol'    => $usuario['rol']
                )
            );
            echo json_encode($response);
        } else {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Credenciales incorrectas.']);
        }
    } else {
        // Mismo mensaje para email no encontrado — no revelar qué campo falla
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Credenciales incorrectas.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error de servidor.']);
}
?>
