<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Cetak Semua Stiker Inventaris</title>
  <link rel="shortcut icon" href="{{ asset('images/RSU.png') }}" type="image/x-icon">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 10px;
      background: #f4f4f4;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
    }

    .sticker-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
      gap: 15px;
      margin-top: 15px;
    }

    .sticker {
      border: 2px solid #000;
      padding: 3mm;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      background: #fff;
      aspect-ratio: 10 / 3;
    }

    .sticker-header {
      text-align: center;
      font-weight: bold;
      font-size: 10pt;
      border-bottom: 2px solid #000;
      padding-bottom: 2px;
      margin-bottom: 3px;
    }

    .sticker-body {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 4mm;
      flex: 1;
    }

    .left-section {
      display: flex;
      align-items: center;
      gap: 3mm;
      flex: 1;
      min-width: 0;
    }

    .stiker-logo {
      flex-shrink: 0;
    }

    .stiker-logo img {
      width: 12mm;
      height: auto;
      display: block;
    }

    .info-section {
      font-size: 8pt;
      line-height: 1.3;
      flex: 1;
      min-width: 0;
    }

    .info-section p {
      margin: 0.5mm 0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .info-section strong {
      font-weight: bold;
    }

    .barcode-section {
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      border-left: 1px solid #ccc;
      padding-left: 3mm;
    }

    .barcode-section svg,
    .barcode-section img {
      width: 22mm;
      height: 22mm;
      display: block;
    }

    .no-print {
      text-align: center;
      padding: 15px;
      background: #fff;
      border-radius: 5px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .print-button {
      background: #007bff;
      color: white;
      border: none;
      padding: 12px 25px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
      font-size: 14px;
      margin: 0 5px;
    }

    .print-button:hover {
      background: #0056b3;
    }

    .print-button.secondary {
      background: #28a745;
    }

    .print-button.secondary:hover {
      background: #218838;
    }

    .info-box {
      background: #e7f3ff;
      border: 1px solid #0066cc;
      border-radius: 5px;
      padding: 10px;
      margin-top: 10px;
      font-size: 13px;
      color: #004085;
    }

    @media print {
      * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }

      html,
      body {
        margin: 0;
        padding: 0;
        background: #fff;
        width: 100mm;
        height: 30mm;
      }

      .no-print {
        display: none !important;
      }

      .container {
        max-width: none;
        padding: 0;
        margin: 0;
      }

      .sticker-grid {
        display: block;
        margin: 0;
        padding: 0;
      }

      @page {
        size: 100mm 30mm;
        margin: 1.5mm;
      }

      .sticker {
        width: 97mm;
        height: 27mm;
        border: 1.5px solid #000;
        padding: 1.2mm;
        margin: 0;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        page-break-after: always;
        page-break-inside: avoid;
        page-break-before: always;
        overflow: hidden;
        position: relative;
      }

      .sticker:first-child {
        page-break-before: auto;
        margin-top: 0;
      }

      .sticker:last-child {
        page-break-after: auto;
      }

      .sticker-header {
        font-size: 6.5pt;
        font-weight: bold;
        text-align: center;
        padding-bottom: 0.3mm;
        margin-bottom: 0.6mm;
        border-bottom: 1.5px solid #000;
        flex-shrink: 0;
        line-height: 1.1;
      }

      .sticker-body {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.2mm;
        flex: 1;
        min-height: 0;
      }

      .left-section {
        display: flex;
        align-items: center;
        gap: 1.2mm;
        flex: 1;
        min-width: 0;
      }

      .stiker-logo {
        flex-shrink: 0;
      }

      .stiker-logo img {
        width: 7.5mm;
        height: auto;
        display: block;
      }

      .info-section {
        font-size: 5.2pt;
        line-height: 1.35;
        flex: 1;
        min-width: 0;
      }

      .info-section p {
        margin: 0.25mm 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .barcode-section {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-left: 1px solid #999;
        padding-left: 1.2mm;
        padding-right: 0;
      }

      .barcode-section svg,
      .barcode-section img {
        width: 20mm !important;
        height: 20mm !important;
        display: block;
        max-width: 20mm !important;
        max-height: 20mm !important;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="no-print">
      <button onclick="window.print()" class="print-button">
        🖨️ Cetak ke Printer Roll Stiker
      </button>
      <button onclick="window.location.reload()" class="print-button secondary">
        🔄 Refresh Halaman
      </button>

      <div class="info-box">
        <strong>📌 Panduan Cetak:</strong><br>
        • Pastikan printer roll stiker sudah terpasang dengan kertas ukuran 10cm x 3cm<br>
        • Setiap stiker akan dicetak satu per satu secara berurutan<br>
        • Total stiker yang akan dicetak: <strong>{{ $records->count() }}</strong><br>
        • Pastikan setting printer: Paper Size = 100mm x 30mm, Margin = 0mm
      </div>
    </div>

    <div class="sticker-grid">
      @foreach ($records as $record)
        <div class="sticker">
          <div class="sticker-header">LABEL INVENTARIS IT</div>
          <div class="sticker-body">
            <div class="left-section">
              <div class="stiker-logo">
                <img src="{{ asset('images/RSU.png') }}" alt="logo">
              </div>
              <div class="info-section">
                <p><strong>Nama:</strong> {{ $record->nama_perangkat }}</p>
                <p><strong>Kode:</strong> {{ $record->nomor_inventaris }}</p>
                <p><strong>Lokasi:</strong> {{ $record->lokasi->nama_lokasi ?? 'N/A' }}</p>
              </div>
            </div>
    
            <div class="barcode-section">
              @php
                // Generate QR code per perangkat
                $url = route('public.perangkat.show', ['perangkat' => $record->id]);
                echo \SimpleSoftwareIO\QrCode\Facades\QrCode::size(75)->generate($url);
              @endphp
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</body>

</html>