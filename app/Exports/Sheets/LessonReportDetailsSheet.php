<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class LessonReportDetailsSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle, WithEvents
{
    protected $reports;

    public function __construct(Collection $reports)
    {
        $this->reports = $reports;
    }

    public function collection()
    {
        return $this->reports;
    }

    public function title(): string
    {
        return 'Chi tiết';
    }

    public function headings(): array
    {
        return [
            'Ngày dạy',
            'Phòng',
            'Giáo viên',
            'Buổi',
            'Lớp',
            'Môn',
            'Số tiết',
            'Ghi chú GV',
            'Sự cố',
            'Sử dụng thiết bị',
        ];
    }

    public function map($report): array
    {
        $dashIfEmpty = function($val) {
            return (empty($val) || $val === 'N/A') ? '—' : $val;
        };

        $usedDevices = $report->deviceUsages->map(function($usage) {
            return ($usage->device->name ?? 'Không rõ') . ' (SL: ' . $usage->quantity_used . ')';
        })->implode(', ');

        return [
            $report->lesson_date ? $report->lesson_date->format('d/m/Y') : '—',
            $dashIfEmpty($report->room->name ?? null),
            $dashIfEmpty($report->teacher->name ?? null),
            $report->session === 'sang' ? 'Sáng' : 'Chiều',
            $dashIfEmpty($report->class_name),
            $dashIfEmpty($report->subject),
            $report->period_count ?: '—',
            $dashIfEmpty($report->teacher_note),
            $report->hasIssues() ? 'Có sự cố' : '—',
            $dashIfEmpty($usedDevices),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFFFF']]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestDataRow();
                
                $totalsRow = $highestRow + 1;
                
                // Add totals label
                $sheet->setCellValue('F' . $totalsRow, 'Tổng cộng:');
                $sheet->getStyle('F' . $totalsRow)->getFont()->setBold(true);
                $sheet->getStyle('F' . $totalsRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                
                // Sum 'Số tiết' (Column G)
                $sheet->setCellValue('G' . $totalsRow, '=SUM(G2:G' . $highestRow . ')');
                $sheet->getStyle('G' . $totalsRow)->getFont()->setBold(true);
                
                // Signatures
                $dateRow = $totalsRow + 4;
                $signatureRow = $totalsRow + 5;
                
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
