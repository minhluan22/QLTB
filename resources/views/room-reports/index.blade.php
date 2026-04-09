@extends('layouts.app')

@section('title', 'Báo cáo phòng')
@section('page-title', auth()->user()->isAdmin() ? 'Báo cáo tất cả phòng' : 'Báo cáo phòng của tôi')

@section('content')

{{-- ADMIN: CARDS THỐNG KÊ --}}
@if(auth()->user()->isAdmin() && $stats)
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#1a73e8,#0d47a1);">
            <div class="value">{{ $stats['total'] }}</div>
            <div class="label">Tổng báo cáo</div>
            <i class="bi bi-file-earmark-text icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#f59e0b,#d97706);">
            <div class="value">{{ $stats['pending'] }}</div>
            <div class="label">Chờ xem xét</div>
            <i class="bi bi-hourglass-split icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#10b981,#059669);">
            <div class="value">{{ $stats['reviewed'] }}</div>
            <div class="label">Đã xem xét</div>
            <i class="bi bi-check-circle icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#8b5cf6,#6d28d9);">
            <div class="value">{{ count($rooms) }}</div>
            <div class="label">Số phòng</div>
            <i class="bi bi-building icon"></i>
        </div>
    </div>
</div>

{{-- ADMIN: CARDS THEO TỪNG PHÒNG --}}
<div class="row g-3 mb-4">
    @foreach($rooms as $room)
    <div class="col-12 col-md-4">
        <div class="card p-3">
            <div class="d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;border-radius:12px;background:{{ $loop->index == 0 ? 'linear-gradient(135deg,#3b82f6,#1d4ed8)' : ($loop->index == 1 ? 'linear-gradient(135deg,#f59e0b,#b45309)' : 'linear-gradient(135deg,#10b981,#065f46)') }};display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-building text-white fs-5"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:.95rem;">{{ $room }}</div>
                    <div class="text-muted" style="font-size:.8rem;">
                        {{ $stats['by_room'][$room] ?? 0 }} báo cáo
                    </div>
                </div>
                <a href="{{ route('room-reports.index', ['room' => $room]) }}"
                   class="btn btn-sm btn-outline-primary ms-auto">Xem</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ROOM MANAGER: Header tạo báo cáo --}}
@if(auth()->user()->isRoomManager())
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1">{{ auth()->user()->room_name }}</h5>
        <div class="text-muted" style="font-size:.85rem;">Báo cáo tình trạng phòng của bạn</div>
    </div>
    <a href="{{ route('room-reports.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Tạo báo cáo mới
    </a>
</div>
@endif

{{-- ADMIN: BỘ LỌC --}}
@if(auth()->user()->isAdmin())
<div class="card mb-4">
    <div class="card-body py-3 px-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label mb-1 fw-semibold" style="font-size:.8rem;">Lọc theo phòng</label>
                <select name="room" class="form-select form-select-sm">
                    <option value="">Tất cả phòng</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room }}" {{ request('room') === $room ? 'selected' : '' }}>{{ $room }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label mb-1 fw-semibold" style="font-size:.8rem;">Trạng thái</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tất cả</option>
                    <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Chờ xem xét</option>
                    <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Đã xem xét</option>
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-funnel me-1"></i>Lọc
                </button>
                <a href="{{ route('room-reports.index') }}" class="btn btn-outline-secondary btn-sm">Xóa lọc</a>
            </div>
        </form>
    </div>
</div>
@endif

{{-- BẢNG BÁO CÁO --}}
<div class="card">
    <div class="card-header py-3 px-4 d-flex align-items-center justify-content-between">
        <span class="fw-semibold"><i class="bi bi-clipboard2-check me-2 text-primary"></i>Danh sách báo cáo phòng</span>
        <span class="badge bg-secondary">{{ $reports->total() }} báo cáo</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    @if(auth()->user()->isAdmin())<th>Phòng</th>@endif
                    <th>Ngày báo cáo</th>
                    @if(auth()->user()->isAdmin())<th>Người báo cáo</th>@endif
                    <th>Tình trạng thiết bị</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                <tr>
                    <td class="text-muted" style="font-size:.8rem;">#{{ $report->id }}</td>
                    @if(auth()->user()->isAdmin())
                    <td>
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold"
                              style="font-size:.78rem;">{{ $report->room_name }}</span>
                    </td>
                    @endif
                    <td>{{ $report->report_date->format('d/m/Y') }}</td>
                    @if(auth()->user()->isAdmin())
                    <td>
                        <div style="font-size:.85rem;">{{ $report->reporter->name ?? 'N/A' }}</div>
                    </td>
                    @endif
                    <td>
                        <div style="font-size:.85rem;max-width:260px;"
                             class="text-truncate">{{ $report->device_condition }}</div>
                    </td>
                    <td>
                        <span class="badge bg-{{ $report->statusColor() }}"
                              style="font-size:.75rem;">{{ $report->statusLabel() }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('room-reports.show', $report) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye me-1"></i>Xem
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                        Chưa có báo cáo phòng nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reports->hasPages())
    <div class="card-footer bg-white py-3 px-4">
        {{ $reports->links() }}
    </div>
    @endif
</div>

@endsection
