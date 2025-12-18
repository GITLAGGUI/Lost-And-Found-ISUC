<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Recover password';
include __DIR__ . '/includes/header.php';
?>
<section class="form-card">
    <h1>Password recovery</h1>
    <p class="section-subtext">Enter your ISU email and we&apos;ll simulate a reset link for the prototype.</p>
    <form action="#" method="post">
        <div class="form-group">
            <label for="recover_email">ISU Email</label>
            <input type="email" id="recover_email" name="recover_email" placeholder="name@isu.edu.ph" required />
        </div>
        <div class="form-group">
            <button type="submit" class="btn primary" style="width:100%">Send reset instructions</button>
        </div>
        <p style="text-align:center"><a href="login.php">Back to log in</a></p>
    </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
