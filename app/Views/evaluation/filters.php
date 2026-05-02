<div class="card mb-4" id="filters-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Filters</h5>
        <button type="button" id="toggle-filters" class="btn btn-sm btn-outline-secondary">
            <i class="mdi mdi-chevron-up" id="filter-icon"></i>
        </button>
    </div>
    <div class="card-body" id="filters-body">
        <form method="get" action="<?= base_url('evaluation') ?>" id="filters-form">
            <div class="row">
                <!-- Date Range Filter -->
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Date Range</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="start_date" name="start_date"
                                value="<?= $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 month')) ?>">
                            <div class="input-group-prepend input-group-append">
                                <span class="input-group-text">to</span>
                            </div>
                            <input type="date" class="form-control" id="end_date" name="end_date"
                                value="<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-between">

                <!-- Business Units Filter -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="business_units">Business Units</label>
                        <div class="multiSelect">
                            <select multiple class="multiSelect_field" id="business_units" name="business_units[]" data-placeholder="Select Business Units">
                                <?php
                                $selectedBusinessUnits = $_GET['business_units'] ?? [];
                                if (isset($businessUnits) && !empty($businessUnits)) {
                                    foreach ($businessUnits as $bu) {
                                        $selected = in_array($bu['id'], $selectedBusinessUnits) ? 'selected' : '';
                                        echo '<option value="' . $bu['id'] . '" ' . $selected . '>' . $bu['name'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Activities Filter -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="activities">Activities</label>
                        <div class="multiSelect">
                            <select multiple class="multiSelect_field" id="activities" name="activities[]" data-placeholder="Select Activities">
                                <?php
                                $selectedActivities = $_GET['activities'] ?? [];
                                if (isset($activities) && !empty($activities)) {
                                    foreach ($activities as $activity) {
                                        $selected = in_array($activity['id'], $selectedActivities) ? 'selected' : '';
                                        echo '<option value="' . $activity['id'] . '" ' . $selected . '>' . $activity['name'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Agents Filter -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="agents">Agents</label>
                        <div class="multiSelect">
                            <select multiple class="multiSelect_field" id="agents" name="agents[]" data-placeholder="Select Agents">
                                <?php
                                $selectedAgents = $_GET['agents'] ?? [];
                                if (isset($agents) && !empty($agents)) {
                                    foreach ($agents as $agent) {
                                        $selected = in_array($agent['id'], $selectedAgents) ? 'selected' : '';
                                        echo '<option value="' . $agent['id'] . '" ' . $selected . '>' . $agent['name'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 d-flex align-items-center justify-content-end ">
                    <button type="submit" class="btn btn-primary mr-2">Apply Filters</button>
                    <a href="<?= base_url('evaluation') ?>" class="btn btn-secondary">Reset</a>
                </div>
            </div>

        </form>
    </div>
</div>

<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
    <symbol xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" id="iconX">
        <g stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </g>
    </symbol>
</svg>

<script>
    // Initialize date range with default values if not set
    document.addEventListener('DOMContentLoaded', function() {
        if (!document.getElementById('start_date').value) {
            const oneMonthAgo = new Date();
            oneMonthAgo.setMonth(oneMonthAgo.getMonth() - 1);
            document.getElementById('start_date').value = oneMonthAgo.toISOString().split('T')[0];
        }

        if (!document.getElementById('end_date').value) {
            const today = new Date();
            document.getElementById('end_date').value = today.toISOString().split('T')[0];
        }

        // Initialize filters toggle
        const toggleFiltersBtn = document.getElementById('toggle-filters');
        const filtersBody = document.getElementById('filters-body');
        const filterIcon = document.getElementById('filter-icon');

        // Check if filters were previously collapsed
        const filtersCollapsed = localStorage.getItem('filtersCollapsed') === 'true';

        // Set initial state
        if (filtersCollapsed) {
            filtersBody.style.display = 'none';
            filterIcon.classList.remove('fa-chevron-up');
            filterIcon.classList.add('fa-chevron-down');
        }

        // Add toggle functionality
        toggleFiltersBtn.addEventListener('click', function() {
            if (filtersBody.style.display === 'none') {
                // Show filters
                filtersBody.style.display = 'block';
                filterIcon.classList.remove('fa-chevron-down');
                filterIcon.classList.add('fa-chevron-up');
                localStorage.setItem('filtersCollapsed', 'false');
            } else {
                // Hide filters
                filtersBody.style.display = 'none';
                filterIcon.classList.remove('fa-chevron-up');
                filterIcon.classList.add('fa-chevron-down');
                localStorage.setItem('filtersCollapsed', 'true');
            }
        });
    });
</script>

<style>
    /* Add some transition effects */
    #filters-body {
        transition: all 0.3s ease;
    }

    #toggle-filters {
        transition: all 0.3s ease;
    }

    /* Style for the filter toggle button */
    #toggle-filters:focus {
        box-shadow: none;
        outline: none;
    }

    /* Style for the filter card */
    #filters-card {
        border: 1px solid #ddd;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* Style for the filter card header */
    #filters-card .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #ddd;
    }
</style>