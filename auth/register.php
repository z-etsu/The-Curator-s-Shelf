<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

startSession();

$errors = [];
$formData = ['first_name' => '', 'last_name' => '', 'email' => ''];
$showSuccessModal = false;

// If already logged in, redirect to home
if (isLoggedIn()) {
    redirect('/index.php');
}

// Validation function for names - only letters and spaces
function validateName($name) {
    return !empty($name) && preg_match('/^[a-zA-Z\s\'-]+$/', $name) && strlen($name) >= 2;
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $formData = ['first_name' => $first_name, 'last_name' => $last_name, 'email' => $email];

    // Validate inputs
    if (!validateName($first_name)) {
        $errors[] = 'First name must contain only letters, spaces, hyphens, and apostrophes';
    }
    if (!validateName($last_name)) {
        $errors[] = 'Last name must contain only letters, spaces, hyphens, and apostrophes';
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
        $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)');
        
        try {
            $stmt->execute([$first_name, $last_name, $email, $passwordHash, 'customer']);
            $showSuccessModal = true;
        } catch (PDOException $e) {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}
?>

<?php if ($showSuccessModal): ?>
<div class="modal-overlay" id="successModal">
    <div class="modal">
        <div class="modal-content">
            <h3>Account Created Successfully!</h3>
            <p>Your account has been registered. You can now log in with your email and password.</p>
            <button class="btn" onclick="window.location.href='login.php'">Go to Login</button>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Keep the modal visible, redirect after a short delay
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 2000);
    });
</script>
<?php else: ?>

<div class="auth-container">
    <h2>Create Account</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <div class="form-row">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($formData['first_name']); ?>" autocomplete="off" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($formData['last_name']); ?>" autocomplete="off" required>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>" autocomplete="off" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="off" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" autocomplete="off" required>
        </div>

        <button type="submit" class="btn" style="width: 100%;">Sign Up</button>
    </form>

    <div class="auth-link">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
