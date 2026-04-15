<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Course;
use App\Models\Role;
use App\Models\User;
use App\Services\CourseCurriculumService;
use App\Services\CourseScheduleSyncService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin 1',
                'username' => 'admin',
                'phone' => '0900000001',
                'password' => bcrypt('123456'),
                'role_id' => Role::idByName('admin'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin2@gmail.com'],
            [
                'name' => 'Admin 2',
                'username' => 'admin2',
                'phone' => '0900000002',
                'password' => bcrypt('123456'),
                'role_id' => Role::idByName('admin'),
            ]
        );

        $teams = [
            'Lập trình',
            'Thiết kế Web',
            'Marketing Digital',
            'Kinh doanh',
            'Phát triển Cá nhân',
        ];

        for ($i = 1; $i <= 10; $i++) {
            $field = $teams[($i - 1) % count($teams)];
            User::updateOrCreate(
                ['email' => "gv{$i}@gmail.com"],
                [
                    'name' => "Giảng viên $i ($field)",
                    'username' => "gv{$i}",
                    'phone' => '09000001' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'password' => bcrypt('123456'),
                    'role_id' => Role::idByName('teacher'),
                ]
            );
        }

        for ($i = 1; $i <= 10; $i++) {
            User::updateOrCreate(
                ['email' => "hv{$i}@gmail.com"],
                [
                    'name' => "Học viên $i",
                    'username' => "hv{$i}",
                    'phone' => '09000002' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'password' => bcrypt('123456'),
                    'role_id' => Role::idByName('student'),
                ]
            );
        }

        $teachers = User::query()
            ->teachers()
            ->orderBy('id')
            ->get(['id', 'name']);

        $data = [
            'Ngoại ngữ - Tin học' => [
                'ANH VĂN THIẾU NHI',
                'ANH VĂN KHUNG 6 BẬC',
                'TIN HỌC THIẾU NHI',
                'TIN HỌC VĂN PHÒNG',
                'ỨNG DỤNG CÔNG NGHỆ THÔNG TIN',
            ],
            'Bồi dưỡng ngắn hạn' => [
                'CHỨNG CHỈ BẤT ĐỘNG SẢN',
                'NGHIỆP VỤ ĐẤU THẦU',
                'BỒI DƯỠNG GIÁO VIÊN PHỔ THÔNG',
                'BỒI DƯỠNG CHỨC DANH NGHỀ NGHIỆP GIÁO VIÊN',
                'BỒI DƯỠNG CHỨC DANH NGHỀ NGHIỆP GIÁO DỤC NGHỀ NGHIỆP',
                'NGHIỆP VỤ SƯ PHẠM (TIỂU HỌC – THCS – THPT)',
                'NGHIỆP VỤ SƯ PHẠM (SƠ CẤP – TRUNG CẤP – CAO ĐẲNG)',
                'VĂN THƯ LƯU TRỮ – THIẾT BỊ TRƯỜNG HỌC',
                'QUẢN LÝ MẦM NON, CẤP DƯỠNG – BẢO MẪU',
                'KẾ TOÁN TRƯỞNG DOANH NGHIỆP – HÀNH CHÍNH SỰ NGHIỆP',
            ],
            'Đào tạo nghề' => [
                'BÁO CÁO THUẾ',
                'KẾ TOÁN THỰC HÀNH',
                'KẾ TOÁN DOANH NGHIỆP',
                'ĐIỆN LẠNH',
                'ĐIỆN DÂN DỤNG',
                'MÁY DÂN DỤNG',
                'KỸ THUẬT CHĂM SÓC DA',
                'THIẾT KẾ ĐỒ HỌA QUẢNG CÁO',
                'KỸ THUẬT SỬA CHỮA – LẮP RÁP MÁY TÍNH',
                'KỸ THUẬT PHA CHẾ',
                'KỸ THUẬT CHẾ BIẾN MÓN ĂN',
            ],
            'Đào tạo dài hạn' => [
                'TRUNG CẤP – CAO ĐẲNG',
                'LIÊN THÔNG ĐẠI HỌC – VĂN BẰNG 2',
                'THẠC SĨ – CAO HỌC',
            ],
        ];

        $courseIndex = 0;

        foreach ($data as $categoryName => $courses) {
            $category = \App\Models\Category::create([
                'name' => $categoryName,
                'slug' => \Str::slug($categoryName),
                'description' => $categoryName . ' danh mục',
                'order' => 0,
            ]);

            foreach ($courses as $courseTitle) {
                $subjectPrice = rand(15, 60) * 100000; // 1,500,000 to 6,000,000
                $teacherId = $this->resolveTeacherIdForCourse($categoryName, $courseTitle, $teachers, $courseIndex);

                $subject = \App\Models\Subject::create([
                    'name' => $courseTitle,
                    'description' => 'Chương trình đào tạo chuẩn kỹ năng ' . $courseTitle,
                    'category_id' => $category->id,
                    'price' => $subjectPrice,
                    'duration' => rand(1, 6), // 1-6 months
                    'status' => \App\Models\Subject::STATUS_OPEN,
                ]);

                $course = \App\Models\Course::create([
                    'subject_id' => $subject->id,
                    'title' => 'Khóa 26 - ' . $courseTitle,
                    'description' => 'Khóa 26 - ' . $courseTitle,
                    'price' => $subjectPrice,
                    'schedule' => 'Tối T2-T4-T6, 18:00 - 20:30',
                    'teacher_id' => $teacherId,
                    'capacity' => 20,
                    'status' => 'active',
                ]);

                app(CourseCurriculumService::class)->syncCourse($course);
                $courseIndex++;
            }
        }

        Course::query()
            ->with(['subject.category', 'classRooms.schedules'])
            ->where('title', 'like', 'Khóa 26 - %')
            ->orderBy('id')
            ->get()
            ->whenNotEmpty(fn ($courses) => app(CourseScheduleSyncService::class)->syncCourses($courses));

        $announcements = [
            ['title' => 'Bí quyết học lập trình hiệu quả', 'message' => 'Hướng dẫn toàn diện giúp bạn học lập trình nhanh hơn, nhớ lâu hơn và giải quyết bài tập khó khăn.', 'published_at' => now()->subDays(2), 'is_pinned' => true],
            ['title' => 'Nguyên tắc thiết kế UI/UX hiện đại', 'message' => 'Tìm hiểu về những nguyên tắc cốt lõi của thiết kế giao diện người dùng đẹp và thân thiện.', 'published_at' => now()->subDays(5), 'is_pinned' => false],
            ['title' => '5 chiến lược marketing digital hiệu quả', 'message' => 'Khám phá những chiến lược marketing digital được chứng minh hiệu quả để phát triển business.', 'published_at' => now()->subDays(8), 'is_pinned' => false],
            ['title' => 'Cách kiếm thêm thu nhập qua kỹ năng online', 'message' => 'Hướng dẫn chi tiết giúp bạn kiếm tiền thêm bằng cách sử dụng kỹ năng lập trình hoặc thiết kế.', 'published_at' => now()->subDays(11), 'is_pinned' => false],
            ['title' => '10 mẹo tăng năng suất học tập hàng ngày', 'message' => 'Những mẹo đơn giản nhưng cực hiệu quả giúp bạn học tập hiệu quả hơn và đạt kết quả tốt.', 'published_at' => now()->subDays(14), 'is_pinned' => false],
            ['title' => 'Review các công cụ lập trình 2026', 'message' => 'So sánh chi tiết các IDE, framework và công cụ lập trình phổ biến nhất hiện nay.', 'published_at' => now()->subDays(17), 'is_pinned' => false],
        ];

        foreach ($announcements as $item) {
            Announcement::updateOrCreate(
                ['title' => $item['title']],
                [
                    'created_by' => $admin->id,
                    'course_id' => null,
                    'message' => $item['message'],
                    'status' => 'published',
                    'published_at' => $item['published_at'],
                    'is_pinned' => $item['is_pinned'],
                ]
            );
        }

        $roomTypes = ['theory', 'practice'];
        $locations = ['Tầng 1 - Khu A', 'Tầng 2 - Khu A', 'Tầng 1 - Khu B', 'Tầng 3 - Khu C'];
        
        for ($i = 1; $i <= 10; $i++) {
            \App\Models\Room::firstOrCreate(
                ['name' => 'Phòng học ' . $i],
                [
                    'code' => 'PH' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'type' => $roomTypes[array_rand($roomTypes)],
                    'location' => $locations[array_rand($locations)],
                    'capacity' => rand(20, 60),
                    'status' => \App\Models\Room::STATUS_ACTIVE,
                    'note' => 'Phòng học đầy đủ trang thiết bị.',
                ]
            );
        }
    }

    protected function resolveTeacherIdForCourse(
        string $categoryName,
        string $courseTitle,
        Collection $teachers,
        int $courseIndex
    ): ?int {
        $title = Str::ascii(Str::upper($categoryName . ' ' . $courseTitle));
        $specialties = match (true) {
            Str::contains($title, ['ANH VAN', 'TIN HOC', 'CONG NGHE THONG TIN', 'LAP RAP', 'SUA CHUA']) => [
                'Lập trình',
                'Thiết kế Web',
            ],
            Str::contains($title, ['KE TOAN', 'THUE', 'KINH DOANH', 'BAT DONG SAN', 'DAU THAU']) => [
                'Kinh doanh',
                'Marketing Digital',
            ],
            Str::contains($title, ['GIAO VIEN', 'SU PHAM', 'MAM NON', 'BAO MAU', 'VAN THU', 'GIAO DUC', 'TRUNG CAP', 'LIEN THONG', 'THAC SI', 'CAO HOC', 'DAI HOC', 'VAN BANG 2']) => [
                'Phát triển Cá nhân',
                'Kinh doanh',
            ],
            Str::contains($title, ['DIEN', 'MAY', 'THIET KE DO HOA', 'CHAM SOC DA', 'PHA CHE', 'CHE BIEN']) => [
                'Thiết kế Web',
                'Marketing Digital',
                'Phát triển Cá nhân',
            ],
            default => [
                'Lập trình',
                'Thiết kế Web',
                'Marketing Digital',
                'Kinh doanh',
                'Phát triển Cá nhân',
            ],
        };

        foreach ($specialties as $specialty) {
            $pool = $teachers->filter(function (User $teacher) use ($specialty) {
                return Str::contains($teacher->name, '(' . $specialty . ')');
            })->values();

            if ($pool->isNotEmpty()) {
                $selectedTeacher = $pool->get($courseIndex % $pool->count());

                if ($selectedTeacher) {
                    return (int) $selectedTeacher->id;
                }
            }
        }

        $fallbackTeacher = $teachers->get($courseIndex % max(1, $teachers->count()));

        return $fallbackTeacher ? (int) $fallbackTeacher->id : null;
    }
}
