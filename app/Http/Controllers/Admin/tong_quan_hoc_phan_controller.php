<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\GhiDanh;
use App\Models\HocPhan;
use App\Models\NguoiDung;
use Illuminate\Http\Request;

class ModuleOverviewController extends Controller
{
    public function index(Request $request)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search']);

        $courses = KhoaHoc::query()
            ->with(['subject.category', 'teacher'])
            ->withCount([
                'modules',
                'modules as published_modules_count' => fn ($query) => $query->where('status', HocPhan::STATUS_PUBLISHED),
                'enrollments as active_students_count' => fn ($query) => $query->whereIn('status', [
                    GhiDanh::STATUS_APPROVED,
                    GhiDanh::STATUS_SCHEDULED,
                    GhiDanh::STATUS_ACTIVE,
                    GhiDanh::STATUS_COMPLETED,
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
            'course_count' => KhoaHoc::count(),
            'module_count' => HocPhan::count(),
            'published_module_count' => HocPhan::where('status', HocPhan::STATUS_PUBLISHED)->count(),
        ];

        return view('quan_tri.hoc_phan.index', compact('current', 'filters', 'courses', 'summary'));
    }
}
