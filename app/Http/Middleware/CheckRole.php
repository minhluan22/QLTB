<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: CheckRole
 *
 * Kiểm tra vai trò người dùng trước khi cho phép truy cập route.
 * Hỗ trợ nhiều role: Route::middleware('role:admin,room_manager')
 *
 * Cách đăng ký: thêm vào bootstrap/app.php
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  string  $roles  Vai trò yêu cầu, có thể nhiều role cách nhau bởi dấu phẩy
     *                         VD: 'admin' hoặc 'admin,room_manager'
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Nếu chưa đăng nhập → chuyển về trang login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;

        // Kiểm tra xem role của user có nằm trong danh sách cho phép không
        if (!in_array($userRole, $roles)) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
