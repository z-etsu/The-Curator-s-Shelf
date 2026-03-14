<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

startSession();

$errors = [];
$email = '';
$showSuccessModal = false;

// If already logged in, redirect to home
if (isLoggedIn()) {
    redirect('/CURATOR/index.php');
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate inputs
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    if (empty($password)) {
        $errors[] = 'Password is required';
    }

    // Check credentials
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id, email, password_hash, first_name, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && verifyPassword($password, $user['password_hash'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_first_name'] = $user['first_name'];
            $_SESSION['user_role'] = $user['role'];
            $showSuccessModal = true;
        } else {
            $errors[] = 'Invalid email or password';
        }
    }
}
?>

<?php if ($showSuccessModal): ?>
<div class="modal-overlay" id="successModal">
    <div class="modal">
        <div class="modal-content">
            <h3>Login Successful!</h3>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_first_name']); ?>!</p>
            <button class="btn" onclick="window.location.href='/CURATOR/index.php'">Go to Home</button>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            window.location.href = '/CURATOR/index.php';
        }, 2000);
    });
</script>
<?php else: ?>

<div class="auth-container">
    <h2>Login</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" autocomplete="off" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="new-password" required>
        </div>

        <button type="submit" class="btn" style="width: 100%;">Login</button>
    </form>

    <div class="auth-link">
        Don't have an account? <a href="register.php">Sign up here</a>
    </div>
</div>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
