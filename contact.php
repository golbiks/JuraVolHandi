<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'assets/PHPMailer-master/src/Exception.php';
require 'assets/PHPMailer-master/src/PHPMailer.php';
require 'assets/PHPMailer-master/src/SMTP.php';
require __DIR__ . '/private/config.php'; // inclusion config sécurisée

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mail = new PHPMailer(true);

    try {
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        // ✅ Forcer UTF-8
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        // Infos du formulaire
        $name = htmlspecialchars($_POST["name"]);
        $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
        $message = htmlspecialchars($_POST["message"]);
        $sendCopy = isset($_POST["copy"]);

        if (empty($name) || empty($email) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: index.html#contact?error=1");
            exit;
        }

        // Mail principal vers l’asso
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress(MAIL_TO);

        $mail->Subject = "Nouveau message du site - $name";
        $mail->Body    = "Nom: $name\nEmail: $email\n\nMessage:\n$message";

        $mail->send();

        // Copie à l'expéditeur si demandé
        if ($sendCopy) {
            $copy = new PHPMailer(true);
            $copy->isSMTP();
            $copy->Host       = SMTP_HOST;
            $copy->SMTPAuth   = true;
            $copy->Username   = SMTP_USER;
            $copy->Password   = SMTP_PASS;
            $copy->SMTPSecure = SMTP_SECURE;
            $copy->Port       = SMTP_PORT;

                   // ✅ Forcer UTF-8
            $copy->CharSet = 'UTF-8';
            $copy->Encoding = 'base64';

            $copy->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $copy->addAddress($email);

            $copy->Subject = "Copie de votre message à Jura Vol Handi";
            $copy->Body    = "Bonjour $name,\n\nVoici une copie de votre message :\n$message\n\n---\nAssociation Jura Vol Handi";

            $copy->send();
        }

        // Succès → redirection
        header("Location: index.html?success=1");
        exit;

    } catch (Exception $e) {
        // Erreur → redirection
        header("Location: index.html?error=1");
        exit;
    }
}
?>
