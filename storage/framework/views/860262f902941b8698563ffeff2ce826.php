
<?php $__env->startSection('title', $lesson->title); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">
    <a href="<?php echo e(route('courses.show', $course->id)); ?>" class="text-primary hover:underline">← Quay lại lớp học</a>
    <div class="bg-white p-6 rounded-xl shadow-sm">
        <h1 class="text-2xl font-bold mb-2"><?php echo e($lesson->title); ?></h1>
        <div class="text-gray-600 mb-4">Module: <?php echo e($module->title); ?></div>
        <p class="text-gray-700 mb-4"><?php echo e($lesson->description); ?></p>
        <?php if($lesson->video_url): ?>
            <div class="mb-4">
                <iframe src="<?php echo e($lesson->video_url); ?>" class="w-full h-80 rounded-lg" allowfullscreen></iframe>
            </div>
        <?php endif; ?>
        <div class="prose max-w-none text-gray-700"><?php echo nl2br(e($lesson->content)); ?></div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm">
        <h2 class="text-xl font-semibold mb-3">Quiz liên quan</h2>
        <?php if($lesson->quiz): ?>
            <a href="<?php echo e(route('courses.quiz.show', [$course->id, $lesson->quiz->id])); ?>" class="inline-block bg-primary hover:bg-primary-dark text-white px-5 py-2 rounded-lg">Làm quiz: <?php echo e($lesson->quiz->title); ?></a>
        <?php else: ?>
            <p class="text-gray-600">Hiện chưa có quiz trong bài học này.</p>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views\courses\lesson.blade.php ENDPATH**/ ?>