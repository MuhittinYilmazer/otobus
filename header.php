<!DOCTYPE html>
<?php require_once 'helpers.php'?>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otobüs Bileti Satın Alma Platformu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .seat {
            width: 40px; height: 40px;
            display: inline-flex; justify-content: center; align-items: center;
            border: 1px solid #ccc; border-radius: 5px; margin: 5px; cursor: pointer;
            font-weight: bold;
        }
        .seat.selected { background-color: #3b82f6; color: white; }
        .seat.occupied { background-color: #ef4444; color: white; cursor: not-allowed; }
        .seat.empty:hover { background-color: #e5e7eb; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-blue-600">BiletAl</a>
            <div class="flex items-center space-x-4">
                <a href="index.php" class="hover:text-blue-500">Ana Sayfa</a>
                	<?php if(is_logged_in()){?>
                   <?php }else{?>
					 <a href="login.php" class="hover:text-blue-500">Giriş Yap</a>
                    <a href="register.php" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Kayıt Ol</a><?php }?>
               
            </div>
        </div>
    </nav>
    <main class="container mx-auto p-4 md:p-8">
        

