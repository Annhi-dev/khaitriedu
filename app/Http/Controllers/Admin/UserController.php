<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        [$user, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $users = User::orderBy('id', 'desc')->get();

        return view('admin.users', compact('users', 'user'));
    }

    public function show($id)
    {
        [$user, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $target = User::find($id);
        if (! $target) {
            return redirect()->route('admin.users')->with('error', 'Người dùng không tồn tại.');
        }

        return view('admin.user.show', compact('target', 'user'));
    }

    public function store(Request $request)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:nguoi_dung,username',
            'email' => 'required|email|unique:nguoi_dung,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,giang_vien,hoc_vien',
        ]);

        User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => Carbon::now(),
        ]);

        return redirect()->route('admin.users')->with('status', 'Người dùng được thêm thành công.');
    }

    public function update(Request $request, $id)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $user = User::find($id);
        if (! $user) {
            return back()->with('error', 'Người dùng không tồn tại.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,giang_vien,hoc_vien',
        ]);

        $user->update($data);

        return redirect()->route('admin.users')->with('status', 'Cập nhật người dùng thành công.');
    }

    public function destroy($id)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        if ($user = User::find($id)) {
            $user->delete();
        }

        return redirect()->route('admin.users')->with('status', 'Người dùng đã được xóa.');
    }
}
