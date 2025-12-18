<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$pageTitle = 'Dashboard';
$current_user = get_logged_in_user();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Invalid request. Please try again.');
        header('Location: dashboard.php');
        exit;
    }
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'post_lost') {
        $item_name = trim($_POST['item_name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $date_lost = $_POST['date_lost'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $contact_email = trim($_POST['contact_email'] ?? '');
        $contact_phone = trim($_POST['contact_phone'] ?? '');

        $category_id = $db->getCategoryIdByName($category);
        if (!$category_id) {
            set_flash('error', 'Invalid category selected.');
            header('Location: dashboard.php');
            exit;
        }

        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            $upload_result = upload_image($_FILES['image'], 'lost');
            if ($upload_result['success']) {
                $image_path = $upload_result['path'];
            } else {
                set_flash('error', $upload_result['error']);
            }
        }

        if ($db->createLostItem($_SESSION['user_id'], $item_name, $category_id, $description, $date_lost, $location, $image_path, $contact_email, $contact_phone)) {
            $db->logActivity($_SESSION['user_id'], 'item_posted', "Posted lost item: $item_name", get_client_ip());
            set_flash('success', 'Lost item posted successfully!');
            header('Location: dashboard.php');
            exit;
        } else {
            set_flash('error', 'Failed to post item. Please try again.');
        }
    } else if ($action === 'post_found') {
        $item_name = trim($_POST['item_name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $date_found = $_POST['date_found'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $contact_email = trim($_POST['contact_email'] ?? '');
        $contact_phone = trim($_POST['contact_phone'] ?? '');

        $category_id = $db->getCategoryIdByName($category);
        if (!$category_id) {
            set_flash('error', 'Invalid category selected.');
            header('Location: dashboard.php');
            exit;
        }

        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            $upload_result = upload_image($_FILES['image'], 'found');
            if ($upload_result['success']) {
                $image_path = $upload_result['path'];
            } else {
                set_flash('error', $upload_result['error']);
            }
        }

        if ($db->createFoundItem($_SESSION['user_id'], $item_name, $category_id, $description, $date_found, $location, $image_path, $contact_email, $contact_phone)) {
            $db->logActivity($_SESSION['user_id'], 'item_posted', "Posted found item: $item_name", get_client_ip());
            set_flash('success', 'Found item posted successfully!');
            header('Location: dashboard.php');
            exit;
        } else {
            set_flash('error', 'Failed to post item. Please try again.');
        }
    } else if ($action === 'mark_claimed' && isset($_POST['item_id'])) {
        $item_id = (int)$_POST['item_id'];
        if ($db->updateLostItemStatus($item_id, 'claimed')) {
            set_flash('success', 'Item marked as claimed!');
            header('Location: dashboard.php');
            exit;
        }
    } else if ($action === 'mark_claimed' && isset($_POST['item_id'])) {
        $item_id = (int)$_POST['item_id'];
        if ($db->updateFoundItemStatus($item_id, 'claimed')) {
            set_flash('success', 'Item marked as claimed!');
            header('Location: dashboard.php');
            exit;
        }
    } else if ($action === 'delete_lost' && isset($_POST['item_id'])) {
        $item_id = (int)$_POST['item_id'];
        if ($db->deleteLostItem($item_id)) {
            $db->logActivity($_SESSION['user_id'], 'item_deleted', "Deleted lost item ID: $item_id", get_client_ip());
            set_flash('success', 'Item deleted successfully!');
            header('Location: dashboard.php');
            exit;
        }
    } else if ($action === 'delete_found' && isset($_POST['item_id'])) {
        $item_id = (int)$_POST['item_id'];
        if ($db->deleteFoundItem($item_id)) {
            $db->logActivity($_SESSION['user_id'], 'item_deleted', "Deleted found item ID: $item_id", get_client_ip());
            set_flash('success', 'Item deleted successfully!');
            header('Location: dashboard.php');
            exit;
        }
    } else if ($action === 'edit_lost' && isset($_POST['item_id'])) {
        $item_id = (int)$_POST['item_id'];
        $item_name = trim($_POST['item_name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $date_lost = $_POST['date_lost'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $contact_email = trim($_POST['contact_email'] ?? '');
        $contact_phone = trim($_POST['contact_phone'] ?? '');
        
        $category_id = $db->getCategoryIdByName($category);
        if (!$category_id) {
            set_flash('error', 'Invalid category selected.');
            header('Location: dashboard.php');
            exit;
        }
        
        if ($db->updateLostItem($item_id, $item_name, $category_id, $description, $date_lost, $location, $contact_email, $contact_phone)) {
            $db->logActivity($_SESSION['user_id'], 'item_edited', "Edited lost item: $item_name", get_client_ip());
            set_flash('success', 'Item updated successfully!');
            header('Location: dashboard.php');
            exit;
        } else {
            set_flash('error', 'Failed to update item.');
        }
    } else if ($action === 'edit_found' && isset($_POST['item_id'])) {
        $item_id = (int)$_POST['item_id'];
        $item_name = trim($_POST['item_name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $date_found = $_POST['date_found'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $contact_email = trim($_POST['contact_email'] ?? '');
        $contact_phone = trim($_POST['contact_phone'] ?? '');
        
        $category_id = $db->getCategoryIdByName($category);
        if (!$category_id) {
            set_flash('error', 'Invalid category selected.');
            header('Location: dashboard.php');
            exit;
        }
        
        if ($db->updateFoundItem($item_id, $item_name, $category_id, $description, $date_found, $location, $contact_email, $contact_phone)) {
            $db->logActivity($_SESSION['user_id'], 'item_edited', "Edited found item: $item_name", get_client_ip());
            set_flash('success', 'Item updated successfully!');
            header('Location: dashboard.php');
            exit;
        } else {
            set_flash('error', 'Failed to update item.');
        }
    }
}

// Fetch user's items
$myLostItems = $db->getUserLostItems($_SESSION['user_id'], 20);
$myFoundItems = $db->getUserFoundItems($_SESSION['user_id'], 20);
$categories = $db->getCategories();

include __DIR__ . '/includes/header.php';
?>
<section class="section-header">
    <div>
        <h1>Welcome, <?php echo e($current_user['full_name']); ?>!</h1>
        <p class="section-subtext">Manage your listings and track your lost and found items.</p>
    </div>
</section>

<div class="card-grid">
    <article class="stat-card">
        <span>Your lost posts</span>
        <strong><?php echo count(array_filter($myLostItems, fn($i) => $i['status'] === 'active')); ?></strong>
    </article>
    <article class="stat-card">
        <span>Your found posts</span>
        <strong><?php echo count(array_filter($myFoundItems, fn($i) => $i['status'] === 'available')); ?></strong>
    </article>
    <article class="stat-card">
        <span>Total claimed</span>
        <strong><?php echo count(array_filter($myLostItems, fn($i) => $i['status'] === 'claimed')) + count(array_filter($myFoundItems, fn($i) => $i['status'] === 'claimed')); ?></strong>
    </article>
</div>

<section class="section-header">
    <div>
        <h2>Your listings</h2>
        <p class="section-subtext">Recent items you have posted.</p>
    </div>
</section>

<?php if (empty($myLostItems) && empty($myFoundItems)): ?>
    <div class="listings-empty">You haven't posted any items yet. Use the form below to get started!</div>
<?php else: ?>
<div class="card-grid dashboard-grid">
    <?php foreach ($myLostItems as $item): ?>
        <article class="item-card is-compact">
            <div class="item-media">
                <?php if ($item['image_path']): ?>
                    <img src="<?php echo e($item['image_path']); ?>" alt="Photo of <?php echo e($item['item_name']); ?>" loading="lazy" />
                <?php else: ?>
                    <div class="item-media-fallback">
                        <span class="icon-circle small" aria-hidden="true">📷</span>
                        <span>No photo</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="item-content">
                <div class="item-meta-inline">
                    <?php echo status_chip($item['status']); ?>
                    <span style="font-size:0.75rem;color:var(--clr-muted)">LOST</span>
                </div>
                <h3><?php echo e($item['item_name']); ?></h3>
                <p><?php echo e(truncate($item['description'] ?? '', 120)); ?></p>
                <div class="card-meta">
                    <span><?php echo format_date($item['date_lost']); ?></span>
                    <span><?php echo e($item['location']); ?></span>
                </div>
                <?php if ($item['status'] === 'active'): ?>
                <form method="post" class="dashboard-btn-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>" />
                    <input type="hidden" name="action" value="mark_claimed" />
                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>" />
                    <input type="hidden" name="item_type" value="lost" />
                    <button class="btn primary small" type="submit">Mark as claimed</button>
                </form>
                <?php endif; ?>
                <?php if ($item['is_deleted'] == 0): ?>
                <div class="dashboard-btn-group">
                    <button class="btn ghost small" type="button" onclick="openEditModal('lost', <?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>)">Edit</button>
                    <form method="post" class="dashboard-btn-form" onsubmit="return confirm('Are you sure you want to delete this item?')">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>" />
                        <input type="hidden" name="action" value="delete_lost" />
                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>" />
                        <button class="btn danger small" type="submit">Delete</button>
                    </form>
                </div>
                <?php else: ?>
                <div style="margin-top:0.5rem;color:var(--clr-muted);font-size:0.875rem;">Deleted</div>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
    
    <?php foreach ($myFoundItems as $item): ?>
        <article class="item-card is-compact">
            <div class="item-media">
                <?php if ($item['image_path']): ?>
                    <img src="<?php echo e($item['image_path']); ?>" alt="Photo of <?php echo e($item['item_name']); ?>" loading="lazy" />
                <?php else: ?>
                    <div class="item-media-fallback">
                        <span class="icon-circle small" aria-hidden="true">📷</span>
                        <span>No photo</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="item-content">
                <div class="item-meta-inline">
                    <?php echo status_chip($item['status']); ?>
                    <span style="font-size:0.75rem;color:var(--clr-muted)">FOUND</span>
                </div>
                <h3><?php echo e($item['item_name']); ?></h3>
                <p><?php echo e(truncate($item['description'] ?? '', 120)); ?></p>
                <div class="card-meta">
                    <span><?php echo format_date($item['date_found']); ?></span>
                    <span><?php echo e($item['location']); ?></span>
                </div>
                <?php if ($item['status'] === 'active'): ?>
                <form method="post" class="dashboard-btn-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>" />
                    <input type="hidden" name="action" value="mark_claimed" />
                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>" />
                    <input type="hidden" name="item_type" value="found" />
                    <button class="btn primary small" type="submit">Mark as claimed</button>
                </form>
                <?php endif; ?>
                <?php if ($item['is_deleted'] == 0): ?>
                <div class="dashboard-btn-group">
                    <button class="btn ghost small" type="button" onclick="openEditModal('found', <?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>)">Edit</button>
                    <form method="post" class="dashboard-btn-form" onsubmit="return confirm('Are you sure you want to delete this item?')">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>" />
                        <input type="hidden" name="action" value="delete_found" />
                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>" />
                        <button class="btn danger small" type="submit">Delete</button>
                    </form>
                </div>
                <?php else: ?>
                <div style="margin-top:0.5rem;color:var(--clr-muted);font-size:0.875rem;">Deleted</div>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Edit Item Modal -->
<div id="edit-modal" class="modal" hidden>
    <div class="modal-overlay" onclick="closeEditModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="edit-modal-title">Edit Item</h3>
            <button class="modal-close" onclick="closeEditModal()" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit-form" method="post" class="form-card">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>" />
                <input type="hidden" name="action" id="edit-action" value="edit_lost" />
                <input type="hidden" name="item_id" id="edit-item-id" value="" />
                
                <div class="form-group">
                    <label for="edit-item-name">Item name *</label>
                    <input type="text" id="edit-item-name" name="item_name" required />
                </div>
                
                <div class="form-group">
                    <label for="edit-category">Category *</label>
                    <select id="edit-category" name="category" required>
                        <option value="">Select category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo e($cat['name']); ?>"><?php echo e($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit-date">Date *</label>
                    <input type="date" id="edit-date" name="date_lost" max="<?php echo date('Y-m-d'); ?>" required />
                </div>
                
                <div class="form-group">
                    <label for="edit-location">Location *</label>
                    <input type="text" id="edit-location" name="location" required />
                </div>
                
                <div class="form-group">
                    <label for="edit-description">Description *</label>
                    <textarea id="edit-description" name="description" rows="4" required></textarea>
                </div>
                
                <div class="grid-two">
                    <div class="form-group">
                        <label for="edit-email">Contact email *</label>
                        <input type="email" id="edit-email" name="contact_email" required />
                    </div>
                    <div class="form-group">
                        <label for="edit-phone">Phone (optional)</label>
                        <input type="tel" id="edit-phone" name="contact_phone" />
                    </div>
                </div>
                
                <div style="display:flex;gap:1rem;margin-top:1rem">
                    <button type="button" class="btn ghost" onclick="closeEditModal()" style="flex:1">Cancel</button>
                    <button type="submit" class="btn primary" style="flex:1">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(type, item) {
    const modal = document.getElementById('edit-modal');
    const form = document.getElementById('edit-form');
    
    // Set form action and title
    document.getElementById('edit-action').value = 'edit_' + type;
    document.getElementById('edit-modal-title').textContent = 'Edit ' + (type === 'lost' ? 'Lost' : 'Found') + ' Item';
    
    // Change date field name based on type
    const dateField = document.getElementById('edit-date');
    dateField.name = type === 'lost' ? 'date_lost' : 'date_found';
    
    // Populate form fields
    document.getElementById('edit-item-id').value = item.id;
    document.getElementById('edit-item-name').value = item.item_name;
    document.getElementById('edit-category').value = item.category;
    document.getElementById('edit-date').value = type === 'lost' ? item.date_lost : item.date_found;
    document.getElementById('edit-location').value = item.location;
    document.getElementById('edit-description').value = item.description || '';
    document.getElementById('edit-email').value = item.contact_email || '';
    document.getElementById('edit-phone').value = item.contact_phone || '';
    
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    const modal = document.getElementById('edit-modal');
    modal.hidden = true;
    document.body.style.overflow = '';
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeEditModal();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
