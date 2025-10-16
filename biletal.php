<?php

if (!is_logged_in()) {
    set_flash_message('Bilet almak için giriş yapmalısınız.', 'error');
    redirect('index.php?page=login');
}
check_permission(['User']);

$trip_id = (int)($_GET['trip_id'] ?? 0);
if (!$trip_id) {
    echo "<p>Geçersiz sefer ID.</p>";
    return;
}

$stmt = $pdo->prepare("SELECT t.*, c.name as company_name FROM trips t JOIN companies c ON t.company_id = c.id WHERE t.id = ?");
$stmt->execute([$trip_id]);
$trip = $stmt->fetch();

if (!$trip) {
    echo "<p>Sefer bulunamadı.</p>";
    return;
}

$stmt = $pdo->prepare("SELECT seat_number FROM bookings WHERE trip_id = ?");
$stmt->execute([$trip_id]);
$booked_seats = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<h1 class="text-3xl font-bold mb-2">Bilet Satın Al</h1>
<p class="mb-6 text-lg"><?php echo htmlspecialchars($trip['departure_location']); ?> - <?php echo htmlspecialchars($trip['arrival_location']); ?> (<?php echo htmlspecialchars($trip['company_name']); ?>)</p>

<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <div class="md:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4">Koltuk Seçimi</h2>
        <div id="seat-map" class="mb-4">
             <?php for ($i = 1; $i <= $trip['seat_count']; $i++): ?>
                <div class="seat <?php echo in_array($i, $booked_seats) ? 'occupied' : 'empty'; ?>" data-seat-number="<?php echo $i; ?>">
                    <?php echo $i; ?>
                </div>
            <?php endfor; ?>
        </div>
        <p class="text-sm text-gray-500">* Lütfen bir koltuk seçin.</p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4">Ödeme Detayları</h2>
        <form action="index.php?action=buy_ticket" method="POST">
            <input type="hidden" name="trip_id" value="<?php echo $trip_id; ?>">
            <input type="hidden" name="seat_number" id="selected-seat-input" required>
            
            <div class="mb-4">
                <p><strong>Seçilen Koltuk:</strong> <span id="selected-seat-display">Yok</span></p>
                <p><strong>Fiyat:</strong> <?php echo number_format($trip['price'], 2); ?> TL</p>
            </div>
            
            <div class="mb-4">
               <label for="coupon_code" class="block mb-2">Kupon Kodu (opsiyonel)</label>
               <input type="text" id="coupon_code" name="coupon_code" class="w-full p-2 border rounded bg-gray-50">
            </div>

            <button type="submit" id="buy-button" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600 disabled:bg-gray-400" disabled>Satın Al</button>
        </form>
    </div>
</div>
<script>
    const seatMap = document.getElementById('seat-map');
    const selectedSeatDisplay = document.getElementById('selected-seat-display');
    const selectedSeatInput = document.getElementById('selected-seat-input');
    const buyButton = document.getElementById('buy-button');
    let currentSelected = null;

    seatMap.addEventListener('click', (e) => {
        const seat = e.target.closest('.seat.empty');
        if (!seat) return;

        if (currentSelected) {
            currentSelected.classList.remove('selected');
        }
        
        seat.classList.add('selected');
        currentSelected = seat;

        const seatNumber = seat.dataset.seatNumber;
        selectedSeatDisplay.textContent = seatNumber;
        selectedSeatInput.value = seatNumber;
        buyButton.disabled = false;
    });
</script>
