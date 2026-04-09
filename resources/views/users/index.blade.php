@extends('layouts.app')

@section('title', 'Quản lý người dùng')
@section('page-title', 'Quản lý người dùng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size:1.25rem;">Danh sách người dùng</h2>
        <p class="text-muted mb-0" style="font-size:.85rem;">Quản lý tài khoản Admin và Giáo viên</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i>Thêm người dùng
    </a>
</div>

{{-- Filter Bar --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label fw-semibold" style="font-size:.8rem;">Tìm kiếm</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted" style="font-size:.85rem;"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0"
                           placeholder="Tên, email, số điện thoại..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold" style="font-size:.8rem;">Vai trò</label>
                <select name="role" class="form-select">
                    <option value="">Tất cả vai trò</option>
                    <option value="admin"   {{ request('role') === 'admin'   ? 'selected' : '' }}>Admin</option>
                    <option value="teacher" {{ request('role') === 'teacher' ? 'selected' : '' }}>Giáo viên</option>
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary px-3">Tìm kiếm</button>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary px-3">Bỏ tìm kiếm</a>
            </div>
        </form>
    </div>
</div>

{{-- Users Table --}}
<div class="card">
    <div class="card-body p-0">
        @if($users->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people fs-1 d-block mb-2"></i>
                Không tìm thấy người dùng nào.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">#</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th>Tổ chuyên môn</th>
                        <th>Môn dạy</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td class="px-4 text-muted" style="font-size:.8rem;">{{ $user->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:36px;height:36px;border-radius:50%;background:{{ $user->isAdmin() ? 'linear-gradient(135deg,#1a73e8,#1557b0)' : 'linear-gradient(135deg,#10b981,#059669)' }};display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.85rem;flex-shrink:0;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold" style="font-size:.875rem;">{{ $user->name }}</div>
                                    @if($user->id === auth()->id())
                                        <span class="badge bg-light text-primary border border-primary" style="font-size:.6rem;">Bạn</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="font-size:.85rem;">{{ $user->email }}</td>
                        <td style="font-size:.85rem;">{{ $user->phone ?? '—' }}</td>
                        <td style="font-size:.85rem;">{{ $user->subject_group ?? '—' }}</td>
                        <td style="font-size:.85rem;">{{ $user->teaching_subject ?? '—' }}</td>
                        <td>
                            @if($user->isAdmin())
                                <span class="badge bg-primary px-3 rounded-pill">Admin</span>
                            @elseif($user->role === 'room_manager')
                                <span class="badge bg-info text-white px-3 rounded-pill">Quản lý phòng</span>
                            @else
                                <span class="badge bg-success px-3 rounded-pill">Giáo viên</span>
                            @endif
                        </td>
                        <td>{!! $user->statusBadge() !!}</td>
                        <td style="font-size:.8rem;color:#64748b;">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('users.show', $user) }}"
                                   class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('users.edit', $user) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Chỉnh sửa">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                @if($user->isPending())
                                <form method="POST" action="{{ route('users.approve', $user) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success" title="Duyệt tài khoản">
                                        <i class="bi bi-check-circle"></i> Duyệt
                                    </button>
                                </form>
                                @endif

                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Xóa" onclick="confirmDelete(event, '{{ addslashes($user->name) }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($users->total() > 0)
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top bg-light-subtle">
                <div class="text-muted" style="font-size:.8rem;">
                    Hiển thị <strong>{{ $users->firstItem() ?? 0 }}</strong> – <strong>{{ $users->lastItem() ?? 0 }}</strong> 
                    trên tổng số <strong>{{ $users->total() }}</strong> người dùng
                </div>
                <div>
                    {{ $users->withQueryString()->links() }}
                </div>
            </div>
        @endif
        @endif
    </div>
</div>
@endsection
