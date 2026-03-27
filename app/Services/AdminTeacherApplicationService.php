<?php

namespace App\Services;

use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminTeacherApplicationService
{
    public function paginateApplications(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));

        return TeacherApplication::query()
            ->with('reviewer')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('experience', 'like', '%' . $search . '%')
                        ->orWhere('message', 'like', '%' . $search . '%');
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function review(TeacherApplication $application, array $data, User $admin): TeacherApplication
    {
        $application->status = $data['action'];
        $application->admin_note = $data['action'] === TeacherApplication::STATUS_NEEDS_REVISION
            ? ($data['admin_note'] ?? null)
            : ($data['admin_note'] ?? null);
        $application->rejection_reason = $data['action'] === TeacherApplication::STATUS_REJECTED
            ? ($data['rejection_reason'] ?? null)
            : null;
        $application->reviewed_at = now();
        $application->reviewed_by = $admin->id;
        $application->save();

        if ($data['action'] === TeacherApplication::STATUS_APPROVED) {
            $this->activateTeacherAccount($application);
        }

        return $application;
    }

    public function resolveRelatedUser(TeacherApplication $application): ?User
    {
        return User::where('email', $application->email)->first();
    }

    protected function activateTeacherAccount(TeacherApplication $application): User
    {
        $user = User::where('email', $application->email)->first();

        if (! $user) {
            return User::create([
                'name' => $application->name,
                'email' => $application->email,
                'phone' => $application->phone,
                'username' => $this->generateUniqueUsername($application->email),
                'password' => Hash::make('12345678'),
                'role' => User::ROLE_TEACHER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]);
        }

        $user->fill([
            'name' => $application->name,
            'phone' => $application->phone,
            'role' => User::ROLE_TEACHER,
            'status' => User::STATUS_ACTIVE,
        ]);

        if (! $user->email_verified_at) {
            $user->email_verified_at = now();
        }

        $user->save();

        return $user;
    }

    protected function generateUniqueUsername(string $email): string
    {
        $base = Str::of($email)->before('@')->lower()->replaceMatches('/[^a-z0-9]+/', '.')->trim('.');
        $candidate = (string) $base;

        if ($candidate === '') {
            $candidate = 'teacher';
        }

        $suffix = 1;
        while (User::where('username', $candidate)->exists()) {
            $candidate = $base . '.' . str_pad((string) $suffix, 3, '0', STR_PAD_LEFT);
            $suffix++;
        }

        return $candidate;
    }
}