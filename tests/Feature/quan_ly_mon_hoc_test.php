<?php

namespace Tests\Feature;

use App\Models\NhomHoc;
use App\Models\KhoaHoc;
use App\Models\GhiDanh;
use App\Models\HocPhan;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class quan_ly_mon_hoc_test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_filter_subjects(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $categoryA = NhomHoc::create([
            'name' => 'Ngoai ngu',
            'slug' => 'ngoai-ngu',
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);
        $categoryB = NhomHoc::create([
            'name' => 'Tin hoc',
            'slug' => 'tin-hoc',
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);
        $target = MonHoc::create([
            'name' => 'Tieng Anh giao tiep',
            'description' => 'Khoa hoc giao tiep co ban',
            'category_id' => $categoryA->id,
            'status' => MonHoc::STATUS_OPEN,
            'duration' => 40,
            'price' => 1500000,
        ]);
        $other = MonHoc::create([
            'name' => 'Tin hoc van phong',
            'category_id' => $categoryB->id,
            'status' => MonHoc::STATUS_DRAFT,
            'duration' => 30,
            'price' => 1200000,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.subjects', [
                'search' => 'giao tiep',
                'category_id' => $categoryA->id,
                'status' => MonHoc::STATUS_OPEN,
            ]));

        $response->assertOk();
        $response->assertSee('Quản lý khóa học');
        $response->assertSee($target->name);
        $response->assertDontSee($other->name);
    }

    public function test_admin_can_create_public_course(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $category = NhomHoc::create([
            'name' => 'Tin hoc',
            'slug' => 'tin-hoc',
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.subjects.create'), [
                'name' => 'Tin hoc van phong',
                'description' => 'Khoa hoc danh cho nguoi moi bat dau',
                'category_id' => $category->id,
                'price' => 1800000,
                'duration' => 36,
                'status' => MonHoc::STATUS_OPEN,
            ]);

        $subject = MonHoc::where('name', 'Tin hoc van phong')->first();

        $response->assertRedirect(route('admin.subject.show', $subject));
        $this->assertDatabaseHas('mon_hoc', [
            'id' => $subject->id,
            'category_id' => $category->id,
            'price' => 1800000,
            'duration' => 36,
            'status' => MonHoc::STATUS_OPEN,
        ]);
    }

    public function test_admin_can_create_public_course_and_return_to_study_group_detail(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $category = NhomHoc::create([
            'name' => 'Ngoai ngu - Tin hoc',
            'slug' => 'ngoai-ngu-tin-hoc',
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.subjects.create'), [
                'name' => 'Tin hoc ung dung van phong',
                'description' => 'Khoa hoc moi duoc tao ngay tu nhom hoc',
                'category_id' => $category->id,
                'return_to_category_id' => $category->id,
                'price' => 1950000,
                'duration' => 42,
                'status' => MonHoc::STATUS_OPEN,
            ]);

        $subject = MonHoc::where('name', 'Tin hoc ung dung van phong')->first();

        $response->assertRedirect(route('admin.categories.show', $category));
        $this->assertDatabaseHas('mon_hoc', [
            'id' => $subject->id,
            'category_id' => $category->id,
            'price' => 1950000,
            'duration' => 42,
            'status' => MonHoc::STATUS_OPEN,
        ]);
    }

    public function test_admin_can_update_public_course(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $category = NhomHoc::create([
            'name' => 'Ngoai ngu',
            'slug' => 'ngoai-ngu',
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);
        $subject = MonHoc::create([
            'name' => 'Tieng Anh co ban',
            'category_id' => $category->id,
            'status' => MonHoc::STATUS_DRAFT,
            'price' => 900000,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.subjects.update', $subject), [
                'name' => 'Tieng Anh giao tiep',
                'description' => 'Cap nhat mo ta moi',
                'category_id' => $category->id,
                'price' => 1400000,
                'duration' => 48,
                'status' => MonHoc::STATUS_CLOSED,
            ]);

        $response->assertRedirect(route('admin.subject.show', $subject));
        $this->assertDatabaseHas('mon_hoc', [
            'id' => $subject->id,
            'name' => 'Tieng Anh giao tiep',
            'price' => 1400000,
            'duration' => 48,
            'status' => MonHoc::STATUS_CLOSED,
        ]);
    }

    public function test_updating_subject_syncs_inherited_internal_course_data_only(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $category = NhomHoc::create([
            'name' => 'Ngoai ngu',
            'slug' => 'ngoai-ngu',
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);

        $subject = MonHoc::create([
            'name' => 'Tieng Anh giao tiep',
            'description' => 'Mo ta cu',
            'category_id' => $category->id,
            'status' => MonHoc::STATUS_OPEN,
            'price' => 1200000,
        ]);

        $inheritedCourse = KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => 'KhaiTriEdu 2026 - Tieng Anh giao tiep',
            'description' => 'Mo ta cu',
            'price' => 1200000,
        ]);

        $customizedCourse = KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => 'Lop dac biet cho doanh nghiep',
            'description' => 'Noi dung tuy chinh',
            'price' => 1999000,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.subjects.update', $subject), [
                'name' => 'Tieng Anh giao tiep nang cao',
                'description' => 'Mo ta moi',
                'category_id' => $category->id,
                'price' => 1500000,
                'duration' => 48,
                'status' => MonHoc::STATUS_OPEN,
            ]);

        $response->assertRedirect(route('admin.subject.show', $subject));

        $this->assertDatabaseHas('khoa_hoc', [
            'id' => $inheritedCourse->id,
            'title' => 'KhaiTriEdu 2026 - Tieng Anh giao tiep nang cao',
            'description' => 'Mo ta moi',
            'price' => 1500000,
        ]);

        $this->assertDatabaseHas('khoa_hoc', [
            'id' => $customizedCourse->id,
            'title' => 'Lop dac biet cho doanh nghiep',
            'description' => 'Noi dung tuy chinh',
            'price' => 1999000,
        ]);
    }

    public function test_admin_can_view_public_course_detail_with_counts(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $teacher = NguoiDung::factory()->teacher()->create(['name' => 'Giang Vien A']);
        $student = NguoiDung::factory()->student()->create();
        $category = NhomHoc::create([
            'name' => 'Ngoai ngu',
            'slug' => 'ngoai-ngu',
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);
        $subject = MonHoc::create([
            'name' => 'Tieng Anh giao tiep',
            'category_id' => $category->id,
            'status' => MonHoc::STATUS_OPEN,
            'duration' => 40,
            'price' => 1600000,
        ]);
        $course = KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => 'Tieng Anh giao tiep - Lop toi',
            'teacher_id' => $teacher->id,
            'schedule' => 'Thu 2 - Thu 4',
        ]);
        HocPhan::create([
            'course_id' => $course->id,
            'title' => 'HocPhan 1',
            'content' => 'Noi dung',
            'position' => 1,
        ]);
        GhiDanh::create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'course_id' => $course->id,
            'status' => 'pending',
            'is_submitted' => true,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.subject.show', $subject));

        $response->assertOk();
        $response->assertSee($subject->name);
        $response->assertSee('Tieng Anh giao tiep - Lop toi');
        $response->assertSee('1 module hiện có');
        $response->assertSee('1 lượt');
    }

    public function test_delete_route_archives_subject_with_dependencies_instead_of_hard_delete(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $subject = MonHoc::create([
            'name' => 'Tin hoc van phong',
            'status' => MonHoc::STATUS_OPEN,
        ]);
        KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => 'Lop co du lieu',
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.subjects.delete', $subject));

        $response->assertRedirect(route('admin.subject.show', $subject));
        $this->assertDatabaseHas('mon_hoc', [
            'id' => $subject->id,
            'status' => MonHoc::STATUS_ARCHIVED,
        ]);
    }

    public function test_admin_can_reopen_archived_subject(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $subject = MonHoc::create([
            'name' => 'Khoa hoc luu tru',
            'status' => MonHoc::STATUS_ARCHIVED,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.subjects.reopen', $subject));

        $response->assertRedirect(route('admin.subject.show', $subject));
        $this->assertDatabaseHas('mon_hoc', [
            'id' => $subject->id,
            'status' => MonHoc::STATUS_OPEN,
        ]);
    }

    public function test_student_is_blocked_from_subject_management(): void
    {
        $student = NguoiDung::factory()->student()->create();

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('admin.subjects'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_teacher_is_blocked_from_subject_management(): void
    {
        $teacher = NguoiDung::factory()->teacher()->create();

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->get(route('admin.subjects'));

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }
}
