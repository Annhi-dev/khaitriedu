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

        return view('admin.subject.index', compact('current', 'filters', 'subjects', 'categories'));
    }

    public function create(AdminSubjectService $subjectService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $categories = $subjectService->getCategories();

        return view('admin.subject.create', compact('current', 'categories'));
    }

    public function store(StoreSubjectRequest $request, AdminSubjectService $subjectService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $subject = $subjectService->createSubject($request->validated(), $request->file('image'));

        return redirect()->route('admin.subject.show', $subject)->with('status', 'Khóa học đã được tạo thành công.');
    }

    public function show(Subject $subject, AdminSubjectService $subjectService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('admin.subject.show', array_merge(
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

        return view('admin.subject.edit', compact('current', 'subject', 'categories'));
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
