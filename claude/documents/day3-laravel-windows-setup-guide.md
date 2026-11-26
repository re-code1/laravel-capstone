# Day 3 Laravel Lab: Windows Setup Guide for `laravel-capstone`

Companion to "Day 3 · Laravel with Claude Code". The training is written for Linux (Debian, `sudo`, `~/projects`). This guide adapts every step to **Windows 11 + PowerShell** and to our project layout.

## Decisions already made

| # | Decision |
|---|---|
| D1 | Install **MariaDB** alongside the existing **MySQL 8.0**. Leave MySQL 8.0 untouched, because it belongs to our other projects. |
| D2 | Create the project with `composer create-project`, not the `laravel new` installer. |
| D3 | The **capstone root (`laravel-capstone`) becomes the Laravel root.** The existing `.git`, `For PHP Exercise`, `For Exercise Website` and `claude` folders are kept. |
| D4 | **No Tailwind for now.** We follow the training exactly: Laravel + MariaDB + Blade with the plain `school.css` from the kit. |

## What is already on this PC (checked 2026-11-25)

| Tool | Status |
|---|---|
| PHP 8.3.37 (Laragon) | ✅ All required extensions present (`pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo`, `pdo_sqlite`, ...) |
| Composer 2.10.3 | ✅ |
| Git, VS Code, Node | ✅ (Node is not used in this lab) |
| MySQL 8.0 Windows service | ✅ Running on **port 3306**. Do not stop or modify it. |
| Other databases (Docker) | PRISM maps **3306** and ATLAS's test stack maps **3307**. Do not stop or modify them. |
| Port 3308 | Already in use by another service. Do not use it. |
| **MariaDB** | ❌ Install in Part 1 |

Because ports 3306, 3307 and 3308 are already taken (MySQL 8.0, PRISM, ATLAS and another service), **MariaDB will use port 3309**. This is the main difference from the training, and it must appear in `.env` and in every `mariadb` command.

---

## Part 0: Ground rules (read once)

- Use **PowerShell** for every command in this guide. Open the terminal in VS Code (**Terminal > New Terminal**).
- Linux to Windows translations used below:

| Training (Linux) | Windows PowerShell |
|---|---|
| `sudo mariadb` | `mariadb -u root -P 3309 -h 127.0.0.1 -p` |
| `cp a b` | `Copy-Item a b` |
| `mkdir -p public/css` | `New-Item -ItemType Directory -Force public/css` |
| `rm AGENTS.md` | `Remove-Item AGENTS.md` |
| `tail -f storage/logs/laravel.log` | `Get-Content storage/logs/laravel.log -Wait -Tail 20` |
| `~/Downloads` | `$env:USERPROFILE\Downloads` |

- **Always type `mariadb`, never `mysql`.** On this PC `mysql` resolves to Laragon's MySQL 8.4 client, which is the wrong server and port.
- Keep **two terminals**: one running `php artisan serve`, one for everything else.
- Commit after every step, as the training says.

---

## Part 1: Install MariaDB on port 3309 (Module 1)

### 1.1 Download and install

1. Go to <https://mariadb.org/download/> and choose the latest **MariaDB Server LTS** for Windows (MSI package). Version 11.4 or newer is fine.
2. Run the installer and set:
   - **Root password:** choose one and note it. This is a lab machine, so keep it simple, but do not leave it blank.
   - **Enable access from remote machines for root user:** leave **unchecked**.
   - **Use UTF8 as default server's character set:** leave **checked**.
   - **Install as service:** checked. Service name `MariaDB` (it must differ from `MySQL80`).
   - **TCP port: `3309`**. Do not accept the default 3306.
3. Finish the installer. HeidiSQL is optional.

