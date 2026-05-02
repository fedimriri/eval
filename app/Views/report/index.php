<div class="page-content">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Reports Dashboard</h4>
                    <p class="card-description">View and analyze evaluation data</p>

                    <div class="row">
                        <div class="col-md-3 mb-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Total Evaluations</h5>
                                    <h2 class="text-white"><?= $summaryData['totalEvaluations'] ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Average Score</h5>
                                    <h2 class="text-white"><?= $summaryData['averageScore'] ?>%</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Business Units</h5>
                                    <h2 class="text-white"><?= $summaryData['totalBusinessUnits'] ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Agents</h5>
                                    <h2 class="text-white"><?= $summaryData['totalAgents'] ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Report Types</h5>
                                    <div class="list-group">
                                        <a href="<?= base_url('report/business_unit') ?>" class="list-group-item list-group-item-action">
                                            <i class="mdi mdi-office-building"></i> Business Unit Reports
                                        </a>
                                        <a href="<?= base_url('report/agent') ?>" class="list-group-item list-group-item-action">
                                            <i class="mdi mdi-account"></i> Agent Reports
                                        </a>
                                        <a href="<?= base_url('report/activity') ?>" class="list-group-item list-group-item-action">
                                            <i class="mdi mdi-clipboard-text"></i> Activity Reports
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Recent Evaluations</h5>
                                    <?php if (empty($summaryData['recentEvaluations'])): ?>
                                        <p>No recent evaluations found.</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Agent</th>
                                                        <th>Score</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($summaryData['recentEvaluations'] as $evaluation): ?>
                                                        <tr>
                                                            <td><?= date('Y-m-d', strtotime($evaluation['evaluation_date'])) ?></td>
                                                            <td><?= $evaluation['agent_name'] ?></td>
                                                            <td><?= $evaluation['score_total'] ?>%</td>
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

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Performance Overview</h5>
                                    <canvas id="performanceChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Wait for the document to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Make sure jQuery is available
    if (typeof jQuery !== 'undefined') {
        // Sample data for the chart - in a real application, this would come from the server
        var ctx = document.getElementById('performanceChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Average Evaluation Score',
                    data: [75, 82, 78, 85, 80, 88],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    } else {
        console.error('jQuery is not loaded. Please check your scripts.');
    }
});
</script>
