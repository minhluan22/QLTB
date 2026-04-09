@extends('layouts.app')

@section('title', 'Thêm người dùng')
@section('page-title', 'Thêm người dùng mới')

@section('content')
<div class="mb-4">
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Quay lại
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header py-3 px-4">
                <i class="bi bi-person-plus text-primary me-2"></i>
                <span class="fw-semibold">Thông tin người dùng mới</span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf

                    {{-- Thông tin cơ bản --}}
                    <h6 class="fw-bold text-muted mb-3" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;">
                        Thông tin cơ bản
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">
                                Họ và tên <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Nguyễn Văn A">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="email@truong.edu.vn">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">
                                Số điện thoại
                            </label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone') }}" placeholder="0987654321">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">
                                Vai trò <span class="text-danger">*</span>
                            </label>
                            <select name="role" id="roleSelect" class="form-select @error('role') is-invalid @enderror"
                                    onchange="toggleRoomField()">
                                <option value="">-- Chọn vai trò --</option>
                                <option value="teacher"      {{ old('role') === 'teacher'      ? 'selected' : '' }}>Giáo viên</option>
                                <option value="room_manager" {{ old('role') === 'room_manager' ? 'selected' : '' }}>Giáo viên QL Phòng</option>
                                <option value="admin"        {{ old('role') === 'admin'        ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6" id="roomNameWrapper"
                             style="display: {{ old('role') === 'room_manager' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">
                                Phòng quản lý <span class="text-danger">*</span>
                            </label>
                            <select name="room_name" class="form-select @error('room_name') is-invalid @enderror">
                                <option value="">-- Chọn phòng --</option>
                                <option value="Phòng Lý"  {{ old('room_name') === 'Phòng Lý'  ? 'selected' : '' }}>Phòng Lý</option>
                                <option value="Phòng Hóa" {{ old('room_name') === 'Phòng Hóa' ? 'selected' : '' }}>Phòng Hóa</option>
                                <option value="Phòng Sinh" {{ old('room_name') === 'Phòng Sinh' ? 'selected' : '' }}>Phòng Sinh</option>
                            </select>
                            @error('room_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6" id="sgWrapper_c">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">
                                Tổ chuyên môn
                            </label>
                            @php
                                $presets_c  = ['Toán - Tin - GDT C', 'KHXH', 'KHTN - Tiếng Anh'];
                                $old_c      = old('subject_group', '');
                                $isPreset_c = in_array($old_c, ['Toán - Tin - GDTC','KHXH','KHTN - Tiếng Anh']) || $old_c === '';
                            @endphp
                            <select id="sg_select_c" class="form-select mb-2 @error('subject_group') is-invalid @enderror"
                                    onchange="toggleSG('_c')">
                                <option value="">-- Chọn tổ --</option>
                                <option value="Toán - Tin - GDTC" {{ $old_c === 'Toán - Tin - GDTC' ? 'selected':'' }}>Toán - Tin - GDTC</option>
                                <option value="KHXH"              {{ $old_c === 'KHXH'              ? 'selected':'' }}>KHXH (Văn, Sử, Địa, GDCD...)</option>
                                <option value="KHTN - Tiếng Anh"  {{ $old_c === 'KHTN - Tiếng Anh'  ? 'selected':'' }}>KHTN - Tiếng Anh (Lý, Hóa, Sinh, TA)</option>
                                <option value="__other__"          {{ (!$isPreset_c && $old_c !== '') ? 'selected':'' }}>Khác (tự nhập)...</option>
                            </select>
                            <input type="text" id="sg_custom_c"
                                   style="display:{{ (!$isPreset_c && $old_c !== '') ? 'block':'none' }};"
                                   class="form-control" placeholder="Nhập tên tổ tự do..."
                                   value="{{ (!$isPreset_c && $old_c !== '') ? $old_c : '' }}">
                            <input type="hidden" name="subject_group" id="sg_hidden_c" value="{{ $old_c }}">
                            @error('subject_group')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">
                                Môn dạy
                            </label>
                            <input type="text" name="teaching_subject" class="form-control @error('teaching_subject') is-invalid @enderror"
                                   value="{{ old('teaching_subject') }}" placeholder="VD: Toán học, Tin học...">
                            @error('teaching_subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">
                                Ghi chú
                            </label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2" placeholder="Thông tin thêm...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Mật khẩu --}}
                    <h6 class="fw-bold text-muted mb-3" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;">
                        Mật khẩu
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">
                                Mật khẩu <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" name="password" id="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Ít nhất 6 ký tự">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">
                                Xác nhận mật khẩu <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirm"
                                       class="form-control" placeholder="Nhập lại mật khẩu">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('password_confirm', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Hủy</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-check me-1"></i>Tạo tài khoản
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePass(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
function toggleRoomField() {
    const role    = document.getElementById('roleSelect').value;
    const wrapper = document.getElementById('roomNameWrapper');
    wrapper.style.display = (role === 'room_manager') ? 'block' : 'none';
}
function toggleSG(suffix) {
    const sel    = document.getElementById('sg_select' + suffix);
    const custom = document.getElementById('sg_custom' + suffix);
    const hidden = document.getElementById('sg_hidden' + suffix);
    if (!sel) return;
    if (sel.value === '__other__') {
        custom.style.display = 'block';
        custom.focus();
        hidden.value = custom.value;
        custom.oninput = () => hidden.value = custom.value;
    } else {
        custom.style.display = 'none';
        hidden.value = sel.value;
    }
}
window.addEventListener('DOMContentLoaded', () => {
    ['_c','_e','_p'].forEach(s => { const el = document.getElementById('sg_select' + s); if (el) toggleSG(s); });
});
</script>
@endpush
@endsection
