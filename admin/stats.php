<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$pageTitle = 'Performance - Admin Control Center';
$bodyClass = 'admin-body';
$mainClass = 'admin-main';

$current_user = get_logged_in_user();
$stats = $db->getStats();
$extraScripts = [app_url('assets/js/admin.js')];
$currentInitials = strtoupper(substr($current_user['full_name'] ?? $current_user['username'], 0, 2));

$openTickets = ($stats['lost_active'] ?? 0) + ($stats['found_available'] ?? 0);
$resolvedTickets = ($stats['claimed'] ?? 0) + ($stats['claimed'] ?? 0);
$totalTickets = max(1, $openTickets + $resolvedTickets);
$resolutionRate = round(($resolvedTickets / $totalTickets) * 100);

include __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
    <?php $activePage = 'stats'; include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-shell" data-js="admin-shell">
            <section class="admin-metrics-grid" aria-label="Key metrics">
        <article class="admin-metric" data-animate="pop">
            <div>
                <p>Open lost posts</p>
                <strong data-metric="true" data-target="<?php echo $stats['lost_active'] ?? 0; ?>">0</strong>
            </div>
            <span class="metric-chip success">+12% week</span>
        </article>
        <article class="admin-metric" data-animate="pop">
            <div>
                <p>Unclaimed found items</p>
                <strong data-metric="true" data-target="<?php echo $stats['found_available'] ?? 0; ?>">0</strong>
            </div>
            <span class="metric-chip warning">+8% week</span>
        </article>
        <article class="admin-metric" data-animate="pop">
            <div>
                <p>Successful reunions</p>
                <strong data-metric="true" data-target="<?php echo $stats['claimed'] ?? 0; ?>">0</strong>
            </div>
            <span class="metric-chip success">+15% week</span>
        </article>
        <article class="admin-metric" data-animate="pop">
            <div>
                <p>Items claimed</p>
                <strong data-metric="true" data-target="<?php echo $stats['claimed'] ?? 0; ?>">0</strong>
            </div>
            <span class="metric-chip success">+10% week</span>
        </article>
        <article class="admin-metric" data-animate="pop">
            <div>
                <p>Total registered users</p>
                <strong data-metric="true" data-target="<?php echo $stats['total_users'] ?? 0; ?>">0</strong>
            </div>
            <span class="metric-chip info">+5% week</span>
        </article>
        <article class="admin-metric" data-animate="pop">
            <div>
                <p>Resolution rate</p>
                <strong data-metric="true" data-target="<?php echo $resolutionRate; ?>" data-suffix="%">0%</strong>
            </div>
            <span class="metric-chip accent"><?php echo $resolvedTickets; ?>/<?php echo $totalTickets; ?> resolved</span>
        </article>
            </section>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>