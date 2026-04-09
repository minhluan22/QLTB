@extends('layouts.app')

@section('title', 'Danh sách sự cố thiết bị')
@section('page-title', 'Báo hỏng / Báo mất')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size:1.25rem;">Danh sách sự cố thiết bị</h2>
        <p class="text-muted mb-0" style="font-size:.85rem;">Theo dõi hỏng hóc và thất lạc thiết bị</p>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->isAdmin())
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
            <i class="bi bi-file-earmark-excel me-1"></i>Xuất Excel
        </button>
        @endif
        <a href="{{ route('damages.create') }}" class="btn btn-danger">
            <i class="bi bi-exclamation-triangle me-1"></i>Báo sự cố mới
        </a>
    </div>
</div>

<div class="card mb-4 shadow-sm border-0">
    <div class="card-body py-3 px-3">
        <form method="GET" action="{{ route('damages.index') }}">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-auto d-flex gap-2 align-items-center">
                    <span class="text-muted" style="font-size:.85rem;"><i class="bi bi-funnel me-1"></i>Lọc:</span>
                    <a href="{{ route('damages.index') }}"
                       class="btn btn-sm {{ !request('damage_type') ? 'btn-dark' : 'btn-outline-secondary' }}">
                        Tất cả
                    </a>
                    <a href="{{ route('damages.index', array_merge(request()->query(), ['damage_type'=>'hỏng'])) }}"
                       class="btn btn-sm {{ request('damage_type')==='hỏng' ? 'btn-warning text-dark' : 'btn-outline-warning' }}">
                        <i class="bi bi-tools me-1"></i>Hỏng
                    </a>
                    <a href="{{ route('damages.index', array_merge(request()->query(), ['damage_type'=>'mất'])) }}"
                       class="btn btn-sm {{ request('damage_type')==='mất' ? 'btn-danger' : 'btn-outline-danger' }}">
                        <i class="bi bi-x-circle me-1"></i>Mất
                    </a>
                    <input type="hidden" name="damage_type" value="{{ request('damage_type') }}">
                </div>
                
                @if(auth()->user()->isAdmin())
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Tìm thiết bị..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted border-end-0">Từ date</span>
                        <input type="date" name="from_date" class="form-control border-start-0" value="{{ request('from_date') }}">
                    </div>
                </div>
                <div class="col-6 col-md-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted border-end-0">Đến</span>
                        <input type="date" name="to_date" class="form-control border-start-0" value="{{ request('to_date') }}">
                    </div>
                </div>
                <div class="col-12 col-md-auto d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary px-3">Tìm kiếm</button>
                    <a href="{{ route('damages.index', ['damage_type' => request('damage_type')]) }}" class="btn btn-sm btn-light border text-muted px-3">Bỏ tìm kiếm</a>
                </div>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($damages->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                Không có sự cố nào. Tuyệt vời! 🎉
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">Thiết bị</th>
                        <th>Loại</th>
                        <th class="text-center">SL</th>
                        <th class="d-none d-md-table-cell">Ngày phát hiện</th>
                        <th class="d-none d-lg-table-cell">Nguyên nhân</th>
                        <th>Mức độ</th>
                        <th class="d-none d-xl-table-cell">Hướng xử lý</th>
                        <th>Người báo</th>
                        <th class="d-none d-md-table-cell">Ngày báo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($damages as $dmg)
                    <tr>
                        <td class="px-4">
                            <div class="fw-semibold" style="font-size:.875rem;">{{ $dmg->device->name ?? '—' }}</div>
                            <code style="font-size:.72rem;">{{ $dmg->device->code ?? '' }}</code>
                        </td>
                        <td>{!! $dmg->typeBadge() !!}</td>
                        <td class="text-center">
                            <span class="badge {{ $dmg->damage_type==='mất' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                {{ $dmg->quantity }}
                            </span>
                        </td>
                        <td class="d-none d-md-table-cell" style="font-size:.83rem;">
                            {{ $dmg->detected_date ? $dmg->detected_date->format('d/m/Y') : '—' }}
                        </td>
                        <td class="d-none d-lg-table-cell text-muted" style="font-size:.83rem;">
                            {{ $dmg->cause ?? '—' }}
                        </td>
                        <td>
                            @if($dmg->damage_type === 'hỏng')
                                {!! $dmg->severityBadge() !!}
                            @else
                                <span class="text-muted" style="font-size:.82rem;">—</span>
                            @endif
                        </td>
                        <td class="d-none d-xl-table-cell text-muted" style="font-size:.82rem;">
                            {{ $dmg->resolution ?? '—' }}
                        </td>
                        <td style="font-size:.85rem;">{{ $dmg->reporter->name ?? '—' }}</td>
                        <td class="d-none d-md-table-cell" style="font-size:.82rem;color:#64748b;">
                            {{ $dmg->created_at->format('d/m/Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($damages->total() > 0)
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top bg-light-subtle">
                <div class="text-muted" style="font-size:.8rem;">
                    Hiển thị <strong>{{ $damages->firstItem() ?? 0 }}</strong> – <strong>{{ $damages->lastItem() ?? 0 }}</strong> 
                    trên tổng số <strong>{{ $damages->total() }}</strong> bản ghi sự cố
                </div>
                <div>
                    {{ $damages->withQueryString()->links() }}
                </div>
            </div>
        @endif
        @endif
    </div>
</div>

@if(auth()->user()->isAdmin())
{{-- EXPORT MODAL --}}
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('damages.export') }}" method="GET" class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white border-bottom-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-excel me-2"></i>Xuất Excel Báo cáo sự cố</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary" style="font-size: .85rem;">LOẠI SỰ CỐ</label>
                    <select name="damage_type" class="form-select shadow-sm">
                        <option value="all">Tất cả (Hỏng & Mất)</option>
                        <option value="hỏng">Chỉ báo Hỏng</option>
                        <option value="mất">Chỉ báo Mất</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary d-flex justify-content-between" style="font-size: .85rem;">
                        <span>THIẾT BỊ (BỎ TRỐNG ĐỂ XUẤT TẤT CẢ)</span>
                    </label>
                    <div class="input-group input-group-sm mb-2 shadow-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="exportDeviceSearch" class="form-control border-start-0" placeholder="Tìm nhanh thiết bị...">
                    </div>
                    <div class="border rounded bg-white shadow-sm p-2" style="max-height: 200px; overflow-y: auto;">
                        @php
                            // Chỉ lấy các thiết bị ĐÃ TỪNG được báo sự cố
                            $damagedDeviceIds = \App\Models\Damage::pluck('device_id')->unique();
                            $devicesForExport = \App\Models\Device::whereIn('id', $damagedDeviceIds)->orderBy('name')->get();
                        @endphp
                        @foreach($devicesForExport as $device)
                        <label class="d-flex align-items-center gap-2 mb-2 device-item" style="cursor:pointer;" data-name="{{ strtolower($device->name . ' ' . $device->code) }}">
                            <input type="checkbox" name="device_ids[]" value="{{ $device->id }}" class="form-check-input mt-0">
                            <span style="font-size: .9rem;">{{ $device->name }} <code class="text-muted ms-1">{{ $device->code }}</code></span>
                        </label>
                        @endforeach
                        @if($devicesForExport->isEmpty())
                            <div class="text-muted text-center py-3" style="font-size: .85rem;">Chưa có thiết bị nào bị sự cố.</div>
                        @endif
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label fw-semibold text-secondary" style="font-size: .85rem;">TỪ NGÀY</label>
                        <input type="date" name="from_date" class="form-control shadow-sm">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold text-secondary" style="font-size: .85rem;">ĐẾN NGÀY</label>
                        <input type="date" name="to_date" class="form-control shadow-sm">
                    </div>
                </div>

            </div>
            <div class="modal-footer border-top-0 bg-light pb-4 px-4">
                <button type="button" class="btn btn-secondary px-4 shadow-sm" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-success px-4 shadow-sm">
                    <i class="bi bi-download me-2"></i>Tải xuống Excel
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    document.getElementById('exportDeviceSearch')?.addEventListener('input', function(e) {
        let keyword = e.target.value.toLowerCase().trim();
        let items = document.querySelectorAll('.device-item');
        items.forEach(item => {
            if (item.getAttribute('data-name').includes(keyword)) {
                item.style.setProperty('display', 'flex', 'important');
            } else {
                item.style.setProperty('display', 'none', 'important');
            }
        });
    });
</script>
@endif

@endsection
