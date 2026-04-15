<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | QLTB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #1a73e8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, .97);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .3);
        }

        .login-logo {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .login-logo i {
            font-size: 1.8rem;
            color: #fff;
        }

        .login-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
        }

        .login-subtitle {
            font-size: .875rem;
            color: #64748b;
        }

        .form-control {
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            padding: 12px 16px;
            font-size: .9rem;
        }

        .form-control:focus {
            border-color: #1a73e8;
            box-shadow: 0 0 0 3px rgba(26, 115, 232, .12);
        }

        .btn-login {
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            font-size: .95rem;
            width: 100%;
            color: #fff;
            transition: opacity .2s, transform .1s;
        }

        .btn-login:hover {
            opacity: .92;
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .demo-accounts {
            background: #f0f7ff;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: .8rem;
        }

        .input-group-text {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px 0 0 10px;
            color: #64748b;
        }

        .input-group .form-control {
            border-radius: 0;
        }

        .btn-toggle-password {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-left: none;
            border-radius: 0 10px 10px 0;
            color: #64748b;
            padding: 0 15px;
            transition: all 0.2s;
        }

        .btn-toggle-password:hover {
            color: #1a73e8;
            background: #f1f5f9;
        }
    </style>
</head>

<body>
    <div class="login-card">
        {{-- Logo --}}
        <div class="text-center mb-4">
            <div class="login-logo">
                <i class="bi bi-box-seam"></i>
            </div>
            <h1 class="login-title">Đăng nhập QLTB</h1>
            <p class="login-subtitle">Hệ thống Quản Lý Thiết Bị Trường Học</p>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success py-2 mb-3" style="font-size:.85rem;">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf

            {{-- Email --}}
            <div class="mb-3">
                <label for="email" class="form-label fw-500" style="font-size:.875rem;font-weight:500;">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" id="email" name="email"
                        class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                        placeholder="Nhập email..." autofocus autocomplete="email">
                </div>
                @error('email')
                    <div class="text-danger mt-1" style="font-size:.8rem;">
                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Mật khẩu --}}
            <div class="mb-4">
                <label for="password" class="form-label" style="font-size:.875rem;font-weight:500;">Mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" id="password" name="password"
                        class="form-control @error('password') is-invalid @enderror" placeholder="Nhập mật khẩu..."
                        autocomplete="current-password">
                    <button class="btn-toggle-password" type="button" id="togglePassword">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')
                    <div class="text-danger mt-1" style="font-size:.8rem;">
                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Remember me --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember" style="font-size:.875rem;">Ghi nhớ đăng nhập</label>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
            </button>
        </form>

        {{-- Link đăng ký --}}
        <div class="text-center mt-3" style="font-size:.875rem;">
            Giáo viên chưa có tài khoản?
            <a href="{{ route('register') }}" style="color:#10b981;font-weight:600;">Đăng ký ngay</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Xử lý ghi nhớ đăng nhập (Remember Me) bằng localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const rememberCheckbox = document.getElementById('remember');

            // 1. Phục hồi thông tin nếu có trong localStorage
            const savedEmail = localStorage.getItem('qltb_remember_email');
            const savedPassword = localStorage.getItem('qltb_remember_password');

            if (savedEmail && savedPassword) {
                emailInput.value = savedEmail;
                passwordInput.value = savedPassword;
                rememberCheckbox.checked = true;
            }

            // 2. Lưu thông tin khi submit form
            loginForm.addEventListener('submit', function() {
                if (rememberCheckbox.checked) {
                    localStorage.setItem('qltb_remember_email', emailInput.value);
                    localStorage.setItem('qltb_remember_password', passwordInput.value);
                } else {
                    localStorage.removeItem('qltb_remember_email');
                    localStorage.removeItem('qltb_remember_password');
                }
            });

            // 3. Xử lý ẩn/hiện mật khẩu
            const togglePassword = document.getElementById('togglePassword');
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Thay đổi icon
                const icon = this.querySelector('i');
                if (type === 'password') {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                } else {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
            });
        });

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/QLTB/public/service-worker.js');
        }
    </script>
</body>

</html>