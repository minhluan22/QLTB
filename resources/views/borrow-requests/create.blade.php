@extends('layouts.app')

@section('title', 'Mượn thiết bị')
@section('page-title', 'Mượn thiết bị')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('borrow-requests.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="fw-bold mb-0" style="font-size:1.2rem;">Mượn thiết bị</h2>
                <p class="text-muted mb-0" style="font-size:.8rem;">Điền thông tin và chọn thiết bị cần mượn</p>
            </div>
        </div>

        <form method="POST" action="{{ route('borrow-requests.store') }}" id="borrow-form">
            @csrf

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
                                        placeholder="VD: Dạy học môn Tin học lớp 10A1, ngày 15/03...">{{ old('purpose') }}</textarea>
                                    @error('purpose') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="class_name" class="form-label fw-semibold">Lớp học</label>
                                    <input type="text" id="class_name" name="class_name"
                                        class="form-control" value="{{ old('class_name') }}"
                                        placeholder="VD: 10A1, 11B2...">
                                </div>

                                <div class="col-6 col-md-4">
                                    <label for="borrow_date" class="form-label fw-semibold">
                                        Ngày mượn <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" id="borrow_date" name="borrow_date"
                                        class="form-control @error('borrow_date') is-invalid @enderror"
                                        value="{{ old('borrow_date', date('Y-m-d')) }}"
                                        min="{{ date('Y-m-d') }}">
                                    @error('borrow_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-6 col-md-4">
                                    <label for="expected_return_date" class="form-label fw-semibold">
                                        Ngày trả dự kiến <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" id="expected_return_date" name="expected_return_date"
                                        class="form-control @error('expected_return_date') is-invalid @enderror"
                                        value="{{ old('expected_return_date') }}">
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
                                {{-- Hàng thiết bị đầu tiên --}}
                                <div class="device-row row g-2 align-items-end mb-3" data-index="0">
                                    <div class="col-8 col-md-9">
                                        <label class="form-label fw-semibold" style="font-size:.85rem;">Thiết bị <span class="text-danger">*</span></label>
                                        <select name="devices[0][device_id]" class="form-select device-select" onchange="updateMaxQty(this)">
                                            <option value="">-- Chọn thiết bị --</option>
                                            @foreach($devices as $device)
                                                <option value="{{ $device->id }}" data-max="{{ $device->available_qty }}">
                                                    {{ $device->name }} (Còn: {{ $device->available_qty }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <label class="form-label fw-semibold" style="font-size:.85rem;">Số lượng <span class="text-danger">*</span></label>
                                        <input type="number" name="devices[0][quantity]" class="form-control qty-input"
                                               value="1" min="1" max="99">
                                    </div>
                                    <div class="col-12 col-md-1 d-flex justify-content-end">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-row" onclick="removeRow(this)" style="display:none;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
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
                <button type="submit" class="btn btn-primary" onclick="return confirm('Xác nhận mượn các thiết bị này?')">
                    <i class="bi bi-send me-1"></i>Xác nhận mượn
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
    });

// Template HTML cho 1 hàng thiết bị
function addDeviceRow() {
    const container = document.getElementById('devices-container');
    const rows = container.querySelectorAll('.device-row');
    const index = rows.length;

    const deviceOptions = `
        <option value=""></option>
        @foreach($devices as $device)
            <option value="{{ $device->id }}" data-max="{{ $device->available_qty }}">
                {{ $device->name }} (Còn: {{ $device->available_qty }})
            </option>
        @endforeach
    `;

    const row = document.createElement('div');
    row.className = 'device-row row g-2 align-items-end mb-3';
    row.dataset.index = index;
    row.innerHTML = `
        <div class="col-8 col-md-9">
            <select name="devices[${index}][device_id]" class="form-select device-select" onchange="updateMaxQty(this)">
                ${deviceOptions}
            </select>
        </div>
        <div class="col-4 col-md-2">
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

    // Hiện nút xoá ở hàng đầu tiên khi có nhiều hàng
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
    });
}

function reindexRows() {
    document.querySelectorAll('.device-row').forEach((row, i) => {
        row.dataset.index = i;
        row.querySelector('[name*="device_id"]').name = `devices[${i}][device_id]`;
        row.querySelector('[name*="quantity"]').name = `devices[${i}][quantity]`;
    });
}

function updateMaxQty(select) {
    const max = select.options[select.selectedIndex]?.dataset.max || 99;
    const qtyInput = select.closest('.device-row').querySelector('.qty-input');
    qtyInput.max = max;
    if (parseInt(qtyInput.value) > parseInt(max)) qtyInput.value = max;
}
</script>
@endpush
