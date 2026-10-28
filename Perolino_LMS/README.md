# Perolino LMS (Library Management System)

PHP + MySQL (XAMPP/WAMP friendly).

## Setup
1. Copy the `Perolino_LMS` folder into `htdocs` (XAMPP) or `www` (WAMP).
2. Start Apache and MySQL, open phpMyAdmin and import `database/schema.sql`
   (creates the `Perolino_LMS_db` database and tables).
3. Adjust credentials in `config/Database.php` if needed (default: root, no password).
4. Visit `http://localhost/Perolino_LMS/`.

## Features
- Dashboard with totals and active borrows
- Students: add, edit, delete, search
- Books: add, edit, delete, search, copy tracking
- Borrow/return with due dates, overdue detection, availability checks
