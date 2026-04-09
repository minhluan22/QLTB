@extends('layouts.app')

@section('title', 'Trang Chủ')
@section('page-title', 'Trang Chủ')

@section('content')

{{-- ===== ADMIN DASHBOARD ===== --}}
@if(auth()->user()->isAdmin())

    {{-- Welcome & Quick Actions --}}
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e293b;">Xin chào, {{ auth()->user()->name }}! 👋</h4>
            <div class="text-muted" style="font-size: .85rem;">Dưới đây là tổng quan tình hình mượn trả thiết bị của trường.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('devices.create') }}" class="btn btn-primary shadow-sm" style="border-radius: 8px;">
                <i class="bi bi-plus-lg me-1"></i> Thêm Thiết Bị
            </a>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary bg-white shadow-sm" style="border-radius: 8px;">
                <i class="bi bi-people me-1"></i> Danh sách GV
            </a>
        </div>
    </div>

    {{-- Stats Cards Style --}}
    <style>
        .stat-card-modern {
            border-radius: 16px;
            padding: 24px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #fff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 20px -5px rgba(0,0,0,0.1);
        }
        .stat-card-modern:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -10px rgba(0,0,0,0.2);
        }
        .stat-card-modern .info-container {
            z-index: 2;
        }
        .stat-card-modern .value {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }
        .stat-card-modern .label {
            font-size: .85rem;
            font-weight: 600;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-card-modern .icon-wrapper {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(8px);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            z-index: 2;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .stat-card-modern .shape {
            position: absolute;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            z-index: 1;
        }
        .table-modern {
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        .table-modern tr {
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border-radius: 12px;
            transition: all 0.2s ease;
        }
        .table-modern tr:hover {
            transform: scale(1.005);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .table-modern td, .table-modern th {
            border: none;
            padding: 16px 20px;
            vertical-align: middle;
        }
        .table-modern th {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #64748b;
            background: none;
            box-shadow: none;
            padding-bottom: 8px;
        }
        .table-modern td:first-child, .table-modern th:first-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
        .table-modern td:last-child, .table-modern th:last-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; }
    </style>

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card-modern" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                <div class="shape" style="width: 150px; height: 150px; top: -50px; right: -50px;"></div>
                <div class="shape" style="width: 80px; height: 80px; bottom: -20px; left: -20px;"></div>
                <div class="info-container">
                    <div class="value">{{ $stats['total_devices'] }}</div>
                    <div class="label">Tổng thiết bị</div>
                </div>
                <div class="icon-wrapper"><i class="bi bi-pc-display"></i></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card-modern" style="background: linear-gradient(135deg, #10b981, #047857);">
                <div class="shape" style="width: 150px; height: 150px; top: -50px; right: -50px;"></div>
                <div class="shape" style="width: 80px; height: 80px; bottom: -20px; left: -20px;"></div>
                <div class="info-container">
                    <div class="value">{{ $stats['borrowing_requests'] }}</div>
                    <div class="label">Đang mượn</div>
                </div>
                <div class="icon-wrapper"><i class="bi bi-arrow-left-right"></i></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card-modern" style="background: linear-gradient(135deg, #f59e0b, #b45309);">
                <div class="shape" style="width: 150px; height: 150px; top: -50px; right: -50px;"></div>
                <div class="shape" style="width: 80px; height: 80px; bottom: -20px; left: -20px;"></div>
                <div class="info-container">
                    <div class="value">{{ $stats['overdue_requests'] }}</div>
                    <div class="label">Quá hạn</div>
                </div>
                <div class="icon-wrapper"><i class="bi bi-clock-history"></i></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card-modern" style="background: linear-gradient(135deg, #ef4444, #b91c1c);">
                <div class="shape" style="width: 150px; height: 150px; top: -50px; right: -50px;"></div>
                <div class="shape" style="width: 80px; height: 80px; bottom: -20px; left: -20px;"></div>
                <div class="info-container">
                    <div class="value">{{ $stats['total_damages'] }}</div>
                    <div class="label">Báo hỏng</div>
                </div>
                <div class="icon-wrapper"><i class="bi bi-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>

    {{-- Phiếu mượn gần đây --}}
    <div class="d-flex align-items-center justify-content-between mb-3 mt-4">
        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-journals text-primary me-2"></i>Phiếu mượn hoạt động gần đây</h5>
        <a href="{{ route('borrow-requests.index') }}" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3 fw-medium text-primary">Xem tất cả <i class="bi bi-arrow-right ms-1"></i></a>
    </div>

    @if($recentBorrows->isEmpty())
        <div class="card shadow-sm border-0" style="border-radius: 16px;">
            <div class="card-body text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; border-radius: 50%; background: #f1f5f9; color: #94a3b8;">
                    <i class="bi bi-inbox fs-1"></i>
                </div>
                <h5 class="fw-semibold text-dark">Chưa có hoạt động mượn trả nào</h5>
                <p class="text-muted mb-0">Các phiếu mượn thiết bị sẽ xuất hiện tại đây.</p>
            </div>
        </div>
    @else
        <div class="table-responsive" style="overflow-x: auto; padding-bottom: 20px;">
            <table class="table table-modern mb-0 w-100">
                <thead>
                    <tr>
                        <th style="min-width: 200px;">Giáo viên</th>
                        <th>Ngày lập</th>
                        <th>Thiết bị mượn</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentBorrows as $req)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                @if($req->user->avatar)
                                    <img src="{{ asset('storage/' . $req->user->avatar) }}" alt="Avatar" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                                @else
                                    <div class="d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, #1a73e8, #0d47a1); color: white; font-weight: bold; font-size: 1.1rem; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                                        {{ strtoupper(substr($req->user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: .95rem;">{{ $req->user->name }}</div>
                                    <div class="text-muted" style="font-size: .75rem;">
                                        @if($req->user->subject_group) {{ $req->user->subject_group }} @else {{ $req->user->email }} @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium text-dark" style="font-size: .9rem;">{{ $req->borrow_date->format('d/m/Y') }}</div>
                            <div class="text-muted" style="font-size: .75rem;">Dự kiến: {{ $req->expected_return_date->format('d/m/Y') }}</div>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($req->borrowDetails->take(2) as $d)
                                    <span class="badge bg-light text-dark border" style="font-size: .75rem; padding: 6px 10px; font-weight: 500;">
                                        <i class="bi bi-box text-primary me-1"></i>{{ $d->device->name }} (x{{ $d->quantity }})
                                    </span>
                                @endforeach
                                @if($req->borrowDetails->count() > 2)
                                    <span class="badge bg-light text-dark border" style="font-size: .75rem; padding: 6px 10px; font-weight: 500;">
                                        +{{ $req->borrowDetails->count()-2 }} nữa
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            {!! $req->statusBadge() !!}
                        </td>
                        <td class="text-end">
                            <a href="{{ route('borrow-requests.show', $req) }}" class="btn btn-sm btn-primary align-items-center d-inline-flex gap-1 shadow-sm" style="border-radius: 8px;">
                                Xem <i class="bi bi-chevron-right" style="font-size: .7rem;"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif


{{-- ===== TEACHER DASHBOARD ===== --}}
@else

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="stat-card" style="background:linear-gradient(135deg,#1a73e8,#1557b0)">
                <div class="value">{{ $stats['borrowing'] }}</div>
                <div class="label">Đang mượn</div>
                <i class="bi bi-laptop icon"></i>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card" style="background:linear-gradient(135deg,#ef4444,#dc2626)">
                <div class="value">{{ $stats['overdue'] }}</div>
                <div class="label">Quá hạn</div>
                <i class="bi bi-exclamation-triangle icon"></i>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card" style="background:linear-gradient(135deg,#10b981,#059669)">
                <div class="value">{{ $stats['returned'] }}</div>
                <div class="label">Đã trả</div>
                <i class="bi bi-check-circle icon"></i>
            </div>
        </div>
    </div>

    {{-- Nút nhanh --}}
    <div class="row g-3 mb-4">
        <div class="col-6">
            <a href="{{ route('borrow-requests.create') }}" class="card text-decoration-none p-3 d-flex flex-row align-items-center gap-3 h-100"
               style="border-left:4px solid #1a73e8 !important;">
                <div style="width:48px;height:48px;background:#eff6ff;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-plus-circle text-primary fs-4"></i>
                </div>
                <div>
                    <div class="fw-semibold" style="font-size:.9rem;">Tạo yêu cầu mượn</div>
                    <div class="text-muted" style="font-size:.78rem;">Gửi yêu cầu mới</div>
                </div>
            </a>
        </div>
        <div class="col-6">
            <a href="{{ route('devices.index') }}" class="card text-decoration-none p-3 d-flex flex-row align-items-center gap-3 h-100"
               style="border-left:4px solid #10b981 !important;">
                <div style="width:48px;height:48px;background:#f0fdf4;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-laptop text-success fs-4"></i>
                </div>
                <div>
                    <div class="fw-semibold" style="font-size:.9rem;">Xem thiết bị</div>
                    <div class="text-muted" style="font-size:.78rem;">Danh sách thiết bị</div>
                </div>
            </a>
        </div>
    </div>

    {{-- Alert nhắc trả thiết bị --}}
    @if(isset($actionableRequests) && $actionableRequests->isNotEmpty())
        <div class="card mb-4 border border-danger shadow-sm">
            <div class="card-header bg-danger text-white py-2 px-4">
                <i class="bi bi-bell-fill me-2"></i>Nhắc nhở: Các thiết bị đang mượn
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                @foreach($actionableRequests as $req)
                    @php 
                        $isOverdue = $req->status === 'overdue';
                        $textClass = $isOverdue ? 'text-danger fw-bold' : 'text-dark fw-semibold';
                        $badge = $isOverdue ? '<span class="badge bg-danger ms-2">Quá hạn</span>' : '<span class="badge bg-warning text-dark ms-2">Sắp đến hạn</span>';
                    @endphp
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="{{ $textClass }}">Hẹn trả: {{ $req->expected_return_date->format('d/m/Y') }} {!! $badge !!}</div>
                            <div class="text-muted mt-1" style="font-size:.85rem;">
                                @foreach($req->borrowDetails as $d)
                                    <span class="badge bg-light text-dark border">{{ $d->device->name }} x{{ $d->quantity }}</span>
                                @endforeach
                            </div>
                        </div>
                        <button type="button" class="btn btn-success btn-sm align-items-center d-flex gap-1" 
                                onclick="openReturnDetailModal({{ $req->id }}, 'Phiếu #{{ $req->id }}', {{ $req->borrowDetails->count() === 1 ? $req->borrowDetails->first()->quantity : -1 }})">
                            <i class="bi bi-arrow-return-left"></i> Trả đồ ngay
                        </button>
                    </li>
                @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Yêu cầu của tôi --}}
    <div class="card">
        <div class="card-header py-3 px-4 d-flex align-items-center justify-content-between">
            <span><i class="bi bi-list-ul text-primary me-2"></i>Yêu cầu mượn gần đây (5 mới nhất)</span>
            <a href="{{ route('borrow-requests.index') }}" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3 fw-medium text-primary">
                Xem tất cả <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body p-0">
            @if($myRequests->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>Bạn chưa có yêu cầu mượn nào
                    <div class="mt-2">
                        <a href="{{ route('borrow-requests.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus"></i> Tạo yêu cầu đầu tiên
                        </a>
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Mục đích</th>
                                <th>Ngày mượn</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($myRequests as $req)
                            <tr>
                                <td class="px-4" style="font-size:.875rem;">{{ Str::limit($req->purpose, 40) }}</td>
                                <td style="font-size:.85rem;">{{ $req->borrow_date->format('d/m/Y') }}</td>
                                <td>{!! $req->statusBadge() !!}</td>
                                <td>
                                    <a href="{{ route('borrow-requests.show', $req) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(in_array($req->status, ['borrowing', 'overdue']) && auth()->id() === $req->user_id)
                                        <button type="button" class="btn btn-sm btn-success text-white" title="Trả đồ"
                                                onclick="openReturnDetailModal({{ $req->id }}, 'Phiếu #{{ $req->id }}', {{ $req->borrowDetails->count() === 1 ? $req->borrowDetails->first()->quantity : -1 }})">
                                            <i class="bi bi-arrow-return-left"></i> Trả
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($myRequests->hasPages())
                <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                    <div class="text-muted" style="font-size:.8rem;">
                        Trang {{ $myRequests->currentPage() }} / {{ $myRequests->lastPage() }}
                        &nbsp;·&nbsp; Tổng {{ $myRequests->total() }} yêu cầu
                    </div>
                    {{ $myRequests->withQueryString()->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
@endif

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
