<?php $__env->startSection('title', 'Chi tiết lớp học'); ?>
<?php $__env->startSection('content'); ?>
<?php
    $scheduleTemplates = [
        't2_t4_t6_evening' => [
            'label' => 'Tối T2-T4-T6, 18:00 - 20:15',
            'days' => ['Monday', 'Wednesday', 'Friday'],
            'start_time' => '18:00',
            'end_time' => \App\Helpers\ScheduleHelper::normalizeEndTime('18:00'),
        ],
        't3_t5_t7_evening' => [
            'label' => 'Tối T3-T5-T7, 18:00 - 20:15',
            'days' => ['Tuesday', 'Thursday', 'Saturday'],
            'start_time' => '18:00',
            'end_time' => \App\Helpers\ScheduleHelper::normalizeEndTime('18:00'),
        ],
        'weekend_morning' => [
            'label' => 'Sáng T7-CN, 08:30 - 10:45',
            'days' => ['Saturday', 'Sunday'],
            'start_time' => '08:30',
            'end_time' => \App\Helpers\ScheduleHelper::normalizeEndTime('08:30'),
        ],
        'weekend_afternoon' => [
            'label' => 'Chiều T7-CN, 14:00 - 16:15',
            'days' => ['Saturday', 'Sunday'],
            'start_time' => '14:00',
            'end_time' => \App\Helpers\ScheduleHelper::normalizeEndTime('14:00'),
        ],
        'flexible' => [
            'label' => 'Linh hoạt (tự nhập)',
            'days' => [],
            'start_time' => '',
            'end_time' => '',
        ],
    ];
    $selectedMeetingDays = old('meeting_days', $course->meetingDayValues());
    if (! is_array($selectedMeetingDays)) {
        $selectedMeetingDays = $selectedMeetingDays ? [(string) $selectedMeetingDays] : [];
    }
    $selectedMeetingDays = array_values(array_filter(array_map(fn ($day) => is_string($day) ? trim($day) : null, $selectedMeetingDays)));
    $selectedStartDate = old('start_date', $course->start_date?->format('Y-m-d') ?? '');
    $selectedEndDate = old('end_date', $course->end_date?->format('Y-m-d') ?? '');
    $selectedStartTime = old('start_time', $course->start_time ? substr((string) $course->start_time, 0, 5) : '');
    $selectedEndTime = old('end_time', $course->end_time ? substr((string) $course->end_time, 0, 5) : '');
