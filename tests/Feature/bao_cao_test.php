<?php

namespace Tests\Feature;

use App\Models\NhomHoc;
use App\Models\KhoaHoc;
use App\Models\GhiDanh;
use App\Models\DiemSo;
use App\Models\DanhGia;
use App\Models\YeuCauDoiLich;
use App\Models\MonHoc;
use App\Models\DonUngTuyenGiaoVien;
use App\Models\NguoiDung;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class bao_cao_test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_report_page(): void
    {
        $admin = NguoiDung::factory()->admin()->create();

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.report'));

        $response->assertOk();
        $response->assertDontSee('Phase 11');
        $response->assertSee('Bao cao tong quan he thong');
        $response->assertSee('Xem chi tiết');
    }

    public function test_report_returns_correct_basic_metrics_for_selected_period(): void
    {
        $admin = NguoiDung::factory()->admin()->create();

        try {
            Carbon::setTestNow('2026-03-05 09:00:00');
            $teacherInPeriod = NguoiDung::factory()->teacher()->create([
                'name' => 'Giang Vien Bao Cao',
                'email' => 'teacher-report@example.com',
            ]);

            Carbon::setTestNow('2026-03-06 09:00:00');
            $studentA = NguoiDung::factory()->student()->create([
                'name' => 'Hoc Vien Mot',
                'email' => 'student-one@example.com',
            ]);

            Carbon::setTestNow('2026-03-07 09:00:00');
            $studentB = NguoiDung::factory()->student()->create([
                'name' => 'Hoc Vien Hai',
                'email' => 'student-two@example.com',
            ]);

            Carbon::setTestNow('2026-01-08 09:00:00');
            $teacherOutsidePeriod = NguoiDung::factory()->teacher()->create([
                'name' => 'Giang Vien Cu',
                'email' => 'teacher-old@example.com',
            ]);
            $studentOutsidePeriod = NguoiDung::factory()->student()->create([
                'name' => 'Hoc Vien Cu',
                'email' => 'student-old@example.com',
            ]);

            [$category, $subject] = $this->createCatalogSubject();

            $activeCourse = $this->createInternalCourse($subject, $teacherInPeriod, [
                'title' => 'Tin hoc van phong - Lop bao cao',
                'status' => KhoaHoc::STATUS_ACTIVE,
                'schedule' => 'Thu 2, 18:00 - 20:00',
            ]);
            $this->createInternalCourse($subject, $teacherOutsidePeriod, [
                'title' => 'Tin hoc van phong - Lop nhap',
                'status' => KhoaHoc::STATUS_DRAFT,
            ]);

            Carbon::setTestNow('2026-03-09 09:00:00');
            DonUngTuyenGiaoVien::create([
                'name' => 'Ung vien moi',
                'email' => 'ung-vien-moi@example.com',
                'status' => DonUngTuyenGiaoVien::STATUS_PENDING,
            ]);

            Carbon::setTestNow('2026-01-10 09:00:00');
            DonUngTuyenGiaoVien::create([
                'name' => 'Ung vien cu',
                'email' => 'ung-vien-cu@example.com',
                'status' => DonUngTuyenGiaoVien::STATUS_APPROVED,
            ]);

            Carbon::setTestNow('2026-03-10 09:00:00');
            $pendingEnrollment = $this->createEnrollment($studentA, $subject, [
                'status' => GhiDanh::STATUS_PENDING,
            ]);
            $activeEnrollmentA = $this->createEnrollment($studentA, $subject, [
                'course_id' => $activeCourse->id,
                'assigned_teacher_id' => $teacherInPeriod->id,
                'status' => GhiDanh::STATUS_ACTIVE,
                'schedule' => $activeCourse->schedule,
            ]);

            Carbon::setTestNow('2026-03-11 09:00:00');
            $activeEnrollmentB = $this->createEnrollment($studentB, $subject, [
                'course_id' => $activeCourse->id,
                'assigned_teacher_id' => $teacherInPeriod->id,
                'status' => GhiDanh::STATUS_ACTIVE,
                'schedule' => $activeCourse->schedule,
            ]);

            Carbon::setTestNow('2026-01-12 09:00:00');
            $this->createEnrollment($studentOutsidePeriod, $subject, [
                'course_id' => $activeCourse->id,
                'assigned_teacher_id' => $teacherInPeriod->id,
                'status' => GhiDanh::STATUS_COMPLETED,
                'schedule' => $activeCourse->schedule,
            ]);

            Carbon::setTestNow('2026-03-12 09:00:00');
            DiemSo::create([
                'enrollment_id' => $activeEnrollmentA->id,
                'score' => 80,
                'grade' => 'A',
            ]);

            Carbon::setTestNow('2026-03-13 09:00:00');
            DiemSo::create([
                'enrollment_id' => $activeEnrollmentB->id,
                'score' => 40,
                'grade' => 'C',
            ]);

            Carbon::setTestNow('2026-03-14 09:00:00');
            DanhGia::create([
                'user_id' => $studentA->id,
                'course_id' => $activeCourse->id,
                'rating' => 5,
                'comment' => 'Lop rat on',
            ]);

            Carbon::setTestNow('2026-03-15 09:00:00');
            DanhGia::create([
                'user_id' => $studentB->id,
                'course_id' => $activeCourse->id,
                'rating' => 3,
                'comment' => 'Lop on',
            ]);

            Carbon::setTestNow('2026-03-16 09:00:00');
            YeuCauDoiLich::create([
                'teacher_id' => $teacherInPeriod->id,
                'course_id' => $activeCourse->id,
                'current_schedule' => $activeCourse->schedule,
                'reason' => 'Can doi sang ca som hon',
                'status' => YeuCauDoiLich::STATUS_PENDING,
            ]);
        } finally {
            Carbon::setTestNow();
        }

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.report', [
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-31',
            ]));

        $response->assertOk();
        $response->assertSee('Tong hoc vien');
        $response->assertSee('Tong giang vien');
        $response->assertSee('Top khoa hoc theo dang ky');
        $response->assertSee('Top giang vien theo danh gia');
        $response->assertSee('Xem chi tiết');
        $response->assertViewHas('filters', function (array $filters) {
            return $filters['start_date'] === '2026-03-01'
                && $filters['end_date'] === '2026-03-31';
        });
        $response->assertViewHas('summary', function (array $summary) {
            return $summary['totalStudents'] === 3
                && $summary['studentsInPeriod'] === 2
                && $summary['totalTeachers'] === 2
                && $summary['teachersInPeriod'] === 1
                && $summary['newEnrollments'] === 3
                && $summary['totalTeacherApplications'] === 2
                && $summary['teacherApplicationsInPeriod'] === 1
                && $summary['activeClasses'] === 1
                && $summary['pendingEnrollments'] === 1
                && $summary['pendingScheduleChanges'] === 1
                && $summary['publicSubjects'] === 1;
        });
        $response->assertViewHas('quality', function (array $quality) {
            return (float) $quality['averageScore'] === 60.0
                && (float) $quality['passRate'] === 50.0
                && $quality['gradeCount'] === 2
                && (float) $quality['averageCourseRating'] === 4.0
                && (float) $quality['averageTeacherRating'] === 4.0
                && $quality['courseReviewCount'] === 2
                && $quality['teacherReviewCount'] === 2
                && $quality['reviewedCourseCount'] === 1;
        });
        $response->assertViewHas('availability', function (array $availability) {
            return $availability['attendance']['available'] === true
                && $availability['payments']['available'] === false;
        });
        $response->assertViewHas('topCourses', function ($topCourses) use ($subject) {
            return $topCourses->count() === 1
                && $topCourses->first()->id === $subject->id
                && $topCourses->first()->enrollments_in_period === 3;
        });
        $response->assertViewHas('topTeachers', function ($topTeachers) use ($teacherInPeriod) {
            return $topTeachers->count() === 1
                && $topTeachers->first()['teacher']->id === $teacherInPeriod->id
                && (float) $topTeachers->first()['average_rating'] === 4.0
                && $topTeachers->first()['reviews_count'] === 2
                && $topTeachers->first()['courses_count'] === 1;
        });
    }

    public function test_student_is_blocked_from_admin_report(): void
    {
        $student = NguoiDung::factory()->student()->create();

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('admin.report'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    private function createCatalogSubject(string $subjectName = 'Tin hoc van phong', string $slug = 'tin-hoc-van-phong'): array
    {
        $category = NhomHoc::create([
            'name' => 'Nhom hoc ' . $slug,
            'slug' => 'nhom-hoc-' . $slug,
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);

        $subject = MonHoc::create([
            'name' => $subjectName,
            'category_id' => $category->id,
            'status' => MonHoc::STATUS_OPEN,
            'price' => 1500000,
            'duration' => 24,
        ]);

        return [$category, $subject];
    }

    private function createEnrollment(NguoiDung $student, MonHoc $subject, array $overrides = []): GhiDanh
    {
        return GhiDanh::create(array_merge([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => GhiDanh::STATUS_PENDING,
            'start_time' => '18:00',
            'end_time' => '20:00',
            'preferred_days' => json_encode(['Monday', 'Wednesday', 'Friday']),
            'is_submitted' => true,
            'submitted_at' => now(),
        ], $overrides));
    }

    private function createInternalCourse(MonHoc $subject, ?NguoiDung $teacher = null, array $overrides = []): KhoaHoc
    {
        return KhoaHoc::create(array_merge([
            'subject_id' => $subject->id,
            'title' => $subject->name . ' - Lop noi bo',
            'description' => 'Lop hoc noi bo phuc vu bao cao he thong.',
            'teacher_id' => $teacher?->id,
            'capacity' => 20,
            'status' => KhoaHoc::STATUS_DRAFT,
        ], $overrides));
    }
}

