<?php 
require_once 'helpers.php';
include 'header.php';
?>

<div class="text-center py-16 px-6 bg-white rounded-lg shadow-xl">
    <h1 class="text-4xl md:text-5xl font-extrabold mb-4">Nereye Gitmek İstersiniz?</h1>
    <p class="text-lg text-gray-600 mb-8">Türkiye'nin her yerine en uygun otobüs biletini bulun.</p>
    <form action="trips.php" method="GET" class="max-w-xl mx-auto flex flex-col md:flex-row items-center gap-4">
        
        <div class="w-full">
            <label for="from" class="sr-only">Nereden</label>
            <input type="text" name="from" placeholder="Nereden" required class="w-full p-3 border rounded-md focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="w-full">
            <label for="to" class="sr-only">Nereye</label>
            <input type="text" name="to" placeholder="Nereye" required class="w-full p-3 border rounded-md focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="w-full md:w-auto bg-blue-600 text-white p-3 rounded-md hover:bg-blue-700 font-semibold shadow-md transition-transform transform hover:scale-105">Sefer Bul</button>
    </form>
</div>

<?php 
include 'footer.php'
?>