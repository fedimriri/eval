<?php
/**
 * Evaluation Template view
 */
?>
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">View Template</h4>
                    <div>
                        <a href="<?= base_url('evaluation_template'); ?>" class="btn btn-light">
                            <i class="mdi mdi-arrow-left"></i> Back to Templates
                        </a>
                        <?php if (current_user()['role'] === 'admin'): ?>
                        <a href="<?= base_url('evaluation_template/edit/' . $template['id']); ?>" class="btn btn-primary ml-2">
                            <i class="mdi mdi-pencil"></i> Edit Template
                        </a>
                        <a href="<?= base_url('evaluation_template/builder/' . $template['id']); ?>" class="btn btn-info ml-2">
                            <i class="mdi mdi-tools"></i> Template Builder
                        </a>
                        <a href="<?= base_url('evaluation_template/delete/' . $template['id']); ?>" class="btn btn-danger ml-2" onclick="return confirm('Are you sure you want to delete this template?');">
                            <i class="mdi mdi-delete"></i> Delete
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Template Information</h5>
                            <p><strong>Name:</strong> <?= e($template['name']); ?></p>
                            <p><strong>Status:</strong> <?= $template['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Activity Information</h5>
                            <p><strong> Activity:</strong> <?= e($activity['name']); ?></p>
                            <p><strong> Business Unit:</strong> <?= e($activity['business_unit_name']); ?></p>
                        </div>
                    </div>
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
                
                <h5 class="mt-4 mb-3">Evaluation Criteria</h5>
                
                <?php if (empty($template['criteria'])): ?>
                    <div class="alert alert-warning">
                        This template has no criteria. Use the Template Builder to add criteria.
                    </div>
                <?php else: ?>
                    <?php 
                    $totalWeight = 0;
                    foreach ($template['criteria'] as $criterion) {
                        $totalWeight += $criterion['weight'];
                    }
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="30%">Criterion</th>
                                    <th width="10%">Weight</th>
                                    <th width="10%">Weight %</th>
                                    <th width="45%">Subcriteria</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($template['criteria'] as $index => $criterion): ?>
                                    <tr>
                                        <td><?= $index + 1; ?></td>
                                        <td>
                                            <strong><?= e($criterion['name']); ?></strong>
                                            <?php if (!empty($criterion['description'])): ?>
                                                <p class="text-muted mb-0 mt-1"><?= e($criterion['description']); ?></p>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $criterion['weight']; ?></td>
                                        <td><?= number_format(($criterion['weight'] / $totalWeight) * 100, 2); ?>%</td>
                                        <td>
                                            <?php if (empty($criterion['subcriteria'])): ?>
                                                <div class="alert alert-warning mb-0">
                                                    No subcriteria found.
                                                </div>
                                            <?php else: ?>
                                                <?php 
                                                $totalSubWeight = 0;
                                                foreach ($criterion['subcriteria'] as $subcriterion) {
                                                    $totalSubWeight += $subcriterion['weight'];
                                                }
                                                ?>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th width="5%">#</th>
                                                                <th width="50%">Subcriterion</th>
                                                                <th width="15%">Weight</th>
                                                                <th width="15%">Weight %</th>
                                                                <th width="15%">Overall %</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($criterion['subcriteria'] as $subIndex => $subcriterion): ?>
                                                                <tr>
                                                                    <td><?= $subIndex + 1; ?></td>
                                                                    <td>
                                                                        <strong><?= e($subcriterion['name']); ?></strong>
                                                                        <?php if (!empty($subcriterion['description'])): ?>
                                                                            <p class="text-muted mb-0 mt-1"><?= e($subcriterion['description']); ?></p>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td><?= $subcriterion['weight']; ?></td>
                                                                    <td><?= number_format(($subcriterion['weight'] / $totalSubWeight) * 100, 2); ?>%</td>
                                                                    <td><?= number_format(($subcriterion['weight'] / $totalSubWeight) * ($criterion['weight'] / $totalWeight) * 100, 2); ?>%</td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
