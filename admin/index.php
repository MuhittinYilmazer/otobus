<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php';
require_once '../helpers.php';

check_permission(['Admin']);

$action = $_GET['action'] ?? '';
$tab = $_GET['tab'] ?? 'companies';


// post işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // firma ekleme
    if ($action === 'add_company') {
        $name = $_POST['name'] ?? '';
        if (!empty($name)) {
            $query = $pdo->prepare("INSERT INTO companies (name) VALUES (?)");
            $query->execute([$name]);
            set_flash_message('Firma başarıyla eklendi.', 'success');
        }
        redirect('index.php?tab=companies');
    }
    
    // firma silme
    if ($action === 'delete_company') {
        $company_id = $_POST['company_id'] ?? 0;
        if ($company_id > 0) {
            $pdo->beginTransaction();
            try {
                // firmaya ait seferlerin tüm biletlerini sil
                $stmt = $pdo->prepare("DELETE FROM bookings WHERE trip_id IN (SELECT id FROM trips WHERE company_id = ?)");
                $stmt->execute([$company_id]);

                // firmaya ait tüm seferleri sil
                $stmt = $pdo->prepare("DELETE FROM trips WHERE company_id = ?");
                $stmt->execute([$company_id]);

                // firmaya ait tüm adminleri sil
                $stmt = $pdo->prepare("DELETE FROM users WHERE company_id = ? AND role = 'Firma Admin'");
                $stmt->execute([$company_id]);
                
                // firmayı sil
                $stmt = $pdo->prepare("DELETE FROM companies WHERE id = ?");
                $stmt->execute([$company_id]);

                $pdo->commit();
                set_flash_message('Firma silindi', 'success');
            } catch (Exception $e) {
                $pdo->rollBack();
                set_flash_message('Firma silinirken bir hata oluştu: ' . $e->getMessage(), 'error');
            }
        }
        redirect('index.php?tab=companies');
    }

    // firma admini ekleme
    if ($action === 'add_firma_admin') {
        $fullname = $_POST['fullname'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $company_id = $_POST['company_id'] ?? null;

        if ($fullname && $email && $password && $company_id) {
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role, company_id) VALUES (?, ?, ?, 'Firma Admin', ?)");
            $stmt->execute([$fullname, $email, $password, $company_id]);
            set_flash_message('Firma Admin başarıyla eklendi.', 'success');
        } else {
            set_flash_message('Lütfen tüm alanları doldurun.', 'error');
        }
        redirect('index.php?tab=admins');
    }

    // firma admini silme
    if ($action === 'delete_firma_admin') {
        $admin_id = $_POST['admin_id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'Firma Admin'");
        $stmt->execute([$admin_id]);
        set_flash_message('Firma Admin başarıyla silindi.', 'success');
        redirect('index.php?tab=admins');
    }

    // kupon ekleme
    if ($action === 'add_coupon') {
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
        redirect('index.php?tab=coupons');
    }

    // kupon silme
    if ($action === 'delete_coupon') {
        $coupon_id = $_POST['coupon_id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->execute([$coupon_id]);
        set_flash_message('Kupon başarıyla silindi.', 'success');
        redirect('index.php?tab=coupons');
    }
}

include '../header.php';
?>
 <h1 class="text-3xl font-bold mb-6">Admin Paneli</h1>
 <div class="mb-6 border-b border-gray-200">
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
        <li class="mr-2"><a href="index.php?tab=companies" class="inline-block p-4 border-b-2 rounded-t-lg <?php echo $tab === 'companies' ? 'text-blue-600 border-blue-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300'; ?>">Firma Yönetimi</a></li>
        <li class="mr-2"><a href="index.php?tab=admins" class="inline-block p-4 border-b-2 rounded-t-lg <?php echo $tab === 'admins' ? 'text-blue-600 border-blue-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300'; ?>">Firma Admin Yönetimi</a></li>
        <li class="mr-2"><a href="index.php?tab=coupons" class="inline-block p-4 border-b-2 rounded-t-lg <?php echo $tab === 'coupons' ? 'text-blue-600 border-blue-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300'; ?>">Kupon Yönetimi</a></li>
    </ul>
</div>

<?php if ($tab === 'companies'): 
    // firmaları çek
    $companies = $pdo->query("SELECT * FROM companies ORDER BY name")->fetchAll();
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <div class="md:col-span-2">
         <h2 class="text-2xl font-bold mb-4">Mevcut Firmalar</h2>
         <div class="bg-white rounded-lg shadow-md overflow-x-auto"><table class="w-full text-left"><thead class="bg-gray-50"><tr><th class="p-3">ID</th><th class="p-3">Firma Adı</th><th class="p-3">İşlem</th></tr></thead>
           <tbody>
               <?php foreach($companies as $company): ?>
               <tr class="border-t">
                   <td class="p-3"><?php echo $company['id']; ?></td>
                   <td class="p-3"><?php echo $company['name']; ?></td>
                   <td class="p-3">
                       <form action="index.php?tab=companies&action=delete_company" method="POST" onsubmit="return confirm('Bu firmayı silmek istediğinize emin misiniz? Bu firmaya ait tüm seferler, biletler ve firma adminleri de silinecektir!');">
                           <input type="hidden" name="company_id" value="<?php echo $company['id']; ?>">
                           <button type="submit" class="text-red-500 hover:underline">Sil</button>
                       </form>
                   </td>
               </tr>
               <?php endforeach; ?>
           </tbody></table></div>
    </div>
    <div>
         <h2 class="text-2xl font-bold mb-4">Yeni Firma Ekle</h2>
         <div class="bg-white rounded-lg shadow-md p-6">
            <form action="index.php?tab=companies&action=add_company" method="POST">
                <div class="mb-3"><label class="block">Firma Adı</label><input type="text" name="name" required class="w-full p-2 border rounded bg-gray-50"></div>
                <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Ekle</button>
            </form>
         </div>
    </div>
</div>
<?php elseif ($tab === 'admins'): 
    // firma adminlerini çek
     $firma_admins = $pdo->query("SELECT u.*, c.name as company_name FROM users u JOIN companies c ON u.company_id = c.id WHERE u.role = 'Firma Admin' ORDER BY u.fullname")->fetchAll();
     $companies = $pdo->query("SELECT * FROM companies ORDER BY name")->fetchAll();
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <div class="md:col-span-2">
         <h2 class="text-2xl font-bold mb-4">Mevcut Firma Adminleri</h2>
         <div class="bg-white rounded-lg shadow-md overflow-x-auto"><table class="w-full text-left"><thead class="bg-gray-50"><tr><th class="p-3">Ad</th><th class="p-3">E-posta</th><th class="p-3">Şifre</th><th class="p-3">Atanan Firma</th><th class="p-3">İşlem</th></tr></thead>
           <tbody>
               <?php foreach($firma_admins as $admin): ?>
               <tr class="border-t"><td class="p-3"><?php echo $admin['fullname']; ?></td><td class="p-3"><?php echo $admin['email']; ?></td><td class="p-3 font-mono"><?php echo $admin['password']; ?></td><td class="p-3"><?php echo $admin['company_name']; ?></td>
               <td class="p-3">
                   <form action="index.php?tab=admins&action=delete_firma_admin" method="POST" onsubmit="return confirm('Bu admini silmek istediğinize emin misiniz?');">
                       <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                       <button type="submit" class="text-red-500 hover:underline">Sil</button>
                   </form>
               </td></tr>
               <?php endforeach; ?>
           </tbody></table></div>
    </div>
    <div>
         <h2 class="text-2xl font-bold mb-4">Yeni Firma Admin Ekle</h2>
          <div class="bg-white rounded-lg shadow-md p-6">
            <form action="index.php?tab=admins&action=add_firma_admin" method="POST">
                <div class="mb-3"><label class="block">Tam Ad</label><input type="text" name="fullname" required class="w-full p-2 border rounded bg-gray-50"></div>
                <div class="mb-3"><label class="block">E-posta</label><input type="email" name="email" required class="w-full p-2 border rounded bg-gray-50"></div>
                <div class="mb-3"><label class="block">Şifre</label><input type="password" name="password" required class="w-full p-2 border rounded bg-gray-50"></div>
                <div class="mb-3"><label class="block">Firma</label><select name="company_id" required class="w-full p-2 border rounded bg-gray-50"><?php foreach($companies as $company): ?><option value="<?php echo $company['id']; ?>"><?php echo htmlspecialchars($company['name']); ?></option><?php endforeach; ?></select></div>
                <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Ekle</button>
            </form>
         </div>
    </div>
</div>
<?php elseif ($tab === 'coupons'):
    // kuponları çek
    $coupons = $pdo->query("SELECT co.*, c.name as company_name FROM coupons co LEFT JOIN companies c ON co.company_id = c.id ORDER BY co.expiry_date DESC")->fetchAll();
    $companies = $pdo->query("SELECT * FROM companies ORDER BY name")->fetchAll();
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <div class="md:col-span-2">
         <h2 class="text-2xl font-bold mb-4">Mevcut Kuponlar</h2>
         <div class="bg-white rounded-lg shadow-md overflow-x-auto"><table class="w-full text-left"><thead class="bg-gray-50"><tr><th class="p-3">Kod</th><th class="p-3">İndirim</th><th class="p-3">Kalan Hak</th><th class="p-3">Son Tarih</th><th class="p-3">Firma</th><th class="p-3">İşlem</th></tr></thead>
           <tbody>
               <?php foreach($coupons as $coupon): ?>
               <tr class="border-t"><td class="p-3 font-mono"><?php echo $coupon['code']; ?></td><td class="p-3"><?php echo ($coupon['discount_rate'] * 100); ?>%</td><td class="p-3"><?php echo $coupon['usage_limit']; ?></td><td class="p-3"><?php echo date('d M Y', strtotime($coupon['expiry_date'])); ?></td><td class="p-3"><?php echo $coupon['company_name'] ?? 'Tümü'; ?></td>
               <td class="p-3">
                   <form action="index.php?tab=coupons&action=delete_coupon" method="POST" onsubmit="return confirm('Bu kuponu silmek istediğinize emin misiniz?');">
                       <input type="hidden" name="coupon_id" value="<?php echo $coupon['id']; ?>">
                       <button type="submit" class="text-red-500 hover:underline">Sil</button>
                   </form>
               </td></tr>
               <?php endforeach; ?>
           </tbody></table></div>
    </div>
    <div>
         <h2 class="text-2xl font-bold mb-4">Yeni Kupon Ekle</h2>
          <div class="bg-white rounded-lg shadow-md p-6">
            <form action="index.php?tab=coupons&action=add_coupon" method="POST">
                <div class="mb-3"><label class="block">Kupon Kodu</label><input type="text" name="code" required class="w-full p-2 border rounded bg-gray-50"></div>
                <div class="mb-3"><label class="block">İndirim Oranı (örn: 0.10 for 10%)</label><input type="number" step="0.01" min="0.01" max="1" name="discount_rate" required class="w-full p-2 border rounded bg-gray-50"></div>
                <div class="mb-3"><label class="block">Kullanım Limiti</label><input type="number" min="1" name="usage_limit" required class="w-full p-2 border rounded bg-gray-50"></div>
                <div class="mb-3"><label class="block">Son Geçerlilik Tarihi</label><input type="date" name="expiry_date" required class="w-full p-2 border rounded bg-gray-50"></div>
                <div class="mb-3"><label class="block">Firma (Boş bırakırsanız hepsi için geçerli olur)</label><select name="company_id" class="w-full p-2 border rounded bg-gray-50"><option value="">Tüm Firmalar</option><?php foreach($companies as $company): ?><option value="<?php echo $company['id']; ?>"><?php echo $company['name']; ?></option><?php endforeach; ?></select></div>
                <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Ekle</button>
            </form>
         </div>
    </div>
</div>
<?php endif; ?>

<?php
include '../footer.php';
?>