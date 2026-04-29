<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\YeuCauDoiLich;
use App\Models\PhongHoc;
use App\Models\NguoiDung;
use App\Services\TeacherScheduleService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request, TeacherScheduleService $service)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        $modeFromRequest = $request->query('mode');
        $mode = is_string($modeFromRequest)
            ? $modeFromRequest
            : (string) $request->session()->get('teacher_schedule_mode', 'week');

        if (! in_array($mode, ['week', 'month', 'year'], true)) {
            $mode = 'week';
        }

        $anchorDate = CarbonImmutable::now();
        $anchorDateInput = $request->query('date');

        if (is_string($anchorDateInput) && trim($anchorDateInput) !== '') {
            try {
                $anchorDate = CarbonImmutable::parse($anchorDateInput);
            } catch (\Throwable $exception) {
                $anchorDate = CarbonImmutable::now();
            }
        } else {
            $storedAnchor = (string) $request->session()->get('teacher_schedule_anchor', '');

            if ($storedAnchor !== '') {
                try {
                    $anchorDate = CarbonImmutable::parse($storedAnchor);
                } catch (\Throwable $exception) {
                    $anchorDate = CarbonImmutable::now();
                }
            }
        }

        $request->session()->put('teacher_schedule_mode', $mode);
        $request->session()->put('teacher_schedule_anchor', $anchorDate->format('Y-m-d'));

        if ($mode === 'month') {
            $periodStart = $anchorDate->startOfMonth();
            $periodEnd = $anchorDate->endOfMonth();
            $periodLabel = 'Thang ' . $periodStart->format('m/Y');
            $modeTitle = 'Lich day theo thang';
            $modeEyebrow = 'Month View';
            $modeDescription = 'Tong hop cac buoi day trong thang de ban quan sat lich day theo tung ngay.';
            $prevDate = $periodStart->subMonth()->format('Y-m-d');
            $nextDate = $periodStart->addMonth()->format('Y-m-d');
        } elseif ($mode === 'year') {
            $periodStart = $anchorDate->startOfYear();
            $periodEnd = $anchorDate->endOfYear();
            $periodLabel = 'Nam ' . $periodStart->format('Y');
            $modeTitle = 'Lich day theo nam';
            $modeEyebrow = 'Year View';
            $modeDescription = 'Theo doi toan bo lich giang day trong nam, phu hop de lap ke hoach dai han.';
            $prevDate = $periodStart->subYear()->format('Y-m-d');
            $nextDate = $periodStart->addYear()->format('Y-m-d');
        } else {
            $periodStart = $anchorDate->startOfWeek(\Carbon\CarbonInterface::MONDAY);
            $periodEnd = $anchorDate->endOfWeek(\Carbon\CarbonInterface::SUNDAY);
            $periodLabel = $periodStart->format('d/m') . ' - ' . $periodEnd->format('d/m/Y');
            $modeTitle = 'Lich day theo tuan';
            $modeEyebrow = 'Week View';
            $modeDescription = 'Mỗi buổi hiển thị theo lớp nội bộ mà admin đã phân công. Bạn có thể gửi yêu cầu dời buổi trực tiếp từ từng slot.';
            $prevDate = $periodStart->subWeek()->format('Y-m-d');
            $nextDate = $periodStart->addWeek()->format('Y-m-d');
        }

        $scheduleItems = $service->scheduleForRange($current, $periodStart, $periodEnd);
        $weeklyTimetable = $service->weeklyTimetable($current, $anchorDate);

        return view('giao_vien.lich_hoc.index', [
            'current' => $current,
            'scheduleItems' => $scheduleItems,
            'weeklyTimetable' => $weeklyTimetable,
            'availableRooms' => PhongHoc::query()
                ->where('status', PhongHoc::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'scheduleMode' => $mode,
            'modeTitle' => $modeTitle,
            'modeEyebrow' => $modeEyebrow,
            'modeDescription' => $modeDescription,
            'periodLabel' => $periodLabel,
            'anchorDate' => $anchorDate,
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
            'pendingRequestsCount' => YeuCauDoiLich::query()
                ->where('teacher_id', $current->id)
                ->where('status', YeuCauDoiLich::STATUS_PENDING)
                ->count(),
        ]);
    }
}
