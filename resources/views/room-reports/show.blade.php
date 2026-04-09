@extends('layouts.app')

@section('title', 'Chi tiết báo cáo phòng #' . $roomReport->id)
@section('page-title', 'Chi tiết báo cáo phòng')

@section('content')
<div class="mb-4">
    <a href="{{ route('room-reports.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
    </a>
</div>

<div class="row g-4">
    {{-- CỘT TRÁI: Thông tin báo cáo --}}
    <div class="col-12 col-lg-8">

        {{-- HEADER PHÒNG --}}
        @php
            $gradients = [
                'Phòng Lý'  => 'linear-gradient(135deg,#3b82f6,#1d4ed8)',
                'Phòng Hóa' => 'linear-gradient(135deg,#f59e0b,#b45309)',
                'Phòng Sinh'=> 'linear-gradient(135deg,#10b981,#065f46)',
            ];
            $grad = $gradients[$roomReport->room_name] ?? 'linear-gradient(135deg,#1a73e8,#0d47a1)';
        @endphp
        <div class="card mb-4 border-0" style="background: {{ $grad }}; color:#fff; border-radius:16px;">
            <div class="card-body py-4 px-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:13px;
                                display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-building fs-3"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $roomReport->room_name }}</h5>
                        <div style="font-size:.82rem;opacity:.85;">
                            Báo cáo #{{ $roomReport->id }} — {{ $roomReport->report_date->format('d/m/Y') }}
                        </div>
                    </div>
                    <span class="badge bg-{{ $roomReport->statusColor() }} ms-auto" style="font-size:.78rem;">
                        {{ $roomReport->statusLabel() }}
                    </span>
                </div>
                <div class="row g-2" style="font-size:.82rem;">
                    <div class="col-6">
                        <div style="opacity:.7;">Người báo cáo</div>
                        <div class="fw-semibold">{{ $roomReport->reporter->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-6">
                        <div style="opacity:.7;">Ngày tạo</div>
                        <div class="fw-semibold">{{ $roomReport->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- NỘI DUNG BÁO CÁO --}}
        <div class="card mb-4">
            <div class="card-header py-3 px-4">
                <i class="bi bi-clipboard2-data me-2 text-primary"></i>
                <span class="fw-semibold">Nội dung báo cáo</span>
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <div class="fw-bold text-secondary mb-2" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;">
                        Tình trạng thiết bị trong phòng
                    </div>
                    <div class="bg-light rounded p-3" style="font-size:.9rem;line-height:1.7;white-space:pre-line;">{{ $roomReport->device_condition }}</div>
                </div>

                @if($roomReport->issues)
                <div class="mb-4">
                    <div class="fw-bold text-secondary mb-2" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;">
                        Vấn đề phát sinh
                    </div>
                    <div class="bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded p-3"
                         style="font-size:.9rem;line-height:1.7;white-space:pre-line;">
                        <i class="bi bi-exclamation-triangle text-warning me-1"></i>
                        {{ $roomReport->issues }}
                    </div>
                </div>
                @endif

                @if($roomReport->actions_taken)
                <div class="mb-0">
                    <div class="fw-bold text-secondary mb-2" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;">
                        Hành động đã thực hiện
                    </div>
                    <div class="bg-success bg-opacity-10 border border-success border-opacity-25 rounded p-3"
                         style="font-size:.9rem;line-height:1.7;white-space:pre-line;">
                        <i class="bi bi-check-circle text-success me-1"></i>
                        {{ $roomReport->actions_taken }}
                    </div>
                </div>
                @endif
            </div>
        </div>

    </div>

    {{-- CỘT PHẢI: Phản hồi Admin --}}
    <div class="col-12 col-lg-4">

        {{-- TRẠNG THÁI --}}
        <div class="card mb-4">
            <div class="card-header py-3 px-4">
                <i class="bi bi-info-circle me-2 text-primary"></i>
                <span class="fw-semibold">Trạng thái xử lý</span>
            </div>
            <div class="card-body p-4">
                @if($roomReport->isPending())
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                            <i class="bi bi-hourglass-split me-1"></i>Chờ xem xét
                        </span>
                    </div>
                    <div class="text-muted" style="font-size:.82rem;">
                        Báo cáo đang chờ Admin xem xét và phản hồi.
                    </div>
                @else
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-success fs-6 px-3 py-2">
                            <i class="bi bi-check-circle me-1"></i>Đã xem xét
                        </span>
                    </div>
                    <div class="text-muted" style="font-size:.82rem;">
                        Đã xem xét bởi <strong>{{ $roomReport->reviewer->name ?? 'Admin' }}</strong>
                        lúc {{ $roomReport->reviewed_at?->format('d/m/Y H:i') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- PHẢN HỒI ADMIN (hiển thị nếu đã review) --}}
        @if(!$roomReport->isPending() && $roomReport->admin_note)
        <div class="card mb-4 border-success border-opacity-25">
            <div class="card-header py-3 px-4 bg-success bg-opacity-10">
                <i class="bi bi-person-check me-2 text-success"></i>
                <span class="fw-semibold text-success">Phản hồi từ Admin</span>
            </div>
            <div class="card-body p-4">
                <p class="mb-0" style="font-size:.9rem;line-height:1.7;white-space:pre-line;">{{ $roomReport->admin_note }}</p>
            </div>
        </div>
        @endif

        {{-- ADMIN: FORM DUYỆT / PHẢN HỒI --}}
        @if(auth()->user()->isAdmin())
        <div class="card border-primary border-opacity-25">
            <div class="card-header py-3 px-4 bg-primary bg-opacity-10">
                <i class="bi bi-shield-check me-2 text-primary"></i>
                <span class="fw-semibold text-primary">
                    {{ $roomReport->isPending() ? 'Xem xét & Phản hồi' : 'Cập nhật phản hồi' }}
                </span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('room-reports.review', $roomReport) }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.85rem;">
                            Ghi chú phản hồi <span class="text-muted fw-normal">(tuỳ chọn)</span>
                        </label>
                        <textarea name="admin_note" rows="4" class="form-control"
                                  placeholder="Nhận xét, hướng dẫn xử lý, hoặc xác nhận...">{{ old('admin_note', $roomReport->admin_note) }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-check2-circle me-1"></i>
                        {{ $roomReport->isPending() ? 'Xác nhận đã xem xét' : 'Cập nhật phản hồi' }}
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
