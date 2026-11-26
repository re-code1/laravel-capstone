# Report Cards

A small Laravel app that shows school report cards: sections, their students, and each student's grades, general average and remark (PASSED, FAILED, INC or NO GRADES). Built with Laravel 13, MariaDB and plain Blade views. No Node, no npm.

## How the code is organised

| Layer | Where | Job |
|---|---|---|
| Route | `routes/web.php` | Maps a URL to a controller |
| Controller | `app/Http/Controllers` | Gets the models, calls the service, returns a view |
| Service | `app/Services/ReportCardService.php` | All school rules (average, remarks) |
| Model | `app/Models` | Relationships and small helpers |
| View | `resources/views` | Displays data only |

## Requirements

- PHP 8.3+ with `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `intl`
- Composer 2
- MariaDB 11.8+ (tested on 13.1)
- Git

## First-time setup

Commands are PowerShell. Use `cp` instead of `Copy-Item` on macOS or Linux.

1. **Create the database and user.** Log in to MariaDB as root (replace 3309 with your port) and run the three SQL lines:

   ```powershell
   mariadb -u root -P 3309 -h 127.0.0.1 -p
   ```
   ```sql
   CREATE DATABASE report_cards;
   CREATE USER 'laravel'@'localhost' IDENTIFIED BY 'secret123';
   GRANT ALL PRIVILEGES ON report_cards.* TO 'laravel'@'localhost';
   ```
   `secret123` is a lab password. Never reuse it anywhere real.

2. **Install and configure:**

   ```powershell
   composer install
   Copy-Item .env.example .env
   ```
3. **Edit `.env`:** set `DB_PASSWORD=secret123`. Set `DB_PORT` to the port your MariaDB uses (`.env.example` says 3309; MariaDB's default is 3306).
4. **Generate the app key:**

   ```powershell
   php artisan key:generate
   ```
5. **Create the tables and load the school data.** Warning: `migrate:fresh` drops every table in the database named in `.env`.

   ```powershell
   php artisan migrate:fresh --seed
   ```
6. **Start the app** (see below) and open http://localhost:8005.

Do not run `composer run setup` or any npm command. This project does not use them.

## Starting up each day

1. Make sure MariaDB is running: `Get-Service MariaDB` should say Running. If it says Stopped, ask whoever owns the machine to start it.
2. Check the database login works (should print 1):

   ```powershell
   mariadb -u laravel -p -P 3309 -h 127.0.0.1 report_cards -e "SELECT 1"
   ```
3. From the project folder, start the app and leave that terminal open:

   ```powershell
   php artisan serve --port=8005
   ```
4. Open http://localhost:8005. Press `Ctrl+C` in the terminal to stop it.

## What you should see

- After seeding: 8 teachers, 6 subjects, 2 sections, 11 students, 120 grades.
- Andrea Bautista (`/students/4`): average 86.7, remark INC, Science Sem 2 shows INC.
- Rafael Castillo (`/students/11`): NO GRADES.
- Carlo Ramos: average 77.96, PASSED.
- `/students/999` is a 404 page.

## Troubleshooting

| Symptom | Fix |
|---|---|
| `Access denied for user 'laravel'` | Wrong `DB_PASSWORD`, or the user was created on a different MariaDB server than `DB_PORT` points to |
| `No connection could be made` / connection refused | MariaDB is stopped, or `DB_PORT` is wrong |
| `Unknown database 'report_cards'` | Step 1 was not done, or was done on another server or port |
| `No application encryption key` | Run `php artisan key:generate` |
| `.env` changes seem ignored | Run `php artisan config:clear` |
| `Address already in use` | Another server is on that port. Use `--port=8006` |
| `mariadb` is not recognized | Reopen the terminal, or use the full path in `C:\Program Files\MariaDB <version>\bin` |
| `Table ... doesn't exist` | Run `php artisan migrate:fresh --seed` |
| Where are the errors? | `Get-Content storage/logs/laravel.log -Wait -Tail 20` |
