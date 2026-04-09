<?php
namespace App\Exports;

use App\Models\RoomDevice;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RoomDeviceExport implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
    protected $filters;
    protected $room;

    public function __construct($room, array $filters = [])
    {
        $this->room = $room;
        $this->filters = $filters;
    }

    public function array(): array
    {
        $query = $this->room->devices()->latest();

        if (!empty($this->filters['search'])) {
            $query->where('name', 'like', '%' . $this->filters['search'] . '%');
        }

        if (!empty($this->filters['condition'])) {
            switch ($this->filters['condition']) {
                case 'broken':
                    $query->where('broken_qty', '>', 0);
                    break;
                case 'consumed':
                    $query->where('consumed_qty', '>', 0);
                    break;
                case 'lost':
                    $query->where('lost_qty', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->whereRaw('quantity - broken_qty - consumed_qty - lost_qty <= 0');
                    break;
            }
        }

        $records = $query->get();
        $rows = [];

        // Header row
        $rows[] = [
            'Tên thiết bị',
            'ĐVT',
            'Tổng SL',
            'Hỏng',
            'Tiêu hao',
            'Mất',
            'Còn dùng',
            'Ghi chú',
        ];

        // Data rows
        $totalQty = 0;
        $totalBroken = 0;
        $totalConsumed = 0;
        $totalLost = 0;
        $totalAvailable = 0;

        foreach ($records as $index => $item) {
            $available = $item->availableQty();
            
            $rows[] = [
                $item->name,
                $item->unit,
                $item->quantity,
                $item->broken_qty,
                $item->consumed_qty,
                $item->lost_qty,
                $available,
                $item->note ?? '—',
            ];

            $totalQty += $item->quantity;
            $totalBroken += $item->broken_qty;
            $totalConsumed += $item->consumed_qty;
            $totalLost += $item->lost_qty;
            $totalAvailable += $available;
        }

        // Summary row
        $rows[] = [
            'TỔNG CỘNG',
            '',
            $totalQty,
            $totalBroken,
            $totalConsumed,
            $totalLost,
            $totalAvailable,
            '',
        ];

        return $rows;
    }

    public function title(): string
    {
        return 'Thiết bị phòng - ' . $this->room->name;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestDataRow();
                $highestColumn = $sheet->getHighestDataColumn();
                $range = 'A1:' . $highestColumn . $highestRow;

                // Style header row
                $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => '000000']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Bold for the summary row (last row)
                $sheet->getStyle('A' . $highestRow . ':' . $highestColumn . $highestRow)->getFont()->setBold(true);

                // Border for whole table
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                // Center align columns: ĐVT, SL, Hỏng, Tiêu hao, Mất, Còn dùng
                foreach (['B', 'C', 'D', 'E', 'F', 'G'] as $col) {
                    $sheet->getStyle($col . '2:' . $col . $highestRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Wrap text for Tên thiết bị and Ghi chú
                $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setWrapText(true);
                $sheet->getStyle('H2:H' . $highestRow)->getAlignment()->setWrapText(true);

                // ---------- Signature block ----------
                $dateRow = $highestRow + 4;
                $signRow  = $highestRow + 5;

                $dateText = '............, ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y');

                // Right: QUẢN LÝ THIẾT BỊ
                $sheet->mergeCells('E' . $dateRow . ':H' . $dateRow);
                $sheet->setCellValue('E' . $dateRow, $dateText);
                $sheet->getStyle('E' . $dateRow)->getFont()->setItalic(true);
                $sheet->getStyle('E' . $dateRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A' . $signRow . ':D' . $signRow);
                $sheet->setCellValue('A' . $signRow, 'BAN GIÁM HIỆU');
                $sheet->getStyle('A' . $signRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $signRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('E' . $signRow . ':H' . $signRow);
                $sheet->setCellValue('E' . $signRow, 'QUẢN LÝ THIẾT BỊ');
                $sheet->getStyle('E' . $signRow)->getFont()->setBold(true);
                $sheet->getStyle('E' . $signRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
