<?php

namespace App\Exports\Sheets;

use App\Models\BorrowRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BorrowTeacherStatsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = BorrowRequest::with(['user', 'borrowDetails']);

        if (!empty($this->filters['from_date'])) {
            $query->whereDate('borrow_date', '>=', $this->filters['from_date']);
        }
        if (!empty($this->filters['to_date'])) {
            $query->whereDate('expected_return_date', '<=', $this->filters['to_date']);
        }
        
        $requests = $query->get();

        $stats = [];
        foreach ($requests as $req) {
            $userId = $req->user_id;
            if (!$userId) continue;

            if (!isset($stats[$userId])) {
                $stats[$userId] = [
                    'user'           => $req->user->name ?? '—',
                    'email'          => $req->user->email ?? '—',
                    'subject'        => $req->user->teaching_subject ?? '—',
                    'total_requests' => 0,
                    'total_devices'  => 0,
                ];
            }

            $stats[$userId]['total_requests'] += 1;
            $stats[$userId]['total_devices'] += $req->borrowDetails->sum('quantity');
        }

        // Sắp xếp giảm dần theo số phiếu mượn
        $statsCollection = collect(array_values($stats))->sortByDesc('total_requests')->values();

        return $statsCollection;
    }

    public function headings(): array
    {
        return [
            'Giáo viên',
            'Email',
            'Môn dạy',
            'Tổng số phiếu',
            'Tổng số thiết bị mượn',
        ];
    }

    public function map($row): array
    {
        return [
            $row['user'],
            $row['email'],
            $row['subject'],
            $row['total_requests'],
            $row['total_devices'],
        ];
    }

    public function title(): string
    {
        return 'Thống kê theo giáo viên';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
