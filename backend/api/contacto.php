<?php
header("Content-Type: application/json; charset=UTF-8");

require_once '../conexion.php';

// Cargamos los 3 ficheros de PHPMailer (descargados a mano en backend/phpmailer/)
require_once '../phpmailer/Exception.php';
require_once '../phpmailer/PHPMailer.php';
require_once '../phpmailer/SMTP.php';

// Usamos el namespace de PHPMailer para poder escribir "new PHPMailer" sin el nombre largo
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$response = array();

// Solo aceptamos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no soportado.']);
    exit;
}

// Recibimos el JSON del fetch
$data = json_decode(file_get_contents("php://input"), true);

$nombre  = isset($data['nombre'])  ? trim($data['nombre'])  : '';
$email   = isset($data['email'])   ? trim($data['email'])   : '';
$mensaje = isset($data['mensaje']) ? trim($data['mensaje']) : '';

// Validación en servidor
if (empty($nombre) || empty($email) || empty($mensaje)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Todos los campos son obligatorios.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'El formato del correo electrónico es inválido.']);
    exit;
}

try {
    // PASO 1: Guardar en la base de datos (esto es lo prioritario)
    $query = "INSERT INTO mensajes_contacto (nombre, email, mensaje) VALUES (:nombre, :email, :mensaje)";
    $stmt  = $pdo->prepare($query);
    $stmt->execute([':nombre' => $nombre, ':email' => $email, ':mensaje' => $mensaje]);

    // PASO 2: Intentar enviar email con PHPMailer
    // Si falla el email, el mensaje ya está guardado en BD — no se pierde el lead
    $mailEnviado = false;

    try {
        $mail = new PHPMailer(true); // true = lanza excepciones si algo falla

        // --- CONFIGURACIÓN SMTP ---
        $mail->isSMTP();
        $mail->Host       = 'sandbox.smtp.mailtrap.io'; // Mailtrap: servidor de pruebas
        $mail->SMTPAuth   = true;
        $mail->Username   = 'a5cce6e9289318';  // ← pega aquí tu Username de Mailtrap
        $mail->Password   = 'b3eecf41fef210';   // ← pega aquí tu Password de Mailtrap
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Mailtrap acepta TLS
        $mail->Port       = 2525; // Puerto de Mailtrap
        $mail->CharSet    = 'UTF-8';

        // --- REMITENTE Y DESTINATARIO ---
        $mail->setFrom('no-reply@villalobos.local', 'Web Villalobos Logística');
        $mail->addAddress('info@villaloboslogistica.com', 'Villalobos Logística'); // ← correo destino

        // --- CONTENIDO DEL EMAIL ---
        $mail->Subject = 'Nuevo mensaje de contacto desde la web';
        $mail->Body    =
            "Has recibido un nuevo mensaje desde el formulario web:\n\n" .
            "Nombre:  " . $nombre  . "\n" .
            "Email:   " . $email   . "\n" .
            "Mensaje: " . $mensaje . "\n";

        $mail->send();
        $mailEnviado = true;

    } catch (Exception $e) {
        // El email falló pero el lead está guardado en BD — no bloqueamos al usuario
        error_log("PHPMailer error: " . $e->getMessage());
    }

    // Respondemos éxito al usuario (la BD siempre guarda, email es bonus)
    http_response_code(201);
    echo json_encode(['ok' => true, 'mensaje' => 'Formulario enviado con éxito. Le contactaremos pronto.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo enviar el mensaje por un fallo del sistema.']);
}
?>
