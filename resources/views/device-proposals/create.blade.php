@extends('layouts.app')
@section('title', 'Tạo đề xuất thiết bị')
@section('page-title', 'Thiết bị tự làm của giáo viên')

@section('content')
    <div class="mb-4">
        <a href="{{ route('device-proposals.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Quay lại
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header py-3 px-4 bg-white border-bottom-0 pt-4">
                    <h5><i class="bi bi-plus-circle text-primary me-2"></i>Nhập thông tin đề xuất</h5>
                    <p class="text-muted mb-0" style="font-size: .85rem;">Điền đầy đủ thông tin thiết bị bạn muốn nhà trường
                        trang bị thêm.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('device-proposals.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Tên thiết bị <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="device_name" class="form-control" value="{{ old('device_name') }}"
                                    required placeholder="VD: Kính hiển vi quang học...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Số lượng <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control" min="1"
                                    value="{{ old('quantity', 1) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Loại thiết bị</label>
                                <input list="category_options" name="category" class="form-control" value="{{ old('category') }}" placeholder="Chọn hoặc nhập loại...">
                                <datalist id="category_options">
                                    <option value="Dụng cụ thực hành">
                                    <option value="Hóa chất">
                                    <option value="Mô hình">
                                    <option value="Thiết bị điện tử">
                                    <option value="Phần mềm">
                                    <option value="Khác">
                                </datalist>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phục vụ môn học</label>
                                <input type="text" name="subject" class="form-control" value="{{ old('subject') }}"
                                    placeholder="VD: Vật lý, Hóa học...">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Mục đích sử dụng</label>
                                <textarea name="purpose" class="form-control" rows="2"
                                    placeholder="Lý do cần thiết bị này? (VD: Phục vụ bài thực hành số 3...)">{{ old('purpose') }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Mô tả cụ thể thông số (nếu có)</label>
                                <textarea name="description" class="form-control" rows="2"
                                    placeholder="Yêu cầu kỹ thuật, cấu hình, nhãn hiệu mong muốn...">{{ old('description') }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Ghi chú thêm</label>
                                <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('device-proposals.index') }}" class="btn btn-light border">Hủy</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-send me-1"></i>Gửi đề xuất
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection