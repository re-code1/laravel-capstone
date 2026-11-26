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

---

# Day 2 and Day 4 exercises: SQL practice and the report card API

This part is for teammates who pulled the latest code and want to repeat what we did. It follows the PSHS training pages:

- SQL exercises: https://trainingmaterial.in/pshs-full-stack/day2/sql-exercises.html
- API basics: https://trainingmaterial.in/pshs-full-stack/day4/api-basics.html
- API lab: https://trainingmaterial.in/pshs-full-stack/day4/api.html

Commands are PowerShell. Run them from the project folder. Your `.env` and database from the first-time setup above are enough. Do not use `migrate:fresh` unless a step says so.

## Part A: Catch up after `git pull`

1. **Pull the branch and install the new packages.** Laravel Sanctum (login tokens) and Pest (tests) were added:

   ```powershell
   git pull
   composer install
   ```
2. **Add the new database columns and the token table.** This keeps your data. It adds `users.role`, `users.teacher_id` and `personal_access_tokens`:

   ```powershell
   php artisan migrate
   ```
3. **Create the logins.** One registrar (admin) and one login per teacher. Run it **once**. A second run fails with a "Duplicate entry" error:

   ```powershell
   php artisan db:seed --class=UserSeeder
   ```
4. **Check the users.** You should see 10 rows: the default Test User, the registrar and 8 teachers:

   ```powershell
   mariadb -u laravel -p -P 3309 -h 127.0.0.1 report_cards -e "SELECT id, name, email, role, teacher_id FROM users"
   ```
5. **Run the tests.** Pest uses an in-memory SQLite database, so PHP needs the `pdo_sqlite` extension. Your real data is never touched:

   ```powershell
   php artisan test
   ```

   Expected: all 43 tests pass.

If you start from a fresh database instead, run `php artisan migrate:fresh --seed` (this drops every table), then step 3 above.

## Part B: The SQL exercises (Day 2)

The data from the SQL page (8 teachers, 11 students, 120 grades) is **already in your database** from `SchoolSeeder`. We compared it with the page's `school-data.sql` and every row matches. So:

- **Do not** download or run `school-data.sql` or `school.sql`. They would fail on duplicate rows or drop the database.
- **Skip** the `sudo`, `apt` and `CREATE DATABASE school` steps. Those are for Linux, and Laravel's migrations already built the tables.

1. **Open a MariaDB prompt on your own database:**

   ```powershell
   mariadb -u laravel -p -P 3309 -h 127.0.0.1 report_cards
   ```
2. **Translate the table and column names.** The page uses `student_id`, `teacher_id` and so on. Our tables use plain `id` for each table's own key:

   | Page says | Our table and column |
   |---|---|
   | `teachers.teacher_id` | `teachers.id` |
   | `students.student_id` | `students.id` |
   | `sections.section_id` | `sections.id` |
   | `subjects.subject_id` | `subjects.id` |
   | `grades.grade_id` | `grades.id` |

   `grades.student_id`, `grades.subject_id` and `grades.teacher_id` keep their names.
3. **Do the SELECT parts (3, 5 and 6 on the page).** Examples that work here:

   ```sql
   -- Students in section 1 (page 3.2)
   SELECT id, last_name FROM students WHERE section_id = 1 ORDER BY last_name;

   -- Failing grades (page 3.3). Expect 74.00 and 72.00.
   SELECT grade FROM grades WHERE grade < 75;

   -- Average per subject (page 5.2)
   SELECT s.name AS subject, ROUND(AVG(g.grade), 2) AS average
   FROM grades g JOIN subjects s ON s.id = g.subject_id
   GROUP BY s.id, s.name ORDER BY average DESC;

   -- Honor roll: average of 90 or more (page 5.4)
   SELECT CONCAT(st.last_name, ', ', st.first_name) AS student, ROUND(AVG(g.grade), 2) AS average
   FROM students st JOIN grades g ON g.student_id = st.id
   GROUP BY st.id, st.last_name, st.first_name
   HAVING AVG(g.grade) >= 90 ORDER BY average DESC;
   ```
