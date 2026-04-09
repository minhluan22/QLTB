@extends('layouts.app')
@section('title', 'Tạo báo cáo tiết dạy')
@section('page-title', 'Tạo báo cáo tiết thực hành')

@push('styles')
<style>
.device-row { background: #f8fafc; border-radius: 8px; padding: 12px; margin-bottom: 8px; transition: background .2s; }
.device-row:hover { background: #f0f4ff; }
.device-badge { font-size: .72rem; }
.section-header { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: 10px; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
<div class="col-12 col-lg-9">

<div class="card mb-4 border-0" style="background:linear-gradient(135deg,#1a73e8,#0d47a1);color:#fff;border-radius:16px;">
    <div class="card-body px-4 py-3 d-flex align-items-center gap-3">
        <i class="bi bi-clipboard2-plus fs-2 opacity-75"></i>
        <div>
            <h5 class="fw-bold mb-0">Báo cáo tiết thực hành</h5>
            <div style="font-size:.83rem;opacity:.85;">Ghi nhận thiết bị sử dụng và sự cố trong tiết dạy</div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('lesson-reports.store') }}" id="lessonForm">
@csrf

{{-- BƯỚC 1: THÔNG TIN TIẾT DẠY --}}
<div class="card mb-4">
    <div class="card-header py-3 px-4">
        <i class="bi bi-info-circle me-2 text-primary"></i>
        <span class="fw-semibold">Thông tin tiết dạy</span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold" style="font-size:.875rem;">Phòng thực hành <span class="text-danger">*</span></label>
                <select name="room_id" id="roomSelect" class="form-select @error('room_id') is-invalid @enderror"
                        onchange="loadDevices(this.value)">
                    <option value="">-- Chọn phòng --</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}"
                                data-devices="{{ json_encode($room->devices->map(fn($d) => ['id'=>$d->id,'name'=>$d->name,'unit'=>$d->unit,'qty'=>$d->availableQty()])) }}"
                                {{ old('room_id') == $room->id ? 'selected' : '' }}>
                            {{ $room->name }}
                        </option>
                    @endforeach
                </select>
                @error('room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if($rooms->isEmpty())
                    <div class="alert alert-warning mt-2 py-2" style="font-size:.82rem;">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Không tìm thấy phòng phù hợp.
                    </div>
                @endif
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label fw-semibold" style="font-size:.875rem;">Môn học <span class="text-danger">*</span></label>
                <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                       value="{{ old('subject', auth()->user()->teaching_subject) }}" placeholder="VD: Vật lý">
                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold" style="font-size:.875rem;">Ngày dạy <span class="text-danger">*</span></label>
                <input type="date" name="lesson_date" class="form-control @error('lesson_date') is-invalid @enderror"
                       value="{{ old('lesson_date', date('Y-m-d')) }}">
                @error('lesson_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold" style="font-size:.875rem;">Buổi <span class="text-danger">*</span></label>
                <select name="session" class="form-select @error('session') is-invalid @enderror">
                    <option value="sang"  {{ old('session','sang') === 'sang'  ? 'selected':'' }}>
                        ☀️ Sáng
                    </option>
                    <option value="chieu" {{ old('session') === 'chieu' ? 'selected':'' }}>
                        🌙 Chiều
                    </option>
                </select>
                @error('session')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-3 col-md-1">
                <label class="form-label fw-semibold" style="font-size:.875rem;">Số tiết <span class="text-danger">*</span></label>
                <input type="number" name="period_count" class="form-control @error('period_count') is-invalid @enderror"
                       value="{{ old('period_count', 2) }}" min="1" max="10">
                @error('period_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-3 col-md-1">
                <label class="form-label fw-semibold" style="font-size:.875rem;">Lớp <span class="text-danger">*</span></label>
                <input type="text" name="class_name" class="form-control @error('class_name') is-invalid @enderror"
                       value="{{ old('class_name') }}" placeholder="VD: 10A1">
                @error('class_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold" style="font-size:.875rem;">Ghi chú của giáo viên</label>
                <textarea name="teacher_note" class="form-control" rows="2"
                          placeholder="Nhận xét chung về tiết dạy, bài thực hành...">{{ old('teacher_note') }}</textarea>
            </div>
        </div>
    </div>
</div>

{{-- BƯỚC 2: THIẾT BỊ SỬ DỤNG --}}
<div class="card mb-4" id="devicesSection" style="{{ old('room_id') ? '' : 'display:none;' }}">
    <div class="card-header py-3 px-4">
        <i class="bi bi-box-seam me-2 text-success"></i>
        <span class="fw-semibold">Thiết bị đã sử dụng trong tiết</span>
        <span class="text-muted ms-2" style="font-size:.8rem;">(chọn và nhập số lượng)</span>
    </div>
    <div class="card-body p-4">
        {{-- Nơi chọn thiết bị --}}
        <div class="mb-4 d-flex gap-2 align-items-center">
            <select id="deviceSelector" class="form-select flex-grow-1">
                <option value="">-- Chọn thiết bị để thêm --</option>
            </select>
            <button type="button" class="btn btn-outline-primary" style="white-space:nowrap;" onclick="addDevice()">
                <i class="bi bi-plus-lg me-1"></i>Thêm vào danh sách
            </button>
        </div>

        <div id="devicesList">
            <div class="text-muted text-center py-3" id="noDeviceMsg">Chưa có thiết bị nào được chọn...</div>
        </div>
    </div>
</div>

{{-- BƯỚC 3: SỰ CỐ --}}
<div class="card mb-4" id="issuesSection" style="{{ old('room_id') ? '' : 'display:none;' }}">
    <div class="card-header py-3 px-4">
        <i class="bi bi-exclamation-triangle me-2 text-danger"></i>
        <span class="fw-semibold">Báo cáo sự cố thiết bị</span>
        <span class="text-muted ms-2" style="font-size:.8rem;">(hỏng hoặc tiêu hao)</span>
    </div>
    <div class="card-body p-4">
        <div id="issuesList">
            <div class="text-muted text-center py-3" id="noIssueMsg">Chưa có thiết bị nào được chọn...</div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 justify-content-end">
    <a href="{{ route('lesson-reports.my') }}" class="btn btn-outline-secondary">Hủy</a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-send me-1"></i>Gửi báo cáo
    </button>
</div>
</form>
</div>
</div>
@endsection

@push('scripts')
<script>
let currentRoomDevices = [];
let nextIndex = 0;

function loadDevices(roomId) {
    const select = document.getElementById('roomSelect');
    const option = select.querySelector(`option[value="${roomId}"]`);

    if (!option || !roomId) {
        document.getElementById('devicesSection').style.display = 'none';
        document.getElementById('issuesSection').style.display  = 'none';
        return;
    }

    currentRoomDevices = JSON.parse(option.dataset.devices || '[]');
    document.getElementById('devicesSection').style.display = '';
    document.getElementById('issuesSection').style.display  = '';

    // Reset danh sách đã chọn
    document.getElementById('devicesList').innerHTML = '<div class="text-muted text-center py-3" id="noDeviceMsg">Chưa có thiết bị nào được chọn...</div>';
    document.getElementById('issuesList').innerHTML = '<div class="text-muted text-center py-3" id="noIssueMsg">Chưa có thiết bị nào được chọn...</div>';
    nextIndex = 0;

    updateDeviceSelector();
}

function updateDeviceSelector() {
    const selector = document.getElementById('deviceSelector');
    selector.innerHTML = '<option value="">-- Chọn thiết bị để thêm --</option>';
    
    currentRoomDevices.forEach(d => {
        let opt = document.createElement('option');
        opt.value = d.id;
        opt.text = `${d.name} (Còn: ${d.qty} ${d.unit})`;
        selector.appendChild(opt);
    });
}

function addDevice() {
    const selector = document.getElementById('deviceSelector');
    const deviceId = selector.value;
    
    if (!deviceId) return;

    // Kiểm tra xem thiết bị đã được chọn chưa
    if (document.querySelector(`input[name^="devices"][value="${deviceId}"]`)) {
        alert('Thiết bị này đã có trong danh sách! Vui lòng thay đổi số lượng bên dưới.');
        return;
    }

    const device = currentRoomDevices.find(d => d.id == deviceId);
    if (!device) return;

    // Ẩn thông báo mặc định
    const noDevMsg = document.getElementById('noDeviceMsg');
    if (noDevMsg) noDevMsg.remove();
    
    const noIssueMsg = document.getElementById('noIssueMsg');
    if (noIssueMsg) noIssueMsg.remove();

    const i = nextIndex++;

    // 1. Thêm HTML vào phần thiết bị đã sử dụng
    const devHtml = `
        <div class="device-row" id="dev-row-${i}">
            <div class="d-flex align-items-center gap-3">
                <div class="flex-fill">
                    <span class="fw-semibold">${device.name}</span>
                    <span class="badge bg-info text-dark ms-2 device-badge">Còn: ${device.qty} ${device.unit}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label style="font-size:.8rem;color:#64748b;">Số lượng dùng:</label>
                    <input type="hidden" name="devices[${i}][id]" value="${device.id}">
                    <input type="number" name="devices[${i}][qty]" class="form-control form-control-sm"
                           style="width:80px;" min="0.01" step="any" max="${device.qty}" value="1" placeholder="0" required>
                    <span style="font-size:.8rem;color:#64748b;">${device.unit}</span>
                    <button type="button" class="btn btn-sm text-danger ms-2" onclick="removeDevice(${i})" title="Xoá khỏi danh sách">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    document.getElementById('devicesList').insertAdjacentHTML('beforeend', devHtml);

    // 2. Thêm HTML vào phần báo cáo sự cố
    const issueHtml = `
        <div class="device-row" id="issue-row-${i}">
            <div class="fw-semibold mb-2">${device.name} <span class="badge bg-secondary device-badge">${device.unit}</span></div>
            <input type="hidden" name="issues[${i}][id]" value="${device.id}">
            <div class="row g-2">
                <div class="col-6 col-md-3">
                    <label style="font-size:.78rem;color:#64748b;">Số hỏng</label>
                    <input type="number" name="issues[${i}][broken]" class="form-control form-control-sm"
                           min="0" step="1" value="0" placeholder="0">
                </div>
                <div class="col-6 col-md-3">
                    <label style="font-size:.78rem;color:#64748b;">Tiêu hao</label>
                    <input type="number" name="issues[${i}][consumed]" class="form-control form-control-sm"
                           min="0" step="any" value="0" placeholder="0">
                </div>
                <div class="col-6 col-md-3">
                    <label style="font-size:.78rem;color:#dc2626;font-weight:600;">Mất</label>
                    <input type="number" name="issues[${i}][lost]" class="form-control form-control-sm border-danger"
                           min="0" step="any" value="0" placeholder="0">
                </div>
                <div class="col-6 col-md-3">
                    <label style="font-size:.78rem;color:#64748b;">Ghi chú sự cố</label>
                    <input type="text" name="issues[${i}][note]" class="form-control form-control-sm"
                           placeholder="Mô tả tình trạng...">
                </div>
            </div>
        </div>
    `;
    document.getElementById('issuesList').insertAdjacentHTML('beforeend', issueHtml);
    
    // Reset dropdown sau khi thêm để người dùng dễ chọn món khác
    selector.value = '';
}

function removeDevice(index) {
    document.getElementById(`dev-row-${index}`).remove();
    document.getElementById(`issue-row-${index}`).remove();
    
    // Nếu xóa hết thì hiện lại thông báo mặc định
    if (document.getElementById('devicesList').children.length === 0) {
        document.getElementById('devicesList').innerHTML = '<div class="text-muted text-center py-3" id="noDeviceMsg">Chưa có thiết bị nào được chọn...</div>';
        document.getElementById('issuesList').innerHTML = '<div class="text-muted text-center py-3" id="noIssueMsg">Chưa có thiết bị nào được chọn...</div>';
    }
}

// Khởi tạo nếu có giá trị cũ (validation fail => pre-select room)
window.addEventListener('DOMContentLoaded', () => {
    const roomId = document.getElementById('roomSelect').value;
    if (roomId) loadDevices(roomId);
});
</script>
@endpush
