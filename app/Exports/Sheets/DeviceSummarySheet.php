<?php

namespace App\Exports\Sheets;

use App\Models\Device;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class DeviceSummarySheet implements FromQuery, WithTitle, WithHeadings, WithMapping, WithStyles, WithEvents, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = is_array($filters) ? $filters : ['subject_group' => $filters];
    }

    public function query()
    {
        $query = Device::query();
        
        if (!empty($this->filters['subject_group'])) {
            $query->where('subject_group', $this->filters['subject_group']);
        }

        if (!empty($this->filters['devices'])) {
            $query->whereIn('id', $this->filters['devices']);
        }

        if (!empty($this->filters['import_month'])) {
            $query->whereMonth('created_at', $this->filters['import_month']);
        }
        if (!empty($this->filters['import_year'])) {
            $query->whereYear('created_at', $this->filters['import_year']);
        }

        return $query;
    }

    public function title(): string
    {
        return 'Tổng hợp thiết bị';
    }

    public function headings(): array
    {
        return [
            'Mã TB',
            'Tên thiết bị',
            'Môn học',
            'Tổ chuyên môn',
            'Đơn giá',
            'Tổng SL',
            'Hỏng',
            'Mất',
            'Đang mượn',
            'Còn lại',
            'Thành tiền',
        ];
    }

    public function map($device): array
    {
        return [
            $device->code,
            $device->name,
            $device->subject,
            $device->subject_group,
            $device->unit_price,
            $device->quantity,
            $device->damaged_qty,
            $device->lost_qty,
            $device->borrowedQty(),
            $device->remainingQty(),
            $device->totalValue(),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => '000000']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => 'FFFFFF']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestDataRow(); // Row number of the last data row
                
                $totalsRow = $highestRow + 1;
                
                // Add totals label
                $sheet->setCellValue('E' . $totalsRow, 'Tổng cộng:');
                $sheet->getStyle('E' . $totalsRow)->getFont()->setBold(true);
                $sheet->getStyle('E' . $totalsRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                
                // Formula columns
                $columnsToSum = ['F', 'G', 'H', 'I', 'J', 'K'];
                foreach ($columnsToSum as $col) {
                    $sheet->setCellValue($col . $totalsRow, '=SUM(' . $col . '2:' . $col . $highestRow . ')');
                }
                $sheet->getStyle('F' . $totalsRow . ':K' . $totalsRow)->getFont()->setBold(true);
                
                // Signatures
                $dateRow = $totalsRow + 3;
                $signatureRow = $totalsRow + 4;
                
                $sheet->mergeCells('B' . $signatureRow . ':D' . $signatureRow);
                $sheet->setCellValue('B' . $signatureRow, 'BAN GIÁM HIỆU');
                $sheet->getStyle('B' . $signatureRow)->getFont()->setBold(true);
                $sheet->getStyle('B' . $signatureRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                $dateText = '............, ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y');
                $sheet->mergeCells('H' . $dateRow . ':K' . $dateRow);
                $sheet->setCellValue('H' . $dateRow, $dateText);
                $sheet->getStyle('H' . $dateRow)->getFont()->setItalic(true);
                $sheet->getStyle('H' . $dateRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                $sheet->mergeCells('H' . $signatureRow . ':K' . $signatureRow);
                $sheet->setCellValue('H' . $signatureRow, 'QUẢN LÝ THIẾT BỊ');
                $sheet->getStyle('H' . $signatureRow)->getFont()->setBold(true);
                $sheet->getStyle('H' . $signatureRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
