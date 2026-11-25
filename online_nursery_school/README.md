# Online Nursery School Management System

This project is a complete nursery school management system built with pure HTML, CSS, JavaScript, PHP, and MySQL. It includes authentication, pupil management, parent management, class management, fee tracking, user management, and reports.

## Project Structure

```text
online_nursery_school/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   ├── Database.php
│   └── helpers.php
├── config/
│   └── config.php
├── database/
│   ├── schema.sql
│   ├── seed.sql
│   └── migration_parent_role_and_fee_limits.sql
├── public/
│   ├── api/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── index.php
├── README.md
├── TEST_PLAN.md
└── .gitignore
```

## Features

- Secure login with username/email and password hashing
- Protected dashboard with PHP sessions
- CRUD for pupils, parents, classes, fees, and users
- Parent user accounts linked to their parent record, with a read-only portal for their children and fee balances
- Fee validation prevents negative payments and amounts above the expected fee
- Role-based access control
- Real database-driven statistics and reports
- Search and filtering features
- AJAX live pupil search using Fetch API and JSON
- Responsive dashboard with a three-dot mobile navigation menu

## Database Setup

1. Start XAMPP and ensure Apache and MySQL are running. The provided XAMPP setup uses MySQL port `3307`; check `C:\xampp\mysql\bin\my.ini` if your port differs.
2. Open phpMyAdmin.
3. Create a database named `nursery_school` or run the SQL files directly.
4. Import the schema:
   - `database/schema.sql`
5. Import the sample data:
   - `database/seed.sql`

For an existing database, import `database/migration_parent_role_and_fee_limits.sql` once instead of importing the schema again. It adds the parent account relationship and fee amount constraints without dropping your records.

## Default Login

- Username: `admin`
- Password: `admin123`

Parent demo account:

- Username: `alice`
- Password: `admin123`

## Local Access

Open the project in the browser through:

```text
http://localhost/student_management_system/online_nursery_school/public/index.php?page=login
```

## Important Notes

- The project uses PDO prepared statements.
- Passwords are stored with `password_hash()`.
- CSRF tokens are generated and validated for secure POST actions.
- All pages are protected by login checks.
- User management is restricted to administrators.
- Each parent account links to one `parents` record; each parent record can be linked to many pupils through `pupils.parent_id`.
- Parent users can only access the parent portal and view pupils associated with their parent record.
- Fee `amount_paid` must be between zero and `amount_due`; outstanding balance is calculated as `amount_due - amount_paid`.

## Files and Responsibilities

- `config/config.php` — database configuration and app URL
- `app/Database.php` — PDO database connection
- `app/helpers.php` — session, validation, redirect, and sanitization helpers
- `app/Models/*.php` — database logic for each table
- `app/Controllers/*.php` — request handling logic
- `app/Views/layout.php` — main dashboard layout and page shell
- `public/index.php` — router and page rendering system
- `public/api/search_pupils.php` — JSON API for live search
- `public/assets/css/styles.css` — full app styling
- `public/assets/js/app.js` — modals, edit forms, and AJAX search

The database connection defaults to `localhost:3307`, `root`, and an empty password to match this XAMPP installation. If your MySQL settings differ, set `NURSERY_DB_PORT`, `NURSERY_DB_USER`, and `NURSERY_DB_PASS` in the PHP environment, or update those defaults in `config/config.php`.

## Testing

Use the `TEST_PLAN.md` file to test the system manually in XAMPP.
