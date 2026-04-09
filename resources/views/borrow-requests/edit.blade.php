@extends('layouts.app')

@section('title', 'Sửa phiếu mượn #' . $borrowRequest->id)
@section('page-title', 'Sửa phiếu mượn')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('borrow-requests.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="fw-bold mb-0" style="font-size:1.2rem;">Sửa phiếu mượn #{{ $borrowRequest->id }}</h2>
                <p class="text-muted mb-0" style="font-size:.8rem;">Cập nhật thông tin phiếu mượn</p>
            </div>
        </div>

        <form method="POST" action="{{ route('borrow-requests.update', $borrowRequest) }}" id="borrow-form">
            @csrf
            @method('PUT')

            <div class="row g-3">
                {{-- Thông tin chung --}}
                <div class="col-12">
                    <div class="card">
                        <div class="card-header py-3 px-4">
                            <i class="bi bi-info-circle text-primary me-2"></i>Thông tin chung
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="purpose" class="form-label fw-semibold">
                                        Mục đích mượn <span class="text-danger">*</span>
                                    </label>
                                    <textarea id="purpose" name="purpose" rows="2"
                                        class="form-control @error('purpose') is-invalid @enderror"
                                        placeholder="VD: Dạy học môn Tin học lớp 10A1, ngày 15/03...">{{ old('purpose', $borrowRequest->purpose) }}</textarea>
                                    @error('purpose') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-6 col-md-6">
                                    <label for="borrow_date" class="form-label fw-semibold">
                                        Ngày mượn <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" id="borrow_date" name="borrow_date"
                                        class="form-control @error('borrow_date') is-invalid @enderror"
                                        value="{{ old('borrow_date', $borrowRequest->borrow_date->format('Y-m-d')) }}">
                                    @error('borrow_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-6 col-md-6">
                                    <label for="expected_return_date" class="form-label fw-semibold">
                                        Ngày trả dự kiến <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" id="expected_return_date" name="expected_return_date"
                                        class="form-control @error('expected_return_date') is-invalid @enderror"
                                        value="{{ old('expected_return_date', $borrowRequest->expected_return_date->format('Y-m-d')) }}">
                                    @error('expected_return_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Chọn thiết bị --}}
                <div class="col-12">
                    <div class="card">
                        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-laptop text-success me-2"></i>Thiết bị cần mượn</span>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="addDeviceRow()">
                                <i class="bi bi-plus"></i> Thêm thiết bị
                            </button>
                        </div>
                        <div class="card-body p-4">
                            <div id="devices-container">
                                @php
                                    $oldDevices = old('devices', $borrowRequest->borrowDetails->toArray());
                                @endphp
                                @foreach($oldDevices as $index => $detail)
                                    @php
                                        // Tính toán số lượng tồn tối đa: available_qty hiện tại + số lượng đang vay trong phiếu này
                                        // Tuy nhiên, đối với old() (trường hợp bị lỗi submit), chúng ta có thể phải điều chỉnh
                                        $currentlyHeld = 0;
                                        $existingDetail = collect($borrowRequest->borrowDetails)->firstWhere('device_id', $detail['device_id']);
                                        if ($existingDetail) {
                                            $currentlyHeld = $existingDetail->quantity;
                                        }
                                    @endphp
                                    <div class="device-row row g-2 align-items-end mb-3" data-index="{{ $index }}">
                                        <div class="col-8 col-md-9">
                                            <label class="form-label fw-semibold" style="font-size:.85rem;">Thiết bị <span class="text-danger">*</span></label>
                                            <select name="devices[{{ $index }}][device_id]" class="form-select device-select" onchange="updateMaxQty(this)">
                                                <option value="">-- Chọn thiết bị --</option>
                                                @foreach($devices as $device)
                                                    @php
                                                        // Nếu thiết bị này đang nằm trong phiếu thì cộng trả số lượng vào maxQty
                                                        $myHeld = collect($borrowRequest->borrowDetails)->firstWhere('device_id', $device->id)?->quantity ?? 0;
                                                        $maxQty = $device->available_qty + $myHeld;
                                                    @endphp
                                                    @if($maxQty > 0 || $device->id == $detail['device_id'])
                                                    <option value="{{ $device->id }}" data-max="{{ $maxQty }}" {{ $device->id == $detail['device_id'] ? 'selected' : '' }}>
                                                        {{ $device->name }} (Được phép chọn tối đa: {{ $maxQty }})
                                                    </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-4 col-md-2">
                                            <label class="form-label fw-semibold" style="font-size:.85rem;">Số lượng <span class="text-danger">*</span></label>
                                            <input type="number" name="devices[{{ $index }}][quantity]" class="form-control qty-input"
                                                   value="{{ $detail['quantity'] }}" min="1">
                                        </div>
                                        <div class="col-12 col-md-1 d-flex justify-content-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-row" onclick="removeRow(this)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @error('devices')
                                <div class="text-danger mt-1" style="font-size:.85rem;">
                                    <i class="bi bi-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-end mt-4">
                <a href="{{ route('borrow-requests.index') }}" class="btn btn-outline-secondary">Huỷ</a>
                <button type="submit" class="btn btn-primary" onclick="return confirm('Xác nhận lưu thay đổi?')">
                    <i class="bi bi-send me-1"></i>Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
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
        border-color: #1a73e8;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.device-select').select2({
            placeholder: "-- Chọn thiết bị --",
            width: '100%'
        });
        
        updateRemoveButtons();
        
        // Cập nhật giá trị max cho input ban đầu dựa theo select
        $('.device-select').each(function() {
            updateMaxQty(this, false); // false để không thay đổi value đang chứa
        });
    });

