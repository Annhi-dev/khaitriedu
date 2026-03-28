<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudyGroupRequest;
use App\Http\Requests\Admin\UpdateStudyGroupRequest;
use App\Models\Category;
use App\Models\User;
use App\Services\AdminStudyGroupService;
use Illuminate\Http\Request;

class StudyGroupController extends Controller
{
    public function index(Request $request, AdminStudyGroupService $studyGroupService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search', 'status']);
        $categories = $studyGroupService->paginateStudyGroups($filters);

        return view('admin.study_groups.index', compact('current', 'filters', 'categories'));
    }

    public function create()
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('admin.study_groups.create', compact('current'));
    }

    public function store(StoreStudyGroupRequest $request, AdminStudyGroupService $studyGroupService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $category = $studyGroupService->createStudyGroup($request->validated(), $request->file('image'));

        return redirect()->route('admin.categories.show', $category)->with('status', 'Nhóm học đã được tạo thành công.');
    }

    public function show(Category $category, AdminStudyGroupService $studyGroupService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('admin.study_groups.show', array_merge(
            ['current' => $current],
            $studyGroupService->getStudyGroupDetail($category),
        ));
    }

    public function edit(Category $category)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('admin.study_groups.edit', compact('current', 'category'));
    }

    public function update(UpdateStudyGroupRequest $request, Category $category, AdminStudyGroupService $studyGroupService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $studyGroupService->updateStudyGroup($category, $request->validated(), $request->file('image'));

        return redirect()->route('admin.categories.show', $category)->with('status', 'Nhóm học đã được cập nhật.');
    }

    public function deactivate(Category $category, AdminStudyGroupService $studyGroupService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $message = $studyGroupService->deactivateStudyGroup($category);

        return redirect()->route('admin.categories.show', $category)->with('status', $message);
    }

    public function activate(Category $category, AdminStudyGroupService $studyGroupService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $studyGroupService->activateStudyGroup($category);

        return redirect()->route('admin.categories.show', $category)->with('status', 'Nhóm học đã được kích hoạt lại.');
    }
}
