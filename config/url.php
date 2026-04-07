<?php

return [

    'service' => [
        'auth' => env('AUTH_SERVICE_URL'),
        'gate' => env('GATE_SERVICE_URL'),
        'pegawai' => env('PEGAWAI_SERVICE_URL'),
        'mahasiswa' => env('MAHASISWA_SERVICE_URL'),
        'disclaimer' => env('DISCLAIMER_SERVICE_URL', 'https://tnc.petra.ac.id'),
        'api' => [
            'auth' => env('AUTH_SERVICE_URL_API', env('AUTH_SERVICE_URL')),
            'gate' => env('GATE_SERVICE_URL_API', env('GATE_SERVICE_URL')),
            'notification' => env('NOTIFICATION_SERVICE_URL_API'),
        ],
    ],

];
