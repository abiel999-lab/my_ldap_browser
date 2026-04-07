<?php

namespace App\Filament\Pages\Akademik;

use App\Helpers\Auth\UserHelper;
use App\Models\Akademik\Mahasiswa;
use App\Models\Ref\Semester;
use App\Models\Ref\Unit;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class LaporanMahasiswa extends Page implements HasForms
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.akademik.laporan-mahasiswa';

    public static function getNavigationGroup(): string
    {
        return __('report');
    }

    public static function getNavigationLabel(): string
    {
        return __('mahasiswa.report'); // Label for navigation
    }

    public static function getModelLabel(): string
    {
        return __('mahasiswa.report'); // Label tunggal
    }

    public static function getPluralModelLabel(): string
    {
        return __('mahasiswa.report'); // Label jamak
    }

    public static function shouldRegisterNavigation(): bool
    {
        return UserHelper::userHasRole(['ADMIN', 'PIC', 'KAPRODI', 'KOORDINATOR']); // --------untuk mengakses halaman laporan, semua boleh mengakses
    }

    public ?array $data = []; // Untuk menyimpan data form

    public ?Collection $reportResults = null; // Untuk menyimpan hasil laporan

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Laporan Mahasiswa')
                    ->columns([
                        'sm' => 2,
                        'xl' => 2,
                        '2xl' => 2,
                    ])
                    ->schema([
                        Select::make('program_studi_id')
                            ->label(__('Program Studi'))
                            ->options(
                                Unit::unitutama()
                                    ->nama()
                                    ->pluck('nama', 'id')
                                    ->toArray()
                            )
                            ->searchable()
                            ->required(),
                        Select::make('smt_masuk_id')
                            ->label(__('Periode Masuk'))
                            ->options(
                                Semester::orderBy('kode_baa', 'desc')
                                    ->whereRaw("substring(kode_baa, 6, 1) IN ('1')") // hanya semester ganjil
                                    ->limit(20)
                                    ->pluck('nama', 'kode_baa')
                                    ->toArray()
                            )
                            ->required(),
                        Actions::make([
                            Action::make('generateReport')
                                ->label(__('Buat Laporan'))
                                ->action('generateReport')
                                ->color('primary')
                                ->icon('heroicon-o-document-text'),
                        ]),
                    ]),
            ])
            ->statePath('data'); // Penting: simpan state form di properti $data
    }

    public function generateReport(): void
    {
        $data = $this->form->getState();

        // Di sini Anda akan menulis logika untuk mengambil data laporan
        if (! isset($data['program_studi_id'])) {
            $this->reportResults = collect(); // Jika tidak ada program studi, kembalikan koleksi kosong

            return;
        }

        $unit_id = $data['program_studi_id'];
        // ambil data periode masuk
        $smt_masuk_id = $data['smt_masuk_id'] ?? null;

        if ($smt_masuk_id) {
            $data = Mahasiswa::where('program_studi_id', $unit_id)
                ->where('smt_masuk', $smt_masuk_id)
                ->whereRaw('substring(status_mhs, 1, 1) = ?', ['A'])
                ->orderBy('nrp', 'asc')
                ->get();
        } else {
            $data = Mahasiswa::where('program_studi_id', $unit_id)
                ->whereRaw('substring(status_mhs, 1, 1) = ?', ['A'])
                ->orderBy('nrp', 'asc')
                ->get();
        }

        // Simpan hasil query ke dalam properti $results
        $results = $data;

        // Simpan hasil ke properti untuk digunakan di view
        $this->reportResults = $results; // Simpan hasil ke properti
    }
}
