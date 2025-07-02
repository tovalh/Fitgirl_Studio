<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $email = htmlspecialchars(trim($_POST['email']));
    $telefono = htmlspecialchars(trim($_POST['telefono']));
    $clase_interes = htmlspecialchars(trim($_POST['clase_interes']));
    $mensaje = htmlspecialchars(trim($_POST['mensaje']));

    $mail = new PHPMailer(true);
    try {
        // ----- 1. CONFIGURACIÓN DEL SERVIDOR DE GMAIL -----
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = GMAIL_USER;
        $mail->Password   = GMAIL_PASSWORD;          // <-- REEMPLAZA ESTO con tu contraseña de 16 letras
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // ----- 2. CONFIGURACIÓN DEL CORREO -----
        $mail->setFrom('tu_correo_de_gmail@gmail.com', 'FitGirl Studio Bot'); // Quien envía
        $mail->addAddress('cristobalva16@gmail.com', 'Admin FitGirl');   // Quien recibe (tú mismo)
        $mail->addReplyTo($email, $nombre); // Para que al darle "responder", le respondas al cliente

        // ----- 3. CONTENIDO DEL MENSAJE -----
        $mail->isHTML(true); // El correo tendrá formato HTML
        $mail->Subject = "Nueva Solicitud de Clase de Prueba de: " . $nombre;
        $mail->Body    = "
            <html>
            <body>
                <h2 style='color: #FF1B6B;'>Nueva solicitud de clase de prueba</h2>
                <p><strong>Nombre:</strong> {$nombre}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Teléfono:</strong> {$telefono}</p>
                <p><strong>Clase de Interés:</strong> {$clase_interes}</p>
                <hr>
                <p><strong>Mensaje Adicional:</strong><br>{$mensaje}</p>
            </body>
            </html>";
        $mail->AltBody = "Nueva solicitud:\nNombre: {$nombre}\nEmail: {$email}\nTeléfono: {$telefono}\nClase: {$clase_interes}\nMensaje: {$mensaje}";

        // ----- 4. ENVIAR -----
        $mail->send();

        try {
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
            // Creamos la instancia de PDO
            $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            // Preparamos la consulta para evitar inyección SQL
            $sql = "INSERT INTO leads (nombre, email, telefono, clase_interes) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);

            // Ejecutamos la consulta pasando los datos en un array
            $stmt->execute([$nombre, $email, $telefono, $clase_interes]);
        } catch (PDOException $e) {
            error_log("Error de base de datos: " . $e->getMessage());
        }
        // Si se envía, mostramos un mensaje de éxito al usuario
        echo '<h1>¡Gracias, ' . $nombre . '!</h1>';
        echo '<p>Tu solicitud ha sido enviada. Te contactaremos muy pronto para agendar tu clase. 💪✨</p>';
        echo '<a href="../index.html" style="color: #FF1B6B;">Volver al inicio</a>';

    } catch (Exception $e) {
        // Si hay un error, le mostramos al usuario un mensaje y a nosotros el error técnico
        echo "<h1>Lo sentimos, hubo un error.</h1>";
        echo "<p>El mensaje no pudo ser enviado. Por favor, intenta más tarde.</p>";
        // La siguiente línea es para nosotros, para saber qué falló durante el desarrollo.
        // En un sitio en producción, esta línea se debería quitar o registrar en un archivo.
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
} else {
    // Si alguien intenta entrar al archivo PHP directamente, lo mandamos al inicio
    header("Location: ../index.html");
    exit();
}