<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_department_list(): void
    {
        $admin = User::factory()->admin()->create();
        $departmentA = Department::create([
            'code' => 'PB-DT',
            'name' => 'Phong Dao tao',
            'description' => 'Phu trach dieu phoi lich hoc',
            'status' => Department::STATUS_ACTIVE,
        ]);
        $departmentB = Department::create([
            'code' => 'PB-KTCL',
            'name' => 'Phong Khao thi',
            'status' => Department::STATUS_INACTIVE,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.departments.index'));

        $response->assertOk();
        $response->assertSee('Quản lý phòng ban');
        $response->assertSee($departmentA->name);
        $response->assertSee($departmentB->name);
    }

    public function test_admin_can_filter_departments(): void
    {
        $admin = User::factory()->admin()->create();
        $target = Department::create([
            'code' => 'PB-CNTT',
            'name' => 'Phong Cong nghe giao duc',
            'status' => Department::STATUS_ACTIVE,
        ]);
        $other = Department::create([
            'code' => 'PB-HCNS',
            'name' => 'Phong Hanh chinh nhan su',
            'status' => Department::STATUS_INACTIVE,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->get(route('admin.departments.index', [
                'search' => 'Cong nghe',
                'status' => Department::STATUS_ACTIVE,
            ]));

        $response->assertOk();
        $response->assertSee($target->name);
        $response->assertDontSee($other->name);
    }

    public function test_admin_can_create_department(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.departments.store'), [
                'code' => 'pb.gv',
                'name' => 'Phong Quan ly giang vien',
                'description' => 'Bo phan theo doi va phan cong giang vien',
                'status' => Department::STATUS_ACTIVE,
            ]);

        $department = Department::where('name', 'Phong Quan ly giang vien')->first();

        $response->assertRedirect(route('admin.departments.edit', $department));
        $this->assertDatabaseHas('phong_ban', [
            'id' => $department->id,
            'code' => 'PB.GV',
            'status' => Department::STATUS_ACTIVE,
        ]);
    }

    public function test_admin_can_update_department(): void
    {
        $admin = User::factory()->admin()->create();
        $department = Department::create([
            'code' => 'PB-CU',
            'name' => 'Phong cu',
            'status' => Department::STATUS_ACTIVE,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.departments.update', $department), [
                'code' => 'pb-moi',
                'name' => 'Phong moi',
                'description' => 'Cap nhat mo ta phong ban',
                'status' => Department::STATUS_INACTIVE,
            ]);

        $response->assertRedirect(route('admin.departments.edit', $department));
        $this->assertDatabaseHas('phong_ban', [
            'id' => $department->id,
            'code' => 'PB-MOI',
            'name' => 'Phong moi',
            'status' => Department::STATUS_INACTIVE,
        ]);
    }

    public function test_admin_can_deactivate_and_activate_department(): void
    {
        $admin = User::factory()->admin()->create();
        $department = Department::create([
            'code' => 'PB-KH',
            'name' => 'Phong ke hoach',
            'status' => Department::STATUS_ACTIVE,
        ]);

        $deactivateResponse = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.departments.deactivate', $department));

        $deactivateResponse->assertRedirect(route('admin.departments.index'));
        $this->assertDatabaseHas('phong_ban', [
            'id' => $department->id,
            'status' => Department::STATUS_INACTIVE,
        ]);

        $activateResponse = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.departments.activate', $department));

        $activateResponse->assertRedirect(route('admin.departments.index'));
        $this->assertDatabaseHas('phong_ban', [
            'id' => $department->id,
            'status' => Department::STATUS_ACTIVE,
        ]);
    }

    public function test_admin_can_assign_teacher_to_department_from_department_management(): void
    {
        $admin = User::factory()->admin()->create();
        $department = Department::create([
            'code' => 'PB-GV',
            'name' => 'Phong quan ly giao vien',
            'status' => Department::STATUS_ACTIVE,
        ]);
        $teacher = User::factory()->teacher()->create([
            'department_id' => null,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.departments.teachers.assign', $department), [
                'teacher_id' => $teacher->id,
            ]);

        $response->assertRedirect(route('admin.departments.edit', $department));
        $this->assertDatabaseHas('nguoi_dung', [
            'id' => $teacher->id,
            'department_id' => $department->id,
        ]);
    }

    public function test_admin_can_reassign_teacher_from_other_department(): void
    {
        $admin = User::factory()->admin()->create();
        $oldDepartment = Department::create([
            'code' => 'PB-OLD',
            'name' => 'Phong cu',
            'status' => Department::STATUS_ACTIVE,
        ]);
        $newDepartment = Department::create([
            'code' => 'PB-NEW',
            'name' => 'Phong moi',
            'status' => Department::STATUS_ACTIVE,
        ]);
        $teacher = User::factory()->teacher()->create([
            'department_id' => $oldDepartment->id,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.departments.teachers.assign', $newDepartment), [
                'teacher_id' => $teacher->id,
            ]);

        $response->assertRedirect(route('admin.departments.edit', $newDepartment));
        $this->assertDatabaseHas('nguoi_dung', [
            'id' => $teacher->id,
            'department_id' => $newDepartment->id,
        ]);
    }

    public function test_department_assignment_rejects_non_teacher_account(): void
    {
        $admin = User::factory()->admin()->create();
        $department = Department::create([
            'code' => 'PB-ERR',
            'name' => 'Phong test loi',
            'status' => Department::STATUS_ACTIVE,
        ]);
        $student = User::factory()->student()->create([
            'department_id' => null,
        ]);

        $response = $this
            ->from(route('admin.departments.edit', $department))
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.departments.teachers.assign', $department), [
                'teacher_id' => $student->id,
            ]);

        $response->assertRedirect(route('admin.departments.edit', $department));
        $response->assertSessionHasErrors('teacher_id');
        $this->assertDatabaseHas('nguoi_dung', [
            'id' => $student->id,
            'department_id' => null,
        ]);
    }

    public function test_student_is_blocked_from_department_management(): void
    {
        $student = User::factory()->student()->create();

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('admin.departments.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_teacher_is_blocked_from_department_management(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this
            ->withSession(['user_id' => $teacher->id])
            ->get(route('admin.departments.index'));

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }
}
