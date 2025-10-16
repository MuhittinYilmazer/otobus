<?php

require_once 'config.php';
include 'header.php'; 
?>
<h1 class="text-3xl font-bold mb-6">Sefer Sonuçları</h1>
 <?php
 // get parametrelerini al
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

// boşsa uyarı ver
if (empty($from) || empty($to)) {
    echo "<p>Lütfen kalkış ve varış noktası seçin.</p>";
} else {
    
    // seferleri çek
    $query = $pdo->prepare("SELECT t.*, c.name as company_name 
                           FROM trips t 
                           JOIN companies c ON t.company_id = c.id 
                           WHERE t.departure_location LIKE ? 
                           AND t.arrival_location LIKE ? 
                           AND REPLACE(t.departure_time, 'T', ' ') > datetime('now') 
                           ORDER BY t.departure_time");

    $query->execute(["%$from%", "%$to%"]);
    $trips = $query->fetchAll();

    // sefer sayısı 0 dan büyükse listele
    if (count($trips) > 0) {
        foreach ($trips as $trip) { ?>
            <div class="bg-white rounded-lg shadow-md p-4 mb-4">
                <div class="flex justify-between items-center">
                     <div>
                        <h2 class="text-xl font-bold"><?php echo $trip['company_name']; ?></h2>
                        <p class="text-gray-600"><?php echo $trip['departure_location']; ?> &rarr; <?php echo $trip['arrival_location']; ?></p>
                        <p><strong>Kalkış:</strong> <?php echo date('d M Y, H:i', strtotime($trip['departure_time'])); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-blue-600"><?php echo number_format($trip['price'], 2); ?> TL</p>
                        <!-- bilet alma linki -->
                        <a href="biletal.php?trip_id=<?php echo $trip['id']; ?>" class="mt-2 inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Bilet Al</a>
                    </div>
                </div>
            </div>
        <?php }
    } else {
        echo "<div class='bg-white p-6 rounded-lg shadow-md'><p>Bu güzergahta uygun sefer bulunamadı.</p></div>";
    }
}
?>
<?php require_once 'footer.php'; ?>