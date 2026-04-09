@extends('layouts.app')
@section('title', 'Thiết bị phòng: ' . $room->name)
@section('page-title', 'Thiết bị trong phòng')

@section('content')

    {{-- HEADER --}}
    <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h5 class="fw-bold mb-1">{{ $room->name }}</h5>
            <div class="text-muted" style="font-size:.85rem;">Quản lý thiết bị trong phòng thực hành</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('my-room') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-house me-1"></i>Phòng của tôi
            </a>
            <a href="{{ route('room-devices.status') }}" class="btn btn-warning btn-sm text-dark">
                <i class="bi bi-clipboard2-check me-1"></i>Cập nhật tình trạng
            </a>
            <a href="{{ route('room-devices.export', request()->all()) }}" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i>Xuất Excel
            </a>
            <a href="{{ route('room-devices.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Thêm thiết bị
            </a>
        </div>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-3 px-4">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <label class="form-label mb-1 fw-semibold text-secondary" style="font-size:.8rem;">Tìm kiếm</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0"
                            placeholder="Nhập tên thiết bị bạn muốn tìm..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label mb-1 fw-semibold text-secondary" style="font-size:.8rem;">Tình trạng</label>
                    <select name="condition" class="form-select form-select-sm">
                        <option value="">-- Tất cả tình trạng --</option>
                        <option value="broken" {{ request('condition') == 'broken' ? 'selected' : '' }}>⚠️ Thiết bị bị hỏng
                        </option>
                        <option value="consumed" {{ request('condition') == 'consumed' ? 'selected' : '' }}>🧪 Thiết bị tiêu
                            hao</option>
                        <option value="lost" {{ request('condition') == 'lost' ? 'selected' : '' }}>🔍 Thiết bị bị mất
                        </option>
                        <option value="out_of_stock" {{ request('condition') == 'out_of_stock' ? 'selected' : '' }}>❌ Hết hàng
                            / Đang thiếu</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-search me-1"></i>Tìm kiếm
                    </button>
                    <a href="{{ route('room-devices.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                        Bỏ tìm kiếm
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- BẢNG --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Tên thiết bị</th>
                        <th>ĐVT</th>
                        <th class="text-center">Tổng SL</th>
                        <th class="text-center text-danger">Hỏng</th>
                        <th class="text-center text-warning">Tiêu hao</th>
                        <th class="text-center">Mất</th>
                        <th class="text-center text-success">Còn dùng</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $dev)
                        <tr>
                            <td class="text-muted" style="font-size:.8rem;">#{{ $dev->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $dev->name }}</div>
                                @if($dev->note)
                                    <div class="text-muted" style="font-size:.75rem;">{{ Str::limit($dev->note, 50) }}</div>
                                @endif
                            </td>
                            <td class="text-muted">{{ $dev->unit }}</td>
                            <td class="text-center fw-bold">{{ $dev->quantity }}</td>
                            <td class="text-center">
                                @if($dev->broken_qty > 0)
                                    <span class="badge bg-danger">{{ $dev->broken_qty }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($dev->consumed_qty > 0)
                                    <span class="badge bg-warning text-dark">{{ $dev->consumed_qty }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(($dev->lost_qty ?? 0) > 0)
                                    <span class="badge bg-secondary">{{ $dev->lost_qty }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $dev->statusColor() }} fs-6 px-3">{{ $dev->availableQty() }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('room-devices.edit', $dev) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('room-devices.destroy', $dev) }}" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirmDelete(event, '{{ $dev->name }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block opacity-25 mb-2"></i>
                                @if(request('search'))
                                    Không tìm thấy thiết bị nào khớp với "<strong>{{ request('search') }}</strong>".
                                @else
                                    Chưa có thiết bị. <a href="{{ route('room-devices.create') }}">Thêm thiết bị đầu tiên</a>.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                {{-- TỔNG KẾT CUỐI BẢNG --}}
                @if($devices->isNotEmpty())
                    <tfoot class="table-light fw-semibold">
                        {{-- Tổng trang hiện tại --}}
                        <tr class="text-muted" style="font-size:.85rem;">
                            <td colspan="3" class="text-end">Tổng cộng (Trang hiện tại):</td>
                            <td class="text-center">{{ $devices->sum('quantity') }}</td>
                            <td class="text-center">{{ $devices->sum('broken_qty') }}</td>
                            <td class="text-center">{{ $devices->sum('consumed_qty') }}</td>
                            <td class="text-center">{{ $devices->sum('lost_qty') }}</td>
                            <td class="text-center">{{ $devices->sum(fn($d) => $d->availableQty()) }}</td>
                            <td></td>
                        </tr>
                        {{-- Tổng hệ thống --}}
                        <tr class="table-primary-subtle text-primary-emphasis">
                            <td colspan="3" class="text-end">TỔNG CỘNG HỆ THỐNG:</td>
                            <td class="text-center">{{ $totalStats['quantity'] }}</td>
                            <td class="text-center">{{ $totalStats['broken'] }}</td>
                            <td class="text-center">{{ $totalStats['consumed'] }}</td>
                            <td class="text-center">{{ $totalStats['lost'] }}</td>
                            <td class="text-center">{{ $totalStats['available'] }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        @if($devices->hasPages())
            <div class="card-footer bg-white px-4 py-3">{{ $devices->links() }}</div>
        @endif
    </div>

@endsection