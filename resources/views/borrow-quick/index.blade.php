@extends('layouts.app')

@section('title', 'Danh sách thiết bị')
@section('page-title', 'Danh sách thiết bị')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
        <span class="fw-semibold text-primary"><i class="bi bi-list-check me-2"></i>Quyển theo dõi mượn / trả nhanh</span>
    </div>

    {{-- Bộ lọc nâng cao --}}
    <div class="card-body border-bottom bg-light py-3">
        <form action="{{ route('borrow-quick.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-2">
                <label class="form-label text-muted mb-1" style="font-size:.75rem; font-weight:600;">TÌM THIẾT BỊ</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Tên, mã thiết bị..." value="{{ request('search') }}">
                </div>
            </div>
            
            <div class="col-6 col-md-2">
                <label class="form-label text-muted mb-1" style="font-size:.75rem; font-weight:600;">TỔ CHUYÊN MÔN</label>
                <input type="text" name="subject_group" list="groupList" class="form-control form-control-sm" placeholder="-- Tất cả --" value="{{ request('subject_group') }}">
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
                <input type="text" name="borrower" class="form-control form-control-sm" placeholder="Tên người mượn..." value="{{ request('borrower') }}">
            </div>
            @endif

            <div class="col-6 col-md-2">
                <label class="form-label text-muted mb-1" style="font-size:.75rem; font-weight:600;">TỪ NGÀY</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label text-muted mb-1" style="font-size:.75rem; font-weight:600;">ĐẾN NGÀY</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>

            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">Tìm kiếm</button>
                <a href="{{ route('borrow-quick.index') }}" class="btn btn-outline-secondary btn-sm flex-fill">Bỏ tìm kiếm</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4 text-uppercase" style="font-size: .75rem; color: #64748b; letter-spacing: .05em;">Tên thiết bị</th>
                        <th class="text-uppercase" style="font-size: .75rem; color: #64748b; letter-spacing: .05em;">Tổ</th>
                        @if(auth()->user()->isAdmin())
                        <th class="text-uppercase" style="font-size: .75rem; color: #64748b; letter-spacing: .05em;">Người mượn</th>
                        <th class="text-center text-uppercase" style="font-size: .75rem; color: #64748b; letter-spacing: .05em;">Số lượng</th>
                        <th class="text-uppercase" style="font-size: .75rem; color: #64748b; letter-spacing: .05em;">Ngày mượn</th>
                        <th class="text-uppercase" style="font-size: .75rem; color: #64748b; letter-spacing: .05em;">Ngày trả</th>
                        <th class="text-center text-uppercase" style="font-size: .75rem; color: #64748b; letter-spacing: .05em;">Trạng thái</th>
                        @endif
                        <th class="text-center text-uppercase" style="font-size: .75rem; color: #64748b; letter-spacing: .05em;">Còn lại</th>
                        @if(!auth()->user()->isAdmin())
                        <th class="text-center text-uppercase" style="font-size: .75rem; color: #64748b; letter-spacing: .05em;" style="width: 250px;">Thao tác</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($flatList as $item)
                        <tr>
                            <td class="px-4 fw-semibold text-dark">
                                {{ $item->device->name }} 
                                <br>
                                <span class="badge bg-light text-secondary border mt-1">{{ $item->device->code }}</span>
                            </td>
                            <td>
                                @if($item->device->subject_group)
                                    <span class="text-secondary" style="font-size: .85rem;">{{ $item->device->subject_group }}</span>
                                @else
                                    <span class="text-muted fst-italic" style="font-size: .8rem;">—</span>
                                @endif
                            </td>
                            
                            @if(auth()->user()->isAdmin())
                                @if($item->is_borrowed)
                                    <td>
                                        <div class="fw-semibold text-primary">{{ $item->borrower_name }}</div>
                                    </td>
                                    <td class="text-center fw-bold text-danger">{{ $item->quantity }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->borrow_date)->format('d/m/Y') }}</td>
                                    <td>
                                        @php 
                                            $returnDate = \Carbon\Carbon::parse($item->return_date);
                                            $isOverdue = $returnDate->isPast() && !$returnDate->isToday();
                                        @endphp
                                        <span class="{{ $isOverdue ? 'badge bg-danger' : 'text-dark' }}">
                                            {{ $returnDate->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">Đang mượn</span>
                                        @if($isOverdue) <br><small class="text-danger fw-bold">Quá hạn</small> @endif
                                    </td>
                                @else
                                    <td class="text-muted fst-italic">Trống</td>
                                    <td class="text-center text-muted">-</td>
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>
                                    <td class="text-center text-muted">-</td>
                                @endif
                            @endif
                            
                            <td class="text-center">
                                @if($item->device->available_qty > 0)
                                    <span class="badge bg-success" style="font-size: .85rem;">{{ $item->device->available_qty }}</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: .85rem;">0</span>
                                @endif
                            </td>
                            
                            @if(!auth()->user()->isAdmin())
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    {{-- Nút Trả -> Xem chi tiết --}}
                                    @if($item->is_borrowed)
                                        <button type="button" class="btn btn-sm btn-success px-3 shadow-sm position-relative" data-bs-toggle="modal" data-bs-target="#returnModal-{{ $item->device->id }}">
                                            <i class="bi bi-eye me-1"></i> Xem chi tiết
                                            @if($item->active_borrows->count() > 1)
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                                {{ $item->active_borrows->count() }}
                                            </span>
                                            @endif
                                        </button>
                                    @endif

                                    {{-- Nút Mượn --}}
                                    @if($item->device->available_qty > 0)
                                        <button type="button" class="btn btn-sm btn-primary px-3 shadow-sm" 
                                            onclick="openBorrowModal({{ $item->device->id }}, '{{ addslashes($item->device->name) }}', {{ $item->device->available_qty }})">
                                            <i class="bi bi-box-arrow-right me-1"></i> Mượn
                                        </button>
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() ? 7 : 4 }}" class="text-center py-5 text-muted">
                                Không có thiết bị nào để hiển thị.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($flatList->hasPages())
    <div class="card-footer bg-white px-4 py-3 border-top">
        {{ $flatList->links() }}
    </div>
    @endif
