<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

startSession();

$errors = [];
$formData = ['name' => '', 'email' => ''];

// If already logged in, redirect to home
if (isLoggedIn()) {
    redirect('/index.php');
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $formData = ['name' => $name, 'email' => $email];

    // Validate inputs
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    if (empty($email) || !validateEmail($email)) {
        $errors[] = 'Valid email is required';
    }
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    if (!validatePassword($password)) {
        $errors[] = 'Password must be at least 6 characters';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'This email is already registered';
        }
    }

    // Create account
    if (empty($errors)) {
        $passwordHash = hashPassword($password);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        
        try {
            $stmt->execute([$name, $email, $passwordHash, 'customer']);
            
            // Auto-login the user
            $userId = $pdo->lastInsertId();
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'customer';

            redirect('/index.php');
        } catch (PDOException $e) {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}
?>

<div class="auth-container">
    <h2>Create Account</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($formData['name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <button type="submit" class="btn" style="width: 100%;">Sign Up</button>
    </form>

    <div class="auth-link">
        Already have an account? <a href="/auth/login.php">Login here</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
