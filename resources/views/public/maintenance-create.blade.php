<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Maintenance: {{ $perangkat->nama_perangkat }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-semibold mb-4">Tambah Riwayat Maintenance</h1>
        <p class="mb-2">Untuk Perangkat: <strong>{{ $perangkat->nama_perangkat }}</strong></p>
        <p class="text-sm text-gray-600 mb-6">No. Inventaris: {{ $perangkat->nomor_inventaris }}</p>

        <form action="{{ route('public.maintenance.store', $perangkat) }}" method="POST">
            @csrf <div class="mb-4">
                <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">
                    Deskripsi Maintenance
                </label>
                <textarea id="deskripsi" name="deskripsi" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    required>{{ old('deskripsi') }}</textarea>
                @error('deskripsi')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="harga" class="block text-sm font-medium text-gray-700 mb-1">
                    Harga (Opsional)
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                    <input type="number" id="harga" name="harga" value="{{ old('harga') }}"
                        class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Contoh: 50000">
                </div>
                @error('harga')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('public.perangkat.show', $perangkat) }}"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300">
                    Batal
                </a>
                <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700">
                    Simpan Maintenance
                </button>
            </div>
        </form>

    </div>
</body>

</html>