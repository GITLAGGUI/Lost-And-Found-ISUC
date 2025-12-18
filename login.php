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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    }
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($errors) && empty($username)) {
        $errors[] = 'Username or email is required';
    } else if (empty($password)) {
        $errors[] = 'Password is required';
    } else {
        $user = $db->getUserByUsername($username);
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_active']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                $db->updateUserLastLogin($user['id']);
                $db->logActivity($user['id'], 'user_login', 'User logged in', get_client_ip());

                if ($remember) {
                    setcookie('remember_token', bin2hex(random_bytes(32)), time() + (86400 * 30), '/');
                }

                set_flash('success', 'Welcome back, ' . $user['full_name'] . '!');
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = 'Account is inactive. Please contact administrator.';
            }
        } else {
            $errors[] = 'Invalid username or password';
            $db->logActivity(null, 'login_failed', "Failed login attempt: $username", get_client_ip());
        }
    }
}

$pageTitle = 'Log in';
$mainClass = 'auth-main';
include __DIR__ . '/includes/header.php';
?>
<section class="form-card">
    <h1>Welcome back</h1>
    <p class="section-subtext">Use your ISU credentials to access your dashboard.</p>

    <?php if (!empty($errors)): ?>
        <div class="flash-message flash-error" style="position:static;margin-bottom:1rem">
            <div>
                <?php foreach ($errors as $error): ?>
                    <p style="margin:0.25rem 0"><?php echo e($error); ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <form action="login.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>" />
        <div class="form-group">
            <label for="username">Username or ISU Email</label>
            <input type="text" id="username" name="username" placeholder="juan.delacruz@isu.edu.ph" 
                   value="<?php echo e($_POST['username'] ?? ''); ?>" required autofocus />
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required />
            <a href="recover_password.php" class="forgot-link">Forgot password?</a>
        </div>
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="remember" />
                <span class="checkmark"></span>
                Remember me for 30 days
            </label>
        </div>
        <div class="form-group">
            <button type="submit" class="btn primary" style="width:100%">Log in</button>
        </div>
        <div class="card-meta">
            <a href="register.php">Need an account?</a>
        </div>
    </form>
</section>
