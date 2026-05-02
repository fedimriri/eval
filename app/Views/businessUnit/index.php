<?php
/**
 * Business Unit index view
 */
?>
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">Business Units</h4>
                    <?php if ($user['role'] === 'admin'): ?>
                    <a href="<?= base_url('business_unit/create'); ?>" class="btn btn-primary">
                        <i class="mdi mdi-plus"></i> Add Business Unit
                    </a>
                    <?php endif; ?>
                </div>

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

                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <?php if ($user['role'] === 'admin'): ?>
                                <th>Managers</th>
                                <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($businessUnits)): ?>
                                <tr>
                                    <td colspan="<?= $user['role'] === 'admin' ? 5 : 3; ?>" class="text-center">No business units found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($businessUnits as $businessUnit): ?>
                                    <tr>
                                        <td><?= $businessUnit['id']; ?></td>
                                        <td><?= e($businessUnit['name']); ?></td>
                                        <td><?= e($businessUnit['description']); ?></td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                        <td>
                                            <?php if (isset($businessUnit['manager_count'])): ?>
                                                <?= $businessUnit['manager_count']; ?>
                                                <a href="<?= base_url('business_unit/managers/' . $businessUnit['id']); ?>" class="btn btn-sm btn-outline-info ml-2">
                                                    <i class="mdi mdi-account-multiple"></i> Manage
                                                </a>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('business_unit/edit/' . $businessUnit['id']); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="mdi mdi-pencil"></i> Edit
                                            </a>
                                            <a href="<?= base_url('business_unit/delete/' . $businessUnit['id']); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this business unit?');">
                                                <i class="mdi mdi-delete"></i> Delete
                                            </a>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
