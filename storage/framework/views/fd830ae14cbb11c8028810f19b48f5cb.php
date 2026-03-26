<?php $__env->startSection('title', 'KhaiTriEdu - Học trực tuyến thông minh'); ?>

<?php $__env->startSection('content'); ?>
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-br from-blue-900 via-blue-700 to-blue-500 text-white overflow-hidden">
        <!-- Animated blob background -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        </div>

        <div class="container mx-auto px-4 py-16 md:py-24 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-8 text-center lg:text-left">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight">
                        KhaiTriEdu
                        <span class="block text-transparent bg-clip-text bg-gradient-to-r from-blue-200 to-white">Học trực tuyến thông minh</span>
                    </h1>
                    <p class="text-xl text-blue-100 max-w-2xl mx-auto lg:mx-0">
                        Nền tảng học tập trực tuyến hàng đầu Việt Nam, mang đến trải nghiệm học tập chất lượng cao với đội ngũ giảng viên giàu kinh nghiệm.
                    </p>
                    <div class="flex flex-wrap gap-4 justify-center lg:justify-start">
                        <?php if(!session('user_id')): ?>
                            <a href="<?php echo e(route('register')); ?>" class="btn px-8 py-4 bg-white text-blue-700 rounded-xl font-semibold shadow-lg hover:bg-blue-50 transition transform hover:scale-105 flex items-center gap-2">
                                <i class="fas fa-rocket"></i> Bắt đầu học ngay
                            </a>
                            <a href="<?php echo e(route('courses.index')); ?>" class="btn px-8 py-4 bg-transparent border-2 border-white text-white rounded-xl font-semibold hover:bg-white/10 transition transform hover:scale-105 flex items-center gap-2">
                                <i class="fas fa-book-open"></i> Xem khóa học
                            </a>
                            <a href="<?php echo e(route('apply-teacher')); ?>" class="btn px-8 py-4 bg-white/20 border border-white text-white rounded-xl font-semibold hover:bg-white/30 transition transform hover:scale-105 flex items-center gap-2">
                                <i class="fas fa-chalkboard-teacher"></i> Ứng tuyển giảng viên
                            </a>
                        <?php else: ?>
                            <a href="<?php echo e(route('dashboard')); ?>" class="btn px-8 py-4 bg-white text-blue-700 rounded-xl font-semibold shadow-lg hover:bg-blue-50 transition transform hover:scale-105 flex items-center gap-2">
                                <i class="fas fa-chalkboard-user"></i> Vào học ngay
                            </a>
                            <a href="<?php echo e(route('courses.index')); ?>" class="btn px-8 py-4 bg-transparent border-2 border-white text-white rounded-xl font-semibold hover:bg-white/10 transition transform hover:scale-105 flex items-center gap-2">
                                <i class="fas fa-search"></i> Khám phá khóa học
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-wrap gap-8 justify-center lg:justify-start pt-8">
                        <div>
                            <div class="text-3xl font-bold"><?php echo e(number_format($studentCount)); ?>+</div>
                            <div class="text-blue-200">Học viên</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold"><?php echo e(number_format($courseCount)); ?>+</div>
                            <div class="text-blue-200">Khóa học</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold"><?php echo e(number_format($teacherCount)); ?>+</div>
                            <div class="text-blue-200">Giảng viên</div>
                        </div>
                    </div>
                </div>
                <div class="hidden lg:block relative">
                    <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80"
                         alt="Học tập trực tuyến"
                         class="rounded-2xl shadow-2xl object-cover w-full h-auto">
                    <div class="absolute -bottom-5 -left-5 bg-white/10 backdrop-blur-md p-4 rounded-xl shadow-lg flex items-center gap-3">
                        <i class="fas fa-play-circle text-white text-3xl"></i>
                        <div>
                            <div class="font-semibold">Giới thiệu về KhaiTri</div>
                            <div class="text-sm text-blue-200">Xem video ngắn</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Section -->
    <div class="container mx-auto px-4 py-16">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Khám phá <span class="text-primary">nhóm ngành</span></h2>
            <p class="text-gray-600 mt-2">Chọn nhóm ngành trước, sau đó tìm khóa học phù hợp với mục tiêu của bạn</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
            <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('courses.index', ['category' => $category->slug])); ?>" class="group">
                    <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 text-center border border-gray-100 group-hover:-translate-y-2">
                        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-primary mx-auto mb-4 group-hover:bg-primary group-hover:text-white transition-colors duration-300">
                            <?php if($category->image_path): ?>
                                <img src="<?php echo e(asset('storage/' . $category->image_path)); ?>" alt="<?php echo e($category->name); ?>" class="w-10 h-10 object-contain">
                            <?php else: ?>
                                <i class="fas fa-th-large text-2xl"></i>
                            <?php endif; ?>
                        </div>
                        <h3 class="font-bold text-gray-800 group-hover:text-primary transition-colors line-clamp-1"><?php echo e($category->name); ?></h3>
                        <div class="mt-2 text-[11px] font-semibold uppercase tracking-wider text-primary">
                            <?php echo e($category->subjects_count); ?> khóa học
                        </div>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full text-center py-8">
                    <p class="text-gray-500 italic">Chưa có nhóm ngành nào được cập nhật.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Features Section -->
    <div class="container mx-auto px-4 py-20">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Tại sao chọn <span class="text-primary">KhaiTriEdu</span>?</h2>
            <p class="text-gray-600 mt-2">Trải nghiệm học tập toàn diện với đội ngũ giảng viên tận tâm và lộ trình cá nhân hóa</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="card bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 text-center">
                <div class="w-20 h-20 bg-primary-light/20 rounded-2xl flex items-center justify-center text-primary mx-auto mb-6">
                    <i class="fas fa-chalkboard-user text-4xl"></i>
                </div>
                <h3 class="text-2xl font-semibold text-primary-dark mb-3">Giảng viên thân thiện</h3>
                <p class="text-gray-600">Đội ngũ giảng viên giàu kinh nghiệm, luôn sẵn sàng hỗ trợ, hướng dẫn chi tiết từng học viên. Phong cách dễ thương, gần gũi, tạo cảm hứng học tập.</p>
            </div>
            <div class="card bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 text-center">
                <div class="w-20 h-20 bg-primary-light/20 rounded-2xl flex items-center justify-center text-primary mx-auto mb-6">
                    <i class="fas fa-user-graduate text-4xl"></i>
                </div>
                <h3 class="text-2xl font-semibold text-primary-dark mb-3">Học viên được hỗ trợ tận tình</h3>
                <p class="text-gray-600">Mỗi học viên đều có lộ trình học riêng, được kèm cặp sát sao, giải đáp thắc mắc 24/7 qua hệ thống chat và lớp học trực tuyến.</p>
            </div>
            <div class="card bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 text-center">
                <div class="w-20 h-20 bg-primary-light/20 rounded-2xl flex items-center justify-center text-primary mx-auto mb-6">
                    <i class="fas fa-certificate text-4xl"></i>
                </div>
                <h3 class="text-2xl font-semibold text-primary-dark mb-3">Chất lượng đào tạo vượt trội</h3>
                <p class="text-gray-600">Nội dung bài giảng cập nhật liên tục, bài tập thực hành sát thực tế, cam kết đầu ra rõ ràng và chứng chỉ có giá trị.</p>
            </div>
        </div>
    </div>

    <!-- Featured Courses -->
    <div class="bg-gray-50 py-20">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Khóa học <span class="text-primary">nổi bật</span></h2>
                <div class="w-24 h-1 bg-primary mx-auto mt-4 rounded-full"></div>
                <p class="text-gray-600 mt-4">Những khóa học được quan tâm nhiều và đang được admin xếp lớp linh hoạt</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php $__empty_1 = true; $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-primary via-blue-500 to-primary rounded-2xl opacity-0 group-hover:opacity-100 transition duration-300 blur-lg"></div>

                        <div class="relative card bg-white rounded-2xl shadow-lg group-hover:shadow-2xl transition-all duration-300 overflow-hidden h-full">
                            <div class="relative overflow-hidden h-56 bg-gradient-to-br from-blue-100 to-sky-100">
                                <img src="<?php echo e($course->image_url ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'); ?>"
                                     alt="<?php echo e($course->name); ?>"
                                     class="w-full h-full object-cover group-hover:scale-110 transition duration-300">

                                <div class="absolute top-3 left-3 bg-gradient-to-r from-primary to-primary-dark text-white text-xs font-bold px-4 py-1.5 rounded-full shadow-lg">
                                    <?php echo e($course->category?->name ?? 'Khóa học'); ?>

                                </div>

                                <div class="absolute top-3 right-3 bg-white/90 text-primary text-xs font-bold px-4 py-1.5 rounded-full shadow-lg">
                                    <?php echo e(($course->courses_count ?? 0) > 0 ? ($course->courses_count . ' lớp mở') : 'Chờ xếp lớp'); ?>

                                </div>
                            </div>

                            <div class="p-6">
                                <h4 class="text-lg font-bold mb-2 line-clamp-2 text-gray-800 group-hover:text-primary transition"><?php echo e($course->name); ?></h4>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?php echo e($course->description ?? 'Khóa học được thiết kế để admin xếp lớp theo lịch phù hợp của học viên.'); ?></p>

                                <div class="grid grid-cols-2 gap-3 mb-4 pb-4 border-b">
                                    <div class="text-center rounded-xl bg-blue-50 p-3">
                                        <div class="text-lg font-bold text-primary"><?php echo e($course->enrollments_count ?? 0); ?></div>
                                        <div class="text-xs text-gray-500">Đăng ký</div>
                                    </div>
                                    <div class="text-center rounded-xl bg-slate-50 p-3">
                                        <div class="text-lg font-bold text-primary"><?php echo e($course->courses_count ?? 0); ?></div>
                                        <div class="text-xs text-gray-500">Lớp hiện có</div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <span class="text-xs text-gray-500">Học phí tham khảo</span>
                                        <div class="text-xl font-black text-transparent bg-clip-text bg-gradient-to-r from-primary to-primary-dark">
                                            <?php echo e(number_format($course->price ?? 0, 0, ',', '.')); ?>đ
                                        </div>
                                    </div>
                                    <a href="<?php echo e(route('khoa-hoc.show', $course->id)); ?>" class="flex-1 text-center bg-gradient-to-r from-primary to-primary-dark hover:from-primary-dark hover:to-primary text-white py-2 rounded-lg font-bold transition transform hover:scale-105 shadow-lg flex items-center justify-center gap-2 text-sm">
                                        <span>Xem</span>
                                        <i class="fas fa-arrow-right text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-span-3 text-center py-12">
                        <p class="text-gray-600">Chưa có khóa học nào. Quay lại sau nhé!</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="text-center mt-12">
                <a href="<?php echo e(route('courses.index')); ?>" class="inline-flex items-center gap-2 text-primary font-semibold hover:gap-3 transition-all border-b-2 border-primary pb-1">
                    Xem tất cả khóa học <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Team Section -->
    <div class="container mx-auto px-4 py-20">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Đội ngũ <span class="text-primary">giảng viên</span></h2>
            <div class="w-24 h-1 bg-primary mx-auto mt-4 rounded-full"></div>
            <p class="text-gray-600 mt-4">Những chuyên gia hàng đầu, cam kết truyền đạt kiến thức chất lượng cao</p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php $__empty_1 = true; $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    preg_match('/\((.+)\)/', $teacher->name, $matches);
                    $field = $matches[1] ?? 'Giảng viên chuyên nghiệp';
                ?>
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition group">
                    <div class="relative overflow-hidden h-64 bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center">
                        <img src="https://randomuser.me/api/portraits/men/<?php echo e($loop->index + 10); ?>.jpg" alt="Teacher" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                    </div>
                    <div class="p-6 text-center">
                        <h3 class="text-xl font-bold text-gray-800 mb-1"><?php echo e($teacher->name); ?></h3>
                        <p class="text-primary font-semibold mb-3"><?php echo e($field); ?></p>
                        <p class="text-gray-600 text-sm mb-4">Hơn 8 năm kinh nghiệm, đã đào tạo hơn 500+ học viên.</p>
                        <div class="flex justify-center gap-2">
                            <a href="#" class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center text-sm hover:bg-primary hover:text-white transition">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center text-sm hover:bg-primary hover:text-white transition">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center text-sm hover:bg-primary hover:text-white transition">
                                <i class="fab fa-linkedin"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-4 text-center py-12 bg-white rounded-2xl shadow-md">
                    <p class="text-gray-600">Chưa có giảng viên nào được đăng ký.</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-12">
            <a href="<?php echo e(route('teachers')); ?>" class="inline-flex items-center gap-2 text-primary font-semibold hover:gap-3 transition-all border-b-2 border-primary pb-1">
                Xem tất cả giảng viên <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Stats Counter -->
    <div class="bg-gradient-to-r from-blue-900 to-blue-700 py-16 text-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center" x-data="{
                counters: {
                    students: <?php echo e($studentCount); ?>,
                    courses: <?php echo e($courseCount); ?>,
                    teachers: <?php echo e($teacherCount); ?>,
                    satisfaction: 98
                },
                init() {
                    for (let key in this.counters) {
                        let target = this.counters[key];
                        let current = 0;
                        let increment = target / 30;
                        let interval = setInterval(() => {
                            current += increment;
                            if (current >= target) {
                                current = target;
                                clearInterval(interval);
                            }
                            this.counters[key] = Math.floor(current);
                        }, 50);
                    }
                }
            }">
                <div>
                    <i class="fas fa-users text-4xl mb-3"></i>
                    <div class="text-4xl font-bold" x-text="counters.students">0</div>
                    <div class="text-blue-200">Học viên đã đăng ký</div>
                </div>
                <div>
                    <i class="fas fa-book-open text-4xl mb-3"></i>
                    <div class="text-4xl font-bold" x-text="counters.courses">0</div>
                    <div class="text-blue-200">Khóa học</div>
                </div>
                <div>
                    <i class="fas fa-chalkboard-user text-4xl mb-3"></i>
                    <div class="text-4xl font-bold" x-text="counters.teachers">0</div>
                    <div class="text-blue-200">Giảng viên</div>
                </div>
                <div>
                    <i class="fas fa-smile text-4xl mb-3"></i>
                    <div class="text-4xl font-bold" x-text="counters.satisfaction">0</div>
                    <div class="text-blue-200">Tỷ lệ hài lòng</div>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works -->
    <div class="container mx-auto px-4 py-20">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Cách <span class="text-primary">hoạt động</span></h2>
            <p class="text-gray-600 mt-2">Chỉ 3 bước đơn giản để bắt đầu hành trình học tập</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8 relative">
            <!-- Connecting lines (desktop) -->
            <div class="hidden md:block absolute top-1/2 left-0 w-full h-0.5 bg-primary/30 -translate-y-1/2 z-0"></div>
            <div class="relative z-10 text-center">
                <div class="w-20 h-20 bg-primary text-white rounded-full flex items-center justify-center text-3xl font-bold mx-auto mb-6 shadow-lg">1</div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Đăng ký tài khoản</h3>
                <p class="text-gray-600">Tạo tài khoản miễn phí và xác thực email chỉ trong 1 phút.</p>
            </div>
            <div class="relative z-10 text-center">
                <div class="w-20 h-20 bg-primary text-white rounded-full flex items-center justify-center text-3xl font-bold mx-auto mb-6 shadow-lg">2</div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Chọn khóa học</h3>
                <p class="text-gray-600">Khám phá hàng trăm khóa học phù hợp với mục tiêu của bạn.</p>
            </div>
            <div class="relative z-10 text-center">
                <div class="w-20 h-20 bg-primary text-white rounded-full flex items-center justify-center text-3xl font-bold mx-auto mb-6 shadow-lg">3</div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Học và nhận chứng chỉ</h3>
                <p class="text-gray-600">Hoàn thành khóa học, nhận chứng chỉ có giá trị.</p>
            </div>
        </div>
    </div>

    <!-- Testimonials -->
    <div class="bg-blue-50 py-20">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Học viên <span class="text-primary">nói gì</span></h2>
                <p class="text-gray-600 mt-2">Những phản hồi thực tế từ cộng đồng KhaiTriEdu</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition">
                    <div class="flex items-center gap-4 mb-4">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="avatar" class="w-14 h-14 rounded-full object-cover">
                        <div>
                            <h5 class="font-semibold">Nguyễn Thị Lan</h5>
                            <div class="flex text-yellow-400 text-sm">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">"Khóa học rất dễ hiểu, giảng viên nhiệt tình. Tôi đã có thể xây dựng website đầu tay sau 2 tháng."</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition">
                    <div class="flex items-center gap-4 mb-4">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="avatar" class="w-14 h-14 rounded-full object-cover">
                        <div>
                            <h5 class="font-semibold">Trần Văn Nam</h5>
                            <div class="flex text-yellow-400 text-sm">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">"Nội dung cập nhật, bài tập thực hành sát thực tế. Hỗ trợ rất tốt từ cộng đồng."</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition">
                    <div class="flex items-center gap-4 mb-4">
                        <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="avatar" class="w-14 h-14 rounded-full object-cover">
                        <div>
                            <h5 class="font-semibold">Lê Thị Hoa</h5>
                            <div class="flex text-yellow-400 text-sm">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">"Giao diện đẹp, dễ sử dụng. Tôi rất thích tính năng theo dõi tiến độ học tập."</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="container mx-auto px-4 py-20">
        <div class="bg-gradient-to-r from-blue-900 to-blue-700 rounded-3xl p-12 text-center relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" viewBox="0 0 1000 1000" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="100" cy="100" r="80" fill="white"/>
                    <circle cx="900" cy="800" r="120" fill="white"/>
                    <circle cx="700" cy="200" r="60" fill="white"/>
                </svg>
            </div>
            <div class="relative z-10">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Sẵn sàng để chinh phục tri thức?</h2>
                <p class="text-xl text-blue-100 mb-8">Đăng ký ngay hôm nay để nhận ưu đãi đặc biệt cho học viên mới.</p>
                <a href="<?php echo e(route('register')); ?>" class="inline-flex items-center gap-2 bg-white text-primary px-8 py-4 rounded-xl font-semibold text-lg shadow-lg hover:bg-gray-100 transition transform hover:scale-105">
                    <i class="fas fa-user-plus"></i> Đăng ký miễn phí
                </a>
            </div>
        </div>
    </div>

    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
    </style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XXamp\htdocs\khaitriedu\resources\views\home.blade.php ENDPATH**/ ?>