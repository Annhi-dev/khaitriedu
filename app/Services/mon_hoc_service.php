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

        $dummyNames = ['Ngoại ngữ - Tin học', 'Bồi dưỡng ngắn hạn', 'Đào tạo nghề', 'Đào tạo dài hạn'];

        return Subject::query()
            ->with('category')
            ->withCount(['courses', 'enrollments', 'modules'])
            ->whereNotIn('name', $dummyNames)
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
        $previous = [
            'name' => (string) $subject->name,
            'description' => $subject->description,
            'price' => (float) ($subject->price ?? 0),
        ];

        $subject->update($this->buildPayload($data, $image, $subject));
        $this->syncRelatedCourses($subject, $previous);

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
            'test_count' => $data['test_count'] ?? Subject::DEFAULT_TEST_COUNT,
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

    protected function syncRelatedCourses(Subject $subject, array $previous): void
    {
        $subject->loadMissing('courses');

        $oldName = (string) ($previous['name'] ?? '');
        $oldDescription = $this->normalizeText($previous['description'] ?? null);
        $oldPrice = (float) ($previous['price'] ?? 0);
        $newName = (string) $subject->name;
        $newDescription = $subject->description;
        $newPrice = (float) ($subject->price ?? 0);

        foreach ($subject->courses as $course) {
            $updates = [];
            $syncedTitle = $this->syncTitleByPattern((string) $course->title, $oldName, $newName);

            if ($syncedTitle !== null && $syncedTitle !== $course->title) {
                $updates['title'] = $syncedTitle;
            }

            if ($this->normalizeText($course->description) === $oldDescription) {
                $updates['description'] = $newDescription;
            }

            if ($this->samePrice((float) ($course->price ?? 0), $oldPrice)) {
                $updates['price'] = $newPrice;
            }

            if ($updates !== []) {
                $course->update($updates);
            }
        }
    }

    protected function syncTitleByPattern(string $title, string $oldName, string $newName): ?string
    {
        $normalizedTitle = $this->normalizeText($title);
        $normalizedOldName = $this->normalizeText($oldName);

        if ($normalizedTitle === '' || $normalizedOldName === '') {
            return null;
        }

        if ($normalizedTitle === $normalizedOldName) {
            return $newName;
        }

        $patterns = [
            '/^(KhaiTriEdu\s+\d{4}\s*-\s*)(.+)$/u',
            '/^(Khoa\s+\d+\s*-\s*)(.+)$/u',
            '/^(Khóa\s+\d+\s*-\s*)(.+)$/u',
            '/^(Khóa nội bộ\s*-\s*)(.+)$/u',
            '/^(Khoa noi bo\s*-\s*)(.+)$/u',
            '/^(Lop\s+\d+\s*-\s*)(.+)$/u',
            '/^(Lớp\s+\d+\s*-\s*)(.+)$/u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $title, $matches) !== 1) {
                continue;
            }

            $titleSubjectPart = $this->normalizeText($matches[2] ?? '');

            if ($titleSubjectPart === $normalizedOldName) {
                return ($matches[1] ?? '') . $newName;
            }
        }

        return null;
    }

    protected function normalizeText(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return mb_strtolower($value, 'UTF-8');
    }

    protected function samePrice(float $left, float $right): bool
    {
        return abs($left - $right) < 0.0001;
    }
}
