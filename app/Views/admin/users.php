<?php
/**
 * Admin users view
 */
?>
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">Manage Users</h4>
                    <a href="<?= base_url('admin/create_user'); ?>" class="btn btn-primary">
                        <i class="mdi mdi-plus"></i> Add User
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
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No users found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $userItem): ?>
                                    <tr>
                                        <td><?= $userItem['id']; ?></td>
                                        <td><?= e($userItem['name']); ?></td>
                                        <td><?= e($userItem['email']); ?></td>
                                        <td>
                                            <span class="badge badge-<?= $userItem['role'] === 'admin' ? 'danger' : 'info'; ?>">
                                                <?= ucfirst($userItem['role']); ?>
                                            </span>
                                        </td>
                                        <td><?= date('d M Y', strtotime($userItem['created_at'])); ?></td>
                                        <td>
                                            <a href="<?= base_url('admin/edit_user/' . $userItem['id']); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="mdi mdi-pencil"></i> Edit
                                            </a>
                                            
                                            <?php if ($userItem['id'] != $user['id']): ?>
                                                <a href="<?= base_url('admin/delete_user/' . $userItem['id']); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user?');">
                                                    <i class="mdi mdi-delete"></i> Delete
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($userItem['role'] === 'manager'): ?>
                                                <a href="<?= base_url('admin/assign_business_units/' . $userItem['id']); ?>" class="btn btn-sm btn-outline-info">
                                                    <i class="mdi mdi-office-building"></i> Assign Business Units
                                                </a>
                                            <?php endif; ?>
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
