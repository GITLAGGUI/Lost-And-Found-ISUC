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
    }
}

$pageTitle = 'Attention Queue - Admin Control Center';
$bodyClass = 'admin-body';
$mainClass = 'admin-main';

$current_user = get_logged_in_user();
$recentLost = $db->getRecentLostItemsForAdmin(10); // More for dedicated page
$recentFound = $db->getRecentFoundItemsForAdmin(10);
$extraScripts = [app_url('assets/js/admin.js')];
$currentInitials = strtoupper(substr($current_user['full_name'] ?? $current_user['username'], 0, 2));

include __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
    <?php $activePage = 'queue'; include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-shell" data-js="admin-shell">
            <section class="admin-panels single-panel">
        <article class="admin-panel wide" data-animate="slide">
            <header>
                <div>
                    <h2>Attention queue</h2>
                    <p>High-priority submissions surface here for faster moderation.</p>
                </div>
                <div class="admin-panel__filters">
                    <button class="queue-toggle is-active" data-queue-target="lost">Lost items</button>
                    <button class="queue-toggle" data-queue-target="found">Found items</button>
                </div>
            </header>
            <div class="admin-queue" data-queue-panel="lost">
                <?php if (empty($recentLost)): ?>
                    <p class="empty-state">No active lost posts in the queue.</p>
                <?php else: ?>
                    <?php foreach ($recentLost as $item): ?>
                        <div class="queue-card">
                            <div>
                                <span class="queue-label">Lost • <?php echo e($item['category']); ?></span>
                                <h3><?php echo e($item['item_name']); ?></h3>
                                <p><?php echo e(truncate($item['description'] ?? '', 90)); ?></p>
                                <div class="queue-meta">
                                    <span><?php echo format_date($item['date_lost']); ?></span>
                                    <span><?php echo e($item['location']); ?></span>
                                    <span><?php echo e($item['full_name']); ?></span>
                                </div>
                            </div>
                            <div class="queue-actions">
                                <span class="status-chip <?php echo $item['status'] === 'active' ? 'status-active' : 'status-muted'; ?>"><?php echo ucfirst($item['status']); ?></span>
                                <a class="btn ghost small" href="mailto:<?php echo e($item['contact_email']); ?>">Reach out</a>
                                <form method="post">
                                    <input type="hidden" name="action" value="archive_lost" />
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>" />
                                    <button class="btn muted small" type="submit" onclick="return confirm('Archive this item?')">Archive</button>
                                </form>
                                <form method="post">
                                    <input type="hidden" name="action" value="delete_lost" />
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>" />
                                    <button class="btn danger small" type="submit" onclick="return confirm('Permanently delete this item?')">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="admin-queue" data-queue-panel="found" hidden>
                <?php if (empty($recentFound)): ?>
                    <p class="empty-state">No found items awaiting review.</p>
                <?php else: ?>
                    <?php foreach ($recentFound as $item): ?>
                        <div class="queue-card">
                            <div>
                                <span class="queue-label">Found • <?php echo e($item['category']); ?></span>
                                <h3><?php echo e($item['item_name']); ?></h3>
                                <p><?php echo e(truncate($item['description'] ?? '', 90)); ?></p>
                                <div class="queue-meta">
                                    <span><?php echo format_date($item['date_found']); ?></span>
                                    <span><?php echo e($item['location']); ?></span>
                                    <span><?php echo e($item['full_name']); ?></span>
                                </div>
                            </div>
                            <div class="queue-actions">
                                <span class="status-chip <?php echo $item['status'] === 'available' ? 'status-active' : 'status-muted'; ?>"><?php echo ucfirst($item['status']); ?></span>
                                <a class="btn ghost small" href="mailto:<?php echo e($item['contact_email']); ?>">Notify</a>
                                <form method="post">
                                    <input type="hidden" name="action" value="archive_found" />
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>" />
                                    <button class="btn muted small" type="submit" onclick="return confirm('Archive this item?')">Archive</button>
                                </form>
                                <form method="post">
                                    <input type="hidden" name="action" value="delete_found" />
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>" />
                                    <button class="btn danger small" type="submit" onclick="return confirm('Permanently delete this item?')">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>
            </section>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>