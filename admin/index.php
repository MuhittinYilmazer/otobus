<?php
// BU SATIRLARI EKLEYİN
session_start();
require_once '../config.php'; // Bir üst dizindeki config.php'yi çağır
require_once '../helpers.php'; // Bir üst dizindeki helpers.php'yi çağır
//-------------------------

check_permission(['Admin']);
$tab = $_GET['tab'] ?? 'companies';

// Form işlemlerini buraya taşıyalım
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_company') {
        $stmt = $pdo->prepare("INSERT INTO companies (name) VALUES (?)");
        $stmt->execute([$_POST['name']]);
        set_flash_message('Firma başarıyla eklendi.', 'success');
        redirect('index.php?tab=companies');
    }
    
    if ($action === 'add_firma_admin') {
        $hashed_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role, company_id) VALUES (?, ?, ?, 'Firma Admin', ?)");
        $stmt->execute([$_POST['fullname'], $_POST['email'], $hashed_pass, $_POST['company_id']]);
        set_flash_message('Firma admini eklendi.', 'success');
        redirect('index.php?tab=admins');
    }
    
    // Diğer form işlemleri buraya eklenebilir...
}


?>
 <h1 class="text-3xl font-bold mb-6">Admin Paneli</h1>
 <?php display_flash_message(); ?>
 <div class="mb-6 border-b border-gray-200">
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
        <li class="mr-2"><a href="index.php?tab=companies" class="inline-block p-4 border-b-2 rounded-t-lg <?php echo $tab === 'companies' ? 'text-blue-600 border-blue-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300'; ?>">Firma Yönetimi</a></li>
        <li class="mr-2"><a href="index.php?tab=admins" class="inline-block p-4 border-b-2 rounded-t-lg <?php echo $tab === 'admins' ? 'text-blue-600 border-blue-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300'; ?>">Firma Admin Yönetimi</a></li>
        <li class="mr-2"><a href="index.php?tab=coupons" class="inline-block p-4 border-b-2 rounded-t-lg <?php echo $tab === 'coupons' ? 'text-blue-600 border-blue-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300'; ?>">Kupon Yönetimi</a></li>
    </ul>
</div>
<form action="index.php?tab=companies" method="POST">
    <input type="hidden" name="action" value="add_company">
    </form>