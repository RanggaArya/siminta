<?php

namespace App\Filament\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class MutasiResumeExport implements FromView, WithTitle, ShouldAutoSize, WithEvents
{
    protected $data;
    protected $grandTotal;
    protected $periodeLabel;

    public function __construct($data, $grandTotal, $periodeLabel = null)
    {
        $this->data = $data;
        $this->grandTotal = $grandTotal;
        $this->periodeLabel = $periodeLabel;
    }

    public function view(): View
    {
        return view('exports.mutasi-resume-excel', [
            'data' => $this->data,
            'grandTotal' => $this->grandTotal,
            'periodeLabel' => $this->periodeLabel,
        ]);
    }

    public function title(): string
    {
        return 'Resume Mutasi Perangkat';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $event->sheet->getDelegate()->getPageSetup()->setFitToWidth(1);
                $event->sheet->getDelegate()->getPageSetup()->setFitToHeight(0);
            },
        ];
    }
}
