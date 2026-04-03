You are a senior Laravel developer with 10+ years of experience.

I have a Laravel project that needs to be refactored to production-level quality.

Your task is to analyze and refactor the ENTIRE project based on the following requirements:

## 1. Architecture Improvements

* Refactor controllers to be thin (only handle request/response)
* Move business logic into Service layer (app/Services)
* (Optional) Use Repository pattern if needed
* Apply clean architecture principles

## 2. Code Quality

* Follow Laravel best practices and conventions
* Apply PSR-12 coding standards
* Remove duplicated code
* Rename unclear variables, functions, folders (e.g. "hinh" → "images", "promt" → "prompts")
* Ensure consistent naming across the project

## 3. Security Fixes

* Remove unsafe files (e.g. check.php)
* Ensure .env is not exposed
* Validate all inputs properly
* Prevent mass assignment vulnerabilities

## 4. File Structure Optimization

* Organize folders:
  app/
  ├── Http/Controllers
  ├── Models
  ├── Services
  ├── DTO (if needed)
* Move images to public/images or storage

## 5. Database & Models

* Optimize relationships (Eloquent)
* Add proper foreign keys
* Prevent N+1 query issues (use eager loading)

## 6. Frontend / UX (Blade)

* Improve Blade structure (layouts/components)
* Avoid duplicate HTML
* Make forms cleaner and reusable

## 7. Output Format

For each change:

* Show BEFORE code
* Show AFTER code
* Explain WHY the change is better

## 8. Priority

Focus on:

1. Maintainability
2. Clean code
3. Security
4. Scalability

## IMPORTANT:

* Do NOT break existing functionality
* Do NOT rewrite everything blindly
* Only improve and refactor
* Keep logic consistent with original system

Start by analyzing the folder structure, then refactor step by step.
