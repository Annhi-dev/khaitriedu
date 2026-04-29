<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoomRequest;
use App\Http\Requests\Admin\UpdateRoomRequest;
use App\Models\PhongHoc;
use App\Models\NguoiDung;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $filters = $request->only(['search', 'status']);

        $rooms = PhongHoc::query()
            ->withCount([
                'timeSlots',
                'timeSlots as open_time_slots_count' => fn ($query) => $query->where('status', 'open_for_registration'),
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->query('search'));
                $query->where(function ($roomQuery) use ($search) {
                    $roomQuery->where('code', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%')
                        ->orWhere('location', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->orderBy('code')
            ->paginate(12)
            ->withQueryString();

        $summary = [
            'total' => PhongHoc::count(),
            'active' => PhongHoc::where('status', PhongHoc::STATUS_ACTIVE)->count(),
            'maintenance' => PhongHoc::where('status', PhongHoc::STATUS_MAINTENANCE)->count(),
            'capacity' => (int) PhongHoc::sum('capacity'),
        ];

        return view('quan_tri.phong_hoc.index', compact('current', 'filters', 'rooms', 'summary'));
    }

    public function create()
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('quan_tri.phong_hoc.create', [
            'current' => $current,
            'room' => new PhongHoc([
                'status' => PhongHoc::STATUS_ACTIVE,
                'capacity' => 20,
            ]),
            'statuses' => PhongHoc::statusOptions(),
        ]);
    }

    public function store(StoreRoomRequest $request)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $data = $request->validated();
        if (blank($data['code'] ?? null)) {
            $last = PhongHoc::latest('id')->first();
            $number = $last && preg_match('/^PH(\d+)$/', $last->code, $matches) ? intval($matches[1]) + 1 : 1;
            $data['code'] = 'PH' . str_pad($number, 3, '0', STR_PAD_LEFT);
        }

        $data['status'] = $data['status'] ?? PhongHoc::STATUS_ACTIVE;

        PhongHoc::create($data);

        return redirect()->route('admin.rooms.index')->with('status', 'Phong hoc da duoc tao.');
    }

    public function edit(PhongHoc $room)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('quan_tri.phong_hoc.edit', compact('current', 'room') + [
            'statuses' => PhongHoc::statusOptions(),
        ]);
    }

    public function update(UpdateRoomRequest $request, PhongHoc $room)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $room->update($request->validated());

        return redirect()->route('admin.rooms.index')->with('status', 'Phong hoc da duoc cap nhat.');
    }
}
