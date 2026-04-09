@extends('layouts.app')
@section('title', 'Báo cáo tiết — ' . $room->name)
@section('page-title', 'Báo cáo tiết — ' . $room->name)

@section('content')

{{-- FLASH --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- STAT CARD --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#1a73e8,#0d47a1);">
            <div class="value">{{ $reports->total() }}</div>
            <div class="label">Tổng báo cáo</div>
            <i class="bi bi-clipboard2 icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#f59e0b,#b45309);">
            <div class="value">{{ $reports->where('session','sang')->count() }}</div>
            <div class="label">Tiết Sáng</div>
            <i class="bi bi-brightness-high icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#f97316,#c2410c);">
            <div class="value">{{ $reports->where('status','pending')->count() }}</div>
            <div class="label">Chờ xác nhận</div>
            <i class="bi bi-hourglass-split icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#ef4444,#991b1b);">
            <div class="value">{{ $reports->filter(fn($r) => $r->hasIssues())->count() }}</div>
            <div class="label">Có sự cố</div>
            <i class="bi bi-exclamation-triangle icon"></i>
        </div>
    </div>
</div>

{{-- FILTER --}}
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('lesson-reports.room-index') }}">
            <div class="row g-3">
                {{-- Tìm kiếm văn bản --}}
                <div class="col-12 col-md-5">
                    <label class="form-label fw-semibold text-secondary small mb-1">TÌM KIẾM</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                            placeholder="Tên giáo viên, lớp, môn học..." 
                            value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Buổi & Trạng thái --}}
                <div class="col-6 col-md-3">
                    <label class="form-label fw-semibold text-secondary small mb-1">BUỔI</label>
                    <select name="session" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="sang"  {{ request('session')=='sang'  ? 'selected':'' }}>☀️ Sáng</option>
                        <option value="chieu" {{ request('session')=='chieu' ? 'selected':'' }}>🌙 Chiều</option>
                    </select>
                </div>

                <div class="col-6 col-md-4">
                    <label class="form-label fw-semibold text-secondary small mb-1">TRẠNG THÁI</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="pending"   {{ request('status')=='pending'   ? 'selected':'' }}>Chờ xác nhận</option>
                        <option value="confirmed" {{ request('status')=='confirmed' ? 'selected':'' }}>Đã xác nhận</option>
                    </select>
                </div>

                {{-- Khoảng ngày --}}
                <div class="col-6 col-md-3">
                    <label class="form-label fw-semibold text-secondary small mb-1">TỪ NGÀY</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-semibold text-secondary small mb-1">ĐẾN NGÀY</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>

                {{-- Thao tác --}}
                <div class="col-12 col-md-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search me-1"></i>Tìm kiếm
                    </button>
                    <a href="{{ route('lesson-reports.room-index') }}" class="btn btn-outline-secondary px-3">
                        Bỏ tìm kiếm
                    </a>
                    <a href="{{ route('lesson-reports.export', ['room_id' => $room->id] + request()->all()) }}" class="btn btn-success px-3">
                        <i class="bi bi-file-earmark-excel me-1"></i>Xuất Excel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- BẢNG --}}
<div class="card">
    <div class="card-header py-3 px-4 d-flex align-items-center justify-content-between">
        <span class="fw-semibold"><i class="bi bi-clipboard2-data me-2 text-primary"></i>Danh sách báo cáo tiết</span>
        <span class="badge bg-secondary">{{ $reports->total() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Ngày dạy</th>
                    <th>Giáo viên</th>
                    <th class="text-center">Buổi</th>
                    <th>Lớp</th>
                    <th>Môn</th>
                    <th class="text-center">Tiết</th>
                    <th class="text-center">Sự cố</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $r)
                <tr class="{{ $r->isPending() && $r->hasIssues() ? 'table-warning' : '' }}">
                    <td>
                        <div class="fw-semibold">{{ $r->lesson_date->format('d/m/Y') }}</div>
                        <div class="text-muted" style="font-size:.75rem;">{{ $r->created_at->diffForHumans() }}</div>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $r->teacher->name ?? 'N/A' }}</div>
                        <div class="text-muted" style="font-size:.75rem;">{{ $r->teacher->teaching_subject ?? '' }}</div>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $r->session === 'sang' ? 'bg-warning text-dark' : 'bg-info text-dark' }}" style="font-size:.75rem;">
                            {{ $r->session === 'sang' ? '☀️ Sáng' : '🌙 Chiều' }}
                        </span>
                    </td>
                    <td><span class="fw-semibold">{{ $r->class_name }}</span></td>
                    <td><span class="fw-semibold">{{ $r->subject ?? '—' }}</span></td>
                    <td class="text-center">{{ $r->period_count }}</td>
                    <td class="text-center">
                        @if($r->hasIssues())
                            <span class="badge bg-danger" style="font-size:.72rem;">
                                <i class="bi bi-exclamation-triangle me-1"></i>Có
                                (H:{{ $r->issues->sum('broken_qty') }} / T:{{ $r->issues->sum('consumed_qty') }})
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $r->statusColor() }}" style="font-size:.75rem;">
                            {{ $r->statusLabel() }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end flex-wrap">
                            <a href="{{ route('lesson-reports.show', $r) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>Xem
                            </a>
                            @if($r->isPending())
                                {{-- Xác nhận --}}
                                <form method="POST" action="{{ route('lesson-reports.confirm', $r) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="confirmed">
                                    <button type="submit" class="btn btn-sm btn-success"
                                        onclick="return confirm('Xác nhận báo cáo tiết ngày {{ $r->lesson_date->format('d/m/Y') }}?\n{{ $r->hasIssues() ? "⚠️ Số hỏng/tiêu hao sẽ được cập nhật vào bảng thiết bị!" : "" }}')">
                                        <i class="bi bi-check-lg me-1"></i>Xác nhận
                                    </button>
                                </form>
                                {{-- Từ chối --}}
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal" data-bs-target="#rejectModal{{ $r->id }}">
                                    <i class="bi bi-x-lg me-1"></i>Từ chối
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>

                {{-- Modal từ chối --}}
                @if($r->isPending())
                <div class="modal fade" id="rejectModal{{ $r->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title text-danger"><i class="bi bi-x-circle me-2"></i>Từ chối báo cáo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('lesson-reports.confirm', $r) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="action" value="rejected">
                                <div class="modal-body">
                                    <p class="text-muted mb-3">Báo cáo tiết ngày <b>{{ $r->lesson_date->format('d/m/Y') }}</b> — Lớp <b>{{ $r->class_name }}</b></p>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Lý do từ chối <span class="text-muted">(tuỳ chọn)</span></label>
                                        <textarea name="manager_note" class="form-control" rows="3" placeholder="Nhập lý do từ chối..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                                    <button type="submit" class="btn btn-danger"><i class="bi bi-x-lg me-1"></i>Xác nhận từ chối</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block opacity-25 mb-2"></i>Chưa có báo cáo nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reports->total() > 0)
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top bg-light-subtle">
            <div class="text-muted" style="font-size:.8rem;">
                Hiển thị <strong>{{ $reports->firstItem() ?? 0 }}</strong> – <strong>{{ $reports->lastItem() ?? 0 }}</strong> 
                trên tổng số <strong>{{ $reports->total() }}</strong> báo cáo
            </div>
            <div>
                {{ $reports->withQueryString()->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
