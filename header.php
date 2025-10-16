<?php
// session'ı başlat, diğer sayfalarda yazılmasına gerek yok
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otobüs Bileti Satın Alma Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-blue-600">BiletAl</a>
            <div class="flex items-center space-x-4">
                <a href="index.php" class="hover:text-blue-500">Ana Sayfa</a>

              <!-- role göre panel gösterimi -->
                <?php if (is_logged_in()): ?>
                    <?php if ($_SESSION['role'] === 'Admin'): ?>
                        <a href="/admin/" class="hover:text-blue-500">Admin Paneli</a>
                    <?php elseif ($_SESSION['role'] === 'Firma Admin'): ?>
                        <a href="/firmaadmin/" class="hover:text-blue-500">Firma Paneli</a>
                    <?php elseif ($_SESSION['role'] === 'User'): ?>
                        <a href="/my_account.php" class="hover:text-blue-500">Hesabım</a>
                    <?php endif; ?>
                    <a href="/logout.php" class="hover:text-blue-500">Çıkış Yap</a>
                <?php else: ?>
                    <a href="login.php" class="hover:text-blue-500">Giriş Yap</a>
                    <a href="register.php" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Kayıt Ol</a>
                <?php endif; ?>

            </div>
        </div>
    </nav>
    <main class="container mx-auto p-4 md:p-8">
        <?php display_flash_message(); ?>