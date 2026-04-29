<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThongBao;
use App\Models\NguoiDung;

class NotificationController extends Controller
{
    public function poll()
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);

        if ($redirect) {
            return $redirect;
        }

        return response()->json($this->notificationPayload($current));
    }

    public function index()
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);

        if ($redirect) {
            return $redirect;
        }

        $notificationQuery = $current->notifications();

        return view('thong_bao.index', [
            'layout' => 'bo_cuc.quan_tri',
            'current' => $current,
            'adminAuthUser' => $current,
            'pageTitle' => 'Hộp thông báo',
            'pageEyebrow' => 'Thông báo',
            'backRoute' => route('admin.dashboard'),
            'backLabel' => 'Về dashboard',
            'openRouteName' => 'admin.notifications.show',
            'emptyMessage' => 'Chưa có thông báo nào cho admin.',
            'notifications' => (clone $notificationQuery)
                ->latest('id')
                ->paginate(12)
                ->withQueryString(),
            'totalNotifications' => $notificationQuery->count(),
            'unreadNotifications' => (clone $notificationQuery)->where('is_read', false)->count(),
        ]);
    }

    public function show($notification)
    {
        [$current, $redirect] = $this->requireRole(NguoiDung::ROLE_ADMIN);

        if ($redirect) {
            return $redirect;
        }

        if (! $notification instanceof ThongBao) {
            $notification = ThongBao::findOrFail($notification);
        }

        if ((int) $notification->user_id !== (int) $current->id) {
            abort(404);
        }

        if (! $notification->is_read) {
            $notification->forceFill([
                'is_read' => true,
                'read_at' => now(),
            ])->save();
        }

        return view('thong_bao.show', [
            'layout' => 'bo_cuc.quan_tri',
            'current' => $current,
            'adminAuthUser' => $current,
            'pageTitle' => 'Chi tiết thông báo',
            'pageEyebrow' => 'Thông báo',
            'backRoute' => route('admin.notifications.index'),
            'backLabel' => 'Về hộp thông báo',
            'notification' => $notification,
            'openUrl' => $notification->link,
        ]);
    }

    private function notificationPayload(NguoiDung $user): array
    {
        $notifications = $user->notifications()->latest('id')->take(5)->get();

        return [
            'unread_count' => $user->notifications()->where('is_read', false)->count(),
            'total_count' => $user->notifications()->count(),
            'notifications' => $notifications->map(fn (ThongBao $notification) => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'is_read' => (bool) $notification->is_read,
                'created_at' => optional($notification->created_at)->toIso8601String(),
                'open_url' => route('admin.notifications.show', $notification),
            ])->values(),
        ];
    }
}
