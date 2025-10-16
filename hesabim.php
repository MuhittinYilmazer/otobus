<?php
require_once 'header.php';
check_permission(['User']);
$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$balance = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT b.*, t.departure_location, t.arrival_location, t.departure_time, c.name as company_name FROM bookings b JOIN trips t ON b.trip_id = t.id JOIN companies c ON t.company_id = c.id WHERE b.user_id = ? ORDER BY t.departure_time DESC");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>
<h1 class="text-3xl font-bold mb-6">Hesabım</h1>
<p class="mb-6 text-lg"><strong>Bakiyeniz:</strong> <?php echo number_format($balance, 2); ?> TL</p>

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
            <?php foreach($bookings as $booking): 
                 $can_cancel = (new DateTime($booking['departure_time']) > (new DateTime())->add(new DateInterval('PT1H')));
            ?>
            <tr class="border-t">
                <td class="p-4"><?php echo htmlspecialchars($booking['departure_location']) . ' - ' . htmlspecialchars($booking['arrival_location']); ?> <br><small>(<?php echo htmlspecialchars($booking['company_name']); ?>)</small></td>
                <td class="p-4"><?php echo date('d M Y, H:i', strtotime($booking['departure_time'])); ?></td>
                <td class="p-4"><?php echo $booking['seat_number']; ?></td>
                <td class="p-4"><?php echo number_format($booking['price_paid'], 2); ?> TL</td>
                <td class="p-4">
                     <a href="index.php?page=view_ticket&booking_id=<?php echo $booking['id']; ?>" class="text-blue-500 hover:underline mr-4" target="_blank">PDF</a>
                     <?php if ($can_cancel): ?>
                     <form action="index.php?action=cancel_ticket" method="POST" class="inline" onsubmit="return confirm('Bu bileti iptal etmek istediğinize emin misiniz?');">
                         <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                         <button type="submit" class="text-red-500 hover:underline">İptal Et</button>
                     </form>
                     <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(count($bookings) === 0): ?>
            <tr><td colspan="5" class="p-4 text-center">Hiç biletiniz bulunmuyor.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once 'footer.php'; ?>