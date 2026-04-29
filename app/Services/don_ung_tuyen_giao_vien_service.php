<?php

namespace App\Services;

use App\Mail\TeacherApplicationReviewedMail;
use App\Models\PhongBan;
use App\Models\DonUngTuyenGiaoVien;
use App\Models\VaiTro;
use App\Models\NguoiDung;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminTeacherApplicationService
{
    public function paginateApplications(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));

        return DonUngTuyenGiaoVien::query()
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

    public function review(DonUngTuyenGiaoVien $application, array $data, NguoiDung $admin): DonUngTuyenGiaoVien
    {
        return DB::transaction(function () use ($application, $data, $admin) {
            $application->status = $data['action'];
            $application->admin_note = $data['admin_note'] ?? null;
            $application->rejection_reason = $data['action'] === DonUngTuyenGiaoVien::STATUS_REJECTED
                ? ($data['rejection_reason'] ?? null)
                : null;
            $application->reviewed_at = now();
            $application->reviewed_by = $admin->id;
            $application->save();

            $credentials = null;

            if ($data['action'] === DonUngTuyenGiaoVien::STATUS_APPROVED) {
                $credentials = $this->activateTeacherAccount($application);
            }

            $this->sendReviewEmail($application, $data['action'], $credentials);

            return $application;
        });
    }

    public function resolveRelatedUser(DonUngTuyenGiaoVien $application): ?NguoiDung
    {
        return NguoiDung::where('email', $application->email)->first();
    }

    protected function activateTeacherAccount(DonUngTuyenGiaoVien $application): array
    {
        $user = NguoiDung::where('email', $application->email)->first();
        $temporaryPassword = $this->generateTemporaryPassword();

        if (! $user) {
            $user = NguoiDung::create([
                'name' => $application->name,
                'email' => $application->email,
                'phone' => $application->phone,
                'username' => $this->generateUniqueUsername($application->email),
                'password' => Hash::make($temporaryPassword),
                'role_id' => VaiTro::idByName(NguoiDung::ROLE_TEACHER),
                'department_id' => $this->resolveTeacherDepartmentId(),
                'status' => NguoiDung::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]);

            return [
                'user' => $user,
                'temporary_password' => $temporaryPassword,
            ];
        }

        $user->fill([
            'name' => $application->name,
            'phone' => $application->phone,
            'role_id' => VaiTro::idByName(NguoiDung::ROLE_TEACHER),
            'department_id' => $this->resolveTeacherDepartmentId($user),
            'status' => NguoiDung::STATUS_ACTIVE,
        ]);
        $user->username = $user->username ?: $this->generateUniqueUsername($application->email);
        $user->password = Hash::make($temporaryPassword);

        if (! $user->email_verified_at) {
            $user->email_verified_at = now();
        }

        $user->save();

        return [
            'user' => $user,
            'temporary_password' => $temporaryPassword,
        ];
    }

    protected function generateUniqueUsername(string $email): string
    {
        $base = Str::of($email)->before('@')->lower()->replaceMatches('/[^a-z0-9]+/', '.')->trim('.');
        $candidate = (string) $base;

        if ($candidate === '') {
            $candidate = 'teacher';
        }

        $suffix = 1;
        while (NguoiDung::where('username', $candidate)->exists()) {
            $candidate = $base . '.' . str_pad((string) $suffix, 3, '0', STR_PAD_LEFT);
            $suffix++;
        }

        return $candidate;
    }

    protected function generateTemporaryPassword(): string
    {
        return 'GV@' . Str::upper(Str::random(4)) . random_int(1000, 9999);
    }

    protected function resolveTeacherDepartmentId(?NguoiDung $user = null): ?int
    {
        if ($user?->department_id) {
            return $user->department_id;
        }

        if (! Schema::hasTable('phong_ban') || ! Schema::hasColumn('nguoi_dung', 'department_id')) {
            return null;
        }

        return PhongBan::query()->orderBy('id')->value('id');
    }

    protected function sendReviewEmail(DonUngTuyenGiaoVien $application, string $action, ?array $credentials = null): void
    {
        Mail::to($application->email)->send(new TeacherApplicationReviewedMail(
            application: $application,
            action: $action,
            reviewMessage: $this->resolveReviewMessage($application, $action),
            username: $credentials['user']->username ?? null,
            temporaryPassword: $credentials['temporary_password'] ?? null,
            loginUrl: $action === DonUngTuyenGiaoVien::STATUS_APPROVED ? route('login') : null,
        ));
    }

    protected function resolveReviewMessage(DonUngTuyenGiaoVien $application, string $action): ?string
    {
        return match ($action) {
            DonUngTuyenGiaoVien::STATUS_REJECTED => $application->rejection_reason,
            DonUngTuyenGiaoVien::STATUS_NEEDS_REVISION,
            DonUngTuyenGiaoVien::STATUS_APPROVED => $application->admin_note,
            default => null,
        };
    }
}
