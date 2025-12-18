<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$pageTitle = 'Live Activity - Admin Control Center';
$bodyClass = 'admin-body';
$mainClass = 'admin-main';

$current_user = get_logged_in_user();
$recentActivity = $db->getRecentActivity(20); // Show more activities since it's a dedicated page
$recentUsers = $db->getRecentUsers(4);
$extraScripts = [app_url('assets/js/admin.js')];
$currentInitials = strtoupper(substr($current_user['full_name'] ?? $current_user['username'], 0, 2));

include __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
    <?php $activePage = 'activity'; include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-shell" data-js="admin-shell">
            <section class="admin-panels two-column">
                <article class="admin-panel" data-animate="slide">
                    <header>
                        <h2>Live activity</h2>
                        <p>System events logged for transparency.</p>
                    </header>
                    <ul class="activity-timeline">
                        <?php if (empty($recentActivity)): ?>
                            <li class="empty-state">No activity yet.</li>
                        <?php else: ?>
                            <?php foreach ($recentActivity as $log): ?>
                                <li>
                                    <span class="bullet"></span>
                                    <div>
                                        <strong><?php echo e(str_replace('_', ' ', $log['action'])); ?></strong>
                                        <p><?php echo e($log['details']); ?></p>
                                        <small><?php echo format_date($log['created_at']); ?> · <?php echo e($log['username'] ?? 'System'); ?></small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </article>

                <article id="people" class="admin-panel" data-animate="slide">
                    <header>
                        <h2>Newest verified users</h2>
                        <p>Quick glance at who just joined.</p>
                    </header>
                    <ul class="user-list">
                        <?php if (empty($recentUsers)): ?>
                            <li class="empty-state">No registered users yet.</li>
                        <?php else: ?>
                            <?php foreach ($recentUsers as $user): ?>
                                <?php $initials = strtoupper(substr($user['full_name'], 0, 1)); ?>
                                <li>
                                    <span class="avatar" aria-hidden="true"><?php echo e($initials); ?></span>
                                    <div>
                                        <strong><?php echo e($user['full_name']); ?></strong>
                                        <small><?php echo e($user['email']); ?></small>
                                    </div>
                                    <?php if ($user['is_admin']): ?>
                                        <span class="metric-chip info">Admin</span>
                                    <?php else: ?>
                                        <span class="metric-chip muted">User</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </article>
            </section>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>