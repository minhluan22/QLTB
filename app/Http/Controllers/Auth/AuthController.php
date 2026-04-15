<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller xử lý đăng nhập / đăng xuất
 */
class AuthController extends Controller
{
    /**
     * Hiển thị form đăng nhập
     */
    public function showLogin()
    {
        // Nếu đã đăng nhập rồi → chuyển về dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập
     */
    public function login(Request $request)
    {
        // Validate dữ liệu đầu vào
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        // Thử đăng nhập với remember me
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // KIỂM TRA TRẠNG THÁI TÀI KHOẢN
            if ($user->status !== 'active') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Tài khoản của bạn đang chờ phê duyệt. Vui lòng liên hệ quản lý phòng thiết bị để được duyệt nhanh hơn.'
                ]);
            }

            $request->session()->regenerate(); // Tránh session fixation attack

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Chào mừng ' . $user->name . '!');
        }

        // Đăng nhập thất bại
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Email hoặc mật khẩu không đúng.']);
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Đã đăng xuất thành công.');
    }

    /**
     * Hiển thị form đăng ký (Giáo viên)
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    /**
     * Xử lý đăng ký tài khoản giáo viên
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6', 'confirmed'],
            'subject_group' => ['required', 'string'],
            'teaching_subject' => ['nullable', 'string', 'max:100'],
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được đăng ký.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'subject_group.required' => 'Vui lòng chọn tổ chuyên môn.',
        ]);

        \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'teacher',
            'subject_group' => $request->subject_group,
            'teaching_subject' => $request->teaching_subject,
            'status' => 'pending',
        ]);

        return redirect()->route('login')
            ->with('success', 'Bạn đã tạo tài khoản thành công. Vui lòng chờ admin duyệt để đăng nhập hoặc liên hệ quản lý phòng thiết bị để nhanh hơn.');
    }
}
