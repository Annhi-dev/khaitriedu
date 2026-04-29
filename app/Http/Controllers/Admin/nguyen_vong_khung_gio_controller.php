<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguyenVongKhungGio;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use Illuminate\Http\Request;

class SlotRegistrationController extends Controller
{
    public function index(Request $request)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search', 'status', 'subject_id']);

        $registrations = NguyenVongKhungGio::query()
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
            'total' => NguyenVongKhungGio::count(),
            'pending' => NguyenVongKhungGio::where('status', NguyenVongKhungGio::STATUS_PENDING)->count(),
            'scheduled' => NguyenVongKhungGio::where('status', NguyenVongKhungGio::STATUS_SCHEDULED)->count(),
            'needs_reselect' => NguyenVongKhungGio::where('status', NguyenVongKhungGio::STATUS_NEEDS_RESELECT)->count(),
        ];

        return view('quan_tri.nguyen_vong_khung_gio.index', compact('current', 'filters', 'registrations', 'summary') + [
            'statuses' => NguyenVongKhungGio::statusOptions(),
            'subjects' => MonHoc::with('category')->orderBy('name')->get(),
        ]);
    }

    public function show(NguyenVongKhungGio $slotRegistration)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
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

        return view('quan_tri.nguyen_vong_khung_gio.show', compact('current', 'slotRegistration', 'choices'));
    }
}
