<?php

namespace App\Exports;

use App\Models\Device;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DevicesFullExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Device::all();
    }

    public function headings(): array
    {
        return [
            'Mã TB',
            'Tên thiết bị',
            'Môn học',
            'Tổ chuyên môn',
            'Đơn giá (VNĐ)',
            'Tổng SL',
            'Hỏng',
            'Mất',
            'Đang mượn',
            'Còn lại',
            'Thành tiền (VNĐ)',
        ];
    }

    public function map($device): array
    {
        return [
            $device->code,
            $device->name,
            $device->subject,
            $device->subject_group,
            $device->unit_price,
            $device->quantity,
            $device->damaged_qty,
            $device->lost_qty,
            $device->borrowedQty(),
            $device->remainingQty(),
            $device->totalValue(),
        ];
    }
}
