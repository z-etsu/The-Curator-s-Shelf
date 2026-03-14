<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

startSession();

// If not logged in, redirect to login
if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

$currentUser = getCurrentUser();
$errors = [];
$success = false;

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($currentPassword)) {
        $errors[] = 'Current password is required';
    }
    if (empty($newPassword)) {
        $errors[] = 'New password is required';
    }
    if (!validatePassword($newPassword)) {
        $errors[] = 'New password must be at least 6 characters';
    }
    if ($newPassword !== $confirmPassword) {
        $errors[] = 'New passwords do not match';
    }

    // Verify current password
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$currentUser['id']]);
        $user = $stmt->fetch();

        if (!verifyPassword($currentPassword, $user['password_hash'])) {
            $errors[] = 'Current password is incorrect';
        }
    }

    // Update password
    if (empty($errors)) {
        $newHash = hashPassword($newPassword);
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        
        try {
            $stmt->execute([$newHash, $currentUser['id']]);
            $success = true;
        } catch (PDOException $e) {
            $errors[] = 'Error updating password. Please try again.';
        }
    }
}
?>

<div class="settings-container" style="max-width: 600px; margin: 3rem auto; padding: 0 2rem;">
    <h2 style="font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 2rem;">Account Settings</h2>

    <!-- Account Info Section -->
    <div style="background-color: #FFFFFF; padding: 2rem; margin-bottom: 2rem; box-shadow: var(--shadow);">
        <h3 style="font-family: 'Playfair Display', serif; font-size: 1.2rem; margin-bottom: 1.5rem;">Account Information</h3>
        <div style="margin-bottom: 1.5rem;">
            <p style="color: var(--text-light); margin-bottom: 0.5rem;">Email</p>
            <p style="font-weight: 600; font-size: 1.05rem;"><?php echo htmlspecialchars($currentUser['email']); ?></p>
        </div>
        <div style="margin-bottom: 1.5rem;">
            <p style="color: var(--text-light); margin-bottom: 0.5rem;">Name</p>
            <p style="font-weight: 600; font-size: 1.05rem;"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></p>
        </div>
        <div>
            <p style="color: var(--text-light); margin-bottom: 0.5rem;">Member Since</p>
            <p style="font-weight: 600; font-size: 1.05rem;">2026</p>
        </div>
    </div>

    <!-- Change Password Section -->
    <div style="background-color: #FFFFFF; padding: 2rem; box-shadow: var(--shadow);">
        <h3 style="font-family: 'Playfair Display', serif; font-size: 1.2rem; margin-bottom: 1.5rem;">Change Password</h3>

        <?php if ($success): ?>
            <div class="alert" style="background-color: #d4edda; border: 1px solid #c3e6cb; padding: 1rem; margin-bottom: 1.5rem; border-radius: 0; color: #155724;">
                <p>Password changed successfully!</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" autocomplete="new-password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required>
            </div>

            <button type="submit" class="btn" style="width: 100%;">Update Password</button>
        </form>
    </div>

    <div style="text-align: center; margin-top: 2rem;">
        <a href="/CURATOR/index.php" style="color: #000000; text-decoration: none; font-weight: 600;">← Back to Home</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
