<?php
// BU SATIRLARI EKLEYİN
session_start();
require_once '../config.php'; // Bir üst dizindeki config.php'yi çağır
require_once '../helpers.php'; // Bir üst dizindeki helpers.php'yi çağır
//-------------------------

check_permission(['Firma Admin']);
$company_id = $_SESSION['company_id'];

// Form işlemlerini buraya taşıyalım
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_trip') {
        $stmt = $pdo->prepare("INSERT INTO trips (company_id, departure_location, arrival_location, departure_time, price, seat_count) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$company_id, $_POST['departure_location'], $_POST['arrival_location'], $_POST['departure_time'], $_POST['price'], $_POST['seat_count']]);
        set_flash_message('Yeni sefer başarıyla eklendi.', 'success');
        redirect('index.php');
    }

    if ($action === 'delete_trip') {
        $stmt = $pdo->prepare("DELETE FROM trips WHERE id = ? AND company_id = ?");
        $stmt->execute([$_POST['trip_id'], $company_id]);
        set_flash_message('Sefer silindi.', 'success');
        redirect('index.php');
    }
}


$stmt = $pdo->prepare("SELECT * FROM trips WHERE company_id = ? ORDER BY departure_time DESC");
$stmt->execute([$company_id]);
$trips = $stmt->fetchAll();
?>
 <h1 class="text-3xl font-bold mb-6">Firma Yönetim Paneli</h1>
 <?php display_flash_message(); ?>
 <form action="index.php" method="POST">
    <input type="hidden" name="action" value="add_trip">
    </form>