<?php

namespace App\Exports;

use App\Models\DeviceProposal;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DeviceProposalExport implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function array(): array
    {
        $query = DeviceProposal::with('user')->latest();

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['device_name'])) {
            $query->where('device_name', 'like', '%' . $this->filters['device_name'] . '%');
        }
        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }
        if (!empty($this->filters['month'])) {
            $query->whereMonth('created_at', $this->filters['month']);
        }
        if (!empty($this->filters['year'])) {
            $query->whereYear('created_at', $this->filters['year']);
        }

        $records = $query->get();

        $rows = [];

        // Header row
        $rows[] = [
            'Ngày Tạo',
            'Giáo Viên Đề Xuất',
            'Tên Thiết Bị',
            'Số Lượng',
            'Loại Thiết Bị',
            'Môn',
            'Trạng Thái',
            'Ghi Chú / Lý Do Từ Chối',
        ];

        // Data rows
        foreach ($records as $item) {
            $statusMap = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối'];
            $rows[] = [
                $item->created_at->format('d/m/Y'),   // Chỉ ngày, không có giờ
                $item->user->name ?? 'N/A',
                $item->device_name,
                $item->quantity,
                $item->category ?? '—',
                $item->subject ?? '—',
                $statusMap[$item->status] ?? $item->status,
                trim(($item->note ?? '') . ($item->reject_reason ? "\nTừ chối: " . $item->reject_reason : '')),
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Danh sách đề xuất';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestDataRow();

                // Style header row
                $sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => '000000']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                // Border for whole table
                $sheet->getStyle('A1:H' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                // Center align columns: Ngày, Số lượng, Trạng thái
                foreach (['A', 'D', 'G'] as $col) {
                    $sheet->getStyle($col . '2:' . $col . $highestRow)
                        ->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                }

                // Wrap text for long columns
                $sheet->getStyle('E2:E' . $highestRow)->getAlignment()->setWrapText(true);
                $sheet->getStyle('H2:H' . $highestRow)->getAlignment()->setWrapText(true);

                // ---------- Signature block ----------
                $dateRow = $highestRow + 4;
                $signRow  = $highestRow + 5;

                $dateText = '............, ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y');

                // Left: BGH
                $sheet->mergeCells('A' . $dateRow . ':D' . $dateRow);
                $sheet->setCellValue('A' . $dateRow, $dateText);
                $sheet->getStyle('A' . $dateRow)->getFont()->setItalic(true);
                $sheet->getStyle('A' . $dateRow)->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A' . $signRow . ':D' . $signRow);
                $sheet->setCellValue('A' . $signRow, 'BAN GIÁM HIỆU');
                $sheet->getStyle('A' . $signRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $signRow)->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Right: Quản lý thiết bị
                $sheet->mergeCells('E' . $dateRow . ':H' . $dateRow);
                $sheet->setCellValue('E' . $dateRow, $dateText);
                $sheet->getStyle('E' . $dateRow)->getFont()->setItalic(true);
                $sheet->getStyle('E' . $dateRow)->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('E' . $signRow . ':H' . $signRow);
                $sheet->setCellValue('E' . $signRow, 'QUẢN LÝ THIẾT BỊ');
                $sheet->getStyle('E' . $signRow)->getFont()->setBold(true);
                $sheet->getStyle('E' . $signRow)->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
