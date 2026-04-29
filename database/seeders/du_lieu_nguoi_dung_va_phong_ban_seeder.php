<?php

namespace Database\Seeders;

use App\Models\PhongBan;
use App\Models\VaiTro;
use App\Models\DonUngTuyenGiaoVien;
use App\Models\NguoiDung;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DuLieuNguoiDungVaPhongBanSeeder extends Seeder
{
    public function run(): void
    {
        $admins = $this->seedAdmins();
        $this->seedTeachers();
        $this->seedStudents();
        $this->seedTeacherApplications($admins['primary']);
    }

    protected function seedAdmins(): array
    {
        $now = Carbon::now();

        $primary = NguoiDung::updateOrCreate(
            ['email' => 'admin@khaitriedu.vn'],
            [
                'name' => 'Nguyễn Minh Triết',
                'username' => 'admin',
                'phone' => '0901000001',
                'password' => '123456',
                'role_id' => VaiTro::idByName(NguoiDung::ROLE_ADMIN),
                'status' => NguoiDung::STATUS_ACTIVE,
                'email_verified_at' => $now,
            ]
        );

        $backup = NguoiDung::updateOrCreate(
            ['email' => 'quanly@khaitriedu.vn'],
            [
                'name' => 'Trần Thu Hà',
                'username' => 'quanly',
                'phone' => '0901000002',
                'password' => '123456',
                'role_id' => VaiTro::idByName(NguoiDung::ROLE_ADMIN),
                'status' => NguoiDung::STATUS_ACTIVE,
                'email_verified_at' => $now,
            ]
        );

        return [
            'primary' => $primary,
            'backup' => $backup,
        ];
    }

    protected function seedTeachers(): void
    {
        $departmentIds = [
            'DT' => $this->departmentId('DT'),
            'KTCL' => $this->departmentId('KTCL'),
            'CNTT' => $this->departmentId('CNTT'),
        ];

        $teachers = [
            [
                'email' => 'minhkhang@khaitriedu.vn',
                'name' => 'Nguyễn Minh Khang (Lập trình)',
                'username' => 'minh.khang',
                'phone' => '0902000001',
                'department_id' => $departmentIds['CNTT'],
            ],
            [
                'email' => 'thuylinh@khaitriedu.vn',
                'name' => 'Lê Thùy Linh (Thiết kế Web)',
                'username' => 'thuy.linh',
                'phone' => '0902000002',
                'department_id' => $departmentIds['CNTT'],
            ],
            [
                'email' => 'quochuy@khaitriedu.vn',
                'name' => 'Phạm Quốc Huy (Marketing Digital)',
                'username' => 'quoc.huy',
                'phone' => '0902000003',
                'department_id' => $departmentIds['DT'],
            ],
            [
                'email' => 'ngoclan@khaitriedu.vn',
                'name' => 'Trần Ngọc Lan (Kinh doanh)',
                'username' => 'ngoc.lan',
                'phone' => '0902000004',
                'department_id' => $departmentIds['DT'],
            ],
            [
                'email' => 'thanhmai@khaitriedu.vn',
                'name' => 'Võ Thanh Mai (Phát triển Cá nhân)',
                'username' => 'thanh.mai',
                'phone' => '0902000005',
                'department_id' => $departmentIds['KTCL'],
            ],
            [
                'email' => 'minhtu@khaitriedu.vn',
                'name' => 'Đặng Minh Tú (Kế toán)',
                'username' => 'minh.tu',
                'phone' => '0902000006',
                'department_id' => $departmentIds['DT'],
            ],
            [
                'email' => 'anhdung@khaitriedu.vn',
                'name' => 'Bùi Anh Dũng (Ngoại ngữ)',
                'username' => 'anh.dung',
                'phone' => '0902000007',
                'department_id' => $departmentIds['DT'],
            ],
            [
                'email' => 'baochau@khaitriedu.vn',
                'name' => 'Huỳnh Bảo Châu (Tin học)',
                'username' => 'bao.chau',
                'phone' => '0902000008',
                'department_id' => $departmentIds['CNTT'],
            ],
            [
                'email' => 'hoangnam@khaitriedu.vn',
                'name' => 'Lê Hoàng Nam (Điện dân dụng)',
                'username' => 'hoang.nam',
                'phone' => '0902000009',
                'department_id' => $departmentIds['DT'],
            ],
            [
                'email' => 'hongnhung@khaitriedu.vn',
                'name' => 'Phan Hồng Nhung (Thiết kế đồ họa)',
                'username' => 'hong.nhung',
                'phone' => '0902000010',
                'department_id' => $departmentIds['CNTT'],
            ],
            [
                'email' => 'thanhtung@khaitriedu.vn',
                'name' => 'Cao Thanh Tùng (Bồi dưỡng giáo viên)',
                'username' => 'thanh.tung',
                'phone' => '0902000011',
                'department_id' => $departmentIds['KTCL'],
            ],
            [
                'email' => 'quangphuc@khaitriedu.vn',
                'name' => 'Lưu Quang Phúc (Quản trị kinh doanh)',
                'username' => 'quang.phuc',
                'phone' => '0902000012',
                'department_id' => $departmentIds['DT'],
            ],
            [
                'email' => 'hamy@khaitriedu.vn',
                'name' => 'Nguyễn Thị Hà My (Chăm sóc da)',
                'username' => 'ha.my',
                'phone' => '0902000013',
                'department_id' => $departmentIds['DT'],
            ],
            [
                'email' => 'thaison@khaitriedu.vn',
                'name' => 'Phạm Thái Sơn (Bán hàng)',
                'username' => 'thai.son',
                'phone' => '0902000014',
                'department_id' => $departmentIds['DT'],
            ],
            [
                'email' => 'hongloan@khaitriedu.vn',
                'name' => 'Nguyễn Thị Hồng Loan (Ngoại ngữ)',
                'username' => 'hong.loan',
                'phone' => '0902000015',
                'department_id' => $departmentIds['DT'],
            ],
            [
                'email' => 'hatrang@khaitriedu.vn',
                'name' => 'Mai Hà Trang (Kinh doanh)',
                'username' => 'ha.trang',
                'phone' => '0902000016',
                'department_id' => $departmentIds['DT'],
                'status' => NguoiDung::STATUS_LOCKED,
            ],
        ];

        foreach ($teachers as $index => $teacher) {
            NguoiDung::updateOrCreate(
                ['email' => $teacher['email']],
                [
                    'name' => $teacher['name'],
                    'username' => $teacher['username'],
                    'phone' => $teacher['phone'],
                    'password' => '123456',
                    'role_id' => VaiTro::idByName(NguoiDung::ROLE_TEACHER),
                    'department_id' => $teacher['department_id'],
                    'status' => $teacher['status'] ?? NguoiDung::STATUS_ACTIVE,
                    'email_verified_at' => Carbon::now()->subDays(40 - $index),
                ]
            );
        }
    }

    protected function seedStudents(): void
    {
        $students = [
            ['email' => 'nguyen.thi.an@khaitriedu.vn', 'name' => 'Nguyễn Thị An', 'username' => 'hv01', 'phone' => '0913000001'],
            ['email' => 'tran.gia.han@khaitriedu.vn', 'name' => 'Trần Gia Hân', 'username' => 'hv02', 'phone' => '0913000002'],
            ['email' => 'le.minh.quan@khaitriedu.vn', 'name' => 'Lê Minh Quân', 'username' => 'hv03', 'phone' => '0913000003'],
            ['email' => 'pham.ngoc.linh@khaitriedu.vn', 'name' => 'Phạm Ngọc Linh', 'username' => 'hv04', 'phone' => '0913000004'],
            ['email' => 'vo.hai.nam@khaitriedu.vn', 'name' => 'Võ Hải Nam', 'username' => 'hv05', 'phone' => '0913000005'],
            ['email' => 'dang.thanh.truc@khaitriedu.vn', 'name' => 'Đặng Thanh Trúc', 'username' => 'hv06', 'phone' => '0913000006'],
            ['email' => 'bui.duc.minh@khaitriedu.vn', 'name' => 'Bùi Đức Minh', 'username' => 'hv07', 'phone' => '0913000007'],
            ['email' => 'huynh.bao.ngoc@khaitriedu.vn', 'name' => 'Huỳnh Bảo Ngọc', 'username' => 'hv08', 'phone' => '0913000008'],
            ['email' => 'nguyen.khac.hung@khaitriedu.vn', 'name' => 'Nguyễn Khắc Hưng', 'username' => 'hv09', 'phone' => '0913000009'],
            ['email' => 'luu.thanh.vy@khaitriedu.vn', 'name' => 'Lưu Thanh Vy', 'username' => 'hv10', 'phone' => '0913000010'],
            ['email' => 'phan.quynh.anh@khaitriedu.vn', 'name' => 'Phan Quỳnh Anh', 'username' => 'hv11', 'phone' => '0913000011'],
            ['email' => 'cao.nhat.minh@khaitriedu.vn', 'name' => 'Cao Nhật Minh', 'username' => 'hv12', 'phone' => '0913000012'],
            ['email' => 'ta.phuong.nhi@khaitriedu.vn', 'name' => 'Tạ Phương Nhi', 'username' => 'hv13', 'phone' => '0913000013'],
            ['email' => 'vuong.gia.bao@khaitriedu.vn', 'name' => 'Vương Gia Bảo', 'username' => 'hv14', 'phone' => '0913000014'],
            ['email' => 'duong.thu.ha@khaitriedu.vn', 'name' => 'Dương Thu Hà', 'username' => 'hv15', 'phone' => '0913000015'],
            ['email' => 'trinh.minh.tu@khaitriedu.vn', 'name' => 'Trịnh Minh Tú', 'username' => 'hv16', 'phone' => '0913000016'],
            ['email' => 'ho.ngoc.diep@khaitriedu.vn', 'name' => 'Hồ Ngọc Diệp', 'username' => 'hv17', 'phone' => '0913000017'],
            ['email' => 'le.anh.khoa@khaitriedu.vn', 'name' => 'Lê Anh Khoa', 'username' => 'hv18', 'phone' => '0913000018'],
            ['email' => 'ngo.bao.tran@khaitriedu.vn', 'name' => 'Ngô Bảo Trân', 'username' => 'hv19', 'phone' => '0913000019'],
            ['email' => 'do.van.tai@khaitriedu.vn', 'name' => 'Đỗ Văn Tài', 'username' => 'hv20', 'phone' => '0913000020'],
            ['email' => 'ly.nhat.ha@khaitriedu.vn', 'name' => 'Lý Nhật Hạ', 'username' => 'hv21', 'phone' => '0913000021'],
            ['email' => 'nguyen.hoang.long@khaitriedu.vn', 'name' => 'Nguyễn Hoàng Long', 'username' => 'hv22', 'phone' => '0913000022'],
            ['email' => 'truong.my.duyen@khaitriedu.vn', 'name' => 'Trương Mỹ Duyên', 'username' => 'hv23', 'phone' => '0913000023'],
            ['email' => 'phan.minh.nhat@khaitriedu.vn', 'name' => 'Phan Minh Nhật', 'username' => 'hv24', 'phone' => '0913000024'],
            ['email' => 'le.dieu.linh@khaitriedu.vn', 'name' => 'Lê Diệu Linh', 'username' => 'hv25', 'phone' => '0913000025', 'status' => NguoiDung::STATUS_LOCKED],
            ['email' => 'do.tuan.kiet@khaitriedu.vn', 'name' => 'Đỗ Tuấn Kiệt', 'username' => 'hv26', 'phone' => '0913000026', 'status' => NguoiDung::STATUS_INACTIVE],
        ];

        foreach ($students as $index => $student) {
            NguoiDung::updateOrCreate(
                ['email' => $student['email']],
                [
                    'name' => $student['name'],
                    'username' => $student['username'],
                    'phone' => $student['phone'],
                    'password' => '123456',
                    'role_id' => VaiTro::idByName(NguoiDung::ROLE_STUDENT),
                    'status' => $student['status'] ?? NguoiDung::STATUS_ACTIVE,
                    'email_verified_at' => Carbon::now()->subDays(28 - $index),
                ]
            );
        }
    }

    protected function seedTeacherApplications(NguoiDung $reviewer): void
    {
        $applications = [
            [
                'email' => 'minh.ha@khaitriedu.vn',
                'name' => 'Minh Hà',
                'phone' => '0917000001',
                'experience' => '8 năm giảng dạy tiếng Anh giao tiếp và luyện thi đầu ra.',
                'message' => 'Mong được tham gia các lớp buổi tối và cuối tuần.',
                'status' => DonUngTuyenGiaoVien::STATUS_PENDING,
            ],
            [
                'email' => 'hoangnam@khaitriedu.vn',
                'name' => 'Lê Hoàng Nam',
                'phone' => '0902000009',
                'experience' => '10 năm phụ trách điện dân dụng và an toàn điện.',
                'message' => 'Sẵn sàng nhận lớp thực hành và lớp xưởng.',
                'status' => DonUngTuyenGiaoVien::STATUS_APPROVED,
                'admin_note' => 'Hồ sơ phù hợp. Mời tham gia buổi trao đổi trực tiếp.',
            ],
            [
                'email' => 'mai.trang@khaitriedu.vn',
                'name' => 'Phạm Mai Trang',
                'phone' => '0917000003',
                'experience' => '5 năm kế toán doanh nghiệp và dịch vụ thuế.',
                'message' => 'Có thể bổ sung hồ sơ sư phạm nếu cần.',
                'status' => DonUngTuyenGiaoVien::STATUS_NEEDS_REVISION,
                'admin_note' => 'Vui lòng bổ sung chứng chỉ sư phạm trước khi xét duyệt tiếp.',
            ],
            [
                'email' => 'quocdat@khaitriedu.vn',
                'name' => 'Trần Quốc Đạt',
                'phone' => '0917000004',
                'experience' => '3 năm hỗ trợ đào tạo nội bộ.',
                'message' => 'Muốn phát triển sang mảng giảng dạy bán hàng.',
                'status' => DonUngTuyenGiaoVien::STATUS_REJECTED,
                'rejection_reason' => 'Chưa đáp ứng yêu cầu kinh nghiệm đứng lớp.',
            ],
        ];

        foreach ($applications as $index => $application) {
            DonUngTuyenGiaoVien::updateOrCreate(
                ['email' => $application['email']],
                [
                    'name' => $application['name'],
                    'phone' => $application['phone'],
                    'experience' => $application['experience'],
                    'message' => $application['message'],
                    'status' => $application['status'],
                    'admin_note' => $application['admin_note'] ?? null,
                    'rejection_reason' => $application['rejection_reason'] ?? null,
                    'reviewed_by' => in_array($application['status'], [
                        DonUngTuyenGiaoVien::STATUS_APPROVED,
                        DonUngTuyenGiaoVien::STATUS_REJECTED,
                        DonUngTuyenGiaoVien::STATUS_NEEDS_REVISION,
                    ], true) ? $reviewer->id : null,
                    'reviewed_at' => in_array($application['status'], [
                        DonUngTuyenGiaoVien::STATUS_APPROVED,
                        DonUngTuyenGiaoVien::STATUS_REJECTED,
                        DonUngTuyenGiaoVien::STATUS_NEEDS_REVISION,
                    ], true) ? Carbon::now()->subDays(12 - $index) : null,
                ]
            );
        }
    }

    protected function departmentId(string $code): ?int
    {
        return PhongBan::query()->where('code', $code)->value('id');
    }
}
