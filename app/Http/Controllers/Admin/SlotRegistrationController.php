<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlotRegistration;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class SlotRegistrationController extends Controller
{
    public function index(Request $request)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search', 'status', 'subject_id']);

        $registrations = SlotRegistration::query()
            ->with(['student', 'subject.category', 'reviewer'])
            ->withCount('choices')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->query('search'));

                $query->where(function ($slotQuery) use ($search) {
                    $slotQuery->whereHas('student', fn ($studentQuery) => $studentQuery->where('name', 'like', '%' . $search . '%'))
                        ->orWhereHas('subject', fn ($subjectQuery) => $subjectQuery->where('name', 'like', '%' . $search . '%'))
                        ->orWhere('note', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->when($request->filled('subject_id'), fn ($query) => $query->where('subject_id', (int) $request->query('subject_id')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $summary = [
            'total' => SlotRegistration::count(),
            'pending' => SlotRegistration::where('status', SlotRegistration::STATUS_PENDING)->count(),
            'scheduled' => SlotRegistration::where('status', SlotRegistration::STATUS_SCHEDULED)->count(),
            'needs_reselect' => SlotRegistration::where('status', SlotRegistration::STATUS_NEEDS_RESELECT)->count(),
        ];

        return view('admin.slot_registrations.index', compact('current', 'filters', 'registrations', 'summary') + [
            'statuses' => SlotRegistration::statusOptions(),
            'subjects' => Subject::with('category')->orderBy('name')->get(),
        ]);
    }

    public function show(SlotRegistration $slotRegistration)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $slotRegistration->load([
            'student',
            'subject.category',
            'reviewer',
            'choices.courseTimeSlot.subject',
            'choices.courseTimeSlot.teacher',
            'choices.courseTimeSlot.room',
        ]);

        $choices = $slotRegistration->choices->sortBy('priority')->values();

        return view('admin.slot_registrations.show', compact('current', 'slotRegistration', 'choices'));
    }
}
