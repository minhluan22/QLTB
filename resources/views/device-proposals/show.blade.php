@extends('layouts.app')
@section('title', 'Chi tiết đề xuất thiết bị')
@section('page-title', 'Chi tiết đề xuất thiết bị')

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Quay lại
    </a>
    
    <span class="badge bg-{{ $deviceProposal->statusColor() }} fs-6 px-3 py-2">
        <i class="bi bi-info-circle me-1"></i>{{ $deviceProposal->statusLabel() }}
    </span>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">

        {{-- Bị từ chối --}}
        @if($deviceProposal->status === 'rejected')
        <div class="alert alert-danger d-flex align-items-start shadow-sm border-0 mb-4">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3 mt-1"></i>
            <div>
                <h6 class="fw-bold mb-1">Đề xuất đã bị từ chối!</h6>
                <p class="mb-0" style="font-size: .9rem;">
                    <strong>Lý do:</strong> {{ $deviceProposal->reject_reason }}
                </p>
                <div class="text-muted mt-2" style="font-size:.75rem;">
                    Bởi: {{ $deviceProposal->approvedBy->name ?? 'Người quản trị' }} lúc {{ $deviceProposal->approved_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
        @endif

        {{-- Đã duyệt --}}
        @if($deviceProposal->status === 'approved')
        <div class="alert alert-success d-flex align-items-start shadow-sm border-0 mb-4">
            <i class="bi bi-check-circle-fill fs-4 me-3 mt-1"></i>
            <div>
                <h6 class="fw-bold mb-1">Đề xuất đã được duyệt!</h6>
                <p class="mb-0" style="font-size: .9rem;">
                    Thiết bị đã được phân bổ vào danh sách dùng chung của trường.
                </p>
                <div class="text-muted mt-2" style="font-size:.75rem;">
                    Bởi: {{ $deviceProposal->approvedBy->name ?? 'Người quản trị' }} lúc {{ $deviceProposal->approved_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header py-3 px-4 bg-white border-bottom-0 pt-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary fw-bold">{{ $deviceProposal->device_name }}</h5>
                    <span class="badge bg-light text-dark border fs-6">S.Lượng: {{ $deviceProposal->quantity }}</span>
                </div>
            </div>
            <div class="card-body p-4 pt-2">
                <div class="row g-4">
                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.8rem; font-weight:600; text-transform:uppercase;">Giáo viên đề xuất</div>
                        <div class="fw-semibold mt-1">{{ $deviceProposal->user->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.8rem; font-weight:600; text-transform:uppercase;">Ngày gửi</div>
                        <div class="fw-semibold mt-1">{{ $deviceProposal->created_at->format('d/m/Y H:i') }}</div>
                    </div>

                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.8rem; font-weight:600; text-transform:uppercase;">Loại thiết bị</div>
                        <div class="mt-1">{{ $deviceProposal->category }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted" style="font-size:.8rem; font-weight:600; text-transform:uppercase;">Phục vụ môn</div>
                        <div class="mt-1">{{ $deviceProposal->subject ?? '—' }}</div>
                    </div>

                    <div class="col-12">
                        <div class="text-muted" style="font-size:.8rem; font-weight:600; text-transform:uppercase;">Mục đích sử dụng</div>
                        <div class="mt-1 p-3 bg-light rounded" style="font-size:.9rem; line-height:1.6;">{{ $deviceProposal->purpose }}</div>
                    </div>

                    @if($deviceProposal->description)
                    <div class="col-12">
                        <div class="text-muted" style="font-size:.8rem; font-weight:600; text-transform:uppercase;">Mô tả / Thông số</div>
                        <div class="mt-1" style="font-size:.9rem; white-space:pre-line;">{{ $deviceProposal->description }}</div>
                    </div>
                    @endif

                    @if($deviceProposal->note)
                    <div class="col-12">
                        <div class="text-muted" style="font-size:.8rem; font-weight:600; text-transform:uppercase;">Ghi chú thêm</div>
                        <div class="mt-1" style="font-size:.9rem; white-space:pre-line;">{{ $deviceProposal->note }}</div>
                    </div>
                    @endif
                </div>

                {{-- Nút duyệt cho Admin nếu đang pending --}}
                @if(auth()->user()->isAdmin() && $deviceProposal->isPending())
                    <hr class="my-4">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-outline-danger px-4" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-lg me-1"></i>Từ chối
                        </button>
                        <button type="button" class="btn btn-success px-4" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i class="bi bi-check-lg me-1"></i>Duyệt đề xuất
                        </button>
                    </div>

                    {{-- APPROVE MODAL --}}
                    <div class="modal fade" id="approveModal" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('device-proposals.approve', $deviceProposal) }}" method="POST">
                                @csrf @method('PATCH')
                                <div class="modal-content">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Duyệt đề xuất thiết bị</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="mb-3">Vui lòng cung cấp Mã thiết bị và Đơn vị tính để đưa vào kho hệ thống.</p>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Gán Mã Thiết Bị mới <span class="text-danger">*</span></label>
                                            <input type="text" name="code" class="form-control" required value="DX-{{ str_pad($deviceProposal->id, 4, '0', STR_PAD_LEFT) }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Đơn vị tính <span class="text-danger">*</span></label>
                                            <input type="text" name="unit" class="form-control" required value="cái">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                        <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Hoàn tất duyệt</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- REJECT MODAL --}}
                    <div class="modal fade" id="rejectModal" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('device-proposals.reject', $deviceProposal) }}" method="POST">
                                @csrf @method('PATCH')
                                <div class="modal-content">
                                    <div class="modal-header border-0 pb-0">
                                        <h5 class="modal-title text-danger"><i class="bi bi-x-circle me-2"></i>Từ chối đề xuất</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Lý do từ chối <span class="text-danger">*</span></label>
                                            <textarea name="reject_reason" required class="form-control" rows="3" placeholder="Nhập lý do để giáo viên biết..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 pt-0 pb-3">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                        <button type="submit" class="btn btn-danger px-4">Từ chối</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
