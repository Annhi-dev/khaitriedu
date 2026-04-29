<?php

namespace App\Services;

use App\Models\NhomHoc;
use App\Models\KhoaHoc;
use App\Models\KhungGioKhoaHoc;
use App\Models\GhiDanh;
use App\Models\PhongHoc;
use App\Models\YeuCauDoiLich;
use App\Models\NguyenVongKhungGio;
use App\Models\LuaChonNguyenVongKhungGio;
use App\Models\MonHoc;
use App\Models\DonUngTuyenGiaoVien;
use App\Models\NguoiDung;
use App\Services\AdminScheduleConflictService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AdminDashboardService
{
    public function __construct(protected AdminScheduleConflictService $scheduleConflictService)
    {
    }

    public function overview(): array
    {
        $infrastructureChecks = $this->infrastructureChecks();
        $roomsReady = $infrastructureChecks['rooms'];
        $timeSlotsReady = $infrastructureChecks['time_slots'];
        $slotRegistrationsReady = $infrastructureChecks['slot_registrations'];
        $slotChoicesReady = $infrastructureChecks['slot_registration_choices'];

        $recentEnrollments = GhiDanh::query()
            ->with(['user', 'subject.category', 'course.subject'])
            ->latest('submitted_at')
            ->latest('id')
            ->limit(6)
            ->get();

        $pendingTeacherApplicationsList = DonUngTuyenGiaoVien::query()
            ->where('status', DonUngTuyenGiaoVien::STATUS_PENDING)
            ->latest('created_at')
            ->limit(5)
            ->get();

        $pendingScheduleRequestsList = YeuCauDoiLich::query()
            ->with(['teacher', 'course.subject'])
            ->where('status', YeuCauDoiLich::STATUS_PENDING)
            ->latest('created_at')
            ->limit(5)
            ->get();

        $recentCourses = KhoaHoc::query()
            ->with(['subject.category', 'teacher'])
            ->latest('id')
            ->limit(5)
            ->get();

        $pendingSlotRegistrationsList = $this->pendingSlotRegistrations($slotRegistrationsReady, $slotChoicesReady);
        $slotDemandSummary = $this->slotDemandSummary($timeSlotsReady, $slotChoicesReady);
        $infrastructureWarnings = $this->infrastructureWarnings($infrastructureChecks);
        [$studentConflictStudentCount, $studentConflictPairCount] = $this->studentConflictSummary();

        return [
            'studentCount' => NguoiDung::students()->count(),
            'teacherCount' => NguoiDung::teachers()->count(),
            'pendingTeacherApplications' => DonUngTuyenGiaoVien::where('status', DonUngTuyenGiaoVien::STATUS_PENDING)->count(),
            'subjectCount' => MonHoc::count(),
            'groupCount' => NhomHoc::count(),
            'roomCount' => $roomsReady ? PhongHoc::count() : 0,
            'openTimeSlotCount' => $timeSlotsReady ? KhungGioKhoaHoc::where('status', KhungGioKhoaHoc::STATUS_OPEN_FOR_REGISTRATION)->count() : 0,
            'pendingEnrollmentCount' => GhiDanh::query()
                ->where('status', GhiDanh::STATUS_PENDING)
                ->where('is_submitted', true)
                ->whereNull('lop_hoc_id')
                ->count(),
            'pendingSlotRegistrationCount' => $slotRegistrationsReady ? NguyenVongKhungGio::where('status', NguyenVongKhungGio::STATUS_PENDING)->count() : 0,
            'configuredTimeSlotCount' => $timeSlotsReady ? KhungGioKhoaHoc::count() : 0,
            'readyToOpenClassSlotCount' => $timeSlotsReady ? KhungGioKhoaHoc::where('status', KhungGioKhoaHoc::STATUS_READY_TO_OPEN_CLASS)->count() : 0,
            'recordedSlotRegistrationCount' => $slotRegistrationsReady ? NguyenVongKhungGio::where('status', NguyenVongKhungGio::STATUS_RECORDED)->count() : 0,
            'slotChoiceCount' => $slotChoicesReady ? LuaChonNguyenVongKhungGio::count() : 0,
            'maintenanceRoomCount' => $roomsReady ? PhongHoc::where('status', PhongHoc::STATUS_MAINTENANCE)->count() : 0,
            'activeClassCount' => KhoaHoc::whereIn('status', KhoaHoc::schedulingStatuses())->count(),
            'pendingScheduleChangeRequests' => YeuCauDoiLich::where('status', YeuCauDoiLich::STATUS_PENDING)->count(),
            'recentEnrollments' => $recentEnrollments,
            'pendingTeacherApplicationsList' => $pendingTeacherApplicationsList,
            'pendingScheduleRequestsList' => $pendingScheduleRequestsList,
            'recentCourses' => $recentCourses,
            'pendingSlotRegistrationsList' => $pendingSlotRegistrationsList,
            'slotDemandSummary' => $slotDemandSummary,
            'infrastructureChecks' => $infrastructureChecks,
            'infrastructureWarnings' => $infrastructureWarnings,
            'studentConflictStudentCount' => $studentConflictStudentCount,
            'studentConflictPairCount' => $studentConflictPairCount,
        ];
    }

    protected function infrastructureChecks(): array
    {
        return [
            'rooms' => Schema::hasTable('rooms'),
            'time_slots' => Schema::hasTable('course_time_slots'),
            'slot_registrations' => Schema::hasTable('slot_registrations'),
            'slot_registration_choices' => Schema::hasTable('slot_registration_choices'),
        ];
    }

    protected function infrastructureWarnings(array $checks): array
    {
        $warnings = [];

        if (! $checks['rooms']) {
            $warnings[] = 'Bảng phòng học chưa sẵn sàng. Hãy chạy migrate để bật số liệu phòng học trên dashboard.';
        }

        if (! $checks['time_slots']) {
            $warnings[] = 'Bảng khung giờ học chưa sẵn sàng. Dashboard sẽ tạm hiển thị 0 cho phần slot mở đăng ký.';
        }

        if (! $checks['slot_registrations']) {
            $warnings[] = 'Bảng đăng ký nguyện vọng khung giờ chưa sẵn sàng. Dashboard sẽ tạm hiển thị 0 cho phần nguyện vọng chờ xử lý.';
        }

        if (! $checks['slot_registration_choices']) {
            $warnings[] = 'Bảng lựa chọn khung giờ cho từng đăng ký chưa sẵn sàng. Dashboard chưa thể theo dõi nhu cầu theo từng slot.';
        }

        return $warnings;
    }

    protected function studentConflictSummary(): array
    {
        try {
            $studentConflicts = $this->scheduleConflictService->studentConflicts();
            $studentCount = $studentConflicts
                ->flatMap(fn (array $item) => collect($item['students'] ?? [])->pluck('student_id'))
                ->filter()
                ->unique()
                ->count();

            return [
                $studentCount,
                $studentConflicts->count(),
            ];
        } catch (Throwable) {
            return [0, 0];
        }
    }

    protected function pendingSlotRegistrations(bool $slotRegistrationsReady, bool $slotChoicesReady): Collection
    {
        if (! $slotRegistrationsReady) {
            return collect();
        }

        $query = NguyenVongKhungGio::query()
            ->with(['student', 'subject'])
            ->pending()
            ->latest('created_at')
            ->limit(5);

        if ($slotChoicesReady) {
            $query->withCount('choices');
        }

        return $query->get();
    }

    protected function slotDemandSummary(bool $timeSlotsReady, bool $slotChoicesReady): Collection
    {
        if (! $timeSlotsReady) {
            return collect();
        }

        $query = KhungGioKhoaHoc::query()
            ->with(['subject.category', 'teacher', 'room'])
            ->whereIn('status', [
                KhungGioKhoaHoc::STATUS_OPEN_FOR_REGISTRATION,
                KhungGioKhoaHoc::STATUS_READY_TO_OPEN_CLASS,
            ])
            ->latest('id')
            ->limit(6);

        if ($slotChoicesReady) {
            $query->withCount('registrations');
        }

        return $query->get();
    }
}
