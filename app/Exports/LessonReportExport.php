<?php

namespace App\Exports;

use App\Models\LessonReport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LessonReportExport implements WithMultipleSheets
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = array_merge([
            'room_id'    => null,
            'teacher_id' => null,
            'session'    => null,
            'month'      => null,
            'year'       => null,
            'from'       => null,
            'to'         => null,
            'search'     => null,
        ], $filters);
    }

    public function sheets(): array
    {
        $query = LessonReport::with(['room', 'teacher', 'deviceUsages.device'])->latest('lesson_date');

        if (!empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $query->where(function($q) use ($s) {
                $q->where('class_name', 'like', "%$s%")
                  ->orWhere('subject', 'like', "%$s%")
                  ->orWhere('teacher_note', 'like', "%$s%")
                  ->orWhereHas('teacher', function($t) use ($s) {
                      $t->where('name', 'like', "%$s%");
                  })
                  ->orWhereHas('room', function($r) use ($s) {
                      $r->where('name', 'like', "%$s%");
                  });
            });
        }

        if (!empty($this->filters['room_id'])) {
            $query->where('room_id', $this->filters['room_id']);
        }
        if (!empty($this->filters['teacher_id'])) {
            $query->where('teacher_id', $this->filters['teacher_id']);
        }
        if (!empty($this->filters['session'])) {
            $query->where('session', $this->filters['session']);
        }
        if (!empty($this->filters['month'])) {
            $query->whereMonth('lesson_date', $this->filters['month']);
        }
        if (!empty($this->filters['year'])) {
            $query->whereYear('lesson_date', $this->filters['year']);
        }
        if (!empty($this->filters['from'])) {
            $query->whereDate('lesson_date', '>=', $this->filters['from']);
        }
        if (!empty($this->filters['to'])) {
            $query->whereDate('lesson_date', '<=', $this->filters['to']);
        }

        $reports = $query->get();

        return [
            new Sheets\LessonReportDetailsSheet($reports),
            new Sheets\LessonReportSummarySheet($reports),
        ];
    }
}
