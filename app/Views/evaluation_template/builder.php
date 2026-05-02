<?php
/**
 * Evaluation Template builder view
 */
?>
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">Template Builder</h4>
                    <div>
                        <a href="<?= base_url('evaluation_template'); ?>" class="btn btn-light">
                            <i class="mdi mdi-arrow-left"></i> Back to Templates
                        </a>
                    </div>
                </div>

                <div class="alert alert-info">
                    <h5>Template Information</h5>
                    <p><strong>Name:</strong> <?= e($template['name']); ?></p>
                    <p><strong>Status:</strong> <?= $template['is_active'] ? 'Active' : 'Inactive'; ?></p>
                </div>

                <div class="alert alert-warning">
                    <h5>Weight Rules</h5>
                    <ul>
                        <li>Each criterion's weight must equal the sum of its subcriteria weights</li>
                        <li>The total weight of all criteria must be exactly 100</li>
                    </ul>
                    <div class="mt-2">
                        <strong>Current Total Weight: <span id="total-template-weight">0</span>/100</strong>
                        <div class="progress mt-1">
                            <div id="total-weight-progress" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                    </div>
                </div>

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

                <form class="forms-sample" method="POST" action="<?= base_url('evaluation_template/builder/' . $template['id']); ?>" id="template-builder-form">
                    <div id="criteria-container">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="35%">Criterion Name</th>
                                    <th width="40%">Description</th>
                                    <th width="10%">Weight</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $criteriaCount = isset($template['criteria']) ? count($template['criteria']) : 0;
                                if ($criteriaCount > 0):
                                    foreach ($template['criteria'] as $criteriaIndex => $criterion):
                                        $criteriaNum = $criteriaIndex + 1;
                                ?>
                                <tr class="criteria-row" id="criteria-row-<?= $criteriaNum; ?>">
                                    <td class="align-middle">
                                        <span class="criteria-number"><?= $criteriaNum; ?></span>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="criteria_name_<?= $criteriaNum; ?>" value="<?= e($criterion['name']); ?>" required>
                                    </td>
                                    <td>
                                        <textarea class="form-control form-control-sm" name="criteria_description_<?= $criteriaNum; ?>" rows="1"><?= e($criterion['description']); ?></textarea>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control form-control-sm criteria-weight" name="criteria_weight_<?= $criteriaNum; ?>" value="<?= $criterion['weight']; ?>" step="0.01" min="0" readonly>
                                            <div class="input-group-append">
                                                <span class="input-group-text">
                                                    <i class="mdi mdi-calculator"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm remove-criteria" data-criteria="<?= $criteriaNum; ?>">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="subcriteria-row">
                                    <td colspan="5" class="p-0">
                                        <div class="subcriteria-container p-2" id="subcriteria-container-<?= $criteriaNum; ?>">
                                            <h6 class="mb-2">Subcriteria</h6>
                                            <table class="table table-bordered table-sm">
                                                <thead class="bg-info text-white">
                                                    <tr>
                                                        <th width="5%">#</th>
                                                        <th width="35%">Subcriterion Name</th>
                                                        <th width="40%">Description</th>
                                                        <th width="10%">Weight</th>
                                                        <th width="10%">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $subcriteriaCount = isset($criterion['subcriteria']) ? count($criterion['subcriteria']) : 0;
                                                    if ($subcriteriaCount > 0):
                                                        foreach ($criterion['subcriteria'] as $subcriteriaIndex => $subcriterion):
                                                            $subcriteriaNum = $subcriteriaIndex + 1;
                                                    ?>
                                                    <tr class="subcriteria-item" id="subcriteria-row-<?= $criteriaNum; ?>-<?= $subcriteriaNum; ?>">
                                                        <td class="align-middle">
                                                            <span class="subcriteria-number"><?= $subcriteriaNum; ?></span>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm" name="subcriteria_name_<?= $criteriaNum; ?>_<?= $subcriteriaNum; ?>" value="<?= e($subcriterion['name']); ?>" required>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control form-control-sm" name="subcriteria_description_<?= $criteriaNum; ?>_<?= $subcriteriaNum; ?>" rows="1"><?= e($subcriterion['description']); ?></textarea>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm subcriteria-weight"
                                                                name="subcriteria_weight_<?= $criteriaNum; ?>_<?= $subcriteriaNum; ?>"
                                                                value="<?= $subcriterion['weight']; ?>"
                                                                step="0.01" min="0"
                                                                data-criteria="<?= $criteriaNum; ?>">
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-danger btn-sm remove-subcriteria" data-criteria="<?= $criteriaNum; ?>" data-subcriteria="<?= $subcriteriaNum; ?>">
                                                                <i class="mdi mdi-delete"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                        endforeach;
                                                    else:
                                                    ?>
                                                    <tr>
                                                        <td colspan="5">
                                                            <div class="alert alert-warning mb-0">No subcriteria found. Add at least one subcriterion.</div>
                                                        </td>
                                                    </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                            <input type="hidden" name="subcriteria_count_<?= $criteriaNum; ?>" id="subcriteria-count-<?= $criteriaNum; ?>" value="<?= $subcriteriaCount; ?>">
                                            <button type="button" class="btn btn-info btn-sm add-subcriteria mt-2" data-criteria="<?= $criteriaNum; ?>">
                                                <i class="mdi mdi-plus"></i> Add Subcriterion
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                    endforeach;
                                else:
                                ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="alert alert-warning mb-0">No criteria found. Add at least one criterion.</div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <input type="hidden" name="criteria_count" id="criteria-count" value="<?= $criteriaCount; ?>">

                    <div class="mt-4 mb-4">
                        <button type="button" class="btn btn-primary" id="add-criteria">
                            <i class="mdi mdi-plus"></i> Add Criterion
                        </button>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-success btn-lg" id="save-template-btn">
                            <i class="mdi mdi-content-save"></i> Save Template
                        </button>
                        <div id="validation-errors" class="mt-3"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize counters
    let criteriaCount = <?= $criteriaCount; ?>;

    // Add criterion
    document.getElementById('add-criteria').addEventListener('click', function() {
        criteriaCount++;

        const criteriaHtml = `
            <tr class="criteria-row" id="criteria-row-${criteriaCount}">
                <td class="align-middle">
                    <span class="criteria-number">${criteriaCount}</span>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="criteria_name_${criteriaCount}" required>
                </td>
                <td>
                    <textarea class="form-control form-control-sm" name="criteria_description_${criteriaCount}" rows="1"></textarea>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control form-control-sm criteria-weight" name="criteria_weight_${criteriaCount}" value="0" step="0.01" min="0" readonly>
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="mdi mdi-calculator"></i>
                            </span>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove-criteria" data-criteria="${criteriaCount}">
                        <i class="mdi mdi-delete"></i>
                    </button>
                </td>
            </tr>
            <tr class="subcriteria-row">
                <td colspan="5" class="p-0">
                    <div class="subcriteria-container p-2" id="subcriteria-container-${criteriaCount}">
                        <h6 class="mb-2">Subcriteria</h6>
                        <table class="table table-bordered table-sm">
                            <thead class="bg-info text-white">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="35%">Subcriterion Name</th>
                                    <th width="40%">Description</th>
                                    <th width="10%">Weight</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5">
                                        <div class="alert alert-warning mb-0">No subcriteria found. Add at least one subcriterion.</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <input type="hidden" name="subcriteria_count_${criteriaCount}" id="subcriteria-count-${criteriaCount}" value="0">
                        <button type="button" class="btn btn-info btn-sm add-subcriteria mt-2" data-criteria="${criteriaCount}">
                            <i class="mdi mdi-plus"></i> Add Subcriterion
                        </button>
                    </div>
                </td>
            </tr>
        `;

        const tableBody = document.querySelector('#criteria-container table tbody');
        // Remove the "No criteria found" message if it exists
        const noDataRow = tableBody.querySelector('tr td div.alert-warning');
        if (noDataRow) {
            tableBody.innerHTML = '';
        }

        tableBody.insertAdjacentHTML('beforeend', criteriaHtml);
        document.getElementById('criteria-count').value = criteriaCount;

        // Add event listeners to new elements
        addEventListeners();
        updateTotalWeight();
    });

    // Add subcriterion
    function addSubcriterion(criteriaNum) {
        const subcriteriaContainer = document.getElementById(`subcriteria-container-${criteriaNum}`);
        const subcriteriaTable = subcriteriaContainer.querySelector('table tbody');
        const subcriteriaCountInput = document.getElementById(`subcriteria-count-${criteriaNum}`);
        let subcriteriaCount = parseInt(subcriteriaCountInput.value) || 0;

        // Clear warning if it exists
        const warning = subcriteriaTable.querySelector('tr td div.alert-warning');
        if (warning) {
            subcriteriaTable.innerHTML = '';
        }

        subcriteriaCount++;

        const subcriteriaHtml = `
            <tr class="subcriteria-item" id="subcriteria-row-${criteriaNum}-${subcriteriaCount}">
                <td class="align-middle">
                    <span class="subcriteria-number">${subcriteriaCount}</span>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="subcriteria_name_${criteriaNum}_${subcriteriaCount}" required>
                </td>
                <td>
                    <textarea class="form-control form-control-sm" name="subcriteria_description_${criteriaNum}_${subcriteriaCount}" rows="1"></textarea>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm subcriteria-weight"
                        name="subcriteria_weight_${criteriaNum}_${subcriteriaCount}"
                        value="1" step="0.01" min="0"
                        data-criteria="${criteriaNum}">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove-subcriteria" data-criteria="${criteriaNum}" data-subcriteria="${subcriteriaCount}">
                        <i class="mdi mdi-delete"></i>
                    </button>
                </td>
            </tr>
        `;

        subcriteriaTable.insertAdjacentHTML('beforeend', subcriteriaHtml);
        subcriteriaCountInput.value = subcriteriaCount;

        // Add event listeners to new elements
        addEventListeners();
        updateCriterionWeight(criteriaNum);
        updateTotalWeight();
    }

    // Remove criterion
    function removeCriterion(criteriaNum) {
        if (confirm('Are you sure you want to remove this criterion and all its subcriteria?')) {
            // Remove both the criteria row and its subcriteria row
            const criteriaRow = document.getElementById(`criteria-row-${criteriaNum}`);
            const subcriteriaRow = criteriaRow.nextElementSibling;

            criteriaRow.remove();
            subcriteriaRow.remove();

            // Update criteria count
            const criteriaRows = document.querySelectorAll('.criteria-row');
            document.getElementById('criteria-count').value = criteriaRows.length;

            // If no criteria left, show warning
            if (criteriaRows.length === 0) {
                const tableBody = document.querySelector('#criteria-container table tbody');
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5">
                            <div class="alert alert-warning mb-0">No criteria found. Add at least one criterion.</div>
                        </td>
                    </tr>
                `;
            } else {
                // Renumber criteria
                criteriaRows.forEach((row, index) => {
                    const newIndex = index + 1;
                    row.querySelector('.criteria-number').textContent = newIndex;
                });
            }

            updateTotalWeight();
        }
    }

    // Remove subcriterion
    function removeSubcriterion(criteriaNum, subcriteriaNum) {
        if (confirm('Are you sure you want to remove this subcriterion?')) {
            document.getElementById(`subcriteria-row-${criteriaNum}-${subcriteriaNum}`).remove();

            // Get all subcriteria rows for this criterion
            const subcriteriaTable = document.querySelector(`#subcriteria-container-${criteriaNum} table tbody`);
            const subcriteriaRows = subcriteriaTable.querySelectorAll('.subcriteria-item');

            // Update subcriteria count
            document.getElementById(`subcriteria-count-${criteriaNum}`).value = subcriteriaRows.length;

            // If no subcriteria left, show warning
            if (subcriteriaRows.length === 0) {
                subcriteriaTable.innerHTML = `
                    <tr>
                        <td colspan="5">
                            <div class="alert alert-warning mb-0">No subcriteria found. Add at least one subcriterion.</div>
                        </td>
                    </tr>
                `;
            } else {
                // Renumber subcriteria
                subcriteriaRows.forEach((row, index) => {
                    const newIndex = index + 1;
                    row.querySelector('.subcriteria-number').textContent = newIndex;
                });
            }

            updateCriterionWeight(criteriaNum);
            updateTotalWeight();
        }
    }

    // Calculate and update criterion weight based on its subcriteria
    function updateCriterionWeight(criteriaNum) {
        const criteriaWeightInput = document.querySelector(`input[name="criteria_weight_${criteriaNum}"]`);
        const subcriteriaWeightInputs = document.querySelectorAll(`input[name^="subcriteria_weight_${criteriaNum}_"]`);

        let totalWeight = 0;
        subcriteriaWeightInputs.forEach(input => {
            totalWeight += parseFloat(input.value) || 0;
        });

        criteriaWeightInput.value = totalWeight.toFixed(2);
    }

    // Calculate and update total template weight
    function updateTotalWeight() {
        const criteriaWeightInputs = document.querySelectorAll('input[name^="criteria_weight_"]');
        const totalWeightElement = document.getElementById('total-template-weight');
        const totalWeightProgress = document.getElementById('total-weight-progress');

        let totalWeight = 0;
        criteriaWeightInputs.forEach(input => {
            totalWeight += parseFloat(input.value) || 0;
        });

        totalWeightElement.textContent = totalWeight.toFixed(2);

        // Update progress bar
        const percentage = Math.min(totalWeight, 100);
        totalWeightProgress.style.width = `${percentage}%`;
        totalWeightProgress.setAttribute('aria-valuenow', percentage);
        totalWeightProgress.textContent = `${percentage.toFixed(2)}%`;

        // Set color based on whether total is exactly 100
        if (Math.abs(totalWeight - 100) < 0.01) {
            totalWeightProgress.classList.remove('bg-danger', 'bg-warning');
            totalWeightProgress.classList.add('bg-success');
        } else if (totalWeight > 100) {
            totalWeightProgress.classList.remove('bg-success', 'bg-warning');
            totalWeightProgress.classList.add('bg-danger');
        } else {
            totalWeightProgress.classList.remove('bg-success', 'bg-danger');
            totalWeightProgress.classList.add('bg-warning');
        }
    }

    // Validate form before submission
    function validateForm() {
        const criteriaRows = document.querySelectorAll('.criteria-row');
        const validationErrors = document.getElementById('validation-errors');
        let errors = [];

        // Check if there are any criteria
        if (criteriaRows.length === 0) {
            errors.push('You must add at least one criterion.');
        }

        // Check if each criterion has at least one subcriterion
        criteriaRows.forEach((row) => {
            const criteriaNum = row.id.replace('criteria-row-', '');
            const criteriaName = row.querySelector(`input[name="criteria_name_${criteriaNum}"]`).value;
            const subcriteriaRows = document.querySelectorAll(`#subcriteria-container-${criteriaNum} .subcriteria-item`);

            if (subcriteriaRows.length === 0) {
                errors.push(`Criterion "${criteriaName}" must have at least one subcriterion.`);
            }
        });

        // Check if total weight is exactly 100
        const totalWeight = parseFloat(document.getElementById('total-template-weight').textContent);
        if (Math.abs(totalWeight - 100) > 0.01) {
            errors.push(`Total template weight must be exactly 100. Current total: ${totalWeight.toFixed(2)}.`);
        }

        // Display errors if any
        if (errors.length > 0) {
            let errorHtml = '<div class="alert alert-error"><ul>';
            errors.forEach(error => {
                errorHtml += `<li>${error}</li>`;
            });
            errorHtml += '</ul></div>';

            validationErrors.innerHTML = errorHtml;
            return false;
        }

        validationErrors.innerHTML = '';
        return true;
    }

    // Add event listeners
    function addEventListeners() {
        // Add subcriterion buttons
        document.querySelectorAll('.add-subcriteria').forEach(button => {
            button.removeEventListener('click', addSubcriterionHandler);
            button.addEventListener('click', addSubcriterionHandler);
        });

        // Remove criterion buttons
        document.querySelectorAll('.remove-criteria').forEach(button => {
            button.removeEventListener('click', removeCriterionHandler);
            button.addEventListener('click', removeCriterionHandler);
        });

        // Remove subcriterion buttons
        document.querySelectorAll('.remove-subcriteria').forEach(button => {
            button.removeEventListener('click', removeSubcriterionHandler);
            button.addEventListener('click', removeSubcriterionHandler);
        });

        // Subcriteria weight inputs
        document.querySelectorAll('.subcriteria-weight').forEach(input => {
            input.removeEventListener('input', subcriteriaWeightChangeHandler);
            input.addEventListener('input', subcriteriaWeightChangeHandler);
        });
    }

    // Event handlers
    function addSubcriterionHandler() {
        const criteriaNum = this.getAttribute('data-criteria');
        addSubcriterion(criteriaNum);
    }

    function removeCriterionHandler() {
        const criteriaNum = this.getAttribute('data-criteria');
        removeCriterion(criteriaNum);
    }

    function removeSubcriterionHandler() {
        const criteriaNum = this.getAttribute('data-criteria');
        const subcriteriaNum = this.getAttribute('data-subcriteria');
        removeSubcriterion(criteriaNum, subcriteriaNum);
    }

    function subcriteriaWeightChangeHandler() {
        const criteriaNum = this.getAttribute('data-criteria');
        updateCriterionWeight(criteriaNum);
        updateTotalWeight();
    }

    // Form submission validation and reindexing
    document.getElementById('template-builder-form').addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return;
        }

        // Reindex criteria and subcriteria before submission
        const criteriaRows = document.querySelectorAll('.criteria-row');
        document.getElementById('criteria-count').value = criteriaRows.length;

        // Create a map to store old indices and new indices
        const criteriaMap = {};

        // First pass: collect all criteria indices
        criteriaRows.forEach((row, newIndex) => {
            const newCriteriaNum = newIndex + 1;
            const oldCriteriaNum = row.id.replace('criteria-row-', '');
            criteriaMap[oldCriteriaNum] = newCriteriaNum;
        });

        // Second pass: rename all form fields
        for (const oldIndex in criteriaMap) {
            const newIndex = criteriaMap[oldIndex];

            // Rename criteria fields
            const criteriaNameInput = document.querySelector(`input[name="criteria_name_${oldIndex}"]`);
            const criteriaDescInput = document.querySelector(`textarea[name="criteria_description_${oldIndex}"]`);
            const criteriaWeightInput = document.querySelector(`input[name="criteria_weight_${oldIndex}"]`);
            const subcriteriaCountInput = document.getElementById(`subcriteria-count-${oldIndex}`);

            if (criteriaNameInput) criteriaNameInput.name = `criteria_name_${newIndex}`;
            if (criteriaDescInput) criteriaDescInput.name = `criteria_description_${newIndex}`;
            if (criteriaWeightInput) criteriaWeightInput.name = `criteria_weight_${newIndex}`;
            if (subcriteriaCountInput) subcriteriaCountInput.name = `subcriteria_count_${newIndex}`;

            // Rename subcriteria fields
            const subcriteriaCount = subcriteriaCountInput ? parseInt(subcriteriaCountInput.value) : 0;
            for (let j = 1; j <= subcriteriaCount; j++) {
                const subNameInput = document.querySelector(`input[name="subcriteria_name_${oldIndex}_${j}"]`);
                const subDescInput = document.querySelector(`textarea[name="subcriteria_description_${oldIndex}_${j}"]`);
                const subWeightInput = document.querySelector(`input[name="subcriteria_weight_${oldIndex}_${j}"]`);

                if (subNameInput) subNameInput.name = `subcriteria_name_${newIndex}_${j}`;
                if (subDescInput) subDescInput.name = `subcriteria_description_${newIndex}_${j}`;
                if (subWeightInput) subWeightInput.name = `subcriteria_weight_${newIndex}_${j}`;
            }
        }
    });

    // Initialize weights and event listeners
    document.querySelectorAll('.criteria-row').forEach((row) => {
        const criteriaNum = row.id.replace('criteria-row-', '');
        updateCriterionWeight(criteriaNum);
    });
    updateTotalWeight();
    addEventListeners();
});
</script>
