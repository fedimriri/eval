<?php
/**
 * Activity index view
 */
?>
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">Activities</h4>
                    <?php if ($user['role'] === 'admin'): ?>
                    <a href="<?= base_url('activity/create'); ?>" class="btn btn-primary">
                        <i class="mdi mdi-plus"></i> Add Activity
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
                                <th>Business Unit</th>
                                <th>Description</th>
                                <?php if ($user['role'] === 'admin'): ?>
                                <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($activities)): ?>
                                <tr>
                                    <td colspan="<?= $user['role'] === 'admin' ? 5 : 4; ?>" class="text-center">No activities found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($activities as $activity): ?>
                                    <tr>
                                        <td><?= $activity['id']; ?></td>
                                        <td><?= e($activity['name']); ?></td>
                                        <td><?= e($activity['business_unit_name']); ?></td>
                                        <td><?= e($activity['description']); ?></td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                        <td>
                                            <a href="<?= base_url('activity/edit/' . $activity['id']); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="mdi mdi-pencil"></i> Edit
                                            </a>
                                            <a href="<?= base_url('activity/delete/' . $activity['id']); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this activity?');">
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
