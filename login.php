<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | ProjectPulse Tracker</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: var(--bg-dark); }
    .login-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 2.5rem; width: 100%; max-width: 400px; box-shadow: 0 20px 40px rgba(0,0,0,0.6); }
  </style>
</head>
<body>

<div class="login-card">
  <div style="text-align: center; margin-bottom: 2rem;">
    <h2 style="color: #fff; font-size: 1.5rem; font-weight: 700;">ProjectPulse Login</h2>
    <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.3rem;">Sign in to access authorized dashboard data.</p>
  </div>

  <?php if (!empty($error)): ?>
    <div style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.3); color: #fca5a5; padding: 0.75rem; border-radius: 6px; font-size: 0.85rem; margin-bottom: 1.5rem;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="login.php">
    <div class="form-group" style="margin-bottom: 1.25rem;">
      <label class="form-label" style="color: var(--text-muted); font-size: 0.85rem; display: block; margin-bottom: 0.4rem;">Username</label>
      <input type="text" name="username" class="form-input" style="width: 100%;" placeholder="e.g. niro" required autofocus>
    </div>

    <div class="form-group" style="margin-bottom: 1.5rem;">
      <label class="form-label" style="color: var(--text-muted); font-size: 0.85rem; display: block; margin-bottom: 0.4rem;">Password</label>
      <input type="password" name="password" class="form-input" style="width: 100%;" placeholder="••••••••" required>
    </div>

    <button type="submit" class="btn-primary-action" style="width: 100%; justify-content: center; padding: 0.75rem;">
      Sign In
    </button>
  </form>
</div>

</body>
</html>