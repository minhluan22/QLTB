@extends('layouts.app')

@section('title', 'Thông tin cá nhân')
@section('page-title', 'Thông tin cá nhân')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                <i class="bi bi-person-badge text-primary" style="font-size: 1.25rem;"></i>
                <h5 class="mb-0 fw-bold" style="font-size: 1.1rem;">Hồ sơ cá nhân</h5>
            </div>
            <div class="card-body p-4 bg-light">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        {{-- Avatar / Basic Info Section --}}
                        <div class="col-12 text-center mb-2">
                            <label for="avatarInput" style="cursor: pointer;" class="position-relative d-inline-block group" title="Bấm để thay đổi ảnh đại diện">
                                @if(auth()->user()->avatar)
                                    <img id="avatarPreview" src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; box-shadow: 0 4px 12px rgba(26, 115, 232, 0.3); outline: 3px solid #fff;">
                                @else
                                    <div id="avatarInitials" class="d-inline-flex align-items-center justify-content-center mb-0" style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #1a73e8, #0d47a1); color: white; font-size: 2rem; font-weight: bold; box-shadow: 0 4px 12px rgba(26, 115, 232, 0.3);">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                    <img id="avatarPreview" src="" alt="Avatar" style="display: none; width: 80px; height: 80px; border-radius: 50%; object-fit: cover; box-shadow: 0 4px 12px rgba(26, 115, 232, 0.3); outline: 3px solid #fff;">
                                @endif
                                <div class="position-absolute bottom-0 end-0 bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 50%; color: #1a73e8;">
                                    <i class="bi bi-camera-fill" style="font-size: .8rem;"></i>
                                </div>
                            </label>
                            <input type="file" name="avatar" id="avatarInput" class="d-none" accept="image/png, image/jpeg, image/jpg, image/webp" onchange="previewAvatar(this)">
                            @error('avatar')
                                <div class="text-danger mt-1 text-center" style="font-size: .8rem;">{{ $message }}</div>
                            @enderror
                            <h5 class="fw-bold mb-1 mt-3">{{ auth()->user()->name }}</h5>
                            <span class="badge {{ auth()->user()->isAdmin() ? 'bg-primary' : 'bg-success' }}">
                                {{ auth()->user()->isAdmin() ? 'Quản trị viên' : 'Giáo viên' }}
                            </span>
                        </div>
                        
                        <hr class="text-muted opacity-25 my-1">

                        {{-- Form Fields --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-secondary" style="font-size: .85rem;">HỌ VÀ TÊN <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control border-0 shadow-sm" value="{{ old('name', $user->name) }}" required>
                        </div>
                        
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-secondary" style="font-size: .85rem;">EMAIL <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control border-0 shadow-sm" value="{{ old('email', $user->email) }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-secondary" style="font-size: .85rem;">SỐ ĐIỆN THOẠI</label>
                            <input type="text" name="phone" class="form-control border-0 shadow-sm" value="{{ old('phone', $user->phone) }}" placeholder="Nhập số điện thoại...">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-secondary" style="font-size: .85rem;">TỔ CHUYÊN MÔN</label>
                            @php
                                $old_p      = old('subject_group', $user->subject_group ?? '');
                                $isPreset_p = in_array($old_p, ['Toán - Tin - GDTC','KHXH','KHTN - Tiếng Anh']) || $old_p === '';
                            @endphp
                            <select id="sg_select_p" class="form-control border-0 shadow-sm mb-2"
                                    style="border-radius:8px;" onchange="toggleSG('_p')">
                                <option value="">-- Chọn tổ --</option>
                                <option value="Toán - Tin - GDTC" {{ $old_p === 'Toán - Tin - GDTC' ? 'selected':'' }}>Toán - Tin - GDTC</option>
                                <option value="KHXH"              {{ $old_p === 'KHXH'              ? 'selected':'' }}>KHXH (Văn, Sử, Địa, GDCD...)</option>
                                <option value="KHTN - Tiếng Anh"  {{ $old_p === 'KHTN - Tiếng Anh'  ? 'selected':'' }}>KHTN - Tiếng Anh (Lý, Hóa, Sinh, TA)</option>
                                <option value="__other__"          {{ (!$isPreset_p && $old_p !== '') ? 'selected':'' }}>Khác (tự nhập)...</option>
                            </select>
                            <input type="text" id="sg_custom_p"
                                   style="display:{{ (!$isPreset_p && $old_p !== '') ? 'block':'none' }};"
                                   class="form-control border-0 shadow-sm" placeholder="Nhập tên tổ..."
                                   value="{{ (!$isPreset_p && $old_p !== '') ? $old_p : '' }}">
                            <input type="hidden" name="subject_group" id="sg_hidden_p" value="{{ $old_p }}">
                        </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary" style="font-size: .85rem;">MÔN DẠY</label>
                            <input type="text" name="teaching_subject" class="form-control border-0 shadow-sm" value="{{ old('teaching_subject', $user->teaching_subject) }}" placeholder="VD: Toán học, Tin học...">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary" style="font-size: .85rem;">GHI CHÚ (NẾU CÓ)</label>
                            <textarea name="notes" class="form-control border-0 shadow-sm" rows="3" placeholder="Thông tin thêm, lưu ý...">{{ old('notes', $user->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" style="border-radius: 8px;">
                            <i class="bi bi-save me-2"></i>Cập nhật thông tin
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Change Password Section --}}
        <div class="card shadow-sm border-0 mt-4" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock text-warning" style="font-size: 1.25rem;"></i>
                <h5 class="mb-0 fw-bold" style="font-size: 1.1rem;">Đổi mật khẩu</h5>
            </div>
            <div class="card-body p-4 bg-light">
                <form action="{{ route('profile.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold text-secondary" style="font-size: .85rem;">MẬT KHẨU HIỆN TẠI <span class="text-danger">*</span></label>
                            <input type="password" name="current_password" class="form-control border-0 shadow-sm" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold text-secondary" style="font-size: .85rem;">MẬT KHẨU MỚI <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control border-0 shadow-sm" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold text-secondary" style="font-size: .85rem;">XÁC NHẬN MẬT KHẨU <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control border-0 shadow-sm" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-warning px-4 fw-bold shadow-sm text-dark" style="border-radius: 8px;">
                            <i class="bi bi-key me-2"></i>Đổi mật khẩu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview  = document.getElementById('avatarPreview');
                var initials = document.getElementById('avatarInitials');
                preview.src = e.target.result;
                preview.style.display = 'block';
                if(initials) initials.style.display = 'none';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    /**
     * Toggle tổ chuyên môn: select dropdown + custom text input
     * suffix: '_c' | '_e' | '_p'
     */
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

    // Init on page load
    window.addEventListener('DOMContentLoaded', () => {
        ['_c','_e','_p'].forEach(s => {
            const el = document.getElementById('sg_select' + s);
            if (el) toggleSG(s);
        });
    });
</script>
@endpush
@endsection