> If you missed the port field, you can fix it afterwards: edit `my.ini` in `C:\Program Files\MariaDB <version>\data\`, set `port=3309` under `[mysqld]`, then restart the `MariaDB` service.

### 1.2 Put MariaDB's tools on your PATH

Do this in **PowerShell as normal user**:

```powershell
$bin = (Get-ChildItem "C:\Program Files\MariaDB*\bin" -Directory -ErrorAction SilentlyContinue | Select-Object -First 1).FullName
$bin   # should print e.g. C:\Program Files\MariaDB 11.4\bin
[Environment]::SetEnvironmentVariable("Path", $env:Path + ";$bin", "User")
```

Close and reopen VS Code, then verify:

```powershell
mariadb --version
Get-Service MariaDB, MySQL80      # both should say Running
```

Laragon's MySQL entries come earlier on PATH than the one you just added. That is fine, because `mariadb` is a different command name, so there is no clash. If `mariadb --version` is not found, run the `mariadb.exe` in `$bin` by its full path.

### 1.3 Create the app's database and user

Ask Claude Code first, as the training suggests (no commands run):

> I'm about to create a MariaDB user for my Laravel app with CREATE USER and GRANT ALL PRIVILEGES ON report_cards.*. Explain what each part means, and why the app shouldn't connect as root. Don't run any commands.

Then run these yourself (they need your root password):

```powershell
mariadb -u root -P 3309 -h 127.0.0.1 -p
```

```sql
CREATE DATABASE report_cards;
CREATE USER 'laravel'@'localhost' IDENTIFIED BY 'secret123';
GRANT ALL PRIVILEGES ON report_cards.* TO 'laravel'@'localhost';
SHOW GRANTS FOR 'laravel'@'localhost';
EXIT;
```

Log in as the new user to prove it works:

```powershell
mariadb -u laravel -psecret123 -P 3309 -h 127.0.0.1 report_cards
```

Practice from the training, same on Windows:

1. `SHOW DATABASES;` shows only `report_cards` (plus `information_schema`).
2. `USE mysql;` is refused, which is least privilege working.
3. Wrong password: `mariadb -u laravel -pwrongpass -P 3309 -h 127.0.0.1 report_cards`. Note the `Access denied` message.

> **Name of the project database.** The training uses `report_cards`. This is our capstone folder, so if you later want a capstone-specific database, create a second one the same way. For the Day 3 lab, keep `report_cards`.

**Checkpoint:** `SHOW GRANTS` lists `report_cards`.* only, and you can log in as `laravel` on port 3309.

---

## Part 2: Create the Laravel project in the capstone root (Module 2)

Laravel's installer needs an **empty** folder, and `laravel-capstone` is not empty. So we create the app in a temporary folder outside the repo, then move it in without touching your existing files.

### 2.1 Create it in a temp folder

```powershell
cd $env:USERPROFILE
composer create-project laravel/laravel report-cards-temp --no-interaction
```

This takes a few minutes. Notes:

- It generates `.env`, the app key, and a SQLite file, and it may try a first migration against SQLite. All harmless. We will discard that SQLite file.
- This does **not** apply the training's `--database=mariadb --pest --no-node` flags. We set MariaDB in `.env` ourselves in 2.3. Pest is optional (see Stretch tasks); the default PHPUnit works for everything except the stretch-task test syntax.

### 2.2 Move it into `laravel-capstone`

The new project has its own `CLAUDE.md`, and probably `AGENTS.md`. We **don't copy those**, so our `CLAUDE.md` and git history stay untouched.

```powershell
$src = "$env:USERPROFILE\report-cards-temp"
$dst = "$env:USERPROFILE\laravel-capstone"

Get-ChildItem $src -Force |
  Where-Object { $_.Name -notin 'CLAUDE.md','AGENTS.md','.git' } |
  Move-Item -Destination $dst

Remove-Item $src -Recurse -Force
Remove-Item "$dst\database\database.sqlite" -ErrorAction SilentlyContinue
```

Before running `Remove-Item $src`, confirm the move worked: `Get-ChildItem $dst` should show `app`, `artisan`, `composer.json`, `vendor`, `.env`, `.gitignore` next to `For PHP Exercise` and `claude`.

> If `Move-Item` says an item already exists (for example `.gitignore`), stop and ask. Don't use `-Force` to overwrite files you haven't looked at.

### 2.3 Point `.env` at MariaDB

Open `.env` in VS Code. Change **these lines** (note the port!):

```ini
APP_NAME="Report Cards"

DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3309
DB_DATABASE=report_cards
DB_USERNAME=laravel
DB_PASSWORD=secret123
```

Also make `.env.example` say the same `DB_CONNECTION=mariadb` and `DB_PORT=3309` only if the team agrees. `.env` itself is never committed.

### 2.4 Migrate and run

```powershell
php artisan migrate
php artisan serve
```

Open <http://localhost:8000>. You should see the Laravel welcome page. Leave this terminal running and open a **second** terminal.

Verify the tables landed in MariaDB, not MySQL:

```powershell
mariadb -u laravel -psecret123 -P 3309 -h 127.0.0.1 report_cards -e "SHOW TABLES"
```

### 2.5 Give Claude its rules (CLAUDE.md)

1. Download the kit and extract it into its **own** folder (never extract into a folder that holds other files):

```powershell
$kit = "$env:USERPROFILE\Downloads\day3-kit-extract"
New-Item -ItemType Directory -Force $kit | Out-Null
Invoke-WebRequest "https://trainingmaterial.in/pshs-full-stack/day3/downloads/day3-kit.tar.gz" -OutFile "$env:USERPROFILE\Downloads\day3-kit.tar.gz"
tar -xzf "$env:USERPROFILE\Downloads\day3-kit.tar.gz" -C $kit
Get-ChildItem $kit -Recurse    # expect CLAUDE.md, SchoolSeeder.php, school.css
```

2. **Read the kit's `CLAUDE.md` before using it.** The training stresses this. It holds the restaurant rules and the school's grading rules.
3. Replace our `CLAUDE.md` with it, but keep our own rules. Our current file has three **Working Instructions** (discuss before executing, ask when unsure, ask for missing context) and a Tech Stack with **Tailwind**. When replacing the contents:
   - keep the three Working Instructions;
   - in Tech Stack, remove or mark Tailwind as "later", per D4 (the kit uses plain CSS);
   - keep MariaDB, Laravel and Blade.

   Ask Claude Code to merge them for you, or do it by hand. Don't just overwrite, or the Working Instructions are lost.
4. Commit (the repo already exists, so **do not run `git init`**):

```powershell
git status          # make sure .env and /vendor do NOT appear
git add .
git commit -m "New Laravel app"
```

Commit only if `git status` doesn't list `.env`, `vendor/` or `database.sqlite`.

**Practice checks:** `php artisan --version`, `php artisan route:list`, and confirm `.env` and `/vendor` are in `.gitignore`. Break it on purpose: set `DB_PASSWORD=wrong`, reload, read the error, then restore it.

---

## Part 3: Migrations (Module 3)

1. Run the **Understand** prompt in Claude Code first (no files created).
2. Run the **Build** prompt. Read the plan, check the order is **Teacher, Subject, Section, Student, Grade**, then approve and read every diff against the expected `up()` code in the training.
3. Run the migration yourself:

```powershell
php artisan migrate
mariadb -u laravel -psecret123 -P 3309 -h 127.0.0.1 report_cards -e "SHOW TABLES; DESCRIBE students;"
```

4. Check: `php artisan migrate:rollback`, then `SHOW TABLES` to see what vanished, then `php artisan migrate` again.
5. Commit: `git add .` then `git commit -m "School tables"`.

---

## Part 4: Seeder (Module 4)

```powershell
Copy-Item "$env:USERPROFILE\Downloads\day3-kit-extract\day3-kit\SchoolSeeder.php" database\seeders\
```

(Adjust the path if `Get-ChildItem $kit -Recurse` showed a different folder layout.)

Edit `database/seeders/DatabaseSeeder.php` and add this line at the end of `run()`:

```php
$this->call(SchoolSeeder::class);
```

> The `User::factory()...` block from the training may already be there. The new `$this->call(...)` line goes after it, before the closing `}`.

```powershell
php artisan migrate:fresh --seed
mariadb -u laravel -psecret123 -P 3309 -h 127.0.0.1 report_cards -e "SELECT COUNT(*) FROM grades; SELECT * FROM sections;"
```

Expected: **120 grades, 2 sections**. Find Andrea's missing grade: `SELECT * FROM grades WHERE grade IS NULL;`. Commit: `git commit -m "Seed school data"`.

`migrate:fresh` drops every table in `report_cards`, which is fine here. It only ever touches the database in `.env`, which is why that file must say `report_cards` on port 3309.

---

## Part 5 to 8: Models, Service, Routes/Controllers, Views (Modules 5-8)

These steps are pure Laravel and identical on Windows. Follow the training exactly (Understand, Build, compare with Expected code, Check, Commit). Windows-specific notes only:

- **Tinker:** `php artisan tinker`, type one line at a time, `exit` to leave.
- **Stylesheet (Module 8):**
  ```powershell
  New-Item -ItemType Directory -Force public\css
  Copy-Item "$env:USERPROFILE\Downloads\day3-kit-extract\day3-kit\school.css" public\css\
  ```
- **Routes:** `php artisan route:list --except-vendor`
- Expected results to verify: Andrea Bautista (student 4): average **86.7**, remark **INC**, Science Sem 2 shows **INC**. Rafael Castillo (student 11): **NO GRADES**. Carlo Ramos: **77.96 PASSED**.
- Commit after each module with the training's messages: `Models and relationships`, `Report card service`, `Routes and controllers`, `Report card pages`.
- No Tailwind and no `npm` anywhere in this lab (D4). If a page ever asks for Vite or `npm run dev`, the wrong layout/starter kit was used.

---

## Part 9: Review (Module 9)

The numbers cross-check against Day 2 uses a different database. The `school` database from Day 2 lives in whichever server you created it in. If it is in MySQL 8.0 on 3306, query it there with Laragon's client:

```powershell
mysql -u root -p -P 3306 -h 127.0.0.1 school -e "SELECT ROUND(AVG(grade), 2) FROM grades WHERE student_id = 4;"
```

If you don't have it, compare against the numbers in the training (Andrea 86.7, Carlo 77.96). The `report_cards` database is the source of truth for the app.

---

## Stretch tasks on Windows

- **Pest tests:** the default Laravel project uses PHPUnit. Either ask Claude Code to write the same tests in PHPUnit style, or run `composer require pestphp/pest --dev --with-all-dependencies` and `php artisan pest:install`. Tests use in-memory SQLite (`pdo_sqlite` is installed), so they never touch MariaDB.
- **Apache task:** skip it. It is Linux-specific (`apt`, `a2ensite`). Laragon already provides Apache and could serve the app later, but that is a separate topic.
- **Teacher page, N+1 detection, weighted average:** pure Laravel, no changes needed.

---

## Troubleshooting (Windows-specific)

| Symptom | Cause / fix |
|---|---|
| `Access denied for user 'laravel'@'localhost'` | Wrong `DB_USERNAME` or `DB_PASSWORD`, or the user wasn't created on the **MariaDB** server. Check that you ran `CREATE USER` against port 3309. |
| `SQLSTATE[HY000] [2002] No connection could be made...` | MariaDB service stopped, or `DB_PORT` in `.env` doesn't match. Check `Get-Service MariaDB` and `.env`. |
| `Unknown database 'report_cards'` | `CREATE DATABASE` was done on MySQL 3306 instead of MariaDB 3309, or the name is misspelled. |
| `Access denied` using **root** on 3306 | Expected. That is MySQL 8.0 with a different root password. Use port 3309 for MariaDB. |
| Laravel connects but data is in the "wrong" server | Your `.env` still has `DB_PORT=3306`. Fix to `3309`, run `php artisan config:clear`. |
| `mariadb` is not recognized | PATH wasn't refreshed. Reopen VS Code or use the full path in `C:\Program Files\MariaDB <version>\bin`. |
| `Table 'report_cards.sessions' doesn't exist` | `php artisan migrate` was not run yet. |
| `Foreign key constraint is incorrectly formed` | Migrations created in the wrong order. Roll back and recreate in order: Teacher, Subject, Section, Student, Grade. |
| `View [sections.index] not found` | Expected at the end of Module 7 (the Blade file doesn't exist yet). |
| `Address already in use` on `artisan serve` | Another `serve` is running. Use `php artisan serve --port=8001`. |
| Composer says `zip extension missing` | Harmless warning. To fix, open Laragon's `php.ini` and uncomment `extension=zip`. |
| `.env` changes seem ignored | Run `php artisan config:clear`. |
| Where are the errors? | `Get-Content storage/logs/laravel.log -Wait -Tail 20` |

## Quick pre-flight checklist (run before each session)

```powershell
Get-Service MariaDB        # Running
php -v                     # 8.3.x
mariadb -u laravel -psecret123 -P 3309 -h 127.0.0.1 report_cards -e "SELECT 1"
git status                 # clean, or only your intended changes
php artisan serve          # then open http://localhost:8000
```

## When to ask Claude Code (and how)

Per `CLAUDE.md`, Claude discusses the approach and waits for approval before writing code. Use the training's **Understand** prompt first, then the **Build** prompt, then read the diff. Never let Claude run commands that need administrator rights or your MariaDB root password. Run those yourself.
