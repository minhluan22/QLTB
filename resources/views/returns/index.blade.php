@extends('layouts.app')

@section('title', 'Lịch sử trả thiết bị')
@section('page-title', 'Lịch sử trả thiết bị')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size:1.25rem;">Lịch sử trả thiết bị</h2>
        <p class="text-muted mb-0" style="font-size:.85rem;">Tất cả lần trả thiết bị trong hệ thống</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($returns->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-arrow-return-left fs-1 d-block mb-2"></i>
                Chưa có lần trả thiết bị nào.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">#</th>
                        <th>Giáo viên</th>
                        <th>Thiết bị trả</th>
                        <th>Ngày trả</th>
                        <th>Ghi chú</th>
                        <th>Chi tiết yêu cầu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($returns as $return)
                    <tr>
                        <td class="px-4 text-muted" style="font-size:.8rem;">{{ $return->id }}</td>
                        <td>
                            <div class="fw-semibold" style="font-size:.875rem;">
                                {{ $return->borrowRequest->user->name ?? '—' }}
                            </div>
                            <div class="text-muted" style="font-size:.75rem;">
                                {{ $return->borrowRequest->user->email ?? '' }}
                            </div>
                        </td>
                        <td>
                            @foreach($return->borrowRequest->borrowDetails->take(2) as $d)
                                <span class="badge bg-light text-dark border" style="font-size:.72rem;">
                                    {{ $d->device->name ?? '—' }} ({{ $d->quantity }})
                                </span>
                            @endforeach
                            @if($return->borrowRequest->borrowDetails->count() > 2)
                                <span class="text-muted" style="font-size:.75rem;">
                                    +{{ $return->borrowRequest->borrowDetails->count() - 2 }} nữa
                                </span>
                            @endif
                        </td>
                        <td style="font-size:.85rem;">
                            {{ $return->return_date->format('d/m/Y') }}
                        </td>
                        <td class="text-muted" style="font-size:.8rem;max-width:180px;">
                            {{ $return->note ? Str::limit($return->note, 50) : '—' }}
                        </td>
                        <td>
                            <a href="{{ route('borrow-requests.show', $return->borrow_request_id) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Xem
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                <div class="text-muted" style="font-size:.8rem;">
                    {{ $returns->firstItem() }}–{{ $returns->lastItem() }} / {{ $returns->total() }} bản ghi
                </div>
                {{ $returns->links() }}
            </div>
        @endif
        @endif
    </div>
</div>
@endsection
