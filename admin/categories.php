<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$pageTitle = 'Categories - Admin Control Center';
$bodyClass = 'admin-body';
$mainClass = 'admin-main';

$current_user = get_logged_in_user();
$categoryBreakdown = $db->getCategoryBreakdown();
$extraScripts = [app_url('assets/js/admin.js')];
$currentInitials = strtoupper(substr($current_user['full_name'] ?? $current_user['username'], 0, 2));

$categoryTotal = array_sum(array_map(fn($row) => (int) $row['total'], $categoryBreakdown));

include __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
    <?php $activePage = 'categories'; include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-shell" data-js="admin-shell">
            <section class="admin-panels single-panel">
                <article class="admin-panel" data-animate="slide">
                    <header>
                        <h2>Category momentum</h2>
                        <p>Spot patterns without loading heavy charts.</p>
                    </header>
                    <ul class="category-list">
                        <?php if (empty($categoryBreakdown)): ?>
                            <li class="empty-state">No category data yet.</li>
                        <?php else: ?>
                            <?php foreach ($categoryBreakdown as $row): ?>
                                <?php
                                    $percent = $categoryTotal > 0 ? round(($row['total'] / $categoryTotal) * 100) : 0;
                                ?>
                                <li>
                                    <div>
                                        <strong><?php echo e($row['category']); ?></strong>
                                        <small><?php echo $row['total']; ?> reports</small>
                                    </div>
                                    <div class="category-bar">
                                        <span style="width: <?php echo $percent; ?>%"></span>
                                    </div>
                                    <span class="metric-chip accent"><?php echo $percent; ?>%</span>
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