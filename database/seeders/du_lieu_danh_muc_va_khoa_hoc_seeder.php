<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Category;
use App\Models\Course;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use App\Services\CourseCurriculumService;
use App\Services\CourseScheduleSyncService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DuLieuDanhMucVaKhoaHocSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()
            ->where('email', 'admin@khaitriedu.vn')
            ->firstOrFail();

        $categories = $this->seedCategories();
        $rooms = $this->seedRooms();
        $courses = $this->seedSubjectsAndCourses($categories);

        app(CourseCurriculumService::class)->syncCourses($courses->values());

        $syncableCourses = $courses
            ->filter(fn (Course $course) => in_array($course->status, [
                Course::STATUS_ACTIVE,
                Course::STATUS_SCHEDULED,
                Course::STATUS_COMPLETED,
            ], true))
            ->values();

        if ($syncableCourses->isNotEmpty()) {
            app(CourseScheduleSyncService::class)->syncCourses($syncableCourses);
        }

        $this->normalizeHistoricalCourses($courses);
        $this->seedAnnouncements($admin, $courses);
    }

    protected function seedCategories(): Collection
    {
        $now = Carbon::now();

        $fixtures = [
            ['key' => 'ngoai_ngu', 'name' => 'Ngoại ngữ', 'slug' => 'ngoai-ngu', 'program' => 'Giao tiếp, luyện thi và thiếu nhi', 'level' => 'Cơ bản - Nâng cao', 'order' => 1],
            ['key' => 'tin_hoc_ung_dung', 'name' => 'Tin học ứng dụng', 'slug' => 'tin-hoc-ung-dung', 'program' => 'Tin học văn phòng và kỹ năng số', 'level' => 'Ngắn hạn', 'order' => 2],
            ['key' => 'ke_toan_tai_chinh', 'name' => 'Kế toán - Tài chính', 'slug' => 'ke-toan-tai-chinh', 'program' => 'Kế toán thực hành và báo cáo thuế', 'level' => 'Chứng chỉ', 'order' => 3],
            ['key' => 'thiet_ke_sang_tao', 'name' => 'Thiết kế - Sáng tạo', 'slug' => 'thiet-ke-sang-tao', 'program' => 'Web, đồ họa và trình bày', 'level' => 'Thực hành', 'order' => 4],
            ['key' => 'ky_thuat_nghe', 'name' => 'Kỹ thuật - Nghề', 'slug' => 'ky-thuat-nghe', 'program' => 'Nghề thực hành', 'level' => 'Ngắn hạn', 'order' => 5],
            ['key' => 'boi_duong_giao_vien', 'name' => 'Bồi dưỡng giáo viên', 'slug' => 'boi-duong-giao-vien', 'program' => 'Sư phạm và bồi dưỡng chức danh', 'level' => 'Chứng chỉ', 'order' => 6],
            ['key' => 'quan_tri_kinh_doanh', 'name' => 'Quản trị - Kinh doanh', 'slug' => 'quan-tri-kinh-doanh', 'program' => 'Bán hàng, marketing, điều hành', 'level' => 'Ngắn hạn', 'order' => 7],
            ['key' => 'chuong_trinh_dai_han', 'name' => 'Chương trình dài hạn', 'slug' => 'chuong-trinh-dai-han', 'program' => 'Liên thông và sau đại học', 'level' => 'Dài hạn', 'order' => 8],
            ['key' => 'internal_english_it', 'name' => 'Ngoại ngữ - Tin học', 'slug' => 'ngoai-ngu-tin-hoc', 'program' => 'Nội bộ đào tạo', 'level' => 'Trung tâm', 'order' => 100],
            ['key' => 'internal_short_term', 'name' => 'Bồi dưỡng ngắn hạn', 'slug' => 'boi-duong-ngan-han', 'program' => 'Các lớp chứng chỉ', 'level' => 'Ngắn hạn', 'order' => 101],
            ['key' => 'internal_vocational', 'name' => 'Đào tạo nghề', 'slug' => 'dao-tao-nghe', 'program' => 'Lớp nghề kỹ thuật', 'level' => 'Thực hành', 'order' => 102],
            ['key' => 'internal_long_term', 'name' => 'Đào tạo dài hạn', 'slug' => 'dao-tao-dai-han', 'program' => 'Lộ trình dài hạn', 'level' => 'Dài hạn', 'order' => 103],
        ];

        $categories = collect();

        foreach ($fixtures as $index => $fixture) {
            $categories->put(
                $fixture['key'],
                Category::updateOrCreate(
                    ['slug' => $fixture['slug']],
                    [
                        'name' => $fixture['name'],
                        'slug' => $fixture['slug'],
                        'description' => sprintf('Nhóm đào tạo %s của KhaiTriEdu.', $fixture['name']),
                        'program' => $fixture['program'],
                        'level' => $fixture['level'],
                        'status' => Category::STATUS_ACTIVE,
                        'order' => $fixture['order'],
                        'created_at' => $now->subDays(40 - $index),
                        'updated_at' => $now->subDays(40 - $index),
                    ]
                )
            );
        }

        return $categories;
    }

    protected function seedRooms(): Collection
    {
        $fixtures = [
            ['code' => 'PH101', 'name' => 'Phòng Lý thuyết A1', 'type' => 'theory', 'location' => 'Tầng trệt', 'capacity' => 12, 'status' => Room::STATUS_ACTIVE, 'note' => 'Phòng học lý thuyết nhỏ cho lớp tối.'],
            ['code' => 'PH102', 'name' => 'Phòng Lý thuyết A2', 'type' => 'theory', 'location' => 'Tầng trệt', 'capacity' => 24, 'status' => Room::STATUS_ACTIVE, 'note' => 'Phòng học lý thuyết tiêu chuẩn, có máy chiếu.'],
            ['code' => 'PH103', 'name' => 'Phòng Lý thuyết B1', 'type' => 'theory', 'location' => 'Tầng hầm', 'capacity' => 30, 'status' => Room::STATUS_ACTIVE, 'note' => 'Phòng học lý thuyết cho lớp đông học viên.'],
            ['code' => 'TH201', 'name' => 'Phòng Nghề may', 'type' => 'practice', 'location' => 'Tầng trệt', 'capacity' => 18, 'status' => Room::STATUS_ACTIVE, 'note' => 'Phòng thực hành may mặc và cắt may cơ bản.'],
            ['code' => 'TH202', 'name' => 'Phòng Nghề nấu', 'type' => 'practice', 'location' => 'Tầng hầm', 'capacity' => 20, 'status' => Room::STATUS_ACTIVE, 'note' => 'Phòng thực hành nấu ăn và bếp bánh.'],
            ['code' => 'MT301', 'name' => 'Phòng Máy tính 1', 'type' => 'practice', 'location' => 'Tầng trệt', 'capacity' => 22, 'status' => Room::STATUS_ACTIVE, 'note' => 'Phòng máy tính cho tin học văn phòng và lập trình.'],
            ['code' => 'MT302', 'name' => 'Phòng Máy tính 2', 'type' => 'practice', 'location' => 'Tầng hầm', 'capacity' => 20, 'status' => Room::STATUS_ACTIVE, 'note' => 'Phòng máy tính cho thực hành thiết kế và tin học.'],
            ['code' => 'HT401', 'name' => 'Phòng Đa năng 401', 'type' => 'theory', 'location' => 'Tầng trệt', 'capacity' => 35, 'status' => Room::STATUS_ACTIVE, 'note' => 'Phòng hội thảo và bồi dưỡng lớp đông.'],
            ['code' => 'DA501', 'name' => 'Phòng Chăm sóc da', 'type' => 'practice', 'location' => 'Tầng hầm', 'capacity' => 24, 'status' => Room::STATUS_ACTIVE, 'note' => 'Phòng thực hành chăm sóc da và spa cơ bản.'],
            ['code' => 'DA502', 'name' => 'Phòng Đa năng thực hành', 'type' => 'practice', 'location' => 'Tầng trệt', 'capacity' => 24, 'status' => Room::STATUS_ACTIVE, 'note' => 'Phòng dùng cho lớp thực hành ngắn hạn.'],
            ['code' => 'BT601', 'name' => 'Phòng Kỹ thuật - Kho thiết bị', 'type' => 'practice', 'location' => 'Tầng hầm', 'capacity' => 16, 'status' => Room::STATUS_ACTIVE, 'note' => 'Phòng phụ trợ cho thiết bị và đồ nghề.'],
            ['code' => 'KT701', 'name' => 'Phòng Kho vật tư', 'type' => 'theory', 'location' => 'Tầng hầm', 'capacity' => 18, 'status' => Room::STATUS_ACTIVE, 'note' => 'Phòng phụ trợ cho lưu trữ vật tư học tập.'],
        ];

        $rooms = collect();

        foreach ($fixtures as $index => $fixture) {
            $rooms->put(
                $fixture['code'],
                Room::updateOrCreate(
                    ['code' => $fixture['code']],
                    [
                        'name' => $fixture['name'],
                        'type' => $fixture['type'],
                        'location' => $fixture['location'],
                        'capacity' => $fixture['capacity'],
                        'status' => $fixture['status'],
                        'note' => $fixture['note'],
                        'created_at' => Carbon::now()->subDays(50 - $index),
                        'updated_at' => Carbon::now()->subDays(50 - $index),
                    ]
                )
            );
        }

        return $rooms;
    }

    protected function seedSubjectsAndCourses(Collection $categories): Collection
    {
        $fixtures = [
            [
                'key' => 'tieng_anh_giao_tiep',
                'category' => 'ngoai_ngu',
                'subject_name' => 'Tiếng Anh giao tiếp',
                'course_title' => 'KhaiTriEdu 2026 - Tiếng Anh giao tiếp',
                'teacher_email' => 'anhdung@khaitriedu.vn',
                'price' => 3200000,
                'duration' => 3,
                'capacity' => 12,
                'status' => Course::STATUS_ACTIVE,
                'students' => 12,
            ],
            [
                'key' => 'tieng_anh_thieu_nhi',
                'category' => 'ngoai_ngu',
                'subject_name' => 'Tiếng Anh thiếu nhi',
                'course_title' => 'KhaiTriEdu 2026 - Tiếng Anh thiếu nhi',
                'teacher_email' => 'hongloan@khaitriedu.vn',
                'price' => 2800000,
                'duration' => 3,
                'capacity' => 10,
                'status' => Course::STATUS_COMPLETED,
                'students' => 6,
                'historical' => true,
            ],
            [
                'key' => 'tin_hoc_van_phong_public',
                'category' => 'tin_hoc_ung_dung',
                'subject_name' => 'Tin học văn phòng',
                'course_title' => 'KhaiTriEdu 2026 - Tin học văn phòng',
                'teacher_email' => 'baochau@khaitriedu.vn',
                'price' => 2400000,
                'duration' => 2,
                'capacity' => 16,
                'status' => Course::STATUS_ACTIVE,
                'students' => 8,
            ],
            [
                'key' => 'lap_trinh_python',
                'category' => 'tin_hoc_ung_dung',
                'subject_name' => 'Lập trình Python cơ bản',
                'course_title' => 'KhaiTriEdu 2026 - Lập trình Python cơ bản',
                'teacher_email' => 'minhkhang@khaitriedu.vn',
                'price' => 4200000,
                'duration' => 4,
                'capacity' => 14,
                'status' => Course::STATUS_SCHEDULED,
                'students' => 6,
            ],
            [
                'key' => 'ung_dung_cntt',
                'category' => 'tin_hoc_ung_dung',
                'subject_name' => 'Ứng dụng công nghệ thông tin',
                'course_title' => 'KhaiTriEdu 2026 - Ứng dụng công nghệ thông tin',
                'teacher_email' => 'minhkhang@khaitriedu.vn',
                'price' => 3900000,
                'duration' => 3,
                'capacity' => 18,
                'status' => Course::STATUS_ACTIVE,
                'students' => 6,
            ],
            [
                'key' => 'ke_toan_thuc_hanh',
                'category' => 'ke_toan_tai_chinh',
                'subject_name' => 'Kế toán thực hành',
                'course_title' => 'KhaiTriEdu 2026 - Kế toán thực hành',
                'teacher_email' => 'minhtu@khaitriedu.vn',
                'price' => 3900000,
                'duration' => 3,
                'capacity' => 16,
                'status' => Course::STATUS_ACTIVE,
                'students' => 7,
            ],
            [
                'key' => 'bao_cao_thue',
                'category' => 'ke_toan_tai_chinh',
                'subject_name' => 'Báo cáo thuế',
                'course_title' => 'KhaiTriEdu 2026 - Báo cáo thuế',
                'teacher_email' => 'minhtu@khaitriedu.vn',
                'price' => 4500000,
                'duration' => 3,
                'capacity' => 12,
                'status' => Course::STATUS_COMPLETED,
                'students' => 5,
                'historical' => true,
            ],
            [
                'key' => 'thiet_ke_web',
                'category' => 'thiet_ke_sang_tao',
                'subject_name' => 'Thiết kế Web',
                'course_title' => 'KhaiTriEdu 2026 - Thiết kế Web',
                'teacher_email' => 'thuylinh@khaitriedu.vn',
                'price' => 3600000,
                'duration' => 4,
                'capacity' => 15,
                'status' => Course::STATUS_ACTIVE,
                'students' => 6,
            ],
            [
                'key' => 'thiet_ke_do_hoa',
                'category' => 'thiet_ke_sang_tao',
                'subject_name' => 'Thiết kế đồ họa',
                'course_title' => 'KhaiTriEdu 2026 - Thiết kế đồ họa',
                'teacher_email' => 'hongnhung@khaitriedu.vn',
                'price' => 4000000,
                'duration' => 4,
                'capacity' => 15,
                'status' => Course::STATUS_ACTIVE,
                'students' => 5,
            ],
            [
                'key' => 'dien_dan_dung',
                'category' => 'ky_thuat_nghe',
                'subject_name' => 'Điện dân dụng',
                'course_title' => 'KhaiTriEdu 2026 - Điện dân dụng',
                'teacher_email' => 'hoangnam@khaitriedu.vn',
                'price' => 3800000,
                'duration' => 3,
                'capacity' => 12,
                'status' => Course::STATUS_ACTIVE,
                'students' => 5,
            ],
            [
                'key' => 'cham_soc_da',
                'category' => 'ky_thuat_nghe',
                'subject_name' => 'Kỹ thuật chăm sóc da',
                'course_title' => 'KhaiTriEdu 2026 - Kỹ thuật chăm sóc da',
                'teacher_email' => 'hamy@khaitriedu.vn',
                'price' => 4200000,
                'duration' => 3,
                'capacity' => 12,
                'status' => Course::STATUS_ACTIVE,
                'students' => 5,
            ],
            [
                'key' => 'boi_duong_giao_vien_pho_thong',
                'category' => 'boi_duong_giao_vien',
                'subject_name' => 'Bồi dưỡng giáo viên phổ thông',
                'course_title' => 'KhaiTriEdu 2026 - Bồi dưỡng giáo viên phổ thông',
                'teacher_email' => 'thanhtung@khaitriedu.vn',
                'price' => 3300000,
                'duration' => 2,
                'capacity' => 18,
                'status' => Course::STATUS_ACTIVE,
                'students' => 4,
            ],
            [
                'key' => 'lien_thong_dai_hoc',
                'category' => 'chuong_trinh_dai_han',
                'subject_name' => 'Liên thông Đại học - Văn bằng 2 Quản trị kinh doanh',
                'course_title' => 'KhaiTriEdu 2026 - Liên thông Đại học - Văn bằng 2 Quản trị kinh doanh',
                'teacher_email' => 'quangphuc@khaitriedu.vn',
                'price' => 9600000,
                'duration' => 12,
                'capacity' => 20,
                'status' => Course::STATUS_ACTIVE,
                'students' => 6,
            ],
            [
                'key' => 'marketing_digital',
                'category' => 'quan_tri_kinh_doanh',
                'subject_name' => 'Marketing Digital căn bản',
                'course_title' => 'KhaiTriEdu 2026 - Marketing Digital căn bản',
                'teacher_email' => 'quochuy@khaitriedu.vn',
                'price' => 3500000,
                'duration' => 3,
                'capacity' => 20,
                'status' => Course::STATUS_PENDING_OPEN,
                'students' => 0,
            ],
            [
                'key' => 'ban_hang',
                'category' => 'quan_tri_kinh_doanh',
                'subject_name' => 'Kỹ năng bán hàng chuyên nghiệp',
                'course_title' => 'KhaiTriEdu 2026 - Kỹ năng bán hàng chuyên nghiệp',
                'teacher_email' => 'thaison@khaitriedu.vn',
                'price' => 3000000,
                'duration' => 2,
                'capacity' => 20,
                'status' => Course::STATUS_PENDING_OPEN,
                'students' => 0,
            ],
            [
                'key' => 'anh_van_khung_6_bac',
                'category' => 'internal_english_it',
                'subject_name' => 'ANH VĂN KHUNG 6 BẬC',
                'course_title' => 'Khóa nội bộ - ANH VĂN KHUNG 6 BẬC',
                'teacher_email' => 'anhdung@khaitriedu.vn',
                'price' => 3200000,
                'duration' => 3,
                'capacity' => 12,
                'status' => Course::STATUS_COMPLETED,
                'students' => 5,
                'historical' => true,
            ],
            [
                'key' => 'tin_hoc_van_phong_internal',
                'category' => 'internal_english_it',
                'subject_name' => 'TIN HỌC VĂN PHÒNG',
                'course_title' => 'Khóa nội bộ - TIN HỌC VĂN PHÒNG',
                'teacher_email' => 'baochau@khaitriedu.vn',
                'price' => 2300000,
                'duration' => 2,
                'capacity' => 15,
                'status' => Course::STATUS_ACTIVE,
                'students' => 6,
            ],
            [
                'key' => 'chung_chi_bat_dong_san',
                'category' => 'internal_short_term',
                'subject_name' => 'CHỨNG CHỈ BẤT ĐỘNG SẢN',
                'course_title' => 'Khóa nội bộ - CHỨNG CHỈ BẤT ĐỘNG SẢN',
                'teacher_email' => 'ngoclan@khaitriedu.vn',
                'price' => 2700000,
                'duration' => 1,
                'capacity' => 12,
                'status' => Course::STATUS_COMPLETED,
                'students' => 5,
                'historical' => true,
            ],
            [
                'key' => 'boi_duong_chuc_danh',
                'category' => 'internal_short_term',
                'subject_name' => 'BỒI DƯỠNG CHỨC DANH NGHỀ NGHIỆP GIÁO VIÊN',
                'course_title' => 'Khóa nội bộ - BỒI DƯỠNG CHỨC DANH NGHỀ NGHIỆP GIÁO VIÊN',
                'teacher_email' => 'thanhtung@khaitriedu.vn',
                'price' => 3300000,
                'duration' => 2,
                'capacity' => 12,
                'status' => Course::STATUS_COMPLETED,
                'students' => 4,
                'historical' => true,
            ],
            [
                'key' => 'thac_si_qtkd',
                'category' => 'internal_long_term',
                'subject_name' => 'THẠC SĨ QUẢN TRỊ KINH DOANH',
                'course_title' => 'Khóa nội bộ - THẠC SĨ QUẢN TRỊ KINH DOANH',
                'teacher_email' => 'quangphuc@khaitriedu.vn',
                'price' => 12500000,
                'duration' => 24,
                'capacity' => 20,
                'status' => Course::STATUS_COMPLETED,
                'students' => 4,
                'historical' => true,
            ],
        ];

        $courses = collect();

        foreach ($fixtures as $index => $fixture) {
            $category = $categories->get($fixture['category']);
            $teacher = User::query()
                ->teachers()
                ->where('email', $fixture['teacher_email'])
                ->firstOrFail();

            $subjectId = $index + 1;
            $subject = Subject::updateOrCreate(
                ['id' => $subjectId],
                [
                    'name' => $fixture['subject_name'],
                    'description' => sprintf('Chương trình đào tạo %s tại KhaiTriEdu.', $fixture['subject_name']),
                    'price' => $fixture['price'],
                    'duration' => $fixture['duration'],
                    'status' => Subject::STATUS_OPEN,
                    'category_id' => $category->id,
                    'image' => null,
                    'created_at' => Carbon::now()->subDays(30 - $index),
                    'updated_at' => Carbon::now()->subDays(30 - $index),
                ]
            );

            $course = Course::updateOrCreate(
                ['title' => $fixture['course_title']],
                [
                    'subject_id' => $subject->id,
                    'title' => $fixture['course_title'],
                    'description' => sprintf('Khóa học %s dành cho học viên KhaiTriEdu.', $fixture['subject_name']),
                    'price' => $fixture['price'],
                    'schedule' => $fixture['status'] === Course::STATUS_PENDING_OPEN ? 'Chờ xếp lớp' : null,
                    'teacher_id' => $teacher->id,
                    'capacity' => $fixture['capacity'],
                    'status' => $fixture['status'],
                    'created_at' => Carbon::now()->subDays(20 - $index),
                    'updated_at' => Carbon::now()->subDays(20 - $index),
                ]
            );

            $courses->put($fixture['key'], $course->fresh(['subject.category']));
        }

        return $courses;
    }

    protected function normalizeHistoricalCourses(Collection $courses): void
    {
        foreach ($courses as $course) {
            $subjectName = Str::upper((string) $course->subject?->name);

            if (! in_array($course->status, [Course::STATUS_COMPLETED], true)) {
                continue;
            }

            $startDate = Carbon::now()->subMonths(max(2, (int) ($course->subject?->duration ?? 3) + 1));
            $endDate = Carbon::now()->subMonth();

            $course->fill([
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'schedule' => sprintf(
                    'Tối T2-T4-T6, 18:00 - 20:15 | Từ %s đến %s',
                    $startDate->format('d/m/Y'),
                    $endDate->format('d/m/Y')
                ),
            ])->save();

            $classRoom = $course->classRooms()->latest('id')->first();

            if ($classRoom) {
                $classRoom->update([
                    'start_date' => $startDate->toDateString(),
                    'duration' => $course->subject?->duration,
                    'status' => 'completed',
                ]);
            }
        }
    }

    protected function seedAnnouncements(User $admin, Collection $courses): void
    {
        $picks = $courses->only([
            'tieng_anh_giao_tiep',
            'tin_hoc_van_phong_public',
            'ke_toan_thuc_hanh',
            'thiet_ke_web',
            'boi_duong_giao_vien_pho_thong',
            'chung_chi_bat_dong_san',
        ]);

        $announcements = [
            [
                'title' => 'Khai giảng lớp Tiếng Anh giao tiếp tháng 4',
                'message' => 'Lớp Tiếng Anh giao tiếp buổi tối đã sẵn sàng, học viên có thể theo dõi lịch học và tài liệu ngay trên hệ thống.',
                'course' => $picks->get('tieng_anh_giao_tiep'),
                'is_pinned' => true,
            ],
            [
                'title' => 'Mở đăng ký khóa Tin học văn phòng',
                'message' => 'Khóa Tin học văn phòng có lịch học tối 3 buổi/tuần, phù hợp cho học viên đi làm muốn nâng cao kỹ năng văn phòng.',
                'course' => $picks->get('tin_hoc_van_phong_public'),
                'is_pinned' => true,
            ],
            [
                'title' => 'Lịch học Kế toán thực hành đã được cập nhật',
                'message' => 'Phòng Đào tạo đã chốt lịch học và phân phòng cho lớp Kế toán thực hành khóa hiện tại.',
                'course' => $picks->get('ke_toan_thuc_hanh'),
                'is_pinned' => false,
            ],
            [
                'title' => 'Thông báo lớp Thiết kế Web',
                'message' => 'Lớp Thiết kế Web tuần này chuyển sang phòng máy tính 301 để thuận tiện thực hành.',
                'course' => $picks->get('thiet_ke_web'),
                'is_pinned' => false,
            ],
            [
                'title' => 'Bồi dưỡng giáo viên phổ thông mở thêm nhóm tối',
                'message' => 'Trung tâm mở thêm nhóm buổi tối cho lớp bồi dưỡng giáo viên phổ thông để hỗ trợ học viên đang công tác.',
                'course' => $picks->get('boi_duong_giao_vien_pho_thong'),
                'is_pinned' => false,
            ],
            [
                'title' => 'Học viên đủ điều kiện nhận chứng chỉ đợt 1',
                'message' => 'Danh sách học viên hoàn thành khóa học và đủ điều kiện nhận chứng chỉ đã được cập nhật trong hệ thống.',
                'course' => $picks->get('chung_chi_bat_dong_san'),
                'is_pinned' => false,
            ],
        ];

        foreach ($announcements as $index => $announcement) {
            Announcement::updateOrCreate(
                ['title' => $announcement['title']],
                [
                    'created_by' => $admin->id,
                    'course_id' => $announcement['course']?->id,
                    'message' => $announcement['message'],
                    'status' => 'published',
                    'published_at' => Carbon::now()->subDays(14 - $index * 2),
                    'is_pinned' => $announcement['is_pinned'],
                ]
            );
        }
    }
}
