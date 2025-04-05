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

if (isset($_SESSION['user'])) {
    $discord_id = $conn->real_escape_string($_SESSION['user']['id']);
    $discord_username = $conn->real_escape_string($_SESSION['user']['username']);
    $discord_avatar = $_SESSION['user']['avatar'];

    $avatar_extension = is_animated($discord_avatar);
    $avatar_url = "https://cdn.discordapp.com/avatars/$discord_id/$discord_avatar$avatar_extension";
}

$auth_url = url($client_id, $redirect_url, $scopes);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amx - تسجيل الدخول</title>
    <!-- Embed Info -->
    <meta property="og:title" content="Amx - تسجيل الدخول" />
    <!-- Libarys -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@160..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.2/dist/semantic.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="../StyleJs/Style.css" rel="stylesheet">
</head>

<body class="bg-[#111111] text-white flex items-center justify-center h-screen">
    <div class="main_overlay">
        <div class="overlay_bulb overlay_bulb__LT"></div>
        <div class="overlay_bulb overlay_bulb__RT"></div>
        <div class="overlay_bulb overlay_bulb__LB"></div>
    </div>

    <div class="max-w-sm w-full bg-[#1A1A1A] p-8 rounded-2xl shadow-2xl border-2 border-[#333333]">
        <div class="text-center mb-6">
            <img src="assests/logo.png" alt="Amx711" class="rounded-2xl mx-auto h-20 mb-3">
            <h2 class="text-3xl font-bold text-white">Login</h2>
            <p class="text-[#A1A1A1]">Access your know by inputing your info</p>
        </div>


        <div class="flex items-center mb-6">
            <hr class="w-full border-t border-[#333333]" />
            <hr class="w-full border-t border-[#333333]" />
        </div>

        <button onclick="window.location.href='<?= $auth_url ?>'"
            class="w-full bg-[#7289DA] hover:bg-[#5A6BB3] text-white py-3 rounded-lg text-center font-semibold transition duration-200 ease-in-out mt-4">
            Login With Discord
        </button>

</body>

</html>