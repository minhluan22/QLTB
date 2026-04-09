<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hệ thống Quản Lý Thiết Bị Trường Học - Quản lý mượn/trả thiết bị dễ dàng">
    <meta name="theme-color" content="#1a73e8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') | QLTB</title>

    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    {{-- PWA Manifest --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <style>
        /* ========== DESIGN SYSTEM ========== */
        :root {
            --primary: #1a73e8;
            --primary-dark: #1557b0;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-width: 260px;
            --topbar-height: 60px;
            --font: 'Inter', sans-serif;
            --radius: 12px;
            --shadow: 0 2px 16px rgba(0, 0, 0, .08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: var(--font);
            background: #f0f4f8;
            color: #1e293b;
            margin: 0;
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            color: #e2e8f0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: transform .3s ease;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            text-decoration: none;
            display: block;
        }

        .sidebar-brand h5 {
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            margin: 0;
            line-height: 1.3;
        }

        .sidebar-brand small {
            color: #64748b;
            font-size: .75rem;
        }

        .sidebar-user {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .sidebar-user .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: .85rem;
            color: #fff;
            flex-shrink: 0;
        }

        .sidebar-user .user-info .name {
            font-size: .85rem;
            font-weight: 600;
            color: #f1f5f9;
        }

        .sidebar-user .user-info .role {
            font-size: .72rem;
            color: #94a3b8;
        }

        .sidebar-nav {
            padding: 12px 0;
            flex: 1;
        }

        .sidebar-section {
            padding: 8px 20px 4px;
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #475569;
        }

        .nav-link-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 20px;
            color: #94a3b8;
            text-decoration: none;
            font-size: .875rem;
            font-weight: 500;
            border-radius: 0;
            transition: all .2s;
            position: relative;
        }

        .nav-link-item:hover {
            color: #fff;
            background: var(--sidebar-hover);
        }

        .nav-link-item.active {
            color: #fff;
            background: var(--primary);
        }

        .nav-link-item i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
        }

        /* ========== TOPBAR ========== */
        .topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            padding: 0 24px;
            z-index: 1030;
            gap: 12px;
        }

        .topbar .page-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            flex: 1;
        }

        .topbar .btn-menu {
            display: none;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #64748b;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 8px;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 24px;
            min-height: calc(100vh - var(--topbar-height));
        }

        /* ========== CARDS ========== */
        .card {
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            background: #fff;
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
            border-radius: var(--radius) var(--radius) 0 0 !important;
            font-weight: 600;
        }

        .stat-card {
            border-radius: var(--radius);
            padding: 20px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .stat-card .icon {
            position: absolute;
            right: 16px;
            bottom: 12px;
            font-size: 3.5rem;
            opacity: .15;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-card .label {
            font-size: .8rem;
            opacity: .85;
            margin-top: 4px;
        }

        /* ========== TABLES ========== */
        .table {
            font-size: .875rem;
        }

        .table th {
            font-weight: 600;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #64748b;
        }

        .table-hover tbody tr:hover {
            background: #f8fafc;
        }

        /* ========== FORMS ========== */
        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1.5px solid #e2e8f0;
            font-size: .875rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 115, 232, .12);
        }

        .btn {
            border-radius: 8px;
            font-size: .875rem;
            font-weight: 500;
            padding: 8px 16px;
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        /* ========== ALERTS ========== */
        .alert {
            border-radius: var(--radius);
            border: none;
            font-size: .875rem;
        }

        /* ========== OVERLAY ========== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .5);
            z-index: 1039;
        }

        /* ========== RESPONSIVE MOBILE ========== */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar-overlay.show {
                display: block;
            }

            .topbar {
                left: 0;
            }

            .topbar .btn-menu {
                display: flex;
                align-items: center;
            }

            .main-content {
                margin-left: 0;
                padding: 16px;
            }
        }

        /* ========== ANIMATIONS ========== */
        .fade-in {
            animation: fadeIn .3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ========== PWA INSTALL BANNER ========== */
        #pwa-install-banner {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--sidebar-bg);
            color: #fff;
            padding: 14px 18px;
            border-radius: var(--radius);
            box-shadow: 0 8px 32px rgba(0, 0, 0, .25);
            display: none;
            z-index: 9999;
            max-width: 320px;
            animation: slideUp .4s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

    {{-- ===== SIDEBAR ===== --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <nav class="sidebar" id="sidebar">
        {{-- Logo --}}
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <div class="d-flex align-items-center gap-2">
                <div
                    style="width:36px;height:36px;background:linear-gradient(135deg,#1a73e8,#0d47a1);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-box-seam text-white" style="font-size:1.1rem;"></i>
                </div>
                <div>
                    <h5 class="mb-0">QLTB</h5>
                    <small>Quản Lý Thiết Bị</small>
                </div>
            </div>
        </a>

        {{-- User Info --}}
        <div class="sidebar-user">
            <div class="d-flex align-items-center gap-2">
                @if(auth()->user()->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="avatar" alt="Avatar"
                        style="object-fit: cover;">
                @else
                    <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                @endif
                <div class="user-info">
                    <div class="name">{{ auth()->user()->name }}</div>
                    <div class="role">
                        @if(auth()->user()->isAdmin())
                            <span class="badge bg-primary" style="font-size:.65rem;">Admin</span>
                        @elseif(auth()->user()->isRoomManager())
                            <span class="badge bg-warning text-dark" style="font-size:.65rem;">QL Phòng</span>
                        @else
                            <span class="badge bg-success" style="font-size:.65rem;">Giáo viên</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <div class="sidebar-nav">
            <div class="sidebar-section">Tổng quan</div>
            <a href="{{ route('dashboard') }}"
                class="nav-link-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Trang Chủ
            </a>

            {{-- ADMIN: Quản lý thiết bị toàn trường --}}
            @if(auth()->user()->isAdmin())
                <div class="sidebar-section mt-2">Thiết bị toàn trường</div>
                <a href="{{ route('devices.index') }}"
                    class="nav-link-item {{ request()->routeIs('devices.*') ? 'active' : '' }}">
                    <i class="bi bi-laptop"></i> Kho thiết bị
                </a>
                <a href="{{ route('device-proposals.admin-index') }}"
                    class="nav-link-item {{ request()->routeIs('device-proposals.admin-index') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Duyệt đề xuất
                    @php $pendingProposals = \App\Models\DeviceProposal::where('status', 'pending')->count(); @endphp
                    @if($pendingProposals > 0)
                        <span class="badge bg-warning text-dark ms-auto"
                            style="font-size:.65rem;">{{ $pendingProposals }}</span>
                    @endif
                </a>
            @endif

            {{-- TẤT CẢ: Mượn trả thiết bị --}}
            <div class="sidebar-section mt-2">Mượn trả thiết bị</div>
            <a href="{{ route('borrow-quick.index') }}"
                class="nav-link-item {{ request()->routeIs('borrow-quick.*') ? 'active' : '' }}">
                <i class="bi bi-list-check"></i> Danh sách thiết bị
            </a>
            <a href="{{ route('borrow-requests.index') }}"
                class="nav-link-item {{ request()->routeIs('borrow-requests.*') || request()->routeIs('returns.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-left-right"></i> Danh sách mượn trả
                @php
                    $isAdm = auth()->user()->isAdmin();
                    $borrowingQuery = \App\Models\BorrowRequest::where('status', 'borrowing');
                    $overdueQuery = \App\Models\BorrowRequest::where('status', 'overdue');
                    
                    if (!$isAdm) {
                        $borrowingQuery->where('user_id', auth()->id());
                        $overdueQuery->where('user_id', auth()->id());
                    }
                    
                    $borrowingCount = $borrowingQuery->count();
                    $overdueCount = $overdueQuery->count();
                @endphp
                <div class="ms-auto d-flex gap-1">
                    @if($borrowingCount > 0)
                        <span class="badge bg-warning text-dark" style="font-size:.65rem;">{{ $borrowingCount }}</span>
                    @endif
                    @if($overdueCount > 0)
                        <span class="badge bg-danger" style="font-size:.65rem;">{{ $overdueCount }}</span>
                    @endif
                </div>
            </a>
            <a href="{{ route('damages.index') }}"
                class="nav-link-item {{ request()->routeIs('damages.*') ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle"></i> Danh sách báo hỏng
            </a>

            {{-- ADMIN: Quản lý phòng thực hành --}}
            @if(auth()->user()->isAdmin())
                <div class="sidebar-section mt-2">Phòng thực hành</div>
                <a href="{{ route('rooms.index') }}"
                    class="nav-link-item {{ request()->routeIs('rooms.*') ? 'active' : '' }}">
                    <i class="bi bi-door-open"></i> Quản lý phòng
                </a>
                <a href="{{ route('lesson-reports.admin-index') }}"
                    class="nav-link-item {{ request()->routeIs('lesson-reports.admin-index') ? 'active' : '' }}">
                    <i class="bi bi-clipboard2-data"></i> Báo cáo tiết dạy TH
                </a>
            @endif

            {{-- ROOM MANAGER: Phòng của tôi --}}
            @if(auth()->user()->isRoomManager())
                <div class="sidebar-section mt-2">Phòng của tôi</div>
                <a href="{{ route('my-room') }}" class="nav-link-item {{ request()->routeIs('my-room') ? 'active' : '' }}">
                    <i class="bi bi-house-door"></i> Tổng quan phòng
                </a>
                <a href="{{ route('room-devices.index') }}"
                    class="nav-link-item {{ request()->routeIs('room-devices.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Thiết bị phòng
                </a>
                <a href="{{ route('lesson-reports.room-index') }}"
                    class="nav-link-item {{ request()->routeIs('lesson-reports.room-index') ? 'active' : '' }}">
                    <i class="bi bi-clipboard2-check"></i> Báo cáo tiết dạy
                    @php
                        $myRoom = \App\Models\Room::where('manager_id', auth()->id())->first();
                        $pendingMyRoom = $myRoom ? \App\Models\LessonReport::where('room_id', $myRoom->id)->where('status', 'pending')->count() : 0;
                    @endphp
                    @if($pendingMyRoom > 0)
                        <span class="badge bg-warning text-dark ms-auto" style="font-size:.65rem;">{{ $pendingMyRoom }}</span>
                    @endif
                </a>
            @endif

            {{-- GIẢNG DẠY: Hiển thị với GV có chuyên môn Lý, Hóa, Sinh HOẶC là Quản lý phòng --}}
            @if((!auth()->user()->isAdmin() && auth()->user()->isLabTeacher()) || auth()->user()->isRoomManager())
                <div class="sidebar-section mt-2">Giảng dạy</div>
                <a href="{{ route('lesson-reports.create') }}"
                    class="nav-link-item {{ request()->routeIs('lesson-reports.create') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle"></i> Tạo báo cáo tiết TH
                </a>
                <a href="{{ route('lesson-reports.my') }}"
                    class="nav-link-item {{ request()->routeIs('lesson-reports.my') ? 'active' : '' }}">
                    <i class="bi bi-clipboard2-pulse"></i> Báo cáo tiết TH của tôi
                </a>
            @endif



            {{-- ĐỀ XUẤT THIẾT BỊ (Tất cả giáo viên/quản lý) --}}
            @if(!auth()->user()->isAdmin())
                <div class="sidebar-section mt-2">Đề xuất thiết bị</div>
                <a href="{{ route('device-proposals.create') }}"
                    class="nav-link-item {{ request()->routeIs('device-proposals.create') ? 'active' : '' }}">
                    <i class="bi bi-pencil-square"></i> Tạo thiết bị tự làm của GV
                </a>
                <a href="{{ route('device-proposals.index') }}"
                    class="nav-link-item {{ request()->routeIs('device-proposals.index') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i> Đề xuất thiết bị của tôi
                </a>
            @endif

            <div class="sidebar-section mt-2">Hệ thống</div>
            <a href="{{ route('profile.edit') }}"
                class="nav-link-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> Thông tin cá nhân
            </a>
            {{-- ADMIN ONLY: Quản lý người dùng --}}
            @if(auth()->user()->isAdmin())
                <a href="{{ route('users.index') }}"
                    class="nav-link-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Người dùng
                    @php $pendingUsers = \App\Models\User::where('status', 'pending')->count(); @endphp
                    @if($pendingUsers > 0)
                        <span class="badge bg-warning text-dark ms-auto" style="font-size:.65rem;">{{ $pendingUsers }}</span>
                    @endif
                </a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link-item w-100 border-0 text-start"
                    style="background:none;cursor:pointer;">
                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                </button>
            </form>
        </div>
    </nav>

    {{-- ===== TOPBAR ===== --}}
    <div class="topbar">
        <button class="btn-menu" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        <span class="page-title">@yield('page-title', 'Dashboard')</span>
        <div class="d-flex align-items-center gap-2">
            <span class="d-none d-md-inline text-muted" style="font-size:.8rem;">
                <i class="bi bi-clock"></i> {{ now()->format('d/m/Y') }}
            </span>
        </div>
    </div>

    {{-- ===== MAIN CONTENT ===== --}}
    <main class="main-content fade-in">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-x-circle-fill"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Có lỗi xảy ra:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- ===== PWA INSTALL BANNER ===== --}}
    <div id="pwa-install-banner">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-phone fs-4 text-primary mt-1"></i>
            <div class="flex-1">
                <div style="font-weight:600;font-size:.9rem;">Cài ứng dụng</div>
                <div style="font-size:.78rem;color:#94a3b8;margin-top:2px;">Thêm QLTB vào màn hình chính để dùng nhanh
                    hơn!</div>
                <div class="d-flex gap-2 mt-2">
                    <button id="pwa-install-btn" class="btn btn-primary btn-sm">Cài ngay</button>
                    <button onclick="document.getElementById('pwa-install-banner').style.display='none'"
                        class="btn btn-outline-secondary btn-sm">Bỏ qua</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ===== SIDEBAR TOGGLE (Mobile) =====
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }

        // ===== PWA: Register Service Worker =====
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/QLTB/public/service-worker.js')
                    .then(reg => console.log('SW registered:', reg.scope))
                    .catch(err => console.log('SW error:', err));
            });
        }

        // ===== PWA: Add to Home Screen =====
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            document.getElementById('pwa-install-banner').style.display = 'block';
        });

        document.getElementById('pwa-install-btn')?.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                deferredPrompt = null;
                document.getElementById('pwa-install-banner').style.display = 'none';
            }
        });

        // ===== SWEETALERT DELETE CONFIRMATION =====
        function confirmDelete(event, name) {
            event.preventDefault();
            const form = event.target.closest('form');
            Swal.fire({
                title: 'Bạn có chắc muốn xoá?',
                text: 'Xoá "' + name + '" sẽ mất dữ liệu vĩnh viễn!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Có, xoá ngay!',
                cancelButtonText: 'Huỷ bỏ',
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary ms-2'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        // Auto-hide alerts sau 5 giây
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(a => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(a);
                bsAlert.close();
            });
        }, 5000);
    </script>

    @stack('scripts')
</body>

</html>