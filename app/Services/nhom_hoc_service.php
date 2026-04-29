<?php

namespace App\Services;

use App\Models\NhomHoc;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;

class AdminStudyGroupService
{
    public function paginateStudyGroups(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));

        return NhomHoc::query()
            ->with('defaultSubject')
            ->withCount(['subjects', 'courses'])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder->where('name', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%')
                        ->orWhere('program', 'like', '%' . $search . '%')
                        ->orWhere('level', 'like', '%' . $search . '%');
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();
    }

    public function createStudyGroup(array $data, ?UploadedFile $image = null): NhomHoc
    {
        return NhomHoc::create($this->buildPayload($data, $image));
    }

    public function updateStudyGroup(NhomHoc $category, array $data, ?UploadedFile $image = null): NhomHoc
    {
        $category->update($this->buildPayload($data, $image, $category));

        return $category;
    }

    public function getStudyGroupDetail(NhomHoc $category): array
    {
        $category->load(['defaultSubject'])->loadCount(['subjects', 'courses']);
        $subjects = $category->subjects()
            ->withCount('courses')
            ->orderBy('name')
            ->get();
        $courses = $category->courses()
            ->with(['teacher', 'subject'])
            ->withCount('enrollments')
            ->orderBy('title')
            ->get();

        return [
            'category' => $category,
            'subjects' => $subjects,
            'courses' => $courses,
        ];
    }

    public function deactivateStudyGroup(NhomHoc $category): string
    {
        $hasDependencies = $category->subjects()->exists() || $category->courses()->exists();
        $category->update(['status' => NhomHoc::STATUS_INACTIVE]);

        return $hasDependencies
            ? 'Nhóm học đang có khóa học liên kết nên đã được chuyển sang trạng thái ngừng hoạt động.'
            : 'Nhóm học đã được ngừng hoạt động.';
    }

    public function activateStudyGroup(NhomHoc $category): void
    {
        $category->update(['status' => NhomHoc::STATUS_ACTIVE]);
    }

    protected function buildPayload(array $data, ?UploadedFile $image = null, ?NhomHoc $category = null): array
    {
        $payload = [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'program' => $data['program'] ?? null,
            'level' => $data['level'] ?? null,
            'status' => $data['status'],
            'order' => $data['order'] ?? 0,
        ];

        if ($image) {
            $payload['image_path'] = $image->store('categories', 'public');
        } elseif ($category) {
            $payload['image_path'] = $category->image_path;
        }

        return $payload;
    }
}