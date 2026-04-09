<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\BorrowDataSheet;
use App\Exports\Sheets\BorrowTeacherStatsSheet;

class BorrowMultiExport implements WithMultipleSheets
{
    use Exportable;

    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Sheet 1: Tất cả
        $sheets[] = new BorrowDataSheet(array_merge($this->filters, ['sheet_title' => 'Tất cả phiếu mượn']));
        
        // Sheet 2: Đang mượn
        $sheets[] = new BorrowDataSheet(array_merge($this->filters, ['sheet_title' => 'Phiếu đang mượn', 'status' => 'borrowing']));
        
        // Sheet 3: Đã trả
        $sheets[] = new BorrowDataSheet(array_merge($this->filters, ['sheet_title' => 'Phiếu đã trả', 'status' => 'returned']));
        
        // Sheet 4: Quá hạn
        $sheets[] = new BorrowDataSheet(array_merge($this->filters, ['sheet_title' => 'Phiếu quá hạn', 'status' => 'overdue']));
        
        // Sheet 5: Thống kê giáo viên
        $sheets[] = new BorrowTeacherStatsSheet($this->filters);

        // Sheet 6: Theo thời gian
        $sheet6Filters = array_merge($this->filters, $this->filters['sheet6_time'] ?? [], ['sheet_title' => 'Theo thời gian']);
        $sheets[] = new BorrowDataSheet($sheet6Filters);

        return $sheets;
    }
}
