<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Certificate;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\CourseTimeSlot;
use App\Models\CustomScheduleRequest;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\LessonProgress;
use App\Models\Notification;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\Review;
use App\Models\Room;
use App\Models\ScheduleChangeRequest;
use App\Models\SlotRegistration;
use App\Models\SlotRegistrationChoice;
use App\Models\TeacherEvaluation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DuLieuVanHanhDaoTaoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $admin = $this->user('admin@khaitriedu.vn');
            $teachers = $this->users([
                'minhkhang@khaitriedu.vn',
                'thuylinh@khaitriedu.vn',
                'quochuy@khaitriedu.vn',
                'ngoclan@khaitriedu.vn',
                'thanhmai@khaitriedu.vn',
                'minhtu@khaitriedu.vn',
                'anhdung@khaitriedu.vn',
                'baochau@khaitriedu.vn',
                'hoangnam@khaitriedu.vn',
                'hongnhung@khaitriedu.vn',
                'thanhtung@khaitriedu.vn',
                'quangphuc@khaitriedu.vn',
                'hamy@khaitriedu.vn',
                'thaison@khaitriedu.vn',
                'hongloan@khaitriedu.vn',
            ])->keyBy('email');

            $students = $this->users([
                'nguyen.thi.an@khaitriedu.vn',
                'tran.gia.han@khaitriedu.vn',
                'le.minh.quan@khaitriedu.vn',
                'pham.ngoc.linh@khaitriedu.vn',
                'vo.hai.nam@khaitriedu.vn',
                'dang.thanh.truc@khaitriedu.vn',
                'bui.duc.minh@khaitriedu.vn',
                'huynh.bao.ngoc@khaitriedu.vn',
                'nguyen.khac.hung@khaitriedu.vn',
                'luu.thanh.vy@khaitriedu.vn',
                'phan.quynh.anh@khaitriedu.vn',
                'cao.nhat.minh@khaitriedu.vn',
                'ta.phuong.nhi@khaitriedu.vn',
                'vuong.gia.bao@khaitriedu.vn',
                'duong.thu.ha@khaitriedu.vn',
                'trinh.minh.tu@khaitriedu.vn',
                'ho.ngoc.diep@khaitriedu.vn',
                'le.anh.khoa@khaitriedu.vn',
                'ngo.bao.tran@khaitriedu.vn',
                'do.van.tai@khaitriedu.vn',
                'ly.nhat.ha@khaitriedu.vn',
                'nguyen.hoang.long@khaitriedu.vn',
                'truong.my.duyen@khaitriedu.vn',
                'phan.minh.nhat@khaitriedu.vn',
                'le.dieu.linh@khaitriedu.vn',
                'do.tuan.kiet@khaitriedu.vn',
            ])->keyBy('email');

            $courses = $this->courses([
                'KhaiTriEdu 2026 - Tiếng Anh giao tiếp',
                'KhaiTriEdu 2026 - Tiếng Anh thiếu nhi',
                'KhaiTriEdu 2026 - Tin học văn phòng',
                'KhaiTriEdu 2026 - Lập trình Python cơ bản',
                'KhaiTriEdu 2026 - Ứng dụng công nghệ thông tin',
                'KhaiTriEdu 2026 - Kế toán thực hành',
                'KhaiTriEdu 2026 - Báo cáo thuế',
                'KhaiTriEdu 2026 - Thiết kế Web',
                'KhaiTriEdu 2026 - Thiết kế đồ họa',
                'KhaiTriEdu 2026 - Điện dân dụng',
                'KhaiTriEdu 2026 - Kỹ thuật chăm sóc da',
                'KhaiTriEdu 2026 - Bồi dưỡng giáo viên phổ thông',
                'KhaiTriEdu 2026 - Liên thông Đại học - Văn bằng 2 Quản trị kinh doanh',
                'KhaiTriEdu 2026 - Marketing Digital căn bản',
                'KhaiTriEdu 2026 - Kỹ năng bán hàng chuyên nghiệp',
                'Khóa nội bộ - ANH VĂN KHUNG 6 BẬC',
                'Khóa nội bộ - TIN HỌC VĂN PHÒNG',
                'Khóa nội bộ - CHỨNG CHỈ BẤT ĐỘNG SẢN',
                'Khóa nội bộ - BỒI DƯỠNG CHỨC DANH NGHỀ NGHIỆP GIÁO VIÊN',
                'Khóa nội bộ - THẠC SĨ QUẢN TRỊ KINH DOANH',
            ]);

            $this->normalizeCurrentClassRoomSchedules($courses);
            $timeSlots = $this->seedCourseTimeSlots($courses, $teachers);
            $enrollments = $this->seedFixedEnrollments($courses, $students, $admin);
            $this->seedPendingEnrollments($courses, $students);
            $this->seedSlotRegistrations($courses, $students, $admin, $timeSlots);
            $this->seedCustomScheduleRequests($courses, $students, $teachers, $admin);
            $this->seedScheduleChangeRequests($courses, $admin);
            $this->markFullClassRooms($courses);
            $this->seedAttendanceRecords($courses, $students, $enrollments);
            $this->seedGrades($courses, $students, $enrollments);
            $this->seedTeacherEvaluations($courses, $students);
            $this->seedCertificates($courses, $students);
            $this->seedQuizData($courses, $students);
            $this->seedLessonProgress($courses, $students);
            $this->seedNotifications($courses, $students, $teachers);
            $this->seedReviews($courses, $students);
            $this->seedClassRoomStatusSamples($courses);
        });
    }

    protected function seedCourseTimeSlots(Collection $courses, Collection $teachers): Collection
    {
        $fixtures = [
            ['key' => 'english_evening', 'course' => 'KhaiTriEdu 2026 - Tiếng Anh giao tiếp', 'teacher' => 'anhdung@khaitriedu.vn', 'room' => 'PH101', 'day_of_week' => 'Monday', 'slot_date' => Carbon::now()->addDays(9), 'start_time' => '18:00:00', 'end_time' => '20:15:00', 'status' => CourseTimeSlot::STATUS_OPEN_FOR_REGISTRATION, 'note' => 'Đợt học tối dành cho học viên đi làm.'],
            ['key' => 'it_office_evening', 'course' => 'KhaiTriEdu 2026 - Tin học văn phòng', 'teacher' => 'baochau@khaitriedu.vn', 'room' => 'MT301', 'day_of_week' => 'Tuesday', 'slot_date' => Carbon::now()->addDays(10), 'start_time' => '18:00:00', 'end_time' => '20:00:00', 'status' => CourseTimeSlot::STATUS_OPEN_FOR_REGISTRATION, 'note' => 'Khung giờ phù hợp cho lớp thực hành máy tính.'],
            ['key' => 'accounting_ready', 'course' => 'KhaiTriEdu 2026 - Kế toán thực hành', 'teacher' => 'minhtu@khaitriedu.vn', 'room' => 'HT401', 'day_of_week' => 'Wednesday', 'slot_date' => Carbon::now()->addDays(11), 'start_time' => '19:00:00', 'end_time' => '21:00:00', 'status' => CourseTimeSlot::STATUS_READY_TO_OPEN_CLASS, 'note' => 'Đã đủ nguyện vọng chờ phê duyệt mở lớp.'],
            ['key' => 'web_ready', 'course' => 'KhaiTriEdu 2026 - Thiết kế Web', 'teacher' => 'thuylinh@khaitriedu.vn', 'room' => 'MT302', 'day_of_week' => 'Thursday', 'slot_date' => Carbon::now()->addDays(12), 'start_time' => '19:00:00', 'end_time' => '21:15:00', 'status' => CourseTimeSlot::STATUS_READY_TO_OPEN_CLASS, 'note' => 'Khung giờ thực hành thiết kế và bố cục web.'],
            ['key' => 'electric_class_opened', 'course' => 'KhaiTriEdu 2026 - Điện dân dụng', 'teacher' => 'hoangnam@khaitriedu.vn', 'room' => 'TH201', 'day_of_week' => 'Saturday', 'slot_date' => Carbon::now()->addDays(13), 'start_time' => '13:30:00', 'end_time' => '16:30:00', 'status' => CourseTimeSlot::STATUS_CLASS_OPENED, 'note' => 'Đợt thực hành cuối tuần đã chuyển sang lớp cố định.'],
            ['key' => 'marketing_pending', 'course' => 'KhaiTriEdu 2026 - Marketing Digital căn bản', 'teacher' => 'quochuy@khaitriedu.vn', 'room' => 'DA501', 'day_of_week' => 'Friday', 'slot_date' => Carbon::now()->addDays(14), 'start_time' => '19:00:00', 'end_time' => '21:00:00', 'status' => CourseTimeSlot::STATUS_PENDING_OPEN, 'note' => 'Chờ đủ học viên để kích hoạt mở đăng ký.'],
            ['key' => 'teacher_boost_open', 'course' => 'KhaiTriEdu 2026 - Bồi dưỡng giáo viên phổ thông', 'teacher' => 'thanhtung@khaitriedu.vn', 'room' => 'HT401', 'day_of_week' => 'Sunday', 'slot_date' => Carbon::now()->addDays(15), 'start_time' => '08:00:00', 'end_time' => '10:15:00', 'status' => CourseTimeSlot::STATUS_OPEN_FOR_REGISTRATION, 'note' => 'Khung giờ bồi dưỡng cuối tuần cho giáo viên đang công tác.'],
        ];

        $timeSlots = collect();

        foreach ($fixtures as $index => $fixture) {
            $course = $courses->get($fixture['course']);
            $teacher = $teachers->get($fixture['teacher']);
            $room = $this->room($fixture['room']);

            if (! $course || ! $teacher || ! $room) {
                continue;
            }

            CourseTimeSlot::query()
                ->where('subject_id', $course->subject_id)
                ->delete();

            $timeSlot = CourseTimeSlot::updateOrCreate(
                [
                    'subject_id' => $course->subject_id,
                    'slot_date' => $fixture['slot_date']->toDateString(),
                    'start_time' => $fixture['start_time'],
                    'end_time' => $fixture['end_time'],
                ],
                [
                    'teacher_id' => $teacher->id,
                    'room_id' => $room->id,
                    'day_of_week' => $fixture['day_of_week'],
                    'registration_open_at' => Carbon::now()->subDays(5 + $index),
                    'registration_close_at' => Carbon::now()->addDays(4 + $index),
                    'min_students' => 5,
                    'max_students' => $room->capacity,
                    'status' => $fixture['status'],
                    'note' => $fixture['note'],
                ]
            );

            $timeSlots->put($fixture['key'], $timeSlot->fresh(['subject', 'teacher', 'room']));
        }

        return $timeSlots;
    }

    protected function seedFixedEnrollments(Collection $courses, Collection $students, User $admin): Collection
    {
        $fixtures = [
            ['course' => 'KhaiTriEdu 2026 - Tiếng Anh giao tiếp', 'records' => [['email' => 'nguyen.thi.an@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'tran.gia.han@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'le.minh.quan@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'pham.ngoc.linh@khaitriedu.vn', 'status' => Enrollment::STATUS_SCHEDULED], ['email' => 'vo.hai.nam@khaitriedu.vn', 'status' => Enrollment::STATUS_SCHEDULED], ['email' => 'dang.thanh.truc@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'bui.duc.minh@khaitriedu.vn', 'status' => Enrollment::STATUS_ENROLLED], ['email' => 'huynh.bao.ngoc@khaitriedu.vn', 'status' => Enrollment::STATUS_ENROLLED], ['email' => 'nguyen.khac.hung@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'luu.thanh.vy@khaitriedu.vn', 'status' => Enrollment::STATUS_SCHEDULED], ['email' => 'phan.quynh.anh@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'cao.nhat.minh@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE]]],
            ['course' => 'KhaiTriEdu 2026 - Tin học văn phòng', 'records' => [['email' => 'ta.phuong.nhi@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'vuong.gia.bao@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'duong.thu.ha@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'trinh.minh.tu@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'ho.ngoc.diep@khaitriedu.vn', 'status' => Enrollment::STATUS_SCHEDULED], ['email' => 'le.anh.khoa@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'ngo.bao.tran@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'do.van.tai@khaitriedu.vn', 'status' => Enrollment::STATUS_ENROLLED]]],
            ['course' => 'KhaiTriEdu 2026 - Lập trình Python cơ bản', 'records' => [['email' => 'nguyen.thi.an@khaitriedu.vn', 'status' => Enrollment::STATUS_SCHEDULED], ['email' => 'tran.gia.han@khaitriedu.vn', 'status' => Enrollment::STATUS_SCHEDULED], ['email' => 'le.minh.quan@khaitriedu.vn', 'status' => Enrollment::STATUS_ENROLLED], ['email' => 'pham.ngoc.linh@khaitriedu.vn', 'status' => Enrollment::STATUS_ENROLLED]]],
            ['course' => 'KhaiTriEdu 2026 - Kế toán thực hành', 'records' => [['email' => 'ly.nhat.ha@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'nguyen.hoang.long@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'truong.my.duyen@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'phan.minh.nhat@khaitriedu.vn', 'status' => Enrollment::STATUS_SCHEDULED], ['email' => 'le.dieu.linh@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'do.tuan.kiet@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE]]],
            ['course' => 'KhaiTriEdu 2026 - Báo cáo thuế', 'records' => [['email' => 'nguyen.thi.an@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED], ['email' => 'le.minh.quan@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED], ['email' => 'vo.hai.nam@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED], ['email' => 'dang.thanh.truc@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED], ['email' => 'bui.duc.minh@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED]]],
            ['course' => 'KhaiTriEdu 2026 - Thiết kế Web', 'records' => [['email' => 'nguyen.thi.an@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'le.minh.quan@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'vuong.gia.bao@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'duong.thu.ha@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'ngo.bao.tran@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE]]],
            ['course' => 'KhaiTriEdu 2026 - Điện dân dụng', 'records' => [['email' => 'tran.gia.han@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'pham.ngoc.linh@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'bui.duc.minh@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'huynh.bao.ngoc@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE]]],
            ['course' => 'KhaiTriEdu 2026 - Bồi dưỡng giáo viên phổ thông', 'records' => [['email' => 'nguyen.khac.hung@khaitriedu.vn', 'status' => Enrollment::STATUS_SCHEDULED], ['email' => 'luu.thanh.vy@khaitriedu.vn', 'status' => Enrollment::STATUS_SCHEDULED], ['email' => 'phan.quynh.anh@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE]]],
            ['course' => 'KhaiTriEdu 2026 - Liên thông Đại học - Văn bằng 2 Quản trị kinh doanh', 'records' => [['email' => 'ta.phuong.nhi@khaitriedu.vn', 'status' => Enrollment::STATUS_SCHEDULED], ['email' => 'vuong.gia.bao@khaitriedu.vn', 'status' => Enrollment::STATUS_SCHEDULED], ['email' => 'do.van.tai@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'ly.nhat.ha@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE]]],
            ['course' => 'KhaiTriEdu 2026 - Kỹ thuật chăm sóc da', 'records' => [['email' => 'phan.quynh.anh@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'cao.nhat.minh@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'ta.phuong.nhi@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE]]],
            ['course' => 'KhaiTriEdu 2026 - Thiết kế đồ họa', 'records' => [['email' => 'nguyen.hoang.long@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'truong.my.duyen@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE], ['email' => 'phan.minh.nhat@khaitriedu.vn', 'status' => Enrollment::STATUS_ACTIVE]]],
            ['course' => 'Khóa nội bộ - ANH VĂN KHUNG 6 BẬC', 'records' => [['email' => 'huynh.bao.ngoc@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED], ['email' => 'nguyen.khac.hung@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED], ['email' => 'luu.thanh.vy@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED], ['email' => 'phan.quynh.anh@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED]]],
            ['course' => 'Khóa nội bộ - TIN HỌC VĂN PHÒNG', 'records' => [['email' => 'ta.phuong.nhi@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED], ['email' => 'vuong.gia.bao@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED], ['email' => 'do.van.tai@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED]]],
            ['course' => 'Khóa nội bộ - CHỨNG CHỈ BẤT ĐỘNG SẢN', 'records' => [['email' => 'le.anh.khoa@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED], ['email' => 'ngo.bao.tran@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED], ['email' => 'do.tuan.kiet@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED], ['email' => 'nguyen.hoang.long@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED]]],
            ['course' => 'Khóa nội bộ - BỒI DƯỠNG CHỨC DANH NGHỀ NGHIỆP GIÁO VIÊN', 'records' => [['email' => 'tran.gia.han@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED], ['email' => 'bui.duc.minh@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED]]],
            ['course' => 'Khóa nội bộ - THẠC SĨ QUẢN TRỊ KINH DOANH', 'records' => [['email' => 'nguyen.thi.an@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED], ['email' => 'tran.gia.han@khaitriedu.vn', 'status' => Enrollment::STATUS_COMPLETED]]],
        ];

        $enrollments = collect();

        foreach ($fixtures as $courseIndex => $fixture) {
            $course = $courses->get($fixture['course']);

            if (! $course) {
                continue;
            }

            $classRoom = $course->currentClassRoom();
            $schedule = $classRoom?->schedules->first();
            $teacherId = $classRoom?->teacher_id ?? $course->teacher_id;

            if (! $classRoom) {
                continue;
            }

            foreach ($fixture['records'] as $studentIndex => $record) {
                $student = $students->get($record['email']);

                if (! $student) {
                    continue;
                }

                $submittedAt = Carbon::now()->subDays(24 - $courseIndex - $studentIndex);
                $reviewedAt = $record['status'] === Enrollment::STATUS_PENDING ? null : Carbon::now()->subDays(23 - $courseIndex - $studentIndex);

                $enrollment = Enrollment::updateOrCreate(
                    [
                        'user_id' => $student->id,
                        'lop_hoc_id' => $classRoom->id,
                    ],
                    [
                        'subject_id' => $course->subject_id,
                        'course_id' => $course->id,
                        'assigned_teacher_id' => $teacherId,
                        'preferred_schedule' => null,
                        'status' => $record['status'],
                        'note' => $record['note'] ?? 'Ghi danh lớp cố định demo.',
                        'schedule' => $classRoom->scheduleSummary(),
                        'start_time' => $schedule?->start_time ? substr((string) $schedule->start_time, 0, 8) : null,
                        'end_time' => $schedule?->end_time ? substr((string) $schedule->end_time, 0, 8) : null,
                        'preferred_days' => null,
                        'is_submitted' => true,
                        'submitted_at' => $submittedAt,
                        'reviewed_by' => $record['status'] === Enrollment::STATUS_PENDING ? null : $admin->id,
                        'reviewed_at' => $reviewedAt,
                    ]
                );

                $enrollments->put($this->enrollmentKey($course->title, $student->email), $enrollment->fresh(['student', 'course', 'classRoom']));
            }
        }

        return $enrollments;
    }

    protected function seedPendingEnrollments(Collection $courses, Collection $students): void
    {
        $fixtures = [
            ['course' => 'KhaiTriEdu 2026 - Ứng dụng công nghệ thông tin', 'student' => 'do.tuan.kiet@khaitriedu.vn', 'preferred_days' => ['Tuesday', 'Thursday'], 'preferred_schedule' => '19:00 - 21:00', 'note' => 'Ưu tiên lớp tối để học viên đi làm theo kịp tiến độ.'],
            ['course' => 'KhaiTriEdu 2026 - Marketing Digital căn bản', 'student' => 'vuong.gia.bao@khaitriedu.vn', 'preferred_days' => ['Monday', 'Wednesday', 'Friday'], 'preferred_schedule' => '19:00 - 21:00', 'note' => 'Chờ ghép lớp buổi tối theo lịch làm việc.'],
            ['course' => 'KhaiTriEdu 2026 - Kỹ năng bán hàng chuyên nghiệp', 'student' => 'le.dieu.linh@khaitriedu.vn', 'preferred_days' => ['Saturday', 'Sunday'], 'preferred_schedule' => '08:00 - 10:15', 'note' => 'Muốn học cuối tuần để không ảnh hưởng ca làm.'],
        ];

        foreach ($fixtures as $index => $fixture) {
            $course = $courses->get($fixture['course']);
            $student = $students->get($fixture['student']);

            if (! $course || ! $student) {
                continue;
            }

            Enrollment::updateOrCreate(
                [
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'lop_hoc_id' => null,
                ],
                [
                    'subject_id' => $course->subject_id,
                    'assigned_teacher_id' => $course->teacher_id,
                    'preferred_schedule' => $fixture['preferred_schedule'],
                    'status' => Enrollment::STATUS_PENDING,
                    'note' => $fixture['note'],
                    'schedule' => null,
                    'start_time' => null,
                    'end_time' => null,
                    'preferred_days' => $fixture['preferred_days'],
                    'is_submitted' => true,
                    'submitted_at' => Carbon::now()->subDays(3 + $index),
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ]
            );
        }
    }

    protected function seedSlotRegistrations(Collection $courses, Collection $students, User $admin, Collection $timeSlots): void
    {
        $fixtures = [
            ['student' => 'nguyen.thi.an@khaitriedu.vn', 'course' => 'KhaiTriEdu 2026 - Tiếng Anh giao tiếp', 'status' => SlotRegistration::STATUS_PENDING, 'note' => 'Muốn học tối thứ 2, 4, 6 để dễ sắp xếp công việc.', 'choices' => ['english_evening', 'teacher_boost_open', 'it_office_evening']],
            ['student' => 'ta.phuong.nhi@khaitriedu.vn', 'course' => 'KhaiTriEdu 2026 - Tin học văn phòng', 'status' => SlotRegistration::STATUS_RECORDED, 'note' => 'Đã ghi nhận nhu cầu học buổi tối sau giờ làm.', 'choices' => ['it_office_evening', 'english_evening']],
            ['student' => 'le.minh.quan@khaitriedu.vn', 'course' => 'KhaiTriEdu 2026 - Kế toán thực hành', 'status' => SlotRegistration::STATUS_SCHEDULED, 'note' => 'Đã xếp vào nhóm học tối trong tuần.', 'choices' => ['accounting_ready', 'web_ready']],
            ['student' => 'phan.quynh.anh@khaitriedu.vn', 'course' => 'KhaiTriEdu 2026 - Thiết kế Web', 'status' => SlotRegistration::STATUS_NEEDS_RESELECT, 'note' => 'Cần chọn lại vì trùng ca với công việc hiện tại.', 'choices' => ['web_ready', 'teacher_boost_open']],
            ['student' => 'do.tuan.kiet@khaitriedu.vn', 'course' => 'KhaiTriEdu 2026 - Điện dân dụng', 'status' => SlotRegistration::STATUS_REJECTED, 'note' => 'Chưa đủ điều kiện đầu vào nên tạm từ chối.', 'choices' => ['electric_class_opened']],
        ];

        foreach ($fixtures as $index => $fixture) {
            $course = $courses->get($fixture['course']);
            $student = $students->get($fixture['student']);

            if (! $course || ! $student) {
                continue;
            }

            $registration = SlotRegistration::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'subject_id' => $course->subject_id,
                    'note' => $fixture['note'],
                ],
                [
                    'status' => $fixture['status'],
                    'reviewed_by' => in_array($fixture['status'], [
                        SlotRegistration::STATUS_RECORDED,
                        SlotRegistration::STATUS_SCHEDULED,
                        SlotRegistration::STATUS_NEEDS_RESELECT,
                        SlotRegistration::STATUS_REJECTED,
                    ], true) ? $admin->id : null,
                    'reviewed_at' => in_array($fixture['status'], [
                        SlotRegistration::STATUS_RECORDED,
                        SlotRegistration::STATUS_SCHEDULED,
                        SlotRegistration::STATUS_NEEDS_RESELECT,
                        SlotRegistration::STATUS_REJECTED,
                    ], true) ? Carbon::now()->subDays(7 - $index) : null,
                    'created_at' => Carbon::now()->subDays(9 - $index),
                    'updated_at' => Carbon::now()->subDays(9 - $index),
                ]
            );

            SlotRegistrationChoice::query()
                ->where('slot_registration_id', $registration->id)
                ->delete();

            foreach ($fixture['choices'] as $priority => $timeSlotKey) {
                $timeSlot = $timeSlots->get($timeSlotKey);

                if (! $timeSlot) {
                    continue;
                }

                SlotRegistrationChoice::updateOrCreate(
                    [
                        'slot_registration_id' => $registration->id,
                        'course_time_slot_id' => $timeSlot->id,
                    ],
                    [
                        'priority' => $priority + 1,
                    ]
                );
            }
        }
    }

    protected function seedCustomScheduleRequests(Collection $courses, Collection $students, Collection $teachers, User $admin): void
    {
        $fixtures = [
            ['student' => 'do.tuan.kiet@khaitriedu.vn', 'course' => 'KhaiTriEdu 2026 - Marketing Digital căn bản', 'teacher' => 'quochuy@khaitriedu.vn', 'days' => ['Monday', 'Wednesday', 'Friday'], 'time' => '20:30 - 22:30', 'status' => 'pending', 'notes' => 'Muốn học sau 20:30 để tránh trùng với lớp buổi tối hiện tại.', 'reviewed' => false],
            ['student' => 'vuong.gia.bao@khaitriedu.vn', 'course' => 'KhaiTriEdu 2026 - Tiếng Anh thiếu nhi', 'teacher' => 'hongloan@khaitriedu.vn', 'days' => ['Saturday', 'Sunday'], 'time' => '08:00 - 10:15', 'status' => 'approved', 'notes' => 'Phù hợp nhóm học sinh tiểu học học cuối tuần.', 'reviewed' => true],
            ['student' => 'le.dieu.linh@khaitriedu.vn', 'course' => 'KhaiTriEdu 2026 - Kỹ năng bán hàng chuyên nghiệp', 'teacher' => 'thaison@khaitriedu.vn', 'days' => ['Saturday', 'Sunday'], 'time' => '08:00 - 10:15', 'status' => 'rejected', 'notes' => 'Tạm hoãn vì chưa ghép được nhóm học cuối tuần.', 'reviewed' => true],
        ];

        foreach ($fixtures as $index => $fixture) {
            $course = $courses->get($fixture['course']);
            $student = $students->get($fixture['student']);
            $teacher = $teachers->get($fixture['teacher']);

            if (! $course || ! $student || ! $teacher) {
                continue;
            }

            CustomScheduleRequest::query()
                ->where('student_id', $student->id)
                ->where('subject_id', $course->subject_id)
                ->delete();

            CustomScheduleRequest::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'subject_id' => $course->subject_id,
                    'course_id' => $course->id,
                ],
                [
                    'preferred_teacher_id' => $teacher->id,
                    'requested_days' => $fixture['days'],
                    'requested_time' => $fixture['time'],
                    'status' => $fixture['status'],
                    'notes' => $fixture['notes'],
                    'reviewed_by' => $fixture['reviewed'] ? $admin->id : null,
                    'reviewed_at' => $fixture['reviewed'] ? Carbon::now()->subDays(6 - $index) : null,
                ]
            );
        }
    }

    protected function seedScheduleChangeRequests(Collection $courses, User $admin): void
    {
        $fixtures = [
            ['course' => 'KhaiTriEdu 2026 - Tiếng Anh giao tiếp', 'room' => 'PH102', 'day_of_week' => 'Monday', 'start_time' => '18:00:00', 'end_time' => '20:15:00', 'status' => ScheduleChangeRequest::STATUS_PENDING, 'reason' => 'Cần đổi sang phòng có màn hình lớn hơn để tiện luyện nghe.'],
            ['course' => 'KhaiTriEdu 2026 - Tin học văn phòng', 'room' => 'MT302', 'day_of_week' => 'Tuesday', 'start_time' => '18:00:00', 'end_time' => '20:00:00', 'status' => ScheduleChangeRequest::STATUS_APPROVED, 'reason' => 'Đổi sang phòng máy dự phòng để tăng trải nghiệm thực hành.', 'admin_note' => 'Đã duyệt theo đề xuất của giảng viên.'],
            ['course' => 'KhaiTriEdu 2026 - Thiết kế Web', 'room' => 'MT301', 'day_of_week' => 'Sunday', 'start_time' => '19:00:00', 'end_time' => '21:15:00', 'status' => ScheduleChangeRequest::STATUS_REJECTED, 'reason' => 'Đề xuất chuyển sang phòng máy khác nhưng lịch phòng đã kín.', 'admin_note' => 'Giữ nguyên lịch cũ để không ảnh hưởng lớp khác.'],
            ['course' => 'KhaiTriEdu 2026 - Bồi dưỡng giáo viên phổ thông', 'room' => 'PH102', 'day_of_week' => 'Sunday', 'start_time' => '08:00:00', 'end_time' => '10:15:00', 'status' => ScheduleChangeRequest::STATUS_PENDING, 'reason' => 'Muốn tách buổi thảo luận sang phòng hội thảo yên tĩnh hơn.'],
        ];

        foreach ($fixtures as $index => $fixture) {
            $course = $courses->get($fixture['course']);
            $classRoom = $course?->currentClassRoom();
            $requestedRoom = $this->room($fixture['room']);
            $schedule = $classRoom?->schedules->first();

            if (! $course || ! $classRoom || ! $requestedRoom) {
                continue;
            }

            ScheduleChangeRequest::updateOrCreate(
                [
                    'teacher_id' => $classRoom->teacher_id,
                    'class_room_id' => $classRoom->id,
                    'requested_start_time' => $fixture['start_time'],
                ],
                [
                    'course_id' => $course->id,
                    'class_schedule_id' => $schedule?->id,
                    'requested_room_id' => $requestedRoom->id,
                    'current_schedule' => $classRoom->scheduleSummary(),
                    'requested_day_of_week' => $fixture['day_of_week'],
                    'requested_date' => Carbon::now()->addDays(7 + $index)->toDateString(),
                    'requested_end_date' => Carbon::now()->addMonths(2 + $index)->toDateString(),
                    'requested_start_time' => $fixture['start_time'],
                    'requested_end_time' => $fixture['end_time'],
                    'reason' => $fixture['reason'],
                    'status' => $fixture['status'],
                    'admin_note' => $fixture['admin_note'] ?? null,
                    'reviewed_by' => in_array($fixture['status'], [ScheduleChangeRequest::STATUS_APPROVED, ScheduleChangeRequest::STATUS_REJECTED], true) ? $admin->id : null,
                    'reviewed_at' => in_array($fixture['status'], [ScheduleChangeRequest::STATUS_APPROVED, ScheduleChangeRequest::STATUS_REJECTED], true) ? Carbon::now()->subDays(4 - $index) : null,
                ]
            );
        }
    }

    protected function seedAttendanceRecords(Collection $courses, Collection $students, Collection $enrollments): void
    {
        $fixtures = [
            [
                'course' => 'KhaiTriEdu 2026 - Tiếng Anh giao tiếp',
                'schedule_index' => 0,
                'date' => Carbon::now()->subDays(2),
                'rows' => [
                    ['email' => 'nguyen.thi.an@khaitriedu.vn', 'status' => AttendanceRecord::STATUS_PRESENT, 'note' => 'Có mặt sớm 5 phút.'],
                    ['email' => 'tran.gia.han@khaitriedu.vn', 'status' => AttendanceRecord::STATUS_PRESENT, 'note' => 'Tham gia đầy đủ.'],
                    ['email' => 'le.minh.quan@khaitriedu.vn', 'status' => AttendanceRecord::STATUS_LATE, 'note' => 'Đến trễ 10 phút do kẹt xe.'],
                    ['email' => 'pham.ngoc.linh@khaitriedu.vn', 'status' => AttendanceRecord::STATUS_EXCUSED, 'note' => 'Có xin phép trước.'],
                    ['email' => 'vo.hai.nam@khaitriedu.vn', 'status' => AttendanceRecord::STATUS_PRESENT, 'note' => 'Hoàn thành bài tập miệng.'],
                    ['email' => 'dang.thanh.truc@khaitriedu.vn', 'status' => AttendanceRecord::STATUS_ABSENT, 'note' => 'Vắng không báo trước.'],
                ],
            ],
            [
                'course' => 'KhaiTriEdu 2026 - Tiếng Anh giao tiếp',
                'schedule_index' => 1,
                'date' => Carbon::now()->subDays(5),
                'rows' => [
                    ['email' => 'bui.duc.minh@khaitriedu.vn', 'status' => AttendanceRecord::STATUS_PRESENT, 'note' => 'Tích cực phát biểu.'],
                    ['email' => 'huynh.bao.ngoc@khaitriedu.vn', 'status' => AttendanceRecord::STATUS_PRESENT, 'note' => 'Chuẩn bị bài tốt.'],
                    ['email' => 'nguyen.khac.hung@khaitriedu.vn', 'status' => AttendanceRecord::STATUS_LATE, 'note' => 'Đến trễ 7 phút.'],
                    ['email' => 'luu.thanh.vy@khaitriedu.vn', 'status' => AttendanceRecord::STATUS_PRESENT, 'note' => 'Làm việc nhóm tốt.'],
                    ['email' => 'phan.quynh.anh@khaitriedu.vn', 'status' => AttendanceRecord::STATUS_EXCUSED, 'note' => 'Nghỉ có phép vì công tác.'],
                    ['email' => 'cao.nhat.minh@khaitriedu.vn', 'status' => AttendanceRecord::STATUS_PRESENT, 'note' => 'Hoàn thành đủ hoạt động.'],
                ],
            ],
            [
                'course' => 'KhaiTriEdu 2026 - Tin học văn phòng',
                'schedule_index' => 0,
                'date' => Carbon::now()->subDays(1),
                'rows' => [
                    ['email' => 'ta.phuong.nhi@khaitriedu.vn', 'status' => AttendanceRecord::STATUS_PRESENT, 'note' => 'Thực hành Word tốt.'],
                    ['email' => 'vuong.gia.bao@khaitriedu.vn', 'status' => AttendanceRecord::STATUS_PRESENT, 'note' => 'Hoàn thành bài Excel.'],
                    ['email' => 'duong.thu.ha@khaitriedu.vn', 'status' => AttendanceRecord::STATUS_LATE, 'note' => 'Vào lớp muộn.'],
                    ['email' => 'trinh.minh.tu@khaitriedu.vn', 'status' => AttendanceRecord::STATUS_PRESENT, 'note' => 'Làm bài thực hành đầy đủ.'],
                ],
            ],
        ];

        foreach ($fixtures as $fixture) {
            $course = $courses->get($fixture['course']);
            $classRoom = $course?->currentClassRoom();

            if (! $course || ! $classRoom) {
                continue;
            }

            $schedule = $classRoom->schedules->get($fixture['schedule_index']) ?? $classRoom->schedules->first();

            if (! $schedule) {
                continue;
            }

            foreach ($fixture['rows'] as $row) {
                $student = $students->get($row['email']);
                $enrollment = $enrollments->get($this->enrollmentKey($course->title, $row['email']));

                if (! $student || ! $enrollment) {
                    continue;
                }

                AttendanceRecord::updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'student_id' => $student->id,
                        'attendance_date' => $fixture['date']->toDateString(),
                    ],
                    [
                        'class_room_id' => $classRoom->id,
                        'class_schedule_id' => $schedule->id,
                        'enrollment_id' => $enrollment->id,
                        'teacher_id' => $classRoom->teacher_id,
                        'status' => $row['status'],
                        'note' => $row['note'],
                        'recorded_at' => $fixture['date']->copy()->setTime(21, 0),
                    ]
                );
            }
        }
    }

    protected function seedGrades(Collection $courses, Collection $students, Collection $enrollments): void
    {
        $fixtures = [
            [
                'course' => 'KhaiTriEdu 2026 - Tiếng Anh giao tiếp',
                'module_index' => 0,
                'test_name' => 'Bài kiểm tra giữa khóa',
                'rows' => [
                    ['email' => 'nguyen.thi.an@khaitriedu.vn', 'score' => 92.5, 'feedback' => 'Phát âm tự nhiên, phản xạ tốt.'],
                    ['email' => 'tran.gia.han@khaitriedu.vn', 'score' => 84.0, 'feedback' => 'Nắm vững mẫu câu giao tiếp.'],
                    ['email' => 'le.minh.quan@khaitriedu.vn', 'score' => 78.5, 'feedback' => 'Cần luyện nghe thêm.'],
                    ['email' => 'pham.ngoc.linh@khaitriedu.vn', 'score' => 69.0, 'feedback' => 'Đã cải thiện rõ rệt.'],
                    ['email' => 'vo.hai.nam@khaitriedu.vn', 'score' => 58.0, 'feedback' => 'Cần tự tin hơn khi nói.'],
                    ['email' => 'dang.thanh.truc@khaitriedu.vn', 'score' => 48.5, 'feedback' => 'Nên ôn lại từ vựng nền tảng.'],
                ],
            ],
            [
                'course' => 'KhaiTriEdu 2026 - Tin học văn phòng',
                'module_index' => 1,
                'test_name' => 'Bài kiểm tra Word và Excel',
                'rows' => [
                    ['email' => 'ta.phuong.nhi@khaitriedu.vn', 'score' => 95.0, 'feedback' => 'Xử lý file và định dạng rất tốt.'],
                    ['email' => 'vuong.gia.bao@khaitriedu.vn', 'score' => 88.5, 'feedback' => 'Công thức Excel chắc tay.'],
                    ['email' => 'duong.thu.ha@khaitriedu.vn', 'score' => 73.0, 'feedback' => 'Đã hiểu thao tác cơ bản.'],
                    ['email' => 'trinh.minh.tu@khaitriedu.vn', 'score' => 65.5, 'feedback' => 'Cần thêm thời gian thực hành.'],
                    ['email' => 'ho.ngoc.diep@khaitriedu.vn', 'score' => 54.0, 'feedback' => 'Nắm được các bước chính.'],
                ],
            ],
            [
                'course' => 'KhaiTriEdu 2026 - Kế toán thực hành',
                'module_index' => 0,
                'test_name' => 'Bài thực hành chứng từ',
                'rows' => [
                    ['email' => 'ly.nhat.ha@khaitriedu.vn', 'score' => 89.0, 'feedback' => 'Lập chứng từ khá chính xác.'],
                    ['email' => 'nguyen.hoang.long@khaitriedu.vn', 'score' => 81.0, 'feedback' => 'Bút toán ổn, cần cẩn thận hơn.'],
                    ['email' => 'truong.my.duyen@khaitriedu.vn', 'score' => 76.0, 'feedback' => 'Kỹ năng hạch toán đang tốt lên.'],
                    ['email' => 'phan.minh.nhat@khaitriedu.vn', 'score' => 67.5, 'feedback' => 'Nên rà soát số liệu cuối bài.'],
                ],
            ],
        ];

        foreach ($fixtures as $fixture) {
            $course = $courses->get($fixture['course']);
            $classRoom = $course?->currentClassRoom();
            $module = $course?->modules->get($fixture['module_index']);

            if (! $course || ! $classRoom || ! $module) {
                continue;
            }

            foreach ($fixture['rows'] as $row) {
                $student = $students->get($row['email']);
                $enrollment = $enrollments->get($this->enrollmentKey($course->title, $row['email']));

                if (! $student || ! $enrollment) {
                    continue;
                }

                $score = (float) $row['score'];

                Grade::updateOrCreate(
                    [
                        'class_room_id' => $classRoom->id,
                        'student_id' => $student->id,
                        'test_name' => $fixture['test_name'],
                    ],
                    [
                        'enrollment_id' => $enrollment->id,
                        'module_id' => $module->id,
                        'teacher_id' => $classRoom->teacher_id,
                        'score' => $score,
                        'grade' => $this->gradeLabel($score),
                        'feedback' => $row['feedback'],
                    ]
                );
            }
        }
    }

    protected function seedTeacherEvaluations(Collection $courses, Collection $students): void
    {
        $fixtures = [
            ['course' => 'KhaiTriEdu 2026 - Tiếng Anh giao tiếp', 'rows' => [['email' => 'nguyen.thi.an@khaitriedu.vn', 'rating' => 5, 'comments' => 'Chủ động giao tiếp, làm bài tập đầy đủ và hỗ trợ bạn cùng lớp.'], ['email' => 'tran.gia.han@khaitriedu.vn', 'rating' => 4, 'comments' => 'Học nghiêm túc, phát biểu tốt nhưng cần ổn định nhịp độ làm bài.']]],
            ['course' => 'KhaiTriEdu 2026 - Tin học văn phòng', 'rows' => [['email' => 'ta.phuong.nhi@khaitriedu.vn', 'rating' => 5, 'comments' => 'Thao tác phần mềm thành thạo, nộp bài đúng hạn và thái độ tích cực.'], ['email' => 'vuong.gia.bao@khaitriedu.vn', 'rating' => 4, 'comments' => 'Có tiến bộ qua từng buổi, cần chủ động hỏi thêm khi gặp lỗi khó.']]],
            ['course' => 'KhaiTriEdu 2026 - Thiết kế Web', 'rows' => [['email' => 'nguyen.hoang.long@khaitriedu.vn', 'rating' => 5, 'comments' => 'Làm dự án nghiêm túc, phối hợp nhóm tốt và hoàn thành đúng tiến độ.']]],
            ['course' => 'KhaiTriEdu 2026 - Báo cáo thuế', 'rows' => [['email' => 'nguyen.thi.an@khaitriedu.vn', 'rating' => 5, 'comments' => 'Nắm chắc quy trình nghiệp vụ, bài làm chính xác và có tinh thần học tập cao.']]],
            ['course' => 'KhaiTriEdu 2026 - Bồi dưỡng giáo viên phổ thông', 'rows' => [['email' => 'nguyen.khac.hung@khaitriedu.vn', 'rating' => 4, 'comments' => 'Tham gia đầy đủ, trao đổi tích cực và áp dụng tốt sau mỗi chuyên đề.']]],
        ];

        foreach ($fixtures as $fixture) {
            $course = $courses->get($fixture['course']);
            $classRoom = $course?->currentClassRoom();

            if (! $course || ! $classRoom) {
                continue;
            }

            foreach ($fixture['rows'] as $row) {
                $student = $students->get($row['email']);

                if (! $student) {
                    continue;
                }

                TeacherEvaluation::updateOrCreate(
                    [
                        'class_room_id' => $classRoom->id,
                        'student_id' => $student->id,
                        'teacher_id' => $classRoom->teacher_id,
                    ],
                    [
                        'rating' => (int) $row['rating'],
                        'comments' => $row['comments'],
                    ]
                );
            }
        }
    }

    protected function seedCertificates(Collection $courses, Collection $students): void
    {
        $fixtures = [
            ['course' => 'KhaiTriEdu 2026 - Tiếng Anh thiếu nhi', 'students' => [['email' => 'nguyen.hoang.long@khaitriedu.vn', 'number' => 'KTE-CERT-2026-ENG-001', 'score' => 89.5], ['email' => 'truong.my.duyen@khaitriedu.vn', 'number' => 'KTE-CERT-2026-ENG-002', 'score' => 91.0]]],
            ['course' => 'KhaiTriEdu 2026 - Báo cáo thuế', 'students' => [['email' => 'nguyen.thi.an@khaitriedu.vn', 'number' => 'KTE-CERT-2026-TAX-001', 'score' => 87.0], ['email' => 'le.minh.quan@khaitriedu.vn', 'number' => 'KTE-CERT-2026-TAX-002', 'score' => 84.5]]],
            ['course' => 'Khóa nội bộ - ANH VĂN KHUNG 6 BẬC', 'students' => [['email' => 'huynh.bao.ngoc@khaitriedu.vn', 'number' => 'KTE-CERT-2026-ENG6-001', 'score' => 90.0], ['email' => 'nguyen.khac.hung@khaitriedu.vn', 'number' => 'KTE-CERT-2026-ENG6-002', 'score' => 88.0]]],
            ['course' => 'Khóa nội bộ - CHỨNG CHỈ BẤT ĐỘNG SẢN', 'students' => [['email' => 'le.anh.khoa@khaitriedu.vn', 'number' => 'KTE-CERT-2026-BDS-001', 'score' => 86.0]]],
        ];

        foreach ($fixtures as $fixture) {
            $course = $courses->get($fixture['course']);

            if (! $course) {
                continue;
            }

            foreach ($fixture['students'] as $index => $row) {
                $student = $students->get($row['email']);

                if (! $student) {
                    continue;
                }

                Certificate::updateOrCreate(
                    [
                        'certificate_number' => $row['number'],
                    ],
                    [
                        'user_id' => $student->id,
                        'course_id' => $course->id,
                        'file_path' => 'certificates/' . Str::slug($row['number']) . '.pdf',
                        'score' => $row['score'],
                        'issued_at' => Carbon::now()->subDays(8 + $index),
                        'expires_at' => Carbon::now()->addYear(),
                        'status' => 'issued',
                    ]
                );
            }
        }
    }

    protected function seedQuizData(Collection $courses, Collection $students): void
    {
        $course = $courses->get('KhaiTriEdu 2026 - Tiếng Anh giao tiếp');
        $lesson = $course?->modules->first()?->lessons->first();

        if (! $course || ! $lesson) {
            return;
        }

        $quiz = Quiz::updateOrCreate(
            [
                'lesson_id' => $lesson->id,
                'title' => 'Kiểm tra nhập môn giao tiếp',
            ],
            [
                'description' => 'Đánh giá khả năng chào hỏi, giới thiệu bản thân và phản xạ cơ bản.',
                'passing_score' => 70,
                'is_required' => true,
                'max_attempts' => 3,
            ]
        );

        $questions = [
            [
                'order' => 1,
                'type' => 'multiple_choice',
                'question' => 'Khi chào hỏi khách hàng, câu nói nào phù hợp nhất?',
                'description' => 'Chọn phương án lịch sự và tự nhiên.',
                'points' => 2,
                'options' => [
                    ['text' => 'Xin chào, tôi có thể hỗ trợ gì cho anh/chị?', 'correct' => true],
                    ['text' => 'Bạn cần gì?', 'correct' => false],
                    ['text' => 'Nói nhanh cho xong nhé.', 'correct' => false],
                    ['text' => 'Tôi bận lắm.', 'correct' => false],
                ],
            ],
            [
                'order' => 2,
                'type' => 'true_false',
                'question' => 'Ngữ điệu thân thiện giúp cuộc hội thoại trở nên dễ tiếp nhận hơn.',
                'description' => 'Đánh dấu đúng hoặc sai.',
                'points' => 1,
                'options' => [
                    ['text' => 'Đúng', 'correct' => true],
                    ['text' => 'Sai', 'correct' => false],
                ],
            ],
            [
                'order' => 3,
                'type' => 'multiple_choice',
                'question' => 'Từ nào sau đây là lời giới thiệu bản thân tự nhiên?',
                'description' => 'Chọn câu đầy đủ ý nhất.',
                'points' => 2,
                'options' => [
                    ['text' => 'My name is An and I work in accounting.', 'correct' => true],
                    ['text' => 'I am fine, thanks.', 'correct' => false],
                    ['text' => 'See you later.', 'correct' => false],
                    ['text' => 'Please wait.', 'correct' => false],
                ],
            ],
            [
                'order' => 4,
                'type' => 'short_answer',
                'question' => 'Hãy viết một câu giới thiệu bản thân ngắn bằng tiếng Anh.',
                'description' => 'Câu trả lời ngắn gọn, rõ nghĩa.',
                'points' => 3,
                'options' => [],
            ],
        ];

        $questionModels = collect();

        foreach ($questions as $questionFixture) {
            $question = Question::updateOrCreate(
                [
                    'quiz_id' => $quiz->id,
                    'order' => $questionFixture['order'],
                ],
                [
                    'question' => $questionFixture['question'],
                    'description' => $questionFixture['description'],
                    'type' => $questionFixture['type'],
                    'points' => $questionFixture['points'],
                ]
            );

            foreach ($questionFixture['options'] as $optionIndex => $optionFixture) {
                Option::updateOrCreate(
                    [
                        'question_id' => $question->id,
                        'order' => $optionIndex + 1,
                    ],
                    [
                        'option_text' => $optionFixture['text'],
                        'is_correct' => (bool) $optionFixture['correct'],
                    ]
                );
            }

            $questionModels->put($questionFixture['order'], $question->fresh(['options']));
        }

        $attempts = [
            [
                'student' => 'nguyen.thi.an@khaitriedu.vn',
                'answers' => [
                    1 => 1,
                    2 => 1,
                    3 => 1,
                    4 => ['text' => 'My name is An and I work at KhaiTriEdu.', 'correct' => true],
                ],
            ],
            [
                'student' => 'tran.gia.han@khaitriedu.vn',
                'answers' => [
                    1 => 1,
                    2 => 1,
                    3 => 1,
                    4 => ['text' => 'My name is Han and I study English.', 'correct' => true],
                ],
            ],
        ];

        foreach ($attempts as $attemptIndex => $attemptFixture) {
            $student = $students->get($attemptFixture['student']);

            if (! $student) {
                continue;
            }

            foreach ($attemptFixture['answers'] as $order => $answerValue) {
                $question = $questionModels->get($order);

                if (! $question) {
                    continue;
                }

                $payload = [
                    'user_id' => $student->id,
                    'quiz_id' => $quiz->id,
                    'question_id' => $question->id,
                    'attempt' => 1,
                    'is_correct' => false,
                    'option_id' => null,
                    'answer_text' => null,
                ];

                if ($question->type === 'short_answer') {
                    $payload['answer_text'] = $answerValue['text'] ?? null;
                    $payload['is_correct'] = (bool) ($answerValue['correct'] ?? false);
                } else {
                    $option = $question->options->sortBy('order')->values()->get((int) $answerValue - 1);

                    if ($option) {
                        $payload['option_id'] = $option->id;
                        $payload['is_correct'] = (bool) $option->is_correct;
                    }
                }

                QuizAnswer::updateOrCreate(
                    [
                        'user_id' => $student->id,
                        'quiz_id' => $quiz->id,
                        'question_id' => $question->id,
                        'attempt' => 1,
                    ],
                    $payload
                );
            }
        }
    }

    protected function seedLessonProgress(Collection $courses, Collection $students): void
    {
        $fixtures = [
            [
                'course' => 'KhaiTriEdu 2026 - Tiếng Anh giao tiếp',
                'lesson_index' => 0,
                'rows' => [
                    ['email' => 'nguyen.thi.an@khaitriedu.vn', 'completed' => true, 'time' => 55],
                    ['email' => 'tran.gia.han@khaitriedu.vn', 'completed' => true, 'time' => 48],
                    ['email' => 'le.minh.quan@khaitriedu.vn', 'completed' => false, 'time' => 25],
                ],
            ],
            [
                'course' => 'KhaiTriEdu 2026 - Tin học văn phòng',
                'lesson_index' => 1,
                'rows' => [
                    ['email' => 'ta.phuong.nhi@khaitriedu.vn', 'completed' => true, 'time' => 50],
                    ['email' => 'vuong.gia.bao@khaitriedu.vn', 'completed' => false, 'time' => 35],
                ],
            ],
        ];

        foreach ($fixtures as $fixture) {
            $course = $courses->get($fixture['course']);
            $lesson = $course?->modules->get($fixture['lesson_index'])?->lessons->first();

            if (! $course || ! $lesson) {
                continue;
            }

            foreach ($fixture['rows'] as $index => $row) {
                $student = $students->get($row['email']);

                if (! $student) {
                    continue;
                }

                LessonProgress::updateOrCreate(
                    [
                        'user_id' => $student->id,
                        'lesson_id' => $lesson->id,
                    ],
                    [
                        'is_completed' => $row['completed'],
                        'time_spent' => $row['time'],
                        'started_at' => Carbon::now()->subDays(6 + $index),
                        'completed_at' => $row['completed'] ? Carbon::now()->subDays(5 + $index) : null,
                    ]
                );
            }
        }
    }

    protected function seedNotifications(Collection $courses, Collection $students, Collection $teachers): void
    {
        $fixtures = [
            ['user' => 'anhdung@khaitriedu.vn', 'title' => 'Lớp Tiếng Anh giao tiếp đã đủ 12 học viên', 'message' => 'Lớp hiện đã đạt sĩ số tối đa tại phòng lý thuyết A1. Hệ thống sẵn sàng chuyển sang trạng thái mở lớp.', 'type' => 'success', 'link' => route('teacher.classes.show', $courses->get('KhaiTriEdu 2026 - Tiếng Anh giao tiếp')?->currentClassRoom())],
            ['user' => 'baochau@khaitriedu.vn', 'title' => 'Tin học văn phòng có lịch mới cần kiểm tra', 'message' => 'Một số học viên vừa chọn lại khung giờ phù hợp hơn cho lớp Tin học văn phòng.', 'type' => 'info', 'link' => route('teacher.schedule-change-requests.index')],
            ['user' => 'thuylinh@khaitriedu.vn', 'title' => 'Thiết kế Web có yêu cầu đổi phòng', 'message' => 'Phòng máy của lớp Thiết kế Web đã có đề xuất đổi ca để tăng thời gian thực hành.', 'type' => 'warning', 'link' => route('teacher.schedule-change-requests.index')],
            ['user' => 'minhtu@khaitriedu.vn', 'title' => 'Bảng điểm Kế toán thực hành đã cập nhật', 'message' => 'Điểm giữa khóa của nhóm Kế toán thực hành đã được nhập đầy đủ.', 'type' => 'success', 'link' => route('teacher.classes.show', $courses->get('KhaiTriEdu 2026 - Kế toán thực hành')?->currentClassRoom())],
            ['user' => 'nguyen.thi.an@khaitriedu.vn', 'title' => 'Bạn đã được xếp vào lớp Tiếng Anh giao tiếp', 'message' => 'Lớp học của bạn đã có lịch cố định, có thể xem ngay trong trang khóa học.', 'type' => 'success', 'link' => route('courses.show', $courses->get('KhaiTriEdu 2026 - Tiếng Anh giao tiếp'))],
            ['user' => 'ta.phuong.nhi@khaitriedu.vn', 'title' => 'Tin học văn phòng đã có lịch học mới', 'message' => 'Khung giờ học của lớp Tin học văn phòng đã được chốt trên phòng máy tính 1.', 'type' => 'info', 'link' => route('courses.show', $courses->get('KhaiTriEdu 2026 - Tin học văn phòng'))],
            ['user' => 'nguyen.hoang.long@khaitriedu.vn', 'title' => 'Chứng chỉ Báo cáo thuế sẵn sàng cấp', 'message' => 'Bạn đã hoàn tất khóa Báo cáo thuế và đủ điều kiện nhận chứng chỉ.', 'type' => 'success', 'link' => route('courses.show', $courses->get('KhaiTriEdu 2026 - Báo cáo thuế'))],
            ['user' => 'le.dieu.linh@khaitriedu.vn', 'title' => 'Đang chờ ghép lớp cuối tuần', 'message' => 'Hệ thống đã ghi nhận mong muốn học cuối tuần của bạn, vui lòng chờ admin sắp xếp.', 'type' => 'warning', 'link' => route('student.schedule')],
        ];

        foreach ($fixtures as $index => $fixture) {
            $user = $this->user($fixture['user']);

            if (! $user) {
                continue;
            }

            Notification::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'title' => $fixture['title'],
                ],
                [
                    'message' => $fixture['message'],
                    'type' => $fixture['type'],
                    'link' => $fixture['link'],
                    'is_read' => $index % 2 === 0,
                    'read_at' => $index % 2 === 0 ? Carbon::now()->subDays(2 + $index) : null,
                ]
            );
        }
    }

    protected function seedReviews(Collection $courses, Collection $students): void
    {
        $fixtures = [
            ['course' => 'KhaiTriEdu 2026 - Tiếng Anh giao tiếp', 'student' => 'nguyen.thi.an@khaitriedu.vn', 'rating' => 5, 'comment' => 'Giảng viên gần gũi, bài học rất thực tế.'],
            ['course' => 'KhaiTriEdu 2026 - Tin học văn phòng', 'student' => 'ta.phuong.nhi@khaitriedu.vn', 'rating' => 4, 'comment' => 'Có nhiều bài tập thực hành dễ áp dụng.'],
            ['course' => 'KhaiTriEdu 2026 - Thiết kế Web', 'student' => 'nguyen.hoang.long@khaitriedu.vn', 'rating' => 5, 'comment' => 'Học theo dự án nên rất dễ hình dung.'],
            ['course' => 'KhaiTriEdu 2026 - Kế toán thực hành', 'student' => 'ly.nhat.ha@khaitriedu.vn', 'rating' => 4, 'comment' => 'Bài giảng rõ ràng, có ví dụ doanh nghiệp thật.'],
            ['course' => 'KhaiTriEdu 2026 - Báo cáo thuế', 'student' => 'nguyen.thi.an@khaitriedu.vn', 'rating' => 5, 'comment' => 'Nội dung sát nghiệp vụ, dễ hiểu.'],
            ['course' => 'KhaiTriEdu 2026 - Bồi dưỡng giáo viên phổ thông', 'student' => 'nguyen.khac.hung@khaitriedu.vn', 'rating' => 4, 'comment' => 'Phù hợp với người đang đi dạy.'],
            ['course' => 'KhaiTriEdu 2026 - Liên thông Đại học - Văn bằng 2 Quản trị kinh doanh', 'student' => 'vuong.gia.bao@khaitriedu.vn', 'rating' => 4, 'comment' => 'Lộ trình học rõ ràng, có định hướng đầu ra.'],
            ['course' => 'KhaiTriEdu 2026 - Tiếng Anh thiếu nhi', 'student' => 'truong.my.duyen@khaitriedu.vn', 'rating' => 5, 'comment' => 'Con rất thích cách dạy sinh động.'],
        ];

        foreach ($fixtures as $index => $fixture) {
            $course = $courses->get($fixture['course']);
            $student = $students->get($fixture['student']);

            if (! $course || ! $student) {
                continue;
            }

            Review::updateOrCreate(
                [
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                ],
                [
                    'rating' => $fixture['rating'],
                    'comment' => $fixture['comment'],
                    'created_at' => Carbon::now()->subDays(11 - $index),
                    'updated_at' => Carbon::now()->subDays(11 - $index),
                ]
            );
        }
    }

    protected function markFullClassRooms(Collection $courses): void
    {
        foreach ($courses as $course) {
            $classRoom = $course->currentClassRoom();

            if (! $classRoom || $classRoom->status === ClassRoom::STATUS_COMPLETED) {
                continue;
            }

            $enrolledCount = $classRoom->enrollments()
                ->whereIn('status', Enrollment::courseAccessStatuses())
                ->count();

            if ($classRoom->room && $enrolledCount >= $classRoom->room->capacity) {
                $classRoom->update(['status' => ClassRoom::STATUS_FULL]);
            }
        }
    }

    protected function seedClassRoomStatusSamples(Collection $courses): void
    {
        $fixtures = [
            [
                'course' => 'KhaiTriEdu 2026 - Tiếng Anh giao tiếp',
                'status' => ClassRoom::STATUS_FULL,
            ],
            [
                'course' => 'KhaiTriEdu 2026 - Tin học văn phòng',
                'status' => ClassRoom::STATUS_CLOSED,
            ],
        ];

        foreach ($fixtures as $fixture) {
            $course = $courses->get($fixture['course']);
            $classRoom = $course?->classRooms()->latest('id')->first();

            if (! $classRoom) {
                continue;
            }

            $classRoom->update([
                'status' => $fixture['status'],
            ]);
        }
    }

    protected function normalizeCurrentClassRoomSchedules(Collection $courses): void
    {
        $fixtures = [
            'KhaiTriEdu 2026 - Tiếng Anh giao tiếp' => [
                ['day_of_week' => 'Monday', 'start_time' => '18:00:00', 'end_time' => '20:15:00'],
                ['day_of_week' => 'Wednesday', 'start_time' => '18:00:00', 'end_time' => '20:15:00'],
                ['day_of_week' => 'Friday', 'start_time' => '18:00:00', 'end_time' => '20:15:00'],
            ],
            'KhaiTriEdu 2026 - Tin học văn phòng' => [
                ['day_of_week' => 'Tuesday', 'start_time' => '18:00:00', 'end_time' => '20:00:00'],
                ['day_of_week' => 'Thursday', 'start_time' => '18:00:00', 'end_time' => '20:00:00'],
                ['day_of_week' => 'Saturday', 'start_time' => '18:00:00', 'end_time' => '20:00:00'],
            ],
            'KhaiTriEdu 2026 - Lập trình Python cơ bản' => [
                ['day_of_week' => 'Tuesday', 'start_time' => '19:00:00', 'end_time' => '21:15:00'],
                ['day_of_week' => 'Thursday', 'start_time' => '19:00:00', 'end_time' => '21:15:00'],
            ],
            'KhaiTriEdu 2026 - Thiết kế Web' => [
                ['day_of_week' => 'Sunday', 'start_time' => '19:00:00', 'end_time' => '21:15:00'],
            ],
            'KhaiTriEdu 2026 - Điện dân dụng' => [
                ['day_of_week' => 'Sunday', 'start_time' => '13:30:00', 'end_time' => '16:30:00'],
            ],
            'KhaiTriEdu 2026 - Kỹ thuật chăm sóc da' => [
                ['day_of_week' => 'Sunday', 'start_time' => '08:00:00', 'end_time' => '10:15:00'],
            ],
            'KhaiTriEdu 2026 - Liên thông Đại học - Văn bằng 2 Quản trị kinh doanh' => [
                ['day_of_week' => 'Monday', 'start_time' => '19:00:00', 'end_time' => '21:15:00'],
                ['day_of_week' => 'Wednesday', 'start_time' => '19:00:00', 'end_time' => '21:15:00'],
            ],
        ];

        foreach ($fixtures as $courseTitle => $schedules) {
            $course = $courses->get($courseTitle);
            $classRoom = $course?->currentClassRoom();

            if (! $course || ! $classRoom) {
                continue;
            }

            $classRoom->update([
                'name' => $course->title,
            ]);

            $classRoom->schedules()->delete();

            foreach ($schedules as $schedule) {
                ClassSchedule::create([
                    'lop_hoc_id' => $classRoom->id,
                    'teacher_id' => $classRoom->teacher_id,
                    'room_id' => $classRoom->room_id,
                    'day_of_week' => $schedule['day_of_week'],
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time'],
                ]);
            }

            $courses->put($courseTitle, $course->fresh(['classRooms.room', 'classRooms.teacher', 'classRooms.schedules']));
        }
    }

    protected function gradeLabel(float $score): string
    {
        return match (true) {
            $score >= 90 => 'A+',
            $score >= 85 => 'A',
            $score >= 80 => 'B+',
            $score >= 70 => 'B',
            $score >= 60 => 'C',
            $score >= 50 => 'D',
            default => 'F',
        };
    }

    protected function enrollmentKey(string $courseTitle, string $email, bool $pending = false): string
    {
        return ($pending ? 'pending|' : 'fixed|') . $courseTitle . '|' . $email;
    }

    protected function users(array $emails): Collection
    {
        return User::query()
            ->whereIn('email', $emails)
            ->get()
            ->keyBy('email');
    }

    protected function user(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    protected function courses(array $titles): Collection
    {
        return Course::query()
            ->with([
                'subject.category',
                'teacher',
                'classRooms.room',
                'classRooms.schedules.room',
                'modules.lessons.quiz.questions.options',
            ])
            ->whereIn('title', $titles)
            ->get()
            ->keyBy('title');
    }

    protected function room(string $code): ?Room
    {
        return Room::query()->where('code', $code)->first();
    }
}
