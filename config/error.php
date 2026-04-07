<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Standar Kode Error Aplikasi
    |--------------------------------------------------------------------------
    |
    | 1000-1999: General, Validation & Request Errors
    | 2000-2999: Authentication & Authorization Errors
    | 3000-3999: User & Profile Errors
    | 4000-4999: Payment & Transaction Errors
    | 5000-5999: Internal & Third-Party Service Errors
    |
    */

    // 1000-1999: General, Validation & Request Errors
    '1001' => [
        'message' => 'Validasi gagal. Silakan periksa kembali data yang Anda masukkan.',
        'http_code' => 422, // Unprocessable Entity
        'log_level' => 'info',
    ],
    '1002' => [
        'message' => 'Resource yang Anda minta tidak ditemukan.',
        'http_code' => 404, // Not Found
        'log_level' => 'info',
    ],
    '1003' => [
        'message' => 'Metode request tidak diizinkan.',
        'http_code' => 405, // Method Not Allowed
        'log_level' => 'warning',
    ],
    '1004' => [
        'message' => 'Terlalu banyak percobaan. Silakan coba lagi nanti.',
        'http_code' => 429, // Too Many Requests
        'log_level' => 'warning',
    ],

    // 2000-2999: Authentication & Authorization Errors
    '2001' => [
        'message' => 'Token otentikasi tidak valid atau telah kedaluwarsa.',
        'http_code' => 401, // Unauthorized
        'log_level' => 'info',
    ],
    '2002' => [
        'message' => 'Email atau password yang Anda masukkan salah.',
        'http_code' => 401, // Unauthorized
        'log_level' => 'info',
    ],
    '2003' => [
        'message' => 'Anda tidak memiliki izin untuk mengakses resource ini.',
        'http_code' => 403, // Forbidden
        'log_level' => 'warning',
    ],
    '2004' => [
        'message' => 'Akun Anda belum terverifikasi.',
        'http_code' => 403, // Forbidden
        'log_level' => 'info',
    ],
    '2005' => [
        'message' => 'Logout gagal.',
        'http_code' => 403, // Forbidden
        'log_level' => 'warning',
    ],

    // 4000-4999: Payment & Transaction Errors
    '4001' => [
        'message' => 'Pembayaran gagal diproses oleh bank.',
        'http_code' => 400, // Bad Request
        'log_level' => 'error',
    ],
    '4002' => [
        'message' => 'Saldo Anda tidak mencukupi untuk melakukan transaksi ini.',
        'http_code' => 400, // Bad Request
        'log_level' => 'info',
    ],
    '4003' => [
        'message' => 'Metode pembayaran yang dipilih tidak tersedia.',
        'http_code' => 400, // Bad Request
        'log_level' => 'warning',
    ],

    // 5000-5999: Internal & Third-Party Service Errors
    '5000' => [
        'message' => 'Terjadi kesalahan pada server kami. Tim kami sedang menanganinya.',
        'http_code' => 500, // Internal Server Error
        'log_level' => 'critical',
    ],
    '5001' => [
        'message' => 'Layanan pihak ketiga sedang tidak tersedia. Silakan coba beberapa saat lagi.',
        'http_code' => 503, // Service Unavailable
        'log_level' => 'error',
    ],
];
