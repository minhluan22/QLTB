@extends('layouts.app')
@section('title', 'Báo cáo tiết của tôi')
@section('page-title', 'Lịch sử báo cáo tiết dạy')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1">Báo cáo tiết thực hành của tôi</h5>
        <div class="text-muted" style="font-size:.85rem;">Lịch sử các báo cáo bạn đã gửi</div>
    </div>
    <a href="{{ route('lesson-reports.create') }}" class="btn btn-primary px-4">
        <i class="bi bi-plus-circle me-1"></i>Tạo báo cáo mới
    </a>
</div>

{{-- SEARCH FORM --}}
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3 px-4">
        <form method="GET" action="{{ route('lesson-reports.my') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" 
                        placeholder="Tìm theo lớp, môn học, ghi chú..." 
                        value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">Tìm kiếm</button>
                <a href="{{ route('lesson-reports.my') }}" class="btn btn-outline-secondary px-3">Bỏ tìm kiếm</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Ngày dạy</th>
                    <th>Phòng</th>
                    <th class="text-center">Buổi</th>
                    <th>Lớp</th>
                    <th>Môn</th>
                    <th class="text-center">Số tiết</th>
                    <th class="text-center">Sự cố</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $r)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $r->lesson_date->format('d/m/Y') }}</div>
                        <div class="text-muted" style="font-size:.75rem;">{{ $r->created_at->diffForHumans() }}</div>
                    </td>
                    <td>
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold" style="font-size:.78rem;">
                            {{ $r->room->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $r->session === 'sang' ? 'bg-warning text-dark' : 'bg-info text-dark' }}" style="font-size:.75rem;">
                            {{ $r->session === 'sang' ? '☀️ Sáng' : '🌙 Chiều' }}
                        </span>
                    </td>
                    <td>{{ $r->class_name }}</td>
                    <td><span class="fw-semibold">{{ $r->subject ?? '—' }}</span></td>
                    <td class="text-center">{{ $r->period_count }} tiết</td>
                    <td class="text-center">
                        @if($r->hasIssues())
                            <span class="badge bg-danger" style="font-size:.72rem;"><i class="bi bi-exclamation-triangle me-1"></i>Có sự cố</span>
                        @else
                            <span class="text-muted" style="font-size:.8rem;">Không</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('lesson-reports.edit', $r) }}" class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="{{ route('lesson-reports.show', $r) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>Xem
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-clipboard2 fs-1 d-block opacity-25 mb-3"></i>
                        Chưa có báo cáo nào.
                        <a href="{{ route('lesson-reports.create') }}">Tạo báo cáo đầu tiên</a>.
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
