<?php
session_start();
require_once 'config.php';
require_once 'helpers.php';

// Veritabanından firmaları çek
$companies = $pdo->query("SELECT * FROM companies ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'User';
    $company_id = !empty($_POST['company_id']) ? $_POST['company_id'] : null;

    // Basit doğrulama
    if (empty($fullname) || empty($email) || empty($password)) {
        set_flash_message('Tüm alanları doldurmak zorunludur.', 'error');
    } elseif ($role === 'Firma Admin' && empty($company_id)) {
        set_flash_message('Firma Admin rolü için bir firma seçmelisiniz.', 'error');
    } else {
        // E-posta'nın zaten kayıtlı olup olmadığını kontrol et
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            set_flash_message('Bu e-posta adresi zaten kayıtlı.', 'error');
        } else {
            // Yeni kullanıcıyı ekle
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role, company_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$fullname, $email, $hashed_pass, $role, $company_id]);

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
        <div class="mb-4">
            <label for="password" class="block text-gray-700 mb-2">Şifre</label>
            <input type="password" id="password" name="password" required class="w-full p-2 border rounded bg-gray-50 focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mb-4">
            <label for="role" class="block text-gray-700 mb-2">Kullanıcı Rolü</label>
            <select id="role" name="role" class="w-full p-2 border rounded bg-gray-50 focus:ring-2 focus:ring-blue-500">
                <option value="User" selected>User (Yolcu)</option>
                <option value="Firma Admin">Firma Admin</option>
                <option value="Admin">Admin</option>
            </select>
        </div>
        <div id="company-selection" class="mb-6 hidden">
            <label for="company_id" class="block text-gray-700 mb-2">Firma</label>
            <select id="company_id" name="company_id" class="w-full p-2 border rounded bg-gray-50 focus:ring-2 focus:ring-blue-500">
                <option value="">Firma Seçin</option>
                <?php foreach ($companies as $company): ?>
                    <option value="<?php echo $company['id']; ?>"><?php echo htmlspecialchars($company['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-md hover:bg-blue-700 font-semibold">Kayıt Ol</button>
    </form>
</div>

<script>
    document.getElementById('role').addEventListener('change', function() {
        const companySelection = document.getElementById('company-selection');
        if (this.value === 'Firma Admin') {
            companySelection.classList.remove('hidden');
        } else {
            companySelection.classList.add('hidden');
        }
    });
</script>

<?php include 'footer.php'; ?>