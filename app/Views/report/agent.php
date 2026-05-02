<div class="page-content">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Agent Report</h4>
                    <p class="card-description">View and analyze evaluation data by agent</p>

                    <form method="get" action="<?= base_url('report/agent') ?>" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="agent_id">Agent</label>
                                    <select class="form-control" id="agent_id" name="agent_id" required>
                                        <option value="">Select Agent</option>
                                        <?php foreach ($agents as $agent): ?>
                                            <option value="<?= $agent['id'] ?>" <?= $selectedAgentId == $agent['id'] ? 'selected' : '' ?>>
                                                <?= $agent['name'] ?> (<?= $agent['activity_name'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $startDate ?>" >
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $endDate ?>" >
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">Generate Report</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <?php if (!empty($reportData)): ?>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5>Report for <?= $reportData['agent']['name'] ?></h5>
                                    <div>
                                        <a href="<?= base_url('report/export_excel/agent?agent_id=' . $selectedAgentId . '&start_date=' . $startDate . '&end_date=' . $endDate) ?>" class="btn btn-success mr-2">
                                            <i class="mdi mdi-file-excel"></i> Export to Excel
                                        </a>
                                        <a href="<?= base_url('report/export_pdf/agent?agent_id=' . $selectedAgentId . '&start_date=' . $startDate . '&end_date=' . $endDate) ?>" class="btn btn-danger mr-2">
                                            <i class="mdi mdi-file-pdf"></i> Export to PDF
                                        </a>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body">
                                                <h5 class="card-title text-white">Total Evaluations</h5>
                                                <h2 class="text-white"><?= $reportData['evaluationCount'] ?></h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="card bg-success text-white">
                                            <div class="card-body">
                                                <h5 class="card-title text-white">Average Score</h5>
                                                <h2 class="text-white"><?= number_format($reportData['averageScore'], 2) ?>%</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Score Trend</h5>
                                                <canvas id="scoreTrendChart" height="100"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Evaluations</h5>
                                                <div class="table-responsive">
                                                    <table class="table table-hover" id="evaluationsTable">
                                                        <thead>
                                                            <tr>
                                                                <th>Date</th>
                                                                <th>Activity</th>
                                                                <th>Evaluator</th>
                                                                <th>Score</th>
                                                                <th>Comments</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($reportData['evaluations'] as $evaluation): ?>
                                                                <tr>
                                                                    <td><?= date('Y-m-d', strtotime($evaluation['evaluation_date'])) ?></td>
                                                                    <td><?= $evaluation['activity_name'] ?></td>
                                                                    <td><?= $evaluation['evaluator_name'] ?></td>
                                                                    <td><?= $evaluation['score_total'] ?>%</td>
                                                                    <td><?= substr($evaluation['comments'], 0, 50) . (strlen($evaluation['comments']) > 50 ? '...' : '') ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
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
                                // Initialize DataTable
                                $('#evaluationsTable').DataTable({
                                    order: [], // Remove default sorting
                                    language: {
                                        emptyTable: "" // Remove the "No data available in table" message
                                    },
                                    // Fix for "Requested unknown parameter" error
                                    columnDefs: [
                                        {
                                            targets: '_all',
                                            render: function(data, type, row, meta) {
                                                return data !== undefined ? data : '';
                                            }
                                        }
                                    ]
                                });

                                // Score Trend Chart
                                var dates = <?= json_encode(array_keys($reportData['dailyAverages'])) ?>;
                                var scores = <?= json_encode(array_values($reportData['dailyAverages'])) ?>;

                                var scoreTrendCtx = document.getElementById('scoreTrendChart').getContext('2d');
                                var scoreTrendChart = new Chart(scoreTrendCtx, {
                                    type: 'line',
                                    data: {
                                        labels: dates,
                                        datasets: [{
                                            label: 'Daily Average Score',
                                            data: scores,
                                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                            borderColor: 'rgba(54, 162, 235, 1)',
                                            borderWidth: 2,
                                            tension: 0.1
                                        }]
                                    },
                                    options: {
                                        scales: {
                                            yAxes: [{
                                                ticks: {
                                                    beginAtZero: true,
                                                    max: 100
                                                }
                                            }]
                                        }
                                    }
                                });
                            } else {
                                console.error('jQuery is not loaded. Please check your scripts.');
                            }
                        });
                        </script>
                    <?php else: ?>
                        <div class="alert alert-info mt-4">
                            <p>Please select an agent and date range to generate a report.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
