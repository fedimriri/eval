<?php
/**
 * Agent edit view
 */
?>
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Agent</h4>
                <p class="card-description">
                    Update agent information
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
                
                <form class="forms-sample" method="POST" action="<?= base_url('agent/edit/' . $agent['id']); ?>">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Agent Name" value="<?= e($name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Agent Email" value="<?= e($email); ?>">
                        <small class="form-text text-muted">Email is optional but must be unique if provided</small>
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
                    <button type="submit" class="btn btn-primary mr-2">Update</button>
                    <a href="<?= base_url('agent'); ?>" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
