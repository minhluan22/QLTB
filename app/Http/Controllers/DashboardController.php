<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\BorrowRequest;
use App\Models\Damage;
use App\Models\Import;
use Illuminate\Http\Request;

/**
 * Controller Dashboard
 *
 * Hiển thị trang tổng quan khác nhau tùy theo role người dùng:
 * - Admin: xem thống kê toàn hệ thống
 * - Teacher: xem yêu cầu mượn của mình
 */
class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        return $this->teacherDashboard($user);
    }

    private function adminDashboard()
    {
        $stats = [
            'total_devices'      => Device::count(),
            'borrowing_requests' => BorrowRequest::whereIn('status', ['borrowing', 'overdue'])->count(),
            'overdue_requests'   => BorrowRequest::where('status', 'overdue')->count(),
            'total_damages'      => Damage::count(),
        ];

        // 5 phiếu mượn mới nhất đang diễn ra
        $recentBorrows = BorrowRequest::with(['user', 'borrowDetails.device'])
            ->whereIn('status', ['borrowing', 'overdue'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recentBorrows'));
    }

    private function teacherDashboard($user)
    {
        // Tự động kiểm tra và cập nhật các phiếu quá hạn
        BorrowRequest::where('user_id', $user->id)
            ->whereIn('status', ['borrowing'])
            ->whereDate('expected_return_date', '<', now())
            ->update(['status' => 'overdue']);

        // Yêu cầu mượn của giáo viên này
        $myRequests = BorrowRequest::with(['borrowDetails.device'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(5)
            ->withQueryString();

        $actionableRequests = BorrowRequest::with(['borrowDetails.device'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['borrowing', 'overdue'])
            ->orderBy('expected_return_date', 'asc')
            ->get();

        $stats = [
            'borrowing' => BorrowRequest::where('user_id', $user->id)->where('status', 'borrowing')->count(),
            'overdue'   => BorrowRequest::where('user_id', $user->id)->where('status', 'overdue')->count(),
            'returned'  => BorrowRequest::where('user_id', $user->id)->where('status', 'returned')->count(),
        ];

        return view('dashboard', compact('myRequests', 'stats', 'actionableRequests'));
    }
}
