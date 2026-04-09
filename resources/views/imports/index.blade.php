@extends('layouts.app')

@section('title', 'Nhập kho thiết bị')
@section('page-title', 'Quản lý nhập kho')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size:1.25rem;">Lịch sử nhập kho</h2>
        <p class="text-muted mb-0" style="font-size:.85rem;">Quản lý các lần nhập thiết bị vào hệ thống</p>
    </div>
    <a href="{{ route('imports.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nhập kho mới
    </a>
</div>

{{-- Filter --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('imports.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <select name="device_id" class="form-select">
                    <option value="">-- Lọc theo thiết bị --</option>
                    @foreach($devices as $device)
                        <option value="{{ $device->id }}" {{ request('device_id')==$device->id ? 'selected' : '' }}>
                            {{ $device->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Lọc</button>
                <a href="{{ route('imports.index') }}" class="btn btn-outline-secondary ms-1">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($imports->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>Chưa có lần nhập kho nào
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">Thiết bị</th>
                        <th>Ngày nhập</th>
                        <th class="text-center">Số lượng</th>
                        <th class="d-none d-md-table-cell">Nhà cung cấp</th>
                        <th class="d-none d-md-table-cell text-end">Giá nhập</th>
                        <th>Người nhập</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($imports as $import)
                    <tr>
                        <td class="px-4">
                            <div class="fw-semibold" style="font-size:.875rem;">{{ $import->device->name ?? '—' }}</div>
                            <code style="font-size:.75rem;">{{ $import->device->code ?? '' }}</code>
                        </td>
                        <td style="font-size:.85rem;">{{ $import->import_date->format('d/m/Y') }}</td>
                        <td class="text-center">
                            <span class="badge bg-success fs-6">+{{ $import->quantity }}</span>
                        </td>
                        <td class="d-none d-md-table-cell text-muted" style="font-size:.85rem;">
                            {{ $import->supplier ?? '—' }}
                        </td>
                        <td class="d-none d-md-table-cell text-end" style="font-size:.85rem;">
                            {{ $import->price ? number_format($import->price, 0, ',', '.') . ' ₫' : '—' }}
                        </td>
                        <td style="font-size:.85rem;">{{ $import->importer->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($imports->hasPages())
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                <div class="text-muted" style="font-size:.8rem;">
                    {{ $imports->firstItem() }}–{{ $imports->lastItem() }} / {{ $imports->total() }}
                </div>
                {{ $imports->withQueryString()->links() }}
            </div>
        @endif
        @endif
    </div>
</div>
@endsection
