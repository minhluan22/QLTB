<?php

namespace App\Exports;

use App\Models\BorrowRequest;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BorrowExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
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
        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }

        // Lọc theo tổ chuyên môn
        // Cấu trúc DB hiện tại lưu subject_group trên thiết bị hoặc người dùng.
        // Ta lọc những phiếu có chứa ít nhất 1 thiết bị thuộc tổ đó, hoặc người mượn thuộc tổ.
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

        // Chuyển Data thành collection phẳng: mỗi dòng là 1 thiết bị mượn
        $data = collect();

        foreach ($requests as $req) {
            foreach ($req->borrowDetails as $detail) {
                // Do thiết kế CSDL không có subject_id trên phiếu mượn,
                // sẽ sử dụng teaching_subject của giáo viên hoặc môn của thiết bị
                $data->push((object)[
                    'id' => $req->id,
                    'user' => $req->user->name ?? null,
                    'subject' => $req->user->teaching_subject ?? null, // Môn dạy
                    'department' => $detail->device->subject_group ?? null, // Tổ chuyên môn
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
        // Format trạng thái
        $statusText = match($row->status) {
            'borrowing' => 'Đang mượn',
            'returned'  => 'Đã trả',
            'overdue'   => 'Quá hạn',
            default     => '—',
        };

        $dashIfEmpty = function($val) {
            return (empty($val) || $val === 'N/A' || $val === 'Chưa rủ') ? '—' : $val;
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
        return 'Danh sách mượn trả';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '1a73e8']]],
        ];
    }
}
