<?php

namespace App\Console\Commands;

use App\Models\Ref\Bahasa;
use Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateLangFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate JSON language files from database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai generate file bahasa...');

        // 1. Ambil semua data dari database
        $translations = Bahasa::all();

        // 2. Siapkan array penampung untuk masing-masing bahasa
        $id = [];
        $en = [];
        $cn = [];

        foreach ($translations as $t) {
            // Format JSON Laravel: "Key Asli" => "Terjemahan"
            // Jika kosong, fallback ke key aslinya
            $id[$t->key] = $t->value_id ?? $t->key;
            $en[$t->key] = $t->value_en ?? $t->key;
            $cn[$t->key] = $t->value_zh ?? $t->key;
        }

        // 3. Tulis ke file JSON (menggunakan JSON_PRETTY_PRINT agar rapi)
        // Pastikan folder lang ada. Di Laravel 10/11 biasanya di root/lang atau resources/lang
        $path = lang_path();

        File::put("$path/id.json", json_encode($id, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        File::put("$path/en.json", json_encode($en, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        File::put("$path/zh_CN.json", json_encode($cn, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        Artisan::call('cache:reload');

        $this->info('Berhasil! File id.json, en.json, dan zh_CN.json telah diperbarui.');
    }
}
