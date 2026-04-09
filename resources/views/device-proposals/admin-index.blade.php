@extends('layouts.app')
@section('title', 'Duyệt thiết bị tự làm của giáo viên')
@section('page-title', 'Duyệt thiết bị tự làm của giáo viên')

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- BỘ LỌC --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('device-proposals.admin-index') }}">
                <div class="row g-3">
                    {{-- Tìm kiếm văn bản --}}
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold text-secondary small mb-1">TÌM KIẾM</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                placeholder="Tên giáo viên, thiết bị, môn học..." 
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Trạng thái --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold text-secondary small mb-1">TRẠNG THÁI</label>
                        <select name="status" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>🕒 Chờ duyệt</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>✅ Đã duyệt</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>❌ Từ chối</option>
                        </select>
                    </div>

                    {{-- Giáo viên --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold text-secondary small mb-1">GIÁO VIÊN</label>
                        <select name="user_id" class="form-select">
                            <option value="">Tất cả giáo viên</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ request('user_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Thời gian --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold text-secondary small mb-1">THÁNG</label>
                        <select name="month" class="form-select">
                            <option value="">Tất cả tháng</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ request('month') == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>Tháng {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold text-secondary small mb-1">NĂM</label>
                        <select name="year" class="form-select">
                            <option value="">Tất cả năm</option>
                            @for($y = date('Y') + 1; $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Thao tác --}}
                    <div class="col-12 col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">Tìm kiếm</button>
                        <a href="{{ route('device-proposals.admin-index') }}" class="btn btn-outline-secondary px-3">Bỏ tìm kiếm</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#exportProposalModal">
            <i class="bi bi-file-earmark-excel me-1"></i>Xuất Excel
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-4">Giáo viên</th>
                        <th scope="col">Tên thiết bị</th>
                        <th scope="col" class="text-center">Số lượng</th>
                        <th scope="col">Loại / Môn</th>
                        <th scope="col" class="text-center">Trạng thái</th>
                        <th scope="col" class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proposals as $item)
                        <tr class="{{ $item->isPending() ? 'table-warning' : '' }}">
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $item->user->name ?? 'N/A' }}</div>
                                <div class="text-muted" style="font-size:.75rem;">{{ $item->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td class="fw-semibold">
                                {{ \Illuminate\Support\Str::limit($item->device_name, 40) }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $item->quantity }}</span>
                            </td>
                            <td>
                                <div style="font-size:.85rem;">{{ $item->category }}</div>
                                @if($item->subject)
                                    <div class="text-muted" style="font-size:.75rem;">Môn: {{ $item->subject }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $item->statusColor() }}">
                                    {{ $item->statusLabel() }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('device-proposals.show', $item) }}" class="btn btn-sm btn-outline-primary"
                                        title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @if($item->isPending())
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                            data-bs-target="#approveModal{{ $item->id }}" title="Duyệt">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                            data-bs-target="#rejectModal{{ $item->id }}" title="Từ chối">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        @if($item->isPending())
                            {{-- APPROVE MODAL --}}
                            <div class="modal fade" id="approveModal{{ $item->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="{{ route('device-proposals.approve', $item) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>DDuyệt thiết bị tự
                                                    làm của giáo viên</h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert alert-info py-2" style="font-size:.85rem;">
                                                    Khi duyệt, thiết bị này sẽ được tự động thêm vào danh sách quản lý của trường.
                                                    Vui lòng cấp mã thiết bị và đơn vị tính.
                                                </div>
                                                <p class="mb-2"><strong>Thiết bị:</strong> {{ $item->device_name }}</p>
                                                <p class="mb-3"><strong>Số lượng:</strong> {{ $item->quantity }}</p>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Gán Mã Thiết Bị mới <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" name="code" class="form-control" required
                                                        value="DX-{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}"
                                                        placeholder="VD: TB-001">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Đơn vị tính <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" name="unit" class="form-control" required value="cái"
                                                        placeholder="cái, bộ, chiếc, lọ...">
                                                </div>
                                            </div>
                                            <div class="modal-footer pb-2 pt-1 border-0">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                                <button type="submit" class="btn btn-success"><i
                                                        class="bi bi-check-lg me-1"></i>Duyệt & Lưu thiết bị</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- REJECT MODAL --}}
                            <div class="modal fade" id="rejectModal{{ $item->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="{{ route('device-proposals.reject', $item) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <div class="modal-content">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title text-danger"><i class="bi bi-x-circle me-2"></i>Từ chối đề
                                                    xuất</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="mb-4">Bạn đang từ chối đề xuất thiết bị
                                                    <strong>{{ $item->device_name }}</strong> của GV {{ $item->user->name ?? '' }}.
                                                </p>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Lý do từ chối <span
                                                            class="text-danger">*</span></label>
                                                    <textarea name="reject_reason" required class="form-control" rows="3"
                                                        placeholder="Nhập lý do để giáo viên biết..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer pb-3 pt-0 border-0">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                                <button type="submit" class="btn btn-danger px-4">Từ chối</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif

                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted mb-2"><i class="bi bi-inbox fs-1 opacity-50"></i></div>
                                <span class="text-muted">Không có đề xuất nào.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($proposals->total() > 0)
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top bg-light-subtle">
                <div class="text-muted" style="font-size:.8rem;">
                    Hiển thị <strong>{{ $proposals->firstItem() ?? 0 }}</strong> – <strong>{{ $proposals->lastItem() ?? 0 }}</strong> 
                    trên tổng số <strong>{{ $proposals->total() }}</strong> đề xuất
                </div>
                <div>
                    {{ $proposals->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- MODAL XUẤT EXCEL --}}
    <div class="modal fade" id="exportProposalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-excel text-success me-2"></i>Xuất Excel Đề Xuất
                        Thiết Bị</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="GET" action="{{ route('device-proposals.export') }}">
                    <div class="modal-body p-4 bg-light">
                        <p class="text-muted small mb-3">Tệp tải xuống sẽ tuân theo các bộ lọc bên dưới.</p>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted mb-1" style="font-size:.8rem;">TRẠNG
                                THÁI</label>
                            <select name="status" class="form-select border-0 shadow-sm">
                                <option value="">-- Tất cả trạng thái --</option>
                                <option value="pending">Chờ duyệt</option>
                                <option value="approved">Đã duyệt</option>
                                <option value="rejected">Từ chối</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted mb-1" style="font-size:.8rem;">GIÁO VIÊN</label>
                            <select name="user_id" class="form-select border-0 shadow-sm">
                                <option value="">-- Tất cả --</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label fw-semibold text-muted mb-1" style="font-size:.8rem;">THÁNG</label>
                                <select name="month" class="form-select border-0 shadow-sm">
                                    <option value="">-- Cả năm --</option>
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">Tháng {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold text-muted mb-1" style="font-size:.8rem;">NĂM</label>
                                <select name="year" class="form-select border-0 shadow-sm">
                                    <option value="">-- Tất cả --</option>
                                    @for($y = date('Y') + 1; $y >= 2020; $y--)
                                        <option value="{{ $y }}">Năm {{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-download me-1"></i>Tải báo
                            cáo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection