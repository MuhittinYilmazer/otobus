
<?php
check_permission(['Firma Admin']);
$company_id = $_SESSION['company_id'];
$stmt = $pdo->prepare("SELECT * FROM trips WHERE company_id = ? ORDER BY departure_time DESC");
$stmt->execute([$company_id]);
$trips = $stmt->fetchAll();
?>
 <h1 class="text-3xl font-bold mb-6">Firma Yönetim Paneli</h1>
 <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <div class="md:col-span-2">
        <h2 class="text-2xl font-bold mb-4">Seferleriniz</h2>
         <div class="bg-white rounded-lg shadow-md overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3">Güzergah</th>
                        <th class="p-3">Kalkış</th>
                        <th class="p-3">Fiyat</th>
                        <th class="p-3">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($trips as $trip): ?>
                    <tr class="border-t">
                        <td class="p-3"><?php echo htmlspecialchars($trip['departure_location']); ?> - <?php echo htmlspecialchars($trip['arrival_location']); ?></td>
                        <td class="p-3"><?php echo date('d M Y, H:i', strtotime($trip['departure_time'])); ?></td>
                        <td class="p-3"><?php echo $trip['price']; ?> TL</td>
                        <td class="p-3">
                            <form action="index.php?action=delete_trip" method="POST" onsubmit="return confirm('Bu seferi silmek istediğinize emin misiniz?');">
                                <input type="hidden" name="trip_id" value="<?php echo $trip['id']; ?>">
                                <button type="submit" class="text-red-500 hover:underline">Sil</button>
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
            <form action="index.php?action=add_trip" method="POST">
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
