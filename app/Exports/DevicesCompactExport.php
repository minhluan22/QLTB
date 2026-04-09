<?php

namespace App\Exports;

use App\Models\Device;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DevicesCompactExport implements FromQuery, WithHeadings, WithMapping
{
    protected $subjectGroup;

    public function __construct($subjectGroup = null)
    {
        // Nhận filter từ controller (nếu có)
        $this->subjectGroup = $subjectGroup;
    }

    public function query()
    {
        $query = Device::query();
        if ($this->subjectGroup) {
            $query->where('subject_group', $this->subjectGroup);
        }
        return $query;
    }

    public function headings(): array
    {
        return [
            'Tên thiết bị',
            'Tổng SL',
            'Hỏng',
            'Mất',
            'Còn lại',
        ];
    }

    public function map($device): array
    {
        return [
            $device->name,
            $device->quantity,
            $device->damaged_qty,
            $device->lost_qty,
            $device->remainingQty(),
        ];
    }
}
