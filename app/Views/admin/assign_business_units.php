<?php
/**
 * Admin assign business units view
 */
?>
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Assign Business Units</h4>
                <p class="card-description">
                    Assign business units to manager: <strong><?= e($manager['name']); ?></strong>
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
                
                <form class="forms-sample" method="POST" action="<?= base_url('admin/assign_business_units/' . $manager['id']); ?>">
                    <div class="form-group">
                        <label>Select Business Units</label>
                        <div class="row">
                            <?php if (empty($businessUnits)): ?>
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        No business units available. Please create business units first.
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($businessUnits as $businessUnit): ?>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="business_units[]" value="<?= $businessUnit['id']; ?>"
                                                    <?php 
                                                    foreach ($assignedBusinessUnits as $assignedBusinessUnit) {
                                                        if ($assignedBusinessUnit['id'] == $businessUnit['id']) {
                                                            echo 'checked';
                                                            break;
                                                        }
                                                    }
                                                    ?>
                                                >
                                                <?= e($businessUnit['name']); ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mr-2">Save</button>
                    <a href="<?= base_url('admin/users'); ?>" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
