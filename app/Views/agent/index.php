<?php
/**
 * Agent index view
 */
?>
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">Agents</h4>
                    <a href="<?= base_url('agent/create'); ?>" class="btn btn-primary">
                        <i class="mdi mdi-plus"></i> Add Agent
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
                                <th>Email</th>
                                <th>Activity</th>
                                <th>Business Unit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($agents)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No agents found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($agents as $agent): ?>
                                    <tr>
                                        <td><?= $agent['id']; ?></td>
                                        <td><?= e($agent['name']); ?></td>
                                        <td><?= e($agent['email']); ?></td>
                                        <td><?= e($agent['activity_name']); ?></td>
                                        <td><?= e($agent['business_unit_name']); ?></td>
                                        <td>
                                            <a href="<?= base_url('agent/edit/' . $agent['id']); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="mdi mdi-pencil"></i> Edit
                                            </a>
                                            <a href="<?= base_url('agent/delete/' . $agent['id']); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this agent?');">
                                                <i class="mdi mdi-delete"></i> Delete
                                            </a>
                                            <a href="<?= base_url('evaluation/create?agent_id=' . $agent['id']); ?>" class="btn btn-sm btn-outline-info">
                                                <i class="mdi mdi-clipboard-check"></i> Evaluate
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
