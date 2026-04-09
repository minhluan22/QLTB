<?php

namespace App\Exports\Sheets;

use App\Models\Import;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class DeviceImportsSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, WithStyles, WithEvents, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = is_array($filters) ? $filters : ['subject_group' => $filters];
    }

    public function query()
    {
        $query = Import::with(['device', 'importer']);
        
        // Filter by conditions on the Device
        $query->whereHas('device', function (Builder $q) {
            if (!empty($this->filters['subject_group'])) {
                $q->where('subject_group', $this->filters['subject_group']);
            }
            if (!empty($this->filters['devices'])) {
                $q->whereIn('id', $this->filters['devices']);
            }
        });

        // Lọc tháng/năm trên bảng Imports theo cột import_date
        if (!empty($this->filters['import_month'])) {
            $query->whereMonth('import_date', $this->filters['import_month']);
        }
        if (!empty($this->filters['import_year'])) {
            $query->whereYear('import_date', $this->filters['import_year']);
        }

        return $query;
    }

    public function title(): string
    {
        return 'Chi tiết nhập kho';
    }

    public function headings(): array
    {
        return [
            'Mã TB',
            'Tên thiết bị',
            'Ngày nhập',
            'Số lượng nhập',
            'Nhà cung cấp',
            'Nhãn hiệu',
            'Xuất xứ',
            'Năm sản xuất',
            'Đơn giá',
            'Thành tiền',
            'Người nhập',
            'Ghi chú',
        ];
    }

    public function map($import): array
    {
        return [
            $import->device->code ?? '—',
            $import->device->name ?? '—',
            $import->import_date ? \Carbon\Carbon::parse($import->import_date)->format('d/m/Y') : '—',
            $import->quantity,
            $import->supplier,
            $import->brand,
            $import->country,
            $import->production_year,
            $import->price,
            $import->quantity * $import->price,
            $import->importer->name ?? '—',
            $import->note,
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
                $sheet->setCellValue('C' . $totalsRow, 'Tổng cộng:');
                $sheet->getStyle('C' . $totalsRow)->getFont()->setBold(true);
                $sheet->getStyle('C' . $totalsRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                
                // Formula columns D and J
                $columnsToSum = ['D', 'J'];
                foreach ($columnsToSum as $col) {
                    $sheet->setCellValue($col . $totalsRow, '=SUM(' . $col . '2:' . $col . $highestRow . ')');
                    $sheet->getStyle($col . $totalsRow)->getFont()->setBold(true);
                }
                
                // Signatures
                $dateRow = $totalsRow + 3;
                $signatureRow = $totalsRow + 4;
                
                $sheet->mergeCells('B' . $signatureRow . ':E' . $signatureRow);
                $sheet->setCellValue('B' . $signatureRow, 'BAN GIÁM HIỆU');
                $sheet->getStyle('B' . $signatureRow)->getFont()->setBold(true);
                $sheet->getStyle('B' . $signatureRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                $dateText = '............, ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y');
                $sheet->mergeCells('I' . $dateRow . ':K' . $dateRow);
                $sheet->setCellValue('I' . $dateRow, $dateText);
                $sheet->getStyle('I' . $dateRow)->getFont()->setItalic(true);
                $sheet->getStyle('I' . $dateRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                $sheet->mergeCells('I' . $signatureRow . ':K' . $signatureRow);
                $sheet->setCellValue('I' . $signatureRow, 'QUẢN LÝ THIẾT BỊ');
                $sheet->getStyle('I' . $signatureRow)->getFont()->setBold(true);
                $sheet->getStyle('I' . $signatureRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
