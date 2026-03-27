<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ScheduleChangeRequest;
use App\Models\TeacherApplication;
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

        return User::query()
            ->teachers()
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
            'role' => User::ROLE_TEACHER,
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
            'status' => $data['status'],
        ]);

        if (! empty($data['password'])) {
            $teacher->password = Hash::make($data['password']);
        }

        $teacher->role = User::ROLE_TEACHER;
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
        $teacher->loadCount(['taughtCourses', 'scheduleChangeRequests']);

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
}