<?php
require "db.php";
require __DIR__ . "/DiscordLogin/discord.php";
require __DIR__ . "/DiscordLogin/config.php";

function is_animated($discord_avatar)
{
    $ext = substr($discord_avatar, 0, 2);
    if ($ext == "a_") {
        return ".gif";
    } else {
        return ".png";
    }
}

if (isset($_SESSION['user']) && $_SESSION['user']['id'] == '798145401844138005') {
    $discord_id = $conn->real_escape_string($_SESSION['user']['id']);
    $discord_username = $conn->real_escape_string($_SESSION['user']['username']);
    $discord_avatar = $_SESSION['user']['avatar'];

    $avatar_extension = is_animated($discord_avatar);
    $avatar_url = "https://cdn.discordapp.com/avatars/$discord_id/$discord_avatar$avatar_extension";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $discord_id_input = $conn->real_escape_string($_POST['discord_id']);
        $product = $conn->real_escape_string($_POST['product']);
        $price = $conn->real_escape_string($_POST['price']);
        $query = "INSERT INTO payments (discord_id, product, price) VALUES ('$discord_id_input', '$product', '$price')";
        if ($conn->query($query) === TRUE) {
            $paymentid = $conn->insert_id;
            echo "<div class='p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg' role='alert'>تم صنع الفاتورة بنجاح! | رقم الفاتورة: $paymentid</div>";
        } else {
            echo "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg' role='alert'>حدث خطأ خلال صنع الفاتورة " . $conn->error . "</div>";
        }
    }

} else {
    header("Location: index.php"); 
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amx - صنع الفواتير </title>
    <link rel="icon" href="assests/logo.png">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@160..700&display=swap" rel="stylesheet">
</head>
<style>
        body {
        font-family: 'Readex Pro', sans-serif;
    }
</style>
<body class="bg-[#111111] text-white p-6">
    <div class="max-w-lg mx-auto bg-[#111111] border-2 border-gray-700 rounded-2xl shadow-2xl p-8 rounded-lg shadow-lg">
        <h2 class="text-3xl font-bold text-[#FF6B00] mb-6">صنع فاتورة</h2>
        <form method="POST">
            <div class="mb-4">
                <label for="discord_id" class="block text-sm font-medium text-gray-300">ايدي صحاب الفاتورة</label>
                <input type="text" name="discord_id" id="discord_id" class="mt-1 block w-full p-2 border border-gray-600 rounded-md shadow-sm focus:ring-[#FF6B00] focus:border-[#FF6B00] sm:text-sm bg-gray-700 text-white" required>
            </div>
            <div class="mb-4">
                <label for="product" class="block text-sm font-medium text-gray-300">المنتج</label>
                <input type="text" name="product" id="product" class="mt-1 block w-full p-2 border border-gray-600 rounded-md shadow-sm focus:ring-[#FF6B00] focus:border-[#FF6B00] sm:text-sm bg-gray-700 text-white" required>
            </div>
            <div class="mb-6">
                <label for="price" class="block text-sm font-medium text-gray-300">السعر</label>
                <input type="number" name="price" id="price" class="mt-1 block w-full p-2 border border-gray-600 rounded-md shadow-sm focus:ring-[#FF6B00] focus:border-[#FF6B00] sm:text-sm bg-gray-700 text-white" required>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="w-full bg-[#FF6B00] text-white py-2 px-4 rounded-md hover:bg-[#e65b00] focus:outline-none focus:ring-2 focus:ring-[#FF6B00] focus:ring-opacity-50">
                    صنع فاتورة 
                </button>
            </div>
        </form>
    </div>
</body>
</html>
