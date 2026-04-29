<?php

namespace Tests\Feature;

use App\Models\NhomHoc;
use App\Models\KhoaHoc;
use App\Models\BaiHoc;
use App\Models\HocPhan;
use App\Models\LuaChon;
use App\Models\CauHoi;
use App\Models\BaiKiemTra;
use App\Models\TraLoiBaiKiemTra;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class quan_ly_hoc_phan_test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_module_management_page(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $course = $this->createCourseWithSubject();
        $module = HocPhan::create([
            'course_id' => $course->id,
            'title' => 'Listening',
            'content' => 'Nghe hội thoại cơ bản và bắt ý chính.',
            'session_count' => 5,
            'status' => HocPhan::STATUS_PUBLISHED,
            'position' => 1,
        ]);
        BaiHoc::create([
            'module_id' => $module->id,
            'title' => 'Buổi 1',
            'description' => 'Nhận diện chủ đề và từ khóa.',
            'content' => 'Nội dung buổi 1.',
            'order' => 1,
            'duration' => 45,
        ]);
        BaiHoc::create([
            'module_id' => $module->id,
            'title' => 'Buổi 2',
            'description' => 'Luyện nghe chi tiết.',
            'content' => 'Nội dung buổi 2.',
            'order' => 2,
            'duration' => 45,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.courses.modules.index', $course));

        $response->assertOk();
        $response->assertSee('Quản lý module');
        $response->assertSee($course->title);
        $response->assertSee('Listening');
        $response->assertSee('Nghe hội thoại cơ bản và bắt ý chính.');
        $response->assertSee('5 buổi');
    }

    public function test_admin_can_create_module_for_course(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $course = $this->createCourseWithSubject();

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.modules.create', $course), [
                'title' => 'HocPhan Nhap Mon',
                'content' => 'Tong quan noi dung',
                'session_count' => 6,
                'duration' => 90,
                'position' => 1,
                'status' => HocPhan::STATUS_PUBLISHED,
            ]);

        $response->assertRedirect(route('admin.courses.modules.index', $course));
        $this->assertDatabaseHas('chuong_hoc', [
            'course_id' => $course->id,
            'title' => 'HocPhan Nhap Mon',
            'session_count' => 6,
            'duration' => 90,
            'status' => HocPhan::STATUS_PUBLISHED,
            'position' => 1,
        ]);
    }

    public function test_admin_can_update_module(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $course = $this->createCourseWithSubject();
        $module = HocPhan::create([
            'course_id' => $course->id,
            'title' => 'HocPhan Cu',
            'session_count' => 3,
            'position' => 1,
            'status' => HocPhan::STATUS_PUBLISHED,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.modules.update', [$course, $module]), [
                'title' => 'HocPhan Da Sua',
                'content' => 'Noi dung moi',
                'session_count' => 4,
                'duration' => 120,
                'position' => 2,
                'status' => HocPhan::STATUS_UNPUBLISHED,
            ]);

        $response->assertRedirect(route('admin.courses.modules.index', $course));
        $this->assertDatabaseHas('chuong_hoc', [
            'id' => $module->id,
            'title' => 'HocPhan Da Sua',
            'session_count' => 4,
            'duration' => 120,
            'position' => 2,
            'status' => HocPhan::STATUS_UNPUBLISHED,
        ]);
    }

    public function test_admin_can_sync_curriculum_template_for_a_course(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $course = $this->createCourseWithSubject('ANH VĂN KHUNG 6 BẬC', 'Ngoại ngữ - Tin học');

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.modules.sync-template', $course));

        $response->assertRedirect(route('admin.courses.modules.index', $course));
        $response->assertSessionHas('status');

        $course = $course->fresh(['modules.lessons']);

        $this->assertSame(6, $course->modules->count());
        $this->assertSame('Listening', $course->modules->first()->title);
        $this->assertSame(5, $course->modules->first()->session_count);
        $this->assertSame(5, $course->modules->first()->lessons->count());
    }

    public function test_admin_can_reorder_modules(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $course = $this->createCourseWithSubject();
        $moduleA = HocPhan::create([
            'course_id' => $course->id,
            'title' => 'HocPhan A',
            'session_count' => 3,
            'position' => 1,
            'status' => HocPhan::STATUS_PUBLISHED,
        ]);
        $moduleB = HocPhan::create([
            'course_id' => $course->id,
            'title' => 'HocPhan B',
            'session_count' => 3,
            'position' => 2,
            'status' => HocPhan::STATUS_PUBLISHED,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.modules.reorder', $course), [
                'positions' => [
                    $moduleA->id => 2,
                    $moduleB->id => 1,
                ],
            ]);

        $response->assertRedirect(route('admin.courses.modules.index', $course));
        $this->assertDatabaseHas('chuong_hoc', ['id' => $moduleA->id, 'position' => 2]);
        $this->assertDatabaseHas('chuong_hoc', ['id' => $moduleB->id, 'position' => 1]);
    }

    public function test_admin_can_delete_module_without_dependencies(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $course = $this->createCourseWithSubject();
        $module = HocPhan::create([
            'course_id' => $course->id,
            'title' => 'HocPhan Xoa',
            'session_count' => 2,
            'position' => 1,
            'status' => HocPhan::STATUS_PUBLISHED,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.modules.delete', [$course, $module]));

        $response->assertRedirect(route('admin.courses.modules.index', $course));
        $this->assertDatabaseMissing('chuong_hoc', ['id' => $module->id]);
    }

    public function test_delete_module_with_lessons_and_quiz_hard_deletes_related_content(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $student = NguoiDung::factory()->student()->create();
        $course = $this->createCourseWithSubject();
        $module = HocPhan::create([
            'course_id' => $course->id,
            'title' => 'HocPhan Co Bai Hoc',
            'session_count' => 2,
            'position' => 1,
            'status' => HocPhan::STATUS_PUBLISHED,
        ]);
        $lesson = BaiHoc::create([
            'module_id' => $module->id,
            'title' => 'Bai hoc 1',
            'content' => 'Noi dung bai hoc 1',
        ]);
        $quiz = BaiKiemTra::create([
            'lesson_id' => $lesson->id,
            'title' => 'BaiKiemTra 1',
            'passing_score' => 70,
            'is_required' => true,
            'max_attempts' => 3,
        ]);
        $question = CauHoi::create([
            'quiz_id' => $quiz->id,
            'question' => 'Cau hoi 1',
            'type' => 'multiple_choice',
            'order' => 1,
            'points' => 1,
        ]);
        $option = LuaChon::create([
            'question_id' => $question->id,
            'option_text' => 'Lua chon 1',
            'is_correct' => true,
            'order' => 1,
        ]);
        TraLoiBaiKiemTra::create([
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'question_id' => $question->id,
            'option_id' => $option->id,
            'answer_text' => null,
            'is_correct' => true,
            'attempt' => 1,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.modules.delete', [$course, $module]));

        $response->assertRedirect(route('admin.courses.modules.index', $course));
        $this->assertDatabaseMissing('chuong_hoc', ['id' => $module->id]);
        $this->assertDatabaseMissing('bai_hoc', ['id' => $lesson->id]);
        $this->assertDatabaseMissing('bai_kiem_tra', ['id' => $quiz->id]);
        $this->assertDatabaseMissing('cau_hoi', ['id' => $question->id]);
        $this->assertDatabaseMissing('lua_chon', ['id' => $option->id]);
        $this->assertDatabaseMissing('tra_loi_kiem_tra', ['user_id' => $student->id, 'quiz_id' => $quiz->id, 'question_id' => $question->id]);
    }

    public function test_student_is_blocked_from_module_management(): void
    {
        $student = NguoiDung::factory()->student()->create();
        $course = $this->createCourseWithSubject();

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('admin.courses.modules.index', $course));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_teacher_is_blocked_from_module_management(): void
    {
        $teacher = NguoiDung::factory()->teacher()->create();
        $course = $this->createCourseWithSubject();

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->get(route('admin.courses.modules.index', $course));

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }

    private function createCourseWithSubject(string $subjectName = 'Tin hoc van phong', string $categoryName = 'Tin hoc'): KhoaHoc
    {
        $category = NhomHoc::create([
            'name' => $categoryName,
            'slug' => Str::slug($categoryName),
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);

        $subject = MonHoc::create([
            'name' => $subjectName,
            'category_id' => $category->id,
            'status' => MonHoc::STATUS_OPEN,
            'duration' => 40,
            'price' => 1500000,
        ]);

        return KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => $subjectName . ' - Lop toi',
            'schedule' => 'Thu 2 - Thu 4',
        ]);
    }
}

