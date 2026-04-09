@extends('layouts.app')

@section('title', 'Nhập kho mới')
@section('page-title', 'Nhập kho thiết bị')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('imports.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="fw-bold mb-0" style="font-size:1.2rem;">Nhập kho thiết bị</h2>
                <p class="text-muted mb-0" style="font-size:.8rem;">Ghi nhận lô hàng nhập vào và cập nhật số lượng tự động</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('imports.store') }}">
                    @csrf

                    <div class="row g-3">
                        {{-- Chọn thiết bị --}}
                        <div class="col-12">
                            <label for="device_id" class="form-label fw-semibold">
                                Thiết bị <span class="text-danger">*</span>
                            </label>
                            <select id="device_id" name="device_id"
                                class="form-select @error('device_id') is-invalid @enderror">
                                <option value="">-- Chọn thiết bị --</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}" {{ old('device_id')==$device->id ? 'selected' : '' }}>
                                        [{{ $device->code }}] {{ $device->name }} (Hiện có: {{ $device->quantity }})
                                    </option>
                                @endforeach
                            </select>
                            @error('device_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Số lượng và ngày nhập --}}
                        <div class="col-6">
                            <label for="quantity" class="form-label fw-semibold">
                                Số lượng nhập <span class="text-danger">*</span>
                            </label>
                            <input type="number" id="quantity" name="quantity"
                                class="form-control @error('quantity') is-invalid @enderror"
                                value="{{ old('quantity', 1) }}" min="1">
                            @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-6">
                            <label for="import_date" class="form-label fw-semibold">
                                Ngày nhập <span class="text-danger">*</span>
                            </label>
                            <input type="date" id="import_date" name="import_date"
                                class="form-control @error('import_date') is-invalid @enderror"
                                value="{{ old('import_date', date('Y-m-d')) }}">
                            @error('import_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Nhà cung cấp và giá --}}
                        <div class="col-12 col-md-7">
                            <label for="supplier" class="form-label fw-semibold">Nhà cung cấp</label>
                            <input type="text" id="supplier" name="supplier"
                                class="form-control @error('supplier') is-invalid @enderror"
                                value="{{ old('supplier') }}"
                                placeholder="Tên công ty cung cấp...">
                            @error('supplier') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-5">
                            <label for="price" class="form-label fw-semibold">Đơn giá (₫/cái)</label>
                            <input type="number" id="price" name="price"
                                class="form-control @error('price') is-invalid @enderror"
                                value="{{ old('price') }}"
                                placeholder="0" min="0" step="1000">
                            @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Tổng giá trị (readonly, tự tính) --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Tổng giá trị lô hàng</label>
                            <div class="input-group">
                                <input type="text" id="total_value_display" class="form-control bg-light fw-bold"
                                       readonly placeholder="0 ₫" style="color:#059669;font-size:1rem;">
                                <span class="input-group-text bg-light text-muted" style="font-size:.8rem;">
                                    = Số lượng × Đơn giá
                                </span>
                            </div>
                        </div>

                        {{-- Ghi chú --}}
                        <div class="col-12">
                            <label for="note" class="form-label fw-semibold">Ghi chú</label>
                            <textarea id="note" name="note" rows="3"
                                class="form-control @error('note') is-invalid @enderror"
                                placeholder="Thông tin bổ sung về lô hàng...">{{ old('note') }}</textarea>
                            @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Info box --}}
                    <div class="alert alert-info d-flex gap-2 mt-3 mb-0" style="font-size:.85rem;">
                        <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                        <div>
                            Sau khi lưu, số lượng thiết bị được chọn sẽ <strong>tự động tăng lên</strong> theo số lượng nhập.
                            Đơn giá cũng được cập nhật vào thông tin thiết bị.
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('imports.index') }}" class="btn btn-outline-secondary">Huỷ</a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>Xác nhận nhập kho
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function calcImportTotal() {
    const qty   = parseFloat(document.getElementById('quantity').value) || 0;
    const price = parseFloat(document.getElementById('price').value) || 0;
    const total = qty * price;
    document.getElementById('total_value_display').value =
        total > 0 ? total.toLocaleString('vi-VN') + ' ₫' : '0 ₫';
}
document.getElementById('quantity').addEventListener('input', calcImportTotal);
document.getElementById('price').addEventListener('input', calcImportTotal);
calcImportTotal();
</script>
@endpush
@endsection
