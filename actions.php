<?php
// Gerekli dosyaları ve oturum yönetimini başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
require_once 'helpers.php';

// URL'den gelen 'action' parametresini al
$action = $_GET['action'] ?? '';

// Sadece POST metodu ile gelen istekleri kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

// ---- FİRMA ADMİN AKSİYONLARI ----
if ($action === 'add_trip') {
    check_permission(['Firma Admin']);
    $company_id = $_SESSION['company_id'];
    $departure_location = $_POST['departure_location'] ?? '';
    $arrival_location = $_POST['arrival_location'] ?? '';
    $departure_time = $_POST['departure_time'] ?? '';
    $price = $_POST['price'] ?? 0;
    $seat_count = $_POST['seat_count'] ?? 0;
    $stmt = $pdo->prepare("INSERT INTO trips (company_id, departure_location, arrival_location, departure_time, price, seat_count) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$company_id, $departure_location, $arrival_location, $departure_time, $price, $seat_count]);
    set_flash_message('Yeni sefer başarıyla eklendi.', 'success');
    redirect('firmaadmin/index.php');
}

if ($action === 'delete_trip') {
    check_permission(['Firma Admin']);
    $trip_id = $_POST['trip_id'] ?? 0;
    $company_id = $_SESSION['company_id'];
    $stmt = $pdo->prepare("DELETE FROM trips WHERE id = ? AND company_id = ?");
    $stmt->execute([$trip_id, $company_id]);
    set_flash_message('Sefer başarıyla silindi.', 'success');
    redirect('firmaadmin/index.php');
}

// ---- SİTE ADMİN AKSİYONLARI (YENİ EKLENEN KISIM) ----

if ($action === 'add_company') {
    check_permission(['Admin']);
    $name = $_POST['name'] ?? '';
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO companies (name) VALUES (?)");
        $stmt->execute([$name]);
        set_flash_message('Firma başarıyla eklendi.', 'success');
    }
    redirect('admin/index.php?tab=companies');
}

if ($action === 'add_firma_admin') {
    check_permission(['Admin']);
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $company_id = $_POST['company_id'] ?? null;

    if ($fullname && $email && $password && $company_id) {
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role, company_id) VALUES (?, ?, ?, 'Firma Admin', ?)");
        $stmt->execute([$fullname, $email, $hashed_pass, $company_id]);
        set_flash_message('Firma Admin başarıyla eklendi.', 'success');
    } else {
        set_flash_message('Lütfen tüm alanları doldurun.', 'error');
    }
    redirect('admin/index.php?tab=admins');
}

if ($action === 'add_coupon') {
    check_permission(['Admin']);
    $code = $_POST['code'] ?? '';
    $discount_rate = $_POST['discount_rate'] ?? 0;
    $usage_limit = $_POST['usage_limit'] ?? 0;
    $expiry_date = $_POST['expiry_date'] ?? '';
    $company_id = !empty($_POST['company_id']) ? $_POST['company_id'] : null;

    if ($code && $discount_rate && $usage_limit && $expiry_date) {
        $stmt = $pdo->prepare("INSERT INTO coupons (code, discount_rate, usage_limit, expiry_date, company_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$code, $discount_rate, $usage_limit, $expiry_date, $company_id]);
        set_flash_message('Kupon başarıyla eklendi.', 'success');
    } else {
        set_flash_message('Lütfen tüm alanları doldurun.', 'error');
    }
    redirect('admin/index.php?tab=coupons');
}

// Eğer hiçbir aksiyon eşleşmezse ana sayfaya yönlendir
redirect('index.php');
?>