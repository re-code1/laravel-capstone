# Plan: make the report card app replicable by teammates

For a fresh Claude Code session working in `C:\Users\User\laravel-capstone`, with Reggie.
Goal: a teammate can clone the repo, follow a short README, run `php artisan migrate:fresh --seed`, and see the same app at `http://localhost:8005`.

## 0. Rules for this session (read first)

1. Read `CLAUDE.md` in the project root. Its three Working Instructions override everything here: **discuss before executing**, **ask when unsure**, **ask for missing context**. Show each step's plan, wait for Reggie's OK, then act. One step at a time.
2. Never run anything that needs administrator rights or the MariaDB root password. Give Reggie the exact command (PowerShell) and he runs it.
3. Use `mariadb`, never `mysql`. MariaDB 13.1 runs on **port 3309** (`C:\Program Files\MariaDB 13.1\bin` is on Reggie's user PATH; use the full path if the shell is older).
4. Do not touch other databases on this PC (MySQL 8.0 on 3307, Docker databases, whatever is on 3306/3308). Never connect to them.
5. No Tailwind, no npm, no Vite, no `composer run setup` (it runs npm). Never edit `vendor/`. Never commit `.env`, `vendor/` or `database/database.sqlite`.
6. Reggie allowed `.env` edits for the project, but **report every `.env` / `.env.example` change as old value, new value**.
7. Commit only after Reggie says OK. Run `git status` first and show it. Commit messages end with the attribution line from the session's system reminder. **Do not push** unless Reggie explicitly asks.
8. Every web request through the app writes a row to the `sessions` table (session driver is `database`). When testing with `curl` or `kernel->handle()`, tell Reggie, or set `config(['session.driver' => 'array'])` for in-process checks. Earlier test runs left 16 stray rows, which were deleted with Reggie's OK.

## 1. Where things stand

Branch `project/laravel-start`, about 7 commits ahead of `origin` (not pushed). Phases done and committed, in order: `New Laravel app`, `School tables`, `Seed school data`, `Models and relationships`, `Report card service`, `Routes and controllers`, `Report card pages`.

**Also committed after Phase 10:** `Define INC once`. It adds an `INCOMPLETE` constant in `app/Services/ReportCardService.php`, `rows()` returns `'INC'` for a missing semester, and the `?? 'INC'` is removed from `resources/views/students/report-card.blade.php`. Reggie confirmed in the browser that Andrea's Science Sem 2 still shows INC.

Working app facts (all verified by Reggie in the browser and tinker):
- Counts after `migrate:fresh --seed`: 8 teachers, 6 subjects, 2 sections, 11 students, 120 grades.
- Andrea Bautista (student 4): average 86.7, remark INC, Science Sem 2 shows INC. Rafael Castillo (student 11): NO GRADES. Carlo Ramos: 77.96 PASSED. `/students/999` is a 404.
- Queries: `/sections` 2, `/sections/{id}` 3 (independent of class size), `/students/4` 5.
- App runs with `php artisan serve --port=8005`. Database `report_cards`, user `laravel`, password `secret123` (lab value).

## 2. Gaps that stop a teammate (what this plan fixes)

| # | Gap | Fix |
|---|---|---|
| 1 | `README.md` is Laravel's default, no setup steps | Step 3 |
| 2 | `.env.example` says `APP_URL=http://localhost:8000` (app uses 8005) | Step 2 |
| 3 | Nothing is pushed | Step 5 (only on Reggie's word) |
| 4 | Port 3309 and `--port=8005` are local choices a teammate won't guess | Document in the README |
| 5 | Never tested from a clean clone | Step 4 |

Not fixing here (parked review findings, mention to Reggie but do not start): rounding rule for 74.995 is undecided in CLAUDE.md; CSS class computed in views three times; hidden dependency on `loadMissing` side effect in the report card view; `Grade` has no casts; no tests and `ExampleTest` likely fails because `/` redirects; no lazy-loading guard; leftovers (`welcome.blade.php`, `package.json`, `vite.config.js`, `.npmrc`, users table).

## 3. Steps

### Step 1. Confirm the INC change (already committed)
Done in the previous session. Just run `git log --oneline -3` to confirm `Define INC once` is there and `git status` is clean, then go to Step 2.

### Step 2. `.env.example`
- Propose `APP_URL=http://localhost:8005`. Leave `DB_PASSWORD=` and `APP_KEY=` empty on purpose. Ask Reggie whether `APP_NAME` should become `"Report Cards"`.
- Report old and new values. Commit on OK, for example `Fix APP_URL in env example`.

### Step 3. README setup section
Draft first and show it to Reggie. Replace Laravel's default README with a short one (keep it under about 80 lines):
1. What the app is (one paragraph) and the restaurant layers in one table: route, controller, `app/Services`, model, Blade view.
2. Requirements: PHP 8.3+ with `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `intl`; Composer 2; MariaDB 11.8+ (tested on 13.1); Git. No Node, no npm.
3. Setup, in order:
   - Create the database and user as MariaDB root (the three SQL lines: `CREATE DATABASE report_cards;`, `CREATE USER 'laravel'@'localhost' IDENTIFIED BY 'secret123';`, `GRANT ALL PRIVILEGES ON report_cards.* TO 'laravel'@'localhost';`). Say the lab password must never be reused anywhere real.
   - `composer install`
   - `Copy-Item .env.example .env` (PowerShell) or `cp .env.example .env`
   - Edit `.env`: `DB_PASSWORD=secret123`; set `DB_PORT` to the port their MariaDB uses (**3306 is MariaDB's default; Reggie's PC uses 3309**)
   - `php artisan key:generate`
   - `php artisan migrate:fresh --seed` with a warning: it drops every table in the database named in `.env`
   - `php artisan serve --port=8005`
4. Expected results (8/6/2/11/120; Andrea 86.7 INC; Rafael NO GRADES; Carlo 77.96 PASSED; `/students/999` is 404).
5. Short troubleshooting table (take rows from `claude/documents/day3-laravel-windows-setup-guide.md`: access denied, connection refused, unknown database, missing key, `.env` changes ignored → `php artisan config:clear`).
6. "Do not run `composer run setup` or any npm command."
- Commit on OK as `Add setup README`.

### Step 4. Clean-clone test (do this before pushing)
Goal: prove the README works from nothing, **without touching `report_cards`**.
1. Ask Reggie to run as MariaDB root (you must not): `CREATE DATABASE report_cards_test; GRANT ALL PRIVILEGES ON report_cards_test.* TO 'laravel'@'localhost';`
2. Clone the local repo into a temporary folder outside the project: `git clone C:\Users\User\laravel-capstone $env:USERPROFILE\capstone-clone-test`.
3. In the clone only, follow the README literally: `composer install`, copy `.env.example` to `.env`, `key:generate`, set `DB_DATABASE=report_cards_test` and `DB_PASSWORD=secret123`. Verify `.env` points at `report_cards_test` **before** running anything destructive.
4. `php artisan migrate:fresh --seed`, then check the counts with `mariadb ... report_cards_test`.
5. `php artisan serve --port=8006` (not 8005, Reggie's own server may be running). Check `/sections`, `/students/4`, `/students/11`, `/students/999` (a GET writes a session row in `report_cards_test`, which is fine).
6. Write down every point where the README was wrong or unclear, fix the README, and commit.
7. Cleanup, each with Reggie's OK: stop the 8006 server; Reggie drops the test database as root (`DROP DATABASE report_cards_test;`); show the folder contents before deleting `capstone-clone-test`, then delete it.

### Step 5. Push (only if Reggie asks)
`git status`, `git log origin/project/laravel-start..HEAD --oneline`, confirm the branch and remote (`origin` is `https://github.com/re-code1/laravel-capstone.git`), then `git push`. Ask Reggie whether the repository is private, because `claude/documents/` contains the lab password.

## 4. Done means
- [x] `Define INC once` committed after Reggie's check.
- [ ] `.env.example` `APP_URL` is 8005; the change was reported.
- [ ] README lets a clean clone reach a working `localhost` page, proven by the Step 4 test.
- [ ] `report_cards` data and `.env` were never touched by the test; the test database and clone are removed.
- [ ] Nothing pushed unless Reggie asked.
