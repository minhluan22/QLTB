@extends('layouts.app')
@section('title', 'Tình trạng thiết bị — ' . $room->name)
@section('page-title', 'Cập nhật tình trạng thiết bị')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1">{{ $room->name }}</h5>
        <div class="text-muted" style="font-size:.85rem;">
            Nhập số lượng hỏng, tiêu hao, mất cho từng thiết bị — hệ thống tự tính <strong>Còn dùng</strong>.
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('room-devices.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-list-ul me-1"></i>Danh sách thiết bị
        </a>
        <a href="{{ route('my-room') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-house me-1"></i>Phòng của tôi
        </a>
    </div>
</div>

{{-- ALERT ERRORS --}}
@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($devices->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block opacity-25 mb-2"></i>
            Chưa có thiết bị nào trong phòng.
            <a href="{{ route('room-devices.create') }}" class="d-block mt-2">Thêm thiết bị đầu tiên</a>
        </div>
    </div>
@else

<form method="POST" action="{{ route('room-devices.batch-update') }}" id="statusForm">
    @csrf

    <div class="card">
        <div class="card-header py-3 px-4 d-flex align-items-center justify-content-between">
            <span class="fw-semibold">
                <i class="bi bi-clipboard2-check me-2 text-primary"></i>
                Bảng tình trạng thiết bị
            </span>
            <span class="text-muted" style="font-size:.82rem;">{{ $devices->count() }} thiết bị</span>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0" id="statusTable">
                <thead class="table-primary">
                    <tr>
                        <th style="min-width:180px;">Tên thiết bị</th>
                        <th class="text-center" style="width:60px;">ĐVT</th>
                        <th class="text-center" style="width:80px;">Tổng SL</th>
                        <th class="text-center" style="width:110px;">
                            <span class="text-danger">Hỏng</span>
                        </th>
                        <th class="text-center" style="width:110px;">
                            <span class="text-warning">Tiêu hao</span>
                        </th>
                        <th class="text-center" style="width:110px;">
                            <span class="text-secondary">Mất</span>
                        </th>
                        <th class="text-center" style="width:100px;">
                            <span class="text-success">Còn dùng</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devices as $dev)
                    <tr data-id="{{ $dev->id }}" data-qty="{{ $dev->quantity }}">
                        {{-- Tên --}}
                        <td>
                            <div class="fw-semibold">{{ $dev->name }}</div>
                            @if($dev->note)
                                <div class="text-muted" style="font-size:.75rem;">{{ Str::limit($dev->note, 45) }}</div>
                            @endif
                        </td>

                        {{-- ĐVT --}}
                        <td class="text-center text-muted">{{ $dev->unit }}</td>

                        {{-- Tổng SL --}}
                        <td class="text-center fw-bold fs-5">{{ $dev->quantity }}</td>

                        {{-- Hỏng --}}
                        <td class="text-center">
                            <input type="number"
                                   name="devices[{{ $dev->id }}][broken_qty]"
                                   class="form-control form-control-sm text-center status-input"
                                   style="width:80px; margin:auto; border-color:#dc3545;"
                                   value="{{ old('devices.' . $dev->id . '.broken_qty', $dev->broken_qty) }}"
                                   min="0" max="{{ $dev->quantity }}">
                        </td>

                        {{-- Tiêu hao --}}
                        <td class="text-center">
                            <input type="number"
                                   name="devices[{{ $dev->id }}][consumed_qty]"
                                   class="form-control form-control-sm text-center status-input"
                                   style="width:80px; margin:auto; border-color:#ffc107;"
                                   value="{{ old('devices.' . $dev->id . '.consumed_qty', $dev->consumed_qty) }}"
                                   min="0" max="{{ $dev->quantity }}">
                        </td>

                        {{-- Mất --}}
                        <td class="text-center">
                            <input type="number"
                                   name="devices[{{ $dev->id }}][lost_qty]"
                                   class="form-control form-control-sm text-center status-input"
                                   style="width:80px; margin:auto; border-color:#6c757d;"
                                   value="{{ old('devices.' . $dev->id . '.lost_qty', $dev->lost_qty ?? 0) }}"
                                   min="0" max="{{ $dev->quantity }}">
                        </td>

                        {{-- Còn dùng (tính realtime) --}}
                        <td class="text-center">
                            <span class="badge fs-6 px-3 available-badge bg-{{ $dev->statusColor() }}"
                                  id="avail-{{ $dev->id }}">
                                {{ $dev->availableQty() }}
                            </span>
                            <div class="text-muted" style="font-size:.7rem;">{{ $dev->unit }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

                {{-- TỔNG KẾT --}}
                <tfoot class="table-light fw-semibold">
                    <tr>
                        <td colspan="2" class="text-end">Tổng cộng:</td>
                        <td class="text-center" id="totalQty">{{ $devices->sum('quantity') }}</td>
                        <td class="text-center text-danger" id="totalBroken">{{ $devices->sum('broken_qty') }}</td>
                        <td class="text-center text-warning" id="totalConsumed">{{ $devices->sum('consumed_qty') }}</td>
                        <td class="text-center" id="totalLost">{{ $devices->sum('lost_qty') }}</td>
                        <td class="text-center text-success" id="totalAvail">{{ $devices->sum(fn($d) => $d->availableQty()) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="card-footer bg-white px-4 py-3 d-flex justify-content-between align-items-center">
            <div class="text-muted" style="font-size:.82rem;">
                <i class="bi bi-info-circle me-1"></i>
                Còn dùng = Tổng SL &minus; Hỏng &minus; Tiêu hao &minus; Mất
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('room-devices.index') }}" class="btn btn-outline-secondary">Huỷ</a>
                <button type="submit" class="btn btn-primary" id="saveBtn">
                    <i class="bi bi-save me-1"></i>Lưu tình trạng
                </button>
            </div>
        </div>
    </div>
</form>

@endif

@push('scripts')
<script>
/**
 * Realtime: tính lại "Còn dùng" và tổng kết khi nhập số
 */
document.querySelectorAll('#statusTable tbody tr').forEach(function(row) {
    const devId  = row.dataset.id;
    const total  = parseInt(row.dataset.qty) || 0;
    const badge  = document.getElementById('avail-' + devId);
    const inputs = row.querySelectorAll('.status-input');

    function recalc() {
        let used = 0;
        inputs.forEach(inp => used += parseInt(inp.value) || 0);
        const avail = Math.max(0, total - used);
        badge.textContent = avail;

        // Màu badge
        badge.className = 'badge fs-6 px-3 available-badge';
        const ratio = total > 0 ? avail / total : 1;
        if (ratio <= 0)    badge.classList.add('bg-danger');
        else if (ratio <= 0.25) badge.classList.add('bg-warning', 'text-dark');
        else               badge.classList.add('bg-success');

        // Cảnh báo nếu vượt quá
        if (used > total) {
            row.classList.add('table-danger');
        } else {
            row.classList.remove('table-danger');
        }

        recalcFooter();
    }

    inputs.forEach(inp => inp.addEventListener('input', recalc));
    recalc();
});

function recalcFooter() {
    let tBroken = 0, tConsumed = 0, tLost = 0, tAvail = 0;
    document.querySelectorAll('#statusTable tbody tr').forEach(function(row) {
        const total  = parseInt(row.dataset.qty) || 0;
        const broken   = parseInt(row.querySelectorAll('.status-input')[0].value) || 0;
        const consumed = parseInt(row.querySelectorAll('.status-input')[1].value) || 0;
        const lost     = parseInt(row.querySelectorAll('.status-input')[2].value) || 0;
        tBroken   += broken;
        tConsumed += consumed;
        tLost     += lost;
        tAvail    += Math.max(0, total - broken - consumed - lost);
    });
    document.getElementById('totalBroken').textContent   = tBroken;
    document.getElementById('totalConsumed').textContent = tConsumed;
    document.getElementById('totalLost').textContent     = tLost;
    document.getElementById('totalAvail').textContent    = tAvail;
}
</script>
@endpush

@endsection
