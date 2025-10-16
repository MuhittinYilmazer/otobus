<?php
session_start();
require_once 'config.php';
require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basit doğrulama
    if (empty($fullname) || empty($email) || empty($password)) {
        set_flash_message('Tüm alanları doldurmak zorunludur.', 'error');
    } else {
        // E-posta'nın zaten kayıtlı olup olmadığını kontrol et
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            set_flash_message('Bu e-posta adresi zaten kayıtlı.', 'error');
        } else {
            // Yeni kullanıcıyı ekle
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, 'User')");
            $stmt->execute([$fullname, $email, $hashed_pass]);

            set_flash_message('Başarıyla kayıt oldunuz. Şimdi giriş yapabilirsiniz.', 'success');
            redirect('login.php');
        }
    }
}

include 'header.php';
?>
<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8 mt-10">
    <h1 class="text-2xl font-bold mb-6 text-center">Kayıt Ol</h1>
    <?php display_flash_message(); ?>
    <form action="register.php" method="POST">
        <div class="mb-4">
            <label for="fullname" class="block text-gray-700 mb-2">Tam Adınız</label>
            <input type="text" id="fullname" name="fullname" required class="w-full p-2 border rounded bg-gray-50 focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mb-4">
            <label for="email" class="block text-gray-700 mb-2">E-posta Adresi</label>
            <input type="email" id="email" name="email" required class="w-full p-2 border rounded bg-gray-50 focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mb-6">
            <label for="password" class="block text-gray-700 mb-2">Şifre</label>
            <input type="password" id="password" name="password" required class="w-full p-2 border rounded bg-gray-50 focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-md hover:bg-blue-700 font-semibold">Kayıt Ol</button>
    </form>
</div>
<?php include 'footer.php'; ?>