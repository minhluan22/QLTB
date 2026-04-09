<?php

namespace App\Exports\Sheets;

use App\Models\BorrowRequest;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class BorrowDataSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = BorrowRequest::with(['user', 'borrowDetails.device', 'returnRecord'])
                    ->latest('borrow_date');

        // Lọc theo người dùng (giáo viên)
        if (!empty($this->filters['borrower'])) {
            $query->whereHas('user', function ($q) {
                // allow search by name or email
                $q->where('name', 'like', '%' . $this->filters['borrower'] . '%')
                  ->orWhere('email', 'like', '%' . $this->filters['borrower'] . '%');
            });
        }

        // Lọc theo tổ chuyên môn
        if (!empty($this->filters['department'])) {
            $query->whereHas('borrowDetails.device', function ($q) {
                $q->where('subject_group', $this->filters['department']);
            });
        }

        // Lọc theo trạng thái
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        // Lọc theo khoảng thời gian mượn
        if (!empty($this->filters['from_date'])) {
            $query->whereDate('borrow_date', '>=', $this->filters['from_date']);
        }
        if (!empty($this->filters['to_date'])) {
            $query->whereDate('expected_return_date', '<=', $this->filters['to_date']);
        }

        $requests = $query->get();

        // Chuyển Data thành collection phẳng
        $data = collect();

        foreach ($requests as $req) {
            foreach ($req->borrowDetails as $detail) {
                $data->push((object)[
                    'id' => $req->id,
                    'user' => $req->user->name ?? null,
                    'subject' => $req->user->teaching_subject ?? null,
                    'department' => $detail->device->subject_group ?? null,
                    'device' => $detail->device->name ?? null,
                    'quantity' => $detail->quantity,
                    'borrow_date' => $req->borrow_date,
                    'expected_return_date' => $req->expected_return_date,
                    'actual_return_date' => $req->returnRecord->return_date ?? null,
                    'status' => $req->status,
                    'purpose' => $req->purpose,
                ]);
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Mã phiếu',
            'Ngày mượn',
            'Ngày trả DK',
            'Ngày trả',
            'Giáo viên',
            'Tổ',
            'Môn',
            'Thiết bị',
            'SL',
            'Trạng thái',
            'Mục đích',
        ];
    }

    public function map($row): array
    {
        $statusText = match($row->status) {
            'borrowing' => 'Đang mượn',
            'returned'  => 'Đã trả',
            'overdue'   => 'Quá hạn',
            default     => '—',
        };

        $dashIfEmpty = function($val) {
            return (empty($val) || $val === 'N/A') ? '—' : $val;
        };

        return [
            '#' . $row->id,
            $row->borrow_date ? Carbon::parse($row->borrow_date)->format('d/m/Y') : '—',
            $row->expected_return_date ? Carbon::parse($row->expected_return_date)->format('d/m/Y') : '—',
            $row->actual_return_date ? Carbon::parse($row->actual_return_date)->format('d/m/Y') : '—',
            $dashIfEmpty($row->user),
            $dashIfEmpty($row->department),
            $dashIfEmpty($row->subject),
            $dashIfEmpty($row->device),
            $row->quantity ?: '—',
            $statusText,
            $dashIfEmpty($row->purpose),
        ];
    }

    public function title(): string
    {
        return $this->filters['sheet_title'] ?? 'Danh sách phiếu mượn';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');

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
                $sheet->setCellValue('H' . $totalsRow, 'Tổng cộng:');
                $sheet->getStyle('H' . $totalsRow)->getFont()->setBold(true);
                $sheet->getStyle('H' . $totalsRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                
                // Formula columns I
                $sheet->setCellValue('I' . $totalsRow, '=SUM(I2:I' . $highestRow . ')');
                $sheet->getStyle('I' . $totalsRow)->getFont()->setBold(true);
                
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
