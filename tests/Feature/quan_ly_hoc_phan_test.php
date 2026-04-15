<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class quan_ly_hoc_phan_test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_module_management_page(): void
    {
        $admin = User::factory()->admin()->create();
        $course = $this->createCourseWithSubject();
        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Listening',
            'content' => 'Nghe hội thoại cơ bản và bắt ý chính.',
            'session_count' => 5,
            'status' => Module::STATUS_PUBLISHED,
            'position' => 1,
        ]);
        Lesson::create([
            'module_id' => $module->id,
            'title' => 'Buổi 1',
            'description' => 'Nhận diện chủ đề và từ khóa.',
            'content' => 'Nội dung buổi 1.',
            'order' => 1,
            'duration' => 45,
        ]);
        Lesson::create([
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
        $admin = User::factory()->admin()->create();
        $course = $this->createCourseWithSubject();

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.modules.create', $course), [
                'title' => 'Module Nhap Mon',
                'content' => 'Tong quan noi dung',
                'session_count' => 6,
                'duration' => 90,
                'position' => 1,
                'status' => Module::STATUS_PUBLISHED,
            ]);

        $response->assertRedirect(route('admin.courses.modules.index', $course));
        $this->assertDatabaseHas('chuong_hoc', [
            'course_id' => $course->id,
            'title' => 'Module Nhap Mon',
            'session_count' => 6,
            'duration' => 90,
            'status' => Module::STATUS_PUBLISHED,
            'position' => 1,
        ]);
    }

    public function test_admin_can_update_module(): void
    {
        $admin = User::factory()->admin()->create();
        $course = $this->createCourseWithSubject();
        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Module Cu',
            'session_count' => 3,
            'position' => 1,
            'status' => Module::STATUS_PUBLISHED,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.modules.update', [$course, $module]), [
                'title' => 'Module Da Sua',
                'content' => 'Noi dung moi',
                'session_count' => 4,
                'duration' => 120,
                'position' => 2,
                'status' => Module::STATUS_UNPUBLISHED,
            ]);

        $response->assertRedirect(route('admin.courses.modules.index', $course));
        $this->assertDatabaseHas('chuong_hoc', [
            'id' => $module->id,
            'title' => 'Module Da Sua',
            'session_count' => 4,
            'duration' => 120,
            'position' => 2,
            'status' => Module::STATUS_UNPUBLISHED,
        ]);
    }

    public function test_admin_can_sync_curriculum_template_for_a_course(): void
    {
        $admin = User::factory()->admin()->create();
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
        $admin = User::factory()->admin()->create();
        $course = $this->createCourseWithSubject();
        $moduleA = Module::create([
            'course_id' => $course->id,
            'title' => 'Module A',
            'session_count' => 3,
            'position' => 1,
            'status' => Module::STATUS_PUBLISHED,
        ]);
        $moduleB = Module::create([
            'course_id' => $course->id,
            'title' => 'Module B',
            'session_count' => 3,
            'position' => 2,
            'status' => Module::STATUS_PUBLISHED,
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
        $admin = User::factory()->admin()->create();
        $course = $this->createCourseWithSubject();
        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Module Xoa',
            'session_count' => 2,
            'position' => 1,
            'status' => Module::STATUS_PUBLISHED,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.modules.delete', [$course, $module]));

        $response->assertRedirect(route('admin.courses.modules.index', $course));
        $this->assertDatabaseMissing('chuong_hoc', ['id' => $module->id]);
    }

    public function test_delete_module_with_lessons_and_quiz_hard_deletes_related_content(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        $course = $this->createCourseWithSubject();
        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Module Co Bai Hoc',
            'session_count' => 2,
            'position' => 1,
            'status' => Module::STATUS_PUBLISHED,
        ]);
        $lesson = Lesson::create([
            'module_id' => $module->id,
            'title' => 'Bai hoc 1',
            'content' => 'Noi dung bai hoc 1',
        ]);
        $quiz = Quiz::create([
            'lesson_id' => $lesson->id,
            'title' => 'Quiz 1',
            'passing_score' => 70,
            'is_required' => true,
            'max_attempts' => 3,
        ]);
        $question = Question::create([
            'quiz_id' => $quiz->id,
            'question' => 'Cau hoi 1',
            'type' => 'multiple_choice',
            'order' => 1,
            'points' => 1,
        ]);
        $option = Option::create([
            'question_id' => $question->id,
            'option_text' => 'Lua chon 1',
            'is_correct' => true,
            'order' => 1,
        ]);
        QuizAnswer::create([
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
        $student = User::factory()->student()->create();
        $course = $this->createCourseWithSubject();

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('admin.courses.modules.index', $course));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_teacher_is_blocked_from_module_management(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = $this->createCourseWithSubject();

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->get(route('admin.courses.modules.index', $course));

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }

    private function createCourseWithSubject(string $subjectName = 'Tin hoc van phong', string $categoryName = 'Tin hoc'): Course
    {
        $category = Category::create([
            'name' => $categoryName,
            'slug' => Str::slug($categoryName),
            'status' => Category::STATUS_ACTIVE,
        ]);

        $subject = Subject::create([
            'name' => $subjectName,
            'category_id' => $category->id,
            'status' => Subject::STATUS_OPEN,
            'duration' => 40,
            'price' => 1500000,
        ]);

        return Course::create([
            'subject_id' => $subject->id,
            'title' => $subjectName . ' - Lop toi',
            'schedule' => 'Thu 2 - Thu 4',
        ]);
    }
}

