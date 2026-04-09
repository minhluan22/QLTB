@extends('layouts.app')

@section('title', 'Nhận lại thiết bị')
@section('page-title', 'Nhận lại thiết bị')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('borrow-requests.show', $borrowRequest) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="fw-bold mb-0" style="font-size:1.2rem;">Xác nhận nhận lại thiết bị - Yêu cầu #{{ $borrowRequest->id }}</h2>
                <p class="text-muted mb-0" style="font-size:.8rem;">Nhận lại thiết bị và ghi chú báo hỏng/mất (nếu có)</p>
            </div>
        </div>

        <form method="POST" action="{{ route('returns.store') }}">
            @csrf
            <input type="hidden" name="borrow_request_id" value="{{ $borrowRequest->id }}">

            {{-- Sao kê thiết bị mượn --}}
            <div class="card mb-3">
                <div class="card-header py-3 px-4">
                    <i class="bi bi-laptop text-primary me-2"></i>Thiết bị đang mượn
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0" style="font-size:.875rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Thiết bị</th>
                                <th>Số lượng mượn</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($borrowRequest->borrowDetails as $detail)
                            <tr>
                                <td class="px-4 fw-semibold">{{ $detail->device->name }}</td>
                                <td><span class="badge bg-primary">{{ $detail->quantity }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Thông tin trả --}}
            <div class="card mb-3">
                <div class="card-header py-3 px-4">
                    <i class="bi bi-arrow-return-left text-success me-2"></i>Thông tin trả
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="return_date" class="form-label fw-semibold">
                                Ngày trả thực tế <span class="text-danger">*</span>
                            </label>
                            <input type="date" id="return_date" name="return_date"
                                class="form-control @error('return_date') is-invalid @enderror"
                                value="{{ old('return_date', date('Y-m-d')) }}">
                            @error('return_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label for="note" class="form-label fw-semibold">Ghi chú khi trả</label>
                            <textarea id="note" name="note" rows="2"
                                class="form-control"
                                placeholder="Tình trạng thiết bị khi trả...">{{ old('note') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Báo hỏng (tùy chọn) --}}
            <div class="card mb-4">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-exclamation-triangle text-danger me-2"></i>Báo hỏng thiết bị <span class="text-muted fw-normal">(nếu có)</span></span>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="addDamageRow()">
                        <i class="bi bi-plus"></i> Thêm báo hỏng
                    </button>
                </div>
                <div class="card-body p-4">
                    <div id="damage-container">
                        {{-- Các hàng báo hỏng sẽ được thêm vào đây --}}
                    </div>
                    <div id="no-damage-msg" class="text-center text-muted py-3" style="font-size:.875rem;">
                        <i class="bi bi-check-circle text-success me-1"></i>Không có thiết bị hỏng — nhấn "+ Thêm báo hỏng" nếu cần
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('borrow-requests.show', $borrowRequest) }}" class="btn btn-outline-secondary">Huỷ</a>
                <button type="submit" class="btn btn-success"
                    onclick="return confirm('Xác nhận trả thiết bị?')">
                    <i class="bi bi-check-circle me-1"></i>Xác nhận trả
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let damageCount = 0;

// Danh sách thiết bị đang mượn (để chọn)
@php
    $mappedDetails = $borrowRequest->borrowDetails->map(function($d) {
        return [
            'id'   => $d->id,
            'name' => $d->device->name,
            'qty'  => $d->quantity,
        ];
    });
@endphp
const borrowDetails = {!! json_encode($mappedDetails) !!};

function addDamageRow() {
    const container = document.getElementById('damage-container');
    document.getElementById('no-damage-msg').style.display = 'none';

    const options = borrowDetails.map(d =>
        `<option value="${d.id}">${d.name} (mượn: ${d.qty})</option>`
    ).join('');

    const div = document.createElement('div');
    div.className = 'border rounded p-3 mb-3 damage-row';
    div.innerHTML = `
        <div class="row g-2">
            <div class="col-12 col-md-5">
                <label class="form-label fw-semibold" style="font-size:.8rem;">Thiết bị <span class="text-danger">*</span></label>
                <select name="damages[${damageCount}][borrow_detail_id]" class="form-select form-select-sm" required>
                    <option value="">-- Chọn thiết bị --</option>
                    ${options}
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold" style="font-size:.8rem;">Tình trạng <span class="text-danger">*</span></label>
                <select name="damages[${damageCount}][damage_type]" class="form-select form-select-sm" required>
                    <option value="hỏng">Hỏng</option>
                    <option value="mất">Mất</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold" style="font-size:.8rem;">Số lượng <span class="text-danger">*</span></label>
                <input type="number" name="damages[${damageCount}][quantity]" class="form-control form-control-sm" value="1" min="1" required>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label fw-semibold" style="font-size:.8rem;">Mức độ hư hại</label>
                <select name="damages[${damageCount}][severity]" class="form-select form-select-sm">
                    <option value="minor">Nhẹ</option>
                    <option value="moderate">Vừa</option>
                    <option value="severe">Nặng</option>
                </select>
            </div>
            <div class="col-12 col-md-10">
                <label class="form-label fw-semibold" style="font-size:.8rem;">Mô tả lỗi <span class="text-danger">*</span></label>
                <input type="text" name="damages[${damageCount}][description]" class="form-control form-control-sm"
                       placeholder="Mô tả tình trạng hỏng..." required>
            </div>
            <div class="col-12 col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('.damage-row').remove(); checkNoDamage();">
                    <i class="bi bi-trash"></i> Xoá
                </button>
            </div>
        </div>
    `;
    container.appendChild(div);
    damageCount++;
}

function checkNoDamage() {
    const rows = document.querySelectorAll('.damage-row');
    document.getElementById('no-damage-msg').style.display = rows.length === 0 ? 'block' : 'none';
}
</script>
@endpush
