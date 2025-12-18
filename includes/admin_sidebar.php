<?php
/**
 * Admin Sidebar Component
 * 
 * Reusable sidebar navigation for admin pages.
 * Include this file in admin pages after setting $activePage variable.
 * 
 * @var string $activePage The current active page ('overview', 'stats', 'queue', 'activity', 'categories')
 * @var array $current_user The logged-in user array
 */

$currentInitials = strtoupper(substr($current_user['full_name'] ?? $current_user['username'], 0, 2));
?>
<div class="admin-sidebar-overlay" data-js="admin-sidebar-overlay"></div>
<button class="admin-sidebar-toggle" aria-label="Toggle admin menu" aria-expanded="false" data-js="admin-sidebar-toggle">
    <svg class="icon-menu" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
    <svg class="icon-close" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
</button>
<aside class="admin-sidebar" data-animate="slide" data-js="admin-sidebar">
    <div class="sidebar-brand">
        <p class="eyebrow">Control Center</p>
        <h2>ISU Admin</h2>
        <p class="sidebar-subtext">Realtime moderation cockpit</p>
    </div>
    <nav class="sidebar-nav" aria-label="Admin navigation">
        <a href="<?php echo app_url('admin/index.php'); ?>"<?php echo ($activePage ?? '') === 'overview' ? ' class="is-active"' : ''; ?>>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
            <span>Overview</span>
        </a>
        <a href="<?php echo app_url('admin/stats.php'); ?>"<?php echo ($activePage ?? '') === 'stats' ? ' class="is-active"' : ''; ?>>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
            <span>Performance</span>
        </a>
        <a href="<?php echo app_url('admin/queue.php'); ?>"<?php echo ($activePage ?? '') === 'queue' ? ' class="is-active"' : ''; ?>>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            <span>Attention Queue</span>
        </a>
        <a href="<?php echo app_url('admin/activity.php'); ?>"<?php echo ($activePage ?? '') === 'activity' ? ' class="is-active"' : ''; ?>>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            <span>Live Activity</span>
        </a>
        <a href="<?php echo app_url('admin/categories.php'); ?>"<?php echo ($activePage ?? '') === 'categories' ? ' class="is-active"' : ''; ?>>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/></svg>
            <span>Categories</span>
        </a>
    </nav>
    <div class="sidebar-foot">
        <div class="sidebar-user">
            <span class="avatar small" aria-hidden="true"><?php echo e($currentInitials); ?></span>
            <div>
                <strong><?php echo e($current_user['full_name'] ?? $current_user['username']); ?></strong>
                <small>Administrator</small>
            </div>
        </div>
        <a href="<?php echo app_url('dashboard.php'); ?>" class="btn ghost">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            User Mode
        </a>
    </div>
</aside>
