@extends('layouts.app')

@section('title', 'Chi tiết người dùng - ' . $user->name)
@section('page-title', 'Hồ sơ người dùng')

@section('content')
<div class="mb-4 d-flex gap-2">
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Quay lại
    </a>
    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-sm">
        <i class="bi bi-pencil me-1"></i>Chỉnh sửa
    </a>
</div>

<div class="row g-4">
    {{-- User Card --}}
    <div class="col-12 col-lg-4">
        <div class="card text-center">
            <div class="card-body py-4">
                <div style="width:72px;height:72px;border-radius:50%;background:{{ $user->isAdmin() ? 'linear-gradient(135deg,#1a73e8,#1557b0)' : 'linear-gradient(135deg,#10b981,#059669)' }};display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:1.75rem;margin:0 auto 16px;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>

                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <div class="mb-2">
                    @if($user->isAdmin())
                        <span class="badge bg-primary">Admin</span>
                    @else
                        <span class="badge bg-success">Giáo viên</span>
                    @endif
                </div>
                <div class="text-muted" style="font-size:.85rem;">{{ $user->email }}</div>

                @if($user->phone)
                    <div class="text-muted mt-1" style="font-size:.85rem;">
                        <i class="bi bi-telephone me-1"></i>{{ $user->phone }}
                    </div>
                @endif

                @if($user->subject_group)
                    <div class="text-muted mt-1" style="font-size:.85rem;">
                        <i class="bi bi-diagram-3 me-1"></i>{{ $user->subject_group }}
                    </div>
                @endif

                @if($user->teaching_subject)
                    <div class="text-muted mt-1" style="font-size:.85rem;">
                        <i class="bi bi-book me-1"></i>{{ $user->teaching_subject }}
                    </div>
                @endif

                @if($user->notes)
                    <div class="text-muted mt-1" style="font-size:.85rem; font-style:italic;">
                        <i class="bi bi-card-text me-1"></i>{{ $user->notes }}
                    </div>
                @endif

                <div class="border-top mt-3 pt-3">
                    <div class="text-muted" style="font-size:.75rem;">Tham gia từ {{ $user->created_at->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="fw-bold text-muted mb-3" style="font-size:.78rem;text-transform:uppercase;">Thống kê</h6>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span style="font-size:.875rem;">Yêu cầu mượn</span>
                    <span class="badge bg-primary">{{ $user->borrow_requests_count }}</span>
                </div>
                @if($user->isAdmin())
                <div class="d-flex justify-content-between align-items-center">
                    <span style="font-size:.875rem;">Đã duyệt</span>
                    <span class="badge bg-success">{{ $user->approved_requests_count }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Recent Requests --}}
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header py-3 px-4">
                <i class="bi bi-clock-history text-primary me-2"></i>
                <span class="fw-semibold">Yêu cầu mượn gần đây</span>
            </div>
            <div class="card-body p-0">
                @if($recentRequests->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        Chưa có yêu cầu mượn nào.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4">Mục đích</th>
                                    <th>Ngày mượn</th>
                                    <th>Thiết bị</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentRequests as $req)
                                <tr>
                                    <td class="px-4" style="font-size:.875rem;">{{ Str::limit($req->purpose, 40) }}</td>
                                    <td style="font-size:.85rem;">{{ $req->borrow_date->format('d/m/Y') }}</td>
                                    <td>
                                        @foreach($req->borrowDetails->take(2) as $d)
                                            <span class="badge bg-light text-dark border" style="font-size:.7rem;">
                                                {{ $d->device->name }}
                                            </span>
                                        @endforeach
                                        @if($req->borrowDetails->count() > 2)
                                            <span class="text-muted" style="font-size:.75rem;">+{{ $req->borrowDetails->count() - 2 }}</span>
                                        @endif
                                    </td>
                                    <td>{!! $req->statusBadge() !!}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
