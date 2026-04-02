Act as a Senior Laravel Developer. 
I am building an educational web application named `khaitriedu`. The tech stack includes Laravel, PHP, MySQL, Blade Templates, and Tailwind CSS.

Your task is to write the complete code for the "Teacher / Academic Admin" module based on the specific User Flow below. Please generate the code step-by-step (Database -> Models -> Routes -> Controllers -> Views) and ensure best practices.

### USER FLOW & REQUIREMENTS:

1. Authentication & Dashboard
- Role: Teacher
- Dashboard should display: 
  + Today's/This week's teaching schedule.
  + Notifications (e.g., status of schedule change requests).
  + Quick links to their assigned classes.

2. Schedule Management (Lịch giảng dạy)
- Display the schedule in a list or simple calendar view.
- Feature: "Request Schedule Change" (Yêu cầu đổi lịch).
  + The teacher clicks on a specific schedule.
  + Submits a form with: `new_date_time` and `reason`.
  + The request status becomes 'pending' (waiting for Super Admin approval).

3. Class & Student Management (Lớp học của tôi)
- Display a list of classes assigned to the logged-in teacher.
- When clicking on a specific Class, show details with 4 tabs/sections:
  + Tab 1 - Student List: View students enrolled in the class.
  + Tab 2 - Attendance (Điểm danh): Select a class session/date. Mark students as: Present, Excused Absence, Unexcused Absence, Late. Save attendance.
  + Tab 3 - Grades (Bảng điểm): Select a test/exam. Input scores manually for each student. Save grades.
  + Tab 4 - Evaluations (Đánh giá): Select a student. Input text comments and a rating/attitude score. Save evaluation.

### STEP-BY-STEP EXECUTION INSTRUCTIONS:

Please provide the code in the following order:

**Step 1: Database Migrations & Models**
Create or update the migrations and Eloquent Models with relationships for:
- `users` (ensure there's a role or is_teacher flag)
- `classes` (id, name, teacher_id, timestamps)
- `class_user` (pivot table for students in a class)
- `schedules` (id, class_id, teacher_id, start_time, end_time, room)
- `schedule_change_requests` (id, schedule_id, requested_time, reason, status[pending, approved, rejected])
- `attendances` (id, schedule_id, user_id(student), status)
- `grades` (id, class_id, user_id(student), test_name, score)
- `evaluations` (id, class_id, user_id(student), comments, rating)

**Step 2: Routes**
- Define a route group with a `teacher` prefix and `auth` middleware. Include routes for Dashboard, Schedules (view, request change), and Classes (list, view details, attendance, grades, evaluations).

**Step 3: Controllers**
- Generate the necessary Controllers (e.g., `TeacherDashboardController`, `ScheduleController`, `TeacherClassController`) with methods to handle the logic described in the flow. Keep controllers clean and use FormRequests for validation if needed.

**Step 4: Blade Views & UI (using Tailwind CSS)**
- Create a simple Sidebar layout for the Teacher.
- Create the Blade templates for:
  1. Dashboard (`teacher/dashboard.blade.php`)
  2. Schedule List & Change Request Modal (`teacher/schedules/index.blade.php`)
  3. Class List (`teacher/classes/index.blade.php`)
  4. Class Details with Tabs for Students, Attendance, Grades, and Evaluations (`teacher/classes/show.blade.php`). Use Alpine.js or simple vanilla JS for tab switching if necessary, and style with Tailwind CSS.

Please start by generating Step 1 (Migrations and Models). Wait for my confirmation, or continue to generate all if you have enough output context limit.