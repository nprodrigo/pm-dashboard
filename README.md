Here is an updated **`README.md`** file that reflects your expanded architecture, including **Business Units (BUs)**, **Daily Updates/Logs**, **Weekly Executive Reporting**, and the updated high-contrast light-slate UI.

You can replace your current `README.md` with the following content:

```markdown
# ProjectPulse - Multi-BU Project Tracker & Daily Update System

A modern, executive project management dashboard built with **PHP** and **MySQL** to manage, track, and log daily updates across multiple **Business Units (BUs)** and project categories:
- 🚀 **Software Development**
- 📦 **Software Implementation**
- 🖥️ **Infrastructure Upgrades**
- 📄 **Documentation Projects**

Designed to answer critical management questions at a glance:
1. **"What finishes when?"** (Chronological target completion timeline & milestone roadmap)
2. **"What needs my attention?"** (Active project blockers, delayed deliverables, and urgent action items)
3. **"What happened this week?"** (Automated weekly status rollups for management across all Business Units)

---

## 🛠️ Tech Stack & Server Architecture

- **Backend:** PHP 8.x (PDO MySQL extension required)
- **Database:** MySQL 5.7+ / MySQL 8.0+ / MariaDB (Supports both local and remote hosting)
- **Frontend:** Vanilla JS (`app.js`), FontAwesome 6, and a clean, high-contrast Slate CSS UI framework

### Database Configuration (`config/database.php`)

Connection settings can be configured directly in `config/database.php` or passed via environment variables:

```php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'project_tracker');
define('DB_USER', getenv('DB_USER') ?: 'db_username');
define('DB_PASS', getenv('DB_PASS') ?: 'db_password');

```

---

## 🚀 Quickstart Setup Instructions

### Step 1: Import Database Schema & Migration

Import `sql/schema.sql` (or run `update_schema.sql`) into your MySQL server via **phpMyAdmin**, **MySQL Workbench**, or the CLI:

```bash
mysql -h <DB_HOST> -u <DB_USER> -p project_tracker < sql/schema.sql

```

### Step 2: Configure Web Server

#### Option A: PHP Built-in Server (Local CLI)

Navigate to the project root directory and run:

```bash
php -S 0.0.0.0:8000

```

Open `http://localhost:8000` in your web browser.

#### Option B: Apache / Nginx / cPanel / Shared Hosting

1. Upload all project files to your server document root (e.g., `/var/www/html/project` or `public_html/project`).
2. Ensure `pdo_mysql` is enabled in your `php.ini`.
3. Access `http://your-domain.com/project/` in your browser.

---

## 📊 Dashboard Views & Modules

| View / File | Description & Functionality |
| --- | --- |
| **Executive Overview** (`index.php`) | High-level metrics, BU & category filtering, real-time search, interactive project cards, and blocker alerts. |
| **Daily Progress Workspace** (`daily_log.php`) | Single-page update workspace to log daily progress notes across projects and flag blockers instantly. |
| **Weekly Status Report** (`weekly_report.php`) | Aggregates daily updates from the past 7 days grouped by **Business Unit → Project**, ready for print/PDF export. |
| **All Projects Directory** (`projects.php`) | Tabular listing of projects with completion status, target completion dates, and project managers. |
| **"What Finishes When"** (`timeline.php`) | Chronological roadmap grouping projects by target completion month. |
| **"Needs Attention" Center** (`attention.php`) | Isolates projects flagged with active blockers and lists open action items requiring attention. |

---

## 🔒 Remote MySQL Troubleshooting Checklist

If hosting the database remotely:

1. **Firewall Access:** Ensure MySQL port `3306` is allowed on the remote database server.
2. **User Privileges:** Verify the user account allows remote access (`'db_username'@'%'`).
3. **Database Port Variable:** Ensure `DB_PORT` is correctly set in `config/database.php`.

```

```