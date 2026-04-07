<?php

namespace Database\Seeders;

use App\Models\Ref\Bahasa;
use Illuminate\Database\Seeder;

class LangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Bahasa::truncate();

        $languages = [
            ['key' => 'nrp', 'value_en' => 'Student ID', 'value_id' => 'NRP', 'value_zh' => '学号'],
            ['key' => 'prodi', 'value_en' => 'Study Program', 'value_id' => 'Program Studi', 'value_zh' => '专业'],
            ['key' => 'no', 'value_en' => 'Number', 'value_id' => 'No', 'value_zh' => '序号'],
            ['key' => 'mahasiswa', 'value_en' => 'Student', 'value_id' => 'Mahasiswa', 'value_zh' => '学生'],
            ['key' => 'mahasiswa.list', 'value_en' => 'Students', 'value_id' => 'Daftar Mahasiswa', 'value_zh' => '学生列表'],
            ['key' => 'mahasiswa.report', 'value_en' => 'Students Report', 'value_id' => 'Laporan Mahasiswa', 'value_zh' => '学生报告'],
            ['key' => 'report', 'value_en' => 'Reports', 'value_id' => 'Laporan', 'value_zh' => '报告'],
            ['key' => 'mahasiswa.name', 'value_en' => 'Student Name', 'value_id' => 'Nama Mahasiswa', 'value_zh' => '学生姓名'],
        ];

        foreach ($languages as $lang) {
            Bahasa::create($lang);
        }
    }
}
