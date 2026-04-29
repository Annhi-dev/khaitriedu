<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhungGioKhoaHoc;
use App\Models\LuaChonNguyenVongKhungGio;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use Illuminate\Http\Request;

class SlotTrackingController extends Controller
{
    public function index(Request $request)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['status', 'subject_id']);

        $timeSlots = KhungGioKhoaHoc::query()
            ->with(['subject.category', 'teacher', 'room'])
            ->withCount([
                'registrations',
                'registrations as pending_registrations_count' => fn ($query) => $query->where('status', 'pending'),
                'registrations as scheduled_registrations_count' => fn ($query) => $query->where('status', 'scheduled'),
            ])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->when($request->filled('subject_id'), fn ($query) => $query->where('subject_id', (int) $request->query('subject_id')))
            ->orderByDesc('registrations_count')
            ->orderBy('start_time')
            ->paginate(12)
            ->withQueryString();

        $summary = [
            'open_slots' => KhungGioKhoaHoc::where('status', KhungGioKhoaHoc::STATUS_OPEN_FOR_REGISTRATION)->count(),
            'ready_slots' => KhungGioKhoaHoc::where('status', KhungGioKhoaHoc::STATUS_READY_TO_OPEN_CLASS)->count(),
            'choices' => LuaChonNguyenVongKhungGio::count(),
            'top_demand' => (int) (LuaChonNguyenVongKhungGio::query()
                ->selectRaw('count(*) as aggregate')
                ->groupBy('course_time_slot_id')
                ->orderByDesc('aggregate')
                ->value('aggregate') ?? 0),
        ];

        return view('quan_tri.theo_doi_khung_gio.index', compact('current', 'filters', 'timeSlots', 'summary') + [
            'statuses' => KhungGioKhoaHoc::statusOptions(),
            'subjects' => MonHoc::with('category')->orderBy('name')->get(),
        ]);
    }
}
