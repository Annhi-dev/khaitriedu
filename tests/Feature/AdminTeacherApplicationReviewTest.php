<?php

namespace Tests\Feature;

use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTeacherApplicationReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_filter_teacher_applications(): void
    {
        $admin = User::factory()->admin()->create();
        $pending = TeacherApplication::create([
            'name' => 'Ung vien Pending',
            'email' => 'pending@example.com',
            'experience' => 'Day giao tiep co ban',
            'status' => TeacherApplication::STATUS_PENDING,
        ]);
        $revision = TeacherApplication::create([
            'name' => 'Ung vien Revision',
            'email' => 'revision@example.com',
            'experience' => 'Can bo sung ho so',
            'status' => TeacherApplication::STATUS_NEEDS_REVISION,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.teacher-applications', [
                'search' => 'bo sung',
                'status' => TeacherApplication::STATUS_NEEDS_REVISION,
            ]));

        $response->assertOk();
        $response->assertSee($revision->email);
        $response->assertDontSee($pending->email);
    }

    public function test_admin_can_approve_teacher_application_and_create_teacher_account(): void
    {
        $admin = User::factory()->admin()->create();
        $application = TeacherApplication::create([
            'name' => 'Ung vien Moi',
            'email' => 'ungvienmoi@example.com',
            'phone' => '0909999888',
            'experience' => '3 nam day tieng Anh',
            'message' => 'Muốn tham gia giảng dạy',
            'status' => TeacherApplication::STATUS_PENDING,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.teacher-applications.review', $application), [
                'action' => TeacherApplication::STATUS_APPROVED,
                'admin_note' => 'Hồ sơ phù hợp, duyệt ngay.',
            ]);

        $response->assertRedirect(route('admin.teacher-applications.show', $application));
        $this->assertDatabaseHas('don_ung_tuyen_giao_vien', [
            'id' => $application->id,
            'status' => TeacherApplication::STATUS_APPROVED,
            'admin_note' => 'Hồ sơ phù hợp, duyệt ngay.',
            'reviewed_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('nguoi_dung', [
            'email' => 'ungvienmoi@example.com',
            'role_id' => \App\Models\Role::idByName(User::ROLE_TEACHER),
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function test_admin_can_approve_teacher_application_and_upgrade_existing_user(): void
    {
        $admin = User::factory()->admin()->create();
        $existingUser = User::factory()->student()->create([
            'name' => 'Hoc vien chuyen role',
            'email' => 'upgrade@example.com',
            'status' => User::STATUS_LOCKED,
        ]);
        $application = TeacherApplication::create([
            'name' => 'Ung vien da co tai khoan',
            'email' => 'upgrade@example.com',
            'status' => TeacherApplication::STATUS_PENDING,
        ]);

        $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.teacher-applications.review', $application), [
                'action' => TeacherApplication::STATUS_APPROVED,
            ])
            ->assertRedirect(route('admin.teacher-applications.show', $application));

        $this->assertDatabaseHas('nguoi_dung', [
            'id' => $existingUser->id,
            'email' => 'upgrade@example.com',
            'role_id' => \App\Models\Role::idByName(User::ROLE_TEACHER),
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function test_admin_can_reject_teacher_application_with_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $application = TeacherApplication::create([
            'name' => 'Ung vien bi tu choi',
            'email' => 'reject@example.com',
            'status' => TeacherApplication::STATUS_PENDING,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.teacher-applications.review', $application), [
                'action' => TeacherApplication::STATUS_REJECTED,
                'rejection_reason' => 'Chưa phù hợp yêu cầu chuyên môn hiện tại.',
            ]);

        $response->assertRedirect(route('admin.teacher-applications.show', $application));
        $this->assertDatabaseHas('don_ung_tuyen_giao_vien', [
            'id' => $application->id,
            'status' => TeacherApplication::STATUS_REJECTED,
            'rejection_reason' => 'Chưa phù hợp yêu cầu chuyên môn hiện tại.',
        ]);
        $this->assertDatabaseMissing('nguoi_dung', [
            'email' => 'reject@example.com',
            'role_id' => \App\Models\Role::idByName(User::ROLE_TEACHER),
        ]);
    }

    public function test_admin_can_mark_teacher_application_needs_revision(): void
    {
        $admin = User::factory()->admin()->create();
        $application = TeacherApplication::create([
            'name' => 'Ung vien can bo sung',
            'email' => 'revision@example.com',
            'status' => TeacherApplication::STATUS_PENDING,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.teacher-applications.review', $application), [
                'action' => TeacherApplication::STATUS_NEEDS_REVISION,
                'admin_note' => 'Vui lòng bổ sung minh chứng kinh nghiệm giảng dạy.',
            ]);

        $response->assertRedirect(route('admin.teacher-applications.show', $application));
        $this->assertDatabaseHas('don_ung_tuyen_giao_vien', [
            'id' => $application->id,
            'status' => TeacherApplication::STATUS_NEEDS_REVISION,
            'admin_note' => 'Vui lòng bổ sung minh chứng kinh nghiệm giảng dạy.',
        ]);
    }

    public function test_review_requires_reason_or_admin_note_by_action(): void
    {
        $admin = User::factory()->admin()->create();
        $application = TeacherApplication::create([
            'name' => 'Ung vien validate',
            'email' => 'validate@example.com',
            'status' => TeacherApplication::STATUS_PENDING,
        ]);

        $rejectResponse = $this
            ->from(route('admin.teacher-applications.show', $application))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.teacher-applications.review', $application), [
                'action' => TeacherApplication::STATUS_REJECTED,
            ]);

        $rejectResponse->assertRedirect(route('admin.teacher-applications.show', $application));
        $rejectResponse->assertSessionHasErrors(['rejection_reason']);

        $revisionResponse = $this
            ->from(route('admin.teacher-applications.show', $application))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.teacher-applications.review', $application), [
                'action' => TeacherApplication::STATUS_NEEDS_REVISION,
            ]);

        $revisionResponse->assertRedirect(route('admin.teacher-applications.show', $application));
        $revisionResponse->assertSessionHasErrors(['admin_note']);
    }

    public function test_student_is_blocked_from_teacher_application_management(): void
    {
        $student = User::factory()->student()->create();

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('admin.teacher-applications'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_teacher_is_blocked_from_teacher_application_management(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->get(route('admin.teacher-applications'));

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }
}