<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>{{ $perangkat->nama_perangkat }} • Maintenance</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { padding-bottom: env(safe-area-inset-bottom); }
    input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    input[type="number"] { -moz-appearance: textfield; }
  </style>
</head>
<body class="bg-slate-50 text-slate-900">
  <header class="sticky top-0 z-10 backdrop-blur bg-white/80 border-b border-slate-200">
    <div class="max-w-lg mx-auto px-4 py-3 flex items-center gap-3">
      <div class="shrink-0 w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
        <span class="text-xl">🛠️</span>
      </div>
      <div class="min-w-0">
        <h1 class="text-base font-semibold truncate">{{ $perangkat->nama_perangkat }}</h1>
        <p class="text-xs text-slate-500 truncate">Inv: {{ $perangkat->nomor_inventaris ?? '—' }}</p>
      </div>
      <a href="#" onclick="location.reload()" class="ml-auto text-xs text-slate-600 px-3 py-1.5 rounded-md border border-slate-200 hover:bg-slate-100">Refresh</a>
    </div>
  </header>

  <main class="max-w-lg mx-auto px-4 py-4 space-y-4">
    <section class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 space-y-2">
      <div class="grid grid-cols-2 gap-x-3 gap-y-2 text-sm">
        <div class="text-slate-500">Tipe/Merek</div>
        <div class="font-medium">{{ $perangkat->tipe ?? '—' }}</div>
        <div class="text-slate-500">Lokasi</div>
        <div class="font-medium">{{ $perangkat->lokasi->nama_lokasi ?? '—' }}</div>
        <div class="text-slate-500">Jenis</div>
        <div class="font-medium">{{ $perangkat->jenis->nama_jenis ?? '—' }}</div>
        <div class="text-slate-500">Status</div>
        <div>
          @php $s = $perangkat->status->nama_status ?? '—'; @endphp
          <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold
            @class([
              'bg-emerald-50 text-emerald-700 border border-emerald-200' => $s === 'Aktif',
              'bg-amber-50 text-amber-700 border border-amber-200'       => $s === 'Perbaikan',
              'bg-rose-50 text-rose-700 border border-rose-200'           => in_array($s,['Rusak','Hilang']),
              'bg-slate-50 text-slate-700 border border-slate-200'        => ! in_array($s,['Aktif','Perbaikan','Rusak','Hilang']),
            ])">
            {{ $s }}
          </span>
        </div>
        <div class="text-slate-500">Kondisi</div>
        <div class="font-medium">{{ $perangkat->kondisi->nama_kondisi ?? '—' }}</div>
      </div>
    </section>

    @if (session('ok'))
      <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-3 text-sm">
        ✅ {{ session('ok') }}
      </div>
    @endif
    @if ($errors->any())
      <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-3 text-sm">
        <ul class="list-disc pl-5">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <section class="bg-white rounded-xl border border-slate-200 shadow-sm">
      <div class="p-4 border-b border-slate-200 flex items-center justify-between">
        <h2 class="text-sm font-semibold">Riwayat Maintenance</h2>
        <span class="text-xs text-slate-500">{{ $perangkat->riwayatMaintenances->count() }} data</span>
      </div>

      <ul class="divide-y divide-slate-200">
        @forelse ($perangkat->riwayatMaintenances as $rm)
          <li class="p-4">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-xs text-slate-500">{{ $rm->created_at->format('d M Y') }}</p>
                <p class="text-sm font-medium mt-1 break-words">{{ $rm->deskripsi }}</p>
              </div>
              <div class="text-right text-sm font-semibold whitespace-nowrap">
                @if(!is_null($rm->harga))
                  Rp {{ number_format((float)$rm->harga, 2, ',', '.') }}
                @else
                  —
                @endif
              </div>
            </div>
          </li>
        @empty
          <li class="p-4 text-sm text-slate-500">Belum ada data.</li>
        @endforelse
      </ul>
    </section>

    <section class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-24">
      <h2 class="text-sm font-semibold mb-3">Tambah Maintenance</h2>
      @auth
        <form method="post" action="{{ route('qr.maintenance.store', $perangkat->qr_token) }}" class="space-y-3" id="fm">
          @csrf
          <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" />
          <label class="block">
            <span class="text-xs text-slate-600">Deskripsi <span class="text-rose-600">*</span></span>
            <textarea name="deskripsi" rows="3" required
              class="mt-1 block w-full rounded-lg border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 text-sm"
              placeholder="Contoh: Ganti thermal paste, bersihkan fan">{{ old('deskripsi') }}</textarea>
          </label>
          <label class="block">
            <span class="text-xs text-slate-600">Harga (Rp)</span>
            <input type="number" inputmode="decimal" min="0" step="0.01" name="harga" value="{{ old('harga') }}"
              class="mt-1 block w-full rounded-lg border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 text-sm"
              placeholder="0.00">
          </label>
          <div class="pt-2">
            <button type="submit"
              class="w-full py-3 rounded-lg bg-sky-600 text-white text-sm font-semibold active:scale-[.99]">
              Simpan
            </button>
            <p class="mt-2 text-[11px] text-slate-500">
              Catatan: Semua akun & role dapat menambah data. Edit/hapus hanya oleh admin/super-admin.
            </p>
          </div>
        </form>
      @else
        <div class="text-sm text-slate-600">
          Untuk menambahkan maintenance, silakan <a href="{{ route('login') }}" class="text-sky-600 font-medium underline">login</a>.
        </div>
      @endauth
    </section>
  </main>

  <a href="#"
     class="fixed bottom-5 right-5 rounded-full bg-white border border-slate-200 shadow-lg w-11 h-11 flex items-center justify-center text-slate-700">
     ↑
  </a>
  <script>
    const fm = document.getElementById('fm');
    if (fm) {
      fm.addEventListener('submit', (e) => {
        const btn = fm.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';
      });
    }
  </script>
</body>
</html>