@php
    $preparedDevices = $devices->map(function($d) use ($borrowRequest) {
        $myHeld = collect($borrowRequest->borrowDetails)->firstWhere('device_id', $d->id)?->quantity ?? 0;
        return [
            'id' => $d->id,
            'name' => $d->name,
            'max' => $d->available_qty + $myHeld
        ];
    })->filter(fn($d) => $d['max'] > 0)->values();
@endphp

// Chuyển dữ liệu PHP sang Javascript
const devicesData = @json($preparedDevices);

function addDeviceRow() {
    const container = document.getElementById('devices-container');
    const rows = container.querySelectorAll('.device-row');
    const index = rows.length ? parseInt(rows[rows.length-1].dataset.index) + 1 : 0;

    let deviceOptions = '<option value=""></option>';
    devicesData.forEach(d => {
        deviceOptions += `<option value="${d.id}" data-max="${d.max}">${d.name} (Được phép chọn tối đa: ${d.max})</option>`;
    });

    const row = document.createElement('div');
    row.className = 'device-row row g-2 align-items-end mb-3';
    row.dataset.index = index;
    row.innerHTML = `
        <div class="col-8 col-md-9">
            <label class="form-label fw-semibold" style="font-size:.85rem; display:none;">Thiết bị <span class="text-danger">*</span></label>
            <select name="devices[${index}][device_id]" class="form-select device-select" onchange="updateMaxQty(this)">
                ${deviceOptions}
            </select>
        </div>
        <div class="col-4 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.85rem; display:none;">Số lượng <span class="text-danger">*</span></label>
            <input type="number" name="devices[${index}][quantity]" class="form-control qty-input"
                   value="1" min="1" max="99">
        </div>
        <div class="col-12 col-md-1 d-flex justify-content-end">
            <button type="button" class="btn btn-outline-danger btn-sm remove-row" onclick="removeRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(row);

    // Bật Select2 cho hàng mới
    $(row).find('.device-select').select2({
        placeholder: "-- Chọn thiết bị --",
        width: '100%'
    });

    updateRemoveButtons();
}

function removeRow(btn) {
    btn.closest('.device-row').remove();
    updateRemoveButtons();
    reindexRows();
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.device-row');
    rows.forEach((row, i) => {
        const btn = row.querySelector('.remove-row');
        btn.style.display = rows.length > 1 ? 'inline-flex' : 'none';
        
        // Hide labels for newly appended rows to match styling
        if (i > 0) {
            row.querySelectorAll('label').forEach(l => l.style.display = 'none');
        } else {
            row.querySelectorAll('label').forEach(l => l.style.display = 'block');
        }
    });
}

function reindexRows() {
    document.querySelectorAll('.device-row').forEach((row, i) => {
        row.dataset.index = i;
        row.querySelector('[name*="device_id"]').name = `devices[${i}][device_id]`;
        row.querySelector('[name*="quantity"]').name = `devices[${i}][quantity]`;
    });
}

function updateMaxQty(select, autoUpdateValue = true) {
    const max = select.options[select.selectedIndex]?.dataset.max || 99;
    const qtyInput = select.closest('.device-row').querySelector('.qty-input');
    qtyInput.max = max;
    if (autoUpdateValue && parseInt(qtyInput.value) > parseInt(max)) {
        qtyInput.value = max;
    }
}
</script>
@endpush
