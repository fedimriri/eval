<div class="page-content">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Activity Report</h4>
                    <p class="card-description">View and analyze evaluation data by activity</p>

                    <form method="get" action="<?= base_url('report/activity') ?>" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="activity_id">Activity</label>
                                    <select class="form-control" id="activity_id" name="activity_id" required>
                                        <option value="">Select Activity</option>
                                        <?php foreach ($activities as $activity): ?>
                                            <option value="<?= $activity['id'] ?>" <?= $selectedActivityId == $activity['id'] ? 'selected' : '' ?>>
                                                <?= $activity['name'] ?> (<?= $activity['business_unit_name'] ?>)
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
                                    <h5>Report for <?= $reportData['activity']['name'] ?></h5>
                                    <div>
                                        <a href="<?= base_url('report/export_excel/activity?activity_id=' . $selectedActivityId . '&start_date=' . $startDate . '&end_date=' . $endDate) ?>" class="btn btn-success mr-2">
                                            <i class="mdi mdi-file-excel"></i> Export to Excel
                                        </a>
                                        <a href="<?= base_url('report/export_pdf/activity?activity_id=' . $selectedActivityId . '&start_date=' . $startDate . '&end_date=' . $endDate) ?>" class="btn btn-danger mr-2">
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
                                                <h2 class="text-white"><?= number_format($reportData['overallAverageScore'], 2) ?>%</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Agent Performance</h5>
                                                <canvas id="agentChart" height="100"></canvas>
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
                                                                <th>Agent</th>
                                                                <th>Evaluator</th>
                                                                <th>Score</th>
                                                                <th>Comments</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($reportData['evaluations'] as $evaluation): ?>
                                                                <tr>
                                                                    <td><?= date('Y-m-d', strtotime($evaluation['evaluation_date'])) ?></td>
                                                                    <td><?= $evaluation['agent_name'] ?></td>
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

                                // Agent Chart
                                var agentCtx = document.getElementById('agentChart').getContext('2d');
                                var agentChart = new Chart(agentCtx, {
                                    type: 'bar',
                                    data: {
                                        labels: <?= json_encode(array_column($reportData['agentScores'], 'agent_name')) ?>,
                                        datasets: [{
                                            label: 'Average Score',
                                            data: <?= json_encode(array_column($reportData['agentScores'], 'average_score')) ?>,
                                            backgroundColor: 'rgba(255, 99, 132, 0.5)',
                                            borderColor: 'rgba(255, 99, 132, 1)',
                                            borderWidth: 1
                                        }, {
                                            label: 'Evaluation Count',
                                            data: <?= json_encode(array_column($reportData['agentScores'], 'evaluation_count')) ?>,
                                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                            borderColor: 'rgba(54, 162, 235, 1)',
                                            borderWidth: 1,
                                            type: 'line',
                                            yAxisID: 'y-axis-1'
                                        }]
                                    },
                                    options: {
                                        scales: {
                                            yAxes: [{
                                                type: 'linear',
                                                display: true,
                                                position: 'left',
                                                id: 'y-axis-0',
                                                ticks: {
                                                    beginAtZero: true,
                                                    max: 100
                                                },
                                                scaleLabel: {
                                                    display: true,
                                                    labelString: 'Score'
                                                }
                                            }, {
                                                type: 'linear',
                                                display: true,
                                                position: 'right',
                                                id: 'y-axis-1',
                                                gridLines: {
                                                    drawOnChartArea: false
                                                },
                                                scaleLabel: {
                                                    display: true,
                                                    labelString: 'Count'
                                                },
                                                ticks: {
                                                    beginAtZero: true
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
                            <p>Please select an activity and date range to generate a report.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
