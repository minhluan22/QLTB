<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản | QLTB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #1a73e8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 20px;
        }

        .register-card {
            background: rgba(255, 255, 255, .97);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .3);
        }

        .register-logo {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .register-logo i {
            font-size: 1.6rem;
            color: #fff;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            padding: 11px 14px;
            font-size: .9rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, .12);
        }

        .input-group-text {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px 0 0 10px;
            color: #64748b;
        }

        .input-group .form-control,
        .input-group .form-select {
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
            display: flex;
            align-items: center;
        }

        .btn-toggle-password:hover {
            color: #10b981;
            background: #f1f5f9;
        }

        .btn-register {
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            font-size: .95rem;
            width: 100%;
            color: #fff;
            transition: opacity .2s, transform .1s;
        }

        .btn-register:hover {
            opacity: .9;
            transform: translateY(-1px);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .badge-role {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            padding: 4px 10px;
            font-size: .78rem;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="register-card">

        {{-- Logo --}}
        <div class="text-center mb-4">
            <div class="register-logo">
                <i class="bi bi-person-plus"></i>
            </div>
            <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;">Đăng ký Giáo viên</h1>
            <p style="font-size:.85rem;color:#64748b;">Hệ thống Quản Lý Thiết Bị Trường Học</p>
            <span class="badge-role"><i class="bi bi-mortarboard me-1"></i>Tài khoản Giáo viên</span>
        </div>

        {{-- Flash / Error --}}
        @if(session('error'))
            <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem;border-radius:10px;">
                <i class="bi bi-x-circle me-1"></i> {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger py-2 mb-3" style="font-size:.83rem;border-radius:10px;">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('register') }}">
            @csrf

            {{-- Họ và tên --}}
            <div class="mb-3">
                <label for="name" class="form-label" style="font-size:.875rem;font-weight:500;">Họ và tên <span
                        class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}" placeholder="Nguyễn Văn A" autofocus>
                </div>
            </div>

            {{-- Email --}}
            <div class="mb-3">
                <label for="email" class="form-label" style="font-size:.875rem;font-weight:500;">Email <span
                        class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" id="email" name="email"
                        class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                        placeholder="nguyenvana@gmail.com">
                </div>
            </div>

            {{-- Tổ chuyên môn --}}
            @php
                $presetGroups = ['Toán - Tin - GDTC', 'KHXH', 'KHTN - Tiếng Anh'];
                $oldGroup = old('subject_group', '');
                $isPreset = in_array($oldGroup, $presetGroups) || $oldGroup === '';
            @endphp
            <div class="mb-3">
                <label for="subject_group_select" class="form-label" style="font-size:.875rem;font-weight:500;">Tổ
                    chuyên môn <span class="text-danger">*</span></label>
                <div class="input-group mb-2">
                    <span class="input-group-text"><i class="bi bi-collection"></i></span>
                    <select id="subject_group_select" class="form-select @error('subject_group') is-invalid @enderror"
                        onchange="toggleCustomGroup(this)">
                        <option value="">-- Chọn tổ --</option>
                        <option value="Toán - Tin - GDTC" {{ $oldGroup === 'Toán - Tin - GDTC' ? 'selected' : '' }}>Toán -
                            Tin - GDTC</option>
                        <option value="KHXH" {{ $oldGroup === 'KHXH' ? 'selected' : '' }}>KHXH (Văn, Sử, Địa, GDCD...)
                        </option>
                        <option value="KHTN - Tiếng Anh" {{ $oldGroup === 'KHTN - Tiếng Anh' ? 'selected' : '' }}>KHTN -
                            Tiếng Anh (Lý, Hóa, Sinh, TA)</option>
                        <option value="__other__" {{ (!$isPreset && $oldGroup !== '') ? 'selected' : '' }}>Khác (tự
                            nhập)...</option>
                    </select>
                </div>
                {{-- Input tự nhập --}}
                <input type="text" id="subject_group_custom"
                    style="display:{{ (!$isPreset && $oldGroup !== '') ? 'block' : 'none' }};" class="form-control"
                    placeholder="Nhập tên tổ tự do..." value="{{ (!$isPreset && $oldGroup !== '') ? $oldGroup : '' }}">
                {{-- Hidden field thực sự submit --}}
                <input type="hidden" name="subject_group" id="subject_group_hidden" value="{{ $oldGroup }}">
            </div>

            {{-- Môn dạy --}}
            <div class="mb-3">
                <label for="teaching_subject" class="form-label" style="font-size:.875rem;font-weight:500;">Môn
                    dạy</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-book"></i></span>
                    <input type="text" id="teaching_subject" name="teaching_subject"
                        class="form-control @error('teaching_subject') is-invalid @enderror"
                        value="{{ old('teaching_subject') }}" list="teaching_subject_list"
                        placeholder="VD: Vật Lý, Toán học, Hoá Học...">
                    <datalist id="teaching_subject_list">
                        <option value="Toán học">
                        <option value="Tin học">
                        <option value="Thể dục">
                        <option value="Ngữ văn">
                        <option value="Lịch sử">
                        <option value="Địa lý">
                        <option value="GDCD">
                        <option value="Vật Lý">
                        <option value="Hoá Học">
                        <option value="Sinh Học">
                        <option value="Tiếng Anh">
                    </datalist>
                    @error('teaching_subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Mật khẩu --}}

            <div class="mb-3">
                <label for="password" class="form-label" style="font-size:.875rem;font-weight:500;">Mật khẩu <span
                        class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" id="password" name="password"
                        class="form-control @error('password') is-invalid @enderror" placeholder="Tối thiểu 6 ký tự">
                    <button class="btn-toggle-password" type="button">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            {{-- Xác nhận mật khẩu --}}
            <div class="mb-4">
                <label for="password_confirmation" class="form-label" style="font-size:.875rem;font-weight:500;">Xác
                    nhận mật khẩu <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                        placeholder="Nhập lại mật khẩu">
                    <button class="btn-toggle-password" type="button">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-register">
                <i class="bi bi-person-check me-2"></i>Tạo tài khoản
            </button>
        </form>

        {{-- Link quay lại đăng nhập --}}
        <div class="text-center mt-4" style="font-size:.875rem;">
            Đã có tài khoản?
            <a href="{{ route('login') }}" class="fw-600" style="color:#1a73e8;font-weight:600;">Đăng nhập tại đây</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleCustomGroup(sel) {
            const custom = document.getElementById('subject_group_custom');
            const hidden = document.getElementById('subject_group_hidden');
            if (sel.value === '__other__') {
                custom.style.display = 'block';
                custom.focus();
                hidden.value = '';
                custom.addEventListener('input', () => hidden.value = custom.value);
            } else {
                custom.style.display = 'none';
                hidden.value = sel.value;
            }
        }
        // Init on load
        window.addEventListener('DOMContentLoaded', () => {
            const sel = document.getElementById('subject_group_select');
            if (sel) toggleCustomGroup(sel);

            // Xử lý ẩn/hiện mật khẩu
            document.querySelectorAll('.btn-toggle-password').forEach(btn => {
                btn.addEventListener('click', function() {
                    const input = this.parentElement.querySelector('input');
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    
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
        });
    </script>
</body>

</html>