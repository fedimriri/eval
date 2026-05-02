<div class="page-content">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Business Unit Report</h4>
                    <p class="card-description">View and analyze evaluation data by business unit</p>

                    <form method="get" action="<?= base_url('report/business_unit') ?>" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="business_unit_id">Business Unit</label>
                                    <select class="form-control" id="business_unit_id" name="business_unit_id" required>
                                        <option value="">Select Business Unit</option>
                                        <?php foreach ($businessUnits as $businessUnit): ?>
                                            <option value="<?= $businessUnit['id'] ?>" <?= $selectedBusinessUnitId == $businessUnit['id'] ? 'selected' : '' ?>>
                                                <?= $businessUnit['name'] ?>
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
                                    <h5>Report for <?= $reportData['businessUnit']['name'] ?></h5>
                                    <div>
                                        <a href="<?= base_url('report/export_excel/business_unit?business_unit_id=' . $selectedBusinessUnitId . '&start_date=' . $startDate . '&end_date=' . $endDate) ?>" class="btn btn-success mr-2">
                                            <i class="mdi mdi-file-excel"></i> Export to Excel
                                        </a>
                                        <a href="<?= base_url('report/export_pdf/business_unit?business_unit_id=' . $selectedBusinessUnitId . '&start_date=' . $startDate . '&end_date=' . $endDate) ?>" class="btn btn-danger mr-2">
                                            <i class="mdi mdi-file-pdf"></i> Export to PDF
                                        </a>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 mb-4">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body">
                                                <h5 class="card-title text-white">Total Evaluations</h5>
                                                <h2 class="text-white"><?= $reportData['evaluationCount'] ?></h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <div class="card bg-success text-white">
                                            <div class="card-body">
                                                <h5 class="card-title text-white">Average Score</h5>
                                                <h2 class="text-white"><?= number_format($reportData['overallAverageScore'], 2) ?>%</h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <div class="card bg-info text-white">
                                            <div class="card-body">
                                                <h5 class="card-title text-white">Activities</h5>
                                                <h2 class="text-white"><?= count($reportData['activities']) ?></h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <div class="card bg-warning text-white">
                                            <div class="card-body">
                                                <h5 class="card-title text-white">Agents</h5>
                                                <h2 class="text-white"><?= count($reportData['agents']) ?></h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Activity Performance</h5>
                                                <canvas id="activityChart" height="200"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Agent Performance</h5>
                                                <canvas id="agentChart" height="200"></canvas>
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
                                                                <th>Activity</th>
                                                                <th>Evaluator</th>
                                                                <th>Score</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($reportData['evaluations'] as $evaluation): ?>
                                                                <tr>
                                                                    <td><?= date('Y-m-d', strtotime($evaluation['evaluation_date'])) ?></td>
                                                                    <td><?= $evaluation['agent_name'] ?></td>
                                                                    <td><?= $evaluation['activity_name'] ?></td>
                                                                    <td><?= $evaluation['evaluator_name'] ?></td>
                                                                    <td><?= $evaluation['score_total'] ?>%</td>
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

                                // Activity Chart
                                var activityCtx = document.getElementById('activityChart').getContext('2d');
                                var activityChart = new Chart(activityCtx, {
                                    type: 'bar',
                                    data: {
                                        labels: <?= json_encode(array_column($reportData['activityScores'], 'activity_name')) ?>,
                                        datasets: [{
                                            label: 'Average Score',
                                            data: <?= json_encode(array_column($reportData['activityScores'], 'average_score')) ?>,
                                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                            borderColor: 'rgba(54, 162, 235, 1)',
                                            borderWidth: 1
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
                            <p>Please select a business unit and date range to generate a report.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
