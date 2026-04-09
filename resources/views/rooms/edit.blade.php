@extends('layouts.app')
@section('title', 'Sửa phòng: ' . $room->name)
@section('page-title', 'Sửa phòng thực hành')

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
        <i class="bi bi-pencil-square text-primary me-2"></i>
        <span class="fw-semibold">Chỉnh sửa: {{ $room->name }}</span>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('rooms.update', $room) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-12 col-md-7">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Tên phòng <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $room->name) }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-5">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Môn học <span class="text-danger">*</span></label>
                    <select name="subject" class="form-select @error('subject') is-invalid @enderror">
                        <option value="ly"   {{ old('subject',$room->subject)=='ly'   ? 'selected':'' }}>Vật Lý</option>
                        <option value="hoa"  {{ old('subject',$room->subject)=='hoa'  ? 'selected':'' }}>Hoá Học</option>
                        <option value="sinh" {{ old('subject',$room->subject)=='sinh' ? 'selected':'' }}>Sinh Học</option>
                    </select>
                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Giáo viên quản lý</label>
                    <select name="manager_id" class="form-select">
                        <option value="">-- Chưa gán --</option>
                        @foreach($managers as $m)
                            <option value="{{ $m->id }}" {{ old('manager_id',$room->manager_id)==$m->id ? 'selected':'' }}>
                                {{ $m->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Vị trí phòng</label>
                    <input type="text" name="location" class="form-control"
                           value="{{ old('location', $room->location) }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $room->description) }}</textarea>
                </div>
            </div>
            <div class="d-flex gap-2 justify-content-end mt-4">
                <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
