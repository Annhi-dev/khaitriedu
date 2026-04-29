<?php

namespace App\Services;

use App\Models\PhongBan;
use App\Models\NguoiDung;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdminDepartmentService
{
    public function paginateDepartments(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));

        return PhongBan::query()
            ->withCount('teachers')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder->where('code', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();
    }

    public function createDepartment(array $data): PhongBan
    {
        return PhongBan::create($this->buildPayload($data));
    }

    public function updateDepartment(PhongBan $department, array $data): PhongBan
    {
        $department->update($this->buildPayload($data));

        return $department;
    }

    public function deactivateDepartment(PhongBan $department): string
    {
        $department->update(['status' => PhongBan::STATUS_INACTIVE]);
        $teacherCount = $department->teachers()->count();

        if ($teacherCount > 0) {
            return 'Phòng ban đã chuyển sang tạm ngưng. Hiện có ' . $teacherCount . ' giảng viên đang thuộc phòng ban này.';
        }

        return 'Phòng ban đã được chuyển sang tạm ngưng.';
    }

    public function activateDepartment(PhongBan $department): void
    {
        $department->update(['status' => PhongBan::STATUS_ACTIVE]);
    }

    public function summary(): array
    {
        return [
            'total' => PhongBan::query()->count(),
            'active' => PhongBan::query()->where('status', PhongBan::STATUS_ACTIVE)->count(),
            'inactive' => PhongBan::query()->where('status', PhongBan::STATUS_INACTIVE)->count(),
            'teachers' => PhongBan::query()->withCount('teachers')->get()->sum('teachers_count'),
        ];
    }

    public function teachersInDepartment(PhongBan $department): Collection
    {
        return NguoiDung::query()
            ->teachers()
            ->where('department_id', $department->id)
            ->orderBy('name')
            ->get();
    }

    public function assignableTeachers(PhongBan $department): Collection
    {
        return NguoiDung::query()
            ->teachers()
            ->with('department')
            ->where(function (Builder $query) use ($department) {
                $query->whereNull('department_id')
                    ->orWhere('department_id', '!=', $department->id);
            })
            ->orderBy('name')
            ->get();
    }

    public function assignTeacher(PhongBan $department, NguoiDung $teacher): string
    {
        $teacher->loadMissing('department');

        if ($teacher->department_id === $department->id) {
            return 'Giảng viên đã thuộc phòng ban này.';
        }

        $previousDepartment = $teacher->department?->name;

        $teacher->department()->associate($department);
        $teacher->save();

        if ($previousDepartment) {
            return 'Đã chuyển giảng viên sang phòng ban mới từ "' . $previousDepartment . '".';
        }

        return 'Đã thêm giảng viên vào phòng ban.';
    }

    protected function buildPayload(array $data): array
    {
        return [
            'code' => Str::upper(trim($data['code'])),
            'name' => trim($data['name']),
            'description' => $data['description'] ?: null,
            'status' => $data['status'],
        ];
    }
}
