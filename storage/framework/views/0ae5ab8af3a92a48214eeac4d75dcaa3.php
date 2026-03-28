<?php $__env->startSection('title', 'Dashboard Admin'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <section class="overflow-hidden rounded-[32px] border border-slate-200 bg-[linear-gradient(135deg,_rgba(15,23,42,0.98),_rgba(8,47,73,0.92),_rgba(15,118,110,0.82))] p-6 text-white shadow-[0_30px_120px_rgba(15,23,42,0.28)] lg:p-8">
        <div class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr] xl:items-end">
            <div>
                <p class="text-xs uppercase tracking-[0.4em] text-cyan-200/80">Dashboard Admin</p>
                <h1 class="mt-3 text-3xl font-extrabold leading-tight lg:text-4xl">Trung tâm điều phối đào tạo và phê duyệt vận hành</h1>
                <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-200/85">
                    Admin là điểm kiểm soát trung tâm của KhaiTriEdu. Từ màn hình này, bạn có thể nhìn nhanh các hàng chờ cần xử lý, lớp học đang vận hành và các tác vụ cần chốt trong ngày.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="<?php echo e(route('admin.enrollments')); ?>" class="inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-3 text-sm font-bold text-slate-900 transition hover:bg-cyan-50">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Xử lý đăng ký học</span>
                    </a>
                    <a href="<?php echo e(route('admin.teacher-applications')); ?>" class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/15">
                        <i class="fas fa-file-signature"></i>
                        <span>Duyệt hồ sơ giảng viên</span>
                    </a>
                    <a href="<?php echo e(route('admin.schedules.queue')); ?>" class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/15">
                        <i class="fas fa-calendar-days"></i>
                        <span>Hàng chờ xếp lịch</span>
                    </a>
                </div>
            </div>
            <div class="grid gap-4 rounded-[28px] border border-white/10 bg-white/10 p-5 backdrop-blur">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-300">Người phụ trách</p>
                    <p class="mt-2 text-xl font-bold"><?php echo e($user->name); ?></p>
                    <p class="mt-1 text-sm text-slate-300">Admin đang đăng nhập</p>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-2xl bg-white/10 px-4 py-4">
                        <p class="text-slate-300">Khóa học public</p>
                        <p class="mt-2 text-2xl font-extrabold"><?php echo e($subjectCount); ?></p>
                    </div>
                    <div class="rounded-2xl bg-white/10 px-4 py-4">
                        <p class="text-slate-300">Lớp đang hoạt động</p>
                        <p class="mt-2 text-2xl font-extrabold"><?php echo e($activeClassCount); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Tổng học viên</p>
                    <p class="mt-3 text-3xl font-extrabold text-slate-950"><?php echo e($studentCount); ?></p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-cyan-50 text-cyan-700">
                    <i class="fas fa-user-graduate text-xl"></i>
                </div>
            </div>
        </article>
        <article class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Tổng giảng viên</p>
                    <p class="mt-3 text-3xl font-extrabold text-slate-950"><?php echo e($teacherCount); ?></p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-emerald-50 text-emerald-700">
                    <i class="fas fa-chalkboard-user text-xl"></i>
                </div>
            </div>
        </article>
        <article class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Đơn ứng tuyển chờ duyệt</p>
                    <p class="mt-3 text-3xl font-extrabold text-slate-950"><?php echo e($pendingTeacherApplications); ?></p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-amber-50 text-amber-700">
                    <i class="fas fa-file-signature text-xl"></i>
                </div>
            </div>
        </article>
        <article class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Đăng ký học chờ xử lý</p>
                    <p class="mt-3 text-3xl font-extrabold text-slate-950"><?php echo e($pendingEnrollments); ?></p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-rose-50 text-rose-700">
                    <i class="fas fa-clipboard-check text-xl"></i>
                </div>
            </div>
        </article>
        <article class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Lớp đang mở</p>
                    <p class="mt-3 text-3xl font-extrabold text-slate-950"><?php echo e($activeClassCount); ?></p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-sky-50 text-sky-700">
                    <i class="fas fa-people-group text-xl"></i>
                </div>
            </div>
        </article>
        <article class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Yêu cầu đổi lịch chờ duyệt</p>
                    <p class="mt-3 text-3xl font-extrabold text-slate-950"><?php echo e($pendingScheduleChangeRequests); ?></p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-violet-50 text-violet-700">
                    <i class="fas fa-calendar-rotate text-xl"></i>
                </div>
            </div>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Recent Pipeline</p>
                    <h2 class="mt-2 text-xl font-extrabold text-slate-950">Đăng ký học gần đây</h2>
                </div>
                <a href="<?php echo e(route('admin.enrollments')); ?>" class="text-sm font-bold text-cyan-700 hover:text-cyan-900">Mở toàn bộ</a>
            </div>
            <div class="mt-4 overflow-hidden rounded-3xl border border-slate-200">
                <?php if($recentEnrollments->count()): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-[0.2em] text-slate-500">
                                <tr>
                                    <th class="px-4 py-4">Học viên</th>
                                    <th class="px-4 py-4">Khóa học</th>
                                    <th class="px-4 py-4">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <?php $__currentLoopData = $recentEnrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-4 py-4">
                                            <p class="font-semibold text-slate-900"><?php echo e($enrollment->user?->name ?? 'Chưa có dữ liệu'); ?></p>
                                            <p class="mt-1 text-xs text-slate-500"><?php echo e($enrollment->user?->email ?? 'Không có email'); ?></p>
                                        </td>
                                        <td class="px-4 py-4 text-slate-600">
                                            <?php echo e($enrollment->subject?->name ?? $enrollment->course?->subject?->name ?? 'Chưa xác định'); ?>

                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700"><?php echo e($enrollment->statusLabel()); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="px-6 py-12 text-center text-sm text-slate-500">Chưa có đăng ký mới cần hiển thị.</div>
                <?php endif; ?>
            </div>
        </article>

        <div class="space-y-6">
            <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Pending Items</p>
                        <h2 class="mt-2 text-xl font-extrabold text-slate-950">Ứng tuyển giảng viên</h2>
                    </div>
                    <a href="<?php echo e(route('admin.teacher-applications')); ?>" class="text-sm font-bold text-cyan-700 hover:text-cyan-900">Xem tất cả</a>
                </div>
                <div class="mt-4 space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $pendingTeacherApplicationsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $application): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <a href="<?php echo e(route('admin.teacher-applications.show', $application)); ?>" class="block rounded-3xl border border-slate-200 px-4 py-4 transition hover:border-cyan-200 hover:bg-cyan-50/40">
                            <p class="font-semibold text-slate-900"><?php echo e($application->name); ?></p>
                            <p class="mt-1 text-sm text-slate-500"><?php echo e($application->email); ?></p>
                            <p class="mt-2 text-xs uppercase tracking-[0.2em] text-amber-600">Đang chờ duyệt</p>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Không có hồ sơ chờ duyệt.</div>
                    <?php endif; ?>
                </div>
            </article>

            <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Operational Alerts</p>
                        <h2 class="mt-2 text-xl font-extrabold text-slate-950">Yêu cầu đổi lịch</h2>
                    </div>
                    <a href="<?php echo e(route('admin.schedule-change-requests.index')); ?>" class="text-sm font-bold text-cyan-700 hover:text-cyan-900">Mở hàng chờ</a>
                </div>
                <div class="mt-4 space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $pendingScheduleRequestsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <a href="<?php echo e(route('admin.schedule-change-requests.show', $request)); ?>" class="block rounded-3xl border border-slate-200 px-4 py-4 transition hover:border-cyan-200 hover:bg-cyan-50/40">
                            <p class="font-semibold text-slate-900"><?php echo e($request->course?->title ?? 'Lớp học'); ?></p>
                            <p class="mt-1 text-sm text-slate-500"><?php echo e($request->teacher?->name ?? 'Chưa rõ giảng viên'); ?></p>
                            <p class="mt-2 text-xs text-slate-500"><?php echo e(\Illuminate\Support\Str::limit($request->reason, 90)); ?></p>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Không có yêu cầu đổi lịch chờ duyệt.</div>
                    <?php endif; ?>
                </div>
            </article>
        </div>
    </section>

    <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Classroom Watch</p>
                <h2 class="mt-2 text-xl font-extrabold text-slate-950">Lớp học mới cập nhật</h2>
            </div>
            <a href="<?php echo e(route('admin.courses')); ?>" class="text-sm font-bold text-cyan-700 hover:text-cyan-900">Quản lý lớp học</a>
        </div>
        <div class="mt-5 grid gap-4 lg:grid-cols-3">
            <?php $__empty_1 = true; $__currentLoopData = $recentCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="rounded-[28px] border border-slate-200 bg-slate-50/70 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400"><?php echo e($course->subject?->category?->name ?? 'Chưa phân nhóm'); ?></p>
                    <h3 class="mt-2 text-lg font-extrabold text-slate-950"><?php echo e($course->title); ?></h3>
                    <div class="mt-4 space-y-2 text-sm text-slate-600">
                        <p>Khóa học: <?php echo e($course->subject?->name ?? 'Chưa gắn khóa học'); ?></p>
                        <p>Giảng viên: <?php echo e($course->teacher?->name ?? 'Chưa phân công'); ?></p>
                        <p>Lịch: <?php echo e($course->formattedSchedule()); ?></p>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-4 py-10 text-center text-sm text-slate-500 lg:col-span-3">Chưa có lớp học nào để hiển thị.</div>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views/admin/dashboard/index.blade.php ENDPATH**/ ?>