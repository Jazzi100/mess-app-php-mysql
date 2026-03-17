<?php
ob_start(); // ⭐ prevents header errors

/*
|--------------------------------------------------------------------------
| Load PHPMailer
|--------------------------------------------------------------------------
*/

require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';
require __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/*
|--------------------------------------------------------------------------
| Load Report Data
|--------------------------------------------------------------------------
*/

require 'connect.php';
require 'report_data.php';

/*
|--------------------------------------------------------------------------
| Capture report table HTML
|--------------------------------------------------------------------------
*/

ob_start();
include 'report_table.php';
$emailBody = ob_get_clean();

/*
|--------------------------------------------------------------------------
| Send Email
|--------------------------------------------------------------------------
*/

$mail = new PHPMailer(true);

try {

    // SMTP SETTINGS
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'example@gmail.com'; // your email
    $mail->Password   = 'abcf efgh ijkl mnop'; // NOT normal password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Get current month and previous month
    $currentMonth = date('M'); // e.g., "Mar"
    $previousMonth = date('M', strtotime('-1 month')); // e.g., "Feb"
    $year = date('Y'); // e.g., "2026"

    // Combine in the format "Feb-Mar 2026"
    $monthRange = $previousMonth . '-' . $currentMonth . ' ' . $year;

    echo $monthRange; // Output: "Feb-Mar 2026"

    // Sender
    $mail->setFrom('example@gmail.com.com', 'Expense Manager');

    $result = mysqli_query($connection, "SELECT * FROM member where is_send_email = 1");
    while($row = mysqli_fetch_assoc($result)){
        echo $row['email'];
        $mail->addAddress($row['email']);
    }
          
    // Receiver
    

    // Email Content
    $mail->isHTML(true);
    $mail->Subject = 'Monthly Expense Summary '.$monthRange;
   //  $mail->Body    = '<b>Email sent successfully using SMTP!</b>';

     $mail->Body = "
    <html>
    <head>
        <style>
            body { font-family: Arial; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border:1px solid #ddd; padding:8px; text-align:left; }
            th { background:#333; color:white; }
        </style>
    </head>
    <body>
        <h2>Expense Report</h2>
        $emailBody
    </body>
    </html>
    ";

    $mail->send();

    // ⭐ CLEAN REDIRECT
    header('Location: report.php?message=Email sent successfully&type=success');
    exit();

} catch (Exception $e) {

    header('Location: report.php?message=' . urlencode($mail->ErrorInfo) . '&type=error');
    exit();
}

ob_end_flush();

?>