@extends('layouts.app')
@section('title', 'Thiết bị tự làm của tôi')
@section('page-title', 'Thiết bị tự làm của tôi')

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 text-secondary" style="font-size:1.1rem;">Danh sách các thiết bị tự làm</h5>
        <a href="{{ route('device-proposals.create') }}" class="btn btn-primary shadow-sm hover-elevate">
            <i class="bi bi-plus-lg me-1"></i>Tạo đề xuất mới
        </a>
    </div>

    {{-- SEARCH --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-3 px-4">
            <form method="GET" action="{{ route('device-proposals.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                            placeholder="Tìm thiết bị, môn học..." 
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>🕒 Chờ duyệt</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>✅ Đã duyệt</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>❌ Từ chối</option>
                    </select>
                </div>
                <div class="col-6 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Tìm kiếm</button>
                    <a href="{{ route('device-proposals.index') }}" class="btn btn-outline-secondary px-3">Bỏ tìm</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-4">Ngày tạo</th>
                        <th scope="col">Tên thiết bị</th>
                        <th scope="col" class="text-center">Số lượng</th>
                        <th scope="col">Loại / Môn</th>
                        <th scope="col" class="text-center">Trạng thái</th>
                        <th scope="col" class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proposals as $item)
                        <tr>
                            <td class="ps-4 text-muted" style="font-size:.85rem;">
                                {{ $item->created_at->format('d/m/Y') }}
                                <div style="font-size:.7rem;">{{ $item->created_at->format('H:i') }}</div>
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
                                <a href="{{ route('device-proposals.show', $item) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Xem
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted mb-2"><i class="bi bi-inbox fs-1 opacity-50"></i></div>
                                <span class="text-muted">Bạn chưa có đề xuất nào.</span>
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
@endsection