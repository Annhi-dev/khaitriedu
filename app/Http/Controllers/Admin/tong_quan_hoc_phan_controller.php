<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;

class ModuleOverviewController extends Controller
{
    public function index(Request $request)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search']);

        $courses = Course::query()
            ->with(['subject.category', 'teacher'])
            ->withCount([
                'modules',
                'modules as published_modules_count' => fn ($query) => $query->where('status', Module::STATUS_PUBLISHED),
                'enrollments as active_students_count' => fn ($query) => $query->whereIn('status', [
                    Enrollment::STATUS_APPROVED,
                    Enrollment::STATUS_SCHEDULED,
                    Enrollment::STATUS_ACTIVE,
                    Enrollment::STATUS_COMPLETED,
                ]),
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->query('search'));

                $query->where(function ($courseQuery) use ($search) {
                    $courseQuery->where('title', 'like', '%' . $search . '%')
                        ->orWhereHas('subject', function ($subjectQuery) use ($search) {
                            $subjectQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', '%' . $search . '%'));
                        });
                });
            })
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $summary = [
            'course_count' => Course::count(),
            'module_count' => Module::count(),
            'published_module_count' => Module::where('status', Module::STATUS_PUBLISHED)->count(),
        ];

        return view('quan_tri.hoc_phan.index', compact('current', 'filters', 'courses', 'summary'));
    }
}
