<?php
/**
 * Error index view
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Not Found - <?= $config['site_title']; ?></title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= asset_url('images/favicon.png'); ?>" />
    <!-- CSS -->
    <link rel="stylesheet" href="<?= asset_url('vendors/mdi/css/materialdesignicons.min.css'); ?>">
    <link rel="stylesheet" href="<?= asset_url('vendors/css/vendor.bundle.base.css'); ?>">
    <link rel="stylesheet" href="<?= asset_url('css/style.css'); ?>">
</head>
<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center text-center error-page bg-primary">
                <div class="row flex-grow">
                    <div class="col-lg-7 mx-auto text-white">
                        <div class="row align-items-center d-flex flex-row">
                            <div class="col-lg-6 text-lg-right pr-lg-4">
                                <h1 class="display-1 mb-0">404</h1>
                            </div>
                            <div class="col-lg-6 error-page-divider text-lg-left pl-lg-4">
                                <h2>SORRY!</h2>
                                <h3 class="font-weight-light">The view you're looking for was not found.</h3>
                            </div>
                        </div>
                        <div class="row mt-5">
                            <div class="col-12 text-center mt-xl-2">
                                <p class="text-white font-weight-medium text-center">The requested view does not exist or has been moved.</p>
                            </div>
                        </div>
                        <div class="row mt-5">
                            <div class="col-12 text-center mt-xl-2">
                                <a class="text-white font-weight-medium" href="<?= base_url(); ?>">Back to home</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- JavaScript -->
    <script src="<?= asset_url('vendors/js/vendor.bundle.base.js'); ?>"></script>
    <script src="<?= asset_url('js/off-canvas.js'); ?>"></script>
    <script src="<?= asset_url('js/hoverable-collapse.js'); ?>"></script>
    <script src="<?= asset_url('js/misc.js'); ?>"></script>
</body>
</html>