</div>

{{-- MODAL MƯỢN THIẾT BỊ --}}
<div class="modal fade" id="borrowModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header bg-primary text-white border-0 py-3">
        <h5 class="modal-title fw-bold"><i class="bi bi-cart-plus me-2"></i>Chi tiết Phiếu Mượn</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="borrowForm" method="POST" action="">
          @csrf
          <div class="modal-body p-4 bg-light">
              <div class="mb-4">
                  <label class="form-label text-muted fw-semibold" style="font-size:.85rem;">THIẾT BỊ ĐƯỢC CHỌN</label>
                  <input type="text" class="form-control form-control-lg bg-white border-0 shadow-sm fw-bold text-primary" id="modalDeviceName" readonly>
              </div>
              
              <div class="row g-3 mb-4">
                  <div class="col-6">
                      <label class="form-label text-muted fw-semibold" style="font-size:.85rem;">KHO CÒN LẠI</label>
                      <input type="text" class="form-control bg-white border-0 shadow-sm font-monospace text-success fw-bold" id="modalAvailableQty" readonly>
                  </div>
                  <div class="col-6">
                      <label class="form-label text-muted fw-semibold" style="font-size:.85rem;">SỐ LƯỢNG MƯỢN <span class="text-danger">*</span></label>
                      <input type="number" name="quantity" class="form-control border-0 shadow-sm fw-bold" id="modalQuantity" min="1" value="1" required>
                  </div>
              </div>

              <div class="mb-3">
                  <label class="form-label text-muted fw-semibold" style="font-size:.85rem;">NGÀY HẸN TRẢ <span class="text-danger">*</span></label>
                  <input type="date" name="expected_return_date" class="form-control border-0 shadow-sm" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
              </div>
              <div class="text-muted d-flex align-items-center gap-2 mt-2" style="font-size: 0.8rem;">
                  <i class="bi bi-info-circle text-primary"></i> Lịch mượn sẽ được tự động tính bắt đầu từ hôm nay.
              </div>
          </div>
          <div class="modal-footer border-0 bg-white py-3">
            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Thoát</button>
            <button type="submit" class="btn btn-primary px-4 fw-semibold"><i class="bi bi-check2-all me-2"></i>Xác nhận mượn</button>
          </div>
      </form>
    </div>
  </div>
