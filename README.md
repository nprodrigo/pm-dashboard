# ProjectPulse - Project Progress & Pendings Tracking Dashboard (PHP & MySQL)

A modern executive dashboard built with **PHP** and **MySQL** to manage and track multiple project streams:
- 🚀 **Software Development**
- 📦 **Software Implementation**
- 🖥️ **Infrastructure Upgrades**
- 📄 **Documentation Projects**

Designed specifically to answer two critical management questions at a glance:
1. **"What finishes when?"** (Chronological target completion timeline & milestone roadmap)
2. **"What needs my attention?"** (Active project blockers, delayed deliverables, and urgent action items)

---

## 🛠️ Remote MySQL & Server Architecture

This application supports both local and **remote MySQL database hosting** (where the PHP application web server and MySQL database reside on separate machines).

### Database Configuration (`config/database.php`)

Adjust the connection settings in `config/database.php` or pass environment variables to your PHP runtime:

```php
define('DB_HOST', getenv('DB_HOST') ?: 'your-remote-db-server.com'); // IP address or hostname of remote MySQL server
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'project_tracker');
define('DB_USER', getenv('DB_USER') ?: 'db_username');
define('DB_PASS', getenv('DB_PASS') ?: 'db_password');
```

---

## 🚀 Quickstart Setup Instructions

### Step 1: Import Database Schema into Remote MySQL

Using MySQL CLI from your local or PHP server:
```bash
mysql -h <REMOTE_DB_HOST> -u <DB_USER> -p < sql/schema.sql
```
*Or import `sql/schema.sql` via **phpMyAdmin**, **MySQL Workbench**, or **DBeaver**.*

### Step 2: Configure Web Server / Run PHP

#### Option A: PHP Built-in Server (Local or Server CLI)
Navigate to the `project-tracker` directory and start the server:
```bash
cd project-tracker
php -S 0.0.0.0:8000
```
Open `http://localhost:8000` (or `http://<your-php-server-ip>:8000`) in your web browser.

#### Option B: Apache / Nginx / XAMPP / cPanel Hosting
1. Copy the `project-tracker` folder to your web server document root (e.g. `/var/www/html/` or `htdocs/`).
2. Ensure PDO MySQL extension is enabled in `php.ini`.
3. Open `http://your-domain.com/project-tracker` in your browser.

---

## 📊 Dashboard Views & Features

| View / Page | Key Functionality |
| :--- | :--- |
| **Executive Overview** (`index.php`) | High-level metrics, category pills, real-time search, interactive project cards, visual progress bars. |
| **All Projects Directory** (`projects.php`) | Tabular directory of projects with inline progress bars, completion dates, and owner badges. |
| **"What Finishes When"** (`timeline.php`) | Chronological roadmap grouping projects by target completion month (Overdue, This Month, Next Month, Future). |
| **"Needs Attention" Center** (`attention.php`) | Isolates projects flagged with active blockers and lists open action items/pendings requiring management action. |
| **Project Detail & Breakdown** (`project_detail.php`) | Full project breakdown, milestone checklist, progress slider update modal, and active pendings log. |

---

## 🔒 Remote MySQL Access Checklist

If your MySQL server is on a separate remote machine, ensure:
1. **Remote Port Access**: Port `3306` is open on the remote MySQL server firewall.
2. **User Privileges**: The MySQL user has remote host privileges granted:
   ```sql
   GRANT ALL PRIVILEGES ON project_tracker.* TO 'db_username'@'%' IDENTIFIED BY 'db_password';
   FLUSH PRIVILEGES;
   ```
