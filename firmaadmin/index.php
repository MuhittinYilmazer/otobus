<?php
// Gerekli dosyaları ve oturumu başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php';
require_once '../helpers.php';

check_permission(['Firma Admin']);

$company_id = $_SESSION['company_id'];
$action = $_GET['action'] ?? '';
$tab = $_GET['tab'] ?? 'trips';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sefer Ekleme
    if ($action === 'add_trip') {
        $departure_location = $_POST['departure_location'] ?? '';
        $arrival_location = $_POST['arrival_location'] ?? '';
        $departure_time = $_POST['departure_time'] ?? '';
        $price = $_POST['price'] ?? 0;
        $seat_count = $_POST['seat_count'] ?? 0;
        $stmt = $pdo->prepare("INSERT INTO trips (company_id, departure_location, arrival_location, departure_time, price, seat_count) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$company_id, $departure_location, $arrival_location, $departure_time, $price, $seat_count]);
        set_flash_message('Yeni sefer başarıyla eklendi.', 'success');
        redirect('index.php?tab=trips');
    }
    // Sefer Silme
    if ($action === 'delete_trip') {
        $trip_id = $_POST['trip_id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM trips WHERE id = ? AND company_id = ?");
        $stmt->execute([$trip_id, $company_id]);
        set_flash_message('Sefer başarıyla silindi.', 'success');
        redirect('index.php?tab=trips');
    }
    // Kupon Ekleme
    if ($action === 'add_coupon') {
        $code = $_POST['code'] ?? '';
        $discount_rate = $_POST['discount_rate'] ?? 0;
        $usage_limit = $_POST['usage_limit'] ?? 0;
        $expiry_date = $_POST['expiry_date'] ?? '';
        if ($code && $discount_rate && $usage_limit && $expiry_date) {
            $stmt = $pdo->prepare("INSERT INTO coupons (code, discount_rate, usage_limit, expiry_date, company_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$code, $discount_rate, $usage_limit, $expiry_date, $company_id]);
            set_flash_message('Kupon başarıyla eklendi.', 'success');
        } else {
            set_flash_message('Lütfen tüm alanları doldurun.', 'error');
        }
        redirect('index.php?tab=coupons');
    }
    // Kupon Silme
    if ($action === 'delete_coupon') {
        $coupon_id = $_POST['coupon_id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ? AND company_id = ?");
        $stmt->execute([$coupon_id, $company_id]);
        set_flash_message('Kupon başarıyla silindi.', 'success');
        redirect('index.php?tab=coupons');
    }
    // Firma Admini tarafından bilet iptali
    if ($action === 'cancel_booking') {
        $booking_id = $_POST['booking_id'] ?? 0;
        $stmt = $pdo->prepare("SELECT b.id, b.user_id, b.price_paid, t.company_id FROM bookings b JOIN trips t ON b.trip_id = t.id WHERE b.id = ?");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch();
        if ($booking && $booking['company_id'] == $company_id) {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$booking['price_paid'], $booking['user_id']]);
                $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
                $stmt->execute([$booking['id']]);
                $pdo->commit();
                set_flash_message('Bilet başarıyla iptal edildi ve ücret iadesi yapıldı.', 'success');
            } catch (Exception $e) {
                $pdo->rollBack();
                set_flash_message('İptal işlemi sırasında bir hata oluştu.', 'error');
            }
        } else {
            set_flash_message('Bu bileti iptal etme yetkiniz yok.', 'error');
        }
        redirect('index.php?tab=bookings');
    }
}

include '../header.php';
?>

<h1 class="text-3xl font-bold mb-6">Firma Yönetim Paneli</h1>

<div class="mb-6 border-b border-gray-200">
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
        <li class="mr-2"><a href="index.php?tab=trips" class="inline-block p-4 border-b-2 rounded-t-lg <?php echo $tab === 'trips' ? 'text-blue-600 border-blue-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300'; ?>">Sefer Yönetimi</a></li>
        <li class="mr-2"><a href="index.php?tab=bookings" class="inline-block p-4 border-b-2 rounded-t-lg <?php echo $tab === 'bookings' ? 'text-blue-600 border-blue-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300'; ?>">Bilet Yönetimi</a></li>
        <li class="mr-2"><a href="index.php?tab=coupons" class="inline-block p-4 border-b-2 rounded-t-lg <?php echo $tab === 'coupons' ? 'text-blue-600 border-blue-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300'; ?>">Kupon Yönetimi</a></li>
    </ul>
</div>

<?php if ($tab === 'trips'): 
    $stmt = $pdo->prepare("SELECT * FROM trips WHERE company_id = ? ORDER BY departure_time DESC");
    $stmt->execute([$company_id]);
    $trips = $stmt->fetchAll();
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <div class="md:col-span-2">
        <h2 class="text-2xl font-bold mb-4">Seferleriniz</h2>
        <div class="bg-white rounded-lg shadow-md overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50"><tr><th class="p-3">Güzergah</th><th class="p-3">Kalkış</th><th class="p-3">Fiyat</th><th class="p-3">İşlem</th></tr></thead>
                <tbody>
                <?php foreach($trips as $trip): ?>
                    <tr class="border-t">
                        <td class="p-3"><?php echo htmlspecialchars($trip['departure_location']); ?> - <?php echo htmlspecialchars($trip['arrival_location']); ?></td>
                        <td class="p-3"><?php echo date('d M Y, H:i', strtotime($trip['departure_time'])); ?></td>
                        <td class="p-3"><?php echo $trip['price']; ?> TL</td>
                        <td class="p-3">
                            <form action="index.php?tab=trips&action=delete_trip" method="POST" onsubmit="return confirm('Bu seferi silmek istediğinize emin misiniz?');">
                                <input type="hidden" name="trip_id" value="<?php echo $trip['id']; ?>"><button type="submit" class="text-red-500 hover:underline">Sil</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div>
        <h2 class="text-2xl font-bold mb-4">Yeni Sefer Ekle</h2>
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="index.php?tab=trips&action=add_trip" method="POST">
                <div class="mb-3"><label class="block">Kalkış Yeri</label><input type="text" name="departure_location" required class="w-full p-2 border rounded bg-gray-50"></div>
                <div class="mb-3"><label class="block">Varış Yeri</label><input type="text" name="arrival_location" required class="w-full p-2 border rounded bg-gray-50"></div>
                <div class="mb-3"><label class="block">Kalkış Zamanı</label><input type="datetime-local" name="departure_time" required class="w-full p-2 border rounded bg-gray-50"></div>
                <div class="mb-3"><label class="block">Fiyat</label><input type="number" step="0.01" name="price" required class="w-full p-2 border rounded bg-gray-50"></div>
                <div class="mb-3"><label class="block">Koltuk Sayısı</label><input type="number" name="seat_count" required class="w-full p-2 border rounded bg-gray-50"></div>
                <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Ekle</button>
            </form>
        </div>
    </div>
