<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);

        if ($redirect) {
            return $redirect;
        }

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'student_id' => $request->integer('student_id') ?: null,
            'subject_id' => $request->integer('subject_id') ?: null,
            'course_id' => $request->integer('course_id') ?: null,
            'class_room_id' => $request->integer('class_room_id') ?: null,
        ];

        $gradesQuery = Grade::query()
            ->with([
                'student',
                'teacher',
                'module',
                'classRoom.subject.category',
                'classRoom.course.subject',
                'enrollment.course.subject',
            ])
            ->when($filters['search'] !== '', function (Builder $query) use ($filters) {
                $search = $filters['search'];

                $query->where(function (Builder $builder) use ($search) {
                    $builder->whereHas('student', function (Builder $studentQuery) use ($search) {
                        $studentQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })->orWhere('test_name', 'like', '%' . $search . '%')
                      ->orWhereHas('classRoom', function (Builder $classQuery) use ($search) {
                          $classQuery->where('name', 'like', '%' . $search . '%')
                              ->orWhereHas('subject', fn (Builder $subjectQuery) => $subjectQuery->where('name', 'like', '%' . $search . '%'))
                              ->orWhereHas('course', fn (Builder $courseQuery) => $courseQuery->where('title', 'like', '%' . $search . '%'));
                      })
                      ->orWhereHas('enrollment.course', fn (Builder $courseQuery) => $courseQuery->where('title', 'like', '%' . $search . '%'))
                      ->orWhereHas('module', fn (Builder $moduleQuery) => $moduleQuery->where('title', 'like', '%' . $search . '%'));
                });
            })
            ->when($filters['student_id'], fn (Builder $query, int $studentId) => $query->where('student_id', $studentId))
            ->when($filters['class_room_id'], fn (Builder $query, int $classRoomId) => $query->where('class_room_id', $classRoomId))
            ->when($filters['course_id'], function (Builder $query, int $courseId) {
                $query->where(function (Builder $builder) use ($courseId) {
                    $builder->whereHas('enrollment', fn (Builder $enrollmentQuery) => $enrollmentQuery->where('course_id', $courseId))
                        ->orWhereHas('classRoom', fn (Builder $classQuery) => $classQuery->where('course_id', $courseId));
                });
            })
            ->when($filters['subject_id'], function (Builder $query, int $subjectId) {
                $query->where(function (Builder $builder) use ($subjectId) {
                    $builder->whereHas('classRoom', fn (Builder $classQuery) => $classQuery->where('subject_id', $subjectId))
                        ->orWhereHas('enrollment', function (Builder $enrollmentQuery) use ($subjectId) {
                            $enrollmentQuery->where('subject_id', $subjectId)
                                ->orWhereHas('course', fn (Builder $courseQuery) => $courseQuery->where('subject_id', $subjectId));
                        });
                });
            });

        $scoreQuery = clone $gradesQuery;

        $summary = [
            'totalGrades' => (clone $gradesQuery)->count(),
            'uniqueStudents' => (clone $gradesQuery)->distinct('student_id')->count('student_id'),
            'uniqueClasses' => (clone $gradesQuery)->distinct('class_room_id')->count('class_room_id'),
            'averageScore' => (clone $scoreQuery)->whereNotNull('score')->exists()
                ? round((float) (clone $scoreQuery)->whereNotNull('score')->avg('score'), 1)
                : null,
        ];

        $grades = $gradesQuery
            ->latest('updated_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $students = User::query()
            ->students()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $subjects = Subject::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $courses = Course::query()
            ->with('subject')
            ->orderBy('title')
            ->get(['id', 'title', 'subject_id']);

        $classRooms = ClassRoom::query()
            ->with(['subject', 'course'])
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('quan_tri.diem_so.index', compact(
            'current',
            'filters',
            'grades',
            'summary',
            'students',
            'subjects',
            'courses',
            'classRooms'
        ));
    }
}
