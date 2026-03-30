<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminStudentService
{
    public function paginateStudents(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));

        return User::query()
            ->students()
            ->withCount('enrollments')
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

    public function createStudent(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
            'password' => Hash::make($data['password']),
            'role_id' => Role::idByName(User::ROLE_STUDENT),
            'status' => $data['status'],
            'email_verified_at' => now(),
        ]);
    }

    public function updateStudent(User $student, array $data): User
    {
        $student->fill([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
            'status' => $data['status'],
        ]);

        if (! empty($data['password'])) {
            $student->password = Hash::make($data['password']);
        }

        $student->role_id = Role::idByName(User::ROLE_STUDENT);
        $student->save();

        return $student;
    }

    public function lockStudent(User $student): void
    {
        $student->update(['status' => User::STATUS_LOCKED]);
    }

    public function unlockStudent(User $student): void
    {
        $student->update(['status' => User::STATUS_ACTIVE]);
    }

    public function getStudentDetail(User $student): array
    {
        $student->loadCount('enrollments');

        $enrollments = Enrollment::with(['subject.category', 'course.subject.category', 'assignedTeacher'])
            ->where('user_id', $student->id)
            ->latest('id')
            ->get();

        $currentSchedules = $enrollments
            ->filter(fn (Enrollment $enrollment) => in_array($enrollment->status, Enrollment::courseAccessStatuses(), true) && $enrollment->course_id)
            ->values();

        $grades = collect();
        if (Schema::hasTable((new Grade())->getTable())) {
            $grades = Grade::with(['enrollment.course.subject', 'module'])
                ->whereHas('enrollment', fn (Builder $query) => $query->where('user_id', $student->id))
                ->latest('updated_at')
                ->take(5)
                ->get();
        }

        $reviews = collect();
        if (Schema::hasTable((new Review())->getTable())) {
            $reviews = Review::with(['course.subject'])
                ->where('user_id', $student->id)
                ->latest('id')
                ->take(5)
                ->get();
        }

        return [
            'student' => $student,
            'enrollments' => $enrollments,
            'currentSchedules' => $currentSchedules,
            'grades' => $grades,
            'reviews' => $reviews,
        ];
    }
}