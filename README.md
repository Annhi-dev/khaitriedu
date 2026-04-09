# KhaiTriEdu

KhaiTriEdu is a Laravel 12 education management website for public course browsing and role-based administration for `admin`, `teacher`, and `student` accounts.

It is built to support a real training workflow: public landing pages, subject and course catalogs, enrollments, class scheduling, quizzes, certificates, teacher applications, and dashboard views for each role.

## Core Features

- Public website pages: home, about, courses, teachers, blog, help, terms, privacy, contact, and teacher application
- Role-based portals for `admin`, `teacher`, and `student`
- Subject and course management
- Enrollment flow for fixed classes and custom schedule requests
- Class scheduling and room assignment
- Quiz submission and certificate viewing
- Teacher application review and account activation
- Attendance, grades, and schedule change workflows
- Dashboard summaries and reporting screens

## Tech Stack

- Laravel 12
- PHP 8.2+
- Blade templates
- Vite
- SQLite by default for local development
- MySQL / MariaDB compatible schema
- PHPUnit 11

## Project Structure

- `app/Http/Controllers` - public, admin, teacher, and student controllers
- `app/Services` - business logic extracted from controllers
- `app/Http/Requests` - request validation classes
- `app/Models` - Eloquent models for education domain entities
- `database/migrations` - schema definitions
- `database/seeders` - demo data
- `resources/views` - Blade UI for public pages and portals
- `routes` - route entry points for web and role-based areas

## Requirements

- PHP 8.2 or newer
- Composer 2
- Node.js and npm
- A supported database engine

## Installation

1. Clone the repository and move into the project directory.
2. Install PHP dependencies:

```bash
composer install
```

3. Install frontend dependencies:

```bash
npm install
```

4. Copy the environment file:

```bash
copy .env.example .env
```

5. Generate the application key:

```bash
php artisan key:generate
```

6. Configure the database, mail, and site settings in `.env`.
7. Run migrations and seed the demo content:

```bash
php artisan migrate --seed
```

8. Create the storage symlink if the app serves uploaded files:

```bash
php artisan storage:link
```

9. Build frontend assets for production or start the Vite dev server locally.

## Environment Setup

The application reads its runtime settings from `.env`.

Recommended values to review:

- `APP_NAME`
- `APP_ENV`
- `APP_DEBUG`
- `APP_URL`
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
- `SITE_FACEBOOK_URL`
- `SITE_YOUTUBE_URL`
- `SITE_ZALO_URL`
- `SITE_MAP_EMBED_URL`

By default, mail is configured to use the `log` mailer for local development.

## Database Setup

The project works with the default Laravel migration flow.

Local SQLite is already supported, but you can switch to MySQL or MariaDB by updating the database environment variables and running:

```bash
php artisan migrate --seed
```

Seeded demo data includes:

- Admin accounts
- Teacher accounts
- Student accounts
- Sample categories, subjects, courses, modules, lessons, and blog announcements
- Sample rooms

## Run Locally

Start the backend:

```bash
php artisan serve
```

Run the Vite frontend watcher:

```bash
npm run dev
```

If you need queue workers locally, run:

```bash
php artisan queue:listen
```

## Test Instructions

Run the full test suite:

```bash
php artisan test
```

You can also run focused feature tests while working on a specific flow.

## Roles

### Admin

- Manage users, teachers, students, departments, rooms, subjects, courses, classes, schedules, enrollments, teacher applications, and reports
- Review enrollment requests and create class schedules
- Review schedule change requests and operational workflows

### Teacher

- View assigned classes and schedules
- Record attendance, grades, and evaluations
- Submit schedule change requests
- Inspect teaching progress and related student data

### Student

- Browse public subjects and courses
- Submit fixed-class enrollments or custom schedule requests
- View personal schedules, grades, and certificates
- Take quizzes tied to enrolled courses

## Demo Accounts

The seeder currently provides sample accounts.

Default password for seeded demo users: `123456`

Examples:

- Admin: `admin@gmail.com`
- Admin: `admin2@gmail.com`
- Teacher: `gv1@gmail.com` through `gv10@gmail.com`
- Student: `hv1@gmail.com` through `hv10@gmail.com`

If you change the seed data, update this section to match the new demo set.

## Screenshots

Add production screenshots here after the UI is finalized.

- Homepage screenshot
- Admin dashboard screenshot
- Teacher portal screenshot
- Student portal screenshot

## Deployment Notes

- Set `APP_ENV=production` and `APP_DEBUG=false`
- Configure a real database, cache, queue, and mail transport
- Set the site contact env values before deploying
- Run `php artisan optimize`
- Run `php artisan migrate --force`
- Run `php artisan storage:link`
- Build frontend assets with `npm run build`
- Make sure a queue worker is running if you rely on queued jobs or mail

## Notes

- The project preserves Vietnamese UI content where it already exists.
- Some controllers have already been partially refactored into services. Keep that direction when extending the codebase.
- Use Laravel conventions for new modules, requests, services, and tests.
