# Online Nursery School Management System

A responsive nursery school management system for managing pupils, parents,
classes, fees, users, reports, and parent accounts.

The application is built with PHP, MySQL, HTML, CSS, and JavaScript. It runs
locally with XAMPP and does not require Composer or any other PHP package manager.

## Features

- Staff and administrator login
- Role-based access control
- Pupil, parent, class, fee, and user management
- Live pupil search
- Fee payment tracking and balance calculations
- Database-driven dashboard and reports
- Parent portal for viewing linked pupils and fee balances
- Password hashing and PDO prepared statements
- CSRF protection for POST actions
- Responsive desktop, tablet, and mobile interface

## Requirements

- XAMPP on Windows
- Apache
- MySQL or MariaDB
- PHP 7.4 or newer
- A modern web browser

## Installation

### 1. Place the project in XAMPP

The project folder must be inside XAMPP's web directory:

```text
C:\xampp\htdocs\student_management_system
```

The application itself is in:

```text
C:\xampp\htdocs\student_management_system\online_nursery_school
```

### 2. Start XAMPP

Open the XAMPP Control Panel and start **Apache** and **MySQL**.

### 3. Create the database

Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Create the database and import the SQL files in this order:

1. `online_nursery_school\database\schema.sql`
2. `online_nursery_school\database\seed.sql`

The SQL creates a database named `nursery_school` and adds sample records.

### 4. Configure the database connection

The default configuration is in
`online_nursery_school\config\config.php`:

```text
Host: localhost
Port: 3306
Database: nursery_school
Username: root
Password: (empty)
```

If your XAMPP MySQL uses another port or credentials, set environment variables
before starting Apache:

```text
NURSERY_DB_PORT
NURSERY_DB_USER
NURSERY_DB_PASS
```

For example, this project can be changed to use port `3307` by editing the
`NURSERY_DB_PORT` environment variable or by updating `config/config.php`.

### 5. Open the application

Open:

```text
http://localhost/student_management_system/
```

The root page redirects to the login screen. You can also open it directly:

```text
http://localhost/student_management_system/online_nursery_school/public/index.php?page=login
```

## Default Accounts

| Role | Username | Password |
|---|---|---|
| Administrator | `admin` | `admin123` |
| Parent | `alice` | `admin123` |

Staff accounts included in the sample database:

| Username | Password |
|---|---|
| `staff1` | `admin123` |
| `staff2` | `admin123` |

All credentials are only for local testing. Change or remove the sample accounts
before deploying the system.

## Main Pages

| Page | URL parameter | Access |
|---|---|---|
| Login | `page=login` | Public |
| Dashboard | `page=dashboard` | Admin and staff |
| Pupils | `page=pupils` | Admin and staff |
| Parents | `page=parents` | Admin and staff |
| Classes | `page=classes` | Admin and staff |
| Fees | `page=fees` | Admin and staff |
| Users | `page=users` | Administrator |
| Reports | `page=reports` | Admin and staff |
| Parent portal | `page=family` | Parent |

## Project Structure

```text
student_management_system/
├── index.html
├── README.md
└── online_nursery_school/
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
    │   └── seed.sql
    ├── public/
    │   ├── api/
    │   ├── assets/
    │   └── index.php
    ├── README.md
    └── TEST_PLAN.md
```

## Key Files

- `online_nursery_school\public\index.php` - application router and request handling
- `online_nursery_school\config\config.php` - application and database settings
- `online_nursery_school\app\Database.php` - PDO database connection
- `online_nursery_school\app\Views\layout.php` - shared dashboard layout
- `online_nursery_school\database\schema.sql` - database tables
- `online_nursery_school\database\seed.sql` - sample users and records
- `online_nursery_school\TEST_PLAN.md` - manual test plan

## Troubleshooting

### Database connection error

- Confirm MySQL is running in XAMPP.
- Confirm the `nursery_school` database exists.
- Confirm the host, port, username, and password in
  `online_nursery_school\config\config.php`.
- If MySQL uses port `3307`, set `NURSERY_DB_PORT=3307` or update the port
  configuration.

### Page cannot be found

Confirm Apache is started and open the application from:

```text
http://localhost/student_management_system/
```

Do not open PHP files directly using `C:\xampp\htdocs\...`; use the localhost URL.

### Login does not work

- Import `schema.sql` before `seed.sql`.
- Confirm the seeded `users` table contains the `admin` account.
- Reset or recreate the database using the SQL files if sample data is missing.

## Testing

Follow `online_nursery_school\TEST_PLAN.md` to verify login, CRUD operations,
fees, parent access, security checks, reports, and responsive behavior.
