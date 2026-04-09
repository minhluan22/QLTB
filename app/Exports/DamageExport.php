<?php

namespace App\Exports;

use App\Models\Damage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class DamageExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Damage::with(['device', 'reporter'])->latest();

        // 1. Lọc theo loại sự cố
        if (!empty($this->filters['damage_type']) && $this->filters['damage_type'] !== 'all') {
            $query->where('damage_type', $this->filters['damage_type']);
        }

        // 2. Lọc theo danh sách thiết bị
        if (!empty($this->filters['device_ids'])) {
            $query->whereIn('device_id', (array) $this->filters['device_ids']);
        }

        // 3. Lọc theo ngày báo
        if (!empty($this->filters['from_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['from_date']);
        }
        if (!empty($this->filters['to_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['to_date']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Tên thiết bị',
            'Mã thiết bị',
            'Loại sự cố',
            'Số lượng',
            'Ngày phát hiện',
            'Nguyên nhân',
            'Mức độ',
            'Hướng xử lý',
            'Người báo',
            'Ngày báo'
        ];
    }

    /**
     * Chuyển đổi mức độ hỏng sang tiếng Việt
     */
    protected function translateSeverity(?string $severity): string
    {
        return match($severity) {
            'minor'    => 'Hỏng nhẹ (sửa được)',
            'moderate' => 'Hỏng vừa (thay linh kiện)',
            'severe'   => 'Hỏng nặng (không sửa được)',
            default    => '—',
        };
    }

    /**
     * Chuyển đổi loại sự cố sang tiếng Việt
     */
    protected function translateDamageType(?string $type): string
    {
        return match($type) {
            'hỏng' => 'Hỏng',
            'mất'  => 'Mất',
            default => $type ?? '—',
        };
    }

    public function map($damage): array
    {
        return [
            $damage->device ? $damage->device->name : 'N/A',
            $damage->device ? $damage->device->code : 'N/A',
            $this->translateDamageType($damage->damage_type),
            $damage->quantity,
            $damage->detected_date ? Carbon::parse($damage->detected_date)->format('d/m/Y') : '—',
            $damage->cause ?? '—',
            $this->translateSeverity($damage->severity),
            $damage->resolution ?? '—',
            $damage->reporter ? $damage->reporter->name : 'N/A',
            $damage->created_at ? Carbon::parse($damage->created_at)->format('d/m/Y') : '—',
        ];
    }

    public function title(): string
    {
        return 'Tổng hợp sự cố';
    }

    public function styles(Worksheet $sheet)
    {
        // Header: nền trắng, chữ đen, in đậm
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
                
                // Formula columns D
                $sheet->setCellValue('D' . $totalsRow, '=SUM(D2:D' . $highestRow . ')');
                $sheet->getStyle('D' . $totalsRow)->getFont()->setBold(true);
                
                // Signatures
                $dateRow = $totalsRow + 3;
                $signatureRow = $totalsRow + 4;
                
                $sheet->mergeCells('B' . $signatureRow . ':D' . $signatureRow);
                $sheet->setCellValue('B' . $signatureRow, 'BAN GIÁM HIỆU');
                $sheet->getStyle('B' . $signatureRow)->getFont()->setBold(true);
                $sheet->getStyle('B' . $signatureRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                $dateText = '............, ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y');
                $sheet->mergeCells('H' . $dateRow . ':J' . $dateRow);
                $sheet->setCellValue('H' . $dateRow, $dateText);
                $sheet->getStyle('H' . $dateRow)->getFont()->setItalic(true);
                $sheet->getStyle('H' . $dateRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                $sheet->mergeCells('H' . $signatureRow . ':J' . $signatureRow);
                $sheet->setCellValue('H' . $signatureRow, 'QUẢN LÝ THIẾT BỊ');
                $sheet->getStyle('H' . $signatureRow)->getFont()->setBold(true);
                $sheet->getStyle('H' . $signatureRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
