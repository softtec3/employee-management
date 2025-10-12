<?php
    // Allow including pages to suppress the header (logo + logout) to avoid duplicates
    $suppress_topbar_header = isset($suppress_topbar_header) ? (bool)$suppress_topbar_header : false;

    if (!$suppress_topbar_header):
?>
<!-- This is top bar -->
<div class="topbar">
    <div style="display: flex; align-items: center; gap: 15px;">
        <?php
        // Include global configuration for asset paths
        if (function_exists('get_asset_path')) {
            $logo_src = get_asset_path('logo.png');
        } else {
            // Fallback if config not loaded
            $logo_paths = ['./logo.png', '../logo.png', '../../logo.png'];
            $logo_src = './logo.png'; // default
            foreach ($logo_paths as $path) {
                if (file_exists($path)) {
                    $logo_src = $path;
                    break;
                }
            }
        }
        ?>
        <img src="<?php echo $logo_src; ?>" alt="logo">
        <!-- <?php if (isset($display_name)): ?>
            <span style="color: #333; font-weight: 500;">Welcome, <?php echo htmlspecialchars($display_name); ?>!</span>
        <?php endif; ?> -->
    </div>
    <!-- logout form and button -->
    <?php
    // Determine correct logout path
    $logout_paths = ['./logout.php', '../logout.php', '../../logout.php'];
    $logout_action = '../logout.php'; // default
    foreach ($logout_paths as $path) {
        if (file_exists($path)) {
            $logout_action = $path;
            break;
        }
    }
    ?>
    <form action="<?php echo $logout_action; ?>" method="post">
        <button name="logout" value="logout" class="btn">Logout</button>
    </form>
    </div>
<?php endif; ?>

