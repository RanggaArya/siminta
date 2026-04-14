<?php

namespace App\Filament\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents; 
use Maatwebsite\Excel\Events\AfterSheet; 
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup; 

class PerangkatResumeExport implements FromView, WithTitle, ShouldAutoSize, WithEvents
{
    protected $data;
    protected $grandTotal;
    protected $periodeLabel;
    protected ?string $password;

    public function __construct($data, $grandTotal, $periodeLabel = null, ?string $password = null)
    {
        $this->data = $data;
        $this->grandTotal = $grandTotal;
        $this->periodeLabel = $periodeLabel;
        $this->password = $password;
    }

    public function view(): View
    {
        return view('exports.perangkat-resume-excel', [
            'data' => $this->data,
            'grandTotal' => $this->grandTotal,
            'periodeLabel' => $this->periodeLabel,
        ]);
    }

    public function title(): string
    {
        return 'Resume Inventaris Perangkat';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $event->sheet->getDelegate()->getPageSetup()->setFitToWidth(1);
                $event->sheet->getDelegate()->getPageSetup()->setFitToHeight(0);

                if ($this->password) {
                    $event->sheet->getDelegate()->getProtection()->setPassword($this->password);
                    $event->sheet->getDelegate()->getProtection()->setSheet(true); 
                }
            },
        ];
    }
}
