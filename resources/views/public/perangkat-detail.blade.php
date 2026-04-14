<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Perangkat: {{ $perangkat->nama_perangkat }}</title>
    <link rel="shortcut icon" href="{{ asset('images/RSU.png') }}" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg sticky top-0 z-10">
        <div class="max-w-3xl mx-auto p-4 flex items-center space-x-3">
            <img src="{{ asset('images/RSU.png') }}" alt="gambar logo" width="50px">
            <span class="text-md font-bold text-gray-800">
                RSU Mitra Paramedika
            </span>
        </div>
    </nav>
    <main class="p-4 sm:p-8">
        <div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg p-4 sm:p-6 overflow-hidden">
            <div>
                <a href="{{ \App\Filament\Resources\RiwayatMaintenanceResource::getUrl('create') . '?perangkat_id=' . $perangkat->id }} "
                    class="block w-full sm:w-auto text-center bg-green-600 text-white px-4 py-2.5 rounded-lg font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 mb-6" target="_blank">
                    <i class="fa-solid fa-plus"></i> Add Maintenance
                </a>
            </div>
            <div class="border-b-4 border-b-green-400 pb-4 mb-6">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $perangkat->nama_perangkat }}</h1>
                <p class="text-lg text-gray-600">{{ $perangkat->nomor_inventaris }}</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 mb-6">
                <div>
                    <span class="text-sm font-medium text-gray-500">Jenis</span>
                    <p class="text-base text-gray-900">{{ $perangkat->jenis?->nama_jenis ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Tipe/Merek</span>
                    <p class="text-base text-gray-900">{{ $perangkat->tipe ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Lokasi</span>
                    <p class="text-base text-gray-900">{{ $perangkat->lokasi?->nama_lokasi ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Status</span>
                    <p>
                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                            {{ $perangkat->status?->nama_status ?? 'N/A' }}
                        </span>
                    </p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Kondisi</span>
                    <p>
                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">
                            {{ $perangkat->kondisi?->nama_kondisi ?? 'N/A' }}
                        </span>
                    </p>
                </div>
            </div>

            <h2 class="text-2xl font-semibold mb-4 text-gray-900">Riwayat Maintenance</h2>
            <div class="space-y-4 border-green-400 shadow-md border rounded-lg">
                @forelse($perangkat->riwayatMaintenances->sortByDesc('tanggal_maintenance') as $history)
                <div x-data="{ expanded: false }" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <p :class="!expanded ? 'line-clamp-1' : ''" class="font-semibold text-gray-800">
                        {{ $history->deskripsi }}
                    </p>
                    <div class="flex flex-wrap gap-2 items-center justify-between mt-2">
                        <div class="flex flex-col gap-2 w-full">
                            <span class="text-sm text-gray-500">
                                {{ optional($history->tanggal_maintenance)->format('d M Y') ?? $history->created_at->format('d M Y') }}
                            </span>
                            <a href="#" @click.prevent="expanded = !expanded"
                                class="text-sm text-green-600 hover:underline hover:text-green-700">
                                <span x-show="!expanded" x-cloak>Selengkapnya</span>
                                <span x-show="expanded" x-cloak>Sembunyikan</span>
                            </a>
                            <a href="{{ route('public.maintenance.show', $history) }}"
                                class="text-sm inline-flex items-center justify-center bg-green-600 text-white px-3 py-1.5 rounded hover:bg-green-700 w-full">
                                Detail
                            </a>
                        </div>
                    </div>
                    @if($history->harga)
                    <div class="mt-2 text-sm font-medium text-gray-900">
                        Rp {{ number_format($history->harga, 0, ',', '.') }}
                    </div>
                    @endif
                </div>
                @empty
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-center">
                    <p class="text-gray-500">Belum ada riwayat maintenance.</p>
                </div>
                @endforelse
            </div>
        </div>
    </main>
</body>
</html>