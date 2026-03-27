<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;

class AdminStudyGroupService
{
    public function paginateStudyGroups(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));

        return Category::query()
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

    public function createStudyGroup(array $data, ?UploadedFile $image = null): Category
    {
        return Category::create($this->buildPayload($data, $image));
    }

    public function updateStudyGroup(Category $category, array $data, ?UploadedFile $image = null): Category
    {
        $category->update($this->buildPayload($data, $image, $category));

        return $category;
    }

    public function getStudyGroupDetail(Category $category): array
    {
        $category->loadCount(['subjects', 'courses']);
        $subjects = $category->subjects()
            ->withCount('courses')
            ->orderBy('name')
            ->get();

        return [
            'category' => $category,
            'subjects' => $subjects,
        ];
    }

    public function deactivateStudyGroup(Category $category): string
    {
        $hasDependencies = $category->subjects()->exists() || $category->courses()->exists();
        $category->update(['status' => Category::STATUS_INACTIVE]);

        return $hasDependencies
            ? 'Nhóm học đang có khóa học liên kết nên đã được chuyển sang trạng thái ngừng hoạt động.'
            : 'Nhóm học đã được ngừng hoạt động.';
    }

    public function activateStudyGroup(Category $category): void
    {
        $category->update(['status' => Category::STATUS_ACTIVE]);
    }

    protected function buildPayload(array $data, ?UploadedFile $image = null, ?Category $category = null): array
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