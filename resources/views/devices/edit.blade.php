@extends('layouts.app')

@section('title', 'Chỉnh sửa: ' . $device->name)
@section('page-title', 'Chỉnh sửa thiết bị')

@section('content')
<div class="mb-4 d-flex align-items-center gap-3">
    <a href="{{ route('devices.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 class="fw-bold mb-0" style="font-size:1.2rem;">Chỉnh sửa: {{ $device->name }}</h2>
        <p class="text-muted mb-0" style="font-size:.8rem;">Mã: <code>{{ $device->code }}</code></p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-xl-9">
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('devices.update', $device) }}">
                    @csrf @method('PUT')

                    {{-- ── THÔNG TIN CƠ BẢN ── --}}
                    <h6 class="fw-bold text-muted mb-3 section-title">Thông tin cơ bản</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold req">Mã thiết bị</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code', $device->code) }}">
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold req">Tên thiết bị</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $device->name) }}">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Môn học</label>
                            <input type="text" name="subject" class="form-control" list="subject-list"
                                   value="{{ old('subject', $device->subject) }}">
                            <datalist id="subject-list">
                                @foreach($subjects as $s)<option value="{{ $s }}">@endforeach
                                <option value="Toán"><option value="Lý"><option value="Hóa">
                                <option value="Sinh"><option value="Tin học">
                            </datalist>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Tổ</label>
                            <input type="text" name="subject_group" class="form-control" list="group-list"
                                   value="{{ old('subject_group', $device->subject_group) }}">
                            <datalist id="group-list">
                                <option value="Toán"><option value="Vật lý"><option value="Hóa học"><option value="Sinh học">
                                <option value="Ngữ văn"><option value="Lịch sử"><option value="Địa lý"><option value="GDCD">
                                <option value="Tin học"><option value="Ngoại ngữ"><option value="Thể dục">
                            </datalist>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Danh mục</label>
                            <input type="text" name="category" class="form-control" list="cat-list"
                                   value="{{ old('category', $device->category) }}">
                            <datalist id="cat-list">
                                @foreach($categories as $c)<option value="{{ $c }}">@endforeach
                            </datalist>
                        </div>
                    </div>

                    {{-- ── GIÁ TRỊ & XUẤT XỨ ── --}}
                    <h6 class="fw-bold text-muted mb-3 section-title">Giá trị & Xuất xứ</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-4">
                            <label class="form-label fw-semibold">Đơn vị</label>
                            <input type="text" name="unit" class="form-control" list="unit-list"
                                   value="{{ old('unit', $device->unit) }}">
                            <datalist id="unit-list">
                                <option value="Cái"><option value="Bộ"><option value="Chiếc">
                            </datalist>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label fw-semibold">Đơn giá (VNĐ)</label>
                            <input type="number" name="unit_price" class="form-control"
                                   value="{{ old('unit_price', $device->unit_price) }}" min="0" step="1000">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-success">Thành tiền</label>
                            <input type="text" id="total_value" class="form-control bg-light text-success fw-bold" readonly placeholder="0 ₫">
                        </div>
                    </div>

                    {{-- ── TRẠNG THÁI & SỐ LƯỢNG (readonly) ── --}}
                    <h6 class="fw-bold text-muted mb-3 section-title">Trạng thái & Số lượng</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold req">Trạng thái</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="available"   {{ old('status',$device->status)=='available'   ? 'selected':'' }}>✅ Sẵn sàng</option>
                                <option value="borrowed"    {{ old('status',$device->status)=='borrowed'    ? 'selected':'' }}>🔶 Đang mượn</option>
                                <option value="maintenance" {{ old('status',$device->status)=='maintenance' ? 'selected':'' }}>🔧 Bảo trì</option>
                                <option value="damaged"     {{ old('status',$device->status)=='damaged'     ? 'selected':'' }}>❌ Hỏng</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="total_qty" class="form-label fw-semibold">Tổng số lượng</label>
                            <input type="number" name="quantity" id="total_qty" class="form-control bg-light text-primary fw-bold" value="{{ old('quantity', $device->quantity) }}" readonly>
                        </div>
                        <div class="col-4 col-md-2">
                            <label for="damaged_qty" class="form-label fw-semibold">Hỏng</label>
                            <input type="number" name="damaged_qty" id="damaged_qty" class="form-control bg-light text-warning fw-bold" value="{{ old('damaged_qty', $device->damaged_qty) }}" readonly>
                        </div>
                        <div class="col-4 col-md-2">
                            <label for="lost_qty" class="form-label fw-semibold">Mất</label>
                            <input type="number" name="lost_qty" id="lost_qty" class="form-control bg-light text-danger fw-bold" value="{{ old('lost_qty', $device->lost_qty) }}" readonly>
                        </div>
                        <div class="col-4 col-md-2">
                            <label class="form-label fw-semibold">Còn lại</label>
                            <input type="text" id="remaining_display" class="form-control bg-light text-success fw-bold" value="{{ $device->remainingQty() }}" readonly>
                        </div>
                    </div>

                    {{-- ── MÔ TẢ ── --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Mô tả / Ghi chú</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $device->description) }}</textarea>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('devices.index') }}" class="btn btn-outline-secondary">Hủy</a>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save me-1"></i>Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.section-title { font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; padding-bottom:8px; border-bottom:2px solid #f1f5f9; margin-bottom:16px !important; }
.form-label.req::after { content:" *"; color:#ef4444; }
</style>
@endpush

@push('scripts')
<script>
function updateRemaining() {
    let total = parseInt(document.getElementById('total_qty').value) || 0;
    let damaged = parseInt(document.getElementById('damaged_qty').value) || 0;
    let lost = parseInt(document.getElementById('lost_qty').value) || 0;
    
    let remaining = total - damaged - lost;
    if (remaining < 0) remaining = 0;
    
    document.getElementById('remaining_display').value = remaining;

    // Tính thành tiền
    let price = parseFloat(document.querySelector('input[name="unit_price"]').value) || 0;
    document.getElementById('total_value').value = (total * price).toLocaleString('vi-VN') + ' ₫';
}

document.getElementById('total_qty').addEventListener('input', updateRemaining);
document.getElementById('damaged_qty').addEventListener('input', updateRemaining);
document.getElementById('lost_qty').addEventListener('input', updateRemaining);
document.querySelector('input[name="unit_price"]').addEventListener('input', updateRemaining);

// Tính ngay khi vừa tải trang
updateRemaining();
</script>
@endpush
@endsection
