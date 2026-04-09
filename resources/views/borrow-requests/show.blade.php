@extends('layouts.app')

@section('title', 'Chi tiết yêu cầu #' . $borrowRequest->id)
@section('page-title', 'Chi tiết yêu cầu mượn')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('borrow-requests.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="flex-1">
        <h2 class="fw-bold mb-0" style="font-size:1.2rem;">Yêu cầu mượn #{{ $borrowRequest->id }}</h2>
        <p class="text-muted mb-0" style="font-size:.8rem;">Tạo lúc {{ $borrowRequest->created_at->format('H:i d/m/Y') }}</p>
    </div>
    <div>{!! $borrowRequest->statusBadge() !!}</div>
</div>

<div class="row g-3">
    {{-- Thông tin yêu cầu --}}
    <div class="col-12 col-lg-5">
        <div class="card mb-3">
            <div class="card-header py-3 px-4">
                <i class="bi bi-info-circle text-primary me-2"></i>Thông tin yêu cầu
            </div>
            <div class="card-body p-4">
                <dl class="row mb-0" style="font-size:.875rem;">
                    <dt class="col-5 text-muted">Người mượn:</dt>
                    <dd class="col-7 fw-semibold">{{ $borrowRequest->user->name }}</dd>

                    <dt class="col-5 text-muted">Email:</dt>
                    <dd class="col-7">{{ $borrowRequest->user->email }}</dd>

                    <dt class="col-5 text-muted">Mục đích:</dt>
                    <dd class="col-7">{{ $borrowRequest->purpose }}</dd>

                    <dt class="col-5 text-muted">Ngày mượn:</dt>
                    <dd class="col-7">{{ $borrowRequest->borrow_date->format('d/m/Y') }}</dd>

                    <dt class="col-5 text-muted">Ngày trả DK:</dt>
                    <dd class="col-7">{{ $borrowRequest->expected_return_date->format('d/m/Y') }}</dd>
                </dl>
            </div>
        </div>

        {{-- Actions --}}
        <div class="card">
            <div class="card-body p-4">
                @if(in_array($borrowRequest->status, ['borrowing', 'overdue']) && auth()->id() === $borrowRequest->user_id)
                    <button type="button" class="btn btn-success w-100"
                            onclick="openReturnDetailModal({{ $borrowRequest->id }}, 'Phiếu #{{ $borrowRequest->id }}', {{ $borrowRequest->borrowDetails->count() === 1 ? $borrowRequest->borrowDetails->first()->quantity : -1 }})">
                        <i class="bi bi-arrow-return-left me-1"></i>Trả thiết bị về kho
                    </button>
                @endif

                @if($borrowRequest->status === 'returned')
                    <div class="alert alert-success mb-0 text-center" style="font-size:.875rem;">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        Đã trả lúc {{ $borrowRequest->returnRecord?->return_date?->format('d/m/Y') ?? '—' }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Danh sách thiết bị mượn --}}
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header py-3 px-4">
                <i class="bi bi-laptop text-success me-2"></i>Thiết bị mượn
            </div>
            <div class="card-body p-0">
                <table class="table mb-0" style="font-size:.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4">Thiết bị</th>
                            <th>Mã</th>
                            <th class="text-center">Số lượng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($borrowRequest->borrowDetails as $detail)
                        <tr>
                            <td class="px-4 fw-semibold">{{ $detail->device->name }}</td>
                            <td><code style="font-size:.8rem;">{{ $detail->device->code }}</code></td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $detail->quantity }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Thông tin trả (nếu đã trả) --}}
        @if($borrowRequest->returnRecord)
        <div class="card mt-3">
            <div class="card-header py-3 px-4">
                <i class="bi bi-arrow-return-left text-secondary me-2"></i>Thông tin trả
            </div>
            <div class="card-body p-4">
                <dl class="row mb-0" style="font-size:.875rem;">
                    <dt class="col-5 text-muted">Ngày trả thực tế:</dt>
                    <dd class="col-7">{{ $borrowRequest->returnRecord->return_date->format('d/m/Y') }}</dd>
                    <dt class="col-5 text-muted">Người trả:</dt>
                    <dd class="col-7">{{ $borrowRequest->returnRecord->returner->name ?? '—' }}</dd>
                    @if($borrowRequest->returnRecord->note)
                    <dt class="col-5 text-muted">Ghi chú:</dt>
                    <dd class="col-7">{{ $borrowRequest->returnRecord->note }}</dd>
                    @endif
                </dl>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- MODAL TRẢ THIẾT BỊ DÙNG CHUNG --}}
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
              <div class="mb-4" id="returnQuantityContainer">
                  <label class="form-label fw-bold text-secondary mb-1" style="font-size: .85rem;">SỐ LƯỢNG TRẢ <span class="text-danger">*</span></label>
                  <input type="number" name="return_qty" id="returnQuantity" class="form-control fw-bold fs-5 text-success border-0 shadow-sm" min="1">
              </div>

              <div class="mb-4">
                  <label class="form-label fw-bold text-secondary mb-2" style="font-size: .85rem;">TÌNH TRẠNG LÚC TRẢ</label>
                  <div class="text-muted mb-2" style="font-size: 0.8rem;"><i class="bi bi-info-circle me-1"></i> Tình trạng này sẽ được áp dụng cho toàn bộ thiết bị trong phiếu.</div>
                  <div class="d-flex flex-column gap-2 bg-white p-3 rounded border-0 shadow-sm">
                      <div class="form-check">
                          <input class="form-check-input" type="radio" name="condition" value="normal" id="condNormal" checked>
                          <label class="form-check-label fw-semibold text-success ms-1" for="condNormal">
                              Bình thường
                          </label>
                      </div>
                      <div class="form-check mt-2">
                          <input class="form-check-input" type="radio" name="condition" value="damaged" id="condDamaged">
                          <label class="form-check-label fw-semibold text-warning ms-1" for="condDamaged">
                              Hỏng
                          </label>
                      </div>
                      <div class="form-check mt-2">
                          <input class="form-check-input" type="radio" name="condition" value="lost" id="condLost">
                          <label class="form-check-label fw-semibold text-danger ms-1" for="condLost">
                              Mất
                          </label>
                      </div>
                  </div>
              </div>

              <div class="mb-2">
                  <label class="form-label fw-bold text-secondary mb-1" style="font-size: .85rem;">GHI CHÚ (NẾU CÓ)</label>
                  <textarea name="note" class="form-control border-0 shadow-sm" rows="3" placeholder="Ghi chú chi tiết nếu thiết bị hỏng/mất..."></textarea>
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
    function openReturnDetailModal(reqId, deviceName, maxQty) {
        document.getElementById('returnDeviceName').innerText = deviceName;
        const qtyContainer = document.getElementById('returnQuantityContainer');
        const qtyInput = document.getElementById('returnQuantity');
        
        if (maxQty === -1) { 
            qtyContainer.style.display = 'none';
            qtyInput.removeAttribute('required');
            qtyInput.value = '';
        } else {
            qtyContainer.style.display = 'block';
            qtyInput.setAttribute('required', 'required');
            qtyInput.max = maxQty;
            qtyInput.value = maxQty;
        }

        document.getElementById('condNormal').checked = true;
        document.querySelector('textarea[name="note"]').value = '';
        
        document.getElementById('returnForm').action = "{{ url('borrow-requests') }}/" + reqId + "/return";

        const modal = new bootstrap.Modal(document.getElementById('confirmReturnModal'));
        if (typeof triggerEvent === 'function') triggerEvent(document, 'click');
        modal.show();
    }
</script>
@endpush
