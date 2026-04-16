# KhaiTriEdu

KhaiTriEdu is a Laravel 12 education management system for a Vietnamese training center.

The project supports the full demo flow used in the thesis: public course browsing, student enrollment, schedule conflict warnings, class scheduling, quizzes, certificates, teacher applications, and separate dashboards for `admin`, `teacher`, and `student`.

## Overview

The application is organized around real operational flows rather than a generic Laravel starter:

- Public pages for courses, teachers, announcements, contact, help, terms, and privacy
- Role-based portals for admin, teacher, and student
- Subject, course, class, room, and schedule management
- Fixed-class enrollment and custom schedule requests
- Schedule conflict detection before and during registration
- Attendance, grades, evaluations, quizzes, and certificates
- Teacher application review and account activation
- Dashboard summaries and operational reports for demo/review

## Core Modules

- `routes/cong_khai.php` - public website, auth, and certificates
- `routes/quan_tri.php` - admin portal
- `routes/giao_vien.php` - teacher portal
- `routes/hoc_vien.php` - student portal
- `app/Services` - enrollment, scheduling, curriculum, reports, quiz, and conflict logic
- `app/Http/Requests` - validation rules for the main flows
- `database/seeders` - realistic demo data for thesis/demo review
- `resources/views` - Blade UI for public pages and role dashboards

## Tech Stack

- Laravel 12
- PHP 8.2+
- Blade templates
- Vite
- SQLite for local development
- MySQL / MariaDB compatible schema
- PHPUnit 11

## Requirements

- PHP 8.2 or newer
- Composer 2
- Node.js and npm
- A supported database engine

## Quick Start

1. Install PHP dependencies:

```bash
composer install
```

2. Install frontend dependencies:

```bash
npm install
```

3. Create the environment file:

```bash
copy .env.example .env
```

4. Generate the application key:

```bash
php artisan key:generate
```

5. Configure database, mail, and site settings in `.env`.
6. Run migrations and seed the demo data:

```bash
php artisan migrate --seed
```

7. Create the storage symlink if you plan to serve uploaded files:

```bash
php artisan storage:link
```

8. Start the backend and frontend in separate terminals:

```bash
php artisan serve
npm run dev
```

## Environment Setup

Review these values in `.env` before running the project locally or deploying it:

- `APP_NAME`
- `APP_ENV`
- `APP_DEBUG`
- `APP_URL`
- `APP_TIMEZONE`
- `DB_CONNECTION`, `DB_DATABASE`, `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`
- `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`
- `QUEUE_CONNECTION`
- `SESSION_DRIVER`
- `CACHE_STORE`
- `SITE_CONTACT_RECIPIENT`
- `SITE_CONTACT_ADDRESS`
- `SITE_CONTACT_PHONE`
- `SITE_CONTACT_HOURS`
- `SITE_CONTACT_EMAILS`
- `SITE_LEGAL_EMAIL`
- `SITE_PRIVACY_EMAIL`
- `SITE_TERMS_UPDATED_AT`
- `SITE_PRIVACY_UPDATED_AT`
- `SITE_FACEBOOK_URL`
- `SITE_YOUTUBE_URL`
- `SITE_ZALO_URL`
- `SITE_MAP_EMBED_URL`

By default, local development uses the `log` mailer.

## Database Setup

The project supports the standard Laravel migration flow.

- For SQLite, ensure the database file exists before running `migrate` or `migrate --seed`.
- For MySQL or MariaDB, update the DB env values and run:

```bash
php artisan migrate --seed
```

If you want to reset everything and rebuild the demo set from scratch, use:

```bash
php artisan migrate:fresh --seed
```

After seeding, you can inspect the demo snapshot with:

```bash
php artisan demo:seed-report
```

Seeded demo data currently includes:

- 2 admin accounts
- 16 teacher accounts
- 26 student accounts
- Realistic categories, subjects, courses, rooms, schedules, enrollments, attendance, grades, quizzes, certificates, announcements, and teacher applications

## Roles

### Admin

- Manage users, teachers, students, departments, rooms, subjects, courses, classes, schedules, enrollments, reports, and teacher applications
- Review enrollment requests and schedule change requests
- Check schedule conflicts before opening or updating classes

### Teacher

- View assigned classes and schedules
- Record attendance, grades, and evaluations
- Submit schedule change requests
- Track class progress and student participation

### Student

- Browse public subjects and courses
- Send fixed-class enrollments or custom schedule requests
- View personal schedule, grades, and certificates
- Take quizzes tied to enrolled courses

## Demo Accounts

Default password for seeded demo users: `123456`

Suggested demo logins:

- Admin: `admin@khaitriedu.vn`
- Admin: `quanly@khaitriedu.vn`
- Teacher: `anhdung@khaitriedu.vn`
- Teacher: `baochau@khaitriedu.vn`
- Teacher: `minhkhang@khaitriedu.vn`
- Student: `nguyen.thi.an@khaitriedu.vn`
- Student: `tran.gia.han@khaitriedu.vn`
- Student: `le.minh.quan@khaitriedu.vn`

The seed data also includes additional teacher and student accounts for broader demo coverage.

## Screenshots

Place final screenshots here once the UI is frozen.

- Homepage
- Public course catalog
- Admin dashboard
- Teacher dashboard
- Student dashboard
- Enrollment conflict warning
- Quiz and certificate screens

## Testing

Run the full test suite:

```bash
php artisan test
```

You can also run focused feature tests when working on a specific flow.

## Deployment Notes

- Set `APP_ENV=production` and `APP_DEBUG=false`
- Configure a real database, cache, queue, and mail transport
- Update the site contact env values before deploying
- Run `php artisan optimize`
- Run `php artisan migrate --force`
- Run `php artisan storage:link`
- Build frontend assets with `npm run build`
- Make sure a queue worker is running if you rely on queued jobs or mail

## Future Development

- Add more polished screenshots and demo walkthroughs
- Expand reporting around attendance and performance
- Continue extracting heavy controller logic into services and form requests
- Add more targeted tests for edge cases as new features are introduced
- Prepare production-specific configuration if the project is deployed beyond demo use

## Notes

- Vietnamese UI copy is preserved where it already exists.
- The project already uses services for several core workflows, and new code should follow that direction.
- Keep new features aligned with the current business model instead of replacing the existing demo flow.
