<?php
/**
 * ProjectPulse Database Migration & Auto-Update Script
 * 
 * Safely updates database schema without overwriting existing data.
 */

require_once __DIR__ . '/config/database.php';

// Turn on error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$db = getDBConnection();

if (!$db) {
    die("<div style='color:red; font-weight:bold; padding:2rem;'>Migration Failed: Could not connect to database. Check config/database.php</div>");
}

echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>Database Migration</title>";
echo "<style>
        body { font-family: Arial, sans-serif; background: #0f172a; color: #f8fafc; padding: 2rem; line-height: 1.6; }
        .card { background: #1e293b; border: 1px solid rgba(255,255,255,0.1); padding: 1.5rem; border-radius: 8px; max-width: 800px; margin: 0 auto; }
        .success { color: #34d399; font-weight: bold; }
        .info { color: #38bdf8; }
        .error { color: #fb7185; }
        .btn { display: inline-block; background: #6366f1; color: #fff; text-decoration: none; padding: 0.6rem 1.2rem; border-radius: 6px; margin-top: 1rem; font-weight: bold; }
      </style></head><body><div class='card'>";

echo "<h2>ProjectPulse Database Auto-Migration</h2><hr style='border-color: rgba(255,255,255,0.1); margin-bottom: 1.5rem;'>";

try {
    // 1. Business Units Table
    $db->exec("CREATE TABLE IF NOT EXISTS `business_units` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `code` VARCHAR(20) NOT NULL UNIQUE,
        `name` VARCHAR(100) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "<p class='info'>✓ Business Units table verified.</p>";

    // Seed Business Units
    $db->exec("INSERT INTO `business_units` (`id`, `code`, `name`) VALUES
        (1, 'FIN', 'Finance & Operations'),
        (2, 'IT', 'IT & Infrastructure'),
        (3, 'CUST', 'Customer Success & Retail'),
        (4, 'COMP', 'Security & Compliance')
        ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);");
    echo "<p class='info'>✓ Business Units seed data verified.</p>";

    // 2. Team Members Table
    $db->exec("CREATE TABLE IF NOT EXISTS `team_members` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `full_name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(120) NOT NULL UNIQUE,
        `role_title` VARCHAR(100) DEFAULT 'Team Member',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "<p class='info'>✓ Team Members table verified.</p>";

    // Seed Sample Team Members
    $db->exec("INSERT INTO `team_members` (`id`, `full_name`, `email`, `role_title`) VALUES
        (1, 'Sarah Jenkins', 'sarah.j@company.com', 'Senior Project Manager'),
        (2, 'Alex Rivera', 'alex.r@company.com', 'Lead Software Engineer'),
        (3, 'David Chen', 'david.c@company.com', 'DevOps / Infrastructure Engineer'),
        (4, 'Elena Rostova', 'elena.r@company.com', 'Technical Writer & Compliance Lead')
        ON DUPLICATE KEY UPDATE `full_name` = VALUES(`full_name`);");
    echo "<p class='info'>✓ Team Members seed data verified.</p>";

    // 3. Update Projects Table Columns (bu_id, manager_id)
    $columns = $db->query("SHOW COLUMNS FROM `projects`")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('bu_id', $columns)) {
        $db->exec("ALTER TABLE `projects` ADD COLUMN `bu_id` INT NULL AFTER `category_id`;");
        $db->exec("ALTER TABLE `projects` ADD CONSTRAINT `fk_projects_bu` FOREIGN KEY (`bu_id`) REFERENCES `business_units`(`id`) ON DELETE SET NULL;");
        echo "<p class='info'>✓ Column 'bu_id' added to projects table.</p>";
    }

    if (!in_array('manager_id', $columns)) {
        $db->exec("ALTER TABLE `projects` ADD COLUMN `manager_id` INT NULL AFTER `bu_id`;");
        $db->exec("ALTER TABLE `projects` ADD CONSTRAINT `fk_projects_manager` FOREIGN KEY (`manager_id`) REFERENCES `team_members`(`id`) ON DELETE SET NULL;");
        echo "<p class='info'>✓ Column 'manager_id' added to projects table.</p>";
    }

    // 4. Project Teams Junction Table
    $db->exec("CREATE TABLE IF NOT EXISTS `project_teams` (
        `project_id` INT NOT NULL,
        `member_id` INT NOT NULL,
        PRIMARY KEY (`project_id`, `member_id`),
        FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`member_id`) REFERENCES `team_members`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "<p class='info'>✓ Project Teams junction table verified.</p>";

    // 5. Daily Logs Table
    $db->exec("CREATE TABLE IF NOT EXISTS `daily_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `project_id` INT NOT NULL,
        `log_text` TEXT NOT NULL,
        `is_blocked` TINYINT(1) NOT NULL DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "<p class='info'>✓ Daily Logs table verified.</p>";

    // 6. Tasks Table
    $db->exec("CREATE TABLE IF NOT EXISTS `tasks` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `project_id` INT NOT NULL,
        `assigned_to` INT NULL,
        `title` VARCHAR(200) NOT NULL,
        `description` TEXT NULL,
        `priority` ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
        `status` ENUM('To Do', 'In Progress', 'Under Review', 'Completed') DEFAULT 'To Do',
        `due_date` DATE NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`assigned_to`) REFERENCES `team_members`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "<p class='info'>✓ Tasks table verified.</p>";

    echo "<hr style='border-color: rgba(255,255,255,0.1); margin: 1.5rem 0;'>";
    echo "<p class='success'>Database Schema Updated Successfully!</p>";
    echo "<a href='index.php' class='btn'>Go to Dashboard &rarr;</a>";

} catch (PDOException $e) {
    echo "<p class='error'>Migration Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div></body></html>";