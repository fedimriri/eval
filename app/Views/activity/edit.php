<?php
/**
 * Activity edit view
 */
?>
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Activity</h4>
                <p class="card-description">
                    Update activity information
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
                
                <form class="forms-sample" method="POST" action="<?= base_url('activity/edit/' . $activity['id']); ?>">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Activity Name" value="<?= e($name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="business_unit_id">Business Unit</label>
                        <select class="form-control" id="business_unit_id" name="business_unit_id" required>
                            <option value="">Select Business Unit</option>
                            <?php foreach ($businessUnits as $businessUnit): ?>
                                <option value="<?= $businessUnit['id']; ?>" <?= $business_unit_id == $businessUnit['id'] ? 'selected' : ''; ?>>
                                    <?= e($businessUnit['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Activity Description"><?= e($description); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary mr-2">Update</button>
                    <a href="<?= base_url('activity'); ?>" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
