/**
 * Evaluation Create Form JavaScript
 * Handles cascading select dropdowns for business units, activities, and agents
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get select elements
    const businessUnitSelect = document.getElementById('business_unit_id');
    const activitySelect = document.getElementById('activity_id');
    const agentSelect = document.getElementById('agent_id');

    // Check if we have preselected values
    const hasPreselectedActivity = activitySelect.querySelector('option[data-preselected="true"]');
    const hasPreselectedAgent = agentSelect.querySelector('option[data-preselected="true"]');

    // Add event listeners
    if (businessUnitSelect) {
        businessUnitSelect.addEventListener('change', function() {
            const businessUnitId = this.value;

            // Reset and disable activity and agent selects
            resetSelect(activitySelect);
            resetSelect(agentSelect);

            if (businessUnitId) {
                // Enable activity select and load activities
                activitySelect.disabled = false;
                loadActivities(businessUnitId);
            } else {
                // Disable activity and agent selects
                activitySelect.disabled = true;
                agentSelect.disabled = true;
            }
        });

        // If business unit is already selected (on page reload after error), trigger the change event
        if (businessUnitSelect.value && hasPreselectedActivity) {
            // Load activities for the selected business unit
            activitySelect.disabled = false;
            loadActivities(businessUnitSelect.value, true);
        }
    }

    if (activitySelect) {
        activitySelect.addEventListener('change', function() {
            const activityId = this.value;

            // Reset and disable agent select
            resetSelect(agentSelect);

            if (activityId) {
                // Enable agent select and load agents
                agentSelect.disabled = false;
                loadAgents(activityId);
            } else {
                // Disable agent select
                agentSelect.disabled = true;
            }
        });

        // If activity is already selected (on page reload after error), trigger the change event
        if (activitySelect.value && hasPreselectedAgent) {
            // Load agents for the selected activity
            agentSelect.disabled = false;
            loadAgents(activitySelect.value, true);
        }
    }

    /**
     * Reset a select element to its default state
     * @param {HTMLSelectElement} selectElement - The select element to reset
     */
    function resetSelect(selectElement) {
        if (selectElement) {
            // Keep only the first option (placeholder)
            while (selectElement.options.length > 1) {
                selectElement.remove(1);
            }

            // Reset to first option
            selectElement.selectedIndex = 0;

            // Disable the select
            selectElement.disabled = true;
        }
    }

    /**
     * Load activities for a business unit
     * @param {number} businessUnitId - The business unit ID
     * @param {boolean} keepPreselected - Whether to keep preselected value
     */
    function loadActivities(businessUnitId, keepPreselected = false) {
        // Save preselected option if needed
        let preselectedOption = null;
        if (keepPreselected) {
            preselectedOption = activitySelect.querySelector('option[data-preselected="true"]');
        }

        // Show loading indicator
        activitySelect.innerHTML = '<option value="">Loading activities...</option>';
        if (preselectedOption) {
            activitySelect.appendChild(preselectedOption);
        }

        // Make AJAX request to get activities
        fetch('/evaluation/get_activities_by_business_unit/' + businessUnitId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Reset select but keep preselected if needed
                if (preselectedOption) {
                    activitySelect.innerHTML = '<option value="">Select Activity</option>';
                    activitySelect.appendChild(preselectedOption);

                    // Trigger change event to load agents
                    if (activitySelect.value) {
                        const event = new Event('change');
                        activitySelect.dispatchEvent(event);
                    }
                } else {
                    activitySelect.innerHTML = '<option value="">Select Activity</option>';
                }

                // Add activities to select
                if (data && data.length > 0) {
                    data.forEach(activity => {
                        // Skip if this is the preselected activity (to avoid duplicates)
                        if (preselectedOption && activity.id == preselectedOption.value) {
                            return;
                        }

                        const option = document.createElement('option');
                        option.value = activity.id;
                        option.textContent = activity.name;
                        activitySelect.appendChild(option);
                    });
                } else if (!preselectedOption) {
                    // No activities found (only show if no preselected option)
                    activitySelect.innerHTML = '<option value="">No activities found</option>';
                }
            })
            .catch(error => {
                console.error('Error loading activities:', error);

                // Keep preselected option if there's an error
                if (preselectedOption) {
                    activitySelect.innerHTML = '<option value="">Error loading activities</option>';
                    activitySelect.appendChild(preselectedOption);
                } else {
                    activitySelect.innerHTML = '<option value="">Error loading activities</option>';
                }
            });
    }

    /**
     * Load agents for an activity
     * @param {number} activityId - The activity ID
     * @param {boolean} keepPreselected - Whether to keep preselected value
     */
    function loadAgents(activityId, keepPreselected = false) {
        // Save preselected option if needed
        let preselectedOption = null;
        if (keepPreselected) {
            preselectedOption = agentSelect.querySelector('option[data-preselected="true"]');
        }

        // Show loading indicator
        agentSelect.innerHTML = '<option value="">Loading agents...</option>';
        if (preselectedOption) {
            agentSelect.appendChild(preselectedOption);
        }

        // Make AJAX request to get agents
        fetch('/evaluation/get_agents_by_activity/' + activityId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Reset select but keep preselected if needed
                if (preselectedOption) {
                    agentSelect.innerHTML = '<option value="">Select Agent</option>';
                    agentSelect.appendChild(preselectedOption);
                } else {
                    agentSelect.innerHTML = '<option value="">Select Agent</option>';
                }

                // Add agents to select
                if (data && data.length > 0) {
                    data.forEach(agent => {
                        // Skip if this is the preselected agent (to avoid duplicates)
                        if (preselectedOption && agent.id == preselectedOption.value) {
                            return;
                        }

                        const option = document.createElement('option');
                        option.value = agent.id;
                        option.textContent = agent.name;
                        agentSelect.appendChild(option);
                    });
                } else if (!preselectedOption) {
                    // No agents found (only show if no preselected option)
                    agentSelect.innerHTML = '<option value="">No agents found</option>';
                }
            })
            .catch(error => {
                console.error('Error loading agents:', error);

                // Keep preselected option if there's an error
                if (preselectedOption) {
                    agentSelect.innerHTML = '<option value="">Error loading agents</option>';
                    agentSelect.appendChild(preselectedOption);
                } else {
                    agentSelect.innerHTML = '<option value="">Error loading agents</option>';
                }
            });
    }
});


