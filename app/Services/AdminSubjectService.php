<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Subject;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class AdminSubjectService
{
    public function paginateSubjects(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $categoryId = $filters['category_id'] ?? null;

        return Subject::query()
            ->with('category')
            ->withCount(['courses', 'enrollments', 'modules'])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($categoryId, fn (Builder $query) => $query->where('category_id', $categoryId))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();
    }

    public function getCategories(): Collection
    {
        return Category::query()->orderBy('order')->orderBy('name')->get();
    }

    public function createSubject(array $data, ?UploadedFile $image = null): Subject
    {
        return Subject::create($this->buildPayload($data, $image));
    }

    public function updateSubject(Subject $subject, array $data, ?UploadedFile $image = null): Subject
    {
        $subject->update($this->buildPayload($data, $image, $subject));

        return $subject;
    }

    public function getSubjectDetail(Subject $subject): array
    {
        $subject->load('category');
        $subject->loadCount(['courses', 'enrollments', 'modules']);

        $courses = $subject->courses()
            ->with('teacher')
            ->withCount(['modules', 'enrollments'])
            ->orderByDesc('id')
            ->get();

        return [
            'subject' => $subject,
            'courses' => $courses,
        ];
    }

    public function archiveSubject(Subject $subject): string
    {
        $hasDependencies = $subject->courses()->exists() || $subject->enrollments()->exists();
        $subject->update(['status' => Subject::STATUS_ARCHIVED]);

        return $hasDependencies
            ? 'Khóa học đang có lớp học hoặc đăng ký liên kết nên đã được chuyển sang trạng thái lưu trữ.'
            : 'Khóa học đã được chuyển sang trạng thái lưu trữ.';
    }

    public function reopenSubject(Subject $subject): void
    {
        $subject->update(['status' => Subject::STATUS_OPEN]);
    }

    protected function buildPayload(array $data, ?UploadedFile $image = null, ?Subject $subject = null): array
    {
        $payload = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? 0,
            'duration' => $data['duration'] ?? null,
            'status' => $data['status'],
            'category_id' => $data['category_id'] ?? null,
        ];

        if ($image) {
            $payload['image'] = $image->store('subjects', 'public');
        } elseif ($subject) {
            $payload['image'] = $subject->image;
        }

        return $payload;
    }
}