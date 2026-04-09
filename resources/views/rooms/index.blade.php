@extends('layouts.app')
@section('title', 'Quản lý phòng thực hành')
@section('page-title', 'Quản lý phòng thực hành')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1">Danh sách phòng thực hành</h5>
        <div class="text-muted" style="font-size:.85rem;">Quản lý 3 phòng: Vật Lý, Hoá Học, Sinh Học</div>
    </div>
    <a href="{{ route('rooms.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Thêm phòng
    </a>
</div>

<div class="row g-4">
    @forelse($rooms as $room)
    @php
        $colors = ['ly'=>'linear-gradient(135deg,#3b82f6,#1d4ed8)','hoa'=>'linear-gradient(135deg,#f59e0b,#b45309)','sinh'=>'linear-gradient(135deg,#10b981,#065f46)'];
        $grad = $colors[$room->subject] ?? 'linear-gradient(135deg,#6366f1,#4338ca)';
    @endphp
    <div class="col-12 col-md-4">
        <div class="card h-100">
            <div style="background:{{ $grad }};border-radius:12px 12px 0 0;padding:24px 20px;color:#fff;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:13px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-door-open fs-3"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $room->name }}</h5>
                        <div style="font-size:.8rem;opacity:.8;">{{ ['ly'=>'Vật Lý','hoa'=>'Hoá Học','sinh'=>'Sinh Học'][$room->subject] ?? '' }}</div>
                    </div>
                </div>
                @if($room->location)
                <div class="mt-2" style="font-size:.8rem;opacity:.8;"><i class="bi bi-geo-alt me-1"></i>{{ $room->location }}</div>
                @endif
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-person-badge text-primary"></i>
                    <span style="font-size:.875rem;">
                        <strong>Quản lý:</strong>
                        {{ $room->manager?->name ?? '<span class="text-danger">Chưa có</span>' }}
                    </span>
                </div>
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="bg-light rounded p-2">
                            <div class="fw-bold text-primary">{{ $room->devices->count() }}</div>
                            <div class="text-muted" style="font-size:.72rem;">Loại TB</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light rounded p-2">
                            <div class="fw-bold text-success">{{ $room->devices->sum('quantity') }}</div>
                            <div class="text-muted" style="font-size:.72rem;">Tổng SL</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light rounded p-2">
                            <div class="fw-bold text-warning">{{ $room->pendingReportsCount() }}</div>
                            <div class="text-muted" style="font-size:.72rem;">Chờ xác nhận</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white py-3 d-flex gap-2">
                <a href="{{ route('rooms.edit', $room) }}" class="btn btn-sm btn-outline-primary flex-fill">
                    <i class="bi bi-pencil me-1"></i>Sửa
                </a>
                <form method="POST" action="{{ route('rooms.destroy', $room) }}" class="flex-fill">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100"
                            onclick="return confirmDelete(event, '{{ $room->name }}')">
                        <i class="bi bi-trash me-1"></i>Xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card p-5 text-center text-muted">
            <i class="bi bi-door-open fs-1 opacity-25 d-block mb-3"></i>
            Chưa có phòng nào. <a href="{{ route('rooms.create') }}">Tạo phòng đầu tiên</a>.
        </div>
    </div>
    @endforelse
</div>
@endsection
