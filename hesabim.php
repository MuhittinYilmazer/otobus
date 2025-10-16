<?php
session_start();
require_once 'config.php';
require_once 'helpers.php';
check_permission(['User']);

// --- FORM İŞLEMLERİ (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bakiye ekleme
    if (isset($_POST['add_balance'])) {
        $stmt = $pdo->prepare("UPDATE users SET balance = balance + 1000 WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        set_flash_message('Hesabınıza 1000 TL eklendi.', 'success');
        redirect('hesabim.php');
    }

    // Bilet iptal etme
    if (isset($_POST['cancel_booking'])) {
        $booking_id = $_POST['booking_id'];
        $user_id = $_SESSION['user_id'];

        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT b.*, t.departure_time FROM bookings b JOIN trips t ON b.trip_id = t.id WHERE b.id = ? AND b.user_id = ?");
        $stmt->execute([$booking_id, $user_id]);
        $booking = $stmt->fetch();

        if ($booking) {
            $can_cancel = (new DateTime($booking['departure_time']) > (new DateTime())->add(new DateInterval('PT1H')));
            if ($can_cancel) {
                $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$booking['price_paid'], $user_id]);
                $pdo->prepare("DELETE FROM bookings WHERE id = ?")->execute([$booking_id]);
                $pdo->commit();
                set_flash_message('Bilet iptal edildi ve ücret iade edildi.', 'success');
            } else {
                $pdo->rollBack();
                set_flash_message('Kalkışa 1 saatten az kaldığı için bilet iptal edilemez.', 'error');
            }
        }
        redirect('hesabim.php');
    }
}

// --- SAYFA GÖRÜNÜMÜ (GET) ---
// Aşağıdaki kodlar sayfa her yüklendiğinde çalışır ve biletleri listeler.
$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$balance = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT b.*, t.departure_location, t.arrival_location, t.departure_time, c.name as company_name FROM bookings b JOIN trips t ON b.trip_id = t.id JOIN companies c ON t.company_id = c.id WHERE b.user_id = ? ORDER BY t.departure_time DESC");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();

include 'header.php';
?>
<h1 class="text-3xl font-bold mb-6">Hesabım</h1>
<div class="flex justify-between items-center mb-6 bg-white p-4 rounded-lg shadow-md">
    <p class="text-lg"><strong>Bakiyeniz:</strong> <?php echo number_format($balance, 2); ?> TL</p>
    <form action="hesabim.php" method="POST">
        <button type="submit" name="add_balance" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Bakiye Ekle (1000 TL)</button>
    </form>
</div>


<h2 class="text-2xl font-bold mb-4">Biletlerim</h2>
<div class="bg-white rounded-lg shadow-md overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-gray-50">
           <tr>
               <th class="p-4">Güzergah</th>
               <th class="p-4">Tarih</th>
               <th class="p-4">Koltuk</th>
               <th class="p-4">Ödenen Ücret</th>
               <th class="p-4">İşlemler</th>
           </tr>
        </thead>
        <tbody>
            <?php if (empty($bookings)): ?>
                <tr><td colspan="5" class="p-4 text-center">Hiç biletiniz bulunmuyor.</td></tr>
            <?php else: ?>
                <?php foreach($bookings as $booking): 
                    $can_cancel = (new DateTime($booking['departure_time']) > (new DateTime())->add(new DateInterval('PT1H')));
                ?>
                <tr class="border-t">
                    <td class="p-4"><?php echo htmlspecialchars($booking['departure_location']) . ' - ' . htmlspecialchars($booking['arrival_location']); ?> <br><small>(<?php echo htmlspecialchars($booking['company_name']); ?>)</small></td>
                    <td class="p-4"><?php echo date('d M Y, H:i', strtotime($booking['departure_time'])); ?></td>
                    <td class="p-4"><?php echo $booking['seat_number']; ?></td>
                    <td class="p-4"><?php echo number_format($booking['price_paid'], 2); ?> TL</td>
                    <td class="p-4">
                         <a href="biletgoruntule.php?booking_id=<?php echo $booking['id']; ?>" class="text-blue-500 hover:underline mr-4" target="_blank">PDF</a>
                         <?php if ($can_cancel): ?>
                         <form action="hesabim.php" method="POST" class="inline" onsubmit="return confirm('Bu bileti iptal etmek istediğinize emin misiniz?');">
                             <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                             <button type="submit" name="cancel_booking" class="text-red-500 hover:underline">İptal Et</button>
                         </form>
                         <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once 'footer.php'; ?>