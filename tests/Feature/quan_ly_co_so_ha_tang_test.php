<?php

namespace Tests\Feature;

use App\Models\NhomHoc;
use App\Models\KhoaHoc;
use App\Models\KhungGioKhoaHoc;
use App\Models\PhongHoc;
use App\Models\NguyenVongKhungGio;
use App\Models\LuaChonNguyenVongKhungGio;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class quan_ly_co_so_ha_tang_test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_infrastructure_management_pages(): void
    {
        [$admin, $subject, $teacher, $room, $slotRegistration] = $this->seedInfrastructureData();
        $course = KhoaHoc::create([
            'subject_id' => $subject->id,
            'title' => 'Tin hoc van phong toi 2-4-6',
            'teacher_id' => $teacher->id,
            'status' => KhoaHoc::STATUS_ACTIVE,
        ]);

        $responses = [
            $this->withSession(['user_id' => $admin->id])->get(route('admin.modules.index')),
            $this->withSession(['user_id' => $admin->id])->get(route('admin.rooms.index')),
            $this->withSession(['user_id' => $admin->id])->get(route('admin.course-time-slots.index')),
            $this->withSession(['user_id' => $admin->id])->get(route('admin.slot-registrations.index')),
            $this->withSession(['user_id' => $admin->id])->get(route('admin.slot-tracking.index')),
            $this->withSession(['user_id' => $admin->id])->get(route('admin.courses.modules.index', $course)),
            $this->withSession(['user_id' => $admin->id])->get(route('admin.slot-registrations.show', $slotRegistration)),
        ];

        foreach ($responses as $response) {
            $response->assertOk();
        }
    }

    public function test_admin_can_create_and_update_room(): void
    {
        $admin = NguoiDung::factory()->admin()->create();

        $createResponse = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.rooms.store'), [
                'code' => 'p203',
                'name' => 'Phong 203',
                'location' => 'Tang 2',
                'capacity' => 32,
                'status' => PhongHoc::STATUS_ACTIVE,
                'note' => 'Phong hoc may lanh',
            ]);

        $room = PhongHoc::where('code', 'P203')->first();

        $createResponse->assertRedirect(route('admin.rooms.index'));
        $this->assertNotNull($room);
        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'code' => 'P203',
            'capacity' => 32,
        ]);

        $updateResponse = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.rooms.update', $room), [
                'code' => 'P203',
                'name' => 'Phong 203 da sua',
                'location' => 'Tang 3',
                'capacity' => 28,
                'status' => PhongHoc::STATUS_MAINTENANCE,
                'note' => 'Tam khoa de bao tri',
            ]);

        $updateResponse->assertRedirect(route('admin.rooms.index'));
        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'name' => 'Phong 203 da sua',
            'location' => 'Tang 3',
            'capacity' => 28,
            'status' => PhongHoc::STATUS_MAINTENANCE,
        ]);
    }

    public function test_admin_can_create_and_update_course_time_slot(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $teacher = NguoiDung::factory()->teacher()->create();
        $category = NhomHoc::create([
            'name' => 'Ngoai ngu - Tin hoc',
            'slug' => 'ngoai-ngu-tin-hoc',
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);
        $subject = MonHoc::create([
            'name' => 'Tin hoc van phong',
            'price' => 1500000,
            'category_id' => $category->id,
        ]);
        $room = PhongHoc::create([
            'code' => 'P101',
            'name' => 'Phong 101',
            'capacity' => 25,
            'status' => PhongHoc::STATUS_ACTIVE,
        ]);

        $createResponse = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.course-time-slots.store'), [
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'room_id' => $room->id,
                'day_of_week' => 'Monday',
                'start_time' => '18:00',
                'end_time' => '20:15',
                'registration_open_at' => now()->format('Y-m-d H:i:s'),
                'registration_close_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'min_students' => 10,
                'max_students' => 20,
                'status' => KhungGioKhoaHoc::STATUS_OPEN_FOR_REGISTRATION,
                'note' => 'Ca toi thu 2',
            ]);

        $timeSlot = KhungGioKhoaHoc::first();

        $createResponse->assertRedirect(route('admin.course-time-slots.index'));
        $this->assertNotNull($timeSlot);
        $this->assertDatabaseHas('course_time_slots', [
            'id' => $timeSlot->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'status' => KhungGioKhoaHoc::STATUS_OPEN_FOR_REGISTRATION,
        ]);

        $updateResponse = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.course-time-slots.update', $timeSlot), [
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'room_id' => $room->id,
                'slot_date' => now()->addWeek()->format('Y-m-d'),
                'start_time' => '17:30',
                'end_time' => '19:45',
                'registration_open_at' => now()->format('Y-m-d H:i:s'),
                'registration_close_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
                'min_students' => 8,
                'max_students' => 18,
                'status' => KhungGioKhoaHoc::STATUS_READY_TO_OPEN_CLASS,
                'note' => 'Da du nhu cau',
            ]);

        $updateResponse->assertRedirect(route('admin.course-time-slots.index'));
        $this->assertDatabaseHas('course_time_slots', [
            'id' => $timeSlot->id,
            'status' => KhungGioKhoaHoc::STATUS_READY_TO_OPEN_CLASS,
            'start_time' => '17:30',
            'end_time' => '19:45',
            'min_students' => 8,
            'max_students' => 18,
        ]);
    }

    public function test_admin_time_slot_validation_messages_are_in_vietnamese(): void
    {
        $admin = NguoiDung::factory()->admin()->create();
        $teacher = NguoiDung::factory()->teacher()->create();
        $category = NhomHoc::create([
            'name' => 'Ngoai ngu - Tin hoc',
            'slug' => 'ngoai-ngu-tin-hoc-viet-hoa',
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);
        $subject = MonHoc::create([
            'name' => 'Tieng Anh giao tiep',
            'price' => 1800000,
            'category_id' => $category->id,
        ]);
        $room = PhongHoc::create([
            'code' => 'P102',
            'name' => 'Phong 102',
            'capacity' => 25,
            'status' => PhongHoc::STATUS_ACTIVE,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.course-time-slots.store'), [
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'room_id' => $room->id,
                'day_of_week' => 'Monday',
                'start_time' => '05:49 AM',
                'end_time' => '10:49 AM',
                'registration_open_at' => now()->format('Y-m-d H:i:s'),
                'registration_close_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'min_students' => 10,
                'max_students' => 20,
                'status' => KhungGioKhoaHoc::STATUS_OPEN_FOR_REGISTRATION,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'start_time' => 'Giờ bắt đầu phải có định dạng H:i.',
            'end_time' => 'Giờ kết thúc phải có định dạng H:i.',
        ]);
    }

    public function test_teacher_is_blocked_from_room_management(): void
    {
        $teacher = NguoiDung::factory()->teacher()->create();

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->get(route('admin.rooms.index'));

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }

    private function seedInfrastructureData(): array
    {
        $admin = NguoiDung::factory()->admin()->create();
        $teacher = NguoiDung::factory()->teacher()->create();
        $student = NguoiDung::factory()->student()->create();
        $category = NhomHoc::create([
            'name' => 'Ngoai ngu - Tin hoc',
            'slug' => 'ngoai-ngu-tin-hoc',
            'status' => NhomHoc::STATUS_ACTIVE,
        ]);
        $subject = MonHoc::create([
            'name' => 'Tin hoc van phong',
            'price' => 1500000,
            'category_id' => $category->id,
        ]);
        $room = PhongHoc::create([
            'code' => 'P101',
            'name' => 'Phong 101',
            'capacity' => 30,
            'status' => PhongHoc::STATUS_ACTIVE,
        ]);
        $timeSlot = KhungGioKhoaHoc::create([
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'day_of_week' => 'Monday',
            'start_time' => '18:00',
            'end_time' => '20:15',
            'min_students' => 10,
            'max_students' => 20,
            'status' => KhungGioKhoaHoc::STATUS_OPEN_FOR_REGISTRATION,
        ]);
        $slotRegistration = NguyenVongKhungGio::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => NguyenVongKhungGio::STATUS_PENDING,
        ]);

        LuaChonNguyenVongKhungGio::create([
            'slot_registration_id' => $slotRegistration->id,
            'course_time_slot_id' => $timeSlot->id,
            'priority' => 1,
        ]);

        return [$admin, $subject, $teacher, $room, $slotRegistration];
    }
}

