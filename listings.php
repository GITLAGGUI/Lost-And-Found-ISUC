<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Listings';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Invalid request. Please try again.');
        header('Location: listings.php');
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
            header('Location: listings.php');
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
            header('Location: listings.php');
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
            header('Location: listings.php');
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
            header('Location: listings.php');
            exit;
        } else {
            set_flash('error', 'Failed to post item. Please try again.');
        }
    }
}

// Get filters from GET params
$filters = [
    'search' => trim($_GET['search'] ?? ''),
    'category_id' => trim($_GET['category_id'] ?? ''),
    'status' => trim($_GET['status'] ?? '')
];

// Pagination settings
$items_per_page = 12;
$current_page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($current_page - 1) * $items_per_page;

// Fetch data from database
$categories = $db->getCategories();
$total_lost = $db->countLostItems($filters);
$total_found = $db->countFoundItems($filters);
$lostItems = $db->getLostItems($filters, $items_per_page, $offset);
$foundItems = $db->getFoundItems($filters, $items_per_page, $offset);

// Calculate pagination
$total_lost_pages = ceil($total_lost / $items_per_page);
$total_found_pages = ceil($total_found / $items_per_page);

// Get current user for forms
$current_user = get_logged_in_user();

include __DIR__ . '/includes/header.php';
?>
<section class="section-header">
    <div>
        <h1>Community listings</h1>
        <p class="section-subtext">Search by keyword, filter by category, and flip between lost or found boards instantly.</p>
    </div>
</section>

<?php if (is_logged_in()): ?>
<div class="post-section">
    <button class="btn primary large" id="toggle-post-form" data-js="toggle-post-form">
        <i class="fas fa-plus-circle btn-icon"></i>
        Post a new item
    </button>
    
    <div id="post-form-container" class="post-form-container" hidden>
        <section class="section-header">
            <div>
                <h2>Post a new item</h2>
                <p class="section-subtext">Found something or lost an item? Help reunite belongings with their owners.</p>
            </div>
        </section>

        <div class="tabs">
            <button class="tab active" data-tab-target="form-lost">Post lost item</button>
            <button class="tab" data-tab-target="form-found">Post found item</button>
        </div>

        <div data-tab-panel="form-lost">
            <form class="form-card" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>" />
                <input type="hidden" name="action" value="post_lost" />
                <div class="grid-two">
                    <div class="form-group">
                        <label for="lost-name">Item name *</label>
                        <input type="text" id="lost-name" name="item_name" placeholder="e.g., DSLR Camera" required />
                    </div>
                    <div class="form-group">
                        <label for="lost-category">Category *</label>
                        <select id="lost-category" name="category" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo e($cat['name']); ?>"><?php echo e($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="grid-two">
                    <div class="form-group">
                        <label for="lost-date">Date lost *</label>
                        <input type="date" id="lost-date" name="date_lost" max="<?php echo date('Y-m-d'); ?>" required />
                    </div>
                    <div class="form-group">
                        <label for="lost-location">Location *</label>
                        <input type="text" id="lost-location" name="location" placeholder="Building / Area" required />
                    </div>
                </div>
                <div class="form-group">
                    <label for="lost-description">Description *</label>
                    <textarea id="lost-description" name="description" rows="4" placeholder="Include unique identifiers, color, brand, etc." required></textarea>
                </div>
                <div class="form-group">
                    <label for="lost-image">Upload image (optional)</label>
                    <input type="file" id="lost-image" name="image" accept="image/*" onchange="previewImage(this, 'lost-preview')" />
                    <div id="lost-preview" class="image-preview"></div>
                </div>
                <div class="grid-two">
                    <div class="form-group">
                        <label for="lost-email">Contact email *</label>
                        <input type="email" id="lost-email" name="contact_email" value="<?php echo e($current_user['email'] ?? ''); ?>" required />
                    </div>
                    <div class="form-group">
                        <label for="lost-phone">Phone (optional)</label>
                        <input type="tel" id="lost-phone" name="contact_phone" value="<?php echo e($current_user['phone'] ?? ''); ?>" />
                    </div>
                </div>
                <button class="btn primary" type="submit">Post lost item</button>
            </form>
        </div>

        <div data-tab-panel="form-found" hidden>
            <form class="form-card" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>" />
                <input type="hidden" name="action" value="post_found" />
                <div class="grid-two">
                    <div class="form-group">
                        <label for="found-name">Item name *</label>
                        <input type="text" id="found-name" name="item_name" placeholder="e.g., Smart watch" required />
                    </div>
                    <div class="form-group">
                        <label for="found-category">Category *</label>
                        <select id="found-category" name="category" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo e($cat['name']); ?>"><?php echo e($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="grid-two">
                    <div class="form-group">
                        <label for="found-date">Date found *</label>
                        <input type="date" id="found-date" name="date_found" max="<?php echo date('Y-m-d'); ?>" required />
                    </div>
                    <div class="form-group">
                        <label for="found-location">Location *</label>
                        <input type="text" id="found-location" name="location" placeholder="Building / Area" required />
                    </div>
                </div>
                <div class="form-group">
                    <label for="found-description">Description *</label>
                    <textarea id="found-description" name="description" rows="4" placeholder="Condition, identifying marks" required></textarea>
                </div>
                <div class="form-group">
                    <label for="found-image">Upload image (optional)</label>
                    <input type="file" id="found-image" name="image" accept="image/*" onchange="previewImage(this, 'found-preview')" />
                    <div id="found-preview" class="image-preview"></div>
                </div>
                <div class="grid-two">
                    <div class="form-group">
                        <label for="found-email">Contact email *</label>
                        <input type="email" id="found-email" name="contact_email" value="<?php echo e($current_user['email'] ?? ''); ?>" required />
                    </div>
                    <div class="form-group">
                        <label for="found-phone">Phone (optional)</label>
                        <input type="tel" id="found-phone" name="contact_phone" value="<?php echo e($current_user['phone'] ?? ''); ?>" />
                    </div>
                </div>
                <button class="btn primary" type="submit">Post found item</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<section class="section-header">
    <div>
        <h2>Browse items</h2>
        <p class="section-subtext">Browse lost and found items in our community.</p>
    </div>
