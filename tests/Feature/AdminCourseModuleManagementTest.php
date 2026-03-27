<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCourseModuleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_module_management_page(): void
    {
        $admin = User::factory()->admin()->create();
        $course = $this->createCourseWithSubject();
        Module::create([
            'course_id' => $course->id,
            'title' => 'Module 1',
            'status' => Module::STATUS_PUBLISHED,
            'position' => 1,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.courses.modules.index', $course));

        $response->assertOk();
        $response->assertSee('Quản lý module');
        $response->assertSee($course->title);
        $response->assertSee('Module 1');
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
                'duration' => 90,
                'position' => 1,
                'status' => Module::STATUS_PUBLISHED,
            ]);

        $response->assertRedirect(route('admin.courses.modules.index', $course));
        $this->assertDatabaseHas('chuong_hoc', [
            'course_id' => $course->id,
            'title' => 'Module Nhap Mon',
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
            'position' => 1,
            'status' => Module::STATUS_PUBLISHED,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.modules.update', [$course, $module]), [
                'title' => 'Module Da Sua',
                'content' => 'Noi dung moi',
                'duration' => 120,
                'position' => 2,
                'status' => Module::STATUS_UNPUBLISHED,
            ]);

        $response->assertRedirect(route('admin.courses.modules.index', $course));
        $this->assertDatabaseHas('chuong_hoc', [
            'id' => $module->id,
            'title' => 'Module Da Sua',
            'duration' => 120,
            'position' => 2,
            'status' => Module::STATUS_UNPUBLISHED,
        ]);
    }

    public function test_admin_can_reorder_modules(): void
    {
        $admin = User::factory()->admin()->create();
        $course = $this->createCourseWithSubject();
        $moduleA = Module::create([
            'course_id' => $course->id,
            'title' => 'Module A',
            'position' => 1,
            'status' => Module::STATUS_PUBLISHED,
        ]);
        $moduleB = Module::create([
            'course_id' => $course->id,
            'title' => 'Module B',
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
            'position' => 1,
            'status' => Module::STATUS_PUBLISHED,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.modules.delete', [$course, $module]));

        $response->assertRedirect(route('admin.courses.modules.index', $course));
        $this->assertDatabaseMissing('chuong_hoc', ['id' => $module->id]);
    }

    public function test_delete_module_with_lessons_turns_it_unpublished_instead_of_hard_delete(): void
    {
        $admin = User::factory()->admin()->create();
        $course = $this->createCourseWithSubject();
        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Module Co Bai Hoc',
            'position' => 1,
            'status' => Module::STATUS_PUBLISHED,
        ]);
        Lesson::create([
            'module_id' => $module->id,
            'title' => 'Bai hoc 1',
            'content' => 'Noi dung bai hoc 1',
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.modules.delete', [$course, $module]));

        $response->assertRedirect(route('admin.courses.modules.index', $course));
        $this->assertDatabaseHas('chuong_hoc', [
            'id' => $module->id,
            'status' => Module::STATUS_UNPUBLISHED,
        ]);
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

    private function createCourseWithSubject(): Course
    {
        $category = Category::create([
            'name' => 'Tin hoc',
            'slug' => 'tin-hoc',
            'status' => Category::STATUS_ACTIVE,
        ]);

        $subject = Subject::create([
            'name' => 'Tin hoc van phong',
            'category_id' => $category->id,
            'status' => Subject::STATUS_OPEN,
            'duration' => 40,
            'price' => 1500000,
        ]);

        return Course::create([
            'subject_id' => $subject->id,
            'title' => 'Tin hoc van phong - Lop toi',
            'schedule' => 'Thu 2 - Thu 4',
        ]);
    }
}