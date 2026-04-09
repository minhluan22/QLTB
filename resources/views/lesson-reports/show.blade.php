@extends('layouts.app')
@section('title', 'Chi tiết báo cáo tiết #' . $lessonReport->id)
@section('page-title', 'Chi tiết báo cáo tiết')

@section('content')
<div class="mb-4">
    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Quay lại
    </a>
</div>

<div class="row g-4">
{{-- CỘT TRÁI --}}
<div class="col-12 col-lg-8">

    {{-- HEADER INFO --}}
    @php
        $grads=['ly'=>'linear-gradient(135deg,#3b82f6,#1d4ed8)','hoa'=>'linear-gradient(135deg,#f59e0b,#b45309)','sinh'=>'linear-gradient(135deg,#10b981,#065f46)'];
        $grad = $grads[$lessonReport->room->subject ?? 'ly'] ?? 'linear-gradient(135deg,#1a73e8,#0d47a1)';
    @endphp
    <div class="card mb-4 border-0" style="background:{{ $grad }};color:#fff;border-radius:16px;">
        <div class="card-body p-4">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1">{{ $lessonReport->room->name ?? 'N/A' }}</h5>
                    <div style="font-size:.85rem;opacity:.85;">
                        <i class="bi bi-calendar me-1"></i>{{ $lessonReport->lesson_date->format('d/m/Y') }}
                        &nbsp;|&nbsp;
                        <i class="bi bi-person me-1"></i>{{ $lessonReport->teacher->name ?? 'N/A' }}
                        &nbsp;|&nbsp;
                        <i class="bi bi-collection me-1"></i>{{ $lessonReport->period_count }} tiết — Lớp {{ $lessonReport->class_name }}
                    </div>
                </div>
                <span class="badge {{ $lessonReport->session === 'sang' ? 'bg-warning text-dark' : 'bg-info text-dark' }} fs-6 px-3 py-2">
                    {{ $lessonReport->session === 'sang' ? '☀️ Sáng' : '🌙 Chiều' }}
                </span>
            </div>
        </div>
    </div>

    {{-- THIẾT BỊ ĐÃ DÙNG --}}
    @if($lessonReport->deviceUsages->isNotEmpty())
    <div class="card mb-4">
        <div class="card-header py-3 px-4">
            <i class="bi bi-box-seam me-2 text-success"></i>
            <span class="fw-semibold">Thiết bị được sử dụng</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Thiết bị</th>
                        <th class="text-center">ĐVT</th>
                        <th class="text-center">Số lượng dùng</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lessonReport->deviceUsages as $usage)
                    <tr>
                        <td class="fw-semibold">{{ $usage->device->name ?? 'N/A' }}</td>
                        <td class="text-center text-muted">{{ $usage->device->unit ?? '' }}</td>
                        <td class="text-center">
                            <span class="badge bg-success">{{ $usage->quantity_used }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- SỰ CỐ --}}
    @if($lessonReport->issues->isNotEmpty())
    <div class="card mb-4 border-danger border-opacity-25">
        <div class="card-header py-3 px-4 bg-danger bg-opacity-10">
            <i class="bi bi-exclamation-triangle me-2 text-danger"></i>
            <span class="fw-semibold text-danger">Sự cố thiết bị</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Thiết bị</th>
                        <th class="text-center">Số hỏng</th>
                        <th class="text-center">Tiêu hao</th>
                        <th class="text-center text-danger">Mất</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lessonReport->issues as $issue)
                    <tr>
                        <td class="fw-semibold">{{ $issue->device->name ?? 'N/A' }}</td>
                        <td class="text-center">
                            @if($issue->broken_qty > 0)
                                <span class="badge bg-danger">{{ $issue->broken_qty }}</span>
                            @else <span class="text-muted">—</span> @endif
                        </td>
                        <td class="text-center">
                            @if($issue->consumed_qty > 0)
                                <span class="badge bg-warning text-dark">{{ $issue->consumed_qty }}</span>
                            @else <span class="text-muted">—</span> @endif
                        </td>
                        <td class="text-center">
                            @if(($issue->lost_qty ?? 0) > 0)
                                <span class="badge bg-dark">{{ $issue->lost_qty }}</span>
                            @else <span class="text-muted">—</span> @endif
                        </td>
                        <td class="text-muted" style="font-size:.85rem;">{{ $issue->note ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- GHI CHÚ GIÁO VIÊN --}}
    @if($lessonReport->teacher_note)
    <div class="card mb-4">
        <div class="card-header py-3 px-4">
            <i class="bi bi-chat-left-text me-2 text-primary"></i>
            <span class="fw-semibold">Ghi chú của giáo viên</span>
        </div>
        <div class="card-body px-4 py-3">
            <p class="mb-0" style="font-size:.9rem;line-height:1.7;white-space:pre-line;">{{ $lessonReport->teacher_note }}</p>
        </div>
    </div>
    @endif

