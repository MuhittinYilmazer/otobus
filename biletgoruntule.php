<?php
session_start();
require_once 'config.php';
require_once 'helpers.php';
check_permission(['User']);

$booking_id = $_GET['booking_id'] ?? null;
$user_id = $_SESSION['user_id'];

if(!$booking_id) die('Geçersiz bilet.');

$stmt = $pdo->prepare("
    SELECT 
        b.id as booking_id, b.seat_number, b.price_paid,
        t.departure_location, t.arrival_location, t.departure_time,
        c.name as company_name,
        u.fullname as passenger_name
    FROM bookings b
    JOIN trips t ON b.trip_id = t.id
    JOIN companies c ON t.company_id = c.id
    JOIN users u ON b.user_id = u.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $user_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die('Bilet bulunamadı veya bu bileti görüntüleme yetkiniz yok.');
}

// PDF kütüphanesi entegrasyonu burada yapılabilir.
// Şimdilik basit HTML çıktısı veriyoruz.
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Bilet Detayı - <?php echo $ticket['booking_id']; ?></title>
    <style>
        body { font-family: sans-serif; margin: 40px; }
        .ticket { border: 2px dashed #ccc; padding: 20px; }
        h1 { border-bottom: 1px solid #eee; padding-bottom: 10px; }
        p { line-height: 1.6; }
    </style>
</head>
<body>
    <div class="ticket">
        <h1>BiletAl - Yolcu Bileti</h1>
        <p><strong>Firma:</strong> <?php echo htmlspecialchars($ticket['company_name']); ?></p>
        <p><strong>Yolcu:</strong> <?php echo htmlspecialchars($ticket['passenger_name']); ?></p>
        <p><strong>Güzergah:</strong> <?php echo htmlspecialchars($ticket['departure_location']); ?> &rarr; <?php echo htmlspecialchars($ticket['arrival_location']); ?></p>
        <p><strong>Kalkış Zamanı:</strong> <?php echo date('d M Y, H:i', strtotime($ticket['departure_time'])); ?></p>
        <p><strong>Koltuk No:</strong> <?php echo $ticket['seat_number']; ?></p>
        <p><strong>Ödenen Tutar:</strong> <?php echo number_format($ticket['price_paid'], 2); ?> TL</p>
    </div>
</body>
</html>