<?php
/**
 * Business Unit managers view
 */
?>
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Manage Business Unit Managers</h4>
                <p class="card-description">
                    Assign managers to business unit: <strong><?= e($businessUnit['name']); ?></strong>
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
                
                <form class="forms-sample" method="POST" action="<?= base_url('business_unit/managers/' . $businessUnit['id']); ?>">
                    <div class="form-group">
                        <label>Select Managers</label>
                        <div class="row">
                            <?php if (empty($managers)): ?>
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        No managers available. Please create manager accounts first.
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($managers as $manager): ?>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="managers[]" value="<?= $manager['id']; ?>"
                                                    <?php 
                                                    foreach ($assignedManagers as $assignedManager) {
                                                        if ($assignedManager['id'] == $manager['id']) {
                                                            echo 'checked';
                                                            break;
                                                        }
                                                    }
                                                    ?>
                                                >
                                                <?= e($manager['name']); ?> (<?= e($manager['email']); ?>)
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mr-2">Save</button>
                    <a href="<?= base_url('business_unit'); ?>" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
