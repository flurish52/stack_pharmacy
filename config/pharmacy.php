<?php
return [

    'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 10),


    'vapid' => [
        'subject' => env('VAPID_SUBJECT'),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ]
]
?>
