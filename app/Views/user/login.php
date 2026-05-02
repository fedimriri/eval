<?php
/**
 * Login view
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - <?= $config['site_title']; ?></title>
    
    <!-- plugins:css -->
    <link rel="stylesheet" href="<?= asset_url('vendors/mdi/css/materialdesignicons.min.css'); ?>">
    <link rel="stylesheet" href="<?= asset_url('vendors/flag-icon-css/css/flag-icon.min.css'); ?>">
    <link rel="stylesheet" href="<?= asset_url('vendors/css/vendor.bundle.base.css'); ?>">
    
    <!-- Layout styles -->
    <link rel="stylesheet" href="<?= asset_url('css/style.css'); ?>">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= asset_url('images/fav-32x32.png'); ?>" />
</head>
<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth">
                <div class="row flex-grow">
                    <div class="col-lg-4 mx-auto">
                        <!-- Flash messages -->
                        <?php $flashes = get_flashes(); ?>
                        <?php if (!empty($flashes)): ?>
                            <?php foreach ($flashes as $flash): ?>
                                <div class="alert alert-<?= $flash['type']; ?> alert-dismissible fade show" role="alert">
                                    <?= $flash['message']; ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <div class="auth-form-light text-left p-5">
                            <div class="brand-logo">
                                <img src="<?= asset_url('images/uman.png'); ?>" alt="logo">
                            </div>
                            <h4>Hello! let's get started</h4>
                            <h6 class="font-weight-light">Sign in to continue.</h6>
                            <form class="pt-3" method="POST" action="<?= base_url('user/login'); ?>">
                                <div class="form-group">
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="Email" value="<?= e($email); ?>" required>
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Password" required>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">SIGN IN</button>
                                </div>
                               
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- content-wrapper ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    
    <!-- plugins:js -->
    <script src="<?= asset_url('vendors/js/vendor.bundle.base.js'); ?>"></script>
    
    <!-- Custom js -->
    <script src="<?= asset_url('js/off-canvas.js'); ?>"></script>
    <script src="<?= asset_url('js/hoverable-collapse.js'); ?>"></script>
    <script src="<?= asset_url('js/misc.js'); ?>"></script>
</body>
</html>
