<?php
/**
 * Evaluation view page
 */
?>
<link rel="stylesheet" href="<?= asset_url('css/evaluation-form-new.css'); ?>">
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">Evaluation Details</h4>
                    <div>
                        <a href="<?= base_url('evaluation'); ?>" class="btn btn-light">
                            <i class="mdi mdi-arrow-left"></i> Back to Evaluations
                        </a>
                        <a href="<?= base_url('evaluation/edit/' . $evaluation['id']); ?>" class="btn btn-primary ml-2">
                            <i class="mdi mdi-pencil"></i> Edit
                        </a>
                        <a href="<?= base_url('evaluation/delete/' . $evaluation['id']); ?>" class="btn btn-danger ml-2" onclick="return confirm('Are you sure you want to delete this evaluation?');">
                            <i class="mdi mdi-delete"></i> Delete
                        </a>

                        <a href="<?= base_url('evaluation/export/excel/' . $evaluation['id']); ?>" class="btn btn-success ml-2">
                            <i class="mdi mdi-file-excel"></i> Export Excel
                        </a>
                        <a href="<?= base_url('evaluation/export/pdf/' . $evaluation['id']); ?>" class="btn btn-warning ml-2">
                            <i class="mdi mdi-file-pdf"></i> Export PDF
                        </a>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered evaluation-table">
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="criterion-header">
                                            <h5>Evaluation Summary</h5>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="20%"><strong>Agent:</strong></td>
                                        <td width="30%"><?= e($agent['name']); ?></td>
                                        <td width="20%"><strong>Evaluation Date:</strong></td>
                                        <td width="30%"><?= date('d M Y', strtotime($evaluation['evaluation_date'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td><?= e($agent['email']); ?></td>
                                        <td><strong>Evaluator:</strong></td>
                                        <td><?= e($user['name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Activity:</strong></td>
                                        <td><?= e($activity['name']); ?></td>
                                        <td><strong>Total Score:</strong></td>
                                        <td>
                                            <span class="badge badge-<?= $evaluation['score_total'] >= 80 ? 'success' : ($evaluation['score_total'] >= 60 ? 'warning' : 'danger'); ?>">
                                                <?= number_format($evaluation['score_total'], 2); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php if (!empty($evaluation['comments'])): ?>
                                    <tr>
                                        <td><strong>Overall Comments:</strong></td>
                                        <td colspan="3"><?= nl2br(e($evaluation['comments'])); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php if (empty($template['criteria'])): ?>
                    <div class="alert alert-warning">
                        This template has no criteria.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered evaluation-table">
                            <thead>
                                <tr>
                                    <th width="30%">Criterion / Subcriterion</th>
                                    <th width="10%">Weight</th>
                                    <th width="15%">Score</th>
                                    <th width="15%">Notation</th>
                                    <th width="30%">Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($template['criteria'] as $criterion): ?>
                                    <!-- Criterion header row -->
                                    <tr class="criterion-row">
                                        <td colspan="5" class="criterion-header">
                                            <h5><?= e($criterion['name']); ?> (Weight: <?= $criterion['weight']; ?>)</h5>
                                            <?php if (!empty($criterion['description'])): ?>
                                                <p><?= e($criterion['description']); ?></p>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <?php if (empty($criterion['subcriteria'])): ?>
                                        <tr>
                                            <td colspan="5" class="text-warning">
                                                This criterion has no subcriteria.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($criterion['subcriteria'] as $subcriterion): ?>
                                            <?php
                                            // Find result for this subcriterion
                                            $score = null;
                                            $comments = '';
                                            $notation = '';

                                            // Get result from results map
                                            if (isset($results_map[$subcriterion['id']])) {
                                                $result = $results_map[$subcriterion['id']];
                                                $score = $result['score'];
                                                $comments = $result['comments'];
                                                $notation = $result['notation'];
                                            }
                                            ?>
                                            <tr class="subcriterion-row">
                                                <td>
                                                    <strong><?= e($subcriterion['name']); ?></strong>
                                                    <!-- <?php if (!empty($subcriterion['description'])): ?>
                                                        <p class="mb-0 mt-1 text-muted"><?= e($subcriterion['description']); ?></p>
                                                    <?php endif; ?> -->
                                                </td>
                                                <td><?= $subcriterion['weight']; ?></td>
                                                <td>
                                                    <?php if ($score !== null): ?>
                                                                                                                    <?= $score; ?>

                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($notation)): ?>
                                                        <?php
                                                        $badgeClass = '';
                                                        $notationText = '';

                                                        switch ($notation) {
                                                            case 'C':
                                                                $badgeClass = 'success';
                                                                $notationText = 'Conforme';
                                                                break;
                                                            case 'NC':
                                                                $badgeClass = 'danger';
                                                                $notationText = 'Non conforme';
                                                                break;
                                                            case 'PC':
                                                                $badgeClass = 'warning';
                                                                $notationText = 'Point critique';
                                                                break;
                                                            case 'SI':
                                                                $badgeClass = 'dark';
                                                                $notationText = 'Situation inacceptable';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="badge badge-<?= $badgeClass; ?>">
                                                            <?= $notation; ?>
                                                        </span>
                                                        <small><?= $notationText; ?></small>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= nl2br(e($comments)); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
