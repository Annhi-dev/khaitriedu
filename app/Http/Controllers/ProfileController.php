<?php

namespace App\Http\Controllers;

use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $this->resolveCurrentUser($request);

        if (! $user) {
            return redirect()->route('login')->with('error', 'Vui long dang nhap de tiep tuc.');
        }

        [$layout, $updateRoute, $backRoute] = $this->resolveProfileContext($user);

        return view('ho_so.chinh_sua', [
            'user' => $user,
            'layout' => $layout,
            'updateRoute' => $updateRoute,
            'backRoute' => $backRoute,
        ]);
    }

    public function update(Request $request)
    {
        $user = $this->resolveCurrentUser($request);

        if (! $user) {
            return redirect()->route('login')->with('error', 'Vui long dang nhap de tiep tuc.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', Rule::unique('nguoi_dung', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('nguoi_dung', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'current_password' => ['nullable', 'string', 'required_with:new_password'],
            'new_password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'avatar.image' => 'Tep anh dai dien khong hop le.',
            'avatar.mimes' => 'Anh dai dien chi ho tro dinh dang JPG, PNG hoac WEBP.',
            'avatar.max' => 'Anh dai dien toi da 2MB.',
            'current_password.required_with' => 'Vui long nhap mat khau hien tai de doi mat khau.',
            'new_password.confirmed' => 'Xac nhan mat khau moi khong khop.',
        ]);

        if ($request->filled('new_password') && ! Hash::check((string) $request->input('current_password'), (string) $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Mat khau hien tai khong dung.',
            ]);
        }

        $user->fill([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $request->filled('phone') ? $validated['phone'] : null,
        ]);

        if ($request->filled('new_password')) {
            $user->password = $validated['new_password'];
        }

        if ($request->hasFile('avatar')) {
            $newAvatarPath = $request->file('avatar')->store('avatars', 'public');

            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $user->avatar_path = $newAvatarPath;
        }

        $user->save();

        return back()->with('status', 'Cập nhật thông tin cá nhân thành công.');
    }

    private function resolveCurrentUser(Request $request): ?NguoiDung
    {
        return $this->sessionUser() ?? Auth::user();
    }

    private function resolveProfileContext(NguoiDung $user): array
    {
        if ($user->isAdmin()) {
            return ['bo_cuc.quan_tri', 'admin.profile.update', 'admin.dashboard'];
        }

        if ($user->isTeacher()) {
            return ['bo_cuc.giao_vien', 'teacher.profile.update', 'teacher.dashboard'];
        }

        return ['bo_cuc.hoc_vien', 'student.profile.update', 'student.dashboard'];
    }
}
