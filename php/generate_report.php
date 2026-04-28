<?php
session_start();
require_once 'db.php';
require('fpdf/fpdf.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // make sure PHPMailer is loaded

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid access. Please submit the form from Reports page.");
}

// Get date range
$startDate = $_POST['start_date'] ?? date('Y-m-01');
$endDate = ($_POST['end_date'] ?? date('Y-m-d')) . " 23:59:59";

// Fetch orders
$stmt = $conn->prepare("
    SELECT o.id, o.order_number, o.total, o.subtotal, o.tax, o.shipping, o.delivery_address, o.created_at, u.full_name, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.created_at BETWEEN ? AND ?
    ORDER BY o.created_at ASC
");
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

if (empty($orders)) die("No orders found.");

// Loop orders
foreach ($orders as $order) {
    // Generate PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10,"IslandLink Invoice",0,1,'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,8,"Order #: ".$order['order_number'],0,1);
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(0,8,"Customer: ".$order['full_name'],0,1);
    $pdf->Cell(0,8,"Email: ".$order['email'],0,1);
    $pdf->Cell(0,8,"Delivery Address: ".$order['delivery_address'],0,1);
    $pdf->Cell(0,8,"Date: ".$order['created_at'],0,1);
    $pdf->Ln(5);

    // Items table
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(80,8,"Product",1);
    $pdf->Cell(30,8,"Qty",1);
    $pdf->Cell(40,8,"Unit Price",1);
    $pdf->Cell(40,8,"Total",1);
    $pdf->Ln();

    $stmtItems = $conn->prepare("
        SELECT p.name, oi.quantity, p.price
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmtItems->bind_param("i", $order['id']);
    $stmtItems->execute();
    $itemsRes = $stmtItems->get_result();
    $items = $itemsRes->fetch_all(MYSQLI_ASSOC);

    $pdf->SetFont('Arial','',12);
    foreach ($items as $item) {
        $pdf->Cell(80,8,$item['name'],1);
        $pdf->Cell(30,8,$item['quantity'],1);
        $pdf->Cell(40,8,'$'.number_format($item['price'],2),1);
        $pdf->Cell(40,8,'$'.number_format($item['quantity']*$item['price'],2),1);
        $pdf->Ln();
    }

    // Totals
    $pdf->Ln(2);
    $pdf->Cell(150,8,'Subtotal',0,0,'R');
    $pdf->Cell(40,8,'$'.number_format($order['subtotal'],2),1,1,'R');
    $pdf->Cell(150,8,'Tax',0,0,'R');
    $pdf->Cell(40,8,'$'.number_format($order['tax'],2),1,1,'R');
    $pdf->Cell(150,8,'Shipping',0,0,'R');
    $pdf->Cell(40,8,'$'.number_format($order['shipping'],2),1,1,'R');
    $pdf->Cell(150,8,'Total',0,0,'R');
    $pdf->Cell(40,8,'$'.number_format($order['total'],2),1,1,'R');
    $pdf->Ln(10);

    // Save PDF temporarily
    $pdfFile = __DIR__ . '/temp/invoice_'.$order['order_number'].'_'.time().'.pdf';
    $pdf->Output('F', $pdfFile);

    // Send email using PHPMailer
    $mail->isSMTP();
$mail->Host = 'smtp.gmail.com'; // or your mail server
$mail->SMTPAuth = true;
$mail->Username = 'your_email@gmail.com'; 
$mail->Password = 'your_app_password'; // NOT normal password
$mail->SMTPSecure = 'tls';
$mail->Port = 587;
try {
    // Sender info
    $mail->setFrom('no-reply@islandlink.com', 'IslandLink');

    // Recipient
    $mail->addAddress($order['email'], $order['full_name']);

    // Email subject and body
    $mail->Subject = "Your Invoice from IslandLink";
    $mail->Body = "Hello ".$order['full_name'].",\n\nPlease find your invoice attached.\n\nThanks, IslandLink Team";

    // Attach the PDF
    $mail->addAttachment($pdfFile);

    // Send the email
    $mail->send();

} catch (Exception $e) {
    // If sending fails, you can log it
    error_log("Mailer Error: " . $mail->ErrorInfo);
}

// Delete PDF after sending
unlink($pdfFile);

}

// Redirect back
header("Location: ../php/reports.php?sent=success");
exit();