</div>

<?php elseif ($tab === 'bookings'): 
    $stmt = $pdo->prepare("SELECT b.*, t.departure_location, t.arrival_location, t.departure_time, u.fullname FROM bookings b JOIN trips t ON b.trip_id = t.id JOIN users u ON b.user_id = u.id WHERE t.company_id = ? ORDER BY t.departure_time DESC");
    $stmt->execute([$company_id]);
    $bookings = $stmt->fetchAll();
?>
<h2 class="text-2xl font-bold mb-4">Satılan Biletler</h2>
<div class="bg-white rounded-lg shadow-md overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-gray-50"><tr><th class="p-3">Yolcu</th><th class="p-3">Güzergah</th><th class="p-3">Kalkış Tarihi</th><th class="p-3">Koltuk</th><th class="p-3">Ödenen Ücret</th><th class="p-3">İşlem</th></tr></thead>
        <tbody>
            <?php if (empty($bookings)): ?>
                <tr><td colspan="6" class="p-3 text-center">Firmanıza ait satılmış bilet bulunmuyor.</td></tr>
            <?php else: ?>
                <?php foreach($bookings as $booking): ?>
                <tr class="border-t">
                    <td class="p-3"><?php echo htmlspecialchars($booking['fullname']); ?></td>
                    <td class="p-3"><?php echo htmlspecialchars($booking['departure_location']) . ' - ' . htmlspecialchars($booking['arrival_location']); ?></td>
                    <td class="p-3"><?php echo date('d M Y, H:i', strtotime($booking['departure_time'])); ?></td>
                    <td class="p-3"><?php echo $booking['seat_number']; ?></td>
                    <td class="p-3"><?php echo number_format($booking['price_paid'], 2); ?> TL</td>
                    <td class="p-3">
                        <form action="index.php?tab=bookings&action=cancel_booking" method="POST" onsubmit="return confirm('Bu bileti iptal etmek istediğinize emin misiniz? Ücret kullanıcıya iade edilecektir.');">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>"><button type="submit" class="text-red-500 hover:underline">İptal Et</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php elseif ($tab === 'coupons'):
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE company_id = ? ORDER BY expiry_date DESC");
    $stmt->execute([$company_id]);
    $coupons = $stmt->fetchAll();
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <div class="md:col-span-2">
        <h2 class="text-2xl font-bold mb-4">Firmanızın Kuponları</h2>
        <div class="bg-white rounded-lg shadow-md overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50"><tr><th class="p-3">Kod</th><th class="p-3">İndirim Oranı</th><th class="p-3">Kalan Hak</th><th class="p-3">Son Kullanma Tarihi</th><th class="p-3">İşlem</th></tr></thead>
                <tbody>
                <?php foreach($coupons as $coupon): ?>
                    <tr class="border-t">
                        <td class="p-3 font-mono"><?php echo htmlspecialchars($coupon['code']); ?></td>
                        <td class="p-3"><?php echo ($coupon['discount_rate'] * 100); ?>%</td>
                        <td class="p-3"><?php echo $coupon['usage_limit']; ?></td>
                        <td class="p-3"><?php echo date('d M Y', strtotime($coupon['expiry_date'])); ?></td>
                        <td class="p-3">
                            <form action="index.php?tab=coupons&action=delete_coupon" method="POST" onsubmit="return confirm('Bu kuponu silmek istediğinize emin misiniz?');">
                                <input type="hidden" name="coupon_id" value="<?php echo $coupon['id']; ?>"><button type="submit" class="text-red-500 hover:underline">Sil</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div>
        <h2 class="text-2xl font-bold mb-4">Yeni Kupon Ekle</h2>
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="index.php?tab=coupons&action=add_coupon" method="POST">
                <div class="mb-3"><label class="block">Kupon Kodu</label><input type="text" name="code" required class="w-full p-2 border rounded bg-gray-50"></div>
                <div class="mb-3"><label class="block">İndirim Oranı (örn: 0.15 for 15%)</label><input type="number" step="0.01" min="0.01" max="1" name="discount_rate" required class="w-full p-2 border rounded bg-gray-50"></div>
                <div class="mb-3"><label class="block">Kullanım Limiti</label><input type="number" min="1" name="usage_limit" required class="w-full p-2 border rounded bg-gray-50"></div>
                <div class="mb-3"><label class="block">Son Geçerlilik Tarihi</label><input type="date" name="expiry_date" required class="w-full p-2 border rounded bg-gray-50"></div>
                <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Ekle</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
include '../footer.php';
?>