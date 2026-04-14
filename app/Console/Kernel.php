protected function schedule(Schedule $schedule)
{
    // Jalankan setiap hari pada pukul 01:00 pagi
    $schedule->command('inventaris:update-expired-harga')->dailyAt('01:00');
}