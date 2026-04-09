@extends('layouts.app')

@section('title', 'Chỉnh sửa người dùng')
@section('page-title', 'Chỉnh sửa người dùng')

@section('content')
<div class="mb-4">
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Quay lại
    </a>
</div>

<div class="row g-4 justify-content-center">
    {{-- Form chỉnh sửa thông tin --}}
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header py-3 px-4">
                <i class="bi bi-pencil-square text-primary me-2"></i>
                <span class="fw-semibold">Thông tin {{ $user->name }}</span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf @method('PUT')

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">
                                Họ và tên <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone', $user->phone) }}">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">
                                Vai trò <span class="text-danger">*</span>
                            </label>
                            <select name="role" id="editRoleSelect" class="form-select @error('role') is-invalid @enderror"
                                    onchange="toggleRoomField()">
                                <option value="teacher"      {{ old('role', $user->role) === 'teacher'      ? 'selected' : '' }}>Giáo viên</option>
                                <option value="room_manager" {{ old('role', $user->role) === 'room_manager' ? 'selected' : '' }}>Giáo viên QL Phòng</option>
                                <option value="admin"        {{ old('role', $user->role) === 'admin'        ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6" id="editRoomNameWrapper"
                             style="display: {{ old('role', $user->role) === 'room_manager' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">
                                Phòng quản lý <span class="text-danger">*</span>
                            </label>
                            <select name="room_name" class="form-select @error('room_name') is-invalid @enderror">
                                <option value="">-- Chọn phòng --</option>
                                <option value="Phòng Lý"  {{ old('room_name', $user->room_name) === 'Phòng Lý'  ? 'selected' : '' }}>Phòng Lý</option>
                                <option value="Phòng Hóa" {{ old('room_name', $user->room_name) === 'Phòng Hóa' ? 'selected' : '' }}>Phòng Hóa</option>
                                <option value="Phòng Sinh" {{ old('room_name', $user->room_name) === 'Phòng Sinh' ? 'selected' : '' }}>Phòng Sinh</option>
                            </select>
                            @error('room_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">Tổ chuyên môn</label>
                            @php
                                $old_e      = old('subject_group', $user->subject_group ?? '');
                                $isPreset_e = in_array($old_e, ['Toán - Tin - GDTC','KHXH','KHTN - Tiếng Anh']) || $old_e === '';
                            @endphp
                            <select id="sg_select_e" class="form-select mb-2"
                                    onchange="toggleSG('_e')">
                                <option value="">-- Chọn tổ --</option>
                                <option value="Toán - Tin - GDTC" {{ $old_e === 'Toán - Tin - GDTC' ? 'selected':'' }}>Toán - Tin - GDTC</option>
                                <option value="KHXH"              {{ $old_e === 'KHXH'              ? 'selected':'' }}>KHXH (Văn, Sử, Địa, GDCD...)</option>
                                <option value="KHTN - Tiếng Anh"  {{ $old_e === 'KHTN - Tiếng Anh'  ? 'selected':'' }}>KHTN - Tiếng Anh (Lý, Hóa, Sinh, TA)</option>
                                <option value="__other__"          {{ (!$isPreset_e && $old_e !== '') ? 'selected':'' }}>Khác (tự nhập)...</option>
                            </select>
                            <input type="text" id="sg_custom_e"
                                   style="display:{{ (!$isPreset_e && $old_e !== '') ? 'block':'none' }};"
                                   class="form-control" placeholder="Nhập tên tổ tự do..."
                                   value="{{ (!$isPreset_e && $old_e !== '') ? $old_e : '' }}">
                            <input type="hidden" name="subject_group" id="sg_hidden_e" value="{{ $old_e }}">
                        </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">Môn dạy</label>
                            <input type="text" name="teaching_subject" class="form-control"
                                   value="{{ old('teaching_subject', $user->teaching_subject) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">Ghi chú</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $user->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Hủy</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Đặt lại mật khẩu --}}
        <div class="card mt-4">
            <div class="card-header py-3 px-4">
                <i class="bi bi-key text-warning me-2"></i>
                <span class="fw-semibold">Đặt lại mật khẩu</span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('users.reset-password', $user) }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.875rem;">
                                Mật khẩu mới <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" name="password" id="newpass"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Ít nhất 6 ký tự">
                                <button type="button" class="btn btn-outline-secondary"
                                        onclick="togglePass('newpass', this)">
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
                                <input type="password" name="password_confirmation" id="newpass2"
                                       class="form-control" placeholder="Nhập lại mật khẩu">
                                <button type="button" class="btn btn-outline-secondary"
                                        onclick="togglePass('newpass2', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-warning text-dark">
                            <i class="bi bi-key me-1"></i>Đặt lại mật khẩu
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
    const role    = document.getElementById('editRoleSelect').value;
    const wrapper = document.getElementById('editRoomNameWrapper');
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
