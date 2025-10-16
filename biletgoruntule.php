<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
require_once 'helpers.php';

// Bu sayfaya hem bilet sahibi (User) hem de Admin erişebilmeli.
check_permission(['User', 'Admin']);

$booking_id = (int)($_GET['booking_id'] ?? 0);

// Kullanıcının sadece kendi biletini görmesini sağla
$sql = "SELECT b.*, u.fullname, t.departure_location, t.arrival_location, t.departure_time, c.name as company_name 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN trips t ON b.trip_id = t.id 
        JOIN companies c ON t.company_id = c.id 
        WHERE b.id = ?";
$params = [$booking_id];

if ($_SESSION['role'] === 'User') {
    $sql .= " AND b.user_id = ?";
    $params[] = $_SESSION['user_id'];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ticket = $stmt->fetch();

if(!$ticket) { 
    // header.php'yi dahil etmeden önce yönlendirme veya çıkış yap
    set_flash_message('Bilet bulunamadı veya bu bileti görme yetkiniz yok.', 'error');
    redirect('index.php');
}

// Bilet varsa, şimdi header'ı dahil et
include 'header.php';
?>
<style>
    /* Sadece yazdırma sırasında uygulanacak stiller */
    @media print {
        body * {
            visibility: hidden; /* Önce her şeyi gizle */
        }
        #printable-ticket, #printable-ticket * {
            visibility: visible; /* Sadece bilet alanını ve içindekileri görünür yap */
        }
        #printable-ticket {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print {
            display: none; /* Yazdırılmayacak elemanları gizle */
        }
    }
</style>

<div id="printable-ticket" class="max-w-2xl mx-auto bg-white p-8 border-4 border-dashed border-gray-300">
     <h1 class="text-3xl font-bold text-center mb-2">OTOBÜS BİLETİ</h1>
     <p class="text-center text-gray-500 mb-6"><?php echo htmlspecialchars($ticket['company_name']); ?></p>
     <div class="grid grid-cols-2 gap-8">
        <div>
           <h3 class="font-bold text-gray-500">YOLCU ADI</h3>
           <p class="text-lg"><?php echo htmlspecialchars($ticket['fullname']); ?></p>
        </div>
        <div class="text-right">
           <h3 class="font-bold text-gray-500">KOLTUK NO</h3>
           <p class="text-lg font-bold"><?php echo $ticket['seat_number']; ?></p>
        </div>
         <div>
           <h3 class="font-bold text-gray-500">KALKIŞ</h3>
           <p class="text-lg"><?php echo htmlspecialchars($ticket['departure_location']); ?></p>
        </div>
         <div class="text-right">
           <h3 class="font-bold text-gray-500">VARIŞ</h3>
           <p class="text-lg"><?php echo htmlspecialchars($ticket['arrival_location']); ?></p>
        </div>
         <div>
           <h3 class="font-bold text-gray-500">KALKIŞ ZAMANI</h3>
           <p class="text-lg"><?php echo date('d M Y, H:i', strtotime($ticket['departure_time'])); ?></p>
        </div>
         <div class="text-right">
           <h3 class="font-bold text-gray-500">ÖDENEN ÜCRET</h3>
           <p class="text-lg"><?php echo number_format($ticket['price_paid'], 2); ?> TL</p>
        </div>
     </div>
     <p class="text-center mt-8 text-gray-500">İyi yolculuklar dileriz!</p>
</div>
 <div class="text-center mt-6 no-print">
    <button onclick="window.print()" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">Yazdır / PDF Olarak Kaydet</button>
</div>

<?php
include 'footer.php';
?>