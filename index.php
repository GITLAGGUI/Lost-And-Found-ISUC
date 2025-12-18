<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Home';

// Fetch stats and recent items
$stats = $db->getStats();
$lostItems = $db->getLostItems([], 3, 0);
$foundItems = $db->getFoundItems([], 3, 0);

include __DIR__ . '/includes/header.php';
?>
<section class="hero hero--with-bg fade-in" style="background: linear-gradient(135deg, rgba(6, 78, 59, 0.75), rgba(15, 23, 42, 0.65)), url('<?php echo app_url('assets/images/hero-bg.png'); ?>'); background-size: cover; background-position: center;">
    <div class="hero-content">
        <h1>Lost &amp; Found tracking built for fast reunions.</h1>
        <p class="hero-lede" style="color:rgba(212, 247, 227, 1);"><?php echo APP_NAME; ?> streamlines how students, faculty, and security report or claim items with guided forms, secure messaging, and live status updates.</p>
        <div class="hero-actions">
            <a class="btn primary btn-lg" href="listings.php">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
                Browse Listings
            </a>
            <?php if (!is_logged_in()): ?>
                <a class="btn ghost btn-lg" href="register.php" style="color: white; border-color: rgba(255,255,255,0.5);">Create Account</a>
            <?php else: ?>
                <a class="btn ghost btn-lg" href="dashboard.php" style="color: white; border-color: rgba(255,255,255,0.5);">Go to Dashboard</a>
            <?php endif; ?>
        </div>
        <p class="hero-footnote" style="color:rgba(212, 247, 227, 1);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22,4 12,14.01 9,11.01"></polyline></svg>
            Average submission to claim time dropped by 48% after launch.
        </p>
    </div>
    <div class="hero-panel">
        <div class="stats-grid compact">
            <article class="stat-card">
                <span class="icon-circle" aria-hidden="true">
                    <svg viewBox="0 0 24 24" role="presentation"><path d="M5 7a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v9a3 3 0 0 1-3 3H8a3 3 0 0 1-3-3z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path><path d="M9 7h6M9 11h6M9 15h3" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path></svg>
                </span>
                <div>
                    <span class="stat-label">Active lost items</span>
                    <strong><?php echo $stats['lost_active']; ?></strong>
                </div>
            </article>
            <article class="stat-card">
                <span class="icon-circle" aria-hidden="true">
                    <svg viewBox="0 0 24 24" role="presentation"><path d="M12 3l6 4v6c0 3.866-2.686 7-6 7s-6-3.134-6-7V7l6-4z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9 11l3 3 3-3" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </span>
                <div>
                    <span class="stat-label">Found items available</span>
                    <strong><?php echo $stats['found_available']; ?></strong>
                </div>
            </article>
            <article class="stat-card">
                <span class="icon-circle" aria-hidden="true">
                    <svg viewBox="0 0 24 24" role="presentation"><path d="M4 12l4.5 4.5L20 5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path><path d="M4 19h12" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path></svg>
                </span>
                <div>
                    <span class="stat-label">Successfully claimed</span>
                    <strong><?php echo $stats['claimed']; ?></strong>
                </div>
            </article>
        </div>
    </div>
</section>

<?php if (count($lostItems) > 0): ?>
<section>
    <div class="section-header">
        <div>
            <h2>Recent lost reports</h2>
            <p class="section-subtext">High-visibility cards help students scan crucial details quickly.</p>
        </div>
        <a href="listings.php">View all</a>
    </div>
    <div class="card-grid">
        <?php foreach ($lostItems as $item): ?>
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
                    <?php echo status_chip($item['status']); ?>
                    <h3><?php echo e($item['item_name']); ?></h3>
                    <p><?php echo e(truncate($item['description'] ?? '')); ?></p>
                    <div class="card-meta">
                        <span>Lost: <?php echo format_date($item['date_lost']); ?></span>
                        <span><?php echo e($item['location']); ?></span>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if (count($foundItems) > 0): ?>
<section>
    <div class="section-header">
        <div>
            <h2>Fresh found items</h2>
            <p class="section-subtext">Security partners and students add updates with contact details.</p>
        </div>
        <a href="listings.php#found">See found board</a>
    </div>
    <div class="card-grid">
        <?php foreach ($foundItems as $item): ?>
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
                    <?php echo status_chip($item['status']); ?>
                    <h3><?php echo e($item['item_name']); ?></h3>
                    <p><?php echo e(truncate($item['description'] ?? '')); ?></p>
                    <div class="card-meta">
                        <span>Found: <?php echo format_date($item['date_found']); ?></span>
                        <span><?php echo e($item['location']); ?></span>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section>
    <div class="section-header">
        <div>
            <h2>How it works</h2>
            <p class="section-subtext">A guided 3-step workflow keeps submissions clean and trackable.</p>
        </div>
    </div>
    <div class="card-grid">
        <article class="item-card">
            <div class="item-content">
                <span class="step-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" role="presentation"><path d="M6 3h9l4 4v14H6z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path><path d="M15 3v5h5" fill="none" stroke="currentColor" stroke-width="1.4"></path><path d="M9 12h6M9 16h4" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path></svg>
                </span>
                <span class="badge">Step 1</span>
                <h3>Report or browse</h3>
                <p class="text-full">Register with your ISU email and post lost/found entries with precise locations, categories, and contact preferences.</p>
            </div>
        </article>
        <article class="item-card">
            <div class="item-content">
                <span class="step-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" role="presentation"><circle cx="10" cy="10" r="5" fill="none" stroke="currentColor" stroke-width="1.4"></circle><path d="M14 14l5 5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path><path d="M9 7h2v2H9z" fill="currentColor"></path></svg>
                </span>
                <span class="badge">Step 2</span>
                <h3>Smart filtering</h3>
                <p class="text-full">Use live search, filters, and responsive cards to narrow down items faster than traditional interfaces.</p>
            </div>
        </article>
        <article class="item-card">
            <div class="item-content">
                <span class="step-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" role="presentation"><path d="M4 12l4.5 4.5 3-3L19 20l1-7-6-6z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path><path d="M5 5l3 3" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path></svg>
                </span>
                <span class="badge">Step 3</span>
                <h3>Coordinate pickup</h3>
                <p class="text-full">Contact owners or security desks directly. Admins can tag items as claimed to keep the board clean.</p>
            </div>
        </article>
    </div>
</section>

<section>
    <div class="section-header">
        <div>
            <h2>About us</h2>
        </div>
    </div>
    <div class="container">
        <div class="content-block">
            <p class="justified"><?php echo APP_NAME; ?> is a lost and found tracking system designed specifically for the ISU campus community. Our platform connects students, faculty, and staff to help recover lost items quickly and efficiently. With secure authentication, real-time updates, and user-friendly interfaces, we make the process of reporting and claiming lost items as simple as possible. Join thousands of ISU community members who have successfully reunited with their belongings through our system.</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
