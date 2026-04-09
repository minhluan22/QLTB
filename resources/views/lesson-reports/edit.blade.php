@extends('layouts.app')
@section('title', 'Sửa báo cáo tiết #' . $lessonReport->id)
@section('page-title', 'Sửa báo cáo tiết dạy')

@section('content')
<div class="mb-4">
    <a href="{{ auth()->user()->isAdmin() ? route('lesson-reports.admin-index') : route('lesson-reports.my') }}"
       class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Quay lại
    </a>
</div>

<div class="row justify-content-center">
<div class="col-12 col-lg-7">

@if($errors->any())
<div class="alert alert-danger mb-3">
    <ul class="mb-0">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

<div class="card">
    <div class="card-header py-3 px-4 d-flex align-items-center gap-2">
        <i class="bi bi-pencil-square text-warning fs-5"></i>
        <span class="fw-semibold">Sửa báo cáo <span class="text-muted">#{{ $lessonReport->id }}</span></span>
        <span class="ms-auto badge bg-secondary" style="font-size:.75rem;">
            GV: {{ $lessonReport->teacher->name ?? 'N/A' }}
        </span>
    </div>

    <div class="card-body p-4">
        <form method="POST" action="{{ route('lesson-reports.update', $lessonReport) }}">
            @csrf @method('PUT')

            <div class="row g-3">

                {{-- Phòng --}}
                <div class="col-12">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">
                        Phòng thực hành <span class="text-danger">*</span>
                    </label>
                    <select name="room_id" class="form-select @error('room_id') is-invalid @enderror">
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}"
                                {{ old('room_id', $lessonReport->room_id) == $room->id ? 'selected' : '' }}>
                                {{ $room->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Ngày dạy --}}
                <div class="col-6">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">
                        Ngày dạy <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="lesson_date"
                           class="form-control @error('lesson_date') is-invalid @enderror"
                           value="{{ old('lesson_date', $lessonReport->lesson_date->format('Y-m-d')) }}">
                    @error('lesson_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Buổi --}}
                <div class="col-6">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">
                        Buổi <span class="text-danger">*</span>
                    </label>
                    <select name="session" class="form-select @error('session') is-invalid @enderror">
                        <option value="sang"  {{ old('session', $lessonReport->session) === 'sang'  ? 'selected' : '' }}>☀️ Sáng</option>
                        <option value="chieu" {{ old('session', $lessonReport->session) === 'chieu' ? 'selected' : '' }}>🌙 Chiều</option>
                    </select>
                    @error('session')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Số tiết --}}
                <div class="col-6">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">
                        Số tiết <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="period_count" min="1" max="10"
                           class="form-control @error('period_count') is-invalid @enderror"
                           value="{{ old('period_count', $lessonReport->period_count) }}">
                    @error('period_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Lớp --}}
                <div class="col-6">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">
                        Lớp <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="class_name"
                           class="form-control @error('class_name') is-invalid @enderror"
                           value="{{ old('class_name', $lessonReport->class_name) }}"
                           placeholder="VD: 8A1, 9B2...">
                    @error('class_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Môn học --}}
                <div class="col-6">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">
                        Môn học <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="subject"
                           class="form-control @error('subject') is-invalid @enderror"
                           value="{{ old('subject', $lessonReport->subject ?? $lessonReport->teacher->teaching_subject) }}"
                           placeholder="VD: Vật lý">
                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Ghi chú --}}
                <div class="col-12">
                    <label class="form-label fw-semibold" style="font-size:.875rem;">Ghi chú giáo viên</label>
                    <textarea name="teacher_note" class="form-control" rows="3"
                              placeholder="Ghi chú thêm nếu có...">{{ old('teacher_note', $lessonReport->teacher_note) }}</textarea>
                </div>

            </div>

            <div class="d-flex gap-2 justify-content-end mt-4">
                <a href="{{ auth()->user()->isAdmin() ? route('lesson-reports.admin-index') : route('lesson-reports.my') }}"
                   class="btn btn-outline-secondary">Hủy</a>
                <button type="submit" class="btn btn-warning fw-semibold">
                    <i class="bi bi-check-lg me-1"></i>Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

</div>
</div>
@endsection
