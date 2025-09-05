<?php 

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail($email,$name,$password)
{
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Replace with your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'aaravprashantmane@gmail.com'; // Replace with your email
        $mail->Password   = 'rpfbzhzfxomebmcq'; // Replace with your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('aaravprashantmane@gmail.com', 'MBOCW CESS Portal');
        $mail->addAddress($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to MBOCW CESS Portal!';
        $mail->Body    = '
                            <meta charset="utf-8" />
                            <meta content="IE=edge" http-equiv="X-UA-Compatible" />
                            <title></title>
                            <link href="https://fonts.googleapis.com/css?family=Lato:900,900italic,700italic,700,400italic,400,300italic,300,100italic,100&amp;subset=latin,latin-ext" rel="stylesheet" type="text/css" />
                            <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
                            <style type="text/css">
                                @import url("https://fonts.googleapis.com/css?family=Lato:900,900italic,700italic,700,400italic,400,300italic,300,100italic,100&subset=latin,latin-ext" rel="stylesheet" type="text/css");         
                                @import url("https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css");
                            </style>
                            <div class="main-content-wrap" style="width: 600px; margin: 50px auto; color: rgb(0, 0, 0); font-family: Lato, sans-serif; font-size: 16px; line-height: 1.62857; -webkit-font-smoothing: antialiased;">
                                <div class="header" style="font-weight: 400;">
                                    <div class="logo" style="padding: 0px 0px 20px;text-align: center;border-bottom: 3px solid #FEAC0D;"><img src="../assets/img/logo-shield-UFSPsey2.png" class="img-circle elevation-2" alt="User Image"></div>
                                </div>
                                <div class="main-content" style="overflow: hidden; text-align: left;">
                                    <div class="section-1" style="padding: 20px 0px;">
                                        <p style="font-weight: 400; text-align: left;"></p>Hello '.$email.',</p>
                                        <p style="font-weight: 400; text-align: left; margin-top: 0px;">Welcome to <strong style="font-weight: 400;">MBOCW CESS Portal</strong>!</p>
                                        <p style="font-weight: 400; text-align: left;">You have been successfully added to the system as a Below are your Verification code:</p>
                                        <p style="font-weight: 400; text-align: left;">🔹 **Username:** <strong>'.$email.'</strong>  </p>
                                        <p style="font-weight: 400; text-align: left;">🔹 **Verification Code:** <strong>'.$password.'</strong> </p>
                                        <p style="font-weight: 400; text-align: left;"></p>You can now sign in and access the platform.
                                        <p style="font-weight: 400; text-align: left;">🔐 For your security, we recommend changing your password after your first login.</p>
                                        <p style="font-weight: 400; text-align: left;">If you have any questions or need assistance, please feel free to contact the support team.
                                        <p style="text-align: left;">Thank you, <br />Team MBOCW CESS Portal</p>
                                    </div>
                                </div>
                            </div>
                        ';
        // $mail->AltBody = 'Hello ' . htmlspecialchars($cafo_name) . ",\n\nWelcome! Your account has been created successfully. Your login details are:\nUsername: " . htmlspecialchars($cafo_email) . "\nPassword: 123456\n\nPlease log in and change your password as soon as possible for security reasons.\n\nThank you,\nThe MBOCW CESS Team";

        $mail->send();
    } catch (Exception $e) {
        // Log the error but don't stop the registration process
        error_log("Email sending failed. Mailer Error: {$mail->ErrorInfo}");
    }
}

function generatePassword($length = 8, $letters = true, $numbers = true, $symbols = true)
{
    $chars = '';
    if ($letters) {
        $chars .= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }
    if ($numbers) {
        $chars .= '0123456789';
    }
    if ($symbols) {
        $chars .= '!@#$%^&*()_+-=[]{}|;:,.<>?';
    }

    if (empty($chars)) {
        throw new Exception("No character types selected for password generation.");
    }

    $password = '';
    $charsLength = strlen($chars);

    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, $charsLength - 1)];
    }

    // Ensure at least one character of each type if specified
    if ($letters) {
        if (!preg_match('/[a-zA-Z]/', $password)) {
            $password[random_int(0, $length - 1)] = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'[random_int(0, 51)];
        }
    }
    if ($numbers) {
        if (!preg_match('/[0-9]/', $password)) {
            $password[random_int(0, $length - 1)] = '0123456789'[random_int(0, 9)];
        }
    }
    if ($symbols) {
        if (!preg_match('/[!@#$%^&*()_+\-=[\]{}|;:,.<>?]/', $password)) {
            $password[random_int(0, $length - 1)] = '!@#$%^&*()_+-=[]{}|;:,.<>?'[random_int(0, 28)];
        }
    }

    return str_shuffle($password);
}