# FieldTrack Attendance System

Laravel-based attendance system for office and field staff. It includes:

- Role-based access for admin and staff
- MySQL-backed users, locations, and attendance logs
- GPS radius validation
- Selfie capture with browser camera support
- Attendance history and admin location management

## Setup

1. Create a MySQL database named `fieldtrack_attendance`.
2. Copy `.env.example` to `.env` if needed and set your MySQL credentials.
3. Run:

```bash
composer install
npm install
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
php artisan storage:link
```

## Demo Accounts

- Admin: `admin@fieldtrack.local` / `admin123`
- Staff: `rahul@fieldtrack.local` / `user123`

## Run Locally

```bash
php artisan serve
npm run dev
```

If you are using MySQL, keep the session and cache tables in place by running the migrations above before starting the app.
