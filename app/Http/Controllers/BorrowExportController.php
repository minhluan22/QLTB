<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\BorrowMultiExport;
use Maatwebsite\Excel\Facades\Excel;

class BorrowExportController extends Controller
{
    /**
     * Xuất báo cáo mượn trả ra Excel (Multi-sheet)
     * Thống kê toàn bộ trạng thái và báo cáo theo giáo viên
     */
    public function export(Request $request)
    {
        $filters = $request->only(['borrower', 'department', 'status', 'from_date', 'to_date']);

        return Excel::download(
            new \App\Exports\Sheets\BorrowDataSheet($filters),
            'bao-cao-muon-tra-thiet-bi.xlsx'
        );
    }
}
