<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseTimeSlot;
use App\Models\Enrollment;
use App\Models\Room;
use App\Models\ScheduleChangeRequest;
use App\Models\SlotRegistration;
use App\Models\SlotRegistrationChoice;
use App\Models\Subject;
use App\Models\TeacherApplication;
use App\Models\User;
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

        $recentEnrollments = Enrollment::query()
            ->with(['user', 'subject.category', 'course.subject'])
            ->latest('submitted_at')
            ->latest('id')
            ->limit(6)
            ->get();

        $pendingTeacherApplicationsList = TeacherApplication::query()
            ->where('status', TeacherApplication::STATUS_PENDING)
            ->latest('created_at')
            ->limit(5)
            ->get();

        $pendingScheduleRequestsList = ScheduleChangeRequest::query()
            ->with(['teacher', 'course.subject'])
            ->where('status', ScheduleChangeRequest::STATUS_PENDING)
            ->latest('created_at')
            ->limit(5)
            ->get();

        $recentCourses = Course::query()
            ->with(['subject.category', 'teacher'])
            ->latest('id')
            ->limit(5)
            ->get();

        $pendingSlotRegistrationsList = $this->pendingSlotRegistrations($slotRegistrationsReady, $slotChoicesReady);
        $slotDemandSummary = $this->slotDemandSummary($timeSlotsReady, $slotChoicesReady);
        $infrastructureWarnings = $this->infrastructureWarnings($infrastructureChecks);
        [$studentConflictStudentCount, $studentConflictPairCount] = $this->studentConflictSummary();

        return [
            'studentCount' => User::students()->count(),
            'teacherCount' => User::teachers()->count(),
            'pendingTeacherApplications' => TeacherApplication::where('status', TeacherApplication::STATUS_PENDING)->count(),
            'subjectCount' => Subject::count(),
            'groupCount' => Category::count(),
            'roomCount' => $roomsReady ? Room::count() : 0,
            'openTimeSlotCount' => $timeSlotsReady ? CourseTimeSlot::where('status', CourseTimeSlot::STATUS_OPEN_FOR_REGISTRATION)->count() : 0,
            'pendingSlotRegistrationCount' => $slotRegistrationsReady ? SlotRegistration::where('status', SlotRegistration::STATUS_PENDING)->count() : 0,
            'configuredTimeSlotCount' => $timeSlotsReady ? CourseTimeSlot::count() : 0,
            'readyToOpenClassSlotCount' => $timeSlotsReady ? CourseTimeSlot::where('status', CourseTimeSlot::STATUS_READY_TO_OPEN_CLASS)->count() : 0,
            'recordedSlotRegistrationCount' => $slotRegistrationsReady ? SlotRegistration::where('status', SlotRegistration::STATUS_RECORDED)->count() : 0,
            'slotChoiceCount' => $slotChoicesReady ? SlotRegistrationChoice::count() : 0,
            'maintenanceRoomCount' => $roomsReady ? Room::where('status', Room::STATUS_MAINTENANCE)->count() : 0,
            'activeClassCount' => Course::where('status', Course::STATUS_ACTIVE)->count(),
            'pendingScheduleChangeRequests' => ScheduleChangeRequest::where('status', ScheduleChangeRequest::STATUS_PENDING)->count(),
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

            return [
                $studentConflicts->count(),
                $studentConflicts->sum(fn (array $item) => count($item['conflicts'] ?? [])),
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

        $query = SlotRegistration::query()
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

        $query = CourseTimeSlot::query()
            ->with(['subject.category', 'teacher', 'room'])
            ->whereIn('status', [
                CourseTimeSlot::STATUS_OPEN_FOR_REGISTRATION,
                CourseTimeSlot::STATUS_READY_TO_OPEN_CLASS,
            ])
            ->latest('id')
            ->limit(6);

        if ($slotChoicesReady) {
            $query->withCount('registrations');
        }

        return $query->get();
    }
}
