<?php
require_once __DIR__ . '/config/database.php';

$db = getDBConnection();
if (!$db) {
    die("Database connection failed.");
}

$newHash = password_hash('Password123!', PASSWORD_BCRYPT);

$stmt = $db->prepare("UPDATE users SET password = :pass WHERE username IN ('admin', 'niro')");
$stmt->execute(['pass' => $newHash]);

echo "<div style='font-family: Arial; padding: 2rem; color: #34d399; background: #0f172a;'>";
echo "✓ Passwords for <strong>admin</strong> and <strong>niro</strong> have been reset to <code>Password123!</code><br><br>";
echo "<a href='login.php' style='color: #6366f1; text-decoration: underline;'>Go to Login Page &rarr;</a>";
echo "</div>";