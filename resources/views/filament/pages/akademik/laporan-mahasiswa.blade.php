<x-filament-panels::page :title="__('Laporan Mahasiswa')" :description="__('Halaman untuk melihat laporan mahasiswa')" :navigation-label="__('Laporan Mahasiswa')" :navigation-group="__('Laporan')" :navigation-icon="'heroicon-o-document-text'"
    :navigation-sort="2">

    {{ $this->form }}

    @if ($reportResults)
        <x-filament::section>
            <x-slot name="heading">
                Hasil Laporan Anda
            </x-slot>

            <div class="space-y-4">
                @if ($reportResults->isEmpty())
                    <p>Tidak ada data ditemukan untuk parameter yang dipilih.</p>
                @else
                    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NRP</th>
                                <th>Nama</th>
                                <th>Program Studi</th>
                                <th>Periode Masuk</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-gray-200 dark:divide-white/5">
                            @foreach ($reportResults as $index => $result)
                                <tr>
                                    <td class="py-1 px-2 border-b">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="py-1 px-2 border-b text-center">
                                        {{ $result->nrp }}
                                    </td>
                                    <td class="py-1 px-2 border-b">
                                        {{ $result->pesertaDidik ? $result->pesertaDidik->nama : 'N/A' }}
                                    </td>
                                    <td class="py-1 px-2 border-b">
                                        {{ $result->program_studi_id ? $result->unit->nama : 'N/A' }}
                                    </td>
                                    <td class="py-1 px-2 border-b text-center">
                                        {{ $result->smt_masuk }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </x-filament::section>
    @endif

</x-filament-panels::page>
