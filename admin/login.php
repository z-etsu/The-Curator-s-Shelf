<?php
require_once __DIR__ . '/../config/database.php';

session_start();

$error = '';

// Check if already logged in as admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    header('Location: /CURATOR/admin/dashboard.php');
    exit;
}

// Handle admin login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Hardcoded admin credentials
    $adminUsername = 'admin';
    $adminPassword = 'admin123';

    if ($username === $adminUsername && $password === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: /CURATOR/admin/dashboard.php');
        exit;
    } else {
        $error = 'Invalid admin credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CURATOR</title>
    <link rel="stylesheet" href="/CURATOR/assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            background: white;
            padding: 3rem 2rem;
            border-radius: 0;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 400px;
            border: 1px solid var(--border-color);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            font-family: 'Playfair Display', serif;
            font-weight: 400;
            letter-spacing: -0.5px;
        }

        .login-header p {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.95rem;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
        }

        .login-container .btn {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .login-container .btn:hover {
            background-color: #1a1a1a;
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .login-container .btn:active {
            transform: translateY(0);
        }

        .alert {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 1rem;
            border-radius: 0;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
            font-weight: 500;
        }

        .back-link a:hover {
            color: var(--text-light);
        }

        .credentials-info {
            background-color: var(--accent-color);
            border: 1px solid var(--border-color);
            color: var(--text-dark);
            padding: 1rem;
            border-radius: 0;
            font-size: 0.85rem;
            margin-top: 1.5rem;
            line-height: 1.6;
        }

        .credentials-info strong {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>CURATOR</h1>
            <p>Admin Login</p>
        </div>

        <?php if ($error): ?>
            <div class="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn">Login as Admin</button>
        </form>

        <div class="back-link">
            <a href="/CURATOR/index.php">Back to Store</a>
        </div>

        <div class="credentials-info">
            <strong>Demo Credentials:</strong>
            Username: admin<br>
            Password: admin123
        </div>
    </div>
</body>
</html>
