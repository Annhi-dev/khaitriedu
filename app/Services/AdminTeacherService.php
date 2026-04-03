<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\ScheduleChangeRequest;
use App\Models\TeacherApplication;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminTeacherService
{
    public function paginateTeachers(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $departmentId = (int) ($filters['department_id'] ?? 0);

        return User::query()
            ->teachers()
            ->with('department')
            ->withCount(['taughtCourses', 'scheduleChangeRequests'])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            })
            ->when($status !== '', function (Builder $query) use ($status) {
                $query->where('status', $status);
            })
            ->when($departmentId > 0, fn (Builder $query) => $query->where('department_id', $departmentId))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function createTeacher(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
            'password' => Hash::make($data['password']),
            'role_id' => Role::idByName(User::ROLE_TEACHER),
            'department_id' => $data['department_id'],
            'status' => $data['status'],
            'email_verified_at' => now(),
        ]);
    }

    public function updateTeacher(User $teacher, array $data): User
    {
        $teacher->fill([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
            'department_id' => $data['department_id'],
            'status' => $data['status'],
        ]);

        if (! empty($data['password'])) {
            $teacher->password = Hash::make($data['password']);
        }

        $teacher->role_id = Role::idByName(User::ROLE_TEACHER);
        $teacher->save();

        return $teacher;
    }

    public function lockTeacher(User $teacher): void
    {
        $teacher->update(['status' => User::STATUS_LOCKED]);
    }

    public function unlockTeacher(User $teacher): void
    {
        $teacher->update(['status' => User::STATUS_ACTIVE]);
    }

    public function getTeacherDetail(User $teacher): array
    {
        $teacher->loadMissing('department')->loadCount(['taughtCourses', 'scheduleChangeRequests']);

        $courses = Course::with(['subject.category'])
            ->withCount('enrollments')
            ->where('teacher_id', $teacher->id)
            ->latest('id')
            ->get();

        $enrollments = Enrollment::with(['user', 'course.subject'])
            ->whereHas('course', fn (Builder $query) => $query->where('teacher_id', $teacher->id))
            ->latest('id')
            ->get();

        $studentCount = $enrollments->pluck('user_id')->filter()->unique()->count();

        $scheduleChangeRequests = collect();
        if (Schema::hasTable((new ScheduleChangeRequest())->getTable())) {
            $scheduleChangeRequests = ScheduleChangeRequest::with(['course.subject', 'reviewer'])
                ->where('teacher_id', $teacher->id)
                ->latest('id')
                ->take(5)
                ->get();
        }

        $application = null;
        if (Schema::hasTable((new TeacherApplication())->getTable())) {
            $application = TeacherApplication::where('email', $teacher->email)
                ->latest('id')
                ->first();
        }

        return [
            'teacher' => $teacher,
            'courses' => $courses,
            'enrollments' => $enrollments,
            'studentCount' => $studentCount,
            'scheduleChangeRequests' => $scheduleChangeRequests,
            'application' => $application,
        ];
    }

    public function departmentOptions()
    {
        return Department::query()
            ->orderBy('name')
            ->get();
    }
}
