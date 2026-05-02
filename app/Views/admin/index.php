<?php
/**
 * Admin index view
 */
?>
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Admin Dashboard</h4>
                <p class="card-description">
                    Welcome to the admin dashboard. Here you can manage users and system settings.
                </p>
                
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="mdi mdi-account-multiple text-primary" style="font-size: 48px;"></i>
                                <h5 class="mt-3">User Management</h5>
                                <p>Manage users, create new accounts, and assign roles</p>
                                <a href="<?= base_url('admin/users'); ?>" class="btn btn-primary mt-3">Manage Users</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="mdi mdi-office-building text-info" style="font-size: 48px;"></i>
                                <h5 class="mt-3">Business Units</h5>
                                <p>Manage business units and assign managers</p>
                                <a href="<?= base_url('business_unit'); ?>" class="btn btn-info mt-3">Manage Business Units</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="mdi mdi-file-document-edit text-success" style="font-size: 48px;"></i>
                                <h5 class="mt-3">Evaluation Templates</h5>
                                <p>Create and manage evaluation templates</p>
                                <a href="<?= base_url('evaluation_template'); ?>" class="btn btn-success mt-3">Manage Templates</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
