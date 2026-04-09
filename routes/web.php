<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\BorrowRequestController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\DamageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Hệ thống Quản Lý Thiết Bị Trường Học (QLTB)
|--------------------------------------------------------------------------
|
| Cấu trúc middleware:
|   auth         → Yêu cầu đăng nhập
|   role:admin   → Chỉ admin mới vào được
|   role:teacher → Chỉ giáo viên mới vào được
|
*/

// Trang chủ → chuyển về dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// ===== AUTH (chỉ dành cho khách chưa đăng nhập) =====
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

// Đăng xuất
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')->middleware('auth');

// ===== CÁC ROUTE YÊU CẦU ĐĂNG NHẬP =====
Route::middleware('auth')->group(function () {

    // Dashboard (cả admin lẫn teacher, nội dung hiển thị khác nhau)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // ===== ADMIN ONLY =====
    Route::middleware('role:admin')->group(function () {

        // Export Excel Thiết Bị
        Route::get('devices/export', [DeviceController::class, 'export'])->name('devices.export');

        // Quản lý thiết bị: Các action sửa/xóa/tạo (index/show nằm ở mục shared)
        Route::resource('devices', DeviceController::class)->except(['index', 'show']);
        Route::post('devices/{device}/import', [DeviceController::class, 'importStore'])->name('devices.import');
        Route::put('devices/{device}/imports/{import}', [DeviceController::class, 'updateImport'])->name('devices.imports.update');

        // Duyệt / Từ chối yêu cầu mượn => Bỏ vì dùng luồng mượn trực tiếp

        // Quản lý người dùng (User CRUD)
        Route::resource('users', UserController::class);
        // Đặt lại mật khẩu
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
             ->name('users.reset-password');
        // Duyệt tài khoản
        Route::patch('users/{user}/approve', [UserController::class, 'approve'])
             ->name('users.approve');

        // Danh sách trả thiết bị
        Route::get('returns', [ReturnController::class, 'index'])->name('returns.index');
    });

    // ===== TEACHER ONLY =====
    Route::middleware('role:teacher')->group(function () {
        // Teacher currently doesn't have exclusive routes anymore, they share the borrowing routes
    });

    // ===== SHARED: cả admin và teacher đều xem được =====
    // Xem danh sách & chi tiết thiết bị (read-only cho teacher, admin vẫn dùng chung)
    Route::get('devices',          [DeviceController::class, 'index'])->name('devices.index');
    Route::get('devices/{device}', [DeviceController::class, 'show'])->name('devices.show');

    // Quản lý báo hỏng (Cả hai đều xem được, giáo viên báo, admin xem tất cả)
    Route::get('damages/export', [DamageController::class, 'export'])->name('damages.export');
    Route::get('damages', [DamageController::class, 'index'])->name('damages.index');
    Route::get('damages/create', [DamageController::class, 'create'])->name('damages.create');
    Route::post('damages', [DamageController::class, 'store'])->name('damages.store');

    // Mượn nhanh (Danh sách thiết bị lưới)
    Route::get('borrow-quick', [App\Http\Controllers\BorrowQuickController::class, 'index'])->name('borrow-quick.index');
    Route::post('borrow-quick/borrow/{device}', [App\Http\Controllers\BorrowQuickController::class, 'borrow'])->name('borrow-quick.borrow');
    Route::post('borrow-quick/return/{borrowRequest}', [App\Http\Controllers\BorrowQuickController::class, 'returnEquipment'])->name('borrow-quick.return');

    // Yêu cầu mượn — xem + tạo mới (cả hai role)
    Route::get('borrow-requests/export', [\App\Http\Controllers\BorrowExportController::class, 'export'])->name('borrow-requests.export');
    Route::get('borrow-requests',        [BorrowRequestController::class, 'index'])->name('borrow-requests.index');
    Route::get('borrow-requests/create', [BorrowRequestController::class, 'create'])->name('borrow-requests.create');
    Route::post('borrow-requests',       [BorrowRequestController::class, 'store'])->name('borrow-requests.store');
    Route::get('borrow-requests/{borrowRequest}', [BorrowRequestController::class, 'show'])->name('borrow-requests.show');
    Route::get('borrow-requests/{borrowRequest}/edit', [BorrowRequestController::class, 'edit'])->name('borrow-requests.edit');
    Route::put('borrow-requests/{borrowRequest}', [BorrowRequestController::class, 'update'])->name('borrow-requests.update');
    Route::delete('borrow-requests/{borrowRequest}', [BorrowRequestController::class, 'destroy'])->name('borrow-requests.destroy');
    Route::post('borrow-requests/{borrowRequest}/return', [BorrowRequestController::class, 'teacherReturn'])->name('borrow-requests.return');

    // ===== PHÒNG THỰC HÀNH: Rooms (Admin CRUD) =====
    Route::middleware('role:admin')->group(function () {
        Route::resource('rooms', \App\Http\Controllers\RoomController::class)->except(['show']);
    });

    // ===== PHÒNG CỦA TÔI + THIẾT BỊ PHÒNG (Room manager) =====
    Route::middleware('role:admin,room_manager')->group(function () {
        Route::get('my-room', [\App\Http\Controllers\RoomController::class, 'myRoom'])->name('my-room');
        Route::get('room-devices/export', [\App\Http\Controllers\RoomDeviceController::class, 'export'])->name('room-devices.export');
        Route::resource('room-devices', \App\Http\Controllers\RoomDeviceController::class);
        // Cập nhật hàng loạt tình trạng thiết bị (inline table form)
        Route::get('room-devices-status',  [\App\Http\Controllers\RoomDeviceController::class, 'statusForm'])->name('room-devices.status');
        Route::post('room-devices-status', [\App\Http\Controllers\RoomDeviceController::class, 'batchUpdate'])->name('room-devices.batch-update');
    });

    // ===== BÁO CÁO TIẾT DẠY (Teacher: tạo + xem của mình) =====
    Route::get('lesson-reports/my',    [\App\Http\Controllers\LessonReportController::class, 'my'])->name('lesson-reports.my');
    Route::get('lesson-reports/create',[\App\Http\Controllers\LessonReportController::class, 'create'])->name('lesson-reports.create');
    Route::post('lesson-reports',      [\App\Http\Controllers\LessonReportController::class, 'store'])->name('lesson-reports.store');

    // ===== BÁO CÁO TIẾT DẠY (Room manager: xem phòng mình + xác nhận) =====
    Route::middleware('role:admin,room_manager')->group(function () {
        Route::get('lesson-reports/room', [\App\Http\Controllers\LessonReportController::class, 'roomIndex'])->name('lesson-reports.room-index');
        Route::patch('lesson-reports/{lessonReport}/confirm', [\App\Http\Controllers\LessonReportController::class, 'confirm'])->name('lesson-reports.confirm');
    });

    // ===== BÁO CÁO TIẾT DẠY (Admin: xem tất cả) =====
    Route::middleware('role:admin')->group(function () {
        Route::get('lesson-reports', [\App\Http\Controllers\LessonReportController::class, 'adminIndex'])->name('lesson-reports.admin-index');
        Route::get('lesson-reports-export', [\App\Http\Controllers\LessonReportController::class, 'export'])->name('lesson-reports.export');
    });

    // ===== CHI TIẾT BÁO CÁO TIẾT (tất cả) =====
    Route::get('lesson-reports/{lessonReport}', [\App\Http\Controllers\LessonReportController::class, 'show'])->name('lesson-reports.show');
    Route::get('lesson-reports/{lessonReport}/edit', [\App\Http\Controllers\LessonReportController::class, 'edit'])->name('lesson-reports.edit');
    Route::put('lesson-reports/{lessonReport}', [\App\Http\Controllers\LessonReportController::class, 'update'])->name('lesson-reports.update');
    Route::delete('lesson-reports/{lessonReport}', [\App\Http\Controllers\LessonReportController::class, 'destroy'])->name('lesson-reports.destroy');

    // ===== ĐỀ XUẤT THIẾT BỊ =====
    // Giáo viên: Tạo, xem danh sách cá nhân
    Route::get('device-proposals/my', [\App\Http\Controllers\DeviceProposalController::class, 'index'])->name('device-proposals.index');
    Route::get('device-proposals/create', [\App\Http\Controllers\DeviceProposalController::class, 'create'])->name('device-proposals.create');
    Route::post('device-proposals', [\App\Http\Controllers\DeviceProposalController::class, 'store'])->name('device-proposals.store');

    // Admin: Xem tất cả, duyệt, từ chối
    Route::middleware('role:admin')->group(function () {
        Route::get('device-proposals/admin', [\App\Http\Controllers\DeviceProposalController::class, 'adminIndex'])->name('device-proposals.admin-index');
        Route::get('device-proposals/export', [\App\Http\Controllers\DeviceProposalController::class, 'export'])->name('device-proposals.export');
        Route::patch('device-proposals/{deviceProposal}/approve', [\App\Http\Controllers\DeviceProposalController::class, 'approve'])->name('device-proposals.approve');
        Route::patch('device-proposals/{deviceProposal}/reject', [\App\Http\Controllers\DeviceProposalController::class, 'reject'])->name('device-proposals.reject');
    });

    // Chung: Xem chi tiết
    Route::get('device-proposals/{deviceProposal}', [\App\Http\Controllers\DeviceProposalController::class, 'show'])->name('device-proposals.show');
});