</section>

<div class="tabs">
    <button class="tab active" data-tab-target="lost">Lost Items (<?php echo count($lostItems); ?>)</button>
    <button class="tab" data-tab-target="found">Found Items (<?php echo count($foundItems); ?>)</button>
</div>

<div class="search-panel">
    <input type="search" placeholder="Search item name or description" data-js="listing-search" aria-label="Search listings" value="<?php echo e($filters['search']); ?>" />
    <select data-js="listing-category" aria-label="Filter by category">
        <option value="">All categories</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?php echo e($cat['id']); ?>" <?php echo $filters['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                <?php echo e($cat['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select data-js="listing-status" aria-label="Filter by status">
        <option value="">All status</option>
        <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
        <option value="claimed" <?php echo $filters['status'] === 'claimed' ? 'selected' : ''; ?>>Claimed</option>
    </select>
</div>

<div data-tab-panel="lost" class="card-grid" id="lost">
    <?php if (empty($lostItems)): ?>
        <div class="listings-empty">No lost items found. Try adjusting your filters.</div>
    <?php else: ?>
        <?php foreach ($lostItems as $item): ?>
            <article class="item-card is-compact" data-js="listing-card" 
                     data-text="<?php echo strtolower(e($item['item_name']) . ' ' . e($item['description'] ?? '')); ?>" 
                     data-category="<?php echo e($item['category_id']); ?>" 
                     data-status="<?php echo e($item['status']); ?>">
                <div class="item-media">
                    <?php if ($item['image_path']): ?>
                        <img src="<?php echo e($item['image_path']); ?>" alt="Photo of <?php echo e($item['item_name']); ?>" loading="lazy" onclick="showItemDetails(<?php echo e($item['id']); ?>, 'lost')" style="cursor: pointer;" />
                    <?php else: ?>
                        <div class="item-media-fallback">
                            <span class="icon-circle small" aria-hidden="true">📷</span>
                            <span>No photo</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="item-content">
                    <?php echo status_chip($item['status']); ?>
                    <h3><?php echo e($item['item_name']); ?></h3>
                    <p><?php echo e(truncate($item['description'] ?? '', 100)); ?></p>
                    <div class="card-actions">
                        <button class="btn ghost small" onclick="showItemDetails(<?php echo e($item['id']); ?>, 'lost')">See details</button>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if ($total_lost_pages > 1): ?>
    <nav class="pagination" aria-label="Lost items pagination" data-pagination="lost">
        <div class="pagination-info">Page <?php echo $current_page; ?> of <?php echo $total_lost_pages; ?> (<?php echo $total_lost; ?> items)</div>
        <div class="pagination-controls">
            <?php if ($current_page > 1): ?>
                <a href="listings.php?page=1&tab=lost<?php echo $filters['search'] ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo $filters['category_id'] ? '&category_id=' . urlencode($filters['category_id']) : ''; ?><?php echo $filters['status'] ? '&status=' . urlencode($filters['status']) : ''; ?>" class="btn ghost small">&laquo; First</a>
                <a href="listings.php?page=<?php echo $current_page - 1; ?>&tab=lost<?php echo $filters['search'] ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo $filters['category_id'] ? '&category_id=' . urlencode($filters['category_id']) : ''; ?><?php echo $filters['status'] ? '&status=' . urlencode($filters['status']) : ''; ?>" class="btn ghost small">&lsaquo; Prev</a>
            <?php endif; ?>
            
            <?php
            $start = max(1, $current_page - 2);
            $end = min($total_lost_pages, $current_page + 2);
            for ($i = $start; $i <= $end; $i++):
            ?>
                <a href="listings.php?page=<?php echo $i; ?>&tab=lost<?php echo $filters['search'] ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo $filters['category_id'] ? '&category_id=' . urlencode($filters['category_id']) : ''; ?><?php echo $filters['status'] ? '&status=' . urlencode($filters['status']) : ''; ?>" class="btn <?php echo $i === $current_page ? 'primary' : 'ghost'; ?> small"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if ($current_page < $total_lost_pages): ?>
                <a href="listings.php?page=<?php echo $current_page + 1; ?>&tab=lost<?php echo $filters['search'] ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo $filters['category_id'] ? '&category_id=' . urlencode($filters['category_id']) : ''; ?><?php echo $filters['status'] ? '&status=' . urlencode($filters['status']) : ''; ?>" class="btn ghost small">Next &rsaquo;</a>
                <a href="listings.php?page=<?php echo $total_lost_pages; ?>&tab=lost<?php echo $filters['search'] ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo $filters['category_id'] ? '&category_id=' . urlencode($filters['category_id']) : ''; ?><?php echo $filters['status'] ? '&status=' . urlencode($filters['status']) : ''; ?>" class="btn ghost small">Last &raquo;</a>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>
