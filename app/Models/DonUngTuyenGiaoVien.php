<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonUngTuyenGiaoVien extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_NEEDS_REVISION = 'needs_revision';

    protected $table = 'don_ung_tuyen_giao_vien';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'experience',
        'message',
        'status',
        'admin_note',
        'rejection_reason',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function reviewer()
    {
        return $this->belongsTo(NguoiDung::class, 'reviewed_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_NEEDS_REVISION => 'Needs revision',
            default => ucfirst((string) $this->status),
        };
    }
}