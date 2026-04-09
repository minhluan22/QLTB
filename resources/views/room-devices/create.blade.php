@extends('layouts.app')
@section('title', 'Thêm thiết bị phòng')
@section('page-title', 'Thêm thiết bị vào ' . $room->name)

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
        <i class="bi bi-box-seam-fill text-primary me-2"></i>
        <span class="fw-semibold">Thêm thiết bị mới</span>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('room-devices.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Tên thiết bị <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="VD: Kính hiển vi, Cân điện tử...">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Đơn vị tính <span class="text-danger">*</span></label>
                    <select name="unit" class="form-select @error('unit') is-invalid @enderror">
                        <option value="cái" {{ old('unit')=='cái' ? 'selected':'' }}>cái</option>
                        <option value="bộ"  {{ old('unit')=='bộ'  ? 'selected':'' }}>bộ</option>
                        <option value="hộp" {{ old('unit')=='hộp' ? 'selected':'' }}>hộp</option>
                        <option value="ml"  {{ old('unit')=='ml'  ? 'selected':'' }}>ml</option>
                        <option value="lít" {{ old('unit')=='lít' ? 'selected':'' }}>lít</option>
                        <option value="gói" {{ old('unit')=='gói' ? 'selected':'' }}>gói</option>
                        <option value="chai"{{ old('unit')=='chai'? 'selected':'' }}>chai</option>
                    </select>
                    @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Số lượng <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                           value="{{ old('quantity', 0) }}" min="0">
                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="2" placeholder="Thông tin thêm về thiết bị...">{{ old('note') }}</textarea>
                </div>
            </div>
            <div class="d-flex gap-2 justify-content-end mt-4">
                <a href="{{ route('room-devices.index') }}" class="btn btn-outline-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Thêm thiết bị</button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
