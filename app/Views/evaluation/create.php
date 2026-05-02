<?php
/**
 * Evaluation create view
 */
?>
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Create Evaluation</h4>
                <p class="card-description">
                    Select business unit, activity, and agent to evaluate
                </p>

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

                <form class="forms-sample" method="POST" action="<?= base_url('evaluation/create'); ?>">
                    <div class="form-group">
                        <label for="business_unit_id">Select Business Unit</label>
                        <select class="form-control" id="business_unit_id" name="business_unit_id" required>
                            <option value="">Select Business Unit</option>
                            <?php foreach ($businessUnits as $bu): ?>
                                <option value="<?= $bu['id']; ?>" <?= isset($business_unit_id) && $business_unit_id == $bu['id'] ? 'selected' : ''; ?>>
                                    <?= e($bu['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="activity_id">Select Activity</label>
                        <select class="form-control" id="activity_id" name="activity_id" required <?= !isset($activity_id) || empty($activity_id) ? 'disabled' : ''; ?>>
                            <option value="">Select Activity</option>
                            <?php if (isset($activity_id) && !empty($activity_id)): ?>
                                <option value="<?= $activity_id; ?>" selected data-preselected="true">
                                    Selected Activity
                                </option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="agent_id">Select Agent</label>
                        <select class="form-control" id="agent_id" name="agent_id" required <?= !isset($agent_id) || empty($agent_id) ? 'disabled' : ''; ?>>
                            <option value="">Select Agent</option>
                            <?php if (isset($agent_id) && !empty($agent_id)): ?>
                                <option value="<?= $agent_id; ?>" selected data-preselected="true">
                                    Selected Agent
                                </option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary mr-2">Continue</button>
                    <a href="<?= base_url('evaluation'); ?>" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset_url('js/evaluation-create.js'); ?>"></script>
