<?php

namespace Tests\Feature;

use App\Models\NhomHoc;
use App\Models\KhoaHoc;
use App\Models\BaiHoc;
use App\Models\HocPhan;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use App\Services\CourseCurriculumService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class sinh_chuong_trinh_hoc_test extends TestCase
{
    use RefreshDatabase;

    public function test_artisan_command_rebuilds_placeholder_modules_for_an_english_course(): void
    {
        $teacher = NguoiDung::factory()->teacher()->create();
        $course = $this->createCourse('ANH VĂN KHUNG 6 BẬC', $teacher);

        $firstModule = HocPhan::create([
            'course_id' => $course->id,
            'title' => 'HocPhan 1 của ANH VĂN KHUNG 6 BẬC',
            'content' => 'Nội dung module 1',
            'position' => 1,
            'status' => HocPhan::STATUS_PUBLISHED,
        ]);

        BaiHoc::create([
            'module_id' => $firstModule->id,
            'title' => 'Bài học 1',
            'description' => 'Bài học cũ',
            'content' => 'Nội dung cũ',
            'order' => 1,
            'duration' => 45,
        ]);

        HocPhan::create([
            'course_id' => $course->id,
            'title' => 'HocPhan 2 của ANH VĂN KHUNG 6 BẬC',
            'content' => 'Nội dung module 2',
            'position' => 2,
            'status' => HocPhan::STATUS_PUBLISHED,
        ]);

        Artisan::call('curriculum:sync-modules');

        $course = $course->fresh(['modules.lessons']);

        $this->assertSame(6, $course->modules->count());
        $this->assertTrue($course->modules->contains(fn (HocPhan $module) => $module->title === 'Listening' && $module->session_count === 5));
        $this->assertTrue($course->modules->contains(fn (HocPhan $module) => $module->title === 'Mock Test & DanhGia' && $module->session_count === 2));

        $listening = $course->modules->firstWhere('title', 'Listening');
        $this->assertNotNull($listening);
        $this->assertSame(5, $listening->lessons->count());
        $this->assertStringContainsString('Listening', $listening->lessons->first()->title);
    }

    public function test_service_generates_professional_curriculum_for_a_real_estate_course(): void
    {
        $teacher = NguoiDung::factory()->teacher()->create();
        $course = $this->createCourse('CHỨNG CHỈ BẤT ĐỘNG SẢN', $teacher);

        app(CourseCurriculumService::class)->syncCourse($course);

        $course = $course->fresh(['modules.lessons']);

        $this->assertSame(5, $course->modules->count());
        $this->assertSame(
            ['Cơ sở nền tảng', 'Khung quy định', 'Phương pháp ứng dụng', 'Thực hành tình huống', 'Tổng kết và đánh giá'],
            $course->modules->pluck('title')->all()
        );
        $this->assertSame([4, 4, 4, 3, 2], $course->modules->pluck('session_count')->all());
        $this->assertSame(4, $course->modules->first()->lessons->count());
    }

    public function test_service_generates_office_it_curriculum_with_clear_syllabus_labels(): void
    {
        $teacher = NguoiDung::factory()->teacher()->create();
        $course = $this->createCourse('TIN HỌC VĂN PHÒNG', $teacher);

        app(CourseCurriculumService::class)->syncCourse($course);

        $course = $course->fresh(['modules.lessons']);

        $this->assertSame(
            ['Windows & File Management', 'Word', 'Excel', 'PowerPoint', 'Email & Internet', 'Practice Project'],
            $course->modules->pluck('title')->all()
        );
        $this->assertSame([4, 4, 5, 4, 3, 2], $course->modules->pluck('session_count')->all());

        $excel = $course->modules->firstWhere('title', 'Excel');
        $this->assertNotNull($excel);
        $this->assertSame(5, $excel->lessons->count());
    }

    private function createCourse(string $subjectName, NguoiDung $teacher): KhoaHoc
    {
        $category = NhomHoc::create([
            'name' => 'Ngoại ngữ - Tin học',
            'slug' => 'ngoai-ngu-tin-hoc',
            'order' => 1,
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);

        if ($subjectName === 'CHỨNG CHỈ BẤT ĐỘNG SẢN') {
            $category->update([
                'name' => 'Bồi dưỡng ngắn hạn',
                'slug' => 'boi-duong-ngan-han',
            ]);
        }

        $subject = MonHoc::create([
            'name' => $subjectName,
            'category_id' => $category->id,
            'price' => 1500000,
            'status' => MonHoc::STATUS_OPEN,
        ]);

        return KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => 'Khóa 26 - ' . $subjectName,
            'description' => 'Khóa học thử nghiệm cho curriculum.',
            'teacher_id' => $teacher->id,
            'schedule' => 'Tối T2-T4-T6, 18:00 - 20:30',
            'capacity' => 20,
            'status' => KhoaHoc::STATUS_ACTIVE,
        ]);
    }
}

