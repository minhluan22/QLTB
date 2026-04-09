@extends('layouts.app')

@section('title', $device->name)
@section('page-title', 'Chi tiết thiết bị')

@section('content')
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('devices.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="flex-1">
            <h2 class="fw-bold mb-0" style="font-size:1.2rem;">{{ $device->name }}</h2>
            <p class="text-muted mb-0" style="font-size:.8rem;">Mã: <code>{{ $device->code }}</code></p>
        </div>
        @if(auth()->user()->isAdmin())
            <div class="d-flex gap-2">
                <a href="{{ route('devices.edit', $device) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>Sửa
                </a>
            </div>
        @endif
    </div>

    @if($device->is_proposed)
        <div class="alert alert-info d-flex align-items-center mb-4 border-0 shadow-sm">
            <i class="bi bi-info-circle-fill fs-4 text-info me-3"></i>
            <div>
                <h6 class="mb-1 text-dark fw-bold">Thiết bị do Giáo viên tự làm</h6>
                <p class="mb-0 text-muted" style="font-size: .85rem;">
                    Thiết bị này được thêm vào kho thông qua tính năng tạo thiết bị tự làm của giáo viên và đã được Quản trị
                    viên phê duyệt. Do không thông qua quy trình nhập kho thông thường nên sẽ không có lịch sử giá nhập.
                </p>
            </div>
        </div>
    @endif

    <div class="row g-3">
        {{-- Thông tin thiết bị --}}
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header py-3 px-4">
                    <i class="bi bi-info-circle text-primary me-2"></i>Thông tin thiết bị
                </div>
                <div class="card-body p-4">
                    <dl class="row mb-0" style="font-size:.875rem;">
                        <dt class="col-5 text-muted">Mã:</dt>
                        <dd class="col-7"><code>{{ $device->code }}</code></dd>

                        <dt class="col-5 text-muted">Tên:</dt>
                        <dd class="col-7 fw-semibold">{{ $device->name }}</dd>

                        <dt class="col-5 text-muted">Danh mục:</dt>
                        <dd class="col-7">{{ $device->category ?? '—' }}</dd>

                        <dt class="col-5 text-muted">Môn học:</dt>
                        <dd class="col-7">{{ $device->subject ?? '—' }}</dd>

                        <dt class="col-5 text-muted">Tổ chuyên môn:</dt>
                        <dd class="col-7">{{ $device->subject_group ?? '—' }}</dd>

                        <dt class="col-5 text-muted">Đơn giá:</dt>
                        <dd class="col-7">
                            {{ $device->unit_price ? number_format($device->unit_price, 0, ',', '.') . ' ₫' : '—' }}</dd>

                        <dt class="col-5 text-muted">Tổng SL:</dt>
                        <dd class="col-7 fw-bold fs-5">{{ $device->quantity }} {{ $device->unit ?? 'Cái' }}</dd>

                        @php $totalVal = $device->totalValue(); @endphp
                        @if($totalVal > 0)
                            <dt class="col-5 text-muted mb-2">Thành tiền:</dt>
                            <dd class="col-7 fw-bold text-success mb-2">{{ number_format($totalVal, 0, ',', '.') }} ₫</dd>
                        @endif

                        @if($device->damaged_qty > 0)
                            <dt class="col-5 text-muted text-warning">Hỏng:</dt>
                            <dd class="col-7 fw-bold text-warning">{{ $device->damaged_qty }}</dd>
                        @endif

                        @if($device->lost_qty > 0)
                            <dt class="col-5 text-muted text-danger">Mất:</dt>
                            <dd class="col-7 fw-bold text-danger">{{ $device->lost_qty }}</dd>
                        @endif

                        <dt class="col-5 text-muted">Còn lại:</dt>
                        <dd class="col-7">
                            <span class="fw-bold fs-5 {{ $device->available_qty == 0 ? 'text-danger' : 'text-success' }}">
                                {{ $device->available_qty }}
                            </span>
                        </dd>
                    </dl>

                    @if($device->description)
                        <hr>
                        <div class="text-muted" style="font-size:.85rem;">
                            <strong>Mô tả:</strong><br>{{ $device->description }}
                        </div>
                    @endif

                    {{-- Progress bar số lượng --}}
                    <hr>
                    <div style="font-size:.8rem;" class="text-muted mb-2">Tỷ lệ phân bổ thiết bị</div>
                    @php
                        $total = $device->quantity > 0 ? $device->quantity : 1;
                        $borrowedQty = max(0, ($device->quantity - $device->damaged_qty - $device->lost_qty) - $device->available_qty);
                        $pctBorrowed = round($borrowedQty / $total * 100);
                        $pctDamaged = round($device->damaged_qty / $total * 100);
                        $pctLost = round($device->lost_qty / $total * 100);
                    @endphp
                    <div class="progress mb-2" style="height:12px;border-radius:6px;">
                        <div class="progress-bar bg-warning" style="width:{{ $pctBorrowed }}%"
                            title="Đang mượn: {{ $borrowedQty }}"></div>
                        <div class="progress-bar" style="width:{{ $pctDamaged }}%; background-color: #fd7e14;"
                            title="Hỏng: {{ $device->damaged_qty }}"></div>
                        <div class="progress-bar bg-danger" style="width:{{ $pctLost }}%"
                            title="Mất: {{ $device->lost_qty }}"></div>
                    </div>
                    <div class="d-flex flex-column gap-1 mt-2" style="font-size:.75rem; color:#64748b;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><span class="d-inline-block rounded-circle bg-warning me-1"
                                    style="width:8px;height:8px;"></span>Đang mượn: <b>{{ $borrowedQty }}</b></span>
                            <span>{{ $pctBorrowed }}%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span><span class="d-inline-block rounded-circle me-1"
                                    style="width:8px;height:8px; background-color: #fd7e14;"></span>Hỏng:
                                <b>{{ $device->damaged_qty }}</b></span>
                            <span>{{ $pctDamaged }}%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span><span class="d-inline-block rounded-circle bg-danger me-1"
                                    style="width:8px;height:8px;"></span>Mất: <b>{{ $device->lost_qty }}</b></span>
                            <span>{{ $pctLost }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            {{-- Lịch sử nhập kho --}}
            <div class="card mb-3">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-box-arrow-in-down text-success me-2"></i>Lịch sử nhập kho
                    </span>
                    @if(auth()->user()->isAdmin())
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                            data-bs-target="#importModal">
                            <i class="bi bi-plus-circle me-1"></i>Nhập thêm
                        </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($imports->isEmpty())
                        <div class="text-center py-4 text-muted" style="font-size:.85rem;">Chưa có lần nhập nào</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm mb-0" style="font-size:.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4">Ngày nhập</th>
                                        <th>Số lượng</th>
                                        <th>Nhà cung cấp</th>
                                        <th>Nhãn hiệu</th>
                                        <th>Xuất xứ</th>
                                        <th>Năm SX</th>
                                        <th>Giá</th>
                                        <th>Thành tiền</th>
                                        <th>Người nhập</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($imports as $import)
                                        <tr>
                                            <td class="px-4">{{ $import->import_date->format('d/m/Y') }}</td>
                                            <td><span class="badge bg-success">+{{ $import->quantity }}</span></td>
                                            <td>{{ $import->supplier ?? '—' }}</td>
                                            <td>{{ $import->brand ?? '—' }}</td>
                                            <td>{{ $import->country ?? '—' }}</td>
                                            <td>{{ $import->production_year ?? '—' }}</td>
                                            <td>{{ $import->price ? number_format($import->price, 0, ',', '.') . '₫' : '—' }}</td>
                                            <td class="fw-bold text-success">
                                                {{ $import->price ? number_format($import->price * $import->quantity, 0, ',', '.') . '₫' : '—' }}
                                            </td>
                                            <td>{{ $import->importer->name ?? '—' }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="d-inline-block text-truncate text-muted" style="max-width: 120px;"
                                                        title="{{ $import->note }}">
                                                        {{ $import->note ?? '—' }}
                                                    </span>
                                                    <button class="btn btn-sm btn-light border-0 btn-edit-import"
                                                        style="padding: 0.1rem 0.4rem;" data-id="{{ $import->id }}"
                                                        data-qty="{{ $import->quantity }}"
                                                        data-date="{{ $import->import_date->format('Y-m-d') }}"
                                                        data-price="{{ $import->price ?? '' }}"
                                                        data-supplier="{{ $import->supplier ?? '' }}"
                                                        data-brand="{{ $import->brand ?? '' }}"
                                                        data-country="{{ $import->country ?? '' }}"
                                                        data-year="{{ $import->production_year ?? '' }}"
                                                        data-note="{{ $import->note ?? '' }}" title="Xem chi tiết">
                                                        <i class="bi bi-eye text-primary"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($imports->hasPages())
                            <div class="mt-3 px-4 d-flex justify-content-end" style="font-size: .85rem;">
                                {{ $imports->appends(request()->except('import_page'))->links('pagination::bootstrap-5') }}
                            </div>
                        @endif

                    @endif
                </div>
            </div>

            {{-- Lịch sử mượn --}}
            <div class="card mb-3">
                <div class="card-header py-3 px-4">
                    <i class="bi bi-clipboard-check text-warning me-2"></i>Lịch sử mượn
                </div>
                <div class="card-body p-0">
                    @if($borrowDetails->isEmpty())
                        <div class="text-center py-4 text-muted" style="font-size:.85rem;">Chưa có ai mượn thiết bị này</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm mb-0" style="font-size:.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4">Người mượn</th>
                                        <th>Số lượng</th>
                                        <th>Ngày mượn</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($borrowDetails as $detail)
                                        <tr>
                                            <td class="px-4">{{ $detail->borrowRequest->user->name ?? '—' }}</td>
                                            <td>{{ $detail->quantity }}</td>
                                            <td>{{ $detail->borrowRequest->borrow_date->format('d/m/Y') }}</td>
                                            <td>{!! $detail->borrowRequest->statusBadge() !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($borrowDetails->hasPages())
                            <div class="card-footer bg-white border-top-0 px-4 py-3 d-flex justify-content-end"
                                style="font-size: .85rem;">
                                {{ $borrowDetails->appends(request()->except('borrow_page'))->links('pagination::bootstrap-5') }}
                            </div>
                        @endif

                    @endif
                </div>
            </div>

            {{-- Báo hỏng --}}
            @if($damages->count() > 0)
                <div class="card">
                    <div class="card-header py-3 px-4">
                        <i class="bi bi-exclamation-triangle text-danger me-2"></i>Lịch sử báo hỏng
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0" style="font-size:.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4">Ngày</th>
                                        <th>SL hỏng</th>
                                        <th>Mức độ</th>
                                        <th>Mô tả</th>
                                        <th>Người báo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($damages as $dmg)
                                        <tr>
                                            <td class="px-4">{{ $dmg->created_at->format('d/m/Y') }}</td>
                                            <td>{{ $dmg->quantity }}</td>
                                            <td>{!! $dmg->severityBadge() !!}</td>
                                            <td>{{ Str::limit($dmg->description, 40) }}</td>
                                            <td>{{ $dmg->reporter->name ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($damages->hasPages())
                            <div class="card-footer bg-white border-top-0 px-4 py-3 d-flex justify-content-end"
                                style="font-size: .85rem;">
                                {{ $damages->appends(request()->except('damage_page'))->links('pagination::bootstrap-5') }}
                            </div>
                        @endif

                    </div>
                </div>
            @endif
        </div>

        <!-- Modal Nhập thêm -->
        @if(auth()->user()->isAdmin())
            <div class="modal fade" id="importModal" tabindex="-1">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('devices.import', $device) }}" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Nhập thiết bị: <span class="text-primary">{{ $device->name }}</span>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-danger">Số lượng thêm *</label>
                                <div class="input-group">
                                    <input type="number" name="import_qty" class="form-control border-success" min="1" required
                                        placeholder="Nhập số lượng nhập đợt này...">
                                    <span
                                        class="input-group-text bg-success text-white border-success">{{ $device->unit ?? 'Cái' }}</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-danger">Ngày nhập *</label>
                                <input type="date" name="import_date" class="form-control" required value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Đơn giá nhập (VNĐ)</label>
                                <input type="number" name="price" class="form-control" min="0" step="1000"
                                    value="{{ $device->unit_price }}">
                                <small class="text-muted d-block mt-1">Mặc định lấy đơn giá hiện tại của thiết bị</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nhà cung cấp</label>
                                <input type="text" name="supplier" class="form-control"
                                    placeholder="Tên công ty / đơn vị cấp hàng...">
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Xuất xứ</label>
                                    <input type="text" name="country" class="form-control" list="country-list"
                                        placeholder="VD: Việt Nam">
                                    <datalist id="country-list">
                                        <option value="Việt Nam">
                                        <option value="Trung Quốc">
                                        <option value="Nhật Bản">
                                    </datalist>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Nhãn hiệu</label>
                                    <input type="text" name="brand" class="form-control" placeholder="Thiên Long...">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Năm SX</label>
                                    <input type="number" name="production_year" class="form-control" min="1900"
                                        max="{{ date('Y') + 1 }}" placeholder="VD: 2024">
                                </div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-semibold">Ghi chú đợt nhập</label>
                                <textarea name="note" class="form-control" rows="2"
                                    placeholder="Ví dụ: Nhập bổ sung đầu học kỳ 2..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light px-4 py-3">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-box-arrow-in-down me-2"></i>Lưu phiếu nhập
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Modal Chi tiết phiếu nhập -->
        <div class="modal fade" id="editImportModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Chi tiết đợt nhập thông tin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-primary">Số lượng nhập</label>
                            <div class="input-group">
                                <input type="number" id="edit_qty" class="form-control" readonly>
                                <span
                                    class="input-group-text bg-primary text-white border-primary">{{ $device->unit ?? 'Cái' }}</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-primary">Ngày nhập</label>
                            <input type="date" id="edit_date" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Đơn giá nhập (VNĐ)</label>
                            <input type="number" id="edit_price" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nhà cung cấp</label>
                            <input type="text" id="edit_supplier" class="form-control" readonly>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Xuất xứ</label>
                                <input type="text" id="edit_country" class="form-control" readonly>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Nhãn hiệu</label>
                                <input type="text" id="edit_brand" class="form-control" readonly>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Năm SX</label>
                                <input type="number" id="edit_year" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Ghi chú đợt nhập</label>
                            <textarea id="edit_note" class="form-control" rows="3" readonly></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light px-4 py-3">
                        <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const viewBtns = document.querySelectorAll('.btn-edit-import');

                if (viewBtns.length > 0) {
                    const viewModal = new bootstrap.Modal(document.getElementById('editImportModal'));

                    viewBtns.forEach(btn => {
                        btn.addEventListener('click', () => {
                            // Điền data vào Modal
                            document.getElementById('edit_qty').value = btn.getAttribute('data-qty');
                            document.getElementById('edit_date').value = btn.getAttribute('data-date');
                            document.getElementById('edit_price').value = btn.getAttribute('data-price');
                            document.getElementById('edit_supplier').value = btn.getAttribute('data-supplier');
                            document.getElementById('edit_brand').value = btn.getAttribute('data-brand');
                            document.getElementById('edit_country').value = btn.getAttribute('data-country');
                            document.getElementById('edit_year').value = btn.getAttribute('data-year');
                            document.getElementById('edit_note').value = btn.getAttribute('data-note');

                            viewModal.show();
                        });
                    });
                }
            });
        </script>
@endsection