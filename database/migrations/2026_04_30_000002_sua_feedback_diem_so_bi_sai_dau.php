<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('diem')) {
            return;
        }

        $feedbackByEmail = [
            'nguyen.thi.an@khaitriedu.vn' => 'Phát âm tự nhiên, phản xạ tốt.',
            'tran.gia.han@khaitriedu.vn' => 'Nắm vững mẫu câu giao tiếp.',
            'le.minh.quan@khaitriedu.vn' => 'Cần luyện nghe thêm.',
            'pham.ngoc.linh@khaitriedu.vn' => 'Đã cải thiện rõ rệt.',
            'vo.hai.nam@khaitriedu.vn' => 'Cần tự tin hơn khi nói.',
            'dang.thanh.truc@khaitriedu.vn' => 'Nên ôn lại từ vựng nền tảng.',
            'ta.phuong.nhi@khaitriedu.vn' => 'Xử lý file và định dạng rất tốt.',
            'vuong.gia.bao@khaitriedu.vn' => 'Công thức Excel chắc tay.',
            'duong.thu.ha@khaitriedu.vn' => 'Đã hiểu thao tác cơ bản.',
            'trinh.minh.tu@khaitriedu.vn' => 'Cần thêm thời gian thực hành.',
            'ho.ngoc.diep@khaitriedu.vn' => 'Nắm được các bước chính.',
            'ly.nhat.ha@khaitriedu.vn' => 'Lập chứng từ khá chính xác.',
            'nguyen.hoang.long@khaitriedu.vn' => 'Bút toán ổn, cần cẩn thận hơn.',
            'truong.my.duyen@khaitriedu.vn' => 'Kỹ năng hạch toán đang tốt lên.',
            'phan.minh.nhat@khaitriedu.vn' => 'Nên rà soát số liệu cuối bài.',
        ];

        foreach ($feedbackByEmail as $email => $feedback) {
            $studentId = DB::table('nguoi_dung')
                ->where('email', $email)
                ->value('id');

            if (! $studentId) {
                continue;
            }

            DB::table('diem')
                ->where('student_id', $studentId)
                ->where('feedback', 'like', '%?%')
                ->update(['feedback' => $feedback]);
        }
    }

    public function down(): void
    {
    }
};
