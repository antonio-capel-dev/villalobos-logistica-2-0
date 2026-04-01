<?php
// Permitir CORS básico si es necesario en desarrollo
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Incluimos la conexión. Como en clase, la conexión gestiona sus propios errores
require_once '../conexion.php'; 

// Array para la respuesta, como hemos visto en clase
$response = array();

// Asegurarse de que recibimos un POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response = array('ok' => false, 'error' => 'Método no permitido. Usa POST.');
    echo json_encode($response);
    exit;
}

// Recibir el JSON enviado por fetch (file_get_contents("php://input"))
$data = json_decode(file_get_contents("php://input"), true);

$email = isset($data['email']) ? trim($data['email']) : '';
$password = isset($data['password']) ? trim($data['password']) : '';

// 1. Validar que vengan los datos
if (empty($email) || empty($password)) {
    http_response_code(400); // Bad Request
    $response = array('ok' => false, 'error' => 'El email y la contraseña son obligatorios.');
    echo json_encode($response);
    exit;
}

try {
    // 2. Buscar al usuario por email usando prepared statements
    $query = "SELECT id, nombre, email, password_hash, rol FROM usuarios WHERE email = :email LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':email' => $email]);
    
    // Comprobar con rowCount() si existe, como hacíamos en los repasos
    if ($stmt->rowCount() > 0) {
        $usuario = $stmt->fetch();
        
        // 3. Verificamos la contraseña con el hash de la base de datos
        if (password_verify($password, $usuario['password_hash'])) {
            
            // 4. Iniciar variables de sesión limpias
            session_regenerate_id(true); // Evita robos de sesión
            
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['rol'] = $usuario['rol']; // Importante para proteger el panel

            // 5. Devolver JSON de éxito
            $response = array(
                'ok' => true,
                'mensaje' => 'Login correcto',
                'usuario' => array(
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'rol' => $usuario['rol']
                )
            );
            echo json_encode($response);
        } else {
            // Contraseña incorrecta
            http_response_code(401); 
            $response = array('ok' => false, 'error' => 'Credenciales incorrectas.');
            echo json_encode($response);
        }
    } else {
        // Email no existe en BD. Damos el mismo mensaje de error por seguridad.
        http_response_code(401);
        $response = array('ok' => false, 'error' => 'Credenciales incorrectas.');
        echo json_encode($response);
    }

} catch (PDOException $e) {
    http_response_code(500);
    $response = array('ok' => false, 'error' => 'Error de servidor: ' . $e->getMessage());
    echo json_encode($response);
}
?>