</div>

<div data-tab-panel="found" class="card-grid" id="found" hidden>
    <?php if (empty($foundItems)): ?>
        <div class="listings-empty">No found items yet.</div>
    <?php else: ?>
        <?php foreach ($foundItems as $item): ?>
            <article class="item-card is-compact" data-js="listing-card" 
                     data-text="<?php echo strtolower(e($item['item_name']) . ' ' . e($item['description'] ?? '')); ?>" 
                     data-category="<?php echo e($item['category_id']); ?>" 
                     data-status="<?php echo e($item['status']); ?>">
                <div class="item-media">
                    <?php if ($item['image_path']): ?>
                        <img src="<?php echo e($item['image_path']); ?>" alt="Photo of <?php echo e($item['item_name']); ?>" loading="lazy" onclick="showItemDetails(<?php echo e($item['id']); ?>, 'found')" style="cursor: pointer;" />
                    <?php else: ?>
                        <div class="item-media-fallback">
                            <span class="icon-circle small" aria-hidden="true">📷</span>
                            <span>No photo</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="item-content">
                    <?php echo status_chip($item['status']); ?>
                    <h3><?php echo e($item['item_name']); ?></h3>
                    <p><?php echo e(truncate($item['description'] ?? '', 100)); ?></p>
                    <div class="card-actions">
                        <button class="btn ghost small" onclick="showItemDetails(<?php echo e($item['id']); ?>, 'found')">See details</button>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if ($total_found_pages > 1): ?>
    <nav class="pagination" aria-label="Found items pagination" data-pagination="found">
        <div class="pagination-info">Page <?php echo $current_page; ?> of <?php echo $total_found_pages; ?> (<?php echo $total_found; ?> items)</div>
        <div class="pagination-controls">
            <?php if ($current_page > 1): ?>
                <a href="listings.php?page=1&tab=found<?php echo $filters['search'] ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo $filters['category_id'] ? '&category_id=' . urlencode($filters['category_id']) : ''; ?><?php echo $filters['status'] ? '&status=' . urlencode($filters['status']) : ''; ?>" class="btn ghost small">&laquo; First</a>
                <a href="listings.php?page=<?php echo $current_page - 1; ?>&tab=found<?php echo $filters['search'] ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo $filters['category_id'] ? '&category_id=' . urlencode($filters['category_id']) : ''; ?><?php echo $filters['status'] ? '&status=' . urlencode($filters['status']) : ''; ?>" class="btn ghost small">&lsaquo; Prev</a>
            <?php endif; ?>
            
            <?php
            $start = max(1, $current_page - 2);
            $end = min($total_found_pages, $current_page + 2);
            for ($i = $start; $i <= $end; $i++):
            ?>
                <a href="listings.php?page=<?php echo $i; ?>&tab=found<?php echo $filters['search'] ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo $filters['category_id'] ? '&category_id=' . urlencode($filters['category_id']) : ''; ?><?php echo $filters['status'] ? '&status=' . urlencode($filters['status']) : ''; ?>" class="btn <?php echo $i === $current_page ? 'primary' : 'ghost'; ?> small"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if ($current_page < $total_found_pages): ?>
                <a href="listings.php?page=<?php echo $current_page + 1; ?>&tab=found<?php echo $filters['search'] ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo $filters['category_id'] ? '&category_id=' . urlencode($filters['category_id']) : ''; ?><?php echo $filters['status'] ? '&status=' . urlencode($filters['status']) : ''; ?>" class="btn ghost small">Next &rsaquo;</a>
                <a href="listings.php?page=<?php echo $total_found_pages; ?>&tab=found<?php echo $filters['search'] ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo $filters['category_id'] ? '&category_id=' . urlencode($filters['category_id']) : ''; ?><?php echo $filters['status'] ? '&status=' . urlencode($filters['status']) : ''; ?>" class="btn ghost small">Last &raquo;</a>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>
</div>

<!-- Item Details Modal -->
<div id="item-modal" class="modal" hidden>
    <div class="modal-overlay" data-js="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title">Item Details</h3>
            <button class="modal-close" data-js="modal-close" aria-label="Close modal">&times;</button>
        </div>
        <div id="modal-body" class="modal-body">
            <!-- Content will be populated by JavaScript -->
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
