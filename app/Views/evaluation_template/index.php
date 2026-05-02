<?php
/**
 * Evaluation Template index view
 */
?>
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">Evaluation Templates</h4>
                    <a href="<?= base_url('evaluation_template/create'); ?>" class="btn btn-primary">
                        <i class="mdi mdi-plus"></i> Add Template
                    </a>
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
                                <th>Activity</th>
                                <th>Business Unit</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($templates)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No templates found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($templates as $template): ?>
                                    <tr>
                                        <td><?= $template['id']; ?></td>
                                        <td><?= e($template['name']); ?></td>
                                        <td><?= e($template['activity_name']); ?></td>
                                        <td><?= e($template['business_unit_name']); ?></td>
                                        <td>
                                            <?php if ($template['is_active']): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d M Y', strtotime($template['created_at'])); ?></td>
                                        <td>
                                            <a href="<?= base_url('evaluation_template/inspect/' . $template['id']); ?>" class="btn btn-sm btn-outline-info">
                                                <i class="mdi mdi-eye"></i> View
                                            </a>
                                            <a href="<?= base_url('evaluation_template/builder/' . $template['id']); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="mdi mdi-file-document-edit"></i> Builder
                                            </a>
                                            <a href="<?= base_url('evaluation_template/edit/' . $template['id']); ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="mdi mdi-pencil"></i> Edit
                                            </a>
                                            <a href="<?= base_url('evaluation_template/delete/' . $template['id']); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this template? This will also delete all criteria and subcriteria associated with this template.');">
                                                <i class="mdi mdi-delete"></i> Delete
                                            </a>
                                        </td>
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
