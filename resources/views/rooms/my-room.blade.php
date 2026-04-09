@extends('layouts.app')
@section('title', 'Phòng của tôi: ' . $room->name)
@section('page-title', 'Phòng của tôi')

@section('content')

{{-- HEADER PHÒNG --}}
@php
    $grads = ['ly'=>'linear-gradient(135deg,#3b82f6,#1d4ed8)','hoa'=>'linear-gradient(135deg,#f59e0b,#b45309)','sinh'=>'linear-gradient(135deg,#10b981,#065f46)'];
    $grad  = $grads[$room->subject] ?? 'linear-gradient(135deg,#6366f1,#4338ca)';
@endphp
<div class="card mb-4 border-0" style="background:{{ $grad }};border-radius:16px;color:#fff;">
    <div class="card-body p-4">
        <div class="d-flex align-items-center gap-3">
            <div style="width:60px;height:60px;background:rgba(255,255,255,.18);border-radius:14px;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-door-open fs-2"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-1">{{ $room->name }}</h4>
                <div style="font-size:.85rem;opacity:.85;">{{ $room->location ?? 'Chưa cập nhật vị trí' }}</div>
            </div>
            <a href="{{ route('room-devices.create') }}" class="btn btn-light ms-auto fw-semibold">
                <i class="bi bi-plus-circle me-1"></i>Thêm thiết bị
            </a>
        </div>
    </div>
</div>

{{-- STAT CARDS --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card p-3 text-center">
            <div class="fw-bold fs-4 text-primary">{{ $stats['total_devices'] }}</div>
            <div class="text-muted" style="font-size:.8rem;">Loại thiết bị</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 text-center">
            <div class="fw-bold fs-4 text-success">{{ $stats['total_qty'] }}</div>
            <div class="text-muted" style="font-size:.8rem;">Tổng số lượng</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 text-center">
            <div class="fw-bold fs-4 text-danger">{{ $stats['broken_qty'] }}</div>
            <div class="text-muted" style="font-size:.8rem;">Đã hỏng</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 text-center">
            <div class="fw-bold fs-4 text-secondary">{{ $room->devices()->sum('lost_qty') }}</div>
            <div class="text-muted" style="font-size:.8rem;">Đã mất</div>
        </div>
    </div>
</div>

{{-- QUICK LINKS --}}
<div class="d-flex gap-2 mb-4 flex-wrap">
    <a href="{{ route('room-devices.index') }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-box-seam me-1"></i>Danh sách thiết bị
    </a>
    <a href="{{ route('room-devices.status') }}" class="btn btn-warning btn-sm text-dark">
        <i class="bi bi-clipboard2-check me-1"></i>Cập nhật tình trạng
    </a>
    <a href="{{ route('lesson-reports.room-index') }}" class="btn btn-outline-success btn-sm">
        <i class="bi bi-clipboard2-check me-1"></i>Xem báo cáo tiết
    </a>
</div>

{{-- DANH SÁCH THIẾT BỊ --}}
<div class="card">
    <div class="card-header py-3 px-4 d-flex align-items-center justify-content-between">
        <span class="fw-semibold"><i class="bi bi-box-seam me-2 text-primary"></i>Thiết bị trong phòng</span>
        <span class="badge bg-secondary">{{ $stats['total_devices'] }} loại</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tên thiết bị</th>
                    <th>ĐVT</th>
                    <th class="text-center">Tổng</th>
                    <th class="text-center text-danger">Hỏng</th>
                    <th class="text-center text-warning">Tiêu hao</th>
                    <th class="text-center">Mất</th>
                    <th class="text-center text-success">Còn lại</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $dev)
                <tr>
                    <td class="fw-semibold">{{ $dev->name }}</td>
                    <td class="text-muted">{{ $dev->unit }}</td>
                    <td class="text-center fw-bold">{{ $dev->quantity }}</td>
                    <td class="text-center">
                        @if($dev->broken_qty > 0)
                            <span class="badge bg-danger">{{ $dev->broken_qty }}</span>
                        @else <span class="text-muted">-</span> @endif
                    </td>
                    <td class="text-center">
                        @if($dev->consumed_qty > 0)
                            <span class="badge bg-warning text-dark">{{ $dev->consumed_qty }}</span>
                        @else <span class="text-muted">-</span> @endif
                    </td>
                    <td class="text-center">
                        @if(($dev->lost_qty ?? 0) > 0)
                            <span class="badge bg-secondary">{{ $dev->lost_qty }}</span>
                        @else <span class="text-muted">-</span> @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $dev->statusColor() }}">{{ $dev->availableQty() }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        Chưa có thiết bị. <a href="{{ route('room-devices.create') }}">Thêm ngay</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($devices->hasPages())
    <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
        <div class="text-muted" style="font-size:.8rem;">
            Hiển thị {{ $devices->firstItem() }}–{{ $devices->lastItem() }} / {{ $devices->total() }} thiết bị
        </div>
        {{ $devices->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
