<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseCurriculumService
{
    public function syncCourses(iterable $courses): array
    {
        $report = [
            'courses_processed' => 0,
            'modules_created' => 0,
            'modules_updated' => 0,
            'modules_deleted' => 0,
            'lessons_created' => 0,
        ];

        foreach ($courses as $course) {
            $courseReport = $this->syncCourse($course);

            $report['courses_processed']++;
            $report['modules_created'] += $courseReport['modules_created'];
            $report['modules_updated'] += $courseReport['modules_updated'];
            $report['modules_deleted'] += $courseReport['modules_deleted'];
            $report['lessons_created'] += $courseReport['lessons_created'];
        }

        return $report;
    }

    public function syncCourse(Course $course): array
    {
        $course->loadMissing(['subject.category', 'modules.lessons']);

        $templates = $this->curriculumFor($course);
        $modules = $course->modules->sortBy('position')->values();
        $placeholderModules = $modules->filter(fn (Module $module) => $this->isPlaceholderModule($module))->values();
        $allPlaceholder = $modules->isNotEmpty() && $placeholderModules->count() === $modules->count();

        return DB::transaction(function () use ($course, $templates, $modules, $placeholderModules, $allPlaceholder) {
            if ($modules->isEmpty() || $allPlaceholder) {
                return $this->rebuildCourseCurriculum($course, $templates, $modules);
            }

            return $this->fillMissingCourseCurriculum($course, $templates, $modules, $placeholderModules);
        });
    }

    public function curriculumFor(Course $course): array
    {
        $signature = $this->signatureFor($course);

        if ($this->containsAny($signature, ['anh van']) && $this->containsAny($signature, ['khung 6 bac'])) {
            return $this->themeCurriculum($course, [
                [
                    'title' => 'Listening',
                    'sessions' => 5,
                    'content' => 'Luyện nghe theo chủ đề, nhận diện âm và phản xạ ngắn theo khung 6 bậc.',
                    'lesson_topics' => [
                        'Phonics & sounds',
                        'Greetings & introductions',
                        'Numbers, time and dates',
                        'Short conversations',
                        'Level review',
                    ],
                ],
                [
                    'title' => 'Speaking',
                    'sessions' => 4,
                    'content' => 'Rèn phát âm, ngữ điệu và phản xạ giao tiếp trước khi kiểm tra đầu ra.',
                    'lesson_topics' => [
                        'Pronunciation drills',
                        'Question & answer',
                        'Role-play',
                        'Topic presentation',
                    ],
                ],
                [
                    'title' => 'Reading',
                    'sessions' => 4,
                    'content' => 'Luyện đọc hiểu ngắn, tìm ý chính và xử lý câu hỏi theo chuẩn đầu ra.',
                    'lesson_topics' => [
                        'Short notices',
                        'Short paragraphs',
                        'Main idea & detail',
                        'Comprehension test',
                    ],
                ],
                [
                    'title' => 'Writing',
                    'sessions' => 4,
                    'content' => 'Thực hành viết câu, đoạn ngắn và sửa lỗi ngữ pháp cơ bản.',
                    'lesson_topics' => [
                        'Sentence patterns',
                        'Guided paragraphs',
                        'Email/message writing',
                        'Writing review',
                    ],
                ],
                [
                    'title' => 'Grammar & Vocabulary',
                    'sessions' => 3,
                    'content' => 'Hệ thống lại ngữ pháp trọng tâm và từ vựng theo chủ đề của khóa.',
                    'lesson_topics' => [
                        'Tenses and structures',
                        'Question forms',
                        'Core vocabulary by topic',
                    ],
                ],
                [
                    'title' => 'Mock Test & Review',
                    'sessions' => 2,
                    'content' => 'Làm bài kiểm tra mô phỏng, chữa lỗi và chốt kỹ năng cần cải thiện.',
                    'lesson_topics' => [
                        'Practice test',
                        'Feedback and correction',
                    ],
                ],
            ]);
        }

        if ($this->containsAny($signature, ['anh van']) && $this->containsAny($signature, ['thieu nhi'])) {
            return $this->themeCurriculum($course, [
                [
                    'title' => 'Làm quen tiếng Anh',
                    'sessions' => 5,
                    'content' => 'Học viên làm quen âm, chữ cái, phát âm và các mẫu câu chào hỏi cơ bản.',
                    'lesson_topics' => [
                        'Alphabet & sounds',
                        'Greetings',
                        'Numbers and colors',
                        'Simple classroom language',
                        'Mini review',
                    ],
                ],
                [
                    'title' => 'Từ vựng và mẫu câu',
                    'sessions' => 5,
                    'content' => 'Xây nền tảng từ vựng theo chủ đề, mẫu câu ngắn và phản xạ giao tiếp ban đầu.',
                    'lesson_topics' => [
                        'Family and friends',
                        'School things',
                        'Daily routines',
                        'Food and drinks',
                        'Topic review',
                    ],
                ],
                [
                    'title' => 'Nghe - nói tương tác',
                    'sessions' => 4,
                    'content' => 'Rèn nghe hiểu chỉ dẫn ngắn, hỏi đáp đơn giản và giao tiếp qua trò chơi, bài hát, hội thoại.',
                    'lesson_topics' => [
                        'Listen and repeat',
                        'Short dialogues',
                        'Speaking games',
                        'Role-play',
                    ],
                ],
                [
                    'title' => 'Đọc - viết cơ bản',
                    'sessions' => 4,
                    'content' => 'Thực hành đọc hiểu ngắn, viết từ đơn, câu đơn và mô tả rất ngắn.',
                    'lesson_topics' => [
                        'Read short words',
                        'Read short texts',
                        'Write simple sentences',
                        'Writing review',
                    ],
                ],
                [
                    'title' => 'Ôn tập và đánh giá',
                    'sessions' => 2,
                    'content' => 'Tổng hợp kiến thức, trò chơi ngôn ngữ và kiểm tra cuối khóa.',
                    'lesson_topics' => [
                        'Revision',
                        'Final check',
                    ],
                ],
            ]);
        }

        if ($this->containsAny($signature, ['anh van'])) {
            return $this->themeCurriculum($course, [
                [
                    'title' => 'Listening',
                    'sessions' => 5,
                    'content' => 'Luyện nghe hội thoại, bắt ý chính và tăng tốc độ phản xạ.',
                    'lesson_topics' => [
                        'Everyday listening',
                        'Daily conversation',
                        'Numbers and time',
                        'Directions and instructions',
                        'Mini test',
                    ],
                ],
                [
                    'title' => 'Speaking',
                    'sessions' => 4,
                    'content' => 'Phát triển phát âm, ngữ điệu và khả năng diễn đạt ý tưởng mạch lạc hơn.',
                    'lesson_topics' => [
                        'Pronunciation',
                        'Speaking in pairs',
                        'Short presentation',
                        'Role-play',
                    ],
                ],
                [
                    'title' => 'Reading',
                    'sessions' => 4,
                    'content' => 'Đọc hiểu đoạn văn, tìm từ khóa và xử lý câu hỏi đọc hiểu.',
                    'lesson_topics' => [
                        'Short texts',
                        'Information scanning',
                        'Reading for detail',
                        'Review',
                    ],
                ],
                [
                    'title' => 'Writing',
                    'sessions' => 4,
                    'content' => 'Viết câu, đoạn ngắn và sửa lỗi ngữ pháp trong bài viết.',
                    'lesson_topics' => [
                        'Sentence writing',
                        'Paragraph building',
                        'Form filling',
                        'Writing review',
                    ],
                ],
                [
                    'title' => 'Grammar & Vocabulary',
                    'sessions' => 3,
                    'content' => 'Hệ thống hóa ngữ pháp trọng tâm và từ vựng theo chủ đề.',
                    'lesson_topics' => [
                        'Sentence patterns',
                        'Common grammar points',
                        'Topic vocabulary',
                    ],
                ],
                [
                    'title' => 'Mock Test & Review',
                    'sessions' => 2,
                    'content' => 'Luyện đề, tổng ôn và đánh giá năng lực cuối khóa.',
                    'lesson_topics' => [
                        'Mock test',
                        'Correction session',
                    ],
                ],
            ]);
        }

        if ($this->containsAny($signature, ['tin hoc']) && $this->containsAny($signature, ['thieu nhi'])) {
            return $this->themeCurriculum($course, [
                [
                    'title' => 'Nhập môn máy tính',
                    'sessions' => 4,
                    'content' => 'Làm quen thiết bị, phần mềm và thao tác máy tính an toàn.',
                    'lesson_topics' => [
                        'Computer basics',
                        'Turning on and off safely',
                        'Desktop and icons',
                        'Practice time',
                    ],
                ],
                [
                    'title' => 'Bàn phím, chuột và gõ chữ',
                    'sessions' => 4,
                    'content' => 'Rèn thao tác chuột, bàn phím và gõ chữ hiệu quả.',
                    'lesson_topics' => [
                        'Mouse control',
                        'Keyboard basics',
                        'Typing practice',
                        'Mini game',
                    ],
                ],
                [
                    'title' => 'Vẽ và sáng tạo',
                    'sessions' => 4,
                    'content' => 'Tạo hình, tô màu và phát triển tư duy sáng tạo với phần mềm trực quan.',
                    'lesson_topics' => [
                        'Drawing tools',
                        'Coloring',
                        'Sticker and shapes',
                        'Creative project',
                    ],
                ],
                [
                    'title' => 'Internet an toàn',
                    'sessions' => 3,
                    'content' => 'Học cách tìm kiếm thông tin và sử dụng internet đúng cách.',
                    'lesson_topics' => [
                        'Safe browsing',
                        'Search basics',
                        'Protecting personal data',
                    ],
                ],
                [
                    'title' => 'Dự án cuối khóa',
                    'sessions' => 2,
                    'content' => 'Hoàn thiện một sản phẩm nhỏ để tổng kết kỹ năng đã học.',
                    'lesson_topics' => [
                        'Project setup',
                        'Final showcase',
                    ],
                ],
            ]);
        }

        if ($this->containsAny($signature, ['tin hoc']) && $this->containsAny($signature, ['van phong'])) {
            return $this->themeCurriculum($course, [
                [
                    'title' => 'Windows & File Management',
                    'sessions' => 4,
                    'content' => 'Quản lý hệ điều hành, thư mục, tệp tin và thao tác máy tính hằng ngày.',
                    'lesson_topics' => [
                        'Desktop and windows',
                        'Folders and files',
                        'Shortcuts and tools',
                        'Practice',
                    ],
                ],
                [
                    'title' => 'Word',
                    'sessions' => 4,
                    'content' => 'Rèn kỹ năng soạn thảo, định dạng văn bản và biểu mẫu hành chính.',
                    'lesson_topics' => [
                        'Document setup',
                        'Formatting text',
                        'Tables and lists',
                        'Template practice',
                    ],
                ],
                [
                    'title' => 'Excel',
                    'sessions' => 5,
                    'content' => 'Học công thức, hàm và xử lý dữ liệu phục vụ công việc văn phòng.',
                    'lesson_topics' => [
                        'Workbook basics',
                        'Formulas and functions',
                        'Data sorting and filtering',
                        'Charts and reports',
                        'Practice file',
                    ],
                ],
                [
                    'title' => 'PowerPoint',
                    'sessions' => 4,
                    'content' => 'Thiết kế slide, trình bày nội dung và thuyết trình bằng PowerPoint.',
                    'lesson_topics' => [
                        'Slide layout',
                        'Visual design',
                        'Animation and transitions',
                        'Presentation practice',
                    ],
                ],
                [
                    'title' => 'Email & Internet',
                    'sessions' => 3,
                    'content' => 'Sử dụng email, lưu trữ đám mây và công cụ trực tuyến để làm việc hiệu quả.',
                    'lesson_topics' => [
                        'Email etiquette',
                        'Cloud storage',
                        'Online collaboration',
                    ],
                ],
                [
                    'title' => 'Practice Project',
                    'sessions' => 2,
                    'content' => 'Thực hiện bài tập tổng hợp và bài kiểm tra đầu ra cho toàn khóa.',
                    'lesson_topics' => [
                        'Mini project',
                        'Final review',
                    ],
                ],
            ]);
        }

        if ($this->containsAny($signature, ['ung dung cong nghe thong tin'])) {
            return $this->themeCurriculum($course, [
                [
                    'title' => 'Kỹ năng số nền tảng',
                    'sessions' => 4,
                    'content' => 'Xây dựng tư duy số và thao tác hệ thống an toàn.',
                    'lesson_topics' => ['Digital basics', 'Operating systems', 'Shortcuts', 'Practice'],
                ],
                [
                    'title' => 'Công cụ làm việc số',
                    'sessions' => 4,
                    'content' => 'Ứng dụng các công cụ xử lý văn bản, bảng tính và trình chiếu.',
                    'lesson_topics' => ['Text tools', 'Spreadsheet tools', 'Presentation tools', 'Practice'],
                ],
                [
                    'title' => 'Lưu trữ và cộng tác',
                    'sessions' => 4,
                    'content' => 'Quản lý dữ liệu đám mây, chia sẻ tài liệu và cộng tác nhóm.',
                    'lesson_topics' => ['Cloud storage', 'Sharing files', 'Team workflow', 'Practice'],
                ],
                [
                    'title' => 'Bảo mật và dữ liệu',
                    'sessions' => 3,
                    'content' => 'Nhận biết rủi ro và bảo vệ tài khoản cá nhân.',
                    'lesson_topics' => ['Password safety', 'Data protection', 'Risk review'],
                ],
                [
                    'title' => 'Dự án ứng dụng',
                    'sessions' => 3,
                    'content' => 'Hoàn thiện bài tập thực tế gắn với công việc và học tập.',
                    'lesson_topics' => ['Project plan', 'Build and test', 'Final review'],
                ],
            ]);
        }

        if ($this->containsAny($signature, ['ke toan', 'bao cao thue'])) {
            return $this->themeCurriculum($course, [
                ['title' => 'Nhập môn kế toán', 'sessions' => 4, 'content' => 'Làm quen hệ thống tài khoản, chứng từ và quy trình cơ bản.'],
                ['title' => 'Chứng từ và hạch toán', 'sessions' => 4, 'content' => 'Thực hành ghi nhận nghiệp vụ, công nợ và bút toán thông dụng.'],
                ['title' => 'Sổ sách và báo cáo', 'sessions' => 4, 'content' => 'Lập sổ, kiểm tra số liệu và hoàn thiện báo cáo kế toán.'],
                ['title' => 'Thuế và quyết toán', 'sessions' => 4, 'content' => 'Xử lý dữ liệu, lập tờ khai và đối chiếu cuối kỳ.'],
                ['title' => 'Thực hành tổng hợp', 'sessions' => 2, 'content' => 'Hoàn thiện bộ hồ sơ trên dữ liệu mô phỏng.'],
            ]);
        }

        if ($this->containsAny($signature, ['dien lanh', 'dien dan dung', 'may dan dung', 'sua chua', 'lap rap may tinh', 'cham soc da', 'thiet ke do hoa', 'pha che', 'che bien mon an'])) {
            return $this->themeCurriculum($course, [
                ['title' => 'Nền tảng nghề', 'sessions' => 4, 'content' => 'Hiểu vật tư, quy trình và nguyên tắc an toàn của nghề.'],
                ['title' => 'Kỹ thuật cốt lõi', 'sessions' => 4, 'content' => 'Thực hành thao tác, kỹ thuật và quy trình chuyên môn.'],
                ['title' => 'Thực hành chuyên đề', 'sessions' => 4, 'content' => 'Luyện tay nghề với các tình huống và bài tập thực tế.'],
                ['title' => 'Xử lý tình huống', 'sessions' => 4, 'content' => 'Giải quyết lỗi thường gặp và tiêu chuẩn chất lượng đầu ra.'],
                ['title' => 'Tổng kết tay nghề', 'sessions' => 2, 'content' => 'Ôn tập và đánh giá thực hành cuối khóa.'],
            ]);
        }

        if ($this->containsAny($signature, ['boi duong', 'nghiep vu su pham', 'van thu luu tru', 'mam non', 'chuc danh', 'bat dong san', 'dau thau'])) {
            return $this->themeCurriculum($course, [
                ['title' => 'Cơ sở nền tảng', 'sessions' => 4, 'content' => 'Củng cố lý thuyết, thuật ngữ và khung kiến thức chung của khóa học.'],
                ['title' => 'Khung quy định', 'sessions' => 4, 'content' => 'Nắm quy trình, văn bản và yêu cầu nghiệp vụ liên quan.'],
                ['title' => 'Phương pháp ứng dụng', 'sessions' => 4, 'content' => 'Áp dụng kiến thức vào tình huống, hồ sơ và bài tập thực tế.'],
                ['title' => 'Thực hành tình huống', 'sessions' => 3, 'content' => 'Xử lý case study và hoàn thiện bài tập theo chuẩn đầu ra.'],
                ['title' => 'Tổng kết và đánh giá', 'sessions' => 2, 'content' => 'Ôn tập, phản hồi và kiểm tra cuối khóa.'],
            ]);
        }

        if ($this->containsAny($signature, ['trung cap', 'lien thong', 'thac si', 'cao hoc', 'dai hoc'])) {
            return $this->themeCurriculum($course, [
                ['title' => 'Củng cố nền tảng', 'sessions' => 4, 'content' => 'Ôn lại kiến thức cốt lõi cho lộ trình học tập dài hạn.'],
                ['title' => 'Học phần chuyên sâu', 'sessions' => 4, 'content' => 'Đi sâu vào các học phần trọng tâm của chương trình.'],
                ['title' => 'Thực hành ứng dụng', 'sessions' => 4, 'content' => 'Làm bài tập, case study và ứng dụng vào thực tế.'],
                ['title' => 'Đánh giá đầu ra', 'sessions' => 3, 'content' => 'Rà soát, luyện tập và chuẩn bị bài kiểm tra kết thúc.'],
                ['title' => 'Tổng kết khóa', 'sessions' => 2, 'content' => 'Ôn tập và hoàn thiện hồ sơ cuối khóa.'],
            ]);
        }

        return $this->themeCurriculum($course, [
            ['title' => 'Tổng quan khóa học', 'sessions' => 4, 'content' => 'Giới thiệu mục tiêu, cấu trúc và yêu cầu đầu ra của khóa học.'],
            ['title' => 'Kiến thức nền tảng', 'sessions' => 4, 'content' => 'Xây nền tảng trước khi bước vào phần chuyên sâu.'],
            ['title' => 'Thực hành chuyên đề', 'sessions' => 4, 'content' => 'Luyện tập tình huống và bài tập ứng dụng.'],
            ['title' => 'Tổng hợp và đánh giá', 'sessions' => 2, 'content' => 'Ôn tập, phản hồi và kiểm tra đầu ra.'],
        ]);
    }

    protected function rebuildCourseCurriculum(Course $course, array $templates, Collection $modules): array
    {
        $report = [
            'modules_created' => 0,
            'modules_updated' => 0,
            'modules_deleted' => 0,
            'lessons_created' => 0,
        ];

        foreach ($templates as $index => $template) {
            $position = $index + 1;
            $module = $modules->get($index);

            if ($module) {
                $report['lessons_created'] += $this->applyTemplateToModule($module, $template, $position);
                $report['modules_updated']++;
                continue;
            }

            $report['lessons_created'] += $this->createModuleFromTemplate($course, $template, $position);
            $report['modules_created']++;
        }

        if ($modules->count() > count($templates)) {
            $modules->slice(count($templates))->each(function (Module $module) use (&$report): void {
                $module->delete();
                $report['modules_deleted']++;
            });
        }

        return $report;
    }

    protected function fillMissingCourseCurriculum(Course $course, array $templates, Collection $modules, Collection $placeholderModules): array
    {
        $report = [
            'modules_created' => 0,
            'modules_updated' => 0,
            'modules_deleted' => 0,
            'lessons_created' => 0,
        ];

        $placeholderCursor = 0;
        $nextPosition = max(1, (int) $modules->max('position') + 1);

        foreach ($templates as $template) {
            $templateKey = $this->moduleKey($template['title']);

            if ($modules->contains(fn (Module $module) => $this->moduleKey($module->title) === $templateKey)) {
                continue;
            }

            $placeholder = $placeholderModules->get($placeholderCursor);

            if ($placeholder) {
                $report['lessons_created'] += $this->applyTemplateToModule($placeholder, $template, (int) ($placeholder->position ?: $nextPosition));
                $report['modules_updated']++;
                $placeholderCursor++;
                continue;
            }

            $report['lessons_created'] += $this->createModuleFromTemplate($course, $template, $nextPosition++);
            $report['modules_created']++;
        }

        return $report;
    }

    protected function applyTemplateToModule(Module $module, array $template, int $position): int
    {
        $module->update([
            'title' => $template['title'],
            'content' => $template['content'],
            'session_count' => $template['session_count'],
            'duration' => $template['duration'],
            'status' => $template['status'] ?? Module::STATUS_PUBLISHED,
            'position' => $position,
        ]);

        $module->lessons()->delete();

        return $this->createLessonsForModule($module, $template);
    }

    protected function createModuleFromTemplate(Course $course, array $template, int $position): int
    {
        $module = $course->modules()->create([
            'title' => $template['title'],
            'content' => $template['content'],
            'session_count' => $template['session_count'],
            'duration' => $template['duration'],
            'status' => $template['status'] ?? Module::STATUS_PUBLISHED,
            'position' => $position,
        ]);

        return $this->createLessonsForModule($module, $template);
    }

    protected function createLessonsForModule(Module $module, array $template): int
    {
        $count = (int) $template['session_count'];
        $lessonDuration = (int) ($template['lesson_duration'] ?? 45);
        $topics = $template['lesson_topics'] ?? [];

        for ($i = 1; $i <= $count; $i++) {
            $topic = $topics[$i - 1] ?? $this->defaultLessonTopic($i);

            $module->lessons()->create([
                'title' => sprintf('Buổi %d: %s - %s', $i, $module->title, $topic),
                'description' => sprintf('Buổi %d thuộc module %s.', $i, $module->title),
                'content' => sprintf('Nội dung buổi %d tập trung vào %s của module %s.', $i, $topic, $module->title),
                'order' => $i,
                'duration' => $lessonDuration,
            ]);
        }

        return $count;
    }

    protected function defaultLessonTopic(int $index): string
    {
        return match ($index) {
            1 => 'Nhập môn',
            2 => 'Kiến thức nền tảng',
            3 => 'Thực hành 1',
            4 => 'Thực hành 2',
            5 => 'Củng cố',
            6 => 'Ôn tập',
            7 => 'Đánh giá',
            default => 'Mở rộng',
        };
    }

    protected function isPlaceholderModule(Module $module): bool
    {
        $title = $this->moduleKey($module->title);

        return Str::startsWith($title, 'module ') && Str::contains($title, ' cua ');
    }

    protected function signatureFor(Course $course): string
    {
        return $this->moduleKey(collect([
            $course->title,
            $course->subject?->name,
            $course->subject?->category?->name,
        ])->filter()->implode(' '));
    }

    protected function moduleKey(string $value): string
    {
        return Str::squish(Str::lower(Str::ascii($value)));
    }

    protected function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && Str::contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    protected function themeCurriculum(Course $course, array $items): array
    {
        $courseTitle = trim((string) $course->title);
        $modules = [];

        foreach ($items as $item) {
            $modules[] = $this->module(
                $item['title'],
                $item['content'] ?? sprintf('Học viên nắm vững %s trong khóa %s.', $item['title'], $courseTitle),
                $item['sessions'] ?? 4,
                $item['duration'] ?? 60,
                $item['lesson_topics'] ?? [],
                $item['status'] ?? Module::STATUS_PUBLISHED
            );
        }

        return $modules;
    }

    protected function module(string $title, string $content, int $sessionCount, int $duration = 60, array $lessonTopics = [], ?string $status = null): array
    {
        return [
            'title' => $title,
            'content' => $content,
            'session_count' => $sessionCount,
            'duration' => $duration,
            'lesson_topics' => $lessonTopics,
            'status' => $status ?? Module::STATUS_PUBLISHED,
        ];
    }
}
