<x-filament::page>
    {{ $this->form }}

    <x-filament::section class="mt-6">
        <x-slot name="heading">Tabel Resume ({{ $periodeLabel }})</x-slot>

        <div class="overflow-auto">
            <table class="w-full text-sm border border-gray-200 dark:border-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="px-3 py-2">No. Inventaris</th>
                        <th class="px-3 py-2">Nama Perangkat</th>
                        <th class="px-3 py-2">Tipe</th>
                        <th class="px-3 py-2">Lokasi Snapshot</th>
                        <th class="px-3 py-2">Tgl. Penarikan</th>
                        <th class="px-3 py-2">Alasan</th>
                        <th class="px-3 py-2">Tindak Lanjut</th>
                        <th class="px-3 py-2">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($rows as $r)
                        @php
                            $al = $r['alasan'];
                            if (is_string($al)) {
                                $dec = json_decode($al, true);
                                $al = is_array($dec) ? $dec : [$al];
                            }
                            $alasanTxt = collect((array)$al)->filter()->implode(', ');
                            if (!empty($r['alasan_lain'])) $alasanTxt = trim($alasanTxt ? "$alasanTxt; {$r['alasan_lain']}" : $r['alasan_lain']);
                            $tl = $r['tindak_lanjut'] . (!empty($r['tindak_detail']) ? " ({$r['tindak_detail']})" : '');
                        @endphp
                        <tr>
                            <td class="px-3 py-2 whitespace-nowrap">{{ $r['nomor_inventaris'] ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $r['nama_perangkat'] ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $r['tipe'] ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $r['lokasi'] ?? '-' }}</td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                @if(!empty($r['tanggal']))
                                    {{ \Illuminate\Support\Carbon::parse($r['tanggal'])->locale('id')->translatedFormat('d M Y') }}
                                @else - @endif
                            </td>
                            <td class="px-3 py-2">{{ $alasanTxt ?: '-' }}</td>
                            <td class="px-3 py-2">{{ $tl ?: '-' }}</td>
                            <td class="px-3 py-2">{{ $r['dicatat_oleh'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td class="px-3 py-4 text-center" colspan="8">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament::page>
