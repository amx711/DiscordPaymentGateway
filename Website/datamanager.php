<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if (isset($data['user_id'], $data['price'], $data['product'], $data['invoice_id'])) {
        $newEntry = [
            'user_id' => $data['user_id'],
            'price' => $data['price'],
            'product' => $data['product'],
            'invoice_id' => $data['invoice_id'],
            'status' => "not paid"
        ];

        $file = 'data.json';
        if (file_exists($file)) {
            $json = json_decode(file_get_contents($file), true);
        } else {
            $json = [];
        }

        $json[] = $newEntry;
        file_put_contents($file, json_encode($json, JSON_PRETTY_PRINT));

        $webhook_url = '';  // رابط الوق
        $message = [
            'content' => 'بدء عملية دفع جديدة',
            'embeds' => [
                [
                    'title' => 'عملية دفع جديد',
                    'description' => "رقم الفاتورة: {$data['invoice_id']}\nالمبلغ: \${$data['price']}\nالمنتج: {$data['product']}\nالحالة:  جاري الدفع",
                    'color' => 16711680, 
                    'fields' => [
                        [
                            'name' => 'ايدي اليوزر',
                            'value' => $data['user_id'],
                            'inline' => true
                        ],
                        [
                            'name' => 'المنتج',
                            'value' => $data['product'],
                            'inline' => true
                        ],
                    ],
                ],
            ],
        ];

        $ch = curl_init($webhook_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send webhook']);
        } else {
            echo json_encode(['status' => 'success']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    }
}
?>
