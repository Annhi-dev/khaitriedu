<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class quan_ly_khoa_hoc_test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_course_with_existing_subject_id(): void
    {
        $admin = User::factory()->admin()->create();
        $category = $this->createCategory('Tin hoc', 'tin-hoc');
        $subject = $this->createSubject($category, [
            'name' => 'Tin hoc van phong',
            'description' => 'Khung co san',
            'price' => 1500000,
            'duration' => 24,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.create'), [
                'category_id' => $category->id,
                'subject_id' => $subject->id,
                'title' => 'Khóa 27 - Tin hoc van phong',
                'description' => 'Khoa hoc noi bo',
                'price' => 4200000,
                'return_to_category_id' => $category->id,
            ]);

        $response->assertRedirect(route('admin.categories.show', $category));
        $this->assertDatabaseCount('mon_hoc', 1);
        $this->assertDatabaseHas('khoa_hoc', [
            'subject_id' => $subject->id,
            'title' => 'Khóa 27 - Tin hoc van phong',
            'price' => 4200000,
        ]);
    }

    public function test_admin_can_create_course_by_typing_new_subject_name(): void
    {
        $admin = User::factory()->admin()->create();
        $category = $this->createCategory('Ngoai ngu', 'ngoai-ngu');

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.create'), [
                'category_id' => $category->id,
                'subject_name' => 'Lap trinh Python co ban',
                'subject_duration' => 18,
                'title' => 'Khóa 27 - Lap trinh Python co ban',
                'description' => 'Chuong trinh moi',
                'price' => 5500000,
                'return_to_category_id' => $category->id,
            ]);

        $response->assertRedirect(route('admin.categories.show', $category));

        $subject = Subject::query()->where('name', 'Lap trinh Python co ban')->first();

        $this->assertNotNull($subject);
        $this->assertDatabaseHas('mon_hoc', [
            'id' => $subject->id,
            'category_id' => $category->id,
            'price' => 5500000,
            'duration' => 18,
            'status' => Subject::STATUS_OPEN,
        ]);
        $this->assertDatabaseHas('khoa_hoc', [
            'subject_id' => $subject->id,
            'title' => 'Khóa 27 - Lap trinh Python co ban',
        ]);
    }

    public function test_admin_reuses_existing_subject_when_typing_matching_name(): void
    {
        $admin = User::factory()->admin()->create();
        $category = $this->createCategory('Ky nang', 'ky-nang');
        $subject = $this->createSubject($category, [
            'name' => 'Tin hoc van phong',
            'description' => 'Khung da ton tai',
            'price' => 1600000,
            'duration' => 24,
        ]);

        $response = $this
            ->withSession(['user_id' => $admin->id])
            ->post(route('admin.courses.create'), [
                'category_id' => $category->id,
                'subject_name' => 'Tin hoc van phong',
                'subject_duration' => 24,
                'title' => 'Khóa 28 - Tin hoc van phong',
                'description' => 'Mo ta moi',
                'price' => 6000000,
                'return_to_category_id' => $category->id,
            ]);

        $response->assertRedirect(route('admin.categories.show', $category));
        $this->assertDatabaseCount('mon_hoc', 1);
        $this->assertDatabaseHas('khoa_hoc', [
            'subject_id' => $subject->id,
            'title' => 'Khóa 28 - Tin hoc van phong',
            'price' => 6000000,
        ]);
    }

    private function createCategory(string $name, string $slug): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => $slug,
            'status' => Category::STATUS_ACTIVE,
        ]);
    }

    private function createSubject(Category $category, array $overrides = []): Subject
    {
        return Subject::create(array_merge([
            'name' => 'Mon hoc mau',
            'category_id' => $category->id,
            'status' => Subject::STATUS_OPEN,
            'price' => 1500000,
            'duration' => 12,
            'description' => 'Mo ta mau',
        ], $overrides));
    }
}

