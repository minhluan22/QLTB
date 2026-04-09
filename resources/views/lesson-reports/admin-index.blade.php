@extends('layouts.app')
@section('title', 'Tất cả báo cáo tiết')
@section('page-title', 'Báo cáo tiết thực hành — Tổng hợp')

@section('content')

    {{-- STAT CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#1a73e8,#0d47a1);">
                <div class="value">{{ $stats['total'] }}</div>
                <div class="label">Tổng báo cáo</div>
                <i class="bi bi-clipboard2 icon"></i>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#10b981,#065f46);">
                <div class="value">{{ $stats['sang'] }}</div>
                <div class="label">Tiết Sáng</div>
                <i class="bi bi-brightness-high icon"></i>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#0891b2,#164e63);">
                <div class="value">{{ $stats['chieu'] }}</div>
                <div class="label">Tiết Chiều</div>
                <i class="bi bi-moon-stars icon"></i>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#ef4444,#991b1b);">
                <div class="value">{{ $stats['has_issues'] }}</div>
                <div class="label">Có sự cố</div>
                <i class="bi bi-exclamation-triangle icon"></i>
            </div>
        </div>
    </div>

    {{-- BỘ LỌC --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('lesson-reports.admin-index') }}">
                <div class="row g-3">
                    {{-- Dòng 1: Tìm kiếm văn bản --}}
                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold text-secondary small mb-1">TÌM KIẾM</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                placeholder="Tên giáo viên, lớp học, môn học hoặc nội dung..." 
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Dòng 1: Buổi & Trạng thái --}}
                    <div class="col-6 col-lg-3">
                        <label class="form-label fw-semibold text-secondary small mb-1">BUỔI</label>
                        <select name="session" class="form-select">
                            <option value="">Tất cả buổi</option>
                            <option value="sang" {{ request('session') == 'sang' ? 'selected' : '' }}>☀️ Sáng</option>
                            <option value="chieu" {{ request('session') == 'chieu' ? 'selected' : '' }}>🌙 Chiều</option>
                        </select>
                    </div>

                    <div class="col-6 col-lg-3">
                        <label class="form-label fw-semibold text-secondary small mb-1">TRẠNG THÁI</label>
                        <select name="status" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                        </select>
                    </div>

                    {{-- Dòng 2: Phòng & Giáo viên --}}
                    <div class="col-12 col-md-4 col-lg-3">
                        <label class="form-label fw-semibold text-secondary small mb-1">PHÒNG</label>
                        <select name="room_id" class="form-select">
                            <option value="">Tất cả phòng</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                    {{ $room->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4 col-lg-3">
                        <label class="form-label fw-semibold text-secondary small mb-1">GIÁO VIÊN</label>
                        <select name="teacher_id" class="form-select">
                            <option value="">Tất cả giáo viên</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dòng 2: Thời gian --}}
                    <div class="col-6 col-md-2 col-lg-2">
                        <label class="form-label fw-semibold text-secondary small mb-1">THÁNG</label>
                        <select name="month" class="form-select">
                            <option value="">Tất cả</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ request('month') == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                    Tháng {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-6 col-md-2 col-lg-2">
                        <label class="form-label fw-semibold text-secondary small mb-1">NĂM</label>
                        <select name="year" class="form-select">
                            <option value="">Tất cả</option>
                            @for($y = date('Y') + 1; $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Dòng 3: Khoảng ngày & Nút thao tác --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold text-secondary small mb-1">TỪ NGÀY</label>
                        <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold text-secondary small mb-1">ĐẾN NGÀY</label>
                        <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                    </div>

                    <div class="col-12 col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary px-4 flex-grow-1">
                            <i class="bi bi-search me-1"></i>Tìm kiếm
                        </button>
                        <a href="{{ route('lesson-reports.admin-index') }}" class="btn btn-outline-secondary px-4">
                            Bỏ tìm kiếm
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- BẢNG --}}
    <div class="card">
        <div class="card-header py-3 px-4 d-flex align-items-center justify-content-between">
            <div>
                <span class="fw-semibold"><i class="bi bi-clipboard2-data me-2 text-primary"></i>Danh sách báo cáo
                    tiết</span>
                <span class="badge bg-secondary ms-1">{{ $reports->total() }}</span>
            </div>
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#exportReportModal">
                <i class="bi bi-file-earmark-excel me-1"></i>Xuất Excel
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ngày dạy</th>
                        <th>Phòng</th>
                        <th>Giáo viên</th>
                        <th class="text-center">Buổi</th>
                        <th>Lớp</th>
                        <th>Môn</th>
                        <th class="text-center">Tiết</th>
                        <th>Ghi chú GV</th>
                        <th class="text-center">Sự cố</th>
                        <th class="text-end">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $r)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $r->lesson_date->format('d/m/Y') }}</div>
                                <div class="text-muted" style="font-size:.75rem;">{{ $r->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold" style="font-size:.75rem;">
                                    {{ $r->room->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $r->teacher->name ?? 'N/A' }}</div>
                                <div class="text-muted" style="font-size:.75rem;">{{ $r->teacher->teaching_subject ?? '' }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $r->session === 'sang' ? 'bg-warning text-dark' : 'bg-info text-dark' }}"
                                    style="font-size:.75rem;">
                                    {{ $r->session === 'sang' ? '☀️ Sáng' : '🌙 Chiều' }}
                                </span>
                            </td>
                            <td><span class="fw-semibold">{{ $r->class_name }}</span></td>
                            <td><span class="fw-semibold">{{ $r->subject ?? '—' }}</span></td>
                            <td class="text-center">{{ $r->period_count }}</td>
                            <td style="max-width:160px; font-size:.83rem; color:#555;">
                                {{ Str::limit($r->teacher_note, 45) ?: '—' }}
                            </td>
                            <td class="text-center">
                                @if($r->hasIssues())
                                    <span class="badge bg-danger" style="font-size:.72rem;"><i
                                            class="bi bi-exclamation-triangle me-1"></i>Có</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end align-items-center">
                                    <a href="{{ route('lesson-reports.edit', $r) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-pencil me-1"></i>Sửa
                                    </a>
                                    <a href="{{ route('lesson-reports.show', $r) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Xem
                                    </a>
                                    <form action="{{ route('lesson-reports.destroy', $r) }}" method="POST"
                                        class="d-inline mb-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            onclick="confirmDelete(event, 'Báo cáo tiết dạy ngày {{ $r->lesson_date->format('d/m/Y') }} - Lớp {{ $r->class_name }}')"
                                            class="btn btn-sm btn-outline-danger" title="Xóa báo cáo">
                                            <i class="bi bi-trash"></i> Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block opacity-25 mb-2"></i>Chưa có báo cáo nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reports->total() > 0)
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top bg-light-subtle">
                <div class="text-muted" style="font-size:.8rem;">
                    Hiển thị <strong>{{ $reports->firstItem() ?? 0 }}</strong> – <strong>{{ $reports->lastItem() ?? 0 }}</strong> 
                    trên tổng số <strong>{{ $reports->total() }}</strong> báo cáo
                </div>
                <div>
                    {{ $reports->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- MODAL XUẤT EXCEL --}}
    <div class="modal fade" id="exportReportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-excel text-success me-2"></i>Xuất Excel Báo Cáo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="GET" action="{{ route('lesson-reports.export') }}">
                    <div class="modal-body p-4 bg-light">
                        <p class="text-muted small mb-3">Tệp tải xuống sẽ tổng hợp dữ liệu dựa trên các bộ lọc bên dưới.
                            (Nếu để trống sẽ xuất toàn bộ)</p>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted mb-1" style="font-size:.8rem;">PHÒNG THỰC
                                HÀNH</label>
                            <select name="room_id" class="form-select border-0 shadow-sm">
                                <option value="">-- Tất cả phòng --</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted mb-1" style="font-size:.8rem;">GIÁO VIÊN</label>
                            <input type="text" name="teacher_id" list="exportTeacherList"
                                class="form-control border-0 shadow-sm" placeholder="-- Tất cả giáo viên --">
                            <datalist id="exportTeacherList">
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </datalist>
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
                        <button type="submit" class="btn btn-success"><i class="bi bi-download me-1"></i>Tải xuống</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection