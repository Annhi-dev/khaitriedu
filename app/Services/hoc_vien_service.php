<?php

namespace App\Services;

use App\Models\GhiDanh;
use App\Models\DiemSo;
use App\Models\DanhGia;
use App\Models\VaiTro;
use App\Models\NguoiDung;
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

        return NguoiDung::query()
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

    public function summary(): array
    {
        return [
            'total' => NguoiDung::query()->students()->count(),
            'active' => NguoiDung::query()->students()->where('status', NguoiDung::STATUS_ACTIVE)->count(),
            'locked' => NguoiDung::query()->students()->where('status', NguoiDung::STATUS_LOCKED)->count(),
            'enrolled' => NguoiDung::query()->students()->has('enrollments')->count(),
        ];
    }

    public function createStudent(array $data): NguoiDung
    {
        return NguoiDung::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
            'password' => Hash::make($data['password']),
            'role_id' => VaiTro::idByName(NguoiDung::ROLE_STUDENT),
            'status' => $data['status'],
            'email_verified_at' => now(),
        ]);
    }

    public function updateStudent(NguoiDung $student, array $data): NguoiDung
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

        $student->role_id = VaiTro::idByName(NguoiDung::ROLE_STUDENT);
        $student->save();

        return $student;
    }

    public function lockStudent(NguoiDung $student): void
    {
        $student->update(['status' => NguoiDung::STATUS_LOCKED]);
    }

    public function unlockStudent(NguoiDung $student): void
    {
        $student->update(['status' => NguoiDung::STATUS_ACTIVE]);
    }

    public function getStudentDetail(NguoiDung $student): array
    {
        $student->loadCount('enrollments');

        $enrollments = GhiDanh::with(['subject.category', 'course.subject.category', 'assignedTeacher'])
            ->where('user_id', $student->id)
            ->latest('id')
            ->get();

        GhiDanh::syncDisplayStatusesByClass($enrollments);

        $currentSchedules = $enrollments
            ->filter(fn (GhiDanh $enrollment) => in_array($enrollment->displayStatus(), [
                GhiDanh::STATUS_ENROLLED,
                GhiDanh::STATUS_SCHEDULED,
                GhiDanh::STATUS_ACTIVE,
            ], true) && $enrollment->course_id)
            ->values();

        $grades = collect();
        if (Schema::hasTable((new DiemSo())->getTable())) {
            $grades = DiemSo::with(['enrollment.course.subject', 'module'])
                ->whereHas('enrollment', fn (Builder $query) => $query->where('user_id', $student->id))
                ->latest('updated_at')
                ->take(5)
                ->get();
        }

        $reviews = collect();
        if (Schema::hasTable((new DanhGia())->getTable())) {
            $reviews = DanhGia::with(['course.subject'])
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
