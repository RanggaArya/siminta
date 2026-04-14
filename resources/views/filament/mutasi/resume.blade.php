<x-filament::page>
    {{ $this->form }}

    <div class="mt-6 space-y-6">

        <x-filament::section>
            <x-slot name="heading">
                Tabel Resume ({{ $periodeLabel }})
            </x-slot>
            <div class="overflow-auto">
                <table class="w-full text-sm border border-gray-200 dark:border-gray-700">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr class="text-left">
                            <th class="px-3 py-2">No. Inventaris</th>
                            <th class="px-3 py-2">Nama Perangkat</th>
                            <th class="px-3 py-2">Tipe</th>
                            <th class="px-3 py-2">Kondisi</th>
                            <th class="px-3 py-2">Lokasi Asal</th>
                            <th class="px-3 py-2">Lokasi Tujuan</th>
                            <th class="px-3 py-2">Tgl. Mutasi</th>
                            <th class="px-3 py-2">Tgl. Diterima</th>
                            <th class="px-3 py-2">Alasan</th>
                            <th class="px-3 py-2">Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($rows as $r)
                        <tr class="hover:bg-green-50 dark:hover:bg-white/5">
                            <td class="px-3 py-2 whitespace-nowrap">{{ $r['nomor_inventaris'] ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $r['nama_perangkat'] ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $r['tipe'] ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $r['kondisi'] ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $r['lokasi_asal'] ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $r['lokasi_tujuan'] ?? '-' }}</td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                @if(!empty($r['tanggal_mutasi']))
                                {{ \Illuminate\Support\Carbon::parse($r['tanggal_mutasi'])->locale('id')->translatedFormat('d M Y') }}
                                @else - @endif
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                @if(!empty($r['tanggal_diterima']))
                                {{ \Illuminate\Support\Carbon::parse($r['tanggal_diterima'])->locale('id')->translatedFormat('d M Y') }}
                                @else - @endif
                            </td>
                            <td class="px-3 py-2">{{ $r['alasan_mutasi'] ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $r['dicatat_oleh'] ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td class="px-3 py-4 text-center" colspan="10">Tidak ada data.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament::page>