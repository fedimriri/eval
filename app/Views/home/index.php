<?php
/**
 * Dashboard view
 */
?>
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Welcome to QA Evaluation App</h4>
                <p class="card-description">
                    This dashboard provides an overview of your call center quality assurance evaluations.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Quick Actions</h4>
                <div class="mt-4">
                    <a href="<?= base_url('evaluation/create'); ?>" class="btn btn-primary btn-lg btn-block">
                        <i class="mdi mdi-clipboard-check mr-2"></i>New Evaluation
                    </a>
                </div>
                <div class="mt-3">
                    <a href="<?= base_url('report'); ?>" class="btn btn-info btn-lg btn-block">
                        <i class="mdi mdi-chart-bar mr-2"></i>View Reports
                    </a>
                </div>
                <?php if (current_user()['role'] === 'admin'): ?>
                <div class="mt-3">
                    <a href="<?= base_url('evaluation_template'); ?>" class="btn btn-success btn-lg btn-block">
                        <i class="mdi mdi-file-document-edit mr-2"></i>Manage Templates
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-row justify-content-between">
                    <h4 class="card-title mb-1">Recent Evaluations</h4>
                    <p class="text-muted mb-1">Last 5 evaluations</p>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="preview-list">
                            <?php
                            // Get recent evaluations
                            $evaluations = [];

                            // We'll use a simple approach for the dashboard
                            // In a real application, you would use a proper controller method
                            require_once dirname(dirname(__DIR__)) . '/Core/Database.php';
                            $db = new Database();

                            if (current_user()['role'] === 'admin') {
                                $query = "SELECT e.*, a.name as agent_name, u.name as evaluator_name, act.name as activity_name
                                          FROM evaluations e
                                          JOIN agents a ON e.agent_id = a.id
                                          JOIN users u ON e.evaluator_id = u.id
                                          JOIN activities act ON e.activity_id = act.id
                                          ORDER BY e.created_at DESC LIMIT 5";
                                $db->query($query);
                                $evaluations = $db->resultSet();
                            } else {
                                $query = "SELECT e.*, a.name as agent_name, u.name as evaluator_name, act.name as activity_name
                                          FROM evaluations e
                                          JOIN agents a ON e.agent_id = a.id
                                          JOIN users u ON e.evaluator_id = u.id
                                          JOIN activities act ON e.activity_id = act.id
                                          WHERE e.evaluator_id = :evaluator_id
                                          ORDER BY e.created_at DESC LIMIT 5";
                                $db->query($query);
                                $db->bind(':evaluator_id', current_user()['id']);
                                $evaluations = $db->resultSet();
                            }

                            if (empty($evaluations)):
                            ?>
                            <div class="preview-item border-bottom">
                                <div class="preview-item-content d-sm-flex flex-grow">
                                    <div class="flex-grow text-center">
                                        <p class="text-muted mb-0">No evaluations found. Start by creating a new evaluation.</p>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                                <?php foreach ($evaluations as $evaluation): ?>
                                <div class="preview-item border-bottom">
                                    <div class="preview-thumbnail">
                                        <div class="preview-icon bg-primary">
                                            <i class="mdi mdi-account-check"></i>
                                        </div>
                                    </div>
                                    <div class="preview-item-content d-sm-flex flex-grow">
                                        <div class="flex-grow">
                                            <h6 class="preview-subject"><?= e($evaluation['agent_name']); ?> - <?= e($evaluation['activity_name']); ?></h6>
                                            <p class="text-muted mb-0">Score: <?= number_format($evaluation['score_total'], 2); ?></p>
                                        </div>
                                        <div class="mr-auto text-sm-right pt-2 pt-sm-0">
                                            <p class="text-muted"><?= date('d M Y', strtotime($evaluation['evaluation_date'])); ?></p>
                                            <p class="text-muted mb-0">By <?= e($evaluation['evaluator_name']); ?></p>
                                            <a href="<?= base_url('evaluation/viewEvaluation/' . $evaluation['id']); ?>" class="btn btn-sm btn-outline-primary mt-2">View</a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
