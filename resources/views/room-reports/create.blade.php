@extends('layouts.app')

@section('title', 'Tạo báo cáo phòng')
@section('page-title', 'Tạo báo cáo phòng mới')

@section('content')
<div class="mb-4">
    <a href="{{ route('room-reports.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Quay lại
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">

        {{-- HEADER PHÒNG --}}
        <div class="card mb-4 border-0"
             style="background: linear-gradient(135deg,#1a73e8,#0d47a1); color: #fff; border-radius: 16px;">
            <div class="card-body py-4 px-4 d-flex align-items-center gap-3">
                <div style="width:56px;height:56px;background:rgba(255,255,255,.15);border-radius:14px;
                            display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-building fs-3"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">{{ $roomName }}</h5>
                    <div style="font-size:.85rem;opacity:.85;">
                        Báo cáo tình trạng phòng — {{ now()->format('d/m/Y') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header py-3 px-4">
                <i class="bi bi-clipboard2-plus text-primary me-2"></i>
                <span class="fw-semibold">Thông tin báo cáo</span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('room-reports.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:.875rem;">
                            Ngày báo cáo <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="report_date"
                               class="form-control @error('report_date') is-invalid @enderror"
                               value="{{ old('report_date', date('Y-m-d')) }}">
                        @error('report_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:.875rem;">
                            Tình trạng thiết bị trong phòng <span class="text-danger">*</span>
                        </label>
                        <textarea name="device_condition" rows="4"
                                  class="form-control @error('device_condition') is-invalid @enderror"
                                  placeholder="Mô tả tình trạng tổng thể của các thiết bị trong phòng (đèn chiếu, bảng, máy tính, dụng cụ thí nghiệm...)">{{ old('device_condition') }}</textarea>
                        @error('device_condition')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="text-muted mt-1" style="font-size:.78rem;">
                            <i class="bi bi-info-circle me-1"></i>Ít nhất 10 ký tự
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:.875rem;">
                            Vấn đề phát sinh <span class="text-muted fw-normal">(nếu có)</span>
                        </label>
                        <textarea name="issues" rows="3"
                                  class="form-control @error('issues') is-invalid @enderror"
                                  placeholder="Liệt kê các thiết bị bị hỏng, mất, hoặc cần bảo trì...">{{ old('issues') }}</textarea>
                        @error('issues')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:.875rem;">
                            Hành động đã thực hiện <span class="text-muted fw-normal">(nếu có)</span>
                        </label>
                        <textarea name="actions_taken" rows="3"
                                  class="form-control @error('actions_taken') is-invalid @enderror"
                                  placeholder="Các bước đã xử lý: đã báo cáo hỏng, đã yêu cầu bảo trì, đã tạm thay thế...">{{ old('actions_taken') }}</textarea>
                        @error('actions_taken')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info d-flex gap-2 align-items-start" style="font-size:.85rem;">
                        <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
                        <div>Sau khi gửi, báo cáo sẽ được chuyển đến Admin xem xét. Bạn sẽ nhận phản hồi qua hệ thống.</div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('room-reports.index') }}" class="btn btn-outline-secondary">Hủy</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i>Gửi báo cáo
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
