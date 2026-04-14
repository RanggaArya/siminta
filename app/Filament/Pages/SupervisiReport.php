<?php

namespace App\Filament\Pages;

use App\Models\Supervisi;
use App\Filament\Exports\SupervisiExporter;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use Filament\Tables\Actions\Action;
// Tambahan import untuk fitur Delete & Bulk Delete
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class SupervisiReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $slug = 'laporan-supervisi';

    protected static ?string $navigationIcon = 'heroicon-s-computer-desktop';
    protected static ?string $navigationGroup = 'Manajemen Inventaris';
    protected static ?string $navigationLabel = 'Laporan Supervisi';
    protected static ?string $title = 'Laporan Supervisi';
    protected static string $view = 'filament.pages.supervisi-report';

  public function table(Table $table): Table
  {
    return $table
      ->query(Supervisi::query()->with(['user', 'perangkat']))
      ->columns([
        TextColumn::make('user.name')
          ->label('User')
          ->sortable()
          ->searchable(),
        TextColumn::make('perangkat.nomor_inventaris')
          ->label('Nomor Inventaris')
          ->sortable()
          ->searchable(),
        TextColumn::make('perangkat.nama_perangkat')
          ->label('Nama Perangkat')
          ->sortable()
          ->searchable(),
        TextColumn::make('tanggal')
          ->label('Tanggal')
          ->dateTime('d M Y H:i')
          ->sortable()
          ->searchable(),
        TextColumn::make('keterangan')
          ->label('Keterangan')
          ->limit(50)
          ->searchable(),
      ])
      ->filters([
        SelectFilter::make('user_id')
          ->label('Filter User')
          ->options(User::all()->pluck('name', 'id'))
          ->searchable()
          ->columnSpanFull(),

        Filter::make('tanggal')
          ->form([
            Grid::make(2)
              ->schema([
                DatePicker::make('bulan_tahun_dari')
                  ->label('Dari Bulan/Tahun')
                  ->displayFormat('d/m/Y')
                  ->format('Y-m-d')
                  ->default(now()->startOfMonth())
                  ->native(false)
                  ->suffixIcon('heroicon-m-calendar-days')
                  ->columnSpan(1),

                DatePicker::make('bulan_tahun_sampai')
                  ->label('Sampai Bulan/Tahun')
                  ->displayFormat('d/m/Y')
                  ->format('Y-m-d')
                  ->default(now()->endOfMonth())
                  ->native(false)
                  ->suffixIcon('heroicon-m-calendar-days')
                  ->columnSpan(1),
              ]),
          ])
          ->columnSpanFull()
          ->query(function (Builder $query, array $data): Builder {
            return $query
              ->when(
                $data['bulan_tahun_dari'] ?? null,
                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
              )
              ->when(
                $data['bulan_tahun_sampai'] ?? null,
                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
              );
          })
      ])
      ->persistFiltersInSession()
      ->persistSortInSession()
      ->persistSearchInSession()
      ->filtersFormColumns(1)
      ->filtersFormWidth('md')
      ->headerActions([
        Action::make('export')
          ->label('Export Data')
          ->color('success')
          ->icon('heroicon-o-arrow-down-tray')
          ->form([
            Grid::make(2)
              ->schema([
                Select::make('filter_type')
                  ->label('Filter Export')
                  ->options([
                    'all' => 'Semua Data',
                    'user' => 'Berdasarkan User',
                    'period' => 'Berdasarkan Periode',
                  ])
                  ->default('all')
                  ->required()
                  ->live()
                  ->columnSpanFull(),

                Select::make('user_id')
                  ->label('Pilih User')
                  ->options(User::all()->pluck('name', 'id'))
                  ->searchable()
                  ->visible(fn($get) => $get('filter_type') === 'user')
                  ->columnSpanFull(),

                DatePicker::make('start_date')
                  ->label('Dari Tanggal')
                  ->displayFormat('d/m/Y')
                  ->format('Y-m-d')
                  ->native(false)
                  ->visible(fn($get) => $get('filter_type') === 'period')
                  ->default(now()->startOfMonth())
                  ->columnSpan(1),

                DatePicker::make('end_date')
                  ->label('Sampai Tanggal')
                  ->displayFormat('d/m/Y')
                  ->format('Y-m-d')
                  ->native(false)
                  ->visible(fn($get) => $get('filter_type') === 'period')
                  ->default(now()->endOfMonth())
                  ->columnSpan(1),
              ]),
          ])
          ->action(function (array $data) {
            $params = [];

            if ($data['filter_type'] === 'user' && !empty($data['user_id'])) {
              $params['user_id'] = $data['user_id'];
            }

            if ($data['filter_type'] === 'period') {
              if (!empty($data['start_date'])) {
                $params['start_date'] = $data['start_date'];
              }
              if (!empty($data['end_date'])) {
                $params['end_date'] = $data['end_date'];
              }
            }

            return redirect()->route('export.supervisi.excel', $params);
          })
      ])
      // =========================================================
      // TAMBAHAN: Action Hapus Satuan & Bulk Hapus
      // =========================================================
      ->actions([
          DeleteAction::make()
              ->label('Hapus')
              ->modalHeading('Hapus Data Supervisi')
              ->modalDescription('Apakah Anda yakin ingin menghapus data supervisi ini? Data yang dihapus tidak dapat dikembalikan.'),
      ])
      ->bulkActions([
          BulkActionGroup::make([
              DeleteBulkAction::make()
                  ->label('Hapus Data Terpilih')
                  ->modalHeading('Hapus Data Terpilih')
                  ->modalDescription('Apakah Anda yakin ingin menghapus semua data supervisi yang dipilih? Data yang dihapus tidak dapat dikembalikan.'),
          ]),
      ])
      // =========================================================
      ->defaultSort('tanggal', 'desc');
  }
}