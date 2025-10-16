<?php
require_once 'config.php';
require_once 'helpers.php';

// post isteği ile giriş yapma
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $query = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $query->execute([$email]);
    $user = $query->fetch();

    // kullanıcı doğrulama ve session başlatma
    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];
        if ($user['role'] === 'Firma Admin') {
            $_SESSION['company_id'] = $user['company_id'];
        }
        redirect('index.php');
    } else {
        set_flash_message('Geçersiz e-posta veya şifre.', 'error');
    }
}
include 'header.php';

?>

<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8 mt-10">
    <h1 class="text-2xl font-bold mb-6 text-center">Giriş Yap</h1>
    <?php display_flash_message(); ?>
    <form action="login.php" method="POST">
        <div class="mb-4">
            <label for="email" class="block text-gray-700 mb-2">E-posta Adresi</label>
            <input type="email" id="email" name="email" required class="w-full p-2 border rounded bg-gray-50 focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mb-6">
            <label for="password" class="block text-gray-700 mb-2">Şifre</label>
            <input type="password" id="password" name="password" required class="w-full p-2 border rounded bg-gray-50 focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-md hover:bg-blue-700 font-semibold">Giriş Yap</button>
    </form>
</div>
<?php include 'footer.php'; ?>