</div>

{{-- CỘT PHẢI: THÔNG TIN NHANH --}}
<div class="col-12 col-lg-4">

    <div class="card mb-4">
        <div class="card-header py-3 px-4">
            <i class="bi bi-info-circle me-2 text-primary"></i>
            <span class="fw-semibold">Thông tin tiết dạy</span>
        </div>
        <div class="card-body p-4">
            <table class="table table-sm table-borderless mb-0">
                <tbody>
                    <tr>
                        <td class="text-muted" style="font-size:.85rem; width:40%;">Giáo viên</td>
                        <td class="fw-semibold" style="font-size:.88rem;">{{ $lessonReport->teacher->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted" style="font-size:.85rem;">Môn học</td>
                        <td style="font-size:.88rem;">{{ $lessonReport->subject ?? $lessonReport->teacher->teaching_subject ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted" style="font-size:.85rem;">Phòng</td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold" style="font-size:.78rem;">
                                {{ $lessonReport->room->name ?? 'N/A' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted" style="font-size:.85rem;">Ngày dạy</td>
                        <td class="fw-semibold" style="font-size:.88rem;">{{ $lessonReport->lesson_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted" style="font-size:.85rem;">Buổi</td>
                        <td>
                            <span class="badge {{ $lessonReport->session === 'sang' ? 'bg-warning text-dark' : 'bg-info text-dark' }}" style="font-size:.78rem;">
                                {{ $lessonReport->session === 'sang' ? '☀️ Sáng' : '🌙 Chiều' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted" style="font-size:.85rem;">Số tiết</td>
                        <td class="fw-semibold" style="font-size:.88rem;">{{ $lessonReport->period_count }} tiết</td>
                    </tr>
                    <tr>
                        <td class="text-muted" style="font-size:.85rem;">Lớp</td>
                        <td class="fw-semibold" style="font-size:.88rem;">{{ $lessonReport->class_name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted" style="font-size:.85rem;">Nhập lúc</td>
                        <td style="font-size:.82rem; color:#666;">{{ $lessonReport->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- TÓM TẮT SỰ CỐ --}}
    @if($lessonReport->issues->isNotEmpty())
    <div class="card border-danger border-opacity-25">
        <div class="card-body p-4 text-center">
            <div class="text-danger fw-bold mb-1" style="font-size:1rem;">
                <i class="bi bi-exclamation-triangle me-1"></i>Có {{ $lessonReport->issues->count() }} sự cố
            </div>
            <div class="text-muted" style="font-size:.82rem;">
                Hỏng: <strong>{{ $lessonReport->issues->sum('broken_qty') }}</strong>
                &nbsp;|&nbsp;
                Tiêu hao: <strong>{{ $lessonReport->issues->sum('consumed_qty') }}</strong>
                &nbsp;|&nbsp;
                Mất: <strong>{{ $lessonReport->issues->sum('lost_qty') }}</strong>
            </div>
        </div>
    </div>
    @else
    <div class="card border-success border-opacity-25">
        <div class="card-body p-3 text-center">
            <div class="text-success" style="font-size:.88rem;">
                <i class="bi bi-check-circle me-1"></i>Không có sự cố
            </div>
        </div>
    </div>
    @endif

</div>
</div>
@endsection
