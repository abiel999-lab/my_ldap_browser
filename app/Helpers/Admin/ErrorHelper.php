<?php

use Illuminate\Support\Facades\Log;

if (! function_exists('throw_error')) {
    function throw_error(string $errorCode, ?array $context = [], ?Throwable $exception = null)
    {
        $errors = config('error');

        // Gunakan kode 5000 jika kode yang diberikan tidak ditemukan
        $errorData = $errors[$errorCode] ?? $errors['5000'];

        $logContext = array_merge($context, [
            'error_code' => $errorCode,
            'ip_address' => request()->ip(),
            'user_id' => auth() ?? auth()->id(),
        ]);

        // Jika ada exception, tambahkan detailnya ke log
        if ($exception) {
            $logContext['exception_message'] = $exception->getMessage();
            $logContext['file'] = $exception->getFile();
            $logContext['line'] = $exception->getLine();
        }

        // Mencatat log secara otomatis sesuai levelnya
        Log::log($errorData['log_level'], $errorData['message'], $logContext);

        // Membuat respons JSON standar
        $response = response()->json([
            'status' => 0,
            'code' => $errorCode,
            'message' => $errorData['message'],
        ] + ($errorData['http_code'] == 422 ? ['errors' => $context['errors'] ?? []] : []), $errorData['http_code']);

        // Menghentikan eksekusi dan mengirimkan respons
        $response->throwResponse();
    }
}
