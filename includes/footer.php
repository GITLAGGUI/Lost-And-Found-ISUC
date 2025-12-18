</main>
<footer class="site-footer">
    <div class="footer-inner">
    <div class="footer-brand">
        <a href="<?php echo app_url('index.php'); ?>" class="footer-logo">
            <img src="<?php echo app_url('assets/images/logo.png'); ?>" alt="ISU Lost &amp; Found logo" />
            <div>
                <strong><?php echo APP_NAME; ?></strong>
                <small><?php echo APP_TAGLINE; ?></small>
            </div>
        </a>
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Designed to keep campus belongings organized, secure, and easy to claim.</p>
    </div>
    <div class="footer-grid">
        <div>
            <h4>Quick links</h4>
            <a href="<?php echo app_url('index.php'); ?>">Home</a>
            <a href="<?php echo app_url('listings.php'); ?>">Browse listings</a>
            <a href="<?php echo app_url('listings.php#found'); ?>">Found board</a>
        </div>
        <div>
            <h4>Support</h4>
            <?php if (!is_logged_in()): ?>
                <a href="<?php echo app_url('register.php'); ?>">Create account</a>
                <a href="<?php echo app_url('login.php'); ?>">Log in</a>
            <?php endif; ?>
            <a href="<?php echo app_url('listings.php'); ?>">Submit an item</a>
        </div>
        <div class="footer-contact">
            <h4>Contact us</h4>
            <ul class="contact-list">
                <li>
                    <span class="icon-circle small" aria-hidden="true">
                        <svg style="color:white;" viewBox="0 0 24 24" role="presentation"><path d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path><path d="M4 8l8 5 8-5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                    </span>
                    <a href="mailto:lostfound@isu.edu.ph">lostfound@isu.edu.ph</a>
                </li>
                <li>
                    <span class="icon-circle small" aria-hidden="true">
                        <svg style="color:white;" viewBox="0 0 24 24" role="presentation"><path d="M5 4h3l2 5-2 2a13 13 0 0 0 5 5l2-2 5 2v3a2 2 0 0 1-2 2A16 16 0 0 1 4 6a2 2 0 0 1 1-2z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                    </span>
                    <a href="tel:+639178901234">+63 917 890 1234</a>
                </li>
                <li>
                    <span class="icon-circle small" aria-hidden="true">
                        <svg style="color:white;" viewBox="0 0 24 24" role="presentation"><path d="M12 3a7 7 0 0 1 7 7c0 5-7 11-7 11s-7-6-7-11a7 7 0 0 1 7-7z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path><circle cx="12" cy="10" r="2.5" fill="none" stroke="currentColor" stroke-width="1.4"></circle></svg>
                    </span>
                    <span>ISU Security Office, Echague, Isabela</span>
                </li>
            </ul>
        </div>
    </div>
    </div>
</footer>
<script src="<?php echo app_url('assets/js/main.js'); ?>"></script>
<?php if (!empty($extraScripts) && is_array($extraScripts)): ?>
    <?php foreach ($extraScripts as $scriptSrc): ?>
        <script src="<?php echo e($scriptSrc); ?>" defer></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