4. **Do the INSERT, UPDATE and DELETE parts (2 and 4) inside a transaction you cancel.** That way the shared data stays the same for everyone and the app's numbers stay correct:

   ```sql
   START TRANSACTION;
   UPDATE grades SET grade = 84 WHERE student_id = 4 AND subject_id = 2 AND semester = 2;
   SELECT * FROM grades WHERE student_id = 4 AND subject_id = 2;
   ROLLBACK;
   ```

   Never run `UPDATE` or `DELETE` without a `WHERE`. If you did change real data by mistake, restore it with `php artisan migrate:fresh --seed` and then `php artisan db:seed --class=UserSeeder`.

## Part C: Learn the API ideas (Day 4 basics)

Read the API basics page (JSON, verbs, status codes, naming). The two parts that need Laravel are already done here:

- The plain-PHP `index.php` API on that page is **not** used. The Laravel API below replaces it.
- Postman is covered in Part E.

Quick JSON practice that needs no setup:

```powershell
php -r 'echo json_encode(["name" => "Andrea", "average" => 86.7, "complete" => false]), PHP_EOL;'
```

## Part D: What the API does and where the code lives

All API URLs start with `/api`. Everything except login needs a token (`Authorization: Bearer ...`). Always send `Accept: application/json`, or Laravel redirects to a web login page.

| Method and URL | What it does | Who may |
|---|---|---|
| `POST /api/login` | Email, password and `device_name` give a token (201). Limited to 5 tries a minute | Anyone |
| `GET /api/me` | Name, email and role of the token owner | Any logged-in user |
| `POST /api/logout` | Cancels the token that was used (204) | Any logged-in user |
| `GET /api/sections` | Sections you may see, with a student count | Registrar: all. Teacher: the section they advise |
| `GET /api/sections/{id}/students` | Class list with average and remark | Same rule |
| `GET /api/students/{id}/report-card` | One report card as JSON | Same rule |
| `PUT /api/grades/{id}` | Change one grade. Body: `{"grade": 91.5}`. `null` clears it (INC) | Registrar, or the teacher who gave that grade |

Status codes: 401 no or bad token, 403 not allowed, 404 not found, 422 invalid input (the grade must be between 60 and 100 with at most 2 decimals).

| Piece | File |
|---|---|
| API routes | `routes/api.php` |
| Login, me, logout | `app/Http/Controllers/Api/AuthController.php` |
| Sections and class list | `app/Http/Controllers/Api/SectionController.php` |
| Report card JSON | `app/Http/Controllers/Api/ReportCardController.php` and `app/Http/Resources/ReportCardResource.php` |
| Grade update | `app/Http/Controllers/Api/GradeController.php`, `app/Http/Requests/UpdateGradeRequest.php`, `app/Http/Resources/GradeResource.php` |
| Who may do what | `app/Policies/SectionPolicy.php`, `app/Policies/GradePolicy.php`, and the same rule as a query in `Section::scopeVisibleTo()` |
| School rules (still one place) | `app/Services/ReportCardService.php` |
| Tests | `tests/Feature/Api*Test.php`, set up in `tests/Pest.php` |

All logins have the password `password` (lab only). Examples: `registrar@school.edu.ph` (sees everything) and `lfernandez@school.edu.ph` (Liza, advises Sampaguita only). Tokens stop working after 7 days.

## Part E: Try it in Postman (Day 4 lab)

1. Start the app and leave it running: `php artisan serve --port=8005`
2. In Postman click **Import** and add both files from the `postman/` folder:
   - `report-cards-local.postman_environment.json`
   - `report-cards.postman_collection.json`
3. In the top-right dropdown choose the environment **Report Cards (local)**. Its `base_url` is `http://localhost:8005`.
4. Run **1 Login > Login as Liza** first. Its test script saves the token in the environment, and every other request uses it.
5. Run the folders in order: **2 Read**, **3 Change**, **4 Logout**. You can also use the collection Runner. Every request should pass.
6. **3 Change** edits grade 1 and its last request puts it back to 88.25. If you stop halfway, send `PUT /api/grades/1` with `{"grade": 88.25}`.
7. After **4 Logout** the token is cancelled. Run **Login as Liza** again before more requests.

