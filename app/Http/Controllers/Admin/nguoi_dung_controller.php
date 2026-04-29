<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VaiTro;
use App\Models\NguoiDung;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        [$user, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $users = NguoiDung::with('role')->orderBy('id', 'desc')->get();

        return view('quan_tri.nguoi_dung', compact('users', 'user'));
    }

    public function show($id)
    {
        [$user, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $target = NguoiDung::with('role')->find($id);
        if (! $target) {
            return redirect()->route('admin.users')->with('error', 'Người dùng không tồn tại.');
        }

        return view('quan_tri.nguoi_dung.show', compact('target', 'user'));
    }

    public function store(Request $request)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:nguoi_dung,username',
            'email' => 'required|email|unique:nguoi_dung,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,teacher,student',
        ]);

        NguoiDung::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => VaiTro::idByName($data['role']),
            'status' => NguoiDung::STATUS_ACTIVE,
            'email_verified_at' => Carbon::now(),
        ]);

        return redirect()->route('admin.users')->with('status', 'Người dùng được thêm thành công.');
    }

    public function update(Request $request, $id)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $user = NguoiDung::find($id);
        if (! $user) {
            return back()->with('error', 'Người dùng không tồn tại.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,teacher,student',
        ]);

        $user->name = $data['name'];
        $user->role_id = VaiTro::idByName($data['role']);
        $user->save();

        return redirect()->route('admin.users')->with('status', 'Cập nhật người dùng thành công.');
    }

    public function destroy($id)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        if ($user = NguoiDung::find($id)) {
            $user->delete();
        }

        return redirect()->route('admin.users')->with('status', 'Người dùng đã được xóa.');
    }
}
