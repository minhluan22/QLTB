@extends('layouts.app')

@section('title', 'Thêm thiết bị')
@section('page-title', 'Thêm thiết bị mới')

@section('content')
<div class="mb-4">
    <a href="{{ route('devices.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Quay lại
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-xl-9">
        <div class="card">
            <div class="card-header py-3 px-4">
                <i class="bi bi-plus-circle text-primary me-2"></i>
                <span class="fw-semibold">Thông tin thiết bị</span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('devices.store') }}">
                    @csrf

                    {{-- ── THÔNG TIN CƠ BẢN ── --}}
                    <h6 class="fw-bold text-muted mb-3 section-title">Thông tin cơ bản</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold req">Mã thiết bị</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code') }}" placeholder="VD: TDD-180-VN">
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold req">Tên thiết bị</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="VD: Thước đo độ nhựa">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Môn học</label>
                            <input type="text" name="subject" class="form-control" list="subject-list"
                                   value="{{ old('subject') }}" placeholder="VD: Toán, Lý...">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Tổ</label>
                            <input type="text" name="subject_group" class="form-control" list="group-list"
                                   value="{{ old('subject_group') }}" placeholder="VD: Tổ Tự nhiên">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Danh mục</label>
                            <input type="text" name="category" class="form-control" list="cat-list"
                                   value="{{ old('category') }}" placeholder="VD: Thiết bị thực hành">
                            <datalist id="cat-list">
                                @foreach($categories as $c)<option value="{{ $c }}">@endforeach
                            </datalist>
                        </div>
                    </div>

                    {{-- ── THÔNG TIN NHẬP KHO (LẦN ĐẦU) ── --}}
                    <h6 class="fw-bold text-muted mb-3 section-title">Thông tin nhập kho (Lần đầu)</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold req">Số lượng nhập</label>
                            <input type="number" name="quantity" id="qty"
                                   class="form-control @error('quantity') is-invalid @enderror"
                                   value="{{ old('quantity', 1) }}" min="1">
                            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold" style="color:#10b981;">Thành tiền</label>
                            <input type="text" id="total_value" class="form-control bg-light" readonly
                                   placeholder="0 ₫" style="color:#10b981;font-weight:700;">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold">Đơn giá nhập (VNĐ)</label>
                            <input type="number" name="unit_price" id="unit_price"
                                   class="form-control" value="{{ old('unit_price') }}"
                                   min="0" step="1000" placeholder="0">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold">Đơn vị</label>
                            <input type="text" name="unit" class="form-control" list="unit-list"
                                   value="{{ old('unit', 'Cái') }}">
                            <datalist id="unit-list">
                                <option value="Cái"><option value="Bộ"><option value="Chiếc">
                                <option value="Hộp"><option value="Quyển"><option value="Tờ">
                            </datalist>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label fw-semibold req">Ngày nhập</label>
                            <input type="date" name="import_date" class="form-control @error('import_date') is-invalid @enderror"
                                   value="{{ old('import_date', date('Y-m-d')) }}">
                            @error('import_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label fw-semibold">Nhà cung cấp</label>
                            <input type="text" name="supplier" class="form-control"
                                   value="{{ old('supplier') }}" placeholder="Tên đơn vị cấp/bán...">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Nước sản xuất</label>
                            <input type="text" name="country" class="form-control" list="country-list"
                                   value="{{ old('country') }}" placeholder="VD: Việt Nam">
                            <datalist id="country-list">
                                <option value="Việt Nam"><option value="Trung Quốc">
                                <option value="Nhật Bản"><option value="Hàn Quốc">
                                <option value="Đức"><option value="Mỹ">
                            </datalist>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Nhãn hiệu</label>
                            <input type="text" name="brand" class="form-control" value="{{ old('brand') }}" placeholder="Ví dụ: Thiên Long, Dell...">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Năm sản xuất</label>
                            <input type="number" name="production_year" class="form-control @error('production_year') is-invalid @enderror"
                                   value="{{ old('production_year') }}" min="1900" max="{{ date('Y') + 1 }}" placeholder="VD: 2024">
                            @error('production_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- ── MÔ TẢ ── --}}
                    <h6 class="fw-bold text-muted mb-3 section-title">Mô tả</h6>
                    <div class="mb-4">
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Ghi chú thêm về thiết bị...">{{ old('description') }}</textarea>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('devices.index') }}" class="btn btn-outline-secondary">Hủy</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Thêm thiết bị
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.section-title {
    font-size:.72rem; text-transform:uppercase; letter-spacing:.06em;
    padding-bottom:8px; border-bottom:2px solid #f1f5f9; margin-bottom:16px !important;
}
.form-label.req::after { content:" *"; color:#ef4444; }
</style>
@endpush

@push('scripts')
<script>
function calcTotal() {
    const qty   = parseFloat(document.getElementById('qty').value) || 0;
    const price = parseFloat(document.getElementById('unit_price').value) || 0;
    const total = qty * price;
    document.getElementById('total_value').value = total.toLocaleString('vi-VN') + ' ₫';
}
document.getElementById('qty').addEventListener('input', calcTotal);
document.getElementById('unit_price').addEventListener('input', calcTotal);
calcTotal();
</script>
@endpush
@endsection
