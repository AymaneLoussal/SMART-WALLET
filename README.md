# Smart Wallet

Simple PHP/MySQL personal finance web app.

## Features

- User authentication (register, login, OTP)
- Manage cards/accounts (add, list)
- Record incomes and expenses
- Transfer money between users' cards (transactional; logs as expense for sender and income for recipient)
- Set monthly salary to be auto-credited to a selected card once per month

## Quick setup

1. Install PHP, MySQL, and a local server (Laragon, XAMPP, etc.).
2. Place project in your webroot (already at this repo).
3. Import the schema in [database.sql](database.sql) into MySQL.
4. Update DB credentials in `config/db.php` if necessary.
5. Start server and visit `/auth/register.php` to create an account.

## Important files

- `config/db.php`: MySQLi connection used throughout app.
- `auth/`: `register.php`, `login.php`, `otp.php`, `logout.php` — authentication flow.
- `cards/transfer.php`: transfer UI and logic (transactional updates, records `transfers`, adds `expenses` and `incomes`).
- `cards/salary.php`: set monthly salary and choose the target card.
- `config/scheduler.php`: helper that credits monthly salary and ensures schema columns exist.
- `expenses/index.php`: lists expenses (including transfers — NULL category shown as "Transfer").
- `mailer/`: PHPMailer sources (ignored by git via `.gitignore`).

## How transfers and salaries are handled

- Transfers: when a user sends money, the code deducts the amount from sender's card, adds it to recipient's card, inserts a `transfers` row, creates an `expenses` row for the sender and an `incomes` row for the recipient — all inside a DB transaction for consistency.
- Salaries: users set `monthly_salary` and `salary_card_id` in `cards/salary.php`. The scheduler (`config/scheduler.php`) is invoked at login (after OTP) and credits the salary to the chosen card once per calendar month, inserting an `incomes` record and updating `last_salary_at` to avoid duplicates.