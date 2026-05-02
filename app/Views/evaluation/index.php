<?php
/**
 * Evaluation index view
 */
?>

<link rel="stylesheet" href="<?= asset_url('css/evaluation-form.css'); ?>">

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">Evaluations</h4>
                    <a href="<?= base_url('evaluation/create'); ?>" class="btn btn-primary">
                        <i class="mdi mdi-plus"></i> New Evaluation
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

                <?php include 'filters.php'; ?>



                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Agent</th>
                                <th>Activity</th>
                                <?php if ($user['role'] === 'admin'): ?>
                                <th>Business Unit</th>
                                <th>Evaluator</th>
                                <?php endif; ?>
                                <th>Date</th>
                                <th>Score</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($evaluations)): ?>
                                <tr>
                                    <td colspan="<?= $user['role'] === 'admin' ? 8 : 6; ?>" class="text-center">No evaluations found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($evaluations as $evaluation): ?>
                                    <tr>
                                        <td><?= $evaluation['id']; ?></td>
                                        <td><?= e($evaluation['agent_name']); ?></td>
                                        <td><?= e($evaluation['activity_name']); ?></td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                        <td><?= e($evaluation['business_unit_name']); ?></td>
                                        <td><?= e($evaluation['evaluator_name']); ?></td>
                                        <?php endif; ?>
                                        <td><?= date('d M Y', strtotime($evaluation['evaluation_date'])); ?></td>
                                        <td>
                                            <div class="badge badge-<?= $evaluation['score_total'] >= 80 ? 'success' : ($evaluation['score_total'] >= 60 ? 'warning' : 'danger'); ?>">
                                                <?= number_format($evaluation['score_total'], 2)." %"; ?> 
                                            </div>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('evaluation/viewEvaluation/' . $evaluation['id']); ?>" class="btn btn-sm btn-outline-info">
                                                <i class="mdi mdi-eye"></i> View
                                            </a>
                                            <a href="<?= base_url('evaluation/edit/' . $evaluation['id']); ?>" class="btn btn-sm btn-outline-primary ml-1">
                                                <i class="mdi mdi-pencil"></i> Edit
                                            </a>
                                            <a href="<?= base_url('evaluation/delete/' . $evaluation['id']); ?>" class="btn btn-sm btn-outline-danger ml-1" onclick="return confirm('Are you sure you want to delete this evaluation?');">
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
