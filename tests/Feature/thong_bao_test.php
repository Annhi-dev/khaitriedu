<?php

namespace Tests\Feature;

use App\Models\ThongBao;
use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class thong_bao_test extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_open_notification_inbox_and_mark_it_as_read(): void
    {
        $student = NguoiDung::factory()->student()->create(['name' => 'Hoc vien thong bao']);

        $notification = ThongBao::create([
            'user_id' => $student->id,
            'title' => 'Lịch học đã thay đổi',
            'message' => 'Thời khóa biểu của bạn vừa được cập nhật.',
            'type' => 'info',
            'link' => route('student.schedule'),
            'is_read' => false,
        ]);

        $response = $this
            ->actingAs($student)
            ->withSession(['user_id' => $student->id])
            ->withoutMiddleware()
            ->get('/student/notifications');

        $response->assertOk();
        $response->assertSee('Hộp thông báo');
        $response->assertSee('Lịch học đã thay đổi');

        $openResponse = $this
            ->actingAs($student)
            ->withSession(['user_id' => $student->id])
            ->withoutMiddleware()
            ->get('/student/notifications/' . $notification->id);

        $openResponse->assertOk();
        $openResponse->assertSee('Chi tiết thông báo');
        $openResponse->assertSee('Lịch học đã thay đổi');
        $openResponse->assertSee(route('student.schedule', [], false), false);

        $notification->refresh();

        $this->assertTrue($notification->is_read);
        $this->assertNotNull($notification->read_at);
    }

    public function test_teacher_can_open_notification_inbox_and_mark_it_as_read(): void
    {
        $teacher = NguoiDung::factory()->teacher()->create(['name' => 'Giang vien thong bao']);

        $notification = ThongBao::create([
            'user_id' => $teacher->id,
            'title' => 'Yêu cầu dời buổi đã được duyệt',
            'message' => 'Admin vừa chốt lại lịch dạy mới cho lớp của bạn.',
            'type' => 'success',
            'link' => route('teacher.schedule-change-requests.index'),
            'is_read' => false,
        ]);

        $response = $this
            ->actingAs($teacher)
            ->withSession(['user_id' => $teacher->id])
            ->withoutMiddleware()
            ->get('/teacher/notifications');

        $response->assertOk();
        $response->assertSee('Hộp thông báo');
        $response->assertSee('Yêu cầu dời buổi đã được duyệt');

        $openResponse = $this
            ->actingAs($teacher)
            ->withSession(['user_id' => $teacher->id])
            ->withoutMiddleware()
            ->get('/teacher/notifications/' . $notification->id);

        $openResponse->assertOk();
        $openResponse->assertSee('Chi tiết thông báo');
        $openResponse->assertSee('Yêu cầu dời buổi đã được duyệt');
        $openResponse->assertSee(route('teacher.schedule-change-requests.index', [], false), false);

        $notification->refresh();

        $this->assertTrue($notification->is_read);
        $this->assertNotNull($notification->read_at);
    }

    public function test_admin_can_open_notification_inbox_and_mark_it_as_read(): void
    {
        $admin = NguoiDung::factory()->admin()->create(['name' => 'Admin thong bao']);

        $notification = ThongBao::create([
            'user_id' => $admin->id,
            'title' => 'Đăng ký lớp cần duyệt',
            'message' => 'Có 1 yêu cầu đăng ký mới đang chờ xử lý.',
            'type' => 'warning',
            'link' => route('admin.enrollments'),
            'is_read' => false,
        ]);

        $response = $this
            ->actingAs($admin)
            ->withSession(['user_id' => $admin->id])
            ->withoutMiddleware()
            ->get('/admin/notifications');

        $response->assertOk();
        $response->assertSee('Hộp thông báo');
        $response->assertSee('Đăng ký lớp cần duyệt');

        $openResponse = $this
            ->actingAs($admin)
            ->withSession(['user_id' => $admin->id])
            ->withoutMiddleware()
            ->get('/admin/notifications/' . $notification->id);

        $openResponse->assertOk();
        $openResponse->assertSee('Chi tiết thông báo');
        $openResponse->assertSee('Đăng ký lớp cần duyệt');
        $openResponse->assertSee(route('admin.enrollments', [], false), false);

        $notification->refresh();

        $this->assertTrue($notification->is_read);
        $this->assertNotNull($notification->read_at);
    }

    public function test_student_cannot_open_another_users_notification(): void
    {
        $owner = NguoiDung::factory()->student()->create();
        $otherStudent = NguoiDung::factory()->student()->create();

        $notification = ThongBao::create([
            'user_id' => $owner->id,
            'title' => 'Thông báo riêng',
            'message' => 'Nội dung chỉ dành cho học viên này.',
            'type' => 'info',
            'link' => route('student.schedule'),
        ]);

        $response = $this
            ->actingAs($otherStudent)
            ->withSession(['user_id' => $otherStudent->id])
            ->withoutMiddleware()
            ->get('/student/notifications/' . $notification->id);

        $response->assertNotFound();
    }

    public function test_student_notification_poll_endpoint_returns_live_summary(): void
    {
        $student = NguoiDung::factory()->student()->create();

        ThongBao::create([
            'user_id' => $student->id,
            'title' => 'Thông báo 1',
            'message' => 'Thông báo chưa đọc.',
            'type' => 'info',
            'link' => route('student.schedule'),
            'is_read' => false,
        ]);

        ThongBao::create([
            'user_id' => $student->id,
            'title' => 'Thông báo 2',
            'message' => 'Thông báo đã đọc.',
            'type' => 'success',
            'link' => route('student.grades'),
            'is_read' => true,
            'read_at' => now(),
        ]);

        $response = $this
            ->actingAs($student)
            ->withSession(['user_id' => $student->id])
            ->withoutMiddleware()
            ->getJson('/student/notifications/poll');

        $response->assertOk();
        $response->assertJsonPath('unread_count', 1);
        $response->assertJsonPath('total_count', 2);
        $response->assertJsonCount(2, 'notifications');
        $response->assertJsonPath('notifications.0.title', 'Thông báo 2');
    }
}
