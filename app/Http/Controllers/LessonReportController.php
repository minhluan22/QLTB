<?php

namespace App\Http\Controllers;

use App\Models\LessonReport;
use App\Models\LessonReportDevice;
use App\Models\LessonReportIssue;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LessonReportController extends Controller
{
    // ================= TEACHER =================

    /**
     * Teacher: form tạo báo cáo tiết dạy
     * Chỉ hiện phòng phù hợp với môn mình dạy
     */
    public function create()
    {
        $user    = auth()->user();
        $subject = $this->subjectKeyFromTeacher($user);

        // Lọc phòng theo môn dạy của giáo viên
        $query = Room::with('devices');
        if ($subject) {
            $query->where('subject', $subject);
        }
        $rooms = $query->get();

        return view('lesson-reports.create', compact('rooms', 'user'));
    }

    /**
     * Teacher: lưu báo cáo tiết mới (kèm thiết bị dùng + sự cố)
     */
    public function store(Request $request)
    {
        $request->validate([
            'room_id'         => ['required', 'exists:rooms,id'],
            'subject'         => ['required', 'string', 'max:100'],
            'lesson_date'     => ['required', 'date'],
            'session'         => ['required', 'in:sang,chieu'],
            'period_count'    => ['required', 'integer', 'min:1', 'max:10'],
            'class_name'      => ['required', 'string', 'max:50'],
            'teacher_note'    => ['nullable', 'string'],
            // devices
            'devices'         => ['nullable', 'array'],
            'devices.*.id'    => ['required', 'exists:room_devices,id'],
            'devices.*.qty'   => ['required', 'numeric', 'min:0'],
            // issues
            'issues'          => ['nullable', 'array'],
            'issues.*.id'     => ['required', 'exists:room_devices,id'],
            'issues.*.broken' => ['nullable', 'integer', 'min:0'],
            'issues.*.consumed' => ['nullable', 'numeric', 'min:0'],
            'issues.*.note'   => ['nullable', 'string'],
        ], [
            'room_id.required'      => 'Vui lòng chọn phòng thực hành.',
            'subject.required'      => 'Vui lòng chọn môn học.',
            'lesson_date.required'  => 'Vui lòng chọn ngày dạy.',
            'session.required'      => 'Vui lòng chọn buổi dạy.',
            'period_count.required' => 'Vui lòng nhập số tiết.',
            'class_name.required'   => 'Vui lòng nhập tên lớp.',
        ]);

        DB::transaction(function () use ($request) {
            $report = LessonReport::create([
                'room_id'      => $request->room_id,
                'teacher_id'   => auth()->id(),
                'subject'      => $request->subject,
                'lesson_date'  => $request->lesson_date,
                'session'      => $request->input('session'),
                'period_count' => $request->period_count,
                'class_name'   => $request->class_name,
                'teacher_note' => $request->teacher_note,
                'status'       => 'confirmed',
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);

            // Thiết bị đã sử dụng
            foreach (($request->devices ?? []) as $entry) {
                if (!empty($entry['id']) && !empty($entry['qty'])) {
                    LessonReportDevice::create([
                        'lesson_report_id' => $report->id,
                        'room_device_id'   => $entry['id'],
                        'quantity_used'    => $entry['qty'],
                    ]);
                }
            }

            // Sự cố thiết bị → lưu + cập nhật ngay số hỏng/tiêu hao/mất
            foreach (($request->issues ?? []) as $issue) {
                $broken   = (float) ($issue['broken']   ?? 0);
                $consumed = (float) ($issue['consumed'] ?? 0);
                $lost     = (float) ($issue['lost']     ?? 0);
                if (!empty($issue['id']) && ($broken > 0 || $consumed > 0 || $lost > 0)) {
                    LessonReportIssue::create([
                        'lesson_report_id' => $report->id,
                        'room_device_id'   => $issue['id'],
                        'broken_qty'       => $broken,
                        'consumed_qty'     => $consumed,
                        'lost_qty'         => $lost,
                        'note'             => $issue['note'] ?? null,
                    ]);

                    // Cập nhật ngay vào bảng thiết bị phòng
                    $device = \App\Models\RoomDevice::find($issue['id']);
                    if ($device) {
                        if ($broken   > 0) $device->increment('broken_qty',   $broken);
                        if ($consumed > 0) $device->increment('consumed_qty', $consumed);
                        if ($lost     > 0) $device->increment('lost_qty',     $lost);
                    }
                }
            }
        });

        return redirect()->route('lesson-reports.my')
                         ->with('success', 'Đã gửi báo cáo tiết! Số hỏng/tiêu hao/mất đã được cập nhật vào thiết bị phòng.');
    }


    /**
     * Teacher: lịch sử báo cáo của mình
     */
    public function my(Request $request)
    {
        $query = LessonReport::with(['room'])
            ->where('teacher_id', auth()->id())
            ->latest('lesson_date');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('class_name', 'like', "%$s%")
                  ->orWhere('subject', 'like', "%$s%")
                  ->orWhere('teacher_note', 'like', "%$s%");
            });
        }

        $reports = $query->paginate(10)->withQueryString();

        return view('lesson-reports.my', compact('reports'));
    }

    // ================= ROOM MANAGER =================

    /**
     * Room manager: xem báo cáo trong phòng mình
     */
    public function roomIndex(Request $request)
    {
        $room = Room::where('manager_id', auth()->id())->firstOrFail();

        $query = LessonReport::with(['teacher', 'issues'])
            ->where('room_id', $room->id)
            ->latest('lesson_date');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('class_name', 'like', "%$s%")
                  ->orWhere('subject', 'like', "%$s%")
                  ->orWhere('teacher_note', 'like', "%$s%")
                  ->orWhereHas('teacher', function($t) use ($s) {
                      $t->where('name', 'like', "%$s%");
                  });
            });
        }

        if ($request->filled('session')) {
            $query->where('session', $request->input('session'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('from')) {
            $query->whereDate('lesson_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('lesson_date', '<=', $request->to);
        }

        $reports = $query->paginate(10)->withQueryString();

        return view('lesson-reports.room-index', compact('room', 'reports'));
    }

    /**
     * Room manager: xác nhận hoặc từ chối báo cáo
     * Nếu xác nhận → cập nhật broken_qty + consumed_qty của room_devices
     */
    public function confirm(Request $request, LessonReport $lessonReport)
    {
        $room = Room::where('manager_id', auth()->id())->firstOrFail();
        if ($lessonReport->room_id !== $room->id) abort(403);

        $request->validate([
            'action'       => ['required', 'in:confirmed,rejected'],
            'manager_note' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $lessonReport) {
            $lessonReport->update([
                'status'       => $request->action,
                'manager_note' => $request->manager_note,
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);

            // Nếu xác nhận → cập nhật số hỏng + tiêu hao của thiết bị trong phòng
            if ($request->action === 'confirmed') {
                // Load issues kèm thiết bị phòng
                $lessonReport->load('issues.device');

                foreach ($lessonReport->issues as $issue) {
                    /** @var \App\Models\RoomDevice $device */
                    $device = $issue->device;
                    if (!$device) continue;

                    if ($issue->broken_qty > 0) {
                        $device->increment('broken_qty', $issue->broken_qty);
                    }
                    if ($issue->consumed_qty > 0) {
                        $device->increment('consumed_qty', $issue->consumed_qty);
                    }
                }
            }
        });

        $msg = $request->action === 'confirmed'
            ? 'Đã xác nhận báo cáo tiết. Số hỏng/tiêu hao thiết bị trong phòng đã được cập nhật.'
            : 'Đã từ chối báo cáo tiết.';

        return back()->with('success', $msg);
    }

    // ================= ADMIN =================

    /**
     * Admin: xem tất cả báo cáo, filter theo phòng / giáo viên / trạng thái
     */
    public function adminIndex(Request $request)
    {
        $rooms = Room::all();
        $teachers = \App\Models\User::whereIn('role', ['teacher', 'admin'])
            ->where(function ($q) {
                $q->where('teaching_subject', 'like', '%Lý%')
                  ->orWhere('teaching_subject', 'like', '%Lí%')
                  ->orWhere('teaching_subject', 'like', '%Hóa%')
                  ->orWhere('teaching_subject', 'like', '%Sinh%');
            })
            ->get();
        $query = LessonReport::with(['room', 'teacher'])->latest('lesson_date');

        if ($request->filled('search')) {
            $s = $request->search;
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

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }
        if ($request->filled('session')) {
            $query->where('session', $request->input('session'));
        }
        if ($request->filled('month')) {
            $query->whereMonth('lesson_date', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('lesson_date', $request->year);
        }
        if ($request->filled('from')) {
            $query->whereDate('lesson_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('lesson_date', '<=', $request->to);
        }

        $reports = $query->paginate(10)->withQueryString();

        $stats = [
            'total'      => LessonReport::count(),
            'sang'       => LessonReport::where('session', 'sang')->count(),
            'chieu'      => LessonReport::where('session', 'chieu')->count(),
            'has_issues' => LessonReport::whereHas('issues')->count(),
        ];

        return view('lesson-reports.admin-index', compact('rooms', 'teachers', 'reports', 'stats'));
    }

    /**
     * Xuất Excel báo cáo tiết
     */
    public function export(Request $request)
    {
        $filters = $request->only(['room_id', 'teacher_id', 'session', 'month', 'year', 'from', 'to', 'search']);
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LessonReportExport($filters),
            'bao-cao-tiet-thuc-hanh.xlsx'
        );
    }

    /**
     * Xem chi tiết báo cáo (admin + room_manager + chính giáo viên)
     */
    public function show(LessonReport $lessonReport)
    {
        $user = auth()->user();

        // Kiểm tra quyền truy cập
        if ($user->isTeacher() && !$user->isRoomManager() && $lessonReport->teacher_id !== $user->id) {
            abort(403);
        }
        if ($user->isRoomManager()) {
            $room = Room::where('manager_id', $user->id)->first();
            if (!$room || $lessonReport->room_id !== $room->id) {
                abort(403);
            }
        }

        $lessonReport->load(['room', 'teacher', 'deviceUsages.device', 'issues.device', 'confirmedBy']);

        return view('lesson-reports.show', compact('lessonReport'));
    }

    // ================= EDIT / UPDATE =================

    /**
     * Hiển thị form sửa báo cáo tiết (Admin + chính giáo viên)
     */
    public function edit(LessonReport $lessonReport)
    {
        $user = auth()->user();

        // Chỉ admin hoặc giáo viên tạo báo cáo mới được sửa
        if ($user->isTeacher() && !$user->isAdmin() && $lessonReport->teacher_id !== $user->id) {
            abort(403);
        }

        $rooms = Room::all();
        $lessonReport->load(['room', 'teacher']);

        return view('lesson-reports.edit', compact('lessonReport', 'rooms'));
    }

    /**
     * Lưu chỉnh sửa báo cáo tiết
     */
    public function update(Request $request, LessonReport $lessonReport)
    {
        $user = auth()->user();
        if ($user->isTeacher() && !$user->isAdmin() && $lessonReport->teacher_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'room_id'      => ['required', 'exists:rooms,id'],
            'subject'      => ['required', 'string', 'max:100'],
            'lesson_date'  => ['required', 'date'],
            'session'      => ['required', 'in:sang,chieu'],
            'period_count' => ['required', 'integer', 'min:1', 'max:10'],
            'class_name'   => ['required', 'string', 'max:50'],
            'teacher_note' => ['nullable', 'string'],
        ]);

        $lessonReport->update([
            'room_id'      => $request->room_id,
            'subject'      => $request->subject,
            'lesson_date'  => $request->lesson_date,
            'session'      => $request->input('session'),
            'period_count' => $request->period_count,
            'class_name'   => $request->class_name,
            'teacher_note' => $request->teacher_note,
        ]);

        $back = $user->isAdmin()
            ? route('lesson-reports.admin-index')
            : route('lesson-reports.my');

        return redirect($back)->with('success', 'Đã cập nhật báo cáo tiết thành công!');
    }

    /**
     * Xóa báo cáo tiết dạy (Chỉ Admin)
     */
    public function destroy(LessonReport $lessonReport)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort(403);
        }

        try {
            DB::transaction(function () use ($lessonReport) {
                // Xoá các chi tiết liên quan
                $lessonReport->deviceUsages()->delete();
                $lessonReport->issues()->delete();
                
                // Xoá báo cáo
                $lessonReport->delete();
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xóa báo cáo: ' . $e->getMessage());
        }

        return back()->with('success', 'Đã xoá báo cáo tiết dạy thành công.');
    }

    // ================= PRIVATE HELPERS =================

    private function subjectKeyFromTeacher($user): ?string
    {
        $subject = strtolower(trim($user->teaching_subject ?? ''));
        if (str_contains($subject, 'lý') || str_contains($subject, 'ly') || str_contains($subject, 'công nghệ') || str_contains($subject, 'cong nghe'))   return 'ly';
        if (str_contains($subject, 'hóa') || str_contains($subject, 'hoa')) return 'hoa';
        if (str_contains($subject, 'sinh'))                                  return 'sinh';
        return null; // Teacher không rõ môn → thấy tất cả phòng
    }
}