</div>

@if(!auth()->user()->isAdmin())
    @foreach($flatList as $item)
        @if($item->is_borrowed)
        <!-- Return Modal -->
        <div class="modal fade" id="returnModal-{{ $item->device->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                    <div class="modal-header bg-success text-white py-3 border-0">
                        <h5 class="modal-title fw-bold"><i class="bi bi-list-check me-2"></i>Chi tiết mượn: {{ $item->device->name }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4" style="font-size:.85rem; color: #64748b;">Mã Phiếu</th>
                                        <th class="text-center" style="font-size:.85rem; color: #64748b;">Ngày mượn</th>
                                        <th class="text-center" style="font-size:.85rem; color: #64748b;">Ngày trả ĐK</th>
                                        <th class="text-center" style="font-size:.85rem; color: #64748b;">Số lượng</th>
                                        <th class="text-center" style="font-size:.85rem; color: #64748b; width: 140px;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item->active_borrows as $detail)
                                        @php $req = $detail->borrowRequest; @endphp
                                        <tr>
                                            <td class="px-4 fw-bold text-secondary">#{{ $req->id }}</td>
                                            <td class="text-center">{{ \Carbon\Carbon::parse($req->borrow_date)->format('d/m/Y') }}</td>
                                            <td class="text-center text-danger fw-semibold">{{ \Carbon\Carbon::parse($req->expected_return_date)->format('d/m/Y') }}</td>
                                            <td class="text-center fw-bold">{{ $detail->quantity }}</td>
                                            <td class="text-center py-2">
                                                <button type="button" class="btn btn-sm btn-outline-success px-3 shadow-sm rounded-pill" onclick="openReturnDetailModal({{ $req->id }}, '{{ addslashes($item->device->name) }}', {{ $detail->quantity }})">
                                                    <i class="bi bi-arrow-return-left"></i> Trả đồ
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-2">
                        <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endforeach
@endif

{{-- MODAL TRẢ THIẾT BỊ (BÁO CÁO TÌNH TRẠNG) --}}
<div class="modal fade" id="confirmReturnModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
      <div class="modal-header bg-success text-white py-3 border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-arrow-return-left me-2"></i> Trả thiết bị: <span id="returnDeviceName" class="text-warning"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="returnForm" method="POST" action="">
          @csrf
          <div class="modal-body bg-light p-4">
              <div class="mb-4">
                  <label class="form-label fw-bold text-secondary mb-1" style="font-size: .85rem;">TỔNG SỐ LƯỢNG TRẢ</label>
                  <input type="number" name="return_qty" id="returnQuantity" class="form-control fw-bold fs-5 text-primary border-0 shadow-sm" readonly>
                  <div class="text-muted mt-1" style="font-size: 0.8rem;"><i class="bi bi-info-circle me-1"></i> Số lượng trả bằng tổng của các tình trạng bên dưới.</div>
              </div>

              <div class="mb-4 bg-white p-3 rounded border-0 shadow-sm">
                  <label class="form-label fw-bold text-secondary mb-3 border-bottom pb-2" style="font-size: .85rem; width:100%;">PHÂN BỔ SỐ LƯỢNG TRẢ <span class="text-danger">*</span></label>
                  <div class="row g-3 align-items-center mb-2">
                       <div class="col-8">
                           <span class="fw-semibold text-success"><i class="bi bi-check-circle me-1"></i> Bình thường</span>
                       </div>
                       <div class="col-4">
                           <input type="number" name="normal_qty" id="calcNormalQty" class="form-control form-control-sm text-center fw-bold text-success fs-6 q-input" value="0" min="0" required>
                       </div>
                  </div>
                  <div class="row g-3 align-items-center mb-2">
                       <div class="col-8">
                           <span class="fw-semibold text-warning"><i class="bi bi-exclamation-triangle me-1"></i> Hỏng (sửa được)</span>
                       </div>
                       <div class="col-4">
                           <input type="number" name="damaged_qty" id="calcDamagedQty" class="form-control form-control-sm text-center fw-bold text-warning fs-6 q-input" value="0" min="0" required>
                       </div>
                  </div>
                  <div class="row g-3 align-items-center">
                       <div class="col-8">
                           <span class="fw-semibold text-danger"><i class="bi bi-x-circle me-1"></i> Mất / Thất lạc</span>
                       </div>
                       <div class="col-4">
                           <input type="number" name="lost_qty" id="calcLostQty" class="form-control form-control-sm text-center fw-bold text-danger fs-6 q-input" value="0" min="0" required>
                       </div>
                  </div>
                  <div class="text-danger mt-2" id="qtyError" style="display:none; font-size:.8rem;">Tổng số lượng trả không được vượt quá số đang mượn (<span id="maxQtyLabel">0</span>)!</div>
              </div>
              
              {{-- FORM BÁO HỎNG --}}
              <div id="damagedFields" class="mb-4 p-3 bg-white rounded border-start border-warning border-4 shadow-sm" style="display: none;">
                  <h6 class="fw-bold text-warning mb-3"><i class="bi bi-exclamation-triangle me-2"></i>Báo cáo thiết bị hỏng</h6>
                  <div class="row g-2 mb-3">
                      <div class="col-6">
                          <label class="form-label fw-bold text-secondary mb-1" style="font-size: .8rem;">NGÀY PHÁT HIỆN <span class="text-danger">*</span></label>
                          <input type="date" name="damaged_detected_date" class="form-control border-0 shadow-sm bg-light" value="{{ date('Y-m-d') }}">
                      </div>
                      <div class="col-6">
                          <label class="form-label fw-bold text-secondary mb-1" style="font-size: .8rem;">MỨC ĐỘ HỎNG <span class="text-danger">*</span></label>
                          <select name="damaged_severity" id="damagedSeverity" class="form-select border-0 shadow-sm bg-light">
                              <option value="minor">Hỏng nhẹ (sửa được)</option>
                              <option value="moderate">Hỏng vừa (thay linh kiện)</option>
                              <option value="severe">Hỏng nặng (bỏ đi)</option>
                          </select>
                      </div>
                  </div>
                  <div class="row g-2 mb-3">
                      <div class="col-6">
                          <label class="form-label fw-bold text-secondary mb-1" style="font-size: .8rem;">NGUYÊN NHÂN</label>
                          <input type="text" name="damaged_cause" class="form-control border-0 shadow-sm bg-light" placeholder="VD: Gãy, Cong...">
                      </div>
                      <div class="col-6">
                          <label class="form-label fw-bold text-secondary mb-1" style="font-size: .8rem;">HƯỚNG XỬ LÝ</label>
                          <input type="text" name="damaged_resolution" class="form-control border-0 shadow-sm bg-light" placeholder="VD: Thay mới...">
                      </div>
                  </div>
                  <div class="mb-1">
                      <label class="form-label fw-bold text-secondary mb-1" style="font-size: .8rem;">MÔ TẢ CHI TIẾT DÀNH CHO BÁO HỎNG</label>
                      <textarea name="damaged_note" id="damagedNote" class="form-control border-0 shadow-sm bg-light" rows="2" placeholder="Ghi chú cụ thể..."></textarea>
                  </div>
              </div>

              {{-- FORM BÁO MẤT --}}
              <div id="lostFields" class="mb-4 p-3 bg-white rounded border-start border-danger border-4 shadow-sm" style="display: none;">
                  <h6 class="fw-bold text-danger mb-3"><i class="bi bi-x-circle me-2"></i>Báo cáo thiết bị mất</h6>
                  <div class="row g-2 mb-3">
                      <div class="col-12">
                          <label class="form-label fw-bold text-secondary mb-1" style="font-size: .8rem;">NGÀY PHÁT HIỆN <span class="text-danger">*</span></label>
                          <input type="date" name="lost_detected_date" class="form-control border-0 shadow-sm bg-light" value="{{ date('Y-m-d') }}">
                      </div>
                  </div>
                  <div class="row g-2 mb-3">
                      <div class="col-6">
                          <label class="form-label fw-bold text-secondary mb-1" style="font-size: .8rem;">NGUYÊN NHÂN</label>
                          <input type="text" name="lost_cause" class="form-control border-0 shadow-sm bg-light" placeholder="VD: Để quên, rơi rớt...">
                      </div>
                      <div class="col-6">
                          <label class="form-label fw-bold text-secondary mb-1" style="font-size: .8rem;">HƯỚNG XỬ LÝ</label>
                          <input type="text" name="lost_resolution" class="form-control border-0 shadow-sm bg-light" placeholder="VD: Bồi thường...">
                      </div>
                  </div>
                  <div class="mb-1">
                      <label class="form-label fw-bold text-secondary mb-1" style="font-size: .8rem;">MÔ TẢ CHI TIẾT DÀNH CHO BÁO MẤT</label>
                      <textarea name="lost_note" id="lostNote" class="form-control border-0 shadow-sm bg-light" rows="2" placeholder="Ghi chú cụ thể..."></textarea>
                  </div>
              </div>

              <div class="mb-2" id="normalNoteWrapper">
                  <label class="form-label fw-bold text-secondary mb-1" style="font-size: .85rem;">GHI CHÚ (NẾU CÓ)</label>
                  <textarea name="normal_note" id="normalNote" class="form-control border-0 shadow-sm" rows="3" placeholder="Ghi chú cho phiếu trả..."></textarea>
              </div>
          </div>
          <div class="modal-footer py-3 bg-white border-0">
            <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-success fw-bold px-4 rounded-pill shadow-sm"><i class="bi bi-check-circle me-2"></i>Xác nhận trả</button>
          </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
    function openBorrowModal(deviceId, deviceName, availableQty) {
        document.getElementById('modalDeviceName').value = deviceName;
        document.getElementById('modalAvailableQty').value = availableQty;
        
        const qtyInput = document.getElementById('modalQuantity');
        qtyInput.max = availableQty;
        qtyInput.value = 1;
        
        // Base URL route modification
        const baseUrl = "{{ url('borrow-quick/borrow') }}";
        document.getElementById('borrowForm').action = baseUrl + "/" + deviceId;

        const modal = new bootstrap.Modal(document.getElementById('borrowModal'));
        modal.show();
    }

    let globalMaxQty = 0;

    function openReturnDetailModal(reqId, deviceName, maxQty) {
        globalMaxQty = maxQty;
        // Hiding other modals
        var returnModals = document.querySelectorAll('.modal.show');
        returnModals.forEach(function(modalElement) {
            var bsModal = bootstrap.Modal.getInstance(modalElement);
            if (bsModal && modalElement.id !== 'confirmReturnModal') {
                bsModal.hide();
            }
        });

        // Set parameters
        document.getElementById('returnDeviceName').innerText = deviceName;
        document.getElementById('maxQtyLabel').innerText = maxQty;
        
        // Reset inputs
        const returnQtyInput = document.getElementById('returnQuantity');
        const normalQtyInput = document.getElementById('calcNormalQty');
        const damagedQtyInput = document.getElementById('calcDamagedQty');
        const lostQtyInput = document.getElementById('calcLostQty');
        
        returnQtyInput.value = maxQty;
        normalQtyInput.value = maxQty;
        damagedQtyInput.value = 0;
        lostQtyInput.value = 0;
        
        document.getElementById('qtyError').style.display = 'none';
        
        // Reset sub forms
        document.getElementById('damagedFields').style.display = 'none';
        document.getElementById('lostFields').style.display = 'none';
        document.getElementById('normalNoteWrapper').style.display = 'block';

        // Build form action
        document.getElementById('returnForm').action = "{{ url('borrow-quick/return') }}/" + reqId;

        // Show after a slight delay
        setTimeout(() => {
            const modal = new bootstrap.Modal(document.getElementById('confirmReturnModal'));
            modal.show();
        }, 350); 
    }

    document.addEventListener('DOMContentLoaded', function() {
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
            input.addEventListener('input', function() {
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
