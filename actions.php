<?php
// Gerekli dosyaları ve oturum yönetimini başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
require_once 'helpers.php';

// URL'den gelen 'action' parametresini al (DEĞİŞİKLİK BURADA)
$action = $_GET['action'] ?? '';

// ---- FİRMA ADMİN AKSİYONLARI ----

// Yeni sefer ekleme işlemi
if ($action === 'add_trip') {
    // Sadece POST metodu ile gelindiğinden emin ol
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('index.php'); // POST değilse ana sayfaya at
    }
    
    check_permission(['Firma Admin']); // Yetki kontrolü

    // Form verilerini al
    $company_id = $_SESSION['company_id'];
    $departure_location = $_POST['departure_location'] ?? '';
    $arrival_location = $_POST['arrival_location'] ?? '';
    $departure_time = $_POST['departure_time'] ?? '';
    $price = $_POST['price'] ?? 0;
    $seat_count = $_POST['seat_count'] ?? 0;

    // Veritabanına ekle
    $stmt = $pdo->prepare("INSERT INTO trips (company_id, departure_location, arrival_location, departure_time, price, seat_count) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$company_id, $departure_location, $arrival_location, $departure_time, $price, $seat_count]);

    set_flash_message('Yeni sefer başarıyla eklendi.', 'success');
    redirect('firmaadmin/index.php'); // Panele geri yönlendir
}

// Sefer silme işlemi
if ($action === 'delete_trip') {
    // Sadece POST metodu ile gelindiğinden emin ol
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('index.php');
    }
    
    check_permission(['Firma Admin']); // Yetki kontrolü

    // Form verilerini al
    $trip_id = $_POST['trip_id'] ?? 0;
    $company_id = $_SESSION['company_id'];

    // Sadece kendi firmasına ait seferi silebildiğinden emin ol
    $stmt = $pdo->prepare("DELETE FROM trips WHERE id = ? AND company_id = ?");
    $stmt->execute([$trip_id, $company_id]);

    set_flash_message('Sefer başarıyla silindi.', 'success');
    redirect('firmaadmin/index.php'); // Panele geri yönlendir
}

// Eğer hiçbir aksiyon eşleşmezse ana sayfaya yönlendir
redirect('index.php');
?>