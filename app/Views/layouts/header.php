<?php
/**
 * Header layout
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="base-url" content="<?= base_url(); ?>">
    <title><?= isset($title) ? $title . ' - ' . $config['site_title'] : $config['site_title']; ?></title>

    <!-- plugins:css -->
    <link rel="stylesheet" href="<?= asset_url('vendors/mdi/css/materialdesignicons.min.css'); ?>">
    <link rel="stylesheet" href="<?= asset_url('vendors/flag-icon-css/css/flag-icon.min.css'); ?>">
    <link rel="stylesheet" href="<?= asset_url('vendors/css/vendor.bundle.base.css'); ?>">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="<?= asset_url('vendors/datatables/dataTables.bootstrap4.min.css'); ?>">

    <!-- Layout styles -->
    <link rel="stylesheet" href="<?= asset_url('css/style.css'); ?>">

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= asset_url('images/fav-32x32.png'); ?>" />
</head>
<body class="sidebar-fixed">
    <div class="container-scroller">
        <?php if (is_authenticated()): ?>
            <!-- Include navbar -->
            <?php include_once dirname(dirname(__DIR__)) . '/Views/partials/navbar.php'; ?>

            <div class="container-fluid page-body-wrapper">
                <!-- Include sidebar -->
                <?php include_once dirname(dirname(__DIR__)) . '/Views/partials/sidebar.php'; ?>

                <div class="main-panel">
                    <div class="content-wrapper">
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
        <?php endif; ?>
