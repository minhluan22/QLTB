@extends('layouts.app')
@section('title', 'Sửa thiết bị: ' . $roomDevice->name)
@section('page-title', 'Cập nhật thiết bị')

@section('content')
<div class="mb-4">
    <a href="{{ route('room-devices.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Quay lại
    </a>
</div>
<div class="row justify-content-center">
<div class="col-12 col-lg-6">
<div class="card">
    <div class="card-header py-3 px-4">
        <i class="bi bi-pencil-square text-primary me-2"></i>
        <span class="fw-semibold">Cập nhật: {{ $roomDevice->name }}</span>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('room-devices.update', $roomDevice) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Tên thiết bị <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $roomDevice->name) }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Đơn vị tính <span class="text-danger">*</span></label>
                    <select name="unit" class="form-select">
                        @foreach(['cái','bộ','hộp','ml','lít','gói','chai'] as $u)
                            <option value="{{ $u }}" {{ old('unit',$roomDevice->unit)===$u ? 'selected':'' }}>{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Tổng số lượng <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" class="form-control"
                           value="{{ old('quantity', $roomDevice->quantity) }}" min="0">
                </div>

                <div class="col-12"><hr class="my-1"><p class="text-muted mb-0" style="font-size:.8rem;">Tình trạng thực tế (hoặc dùng <a href="{{ route('room-devices.status') }}">Cập nhật tình trạng hàng loạt</a>)</p></div>

                <div class="col-4">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Số hỏng</label>
                    <input type="number" name="broken_qty" class="form-control"
                           value="{{ old('broken_qty', $roomDevice->broken_qty) }}" min="0"
                           style="border-color:#dc3545;">
                </div>
                <div class="col-4">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Tiêu hao</label>
                    <input type="number" name="consumed_qty" class="form-control"
                           value="{{ old('consumed_qty', $roomDevice->consumed_qty) }}" min="0"
                           style="border-color:#ffc107;">
                </div>
                <div class="col-4">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Mất</label>
                    <input type="number" name="lost_qty" class="form-control"
                           value="{{ old('lost_qty', $roomDevice->lost_qty ?? 0) }}" min="0"
                           style="border-color:#6c757d;">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="2">{{ old('note', $roomDevice->note) }}</textarea>
                </div>
            </div>
            <div class="d-flex gap-2 justify-content-end mt-4">
                <a href="{{ route('room-devices.index') }}" class="btn btn-outline-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
