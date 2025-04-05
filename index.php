<?php

require_once "Website/db.php";

function is_animated($image)
{
    return substr($image, 0, 2) === "a_" ? "gif" : "png";
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$avatar_url = "https://media.discordapp.net/attachments/1244647915759079571/1244647916212322305/bbbff967f9b04bf6_1-16.png?ex=67e556b3&is=67e40533&hm=25079f1a140716c7add453f03c8d328320964d37373a8867d324411a9d86b65d&format=webp&quality=lossless&";

if (!empty($_SESSION['user_id']) && !empty($_SESSION['user_avatar'])) {
    $discord_id = $_SESSION['user_id'];
    $avatar_hash = $_SESSION['user_avatar'];
    $avatar_ext = is_animated($avatar_hash);
    $avatar_url = "https://cdn.discordapp.com/avatars/{$discord_id}/{$avatar_hash}.{$avatar_ext}";
}

if (!isset($_SESSION['user'])) {
    header('Location: Website/index.php');
    exit();
}

if (!isset($_GET['invoice']) || empty($_GET['invoice'])) {
    die("❌ خطأ: لم يتم العثور على الفاتورة.");
}

$invoice_id = intval($_GET['invoice']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT discord_id, time, price, product, finalstatus FROM payments WHERE paymentid = ?");
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    die("❌ خطأ: الفاتورة غير موجودة.");
}

$stmt->bind_result($db_user_id, $time, $price, $product, $finalstatus);
$stmt->fetch();

if ($db_user_id != $user_id) {
    die("❌ خطأ: هذه الفاتورة ليست لك.");
}

$formatted_time = date("d/m/Y H:i:s", strtotime($time));

$stmt->close();

?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amx - الرئيسية</title>
    <!-- Embed Info -->
    <link rel="icon" href="Website/assests/logo.png">
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="Website/assests/Style.css" rel="stylesheet">
</head>
<style>
    
    ::-webkit-scrollbar {
        width: 10px;
    }

    ::-webkit-scrollbar-track {
        background: #111111;
        border-radius: 12px;
        box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.1);
    }

    ::-webkit-scrollbar-thumb {
        background: linear-gradient(45deg, #FF6B00, #FF6B00);
        border-radius: 12px;
        border: 3px solid #111111;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease-in-out;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(45deg, #FF6B00, #FF6B00);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
        transform: scale(1.1);
    }

    ::-webkit-scrollbar-thumb:active {
        background: linear-gradient(45deg, #FF6B00, #FF6B00);
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
        transform: scale(1.2);
    }

    html {
        scroll-behavior: smooth;
    }

    body {
        font-family: 'Readex Pro', sans-serif;
        color: white;
    }
</style>

<body class="bg-[#111111] min-h-screen">
    <div class="main_overlay">
        <div class="overlay_bulb overlay_bulb__LT"></div>
        <div class="overlay_bulb overlay_bulb__RT"></div>
        <div class="overlay_bulb overlay_bulb__LB"></div>
    </div>


    <!-- Body Of Invoice -->
<div class="max-w-4xl px-6 sm:px-10 mx-auto my-6">
<div class="bg-[#111111] border-2 border-gray-700 rounded-2xl shadow-2xl p-6 sm:p-12 min-h-[500px]"> <!-- Add min-h-[500px] or adjust the value based on your needs -->

        <div class="flex justify-between items-center">
            <?php
            if ($finalstatus === "paid") {
                echo '<h1 class="text-lg md:text-xl font-semibold text-green-500" style="font-family: \'Readex Pro\', sans-serif;">حالة الفاتورة : مدفوع</h1>';
            } else {
                echo '<h1 class="text-lg md:text-xl font-semibold text-red-500" style="font-family: \'Readex Pro\', sans-serif;">حالة الفاتورة : ليس مدفوع</h1>';
            }
            ?>
            <div class="text-end">
                <h2 class="text-2xl md:text-3xl font-semibold" style="font-family: 'Readex Pro', sans-serif;">فاتورة
                    #</h2>
                <span class="text-gray-400"><?= htmlspecialchars($invoice_id, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>

        <div class="mt-8 grid sm:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold" style="font-family: 'Readex Pro', sans-serif;">الفاتورة باسم :</h3>
                <h3 class="text-lg font-semibold">
                    <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?>
                </h3>
                <p class="mt-2 text-gray-400">(<?= htmlspecialchars($_SESSION['user_id'], ENT_QUOTES, 'UTF-8') ?>)</p>
            </div>
            <div class="text-end">
                <dl class="grid grid-cols-2 sm:grid-cols-5 gap-x-3">
                    <dt class="col-span-3 font-semibold">تاريخ إنشاء الفاتورة :</dt>
                    <dd class="col-span-2 text-gray-400">
                        <?= htmlspecialchars($formatted_time, ENT_QUOTES, 'UTF-8') ?>
                    </dd>
                </dl>
            </div>
        </div>

        <div class="mt-8 border border-gray-700 p-4 rounded-lg bg-[#1a1a1a]">
            <div class="hidden sm:grid sm:grid-cols-5 text-gray-400 text-xs font-medium uppercase mb-3">
                <div class="sm:col-span-2">المنتج</div>
                <div class="text-end">السعر</div>
            </div>

            <div class="border-b border-gray-700 my-3"></div>
            <div class="grid grid-cols-3 sm:grid-cols-5 gap-4">
                <div class="col-span-full sm:col-span-2">
                    <h5 class="sm:hidden text-xs font-medium text-gray-400 uppercase">المنتج</h5>
                    <p class="font-medium"><?= htmlspecialchars($product, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="sm:text-end">
                    <h5 class="sm:hidden text-xs font-medium text-gray-400 uppercase">السعر</h5>
                    <p><?= htmlspecialchars($price, ENT_QUOTES, 'UTF-8') ?>c</p>
                </div>
            </div>

        </div>
    </div>
    <div class="mt-10 flex justify-center gap-4">
        <?php
        if ($finalstatus === "paid") {
            echo '        <a class="py-3 px-6 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-600 bg-gray-800 text-white shadow-lg hover:bg-gray-700 transition duration-300"
                    href="#" onclick="amxsaveinvoice()">
                    <i class="fa-solid fa-download"></i>
                    حفظ الفاتورة
                </a>';
        } else {
            echo '
                <a class="py-3 px-6 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white shadow-lg hover:bg-blue-700 transition duration-300"
                    onclick="handleButtonClick()">
                    <i class="fa-solid fa-cart-shopping"></i>
                    دفع
                </a>';
        }
        ?>
    </div>
    <div class="mt-5 text-sm text-gray-400 text-center">
        <p>© 2025 amx711 system.</p>
    </div>
</div>



<script>
function handleButtonClick() {
    var userId = "<?php echo $_SESSION['user_id']; ?>";
    var price = "<?php echo $price ?>";; 
    var product = "<?php echo $product ?>";; 
    var invoiceId = "<?php echo $invoice_id ?>";; 

    var data = {
        user_id: userId,
        price: price,
        product: product,
        invoice_id: invoiceId
    };

    fetch('Website/datamanager.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        Swal.fire({
              title: "تم بدء عملية الدفع",
              text: "لاكمال عملية الدفع عليك بضغط على الزر بلاسفل وسوف يتم ايصالك الى مكان الدفع",
              icon: "info",
              confirmButtonText: "دفـع",
              customClass: {
                popup: 'custom-popup'
            },
            willOpen: () => {
                const popup = Swal.getPopup();
                popup.style.color = "white"; 
                popup.style.backgroundColor = "#111111"; 
                popup.style.border = "2px solid orange";
            }
            }).then(() => {
              window.location.href = "https://discord.gg/k9eNaYJHEK";  // رابط الدس الي فيه البوت
      });
    })
    .catch((error) => {
        console.error('Error:', error);
    });
}

      function amxsaveinvoice() { 
        window.print(); 
      }
</script>

</body>