<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubjectRequest;
use App\Http\Requests\Admin\UpdateSubjectRequest;
use App\Models\Subject;
use App\Models\User;
use App\Services\AdminSubjectService;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request, AdminSubjectService $subjectService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search', 'status', 'category_id']);
        $subjects = $subjectService->paginateSubjects($filters);
        $categories = $subjectService->getCategories();

        return view('quan_tri.mon_hoc.index', compact('current', 'filters', 'subjects', 'categories'));
    }

    public function create(Request $request, AdminSubjectService $subjectService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $categories = $subjectService->getCategories();
        $selectedCategory = null;
        $returnToCategoryId = null;

        if ($request->filled('category_id')) {
            $selectedCategory = $categories->firstWhere('id', (int) $request->query('category_id'));

            if ($selectedCategory) {
                $selectedCategory->loadCount(['subjects', 'courses']);
            }

            $returnToCategoryId = $selectedCategory?->id;
        }

        return view('quan_tri.mon_hoc.create', compact('current', 'categories', 'selectedCategory', 'returnToCategoryId'));
    }

    public function store(StoreSubjectRequest $request, AdminSubjectService $subjectService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $validated = $request->validated();
        $subject = $subjectService->createSubject($validated, $request->file('image'));
        $returnToCategoryId = (int) ($validated['return_to_category_id'] ?? 0);

        if ($returnToCategoryId > 0 && (int) $subject->category_id === $returnToCategoryId) {
            return redirect()->route('admin.categories.show', $returnToCategoryId)->with('status', 'Đã tạo khóa học mới trong nhóm học.');
        }

        return redirect()->route('admin.subject.show', $subject)->with('status', 'Khóa học đã được tạo thành công.');
    }

    public function show(Subject $subject, AdminSubjectService $subjectService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('quan_tri.mon_hoc.show', array_merge(
            ['current' => $current],
            $subjectService->getSubjectDetail($subject),
        ));
    }

    public function edit(Subject $subject, AdminSubjectService $subjectService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $categories = $subjectService->getCategories();

        return view('quan_tri.mon_hoc.edit', compact('current', 'subject', 'categories'));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject, AdminSubjectService $subjectService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $subjectService->updateSubject($subject, $request->validated(), $request->file('image'));

        return redirect()->route('admin.subject.show', $subject)->with('status', 'Khóa học đã được cập nhật.');
    }

    public function archive(Subject $subject, AdminSubjectService $subjectService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $message = $subjectService->archiveSubject($subject);

        return redirect()->route('admin.subject.show', $subject)->with('status', $message);
    }

    public function reopen(Subject $subject, AdminSubjectService $subjectService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $subjectService->reopenSubject($subject);

        return redirect()->route('admin.subject.show', $subject)->with('status', 'Khóa học đã được mở lại để nhận đăng ký.');
    }
}