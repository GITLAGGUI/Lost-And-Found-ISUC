<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $item_id = (int)($_POST['item_id'] ?? 0);
    
    if ($action === 'delete_lost' && $item_id) {
        if ($db->hardDeleteLostItem($item_id)) {
            $db->logActivity($_SESSION['user_id'], 'admin_hard_delete', "Hard deleted lost item ID: $item_id", get_client_ip());
            set_flash('success', 'Item permanently deleted.');
        } else {
            set_flash('error', 'Failed to delete item.');
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } elseif ($action === 'delete_found' && $item_id) {
        if ($db->hardDeleteFoundItem($item_id)) {
            $db->logActivity($_SESSION['user_id'], 'admin_hard_delete', "Hard deleted found item ID: $item_id", get_client_ip());
            set_flash('success', 'Item permanently deleted.');
        } else {
            set_flash('error', 'Failed to delete item.');
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } elseif ($action === 'archive_lost' && $item_id) {
        if ($db->deleteLostItem($item_id)) { // soft delete
            $db->logActivity($_SESSION['user_id'], 'admin_archive', "Archived lost item ID: $item_id", get_client_ip());
            set_flash('success', 'Item archived.');
        } else {
            set_flash('error', 'Failed to archive item.');
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } elseif ($action === 'archive_found' && $item_id) {
        if ($db->deleteFoundItem($item_id)) {
            $db->logActivity($_SESSION['user_id'], 'admin_archive', "Archived found item ID: $item_id", get_client_ip());
            set_flash('success', 'Item archived.');
        } else {
            set_flash('error', 'Failed to archive item.');
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } elseif ($action === 'restore_lost' && $item_id) {
        if ($db->restoreLostItem($item_id)) {
            $db->logActivity($_SESSION['user_id'], 'admin_restore', "Restored lost item ID: $item_id", get_client_ip());
            set_flash('success', 'Item restored.');
        } else {
            set_flash('error', 'Failed to restore item.');
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } elseif ($action === 'restore_found' && $item_id) {
        if ($db->restoreFoundItem($item_id)) {
            $db->logActivity($_SESSION['user_id'], 'admin_restore', "Restored found item ID: $item_id", get_client_ip());
            set_flash('success', 'Item restored.');
        } else {
            set_flash('error', 'Failed to restore item.');
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

$pageTitle = 'Admin Control Center';
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
    <?php $activePage = 'overview'; include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-shell" data-js="admin-shell">
            <section id="overview" class="admin-hero" data-animate="stagger">
                <div>
                    <p class="eyebrow">Control Center</p>
                    <h1>Live campus operations</h1>
                    <p class="admin-hero__lede">Monitor submissions, fast-track reviews, and keep reunions moving. Everything refreshes instantly without heavy assets so the experience stays silky-smooth even on lab PCs.</p>
                    <div class="admin-hero__pills">
                        <span class="pulse-pill">Live sync</span>
                        <span class="ghost-pill"><?php echo e($recentActivity[0]['action'] ?? 'No events'); ?></span>
                    </div>
                    <div class="admin-hero__actions">
                        <a href="<?php echo app_url('listings.php'); ?>" class="btn primary">View public board</a>
                    </div>
                </div>
                <div class="admin-hero__panel">
                    <div class="spark-card">
                        <span>Resolution rate</span>
                        <strong data-metric="true" data-target="<?php echo $resolutionRate; ?>" data-suffix="%">0%</strong>
                        <small><?php echo $resolvedTickets; ?> closed of <?php echo $totalTickets; ?> total submissions</small>
                        <div class="spark-bar" aria-hidden="true">
                            <span style="width: <?php echo $resolutionRate; ?>%"></span>
                        </div>
                    </div>
                    <div class="spark-row">
                        <div>
                            <span>Open lost cases</span>
                            <p data-metric="true" data-target="<?php echo $stats['lost_active'] ?? 0; ?>">0</p>
                        </div>
                        <div>
                            <span>Unclaimed finds</span>
                            <p data-metric="true" data-target="<?php echo $stats['found_available'] ?? 0; ?>">0</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