No Postman? Try the API from PowerShell:

```powershell
$h = @{ Accept = 'application/json' }
$login = Invoke-RestMethod -Method Post http://localhost:8005/api/login -Headers $h -ContentType 'application/json' -Body '{"email":"lfernandez@school.edu.ph","password":"password","device_name":"powershell"}'
$h.Authorization = "Bearer $($login.token)"
Invoke-RestMethod http://localhost:8005/api/sections -Headers $h
```

## Part F: How we built it (to repeat it step by step on a fresh copy)

Each step ended with `php artisan test` passing and a commit. The rules for this live in the "API rules (Day 4)" section of `CLAUDE.md`.

1. **Add the Day 4 rules to `CLAUDE.md`.** Download `day4-kit.tar.gz` from the API lab page into its own empty folder and extract it. Read `CLAUDE-day4.md`, then append it to `CLAUDE.md`. Change the old line "Routes only in `routes/web.php`" so API routes may live in `routes/api.php`.
2. **Link logins to teachers.** Create a **new** migration that adds `users.role` (default `teacher`) and `users.teacher_id` (nullable foreign key to `teachers`). Never edit a migration that has already run. Add `teacher()` and `isAdmin()` to `User`, and keep `role` and `teacher_id` out of `$fillable`. Copy `UserSeeder.php` from the kit into `database/seeders`, then run `php artisan migrate` and `php artisan db:seed --class=UserSeeder`.
3. **Install Pest:** `composer require --dev pestphp/pest pestphp/pest-plugin-laravel`, then add `tests/Pest.php` that gives Feature tests the Laravel app and `RefreshDatabase`.
4. **Install Sanctum:** `php artisan install:api` (answer yes to migrations). Add `HasApiTokens` to `User` and delete the example `/api/user` route.
5. **Build login:** `AuthController` with `login`, `me` and `logout`, and the routes in `routes/api.php`. Put every route except login inside `Route::middleware('auth:sanctum')`. Write tests as you go.
6. **Add permissions and JSON.** Create `SectionPolicy` and `Section::scopeVisibleTo()` (one rule, written twice, so change both together). Add the sections list, class list and report card endpoints. Shape every response with an API Resource. The controllers only call `ReportCardService`.
7. **Add the first endpoint that writes.** Create `GradePolicy`, `UpdateGradeRequest` and `GradeController::update`. Save only `$request->validated()`. Give `Grade` `#[Fillable(['grade'])]`. The check order is: token (401), policy (403), rules (422), controller.
8. **Review the API for security.** Check `app/Http/Controllers/Api` and `app/Http/Requests` for SQL injection, mass assignment, missing authorization and weak logins. Fix what you find. We fixed four things:
   - Tokens expire after 7 days (`config/sanctum.php`).
   - Login takes the same time for an unknown email.
   - One token per `device_name`.
   - Every model has `#[Fillable([])]` or an explicit list.
9. **Import the kit's Postman files** from the `postman/` folder, change `base_url` to your port, and run the collection (Part E).

## Part G: Troubleshooting the API

| Symptom | Fix |
|---|---|
| `Route [login] not defined` | Add the header `Accept: application/json` |
| `{"message":"Unauthenticated."}` | No token, an old token, or the request is set to "No Auth". Log in again |
| `Call to undefined method ...createToken()` | `HasApiTokens` is missing from `User` |
| `Unknown column 'role'` | Run `php artisan migrate` |
| `Duplicate entry` when seeding users | You already ran `UserSeeder`. Do not run it twice |
| 429 on login | Too many tries. Wait a minute |
| 403 on a grade or report card | That section or grade belongs to another teacher. Log in as the right teacher or as the registrar |
| 422 on a grade | The grade must be a number from 60 to 100 with at most 2 decimals, or `null` |
| 200 but the grade did not change | In Postman set the body type to **raw > JSON**, not Text |
| Tests fail with a driver error | Enable `pdo_sqlite` in `php.ini` |
