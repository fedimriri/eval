<?php
/**
 * Evaluation edit view
 */
?>
<link rel="stylesheet" href="<?= asset_url('css/evaluation-form.css'); ?>">
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">Edit Evaluation</h4>
                    <div>
                        <button type="button" class="btn btn-info mr-2" data-toggle="modal" data-target="#helpModal">
                            <i class="mdi mdi-help-circle"></i> Help
                        </button>
                        <a href="<?= base_url('evaluation'); ?>" class="btn btn-light">
                            <i class="mdi mdi-arrow-left"></i> Back to Evaluations
                        </a>
                        <a href="<?= base_url('evaluation/viewEvaluation/' . $evaluation['id']); ?>" class="btn btn-info ml-2">
                            <i class="mdi mdi-eye"></i> View Evaluation
                        </a>
                    </div>
                </div>

                <!-- Help Modal -->
                <div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="helpModalLabel">Evaluation Form Help</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <h6>Notation Types:</h6>
                        <ul>
                          <li><span class="badge badge-success">C</span> - Conforme (Full weight)</li>
                          <li><span class="badge badge-danger">NC</span> - Non conforme (0 points)</li>
                          <li><span class="badge badge-warning">PC</span> - Point critique (0 for the main criteria)</li>
                          <li><span class="badge badge-dark">SI</span> - Situation inacceptable (0 for the entire evaluation)</li>
                        </ul>

                        <h6 class="mt-4">Keyboard Shortcuts:</h6>
                        <ul>
                          <li><strong>1</strong> - Select "C" (Conforme) for the current row</li>
                          <li><strong>2</strong> - Select "NC" (Non conforme) for the current row</li>
                          <li><strong>3</strong> - Select "PC" (Point critique) for the current row</li>
                          <li><strong>4</strong> - Select "SI" (Situation inacceptable) for the current row</li>
                          <li><strong>Tab</strong> - Move to the next field</li>
                        </ul>

                        <div class="alert alert-info mt-3">
                          <i class="mdi mdi-information-outline mr-2"></i>
                          All subcriteria evaluations are required. The form will automatically guide you to the next unanswered question.
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Agent Information</h5>
                            <p><strong>Name:</strong> <?= e($agent['name']); ?></p>
                            <p><strong>Email:</strong> <?= e($agent['email']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Activity Information</h5>
                            <p><strong>Activity:</strong> <?= e($activity['name']); ?></p>
                            <p><strong>Template:</strong> <?= e($template['name']); ?></p>
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

                <form class="forms-sample" method="POST" action="<?= base_url('evaluation/edit/' . $evaluation['id']); ?>">

                    <?php if (empty($template['criteria'])): ?>
                        <div class="alert alert-warning">
                            This template has no criteria. Please add criteria to the template first.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered evaluation-table">
                                <thead>
                                    <tr>
                                        <th width="30%">Criterion / Subcriterion</th>
                                        <th width="10%">Weight</th>
                                        <th width="40%">Notation</th>
                                        <th width="20%">Comments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($template['criteria'] as $criterion): ?>
                                        <!-- Criterion header row -->
                                        <tr class="criterion-row">
                                            <td colspan="4" class="criterion-header">
                                                <h5><?= e($criterion['name']); ?> (Weight: <?= $criterion['weight']; ?>)</h5>
                                                <?php if (!empty($criterion['description'])): ?>
                                                    <p><?= e($criterion['description']); ?></p>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <?php if (empty($criterion['subcriteria'])): ?>
                                            <tr>
                                                <td colspan="4" class="text-warning">
                                                    This criterion has no subcriteria. Please add subcriteria to this criterion.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($criterion['subcriteria'] as $subcriterion): ?>
                                                <?php
                                                // Get existing notation and comment if available
                                                $existingNotation = isset($results_map[$subcriterion['id']]) ? $results_map[$subcriterion['id']]['notation'] : '';
                                                $existingComment = isset($results_map[$subcriterion['id']]) ? $results_map[$subcriterion['id']]['comments'] : '';
                                                ?>
                                                <tr class="subcriterion-row">
                                                    <td>
                                                        <strong><?= e($subcriterion['name']); ?></strong>
                                                        <?php if (!empty($subcriterion['description'])): ?>
                                                            <p class="mb-0 mt-1 text-muted"><?= e($subcriterion['description']); ?></p>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= $subcriterion['weight']; ?></td>
                                                    <td>
                                                        <div class="notation-badges">
                                                            <label class="notation-badge-label" for="notation_c_<?= $subcriterion['id']; ?>">
                                                                <input class="notation-radio" type="radio" name="notation_<?= $subcriterion['id']; ?>" id="notation_c_<?= $subcriterion['id']; ?>" value="C" <?= $existingNotation === 'C' ? 'checked' : ''; ?> required>
                                                                <span class="badge badge-pill badge-success badge-lg" data-toggle="tooltip" title="Conforme">C</span>
                                                            </label>
                                                            <label class="notation-badge-label" for="notation_nc_<?= $subcriterion['id']; ?>">
                                                                <input class="notation-radio" type="radio" name="notation_<?= $subcriterion['id']; ?>" id="notation_nc_<?= $subcriterion['id']; ?>" value="NC" <?= $existingNotation === 'NC' ? 'checked' : ''; ?> required>
                                                                <span class="badge badge-pill badge-danger badge-lg" data-toggle="tooltip" title="Non conforme">NC</span>
                                                            </label>
                                                            <label class="notation-badge-label" for="notation_pc_<?= $subcriterion['id']; ?>">
                                                                <input class="notation-radio" type="radio" name="notation_<?= $subcriterion['id']; ?>" id="notation_pc_<?= $subcriterion['id']; ?>" value="PC" <?= $existingNotation === 'PC' ? 'checked' : ''; ?> required>
                                                                <span class="badge badge-pill badge-warning badge-lg" data-toggle="tooltip" title="Point critique">PC</span>
                                                            </label>
                                                            <label class="notation-badge-label" for="notation_si_<?= $subcriterion['id']; ?>">
                                                                <input class="notation-radio" type="radio" name="notation_<?= $subcriterion['id']; ?>" id="notation_si_<?= $subcriterion['id']; ?>" value="SI" <?= $existingNotation === 'SI' ? 'checked' : ''; ?> required>
                                                                <span class="badge badge-pill badge-dark badge-lg" data-toggle="tooltip" title="Situation inacceptable">SI</span>
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <textarea class="form-control" name="comment_<?= $subcriterion['id']; ?>" rows="1"><?= e($existingComment); ?></textarea>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="comments">Overall Comments</label>
                        <textarea class="form-control" id="comments" name="comments" rows="4"><?= e($comments); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary mr-2">Update Evaluation</button>
                    <a href="<?= base_url('evaluation'); ?>" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset_url('js/evaluation-form.js'); ?>"></script>
