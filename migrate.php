// 7. Users Table for Authentication
    $db->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `full_name` VARCHAR(100) NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // Seed admin and niro user accounts
    $defaultHash = '$2y$10$p0Jd4M/fS4AemF4zAnC5a.5NByyO40OToR8B/qJk.Xp3b3e0k1w3y'; // Password123!
    $stmtU = $db->prepare("INSERT INTO `users` (`username`, `full_name`, `password`) VALUES
        ('admin', 'System Administrator', :pass),
        ('niro', 'Niroshan', :pass)
        ON DUPLICATE KEY UPDATE `full_name` = VALUES(`full_name`);");
    $stmtU->execute(['pass' => $defaultHash]);
    echo "<p class='info'>✓ Users table and default user accounts (admin, niro) verified.</p>";