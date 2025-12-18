<?php
require_once __DIR__ . '/includes/functions.php';

// Prevent cache issues on back button
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    }
    
    $full_name = trim($_POST['full_name'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation (only if CSRF passed)
    if (empty($errors)) {
        if (empty($full_name)) $errors[] = 'Full name is required';
        if (empty($student_id)) $errors[] = 'Student/Staff ID is required';
        if (empty($email)) $errors[] = 'Email is required';
        else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
        else if (!is_isu_email($email)) $errors[] = 'Must use ISU email (@isu.edu.ph)';
        if (empty($username)) $errors[] = 'Username is required';
        else if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters';
        if (empty($password)) $errors[] = 'Password is required';
        else if (!is_strong_password($password)) $errors[] = 'Password must be at least 8 characters with uppercase, lowercase, and number';
        if ($password !== $confirm_password) $errors[] = 'Passwords do not match';
    }

    if (empty($errors)) {
        // Check if username or email exists
        if ($db->getUserByUsername($username)) {
            $errors[] = 'Username already taken';
        } else if ($db->getUserByUsername($email)) {
            $errors[] = 'Email already registered';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            if ($db->createUser($full_name, $username, $email, $student_id, $phone, $password_hash)) {
                $db->logActivity(null, 'user_registered', "New user: $username", get_client_ip());
                set_flash('success', 'Account created successfully! Please log in.');
                header('Location: login.php');
                exit;
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
}

$pageTitle = 'Register';
$mainClass = 'auth-main';
include __DIR__ . '/includes/header.php';
?>
<section class="form-card">
    <h1>Create ISU account</h1>
    <p class="section-subtext">All registrations require an @isu.edu.ph email to keep the board safe.</p>
    
    <?php if (!empty($errors)): ?>
        <div class="flash-message flash-error" style="position:static;margin-bottom:1rem">
            <div>
                <?php foreach ($errors as $error): ?>
                    <p style="margin:0.25rem 0"><?php echo e($error); ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <form action="register.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>" />
        <div class="grid-two">
            <div class="form-group">
                <label for="full_name">Full name *</label>
                <input type="text" id="full_name" name="full_name" placeholder="Juan Dela Cruz" 
                       value="<?php echo e($_POST['full_name'] ?? ''); ?>" required />
            </div>
            <div class="form-group">
                <label for="student_id">Student / Staff ID *</label>
                <input type="text" id="student_id" name="student_id" placeholder="2025-12345" 
                       value="<?php echo e($_POST['student_id'] ?? ''); ?>" required />
            </div>
        </div>
        <div class="grid-two">
            <div class="form-group">
                <label for="email">ISU Email *</label>
                <input type="email" id="email" name="email" placeholder="name@isu.edu.ph" 
                       value="<?php echo e($_POST['email'] ?? ''); ?>" required />
            </div>
            <div class="form-group">
                <label for="phone">Phone (optional)</label>
                <input type="tel" id="phone" name="phone" placeholder="+63" 
                       value="<?php echo e($_POST['phone'] ?? ''); ?>" />
            </div>
        </div>
        <div class="grid-two">
            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" id="username" name="username" placeholder="isustudent" 
                       value="<?php echo e($_POST['username'] ?? ''); ?>" required />
            </div>
        </div>
        <div class="grid-two">
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" placeholder="Minimum 8 characters" required />
                <small style="color:var(--clr-muted)">Must include uppercase, lowercase, and number</small>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm password *</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required />
            </div>
        </div>
        <div class="form-group">
            <button type="submit" class="btn primary" style="width:100%">Create account</button>
        </div>
        <p style="text-align:center;margin:0.25rem 0 0;">Already registered? <a href="login.php">Log in</a></p>
    </form>
</section>
