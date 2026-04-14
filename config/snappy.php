<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Snappy PDF options
    |--------------------------------------------------------------------------
    |
    | Halaman opsi untuk PDF.
    |
    */
    'pdf' => [
        'enabled' => true,
        
        /**
         * Path ke binary wkhtmltopdf.
         * * PENTING: Sesuaikan path ini dengan lokasi instalasi di server/komputer Anda.
         * * Contoh Windows: 'binary' => '"C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"',
         * Contoh Linux/MacOS: 'binary' => '/usr/local/bin/wkhtmltopdf',
         * * Jika wkhtmltopdf ada di PATH sistem Anda, Anda bisa coba:
         * 'binary' => 'wkhtmltopdf', 
         * (tapi menggunakan path absolut lebih aman)
         */
        'binary' => '"C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"', // <-- GANTI BAGIAN INI

        'timeout' => false,
        'options' => [],
        'env'     => [],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Snappy Image options
    |--------------------------------------------------------------------------
    |
    | Halaman opsi untuk Image (wkhtmltoimage).
    |
    */
    'image' => [
        'enabled' => true,
        
        /**
         * Path ke binary wkhtmltoimage.
         * * PENTING: Sesuaikan path ini dengan lokasi instalasi di server/komputer Anda.
         * * Contoh Windows: 'binary' => '"C:\Program Files\wkhtmltopdf\bin\wkhtmltoimage.exe"',
         * Contoh Linux/MacOS: 'binary' => '/usr/local/bin/wkhtmltoimage',
         */
        'binary' => '"C:\Program Files\wkhtmltopdf\bin\wkhtmltoimage.exe"', // <-- GANTI BAGIAN INI

        'timeout' => false,
        'options' => [],
        'env'     => [],
    ],

];