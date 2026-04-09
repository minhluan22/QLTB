<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\DeviceSummarySheet;
use App\Exports\Sheets\DeviceImportsSheet;

class DevicesMultipleSheetsExport implements WithMultipleSheets
{
    use Exportable;

    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = is_array($filters) ? $filters : ['subject_group' => $filters];
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            new DeviceSummarySheet($this->filters),
            new DeviceImportsSheet($this->filters),
        ];
    }
}
