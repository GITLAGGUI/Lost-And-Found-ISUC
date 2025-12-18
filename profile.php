<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$pageTitle = 'Profile Settings';
$current_user = get_logged_in_user();
$profileInitials = strtoupper(substr($current_user['full_name'] ?? $current_user['username'], 0, 2));
$memberSince = format_date($current_user['created_at']);
$lastLoginLabel = $current_user['last_login'] ? format_date($current_user['last_login']) : 'No logins yet';
$roleLabel = $current_user['is_admin'] ? 'Administrator' : 'Community Member';

$view = $_GET['view'] ?? 'overview';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Invalid request. Please try again.');
        header('Location: profile.php');
        exit;
    }
    
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        // Validation
        $errors = [];
        if (empty($full_name)) {
            $errors[] = 'Full name is required.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required.';
        }
        if (!empty($phone) && !preg_match('/^[0-9+\-\s()]+$/', $phone)) {
            $errors[] = 'Invalid phone number format.';
        }

        if (empty($errors)) {
            if ($db->updateUserProfile($_SESSION['user_id'], $full_name, $email, $phone)) {
                $db->logActivity($_SESSION['user_id'], 'profile_updated', 'Updated profile information', get_client_ip());
                set_flash('success', 'Profile updated successfully!');
                header('Location: profile.php?view=personal');
                exit;
            } else {
                set_flash('error', 'Failed to update profile. Please try again.');
            }
        } else {
            set_flash('error', implode('<br>', $errors));
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validation
        $errors = [];
        if (empty($current_password)) {
            $errors[] = 'Current password is required.';
        }
        if (empty($new_password)) {
            $errors[] = 'New password is required.';
        } elseif (!is_strong_password($new_password)) {
            $errors[] = 'Password must be at least 8 characters long and contain uppercase, lowercase, and numbers.';
        }
        if ($new_password !== $confirm_password) {
            $errors[] = 'New passwords do not match.';
        }

        if (empty($errors)) {
            // Verify current password
            $user = $db->getUserById($_SESSION['user_id']);
            if (password_verify($current_password, $user['password'])) {
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                if ($db->updateUserPassword($_SESSION['user_id'], $new_password_hash)) {
                    $db->logActivity($_SESSION['user_id'], 'password_changed', 'Changed account password', get_client_ip());
                    set_flash('success', 'Password changed successfully!');
                    header('Location: profile.php?view=password');
                    exit;
                } else {
                    set_flash('error', 'Failed to change password. Please try again.');
                }
            } else {
                set_flash('error', 'Current password is incorrect.');
            }
        } else {
            set_flash('error', implode('<br>', $errors));
        }
    }
}

include __DIR__ . '/includes/header.php';
?>



<?php
$flash = get_flash();
if ($flash): ?>
    <div class="alert alert-<?php echo $flash['type']; ?>">
        <?php echo $flash['message']; ?>
    </div>
<?php endif; ?>

