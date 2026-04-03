<?php

namespace App\Services;

use App\Models\Department;
use App\Models\User;
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

        return Department::query()
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

    public function createDepartment(array $data): Department
    {
        return Department::create($this->buildPayload($data));
    }

    public function updateDepartment(Department $department, array $data): Department
    {
        $department->update($this->buildPayload($data));

        return $department;
    }

    public function deactivateDepartment(Department $department): string
    {
        $department->update(['status' => Department::STATUS_INACTIVE]);
        $teacherCount = $department->teachers()->count();

        if ($teacherCount > 0) {
            return 'Phòng ban đã chuyển sang tạm ngưng. Hiện có ' . $teacherCount . ' giảng viên đang thuộc phòng ban này.';
        }

        return 'Phòng ban đã được chuyển sang tạm ngưng.';
    }

    public function activateDepartment(Department $department): void
    {
        $department->update(['status' => Department::STATUS_ACTIVE]);
    }

    public function summary(): array
    {
        return [
            'total' => Department::query()->count(),
            'active' => Department::query()->where('status', Department::STATUS_ACTIVE)->count(),
            'inactive' => Department::query()->where('status', Department::STATUS_INACTIVE)->count(),
            'teachers' => Department::query()->withCount('teachers')->get()->sum('teachers_count'),
        ];
    }

    public function teachersInDepartment(Department $department): Collection
    {
        return User::query()
            ->teachers()
            ->where('department_id', $department->id)
            ->orderBy('name')
            ->get();
    }

    public function assignableTeachers(Department $department): Collection
    {
        return User::query()
            ->teachers()
            ->with('department')
            ->where(function (Builder $query) use ($department) {
                $query->whereNull('department_id')
                    ->orWhere('department_id', '!=', $department->id);
            })
            ->orderBy('name')
            ->get();
    }

    public function assignTeacher(Department $department, User $teacher): string
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
