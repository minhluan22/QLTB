@extends('layouts.app')

@section('title', 'Yêu cầu mượn thiết bị')
@section('page-title', 'Yêu cầu mượn thiết bị')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="font-size:1.25rem;">
                {{ auth()->user()->isAdmin() ? 'Tất cả phiếu mượn' : 'Phiếu mượn của tôi' }}
            </h2>
            <p class="text-muted mb-0" style="font-size:.85rem;">Quản lý trạng thái mượn/trả thiết bị</p>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->isAdmin())
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="bi bi-file-earmark-excel me-1"></i>Xuất Excel
                </button>
            @endif
            @if(auth()->user()->isTeacher())
                <a href="{{ route('borrow-requests.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Tạo yêu cầu mượn
                </a>
            @endif
        </div>
    </div>

    {{-- Bộ lọc nâng cao --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form action="{{ route('borrow-requests.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-2">
                    <label class="form-label text-muted mb-1" style="font-size:.75rem; font-weight:600;">TÌM KIẾM</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                            placeholder="Mã phiếu, mục đích..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label text-muted mb-1" style="font-size:.75rem; font-weight:600;">TRẠNG THÁI</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- Tất cả --</option>
                        <option value="borrowing" {{ request('status') == 'borrowing' ? 'selected' : '' }}>Đang mượn</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Quá hạn</option>
                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Đã trả</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label text-muted mb-1" style="font-size:.75rem; font-weight:600;">TỔ THIẾT BỊ</label>
                    <input type="text" name="subject_group" list="groupList" class="form-control form-control-sm"
                        placeholder="-- Tất cả --" value="{{ request('subject_group') }}">
                    <datalist id="groupList">
                        @php $groups = \App\Models\Device::distinct()->pluck('subject_group')->filter()->sort()->values(); @endphp
                        @foreach($groups as $group)
                            <option value="{{ $group }}"></option>
                        @endforeach
                    </datalist>
                </div>

                @if(auth()->user()->isAdmin())
                    <div class="col-6 col-md-2">
                        <label class="form-label text-muted mb-1" style="font-size:.75rem; font-weight:600;">NGƯỜI MƯỢN</label>
                        <input type="text" name="borrower" class="form-control form-control-sm" placeholder="Tên giáo viên..."
                            value="{{ request('borrower') }}">
                    </div>
                @endif

                <div class="col-6 col-md-1">
                    <label class="form-label text-muted mb-1" style="font-size:.75rem; font-weight:600;">TỪ NGÀY</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                        value="{{ request('date_from') }}">
                </div>

                <div class="col-6 col-md-1">
                    <label class="form-label text-muted mb-1" style="font-size:.75rem; font-weight:600;">ĐẾN NGÀY</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>

                <div class="col-12 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">Tìm kiếm</button>
                    <a href="{{ route('borrow-requests.index') }}" class="btn btn-outline-secondary btn-sm flex-fill">Bỏ tìm
                        kiếm</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($requests->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Không có phiếu mượn nào.
                    @if(auth()->user()->isTeacher())
                        <div class="mt-2">
                            <a href="{{ route('borrow-requests.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus"></i> Tạo phiếu mượn mới
                            </a>
                        </div>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">#</th>
                                @if(auth()->user()->isAdmin())
                                    <th>Giáo viên</th>
                                @endif
                                <th style="width: 25%">Thiết bị mượn</th>
                                <th>Mục đích</th>
                                <th>Ngày mượn</th>
                                <th>Ngày trả DK</th>
                                <th>Trạng thái</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $req)
                                <tr>
                                    <td class="px-4 text-muted" style="font-size:.8rem;">#{{ $req->id }}</td>
                                    @if(auth()->user()->isAdmin())
                                        <td>
                                            <div class="fw-semibold text-dark" style="font-size:.875rem;">{{ $req->user->name }}</div>
                                            <div class="text-muted" style="font-size:.75rem;">
                                                {{ $req->user->teaching_subject ?? 'Chưa cập nhật môn' }}
                                            </div>
                                        </td>
                                    @endif
                                    <td>
                                        <ul class="list-unstyled mb-0" style="font-size:.85rem;">
                                            @foreach($req->borrowDetails as $detail)
                                                <li class="mb-1">
                                                    <span class="fw-semibold text-dark">{{ $detail->device->name }}</span>
                                                    <strong class="text-danger ms-1">x{{ $detail->quantity }}</strong>
                                                    @if($detail->device->subject_group)
                                                        <div class="text-muted" style="font-size:.7rem;"><i
                                                                class="bi bi-diagram-3 me-1"></i>Tổ: {{ $detail->device->subject_group }}
                                                        </div>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td style="font-size:.875rem;">{{ Str::limit($req->purpose, 45) }}</td>
                                    <td style="font-size:.85rem;">{{ $req->borrow_date->format('d/m/Y') }}</td>
                                    <td style="font-size:.85rem;">{{ $req->expected_return_date->format('d/m/Y') }}</td>
                                    <td>{!! $req->statusBadge() !!}</td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="{{ route('borrow-requests.show', $req) }}" class="btn btn-sm btn-outline-info"
                                                title="Xem chi tiết">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            @if(in_array($req->status, ['borrowing', 'overdue']))
                                                @if(auth()->user()->isAdmin())
                                                    <a href="{{ route('borrow-requests.edit', $req) }}"
                                                        class="btn btn-sm btn-outline-warning" title="Sửa phiếu mượn">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endif

                                                {{-- Teacher: nút trả đồ có Modal --}}
                                                @if(auth()->id() === $req->user_id)
                                                    <button type="button" class="btn btn-sm btn-success" title="Trả lại thiết bị"
                                                        onclick="openReturnDetailModal({{ $req->id }}, 'Phiếu #{{ $req->id }}', {{ $req->borrowDetails->count() === 1 ? $req->borrowDetails->first()->quantity : -1 }})">
                                                        <i class="bi bi-arrow-return-left"></i> Trả đồ
                                                    </button>
                                                @endif
                                                {{-- Admin: nút Xoá phiếu mượn (nếu tạo nhầm) --}}
                                                @if(auth()->user()->isAdmin())
                                                    <form action="{{ route('borrow-requests.destroy', $req) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            onclick="confirmDelete(event, 'Phiếu mượn #{{ $req->id }} (Số lượng kho sẽ được hoàn trả nếu đang mượn)')"
                                                            class="btn btn-sm btn-outline-danger" title="Xóa phiếu mượn rác">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($requests->total() > 0)
                    <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top bg-light-subtle">
                        <div class="text-muted" style="font-size:.8rem;">
                            Hiển thị <strong>{{ $requests->firstItem() ?? 0 }}</strong> – <strong>{{ $requests->lastItem() ?? 0 }}</strong> 
                            trên tổng số <strong>{{ $requests->total() }}</strong> phiếu mượn
                        </div>
                        <div>
                            {{ $requests->withQueryString()->links() }}
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
    </div>

    {{-- MODAL TRẢ THIẾT BỊ DÙNG CHUNG --}}
    <div class="modal fade" id="confirmReturnModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
                <div class="modal-header bg-success text-white py-3 border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-arrow-return-left me-2"></i> Trả thiết bị: <span
                            id="returnDeviceName" class="text-warning"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="returnForm" method="POST" action="">
                    @csrf
                    <div class="modal-body bg-light p-4">
                        <div class="mb-4" id="returnQuantityContainer">
                            <label class="form-label fw-bold text-secondary mb-1" style="font-size: .85rem;">TỔNG SỐ LƯỢNG
                                TRẢ</label>
                            <input type="number" name="return_qty" id="returnQuantity"
                                class="form-control fw-bold fs-5 text-primary border-0 shadow-sm" readonly>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;"><i class="bi bi-info-circle me-1"></i>
                                Số lượng trả bằng tổng của các tình trạng bên dưới.</div>
                        </div>

                        <div class="mb-4 bg-white p-3 rounded border-0 shadow-sm" id="conditionQuantitiesContainer">
                            <label class="form-label fw-bold text-secondary mb-3 border-bottom pb-2"
                                style="font-size: .85rem; width:100%;">PHÂN BỔ SỐ LƯỢNG TRẢ <span
                                    class="text-danger">*</span></label>
                            <div class="row g-3 align-items-center mb-2">
                                <div class="col-8">
                                    <span class="fw-semibold text-success"><i class="bi bi-check-circle me-1"></i> Bình
                                        thường</span>
                                </div>
                                <div class="col-4">
                                    <input type="number" name="normal_qty" id="calcNormalQty"
                                        class="form-control form-control-sm text-center fw-bold text-success fs-6 q-input"
                                        value="0" min="0" required>
                                </div>
                            </div>
                            <div class="row g-3 align-items-center mb-2">
                                <div class="col-8">
                                    <span class="fw-semibold text-warning"><i class="bi bi-exclamation-triangle me-1"></i>
                                        Hỏng (sửa được)</span>
                                </div>
                                <div class="col-4">
                                    <input type="number" name="damaged_qty" id="calcDamagedQty"
                                        class="form-control form-control-sm text-center fw-bold text-warning fs-6 q-input"
                                        value="0" min="0" required>
                                </div>
                            </div>
                            <div class="row g-3 align-items-center">
                                <div class="col-8">
                                    <span class="fw-semibold text-danger"><i class="bi bi-x-circle me-1"></i> Mất / Thất
                                        lạc</span>
                                </div>
                                <div class="col-4">
                                    <input type="number" name="lost_qty" id="calcLostQty"
                                        class="form-control form-control-sm text-center fw-bold text-danger fs-6 q-input"
                                        value="0" min="0" required>
                                </div>
                            </div>
                            <div class="text-danger mt-2" id="qtyError" style="display:none; font-size:.8rem;">Tổng số lượng
                                trả không được vượt quá số đang mượn (<span id="maxQtyLabel">0</span>)!</div>
                        </div>

                        {{-- FORM BÁO HỎNG --}}
                        <div id="damagedFields"
                            class="mb-4 p-3 bg-white rounded border-start border-warning border-4 shadow-sm"
                            style="display: none;">
                            <h6 class="fw-bold text-warning mb-3"><i class="bi bi-exclamation-triangle me-2"></i>Báo cáo
                                thiết bị hỏng</h6>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: .8rem;">NGÀY
                                        PHÁT HIỆN <span class="text-danger">*</span></label>
                                    <input type="date" name="damaged_detected_date"
                                        class="form-control border-0 shadow-sm bg-light" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: .8rem;">MỨC ĐỘ
                                        HỎNG <span class="text-danger">*</span></label>
                                    <select name="damaged_severity" id="damagedSeverity"
                                        class="form-select border-0 shadow-sm bg-light">
                                        <option value="minor">Hỏng nhẹ (sửa được)</option>
                                        <option value="moderate">Hỏng vừa (thay linh kiện)</option>
                                        <option value="severe">Hỏng nặng (bỏ đi)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: .8rem;">NGUYÊN
                                        NHÂN</label>
                                    <input type="text" name="damaged_cause" class="form-control border-0 shadow-sm bg-light"
                                        placeholder="VD: Gãy, Cong...">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: .8rem;">HƯỚNG XỬ
                                        LÝ</label>
                                    <input type="text" name="damaged_resolution"
                                        class="form-control border-0 shadow-sm bg-light" placeholder="VD: Thay mới...">
                                </div>
                            </div>
                            <div class="mb-1">
                                <label class="form-label fw-bold text-secondary mb-1" style="font-size: .8rem;">MÔ TẢ CHI
                                    TIẾT DÀNH CHO BÁO HỎNG</label>
                                <textarea name="damaged_note" id="damagedNote"
                                    class="form-control border-0 shadow-sm bg-light" rows="2"
                                    placeholder="Ghi chú cụ thể..."></textarea>
                            </div>
                        </div>

                        {{-- FORM BÁO MẤT --}}
                        <div id="lostFields" class="mb-4 p-3 bg-white rounded border-start border-danger border-4 shadow-sm"
                            style="display: none;">
                            <h6 class="fw-bold text-danger mb-3"><i class="bi bi-x-circle me-2"></i>Báo cáo thiết bị mất
                            </h6>
                            <div class="row g-2 mb-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: .8rem;">NGÀY
                                        PHÁT HIỆN <span class="text-danger">*</span></label>
                                    <input type="date" name="lost_detected_date"
                                        class="form-control border-0 shadow-sm bg-light" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: .8rem;">NGUYÊN
                                        NHÂN</label>
                                    <input type="text" name="lost_cause" class="form-control border-0 shadow-sm bg-light"
                                        placeholder="VD: Để quên, rơi rớt...">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: .8rem;">HƯỚNG XỬ
                                        LÝ</label>
                                    <input type="text" name="lost_resolution"
                                        class="form-control border-0 shadow-sm bg-light" placeholder="VD: Bồi thường...">
                                </div>
                            </div>
                            <div class="mb-1">
                                <label class="form-label fw-bold text-secondary mb-1" style="font-size: .8rem;">MÔ TẢ CHI
                                    TIẾT DÀNH CHO BÁO MẤT</label>
                                <textarea name="lost_note" id="lostNote" class="form-control border-0 shadow-sm bg-light"
                                    rows="2" placeholder="Ghi chú cụ thể..."></textarea>
                            </div>
                        </div>

                        <div class="mb-2" id="normalNoteWrapper">
                            <label class="form-label fw-bold text-secondary mb-1" style="font-size: .85rem;">GHI CHÚ (NẾU
                                CÓ)</label>
                            <textarea name="normal_note" class="form-control border-0 shadow-sm" rows="3"
                                placeholder="Ghi chú chi tiết nếu thiết bị hỏng/mất..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer py-3 bg-white border-0">
                        <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success fw-bold px-4 rounded-pill shadow-sm"><i
                                class="bi bi-check-circle me-2"></i>Xác nhận trả</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- EXPORT MODAL -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('borrow-requests.export') }}" method="GET" class="modal-content shadow-lg border-0"
                style="border-radius: 12px;">
                <div class="modal-header bg-success text-white py-3 border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-excel me-2"></i>Tùy chọn Xuất Excel (1
                        Sheet)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light p-4">

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label text-muted mb-1" style="font-size:.75rem; font-weight:600;">TỔ CHUYÊN
                                MÔN</label>
                            <input type="text" name="department" list="exportGroupList"
                                class="form-control border-0 shadow-sm" placeholder="-- Tất cả tổ --">
                            <datalist id="exportGroupList">
                                @php $groups = \App\Models\Device::distinct()->pluck('subject_group')->filter()->sort()->values(); @endphp
                                @foreach($groups as $group)
                                    <option value="{{ $group }}"></option>
                                @endforeach
                            </datalist>
                        </div>

                        <div class="col-12">
                            <label class="form-label text-muted mb-1" style="font-size:.75rem; font-weight:600;">GIÁO
                                VIÊN</label>
                            <input type="text" name="borrower" list="exportTeacherList"
                                class="form-control border-0 shadow-sm" placeholder="-- Tất cả giáo viên --">
                            <datalist id="exportTeacherList">
                                @php $teachers = \App\Models\User::where('role', 'teacher')->orWhere('role', 'admin')->get(); @endphp
                                @foreach($teachers as $t)
                                    <option value="{{ $t->name }}">{{ $t->email }}</option>
                                @endforeach
                            </datalist>
                        </div>

                        <div class="col-12">
                            <label class="form-label text-muted mb-1" style="font-size:.75rem; font-weight:600;">TRẠNG
                                THÁI</label>
                            <select name="status" class="form-select border-0 shadow-sm">
                                <option value="">-- Tất cả --</option>
                                <option value="borrowing">Đang mượn</option>
                                <option value="returned">Đã trả</option>
                                <option value="overdue">Quá hạn</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label text-muted mb-1" style="font-size:.75rem; font-weight:600;">TỪ
                                NGÀY</label>
                            <input type="date" name="from_date" class="form-control border-0 shadow-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted mb-1" style="font-size:.75rem; font-weight:600;">ĐẾN
                                NGÀY</label>
                            <input type="date" name="to_date" class="form-control border-0 shadow-sm">
                        </div>
                    </div>

                    <div class="text-muted mt-2" style="font-size:.8rem;">
                        <i class="bi bi-info-circle me-1"></i> Bỏ trống các mục nếu muốn xuất toàn bộ danh sách.
                    </div>
                </div>
                <div class="modal-footer border-0 bg-white border-top">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill"
                        data-bs-dismiss="modal">Huỷ</button>
                    <button type="submit" class="btn btn-success fw-bold px-4 rounded-pill shadow-sm"><i
                            class="bi bi-download me-2"></i>Xuất File</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleExportTime() {
            const type = document.getElementById('exportTimeType').value;
            const els = document.querySelectorAll('.export-time-fields');
            els.forEach(el => el.style.display = 'none');

            if (type === 'date') {
                document.getElementById('exportDateRange').style.display = 'flex';
            } else if (type === 'month') {
                document.getElementById('exportMonth').style.display = 'flex';
            } else if (type === 'year') {
                document.getElementById('exportYear').style.display = 'block';
            }
        }
    </script>
