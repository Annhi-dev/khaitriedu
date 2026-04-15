<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class quan_ly_nhom_hoc_test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_study_group_list(): void
    {
        $admin = User::factory()->admin()->create();
        $visible = Category::create([
            'name' => 'Ngoai ngu - Tin hoc',
            'slug' => 'ngoai-ngu-tin-hoc',
            'program' => 'Tong hop',
            'level' => 'Co ban',
            'status' => Category::STATUS_ACTIVE,
            'order' => 1,
        ]);
        $hidden = Category::create([
            'name' => 'Ky nang mem',
            'slug' => 'ky-nang-mem',
            'status' => Category::STATUS_INACTIVE,
            'order' => 2,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.categories'));

        $response->assertOk();
        $response->assertSee('Quản lý nhóm học');
        $response->assertSee($visible->name);
        $response->assertSee($hidden->name);
    }

    public function test_admin_can_filter_study_groups(): void
    {
        $admin = User::factory()->admin()->create();
        $target = Category::create([
            'name' => 'Tieng Anh Thieu Nhi',
            'slug' => 'tieng-anh-thieu-nhi',
            'program' => 'Tre em',
            'status' => Category::STATUS_INACTIVE,
            'order' => 1,
        ]);
        $other = Category::create([
            'name' => 'Tin hoc van phong',
            'slug' => 'tin-hoc-van-phong',
            'program' => 'Nguoi lon',
            'status' => Category::STATUS_ACTIVE,
            'order' => 2,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.categories', [
                'search' => 'Thieu Nhi',
                'status' => Category::STATUS_INACTIVE,
            ]));

        $response->assertOk();
        $response->assertSee($target->name);
        $response->assertDontSee($other->name);
    }

    public function test_admin_can_create_study_group(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.categories.create'), [
                'name' => 'Ngoai ngu - Tin hoc',
                'slug' => '',
                'description' => 'Nhom hoc tong hop cho cac khoa ngoai ngu va tin hoc.',
                'program' => 'Tong hop',
                'level' => 'Co ban',
                'status' => Category::STATUS_ACTIVE,
                'order' => 5,
            ]);

        $category = Category::where('name', 'Ngoai ngu - Tin hoc')->first();

        $response->assertRedirect(route('admin.categories.show', $category));
        $this->assertDatabaseHas('danh_muc', [
            'id' => $category->id,
            'slug' => 'ngoai-ngu-tin-hoc',
            'status' => Category::STATUS_ACTIVE,
            'program' => 'Tong hop',
            'level' => 'Co ban',
        ]);
    }

    public function test_admin_can_update_study_group(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::create([
            'name' => 'Nhom hoc cu',
            'slug' => 'nhom-hoc-cu',
            'status' => Category::STATUS_ACTIVE,
            'order' => 1,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.categories.update', $category), [
                'name' => 'Nhom hoc da sua',
                'slug' => 'nhom-hoc-da-sua',
                'description' => 'Da bo sung mo ta moi',
                'program' => 'Luyen thi',
                'level' => 'Nang cao',
                'status' => Category::STATUS_INACTIVE,
                'order' => 9,
            ]);

        $response->assertRedirect(route('admin.categories.show', $category));
        $this->assertDatabaseHas('danh_muc', [
            'id' => $category->id,
            'name' => 'Nhom hoc da sua',
            'slug' => 'nhom-hoc-da-sua',
            'program' => 'Luyen thi',
            'level' => 'Nang cao',
            'status' => Category::STATUS_INACTIVE,
            'order' => 9,
        ]);
    }

    public function test_admin_can_view_study_group_detail(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::create([
            'name' => 'Ngoai ngu',
            'slug' => 'ngoai-ngu',
            'description' => 'Nhom ngoai ngu tong hop',
            'program' => 'Giao tiep',
            'level' => 'A1',
            'status' => Category::STATUS_ACTIVE,
        ]);
        Subject::create([
            'name' => 'Tieng Anh giao tiep',
            'description' => 'Khoa hoc giao tiep co ban',
            'price' => 1200000,
            'category_id' => $category->id,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.categories.show', $category));

        $response->assertOk();
        $response->assertSee($category->name);
        $response->assertSee('Tieng Anh giao tiep');
        $response->assertSee('Giao tiep');
    }

    public function test_study_group_detail_shows_only_actual_courses_of_that_group(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::create([
            'name' => 'Ngoai ngu - Tin hoc',
            'slug' => 'ngoai-ngu-tin-hoc',
            'status' => Category::STATUS_ACTIVE,
        ]);
        $subject = Subject::create([
            'name' => 'Ngoai ngu - Tin hoc',
            'price' => 1500000,
            'category_id' => $category->id,
        ]);
        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => 'Tin hoc van phong',
            'description' => 'Khoa hoc thuoc nhom dang xem',
        ]);

        $otherCategory = Category::create([
            'name' => 'Dao tao nghe',
            'slug' => 'dao-tao-nghe',
            'status' => Category::STATUS_ACTIVE,
        ]);
        $otherSubject = Subject::create([
            'name' => 'Dao tao nghe',
            'price' => 1800000,
            'category_id' => $otherCategory->id,
        ]);
        $otherCourse = Course::create([
            'subject_id' => $otherSubject->id,
            'title' => 'Ke toan doanh nghiep',
            'description' => 'Khoa hoc thuoc nhom khac',
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.categories.show', $category));

        $response->assertOk();
        $response->assertSee('Danh sách khóa học trong nhóm');
        $response->assertSee($course->title);
        $response->assertDontSee($otherCourse->title);
    }

    public function test_admin_can_open_course_management_from_study_group_context(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::create([
            'name' => 'Ngoai ngu - Tin hoc',
            'slug' => 'ngoai-ngu-tin-hoc',
            'status' => Category::STATUS_ACTIVE,
        ]);
        $subject = Subject::create([
            'name' => 'Ngoai ngu - Tin hoc',
            'price' => 1500000,
            'category_id' => $category->id,
        ]);
        $course = Course::create([
            'subject_id' => $subject->id,
            'title' => 'Anh van thieu nhi',
        ]);

        $otherCategory = Category::create([
            'name' => 'Dao tao dai han',
            'slug' => 'dao-tao-dai-han',
            'status' => Category::STATUS_ACTIVE,
        ]);
        $otherSubject = Subject::create([
            'name' => 'Dao tao dai han',
            'price' => 2000000,
            'category_id' => $otherCategory->id,
        ]);
        $otherCourse = Course::create([
            'subject_id' => $otherSubject->id,
            'title' => 'Lien thong dai hoc',
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.courses', [
                'subject_id' => $subject->id,
                'return_to_category_id' => $category->id,
            ]));

        $response->assertOk();
        $response->assertSee('Khóa học trong nhóm ' . $category->name);
        $response->assertSee($course->title);
        $response->assertDontSee($otherCourse->title);
    }

    public function test_admin_can_create_course_directly_from_study_group_context(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::create([
            'name' => 'Ngoai ngu - Tin hoc',
            'slug' => 'ngoai-ngu-tin-hoc',
            'status' => Category::STATUS_ACTIVE,
        ]);
        $subject = Subject::create([
            'name' => 'Ngoai ngu - Tin hoc',
            'price' => 1500000,
            'category_id' => $category->id,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.create'), [
                'subject_id' => $subject->id,
                'title' => 'Tin hoc van phong',
                'description' => 'Khoa hoc moi trong nhom hoc',
                'schedule' => 'T2-T4-T6',
                'return_to_category_id' => $category->id,
            ]);

        $response->assertRedirect(route('admin.categories.show', $category));
        $this->assertDatabaseHas('khoa_hoc', [
            'subject_id' => $subject->id,
            'title' => 'Tin hoc van phong',
            'schedule' => 'T2-T4-T6',
        ]);
    }

    public function test_admin_delete_route_deactivates_but_does_not_hard_delete_group_with_dependencies(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::create([
            'name' => 'Tin hoc',
            'slug' => 'tin-hoc',
            'status' => Category::STATUS_ACTIVE,
        ]);
        $subject = Subject::create([
            'name' => 'Tin hoc van phong',
            'price' => 1500000,
            'category_id' => $category->id,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.categories.delete', $category));

        $response->assertRedirect(route('admin.categories.show', $category));
        $this->assertDatabaseHas('danh_muc', [
            'id' => $category->id,
            'status' => Category::STATUS_INACTIVE,
        ]);
        $this->assertDatabaseHas('mon_hoc', [
            'id' => $subject->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_admin_can_activate_study_group_again(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::create([
            'name' => 'Nhom hoc tam dung',
            'slug' => 'nhom-hoc-tam-dung',
            'status' => Category::STATUS_INACTIVE,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.categories.activate', $category));

        $response->assertRedirect(route('admin.categories.show', $category));
        $this->assertDatabaseHas('danh_muc', [
            'id' => $category->id,
            'status' => Category::STATUS_ACTIVE,
        ]);
    }

    public function test_student_is_blocked_from_study_group_management(): void
    {
        $student = User::factory()->student()->create();

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('admin.categories'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_teacher_is_blocked_from_study_group_management(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->get(route('admin.categories'));

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }
}
