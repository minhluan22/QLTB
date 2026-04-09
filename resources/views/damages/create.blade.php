@extends('layouts.app')

@section('title', 'Báo hỏng / Báo mất thiết bị')
@section('page-title', 'Báo hỏng / Báo mất')

@section('content')
<div class="mb-4">
    <a href="{{ route('damages.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Quay lại
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header py-3 px-4">
                <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                <span class="fw-semibold">Ghi nhận sự cố thiết bị</span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('damages.store') }}">
                    @csrf

                    {{-- Loại sự cố --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold req">Loại sự cố</label>
                        <div class="d-flex gap-3">
                            <div class="form-check form-check-inline p-0 flex-fill">
                                <input class="btn-check" type="radio" name="damage_type" id="type_hong"
                                       value="hỏng" {{ old('damage_type','hỏng')==='hỏng' ? 'checked':'' }}>
                                <label class="btn btn-outline-warning w-100" for="type_hong">
                                    <i class="bi bi-tools me-1"></i>Hỏng (sửa được)
                                </label>
                            </div>
                            <div class="form-check form-check-inline p-0 flex-fill">
                                <input class="btn-check" type="radio" name="damage_type" id="type_mat"
                                       value="mất" {{ old('damage_type')==='mất' ? 'checked':'' }}>
                                <label class="btn btn-outline-danger w-100" for="type_mat">
                                    <i class="bi bi-x-circle me-1"></i>Mất / Thất lạc
                                </label>
                            </div>
                        </div>
                        @error('damage_type')<div class="text-danger mt-1" style="font-size:.85rem;">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        {{-- Thiết bị --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold req">Thiết bị</label>
                            <select name="device_id" class="form-select @error('device_id') is-invalid @enderror"
                                    id="device_select">
                                <option value="">-- Chọn thiết bị --</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}"
                                            data-available="{{ $device->available_qty }}"
                                            data-unit="{{ $device->unit }}"
                                            data-remaining="{{ $device->remainingQty() }}"
                                            {{ old('device_id') == $device->id ? 'selected' : '' }}>
                                        [{{ $device->code }}] {{ $device->name }}
                                        (Có thể báo: {{ $device->available_qty }} {{ $device->unit }})
                                    </option>
                                @endforeach
                            </select>
                            @error('device_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div id="device_info" class="mt-1" style="display:none;">
                                <small class="text-muted">
                                    Tổng: <strong id="di_total">—</strong> |
                                    Hỏng: <strong id="di_damaged" class="text-warning">—</strong> |
                                    Mất: <strong id="di_lost" class="text-danger">—</strong> |
                                    Còn lại: <strong id="di_remaining" class="text-success">—</strong>
                                </small>
                            </div>
                        </div>

                        {{-- Số lượng & Ngày phát hiện --}}
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold req">Số lượng</label>
                            <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                                   value="{{ old('quantity', 1) }}" min="1">
                            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label fw-semibold req">Ngày phát hiện</label>
                            <input type="date" name="detected_date" class="form-control @error('detected_date') is-invalid @enderror"
                                   value="{{ old('detected_date', date('Y-m-d')) }}">
                            @error('detected_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Mức độ (chỉ hiện khi hỏng) --}}
                        <div class="col-12 col-md-5" id="severity_wrap">
                            <label class="form-label fw-semibold req">Mức độ hỏng</label>
                            <select name="severity" class="form-select @error('severity') is-invalid @enderror">
                                <option value="minor"    {{ old('severity')==='minor'    ? 'selected':'' }}>Hỏng nhẹ (sửa được)</option>
                                <option value="moderate" {{ old('severity')==='moderate' ? 'selected':'' }}>Hỏng vừa</option>
                                <option value="severe"   {{ old('severity')==='severe'   ? 'selected':'' }}>Hỏng nặng / không sửa được</option>
                            </select>
                            @error('severity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Nguyên nhân & Hướng xử lý --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Nguyên nhân</label>
                            <input type="text" name="cause" class="form-control" list="cause-list"
                                   value="{{ old('cause') }}" placeholder="VD: Gãy, Cong, Rơi vỡ...">
                            <datalist id="cause-list">
                                <option value="Gãy"><option value="Cong vênh">
                                <option value="Rơi vỡ"><option value="Bị ướt">
                                <option value="Hỏng điện tử"><option value="Thất lạc">
                            </datalist>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Hướng xử lý</label>
                            <input type="text" name="resolution" class="form-control" list="res-list"
                                   value="{{ old('resolution') }}" placeholder="VD: Thay mới, Sửa chữa...">
                            <datalist id="res-list">
                                <option value="Thay mới"><option value="Sửa chữa">
                                <option value="Thanh lý"><option value="Báo lên phòng thiết bị">
                            </datalist>
                        </div>

                        {{-- Mô tả chi tiết --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Mô tả chi tiết</label>
                            <textarea name="description" rows="3"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Mô tả cụ thể tình trạng hỏng/mất...">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Cảnh báo --}}
                    <div class="alert alert-warning d-flex gap-2 align-items-start py-2">
                        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                        <div style="font-size:.85rem;">
                            <strong>Lưu ý:</strong> Sau khi ghi nhận, số lượng thiết bị sẽ tự động cập nhật.
                            Báo <strong>Hỏng</strong> → tăng cột Hỏng.
                            Báo <strong>Mất</strong> → tăng cột Mất. Cả hai đều giảm số lượng "Còn lại".
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('damages.index') }}" class="btn btn-outline-secondary">Hủy</a>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-exclamation-triangle me-1"></i>Ghi nhận sự cố
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.form-label.req::after { content:" *"; color:#ef4444; }

/* Tùy chỉnh Select2 cho đồng bộ với Bootstrap 5 */
.select2-container .select2-selection--single {
    height: 38px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
    padding-left: 12px;
    font-size: .875rem;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #ef4444; /* Đậm hơn chút vì Form này form mạo hiểm/cảnh báo */
}
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#device_select').select2({
        placeholder: "-- Chọn thiết bị --",
        width: '100%'
    });
});

// Hiện/ẩn mức độ dựa theo loại
function toggleSeverity() {
    const type = document.querySelector('input[name="damage_type"]:checked')?.value;
    document.getElementById('severity_wrap').style.display = (type === 'hỏng') ? '' : 'none';
}
document.querySelectorAll('input[name="damage_type"]').forEach(r => r.addEventListener('change', toggleSeverity));
toggleSeverity();

// Cập nhật max cho input số lượng dựa trên thiết bị đã chọn
$('#device_select').on('change', function() {
    const opt = this.options[this.selectedIndex];
    if (opt && opt.value) {
        const maxQty = opt.getAttribute('data-available');
        const qtyInput = document.querySelector('input[name="quantity"]');
        if(qtyInput) {
            qtyInput.max = maxQty;
            if (parseInt(qtyInput.value) > parseInt(maxQty)) {
                qtyInput.value = maxQty;
            }
        }
    }
});
</script>
@endpush
@endsection
