<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LessonReportSummarySheet implements FromArray, WithTitle, WithEvents
{
    protected $reports;

    public function __construct(Collection $reports)
    {
        $this->reports = $reports;
    }

    public function array(): array
    {
        $statsGrades = [
            'Toán'      => [6=>0, 7=>0, 8=>0, 9=>0],
            'Lý'        => [6=>0, 7=>0, 8=>0, 9=>0],
            'Hóa'       => [6=>0, 7=>0, 8=>0, 9=>0],
            'Sinh'      => [6=>0, 7=>0, 8=>0, 9=>0],
            'Công nghệ' => [6=>0, 7=>0, 8=>0, 9=>0],
        ];

        foreach ($this->reports as $report) {
            // grades
            $subjRaw = strtolower(trim($report->subject ?? $report->teacher->teaching_subject ?? ''));
            $matchedSubj = null;
            if (str_contains($subjRaw, 'toán') || str_contains($subjRaw, 'toan')) $matchedSubj = 'Toán';
            elseif (str_contains($subjRaw, 'lý') || str_contains($subjRaw, 'ly')) $matchedSubj = 'Lý';
            elseif (str_contains($subjRaw, 'hóa') || str_contains($subjRaw, 'hoa')) $matchedSubj = 'Hóa';
            elseif (str_contains($subjRaw, 'sinh')) $matchedSubj = 'Sinh';
            elseif (str_contains($subjRaw, 'công nghệ') || str_contains($subjRaw, 'cong nghe')) $matchedSubj = 'Công nghệ';

            if ($matchedSubj) {
                $cname = trim($report->class_name ?? '');
                // Bắt linh hoạt: "6A1", "Lớp 6/2", "8A3"...
                if (preg_match('/([6-9])/', $cname, $matches)) {
                    $grade = (int) $matches[1];
                    // TÍNH TỔNG SỐ TIẾT DẠY
                    $statsGrades[$matchedSubj][$grade] += $report->period_count;
                }
            }
        }

        $rows = [];
        $rows[] = ['Môn', "Lớp 6\n(số tiết)", "Lớp 7\n(số tiết)", "Lớp 8\n(số tiết)", "Lớp 9\n(số tiết)", "Tổng cộng\n(số tiết)"];
        $totalsGrade = [6=>0, 7=>0, 8=>0, 9=>0, 'row'=>0];
        
        $formatNum = function($num) {
            return str_pad($num, 2, '0', STR_PAD_LEFT);
        };

        foreach ($statsGrades as $sub => $grades) {
            $rowSum = array_sum($grades);
            $rows[] = [
                $sub, 
                $formatNum($grades[6]), 
                $formatNum($grades[7]), 
                $formatNum($grades[8]), 
                $formatNum($grades[9]), 
                $formatNum($rowSum)
            ];
            $totalsGrade[6] += $grades[6];
            $totalsGrade[7] += $grades[7];
            $totalsGrade[8] += $grades[8];
            $totalsGrade[9] += $grades[9];
            $totalsGrade['row'] += $rowSum;
        }

        $rows[] = [
            'TỔNG CỘNG', 
            $formatNum($totalsGrade[6]), 
            $formatNum($totalsGrade[7]), 
            $formatNum($totalsGrade[8]), 
            $formatNum($totalsGrade[9]), 
            $formatNum($totalsGrade['row'])
        ];

        return $rows;
    }

    public function title(): string
    {
        return 'Tổng hợp';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestDataRow();

                // Format Table header: nền trắng, chữ đen, in đậm
                $sheet->getStyle('A1:F1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => '000000']],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                ]);
                
                // Môn column -> Center / Left? User image has Top/Center align or Left. Let's center everything like previous.
                $sheet->getStyle('A1:A'.$highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B2:F'.$highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Add Borders to Table
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ];
                $sheet->getStyle('A1:F'.$highestRow)->applyFromArray($styleArray);

                // Last row (Tổng cộng)
                $sheet->getStyle("A$highestRow:F$highestRow")->getFont()->setBold(true);

                // Auto size cols
                foreach (range('A','F') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Signatures
                $dateRow = $highestRow + 4;
                $signatureRow = $highestRow + 5;
                
                $sheet->mergeCells('A' . $signatureRow . ':C' . $signatureRow);
                $sheet->setCellValue('A' . $signatureRow, 'BAN GIÁM HIỆU');
                $sheet->getStyle('A' . $signatureRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $signatureRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                $dateText = '............, ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y');
                $sheet->mergeCells('D' . $dateRow . ':F' . $dateRow);
                $sheet->setCellValue('D' . $dateRow, $dateText);
                $sheet->getStyle('D' . $dateRow)->getFont()->setItalic(true);
                $sheet->getStyle('D' . $dateRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                $sheet->mergeCells('D' . $signatureRow . ':F' . $signatureRow);
                $sheet->setCellValue('D' . $signatureRow, 'QUẢN LÝ THIẾT BỊ');
                $sheet->getStyle('D' . $signatureRow)->getFont()->setBold(true);
                $sheet->getStyle('D' . $signatureRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