?>
<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-600">Khóa học triển khai</p>
            <h1 class="mt-1 text-3xl font-semibold text-slate-900"><?php echo e($course->title); ?></h1>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-600">
                <span><?php echo e($course->subject?->name ?? 'Chưa gắn khóa học công khai'); ?></span>
                <span class="text-slate-300">|</span>
                <span><?php echo e($course->subject?->category?->name ?? 'Chưa phân nhóm học'); ?></span>
                <span class="text-slate-300">|</span>
                <span><?php echo e($course->schedule ?: 'Chưa chốt lịch học'); ?></span>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?php echo e(route('admin.courses.modules.index', $course)); ?>" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">Quản lý module</a>
            <a href="<?php echo e(route('admin.courses')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Danh sách lớp học</a>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(320px,1fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Thông tin lớp học</h2>
            <form
                method="post"
                action="<?php echo e(route('admin.courses.update', $course->id)); ?>"
                data-schedule-preview-url="<?php echo e(route('admin.courses.schedule-preview', $course)); ?>"
                class="mt-5 grid gap-4"
            >
                <?php echo csrf_field(); ?>
                <?php if($errors->any()): ?>
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <p class="font-semibold">Lưu chưa thành công</p>
                        <p class="mt-1">Kiểm tra lại phần ngày học, giờ học và các trường bắt buộc bên dưới.</p>
                    </div>
                <?php endif; ?>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Tên lớp học</label>
                        <input name="title" value="<?php echo e($course->title); ?>" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" required />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Thuộc khóa học công khai</label>
                        <select name="subject_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" required>
                            <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($subject->id); ?>" <?php if($course->subject_id == $subject->id): echo 'selected'; endif; ?>><?php echo e($subject->name); ?><?php echo e($subject->category ? ' - ' . $subject->category->name : ''); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="rounded-3xl border border-cyan-100 bg-cyan-50/60 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-cyan-700">Lịch học</p>
                            <h3 class="mt-1 text-base font-semibold text-slate-900">Chọn mẫu nhanh hoặc tự nhập từng phần</h3>
                            <p class="mt-1 text-sm text-slate-600">Nếu lịch bị trùng, hệ thống sẽ chặn lưu và báo lỗi ngay.</p>
                        </div>
                        <div class="sm:w-96">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Mẫu lịch nhanh</label>
                            <select id="schedule-template" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                                <option value="">Tự điền / chỉnh tay</option>
                                <?php $__currentLoopData = $scheduleTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($key); ?>"
                                        data-days="<?php echo e(implode(',', $template['days'])); ?>"
                                        data-start-time="<?php echo e($template['start_time']); ?>"
                                        data-end-time="<?php echo e($template['end_time']); ?>"
                                    >
                                        <?php echo e($template['label']); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Giảng viên</label>
                        <select name="teacher_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                            <option value="">Chưa phân công</option>
                            <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($teacher->id); ?>" <?php if($course->teacher_id == $teacher->id): echo 'selected'; endif; ?>><?php echo e($teacher->displayName()); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Ngày bắt đầu</label>
                        <input type="date" name="start_date" value="<?php echo e($selectedStartDate); ?>" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Ngày kết thúc</label>
                        <input type="date" name="end_date" value="<?php echo e($selectedEndDate); ?>" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Giờ bắt đầu</label>
                        <input type="time" name="start_time" value="<?php echo e($selectedStartTime); ?>" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Giờ kết thúc</label>
                        <input type="time" name="end_time" value="<?php echo e($selectedEndTime); ?>" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-700">Ngày học</label>
                        <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
                            <?php $__currentLoopData = \App\Models\KhoaHoc::dayOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayValue => $dayLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="flex cursor-pointer items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 transition hover:border-cyan-200 hover:bg-cyan-50">
                                    <input type="checkbox" name="meeting_days[]" value="<?php echo e($dayValue); ?>" class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" <?php if(in_array($dayValue, $selectedMeetingDays, true)): echo 'checked'; endif; ?>>
                                    <span><?php echo e($dayLabel); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <span class="font-medium text-slate-900">Lịch sẽ lưu</span>
                                    <p class="mt-1 text-xs text-slate-500">Mẫu xem trước này bám theo dữ liệu vừa chọn.</p>
                                </div>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Xem trước</span>
                            </div>
                            <span id="schedule-preview" class="mt-2 block"><?php echo e($course->formattedSchedule()); ?></span>
                        </div>
                        <?php $__errorArgs = ['meeting_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-2 text-sm font-medium text-rose-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <div id="schedule-live-panel" class="mt-4 rounded-2xl border border-cyan-100 bg-cyan-50/70 px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs uppercase tracking-[0.2em] text-cyan-700">Kiểm tra xung đột trực tiếp</p>
                                    <h4 id="schedule-live-title" class="mt-1 text-sm font-semibold text-slate-900">Sẵn sàng kiểm tra</h4>
                                    <p id="schedule-live-message" class="mt-1 text-sm text-slate-600">Chọn ngày học, ngày và giờ để hệ thống tự dò xung đột ngay.</p>
                                </div>
                                <span id="schedule-live-badge" class="inline-flex shrink-0 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600">Chờ nhập</span>
                            </div>
                            <div id="schedule-live-details" class="mt-4 hidden space-y-4"></div>
                        </div>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Giá khóa học</label>
                        <div class="relative">
                            <input type="number" name="price" value="<?php echo e($course->price ?? 0); ?>" min="0" placeholder="Giá" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 pr-12 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100" />
                            <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-sm text-slate-500">VNĐ</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Mô tả lớp học</label>
                    <textarea name="description" rows="4" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100"><?php echo e($course->description); ?></textarea>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800">Lưu thay đổi lớp học</button>
                </div>
            </form>
        </section>

        <aside class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Thống kê nhanh</h2>
                <?php
                    $totalSessions = $course->modules->sum(fn ($module) => $module->plannedSessionCount());
                ?>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Học viên đã xếp</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($course->enrollments_count ?? 0); ?></p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Số module</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($course->modules->count()); ?></p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Tổng buổi dự kiến</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($totalSessions); ?></p>
                    </div>
                </div>
            </section>

        </aside>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Danh sách module hiện có</h2>
            </div>
            <a href="<?php echo e(route('admin.courses.modules.index', $course)); ?>" class="inline-flex items-center justify-center rounded-2xl border border-cyan-200 px-4 py-2.5 text-sm font-medium text-cyan-700 hover:bg-cyan-50">Mở quản lý module</a>
        </div>

        <div class="mt-5 grid gap-4">
            <?php $__empty_1 = true; $__currentLoopData = $course->modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $statusClasses = $module->status === \App\Models\HocPhan::STATUS_PUBLISHED
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                        : 'border-amber-200 bg-amber-50 text-amber-700';
                ?>
                <div class="rounded-2xl border border-slate-200 px-4 py-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <p class="text-sm font-semibold text-slate-900"><?php echo e($module->position); ?>. <?php echo e($module->title); ?></p>
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold <?php echo e($statusClasses); ?>"><?php echo e($module->statusLabel()); ?></span>
                            </div>
                            <p class="mt-1 text-sm leading-6 text-slate-600"><?php echo e($module->learningSummary()); ?></p>
                            <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                <span><?php echo e($module->sessionCountLabel()); ?></span>
                                <span><?php echo e($module->durationLabel()); ?></span>
                            </div>
                        </div>
                        <a href="<?php echo e(route('admin.courses.modules.edit', [$course, $module])); ?>" class="inline-flex items-center justify-center rounded-xl border border-cyan-200 px-3 py-2 text-xs font-medium text-cyan-700 hover:bg-cyan-50">Sửa module</a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Lớp học này chưa có module nào.</div>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form[data-schedule-preview-url]');
    const previewUrl = form?.dataset.schedulePreviewUrl || '';
    const templateSelect = document.getElementById('schedule-template');
    const preview = document.getElementById('schedule-preview');
    const dayLabels = <?php echo json_encode(\App\Models\KhoaHoc::dayOptions(), 15, 512) ?>;
    const sessionMinutes = <?php echo json_encode(\App\Helpers\ScheduleHelper::sessionMinutes(), 15, 512) ?>;
    const dayCheckboxes = Array.from(document.querySelectorAll('input[name="meeting_days[]"]'));
    const teacherSelect = document.querySelector('select[name="teacher_id"]');
    const startTimeInput = document.querySelector('input[name="start_time"]');
    const endTimeInput = document.querySelector('input[name="end_time"]');
    const startDateInput = document.querySelector('input[name="start_date"]');
    const endDateInput = document.querySelector('input[name="end_date"]');
    const livePanel = document.getElementById('schedule-live-panel');
    const liveTitle = document.getElementById('schedule-live-title');
    const liveMessage = document.getElementById('schedule-live-message');
    const liveBadge = document.getElementById('schedule-live-badge');
    const liveDetails = document.getElementById('schedule-live-details');
    let previewTimer = null;
    let previewRequestId = 0;

    const addMinutes = (time, minutes) => {
        if (! time) {
            return '';
        }

        const [hours, mins] = time.split(':').map((value) => parseInt(value, 10));
        if (Number.isNaN(hours) || Number.isNaN(mins)) {
            return '';
        }

        const totalMinutes = (hours * 60) + mins + minutes;
        const normalizedMinutes = ((totalMinutes % 1440) + 1440) % 1440;
        const nextHours = Math.floor(normalizedMinutes / 60);
        const nextMinutes = normalizedMinutes % 60;

        return `${String(nextHours).padStart(2, '0')}:${String(nextMinutes).padStart(2, '0')}`;
    };

    const syncEndTime = () => {
        if (! startTimeInput || ! endTimeInput || ! startTimeInput.value) {
            return;
        }

        endTimeInput.value = addMinutes(startTimeInput.value, sessionMinutes);
    };

    const escapeHtml = (value) => String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const buildPreviewQuery = () => {
        const params = new URLSearchParams();

        dayCheckboxes.forEach((checkbox) => {
            if (checkbox.checked) {
                params.append('meeting_days[]', checkbox.value);
            }
        });

        if (startDateInput?.value) {
            params.set('start_date', startDateInput.value);
        }

        if (endDateInput?.value) {
            params.set('end_date', endDateInput.value);
        }

        if (startTimeInput?.value) {
            params.set('start_time', startTimeInput.value);
        }

        if (endTimeInput?.value) {
            params.set('end_time', endTimeInput.value);
        }

        if (teacherSelect?.value) {
            params.set('teacher_id', teacherSelect.value);
        }

        return params;
    };

    const setLiveState = ({ tone, title, message, badge, detailsHtml }) => {
        if (! livePanel || ! liveTitle || ! liveMessage || ! liveBadge || ! liveDetails) {
            return;
        }

        const panelToneClasses = {
            neutral: 'mt-4 rounded-2xl border border-cyan-100 bg-cyan-50/70 px-4 py-4',
            loading: 'mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4',
            success: 'mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4',
            danger: 'mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4',
        };

        const badgeToneClasses = {
            neutral: 'inline-flex shrink-0 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600',
            loading: 'inline-flex shrink-0 rounded-full border border-amber-200 bg-white px-3 py-1 text-xs font-semibold text-amber-700',
            success: 'inline-flex shrink-0 rounded-full border border-emerald-200 bg-white px-3 py-1 text-xs font-semibold text-emerald-700',
            danger: 'inline-flex shrink-0 rounded-full border border-rose-200 bg-white px-3 py-1 text-xs font-semibold text-rose-700',
        };

        livePanel.className = panelToneClasses[tone] || panelToneClasses.neutral;
        liveTitle.textContent = title;
        liveMessage.textContent = message;
        liveBadge.className = badgeToneClasses[tone] || badgeToneClasses.neutral;
        liveBadge.textContent = badge;
        liveDetails.innerHTML = detailsHtml || '';
        liveDetails.classList.toggle('hidden', ! detailsHtml);
    };

    const renderConflictAction = (item) => {
        const openButton = item.url
            ? `<a href="${escapeHtml(item.url)}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Mở</a>`
            : '';
        const editButton = item.edit_url
            ? `<a href="${escapeHtml(item.edit_url)}" class="inline-flex items-center justify-center rounded-xl bg-cyan-600 px-3 py-2 text-xs font-semibold text-white hover:bg-cyan-700">Sửa nhanh</a>`
            : '';

        return `<div class="flex flex-wrap gap-2">${openButton}${editButton}</div>`;
    };

    const conflictCardClasses = 'rounded-2xl border border-rose-200 bg-rose-50 p-4 shadow-[0_1px_0_rgba(244,63,94,0.08)]';
    const nestedConflictCardClasses = 'rounded-xl border border-rose-200 bg-white p-3 ring-1 ring-rose-100';

    const renderTeacherConflicts = (items) => {
        if (! items.length) {
            return '';
        }

        return `
            <section class="rounded-2xl border border-rose-100 bg-white p-4">
                <div class="flex items-center justify-between gap-2">
                    <h5 class="text-sm font-semibold text-slate-900">Giảng viên bị trùng</h5>
                    <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">${items.length}</span>
                </div>
                <div class="mt-3 space-y-3">
                    ${items.map((item) => `
                        <div class="${conflictCardClasses}">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0">
                                    <div class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-white px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-rose-700">
                                        <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                        Trùng lịch
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-slate-900">${escapeHtml(item.title || 'Chưa rõ lớp')}</p>
                                    <p class="mt-1 text-sm text-slate-700">${escapeHtml(item.schedule || 'Chưa có lịch')}</p>
                                    <p class="mt-1 text-xs font-medium text-rose-700">${escapeHtml(item.note || '')}</p>
                                </div>
                                ${renderConflictAction(item)}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </section>
        `;
    };

    const renderRoomConflicts = (items) => {
        if (! items.length) {
            return '';
        }

        return `
            <section class="rounded-2xl border border-amber-100 bg-white p-4">
                <div class="flex items-center justify-between gap-2">
                    <h5 class="text-sm font-semibold text-slate-900">Phòng học bị trùng</h5>
                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">${items.length}</span>
                </div>
                <div class="mt-3 space-y-3">
                    ${items.map((item) => `
                        <div class="${conflictCardClasses}">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0">
                                    <div class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-white px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-rose-700">
                                        <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                        Trùng phòng
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-slate-900">${escapeHtml(item.title || 'Chưa rõ lớp')}</p>
                                    <p class="mt-1 text-sm text-slate-700">${escapeHtml(item.schedule || 'Chưa có lịch')}</p>
                                    ${item.room_name ? `<p class="mt-1 text-xs font-medium text-rose-700">Phòng: ${escapeHtml(item.room_name)}</p>` : ''}
                                    <p class="mt-1 text-xs font-medium text-rose-700">${escapeHtml(item.note || '')}</p>
                                </div>
                                ${renderConflictAction(item)}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </section>
        `;
    };

    const renderStudentConflicts = (groups) => {
        if (! groups.length) {
            return '';
        }

        return `
            <section class="rounded-2xl border border-cyan-100 bg-white p-4">
                <div class="flex items-center justify-between gap-2">
                    <h5 class="text-sm font-semibold text-slate-900">Học viên bị trùng</h5>
                    <span class="rounded-full bg-cyan-50 px-2.5 py-1 text-xs font-semibold text-cyan-700">${groups.length}</span>
                </div>
                <div class="mt-3 space-y-4">
                    ${groups.map((group) => `
                        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4">
                            <div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0">
                                    <div class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-white px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-rose-700">
                                        <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                        Trùng học viên
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-slate-900">${escapeHtml(group.student_name || 'Chưa rõ')}</p>
                                    ${group.student_email ? `<p class="mt-1 text-xs font-medium text-rose-700">${escapeHtml(group.student_email)}</p>` : ''}
                                    <p class="mt-1 text-xs font-medium text-rose-700">Số lịch trùng: ${escapeHtml(group.conflict_count ?? 0)}</p>
                                </div>
                                ${group.student_url ? `<a href="${escapeHtml(group.student_url)}" class="inline-flex items-center justify-center rounded-xl border border-cyan-200 bg-white px-3 py-2 text-xs font-semibold text-cyan-700 hover:bg-cyan-50">Mở học viên</a>` : ''}
                            </div>
                            <div class="mt-3 space-y-2">
                                ${(group.conflicts || []).map((conflict) => `
                                    <div class="${nestedConflictCardClasses}">
                                        <div class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-rose-700">
                                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                            Mục trùng
                                        </div>
                                        <p class="mt-3 text-sm font-semibold text-slate-900">${escapeHtml(conflict.course_title || 'Chưa rõ khóa')}</p>
                                        <p class="mt-1 text-sm text-slate-700">${escapeHtml(conflict.schedule || 'Chưa có lịch')}</p>
                                        ${conflict.note ? `<p class="mt-1 text-xs font-medium text-rose-700">${escapeHtml(conflict.note)}</p>` : ''}
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            ${conflict.url ? `<a href="${escapeHtml(conflict.url)}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Mở</a>` : ''}
                                            ${conflict.edit_url ? `<a href="${escapeHtml(conflict.edit_url)}" class="inline-flex items-center justify-center rounded-xl bg-cyan-600 px-3 py-2 text-xs font-semibold text-white hover:bg-cyan-700">Sửa nhanh</a>` : ''}
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </section>
        `;
    };

    const renderLiveDetails = (data) => {
        const teacherItems = data.teacher_conflicts || [];
        const roomItems = data.room_conflicts || [];
        const studentGroups = data.student_conflicts || [];
        const sections = [
            renderTeacherConflicts(teacherItems),
            renderRoomConflicts(roomItems),
            renderStudentConflicts(studentGroups),
        ].filter(Boolean);

        return sections.join('');
    };

    const queueLivePreview = () => {
        if (! previewUrl) {
            return;
        }

        window.clearTimeout(previewTimer);
        previewTimer = window.setTimeout(runLivePreview, 320);
    };

    const runLivePreview = async () => {
        if (! previewUrl) {
            return;
        }

        const requestId = ++previewRequestId;
        setLiveState({
            tone: 'loading',
            title: 'Đang kiểm tra...',
            message: 'Hệ thống đang dò xung đột theo lịch bạn vừa chọn.',
            badge: 'Đang kiểm tra',
            detailsHtml: '',
        });

        try {
            const response = await fetch(`${previewUrl}?${buildPreviewQuery().toString()}`, {
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (requestId !== previewRequestId) {
                return;
            }

            if (! response.ok) {
                throw new Error('Không thể kiểm tra xung đột ngay lúc này.');
            }

            const data = await response.json();
            if (requestId !== previewRequestId) {
                return;
            }

            const ready = Boolean(data.ready);
            const hasConflicts = Boolean(data.has_conflicts);
            const counts = data.counts || {};
            const scheduleLabel = data.candidate?.schedule_label || preview?.textContent || '';
            const conflictSentence = [
                counts.teacher ? `${counts.teacher} giảng viên` : null,
                counts.room ? `${counts.room} phòng học` : null,
                counts.student_groups ? `${counts.student_groups} nhóm học viên` : null,
            ].filter(Boolean).join(', ');

            if (! ready) {
                setLiveState({
                    tone: 'neutral',
                    title: 'Chưa đủ dữ liệu',
                    message: 'Chọn đủ ngày học, ngày bắt đầu/kết thúc và giờ bắt đầu/kết thúc để kiểm tra trùng ngay.',
                    badge: 'Thiếu thông tin',
                    detailsHtml: '',
                });
                return;
            }

            if (! hasConflicts) {
                setLiveState({
                    tone: 'success',
                    title: 'Không phát hiện trùng lịch',
                    message: scheduleLabel
                        ? `Lịch hiện tại (${scheduleLabel}) đang an toàn để lưu.`
                        : 'Lịch hiện tại không bị trùng.',
                    badge: 'Không trùng',
                    detailsHtml: '',
                });
                return;
            }

            setLiveState({
                tone: 'danger',
                title: 'Đã phát hiện xung đột',
                message: conflictSentence
                    ? `Lịch này đang trùng với ${conflictSentence}. Kéo xuống để xem chi tiết từng mục.`
                    : 'Lịch này đang bị trùng. Kéo xuống để xem chi tiết từng mục.',
                badge: 'Bị trùng',
                detailsHtml: renderLiveDetails(data),
            });
        } catch (error) {
            if (requestId !== previewRequestId) {
                return;
            }

            setLiveState({
                tone: 'neutral',
                title: 'Không thể kiểm tra lúc này',
                message: error instanceof Error ? error.message : 'Đã xảy ra lỗi khi kiểm tra xung đột.',
                badge: 'Lỗi kiểm tra',
                detailsHtml: '',
            });
        }
    };

    const buildPreview = () => {
        if (! preview) {
            return;
        }

        const selectedDays = dayCheckboxes
            .filter((checkbox) => checkbox.checked)
            .map((checkbox) => dayLabels[checkbox.value] ?? checkbox.value);
        const timeRange = startTimeInput?.value && endTimeInput?.value
            ? `${startTimeInput.value} - ${endTimeInput.value}`
            : '';
        const dateRange = startDateInput?.value
            ? `Từ ${formatDate(startDateInput.value)}${endDateInput?.value ? ` đến ${formatDate(endDateInput.value)}` : ''}`
            : '';
        const segments = [];

        if (selectedDays.length > 0) {
            segments.push(selectedDays.join(', '));
        }

        if (timeRange) {
            segments.push(timeRange);
        }

        if (dateRange) {
            segments.push(dateRange);
        }

        preview.textContent = segments.length > 0 ? segments.join(' | ') : 'Chưa có lịch cụ thể';
        queueLivePreview();
    };

    const formatDate = (value) => {
        const [year, month, day] = value.split('-');

        return `${day}/${month}/${year}`;
    };

    const applyTemplate = () => {
        if (! templateSelect || ! templateSelect.value) {
            buildPreview();
            return;
        }

        const selected = templateSelect.selectedOptions[0];
        const days = (selected?.dataset.days || '')
            .split(',')
            .map((day) => day.trim())
            .filter(Boolean);

        dayCheckboxes.forEach((checkbox) => {
            checkbox.checked = days.includes(checkbox.value);
        });

        if (startTimeInput && selected?.dataset.startTime !== undefined) {
            startTimeInput.value = selected.dataset.startTime || '';
        }

        syncEndTime();

        buildPreview();
    };

    templateSelect?.addEventListener('change', applyTemplate);
    dayCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', buildPreview));
    teacherSelect?.addEventListener('change', buildPreview);
    startTimeInput?.addEventListener('input', () => {
        syncEndTime();
        buildPreview();
    });
    endTimeInput?.addEventListener('input', buildPreview);
    startDateInput?.addEventListener('input', buildPreview);
    endDateInput?.addEventListener('input', buildPreview);

    buildPreview();
    queueLivePreview();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('bo_cuc.quan_tri', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu-main\resources\views/quan_tri/khoa_hoc/show.blade.php ENDPATH**/ ?>