@endpush

@push('scripts')
    <script>
        let globalMaxQty = 0;

        function openReturnDetailModal(reqId, deviceName, maxQty) {
            globalMaxQty = maxQty;
            document.getElementById('returnDeviceName').innerText = deviceName;

            const qtyContainer = document.getElementById('returnQuantityContainer');
            const condContainer = document.getElementById('conditionQuantitiesContainer');
            const returnQtyInput = document.getElementById('returnQuantity');
            const normalQtyInput = document.getElementById('calcNormalQty');
            const damagedQtyInput = document.getElementById('calcDamagedQty');
            const lostQtyInput = document.getElementById('calcLostQty');
            const damagedFields = document.getElementById('damagedFields');
            const lostFields = document.getElementById('lostFields');

            if (maxQty === -1) { // Phiếu mượn có nhiều hơn 1 loại học cụ
                qtyContainer.style.display = 'none';
                condContainer.style.display = 'none';

                // Remove required
                normalQtyInput.removeAttribute('required');
                damagedQtyInput.removeAttribute('required');
                lostQtyInput.removeAttribute('required');
            } else {
                qtyContainer.style.display = 'block';
                condContainer.style.display = 'block';

                document.getElementById('maxQtyLabel').innerText = maxQty;

                // Set required
                normalQtyInput.setAttribute('required', 'required');
                damagedQtyInput.setAttribute('required', 'required');
                lostQtyInput.setAttribute('required', 'required');

                returnQtyInput.value = maxQty;
                normalQtyInput.value = maxQty;
                damagedQtyInput.value = 0;
                lostQtyInput.value = 0;
            }

            // Reset sub forms
            damagedFields.style.display = 'none';
            lostFields.style.display = 'none';

            document.getElementById('normalNoteWrapper').style.display = 'block';
            document.getElementById('qtyError').style.display = 'none';

            // Đổi đường dẫn API submit form
            document.getElementById('returnForm').action = "{{ url('borrow-requests') }}/" + reqId + "/return";

            const modal = new bootstrap.Modal(document.getElementById('confirmReturnModal'));
            // Ẩn dropdown click (nếu có)
            triggerEvent(document, 'click');
            modal.show();
        }

        // Hàm trigger event đơn giản để tắt dropdowns
        function triggerEvent(el, type) {
            if ('createEvent' in document) {
                var e = document.createEvent('HTMLEvents');
                e.initEvent(type, false, true);
                el.dispatchEvent(e);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const normalInput = document.getElementById('calcNormalQty');
            const damagedInput = document.getElementById('calcDamagedQty');
            const lostInput = document.getElementById('calcLostQty');
            const totalInput = document.getElementById('returnQuantity');
            const qtyError = document.getElementById('qtyError');
            const submitBtn = document.querySelector('#returnForm button[type="submit"]');

            const damagedFields = document.getElementById('damagedFields');
            const damagedNote = document.getElementById('damagedNote');
            const damagedSeverity = document.getElementById('damagedSeverity');

            const lostFields = document.getElementById('lostFields');
            const lostNote = document.getElementById('lostNote');

            const qInputs = document.querySelectorAll('.q-input');

            qInputs.forEach(input => {
                input.addEventListener('input', function () {
                    // If container is hidden (multiple devices case), skip
                    if (normalInput.offsetParent === null) return;

                    let n = parseInt(normalInput.value) || 0;
                    let d = parseInt(damagedInput.value) || 0;
                    let l = parseInt(lostInput.value) || 0;

                    let total = n + d + l;
                    totalInput.value = total;

                    if (total > globalMaxQty || total === 0) {
                        qtyError.style.display = 'block';
                        submitBtn.disabled = true;
                    } else {
                        qtyError.style.display = 'none';
                        submitBtn.disabled = false;
                    }

                    if (d > 0) {
                        damagedFields.style.display = 'block';
                        damagedSeverity.required = true;
                    } else {
                        damagedFields.style.display = 'none';
                        damagedNote.required = false;
                        damagedSeverity.required = false;
                    }

                    if (l > 0) {
                        lostFields.style.display = 'block';
                    } else {
                        lostFields.style.display = 'none';
                        lostNote.required = false;
                    }
                });
            });
        });
    </script>
@endpush