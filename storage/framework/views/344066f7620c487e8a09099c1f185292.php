
<?php $__env->startSection('title', 'Quiz: ' . $quiz->title); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">
    <a href="<?php echo e(route('courses.show', $course->id)); ?>" class="text-primary hover:underline">← Quay lại lớp học</a>
    <div class="bg-white p-6 rounded-xl shadow-sm">
        <h1 class="text-2xl font-bold mb-1"><?php echo e($quiz->title); ?></h1>
        <p class="text-gray-600 mb-4"><?php echo e($quiz->description); ?></p>
        <form method="post" action="<?php echo e(route('courses.quiz.submit', [$course->id, $quiz->id])); ?>" class="space-y-6">
            <?php echo csrf_field(); ?>
            <?php $__currentLoopData = $quiz->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="font-semibold"><?php echo e($loop->iteration); ?>. <?php echo e($question->question); ?></div>
                    <div class="text-sm text-gray-500 mb-3">Loại: <?php echo e(ucfirst(str_replace('_', ' ', $question->type))); ?></div>

                    <?php if($question->type === 'multiple_choice' || $question->type === 'true_false'): ?>
                        <?php $__currentLoopData = $question->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="flex items-center gap-2 text-gray-700">
                                <input type="radio" name="answers[<?php echo e($question->id); ?>]" value="<?php echo e($option->id); ?>" required class="text-primary focus:ring-primary" />
                                <?php echo e($option->option_text); ?>

                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <textarea name="answers[<?php echo e($question->id); ?>]" rows="3" class="w-full border rounded p-2" placeholder="Trả lời ngắn"></textarea>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary-dark">Nộp bài</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views\courses\quiz.blade.php ENDPATH**/ ?>