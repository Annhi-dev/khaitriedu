<?php

namespace App\Mail;

use App\Models\TeacherApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeacherApplicationReviewedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TeacherApplication $application,
        public string $action,
        public ?string $reviewMessage = null,
        public ?string $username = null,
        public ?string $temporaryPassword = null,
        public ?string $loginUrl = null,
    ) {
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine())
            ->view('emails.teacher_application_reviewed');
    }

    protected function subjectLine(): string
    {
        return match ($this->action) {
            TeacherApplication::STATUS_APPROVED => 'KhaiTriEdu - Hồ sơ ứng tuyển giảng viên đã được duyệt',
            TeacherApplication::STATUS_NEEDS_REVISION => 'KhaiTriEdu - Hồ sơ ứng tuyển cần bổ sung',
            TeacherApplication::STATUS_REJECTED => 'KhaiTriEdu - Kết quả hồ sơ ứng tuyển giảng viên',
            default => 'KhaiTriEdu - Cập nhật hồ sơ ứng tuyển giảng viên',
        };
    }
}
