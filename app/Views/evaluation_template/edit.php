<?php
/**
 * Evaluation Template edit view
 */
?>
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Evaluation Template</h4>
                <p class="card-description">
                    Update evaluation template information
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
                
                <form class="forms-sample" method="POST" action="<?= base_url('evaluation_template/edit/' . $template['id']); ?>">
                    <div class="form-group">
                        <label for="name">Template Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Template Name" value="<?= e($name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="activity_id">Activity</label>
                        <select class="form-control" id="activity_id" name="activity_id" required>
                            <option value="">Select Activity</option>
                            <?php foreach ($activities as $activity): ?>
                                <option value="<?= $activity['id']; ?>" <?= $activity_id == $activity['id'] ? 'selected' : ''; ?>>
                                    <?= e($activity['name']); ?> (<?= e($activity['business_unit_name']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="form-check form-check-flat form-check-primary">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" name="is_active" <?= $is_active ? 'checked' : ''; ?>>
                                Set as active template for this activity
                                <i class="input-helper"></i>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mr-2">Update Template</button>
                    <a href="<?= base_url('evaluation_template'); ?>" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
