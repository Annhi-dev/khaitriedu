<?php

namespace App\Services;

use App\Models\KhoaHoc;
use App\Models\PhongBan;
use App\Models\GhiDanh;
use App\Models\YeuCauDoiLich;
use App\Models\DonUngTuyenGiaoVien;
use App\Models\VaiTro;
use App\Models\NguoiDung;
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

        return NguoiDung::query()
            ->teachers()
            ->with(['department'])
            ->withCount(['scheduleChangeRequests'])
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

    public function summary(): array
    {
        return [
            'total' => NguoiDung::query()->teachers()->count(),
            'active' => NguoiDung::query()->teachers()->where('status', NguoiDung::STATUS_ACTIVE)->count(),
            'locked' => NguoiDung::query()->teachers()->where('status', NguoiDung::STATUS_LOCKED)->count(),
            'assigned' => NguoiDung::query()->teachers()->whereNotNull('department_id')->count(),
        ];
    }

    public function createTeacher(array $data): NguoiDung
    {
        return NguoiDung::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
            'password' => Hash::make($data['password']),
            'role_id' => VaiTro::idByName(NguoiDung::ROLE_TEACHER),
            'department_id' => $data['department_id'],
            'status' => $data['status'],
            'email_verified_at' => now(),
        ]);
    }

    public function updateTeacher(NguoiDung $teacher, array $data): NguoiDung
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

        $teacher->role_id = VaiTro::idByName(NguoiDung::ROLE_TEACHER);
        $teacher->save();

        return $teacher;
    }

    public function lockTeacher(NguoiDung $teacher): void
    {
        $teacher->update(['status' => NguoiDung::STATUS_LOCKED]);
    }

    public function unlockTeacher(NguoiDung $teacher): void
    {
        $teacher->update(['status' => NguoiDung::STATUS_ACTIVE]);
    }

    public function getTeacherDetail(NguoiDung $teacher): array
    {
        $teacher->loadMissing('department')->loadCount(['scheduleChangeRequests']);

        $courses = KhoaHoc::with(['subject.category'])
            ->withCount('enrollments')
            ->where('teacher_id', $teacher->id)
            ->latest('id')
            ->get();

        $enrollments = GhiDanh::with(['user', 'course.subject'])
            ->whereHas('course', fn (Builder $query) => $query->where('teacher_id', $teacher->id))
            ->latest('id')
            ->get();

        $studentCount = $enrollments->pluck('user_id')->filter()->unique()->count();

        $scheduleChangeRequests = collect();
        if (Schema::hasTable((new YeuCauDoiLich())->getTable())) {
            $scheduleChangeRequests = YeuCauDoiLich::with(['course.subject', 'reviewer'])
                ->where('teacher_id', $teacher->id)
                ->latest('id')
                ->take(5)
                ->get();
        }

        $application = null;
        if (Schema::hasTable((new DonUngTuyenGiaoVien())->getTable())) {
            $application = DonUngTuyenGiaoVien::where('email', $teacher->email)
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
        return PhongBan::query()
            ->orderBy('name')
            ->get();
    }
}
