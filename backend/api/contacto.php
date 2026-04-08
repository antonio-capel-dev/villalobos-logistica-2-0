<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../conexion.php';
require_once '../phpmailer/Exception.php';
require_once '../phpmailer/PHPMailer.php';
require_once '../phpmailer/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Metodo no soportado.']);
    exit;
}
$data = json_decode(file_get_contents("php://input"), true);
$nombre   = isset($data['nombre'])   ? trim($data['nombre'])   : '';
$servicio = isset($data['servicio']) ? trim($data['servicio']) : '';
$origen   = isset($data['origen'])   ? trim($data['origen'])   : '';
$destino  = isset($data['destino'])  ? trim($data['destino'])  : '';
$mensaje  = isset($data['mensaje'])  ? trim($data['mensaje'])  : '';

// El contacto puede llegar como email (formulario) o como campo libre del chat (telefono o email)
$contacto = isset($data['email'])    ? trim($data['email'])    : '';
$telefono = isset($data['telefono']) ? trim($data['telefono']) : '';

$esTelefono = preg_match('/^[6789]\d{8}$/', preg_replace('/\s/', '', $contacto));
$esEmail    = filter_var($contacto, FILTER_VALIDATE_EMAIL);

if ($esTelefono) {
    $telefono = $contacto;
    $email    = '';
} elseif ($esEmail) {
    $email = $contacto;
} else {
    $email = $contacto;
}

if (empty($nombre) || empty($contacto)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Faltan nombre y datos de contacto.']);
    exit;
}

$mensajeCompleto = "Servicio: $servicio\n";
if ($origen)  $mensajeCompleto .= "Origen: $origen\n";
if ($destino) $mensajeCompleto .= "Destino: $destino\n";
if ($mensaje) $mensajeCompleto .= "Detalles: $mensaje\n";
$mensajeCompleto .= "Telefono: $telefono";

try {
    $stmt = $pdo->prepare("INSERT INTO mensajes_contacto (nombre, email, mensaje) VALUES (:nombre, :email, :mensaje)");
    $stmt->execute([':nombre' => $nombre, ':email' => $email, ':mensaje' => $mensajeCompleto]);

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Username = 'a5cce6e9289318';
        $mail->Password = 'b3eecf41fef210';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 2525;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('no-reply@villalobos.local', 'Web Villalobos Logistica');
        $mail->addAddress('info@villaloboslogistica.com', 'Villalobos Logistica');
        $mail->Subject = "Nueva solicitud: $servicio - $nombre";
        $mail->Body = "NUEVA SOLICITUD DE PRESUPUESTO\n================================\n\nNombre:   $nombre\nTelefono: $telefono\nEmail:    $email\n\nServicio: $servicio\nOrigen:   " . ($origen ?: 'No indicado') . "\nDestino:  " . ($destino ?: 'No indicado') . "\n\nDetalles:\n" . ($mensaje ?: 'Sin detalles adicionales');
        $mail->send();
    } catch (Exception $e) {
        error_log("PHPMailer: " . $e->getMessage());
    }

    http_response_code(201);
    echo json_encode(['ok' => true, 'mensaje' => 'Solicitud enviada. Le contactaremos pronto.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error del servidor.']);
}
?>
