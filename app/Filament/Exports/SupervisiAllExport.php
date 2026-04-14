<?php

namespace App\Filament\Exports;

use App\Models\Supervisi;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SupervisiAllExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithEvents
{
    private int $no = 0;
    private ?string $password;
    private ?int $userId;
    private ?string $startDate;
    private ?string $endDate;

    public function __construct(
        ?string $password = null,
        ?int $userId = null,
        ?string $startDate = null,
        ?string $endDate = null
    ) {
        $this->password = $password;
        $this->userId = $userId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query(): Builder
    {
        $query = Supervisi::query()
            ->with(['user', 'perangkat'])
            ->orderBy('tanggal', 'desc');

        // Filter by user
        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        // Filter by date range
        if ($this->startDate) {
            $query->whereDate('tanggal', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('tanggal', '<=', $this->endDate);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'User',
            'Nomor Inventaris',
            'Nama Perangkat',
            'Tanggal',
            'Keterangan',
        ];
    }

    public function map($row): array
    {
        $this->no++;

        return [
            $this->no,
            $row->user?->name,
            $row->perangkat?->nomor_inventaris,
            $row->perangkat?->nama_perangkat,
            optional($row->tanggal)->format('d-m-Y H:i'),
            $row->keterangan,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Insert title row
                $sheet->insertNewRowBefore(1, 1);
                $title = 'LAPORAN SUPERVISI ALAT KESEHATAN';
                $titleRange = 'A1:' . $highestColumn . '1';
                $sheet->mergeCells($titleRange);
                $sheet->setCellValue('A1', $title);

                // Style title
                $titleStyle = $sheet->getStyle($titleRange);
                $titleStyle->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFFFFF00');
                $titleStyle->getFont()->setBold(true)->setSize(13);
                $titleStyle->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension('1')->setRowHeight(22);

                // Style header
                $headerRange = 'A2:' . $highestColumn . '2';
                $headerStyle = $sheet->getStyle($headerRange);
                $headerStyle->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FF1F4E78');
                $headerStyle->getFont()
                    ->setBold(true)
                    ->setSize(11)
                    ->getColor()
                    ->setARGB('FFFFFFFF');
                $headerStyle->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                $headerStyle->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Alternating row colors
                $newHighestRow = $sheet->getHighestRow();
                for ($row = 3; $row <= $newHighestRow; $row++) {
                    if ($row % 2 == 1) {
                        $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB('FFF2F2F2');
                    }
                    $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }

                // Center align No column
                $sheet->getStyle('A3:A' . $newHighestRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Freeze pane and autofilter
                $sheet->freezePane('A3');
                $sheet->setAutoFilter('A2:' . $highestColumn . '2');

                // Password protect
                if ($this->password) {
                    $sheet->getProtection()->setPassword($this->password);
                    $sheet->getProtection()->setSheet(true);
                }
            },
        ];
    }
}