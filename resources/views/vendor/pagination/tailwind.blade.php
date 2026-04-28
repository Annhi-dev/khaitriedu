<?php if ($paginator->hasPages()): ?>
    <nav role="navigation" aria-label="<?php echo e(__('Pagination Navigation')); ?>">
        <div class="inline-flex items-center shadow-sm rounded-md overflow-hidden border border-slate-200 bg-white">
            <?php if ($paginator->onFirstPage()): ?>
                <span aria-disabled="true" aria-label="<?php echo e(__('pagination.previous')); ?>">
                    <span class="inline-flex items-center justify-center px-3 py-3 text-sm font-medium text-gray-400 bg-white border-r border-slate-200 cursor-not-allowed" aria-hidden="true">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </span>
            <?php else: ?>
                <a href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" class="inline-flex items-center justify-center px-3 py-3 text-sm font-medium text-slate-600 bg-white border-r border-slate-200 hover:bg-slate-50 hover:text-primary transition" aria-label="<?php echo e(__('pagination.previous')); ?>">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
            <?php endif; ?>

            <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if (is_string($element)): ?>
                    <span aria-disabled="true">
                        <span class="inline-flex items-center justify-center min-w-12 px-4 py-3 text-sm font-medium text-slate-500 bg-white border-r border-slate-200 cursor-default">
                            <?php echo e($element); ?>
                        </span>
                    </span>
                <?php endif; ?>

                <?php if (is_array($element)): ?>
                    <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if ($page == $paginator->currentPage()): ?>
                            <span aria-current="page">
                                <span class="inline-flex items-center justify-center min-w-12 px-4 py-3 text-sm font-semibold text-white bg-primary border-r border-primary cursor-default">
                                    <?php echo e($page); ?>
                                </span>
                            </span>
                        <?php else: ?>
                            <a href="<?php echo e($url); ?>" class="inline-flex items-center justify-center min-w-12 px-4 py-3 text-sm font-medium text-slate-700 bg-white border-r border-slate-200 hover:bg-slate-50 hover:text-primary transition" aria-label="<?php echo e(__('Go to page :page', ['page' => $page])); ?>">
                                <?php echo e($page); ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if ($paginator->hasMorePages()): ?>
                <a href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" class="inline-flex items-center justify-center px-3 py-3 text-sm font-medium text-slate-600 bg-white hover:bg-slate-50 hover:text-primary transition" aria-label="<?php echo e(__('pagination.next')); ?>">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
            <?php else: ?>
                <span aria-disabled="true" aria-label="<?php echo e(__('pagination.next')); ?>">
                    <span class="inline-flex items-center justify-center px-3 py-3 text-sm font-medium text-gray-400 bg-white cursor-not-allowed" aria-hidden="true">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </span>
            <?php endif; ?>
        </div>
    </nav>
<?php endif; ?>
