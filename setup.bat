@echo off
cd d:\XXamp\htdocs\khaitriedu
echo Running migrations...
php artisan migrate --force
echo.
echo Starting Laravel development server...
php artisan serve --host=127.0.0.1 --port=8000
