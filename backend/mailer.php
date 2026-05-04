<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/phpmailer/Exception.php';
require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Envia un correo via SMTP usando la configuracion definida en .env.
 * Devuelve true si el envio fue aceptado, false en caso de error.
 * El error se registra con error_log y nunca se muestra al usuario.
 */
function enviarEmail(string $asunto, string $cuerpo): bool
{
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = env('SMTP_HOST', 'localhost');
        $mail->SMTPAuth   = true;
        $mail->Username   = env('SMTP_USER', '');
        $mail->Password   = env('SMTP_PASS', '');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int) env('SMTP_PORT', '587');
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(
            env('SMTP_FROM_EMAIL', 'no-reply@villaloboslogistica.com'),
            env('SMTP_FROM_NAME', 'Web Villalobos Logistica')
        );
        $mail->addAddress(
            env('SMTP_TO_EMAIL', 'info@villaloboslogistica.com'),
            'Villalobos Logistica'
        );

        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo;

        return $mail->send();
    } catch (PHPMailerException $e) {
        error_log('PHPMailer: ' . $e->getMessage());
        return false;
    }
}