<div class="profile-layout" data-profile-shell>
    <div class="profile-menu-overlay" data-profile-overlay></div>
    <button class="profile-menu-toggle" type="button" aria-label="Toggle profile menu" aria-expanded="false" data-profile-toggle>
        <svg class="icon-menu" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="6" x2="20" y2="6" /><line x1="4" y1="12" x2="20" y2="12" /><line x1="4" y1="18" x2="20" y2="18" /></svg>
        <svg class="icon-close" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" /></svg>
    </button>
    <aside class="profile-menu" aria-label="Profile navigation" data-profile-menu>
        <p class="profile-menu__label">Quick sections</p>
        <nav class="profile-menu__nav">
            <a href="profile.php?view=overview" class="profile-menu__link <?php echo $view === 'overview' ? 'active' : ''; ?>">Overview</a>
            <a href="profile.php?view=personal" class="profile-menu__link <?php echo $view === 'personal' ? 'active' : ''; ?>">Personal Information</a>
            <a href="profile.php?view=password" class="profile-menu__link <?php echo $view === 'password' ? 'active' : ''; ?>">Change Password</a>
            <a href="profile.php?view=account" class="profile-menu__link <?php echo $view === 'account' ? 'active' : ''; ?>">Account Information</a>
        </nav>
        <p class="profile-menu__hint">Tap to jump between forms without endless scrolling.</p>
    </aside>

    <div class="profile-content card-grid profile-grid">
    <?php if ($view === 'personal'): ?>
    <article id="personal-info" class="card profile-section">
        <div class="card-header">
            <h3><i class="fas fa-user-edit"></i> Personal Information</h3>
        </div>
        <div class="card-content">
            <form method="post" class="form-card">
                <input type="hidden" name="action" value="update_profile" />
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>" />

                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo e($current_user['full_name']); ?>" required />
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" value="<?php echo e($current_user['username']); ?>" readonly disabled />
                    <small class="form-help">Username cannot be changed</small>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?php echo e($current_user['email']); ?>" required />
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo e($current_user['phone'] ?? ''); ?>" />
                </div>

                <div class="form-group">
                    <label for="student_id">Student/Staff ID</label>
                    <input type="text" id="student_id" value="<?php echo e($current_user['student_staff_id'] ?? ''); ?>" readonly disabled />
                    <small class="form-help">ID cannot be changed</small>
                </div>

                <button class="btn primary" type="submit">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>
        </div>
    </article>
    <?php endif; ?>

    <?php if ($view === 'password'): ?>
    <article id="change-password" class="card profile-section">
        <div class="card-header">
            <h3><i class="fas fa-lock"></i> Change Password</h3>
        </div>
        <div class="card-content">
            <form method="post" class="form-card">
                <input type="hidden" name="action" value="change_password" />
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>" />

                <div class="form-group">
                    <label for="current_password">Current Password *</label>
                    <input type="password" id="current_password" name="current_password" required />
                </div>

                <div class="form-group">
                    <label for="new_password">New Password *</label>
                    <input type="password" id="new_password" name="new_password" required />
                    <small class="form-help">Must be at least 8 characters with uppercase, lowercase, and numbers</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required />
                </div>

                <button class="btn primary" type="submit">
                    <i class="fas fa-key"></i> Change Password
                </button>
            </form>
        </div>
    </article>
    <?php endif; ?>

    <?php if ($view === 'account'): ?>
    <article id="account-info" class="card profile-section">
        <div class="card-header">
            <h3><i class="fas fa-info-circle"></i> Account Information</h3>
        </div>
        <div class="card-content">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Account Created</span>
                    <span class="info-value"><?php echo format_date($current_user['created_at']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Last Login</span>
                    <span class="info-value"><?php echo $current_user['last_login'] ? format_date($current_user['last_login']) : 'Never'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Account Type</span>
                    <span class="info-value"><?php echo $current_user['is_admin'] ? 'Administrator' : 'Regular User'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email Verified</span>
                    <span class="info-value">
                        <span class="status-chip status-success">
                            <i class="fas fa-check"></i> Verified
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </article>
    <?php endif; ?>

    <?php if ($view === 'overview'): ?>
    <section class="profile-hero page-hero">
        <div class="profile-hero__content">
            <p class="eyebrow" style="color:white;">Profile &amp; Security</p>
            <h1>Keep your ISU profile current</h1>
            <p class="section-subtext" style="color:white;">Fine-tune your contact details, keep your password strong, and make sure campus updates reach you instantly.</p>
            <div class="profile-hero__chips">
                <span class="profile-pill">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" /></svg>
                    Member since <?php echo e($memberSince); ?>
                </span>
                <span class="profile-pill subtle">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg>
                    Last login: <?php echo e($lastLoginLabel); ?>
                </span>
            </div>
        </div>
        <div class="profile-hero__card">
            <div class="profile-avatar" aria-hidden="true"><?php echo e($profileInitials); ?></div>
            <ul class="profile-meta-list">
                <li>
                    <span>Primary email</span>
                    <strong><?php echo e($current_user['email']); ?></strong>
                </li>
                <li>
                    <span>Phone number</span>
                    <strong><?php echo e($current_user['phone'] ?: 'No phone yet'); ?></strong>
                </li>
                <li>
                    <span>Account role</span>
                    <strong><?php echo e($roleLabel); ?></strong>
                </li>
                <li>
                    <span>ID reference</span>
                    <strong><?php echo e($current_user['student_staff_id'] ?? 'N/A'); ?></strong>
                </li>
            </ul>
        </div>
    </section>
    <?php endif; ?>
    </div>
    </div>
</div>

<script>
(function() {
    const toggle = document.querySelector('[data-profile-toggle]');
    const menu = document.querySelector('[data-profile-menu]');
    const overlay = document.querySelector('[data-profile-overlay]');
    const links = document.querySelectorAll('.profile-menu__link');

    if (!toggle || !menu) return;

    const openMenu = () => {
        menu.classList.add('is-visible');
        overlay && overlay.classList.add('is-visible');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('profile-menu-open');
    };

    const closeMenu = () => {
        menu.classList.remove('is-visible');
        overlay && overlay.classList.remove('is-visible');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('profile-menu-open');
    };

    toggle.addEventListener('click', () => {
        if (menu.classList.contains('is-visible')) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    overlay && overlay.addEventListener('click', closeMenu);
    links.forEach(link => link.addEventListener('click', closeMenu));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>