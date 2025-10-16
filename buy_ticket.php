<?php
session_start();
require_once 'config.php';
require_once 'helpers.php';

check_permission(['User']);
if (!is_logged_in()) {
    set_flash_message('Bilet almak için giriş yapmalısınız.', 'error');
    redirect('login.php');
}

$trip_id = $_GET['trip_id'] ?? null;

// get isteğiyle gönderilmişse (sefer view için)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // id girilmemişse yönlendir
    if (!$trip_id) {
        redirect('index.php');
    }

    // öyle bir sefer id'si yoksa yönlendir
    $query = $pdo->prepare("SELECT t.*, c.name as company_name FROM trips t JOIN companies c ON t.company_id = c.id WHERE t.id = ?");
    $query->execute([$trip_id]);
    $trip = $query->fetch();
    if (!$trip) {
        set_flash_message('Sefer bulunamadı.', 'error');
        redirect('index.php');
    }
}

// post isteğiyle form gönderildiyse (bilet almak için)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // form verilerini al
    $trip_id = $_POST['trip_id'] ?? null;
    $seat_number = $_POST['seat_number'] ?? null;
    $coupon_code = trim($_POST['coupon_code'] ?? '');
    $user_id = $_SESSION['user_id'];

    // koltuk seçilmemişse hata ver
    if (empty($seat_number)) {
        set_flash_message('Lütfen bir koltuk seçin.', 'error');
        redirect("buy_ticket.php?trip_id=$trip_id");
    }

    // trip bilgilerini çek
    $pdo->beginTransaction();
    $trip = $pdo->query("SELECT * FROM trips WHERE id = " . $pdo->quote($trip_id))->fetch();
    

    $final_price = $trip['price'];
    // kupon kodu varsa kontrol et
    if (!empty($coupon_code)) {
        $query = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND expiry_date >= date('now') AND usage_limit > 0");
        $query->execute([$coupon_code]);
        $coupon = $query->fetch();
        // kupon geçerliyse && firma kısıtlaması yoksa || firma eşleşiyorsa indirimi uygula
        if ($coupon && (is_null($coupon['company_id']) || $coupon['company_id'] == $trip['company_id'])) {
            $final_price *= (1 - $coupon['discount_rate']);
            // kupon kullanım sayısını azalt
            $pdo->prepare("UPDATE coupons SET usage_limit = usage_limit - 1 WHERE id = ?")->execute([$coupon['id']]);
        } else {
            set_flash_message('Geçersiz kupon kodu.', 'error');
        }
    }

    // user balance çek
    $user = $pdo->query("SELECT balance FROM users WHERE id = " . $pdo->quote($user_id))->fetch();
    // bakiye yetersizse hata ver
    if ($user['balance'] < $final_price) {
        $pdo->rollBack();
        set_flash_message('Yetersiz bakiye.', 'error');
        redirect("buy_ticket.php?trip_id=$trip_id");
    }

    // bakiyeyi düş ve güncelle
    $new_balance = $user['balance'] - $final_price;
    $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?")->execute([$new_balance, $user_id]);
    
    // bileti bookings'e ekle
    $query = $pdo->prepare("INSERT INTO bookings (user_id, trip_id, seat_number, price_paid) VALUES (?, ?, ?, ?)");
    $query->execute([$user_id, $trip_id, $seat_number, $final_price]);
    
    $pdo->commit();

    set_flash_message('Biletiniz başarıyla satın alındı!', 'success');
    redirect('my_account.php');
}

// dolu koltukları çek
$query = $pdo->prepare("SELECT seat_number FROM bookings WHERE trip_id = ?");
$query->execute([$trip_id]);
$occupied_seats = $query->fetchAll(PDO::FETCH_COLUMN);

include 'header.php';
?>

<h1 class="text-3xl font-bold mb-2">Bilet Satın Al</h1>
<p class="text-lg text-gray-600 mb-6"><?php echo htmlspecialchars($trip['departure_location']); ?> &rarr; <?php echo htmlspecialchars($trip['arrival_location']); ?></p>

<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <div class="md:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-4">Koltuk Seçimi</h2>
        <div class="p-4 border rounded-lg bg-gray-50">
            <!-- koltuk düzeni -->
            <?php for ($i = 1; $i <= $trip['seat_count']; $i++):
                $is_occupied = in_array($i, $occupied_seats);
                $seat_class = 'seat ' . ($is_occupied ? 'occupied' : 'empty');
            ?>
                <div class="<?php echo $seat_class; ?>" <?php if (!$is_occupied) echo "onclick='selectSeat(this, $i)'"; ?>>
                    <?php echo $i; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-4">Ödeme Detayları</h2>
            <form id="booking-form" action="buy_ticket.php" method="POST">
                <!-- trip id ve seat number hidden, önemli  -->
                <input type="hidden" name="trip_id" value="<?php echo $trip['id']; ?>">
                <input type="hidden" id="selected-seat" name="seat_number" required>
                <p><strong>Seçilen Koltuk:</strong> <span id="seat-display" class="font-bold">Yok</span></p>
                <div class="my-4"><input type="text" name="coupon_code" class="w-full p-2 border rounded" placeholder="Kupon Kodu"></div>
                <button type="submit" class="w-full bg-green-500 text-white p-3 rounded-md hover:bg-green-600">Satın Al</button>
            </form>
        </div>
    </div>
</div>

<script>
function selectSeat(element, seatNumber) {
    // dinamik koltuk seçme
    document.querySelectorAll('.seat.selected').forEach(s => s.classList.remove('selected'));
    element.classList.add('selected');
    document.getElementById('selected-seat').value = seatNumber;
    document.getElementById('seat-display').textContent = seatNumber;
}
</script>

<?php include 'footer.php'; ?>