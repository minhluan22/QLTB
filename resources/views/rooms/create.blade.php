@extends('layouts.app')
@section('title', 'Thêm phòng thực hành')
@section('page-title', 'Thêm phòng thực hành mới')

@section('content')
<div class="mb-4">
    <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Quay lại
    </a>
</div>
<div class="row justify-content-center">
<div class="col-12 col-lg-7">
<div class="card">
    <div class="card-header py-3 px-4">
        <i class="bi bi-door-open text-primary me-2"></i>
        <span class="fw-semibold">Thông tin phòng thực hành</span>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('rooms.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-12 col-md-7">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Tên phòng <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="VD: Phòng Thực Hành Vật Lý A1">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-5">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Môn học <span class="text-danger">*</span></label>
                    <select name="subject" class="form-select @error('subject') is-invalid @enderror">
                        <option value="">-- Chọn môn --</option>
                        <option value="ly"   {{ old('subject')=='ly'   ? 'selected':'' }}>Vật Lý</option>
                        <option value="hoa"  {{ old('subject')=='hoa'  ? 'selected':'' }}>Hoá Học</option>
                        <option value="sinh" {{ old('subject')=='sinh' ? 'selected':'' }}>Sinh Học</option>
                    </select>
                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Giáo viên quản lý</label>
                    <select name="manager_id" class="form-select @error('manager_id') is-invalid @enderror">
                        <option value="">-- Chưa gán --</option>
                        @foreach($managers as $m)
                            <option value="{{ $m->id }}" {{ old('manager_id')==$m->id ? 'selected':'' }}>
                                {{ $m->name }}
                                @if($m->room_name) ({{ $m->room_name }}) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('manager_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Vị trí phòng</label>
                    <input type="text" name="location" class="form-control"
                           value="{{ old('location') }}" placeholder="VD: Tầng 2, Dãy A">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="d-flex gap-2 justify-content-end mt-4">
                <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Tạo phòng</button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
