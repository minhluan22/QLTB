@extends('layouts.app')

@section('title', 'Danh sách thiết bị')
@section('page-title', 'Quản lý thiết bị')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="font-size:1.25rem;">Danh sách thiết bị</h2>
            <p class="text-muted mb-0" style="font-size:.85rem;">Quản lý toàn bộ thiết bị trong hệ thống</p>
        </div>
        @if(auth()->user()->isAdmin())
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="bi bi-file-earmark-excel me-1"></i>Xuất Excel
                </button>
                <a href="{{ route('devices.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Thêm thiết bị
                </a>
            </div>
        @endif
    </div>

    {{-- Filter --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('devices.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0"
                            placeholder="Tên, mã thiết bị, môn học..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <input type="text" name="subject" list="subjectList" class="form-control" placeholder="-- Môn học --" value="{{ request('subject') }}">
                    <datalist id="subjectList">
                        @foreach($subjects as $s)
                            <option value="{{ $s }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="col-6 col-md-2">
                    <input type="text" name="subject_group" list="groupList" class="form-control" placeholder="-- Tổ chuyên môn --" value="{{ request('subject_group') }}">
                    <datalist id="groupList">
                        @foreach($subjectGroups as $group)
                            <option value="{{ $group }}"></option>
                        @endforeach
                    </datalist>
                </div>

                <div class="col-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">Tìm kiếm</button>
                    <a href="{{ route('devices.index') }}" class="btn btn-outline-secondary">Bỏ tìm kiếm</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body p-0">
            @if($devices->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    <div>Không tìm thấy thiết bị nào.</div>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('devices.create') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-plus"></i> Thêm thiết bị đầu tiên
                        </a>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Mã TB</th>
                                <th>Tên thiết bị</th>
                                <th class="d-none d-lg-table-cell">Môn học</th>
                                <th class="d-none d-lg-table-cell">Tổ</th>
                                <th class="d-none d-xl-table-cell text-end">Đơn giá</th>
                                <th class="text-center">Tổng SL</th>
                                <th class="d-none d-xl-table-cell text-end text-success">Thành tiền</th>
                                <th class="text-center text-warning">Hỏng</th>
                                <th class="text-center text-danger">Mất</th>
                                <th class="text-center text-primary">Đang mượn</th>
                                <th class="text-center text-success">Còn lại</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($devices as $device)
                                <tr>
                                    <td class="px-4">
                                        <code
                                            style="font-size:.78rem;background:#f1f5f9;padding:2px 8px;border-radius:4px;">{{ $device->code }}</code>
                                    </td>
                                    <td>
                                        <div class="fw-semibold" style="font-size:.9rem;">{{ $device->name }}</div>
                                        @if($device->specification)
                                            <div class="text-muted" style="font-size:.75rem;">{{ $device->specification }}</div>
                                        @endif
                                    </td>
                                    <td class="d-none d-lg-table-cell text-muted" style="font-size:.85rem;">
                                        {{ $device->subject ?? '—' }}
                                    </td>
                                    <td class="d-none d-lg-table-cell text-muted" style="font-size:.85rem;">
                                        {{ $device->subject_group ?? '—' }}
                                    </td>
                                    <td class="d-none d-xl-table-cell text-end" style="font-size:.85rem;">
                                        @if($device->unit_price)
                                            {{ number_format($device->unit_price, 0, ',', '.') }} ₫
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-semibold">{{ $device->quantity }}</td>
                                    <td class="d-none d-xl-table-cell text-end fw-semibold text-success" style="font-size:.85rem;">
                                        @if($device->unit_price)
                                            {{ number_format($device->quantity * $device->unit_price, 0, ',', '.') }} ₫
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="{{ $device->damaged_qty > 0 ? 'text-warning fw-semibold' : 'text-muted' }}">
                                            {{ $device->damaged_qty }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="{{ $device->lost_qty > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                            {{ $device->lost_qty }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="{{ $device->borrowedQty() > 0 ? 'text-primary fw-semibold' : 'text-muted' }}">
                                            {{ $device->borrowedQty() }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold {{ $device->available_qty == 0 ? 'text-danger' : 'text-success' }}">
                                            {{ $device->available_qty }}
                                        </span>
                                        @if($device->unit)
                                            <span class="text-muted" style="font-size:.72rem;"> {{ $device->unit }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="{{ route('devices.show', $device) }}" class="btn btn-sm btn-outline-info"
                                                title="Xem chi tiết">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if(auth()->user()->isAdmin())
                                                <a href="{{ route('devices.edit', $device) }}" class="btn btn-sm btn-outline-warning"
                                                    title="Chỉnh sửa">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" action="{{ route('devices.destroy', $device) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Xoá" onclick="confirmDelete(event, '{{ addslashes($device->name) }}')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($devices->total() > 0)
                    <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top bg-light-subtle">
                        <div class="text-muted" style="font-size:.8rem;">
                            Hiển thị <strong>{{ $devices->firstItem() ?? 0 }}</strong> – <strong>{{ $devices->lastItem() ?? 0 }}</strong> 
                            trên tổng số <strong>{{ $devices->total() }}</strong> thiết bị
                        </div>
                        <div>
                            {{ $devices->withQueryString()->links() }}
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Modal Xuất Excel --}}
    <div class="modal fade" id="exportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-excel text-success me-2"></i>Xuất Excel Tổng Hợp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="GET" action="{{ route('devices.export') }}">
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Tệp Excel tải xuống (<b>thiet-bi-tong-hop.xlsx</b>) sẽ bao gồm 2 Sheet:
                        <br>- <b>Tổng hợp thiết bị</b>
                        <br>- <b>Chi tiết nhập kho</b>
                        </p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lọc theo Tổ chuyên môn (Tùy chọn)</label>
                            <select name="subject_group" class="form-select">
                                <option value="">-- Tất cả các tổ (Bỏ trống) --</option>
                                @php
                                    $groups = \App\Models\Device::distinct()->pluck('subject_group')->filter()->sort()->values();
                                @endphp
                                @foreach($groups as $g)
                                    <option value="{{ $g }}">{{ $g }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lọc theo Thiết bị (Tùy chọn)</label>
                            <input type="text" id="deviceExportSearch" class="form-control form-control-sm mb-2" placeholder="Tìm tên thiết bị...">
                            <div class="border rounded p-2 bg-white" style="max-height: 200px; overflow-y: auto;" id="deviceCheckboxList">
                                @php
                                    $allDevices = \App\Models\Device::orderBy('name')->get();
                                @endphp
                                @foreach($allDevices as $d)
                                    <div class="form-check device-item">
                                        <input class="form-check-input" type="checkbox" name="devices[]" value="{{ $d->id }}" id="expDevice_{{ $d->id }}">
                                        <label class="form-check-label" for="expDevice_{{ $d->id }}">
                                            {{ $d->name }} ({{ $d->code }})
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle"></i> Có thể dùng thanh tìm kiếm bên trên và tick chọn 1 hay nhiều thiết bị.</small>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Tháng nhập kho</label>
                                <select name="import_month" class="form-select">
                                    <option value="">-- Tất cả --</option>
                                    @for($i=1; $i<=12; $i++)
                                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">Tháng {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Năm nhập kho</label>
                                <select name="import_year" class="form-select">
                                    <option value="">-- Tất cả --</option>
                                    @for($y=date('Y')+1; $y>=2020; $y--)
                                        <option value="{{ $y }}">Năm {{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-download me-1"></i>Tải xuống</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    document.getElementById('deviceExportSearch')?.addEventListener('input', function() {
        let filter = this.value.toLowerCase();
        let labels = document.querySelectorAll('#deviceCheckboxList .device-item');
        labels.forEach(label => {
            if (label.innerText.toLowerCase().includes(filter)) {
                label.style.display = '';
            } else {
                label.style.display = 'none';
            }
        });
    });
</script>
@endpush