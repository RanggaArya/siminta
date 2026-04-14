<?php

namespace App\Filament\Resources\PengajuanMaintenances\Schemas;

use App\Models\Perangkat;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;

class PengajuanMaintenanceForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('perangkat_id')
                    ->label('Perangkat (Nomor Inventaris)')
                    ->searchable()
                    ->required()
                    ->getSearchResultsUsing(function (string $query) {
                        return Perangkat::query()
                            ->with(['kondisi'])
                            ->select(['id', 'nomor_inventaris', 'nama_perangkat', 'tipe', 'kondisi_id'])
                            ->where('nomor_inventaris', 'like', "%{$query}%")
                            ->orWhere('nama_perangkat', 'like', "%{$query}%")
                            ->orWhere('tipe', 'like', "%{$query}%")
                            ->orderBy('nomor_inventaris')
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(function ($p) {
                                return [$p->id => "{$p->nomor_inventaris}"];
                            })->toArray();
                    })
                    ->getOptionLabelUsing(function ($value) {
                        $p = Perangkat::find($value);
                        return $p
                            ? "{$p->nomor_inventaris} — {$p->nama_perangkat}" . ($p->tipe ? " ({$p->tipe})" : '')
                            : null;
                    })
                    ->live() // Gunakan live() di v3 menggantikan reactive()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $p = Perangkat::with('kondisi')->find($state);
                        
                        if ($p) {
                            $set('nomor_inventaris', $p->nomor_inventaris); // Field hidden jika perlu disimpan
                            $set('nama_perangkat', $p->nama_perangkat);
                            $set('tipe', $p->tipe);
                            // Asumsi ada field untuk menampilkan kondisi terakhir jika diperlukan
                        } else {
                            $set('nama_perangkat', null);
                            $set('tipe', null);
                        }

                        if (blank($state)) {
                            $set('lokasi_id', null);
                            return;
                        }

                        $perangkat = Perangkat::select(['id', 'lokasi_id'])->find($state);
                        if ($perangkat) {
                            $set('lokasi_id', $perangkat->lokasi_id);
                        }
                    })
                    ->default(fn() => request()->query('perangkat_id'))
                    ->rules(['required', 'exists:perangkats,id']),

                TextInput::make('nama_perangkat')
                    ->label('Nama Perangkat')
                    ->disabled()
                    ->dehydrated(), // Tetap dikirim ke server meski disabled

                TextInput::make('tipe')
                    ->label('Tipe')
                    ->disabled()
                    ->dehydrated(),

                Select::make('lokasi_id')
                    ->label('Lokasi Ruangan')
                    ->relationship('lokasi', 'nama_lokasi')
                    ->searchable()
                    ->preload()
                    ->dehydrated(),

                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->maxLength(350)
                    ->columnSpanFull(), // columnSpan('full') -> columnSpanFull() di v3
            ]);
    }
}