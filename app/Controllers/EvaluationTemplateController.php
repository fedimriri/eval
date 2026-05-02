<?php
/**
 * Evaluation Template Controller
 */
class EvaluationTemplateController extends Controller {
    private $evaluationTemplateModel;
    private $evaluationCriteriaModel;
    private $evaluationSubcriteriaModel;
    private $activityModel;
    private $businessUnitModel;
    private $managerBusinessUnitModel;

    /**
     * Constructor - Load models
     */
    public function __construct() {
        $this->evaluationTemplateModel = $this->model('EvaluationTemplateModel');
        $this->evaluationCriteriaModel = $this->model('EvaluationCriteriaModel');
        $this->evaluationSubcriteriaModel = $this->model('EvaluationSubcriteriaModel');
        $this->activityModel = $this->model('ActivityModel');
        $this->businessUnitModel = $this->model('BusinessUnitModel');
        $this->managerBusinessUnitModel = $this->model('ManagerBusinessUnitModel');
    }

    /**
     * Index method - Display all evaluation templates
     *
     * @return void
     */
    public function index() {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        $user = current_user();

        // Get templates
        if ($user['role'] === 'admin') {
            $templates = $this->evaluationTemplateModel->getAllWithDetails();
        } else {
            $templates = $this->evaluationTemplateModel->getAccessibleToManager($user['id']);
        }

        // Load the view
        $data = [
            'title' => 'Evaluation Templates',
            'templates' => $templates,
            'user' => $user
        ];

        $this->view('evaluation_template/index', $data);
    }

    /**
     * Create method - Display the create form and process form submission
     *
     * @return void
     */
    public function create() {
        // Check if user is authenticated and is admin
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        $user = current_user();

        if ($user['role'] !== 'admin') {
            flash('error', 'You do not have permission to create evaluation templates');
            redirect(base_url('evaluation_template'));
        }

        // Get activities
        $activities = $this->activityModel->getAllWithBusinessUnitName();

        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate form data
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $activityId = isset($_POST['activity_id']) ? (int)$_POST['activity_id'] : 0;
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            // Check if name is empty
            if (empty($name)) {
                flash('error', 'Template name is required');

                $data = [
                    'title' => 'Create Evaluation Template',
                    'name' => $name,
                    'activity_id' => $activityId,
                    'is_active' => $isActive,
                    'activities' => $activities,
                    'user' => $user
                ];

                $this->view('evaluation_template/create', $data);
                return;
            }

            // Check if activity is selected
            if ($activityId <= 0) {
                flash('error', 'Please select an activity');

                $data = [
                    'title' => 'Create Evaluation Template',
                    'name' => $name,
                    'activity_id' => $activityId,
                    'is_active' => $isActive,
                    'activities' => $activities,
                    'user' => $user
                ];

                $this->view('evaluation_template/create', $data);
                return;
            }

            // Check if activity exists
            if (!$this->activityModel->getById($activityId)) {
                flash('error', 'Selected activity does not exist');

                $data = [
                    'title' => 'Create Evaluation Template',
                    'name' => $name,
                    'activity_id' => $activityId,
                    'is_active' => $isActive,
                    'activities' => $activities,
                    'user' => $user
                ];

                $this->view('evaluation_template/create', $data);
                return;
            }

            // If this template is active, deactivate other templates for this activity
            if ($isActive) {
                $activeTemplate = $this->evaluationTemplateModel->getActiveByActivityId($activityId);

                if ($activeTemplate) {
                    $this->evaluationTemplateModel->update($activeTemplate['id'], [
                        'is_active' => 0
                    ]);
                }
            }

            // Create template
            $templateId = $this->evaluationTemplateModel->create([
                'name' => $name,
                'activity_id' => $activityId,
                'is_active' => $isActive,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if ($templateId) {
                // Set success message
                flash('success', 'Evaluation template created successfully');

                // Redirect to template builder
                redirect(base_url('evaluation_template/builder/' . $templateId));
            } else {
                // Set error message
                flash('error', 'Failed to create evaluation template');

                $data = [
                    'title' => 'Create Evaluation Template',
                    'name' => $name,
                    'activity_id' => $activityId,
                    'is_active' => $isActive,
                    'activities' => $activities,
                    'user' => $user
                ];

                $this->view('evaluation_template/create', $data);
            }
        } else {
            // Load the create view
            $data = [
                'title' => 'Create Evaluation Template',
                'name' => '',
                'activity_id' => 0,
                'is_active' => 1,
                'activities' => $activities,
                'user' => $user
            ];

            $this->view('evaluation_template/create', $data);
        }
    }

    /**
     * Builder method - Display the template builder and process form submission
     *
     * @param int $id The template ID
     * @return void
     */
    public function builder($id) {
        // Check if user is authenticated and is admin
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        $user = current_user();

        if ($user['role'] !== 'admin') {
            flash('error', 'You do not have permission to build evaluation templates');
            redirect(base_url('evaluation_template'));
        }

        // Get template
        $template = $this->evaluationTemplateModel->getWithCriteriaAndSubcriteria($id);

        if (!$template) {
            flash('error', 'Evaluation template not found');
            redirect(base_url('evaluation_template'));
        }

        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
           // debug($_POST);
            // Get form data
            $criteriaCount = isset($_POST['criteria_count']) ? (int)$_POST['criteria_count'] : 0;

            // Validate template weights
            $totalTemplateWeight = 0;
            $validationErrors = [];

            // Get all criteria indices from POST data
            $criteriaIndices = [];
            foreach ($_POST as $key => $value) {
                if (preg_match('/^criteria_name_(\d+)$/', $key, $matches)) {
                    $criteriaIndices[] = (int)$matches[1];
                }
            }

            // First pass: validate subcriteria weights for each criterion
            foreach ($criteriaIndices as $i) {
                $criteriaName = isset($_POST['criteria_name_' . $i]) ? trim($_POST['criteria_name_' . $i]) : '';
                $criteriaWeight = isset($_POST['criteria_weight_' . $i]) ? (float)$_POST['criteria_weight_' . $i] : 0;
                $subcriteriaCount = isset($_POST['subcriteria_count_' . $i]) ? (int)$_POST['subcriteria_count_' . $i] : 0;

                // Calculate total subcriteria weight
                $totalSubcriteriaWeight = 0;
                for ($j = 1; $j <= $subcriteriaCount; $j++) {
                    $subcriteriaWeight = isset($_POST['subcriteria_weight_' . $i . '_' . $j]) ? (float)$_POST['subcriteria_weight_' . $i . '_' . $j] : 0;
                    $totalSubcriteriaWeight += $subcriteriaWeight;
                }

                // Validate that criterion weight equals sum of subcriteria weights
                if ($subcriteriaCount > 0 && abs($criteriaWeight - $totalSubcriteriaWeight) > 0.01) {
                    $validationErrors[] = "Criterion '{$criteriaName}' weight ({$criteriaWeight}) must equal the sum of its subcriteria weights ({$totalSubcriteriaWeight}).";
                }

                $totalTemplateWeight += $criteriaWeight;
            }

            // Validate that total template weight equals 100
            if (abs($totalTemplateWeight - 100) > 0.01) {
                $validationErrors[] = "Total template weight must be exactly 100. Current total: {$totalTemplateWeight}.";
            }

            // If validation errors exist, show them and return
            if (!empty($validationErrors)) {
                foreach ($validationErrors as $error) {
                    flash('error', $error);
                }

                // Reload the builder view with submitted data
                $template = $this->evaluationTemplateModel->getWithCriteriaAndSubcriteria($id);

                // Prepare data with submitted values
                $submittedData = [
                    'criteria_count' => $criteriaCount,
                    'criteria' => []
                ];

                // Get all criteria indices from POST data if not already defined
                if (!isset($criteriaIndices)) {
                    $criteriaIndices = [];
                    foreach ($_POST as $key => $value) {
                        if (preg_match('/^criteria_name_(\d+)$/', $key, $matches)) {
                            $criteriaIndices[] = (int)$matches[1];
                        }
                    }
                }

                foreach ($criteriaIndices as $i) {
                    $criteriaData = [
                        'name' => isset($_POST['criteria_name_' . $i]) ? trim($_POST['criteria_name_' . $i]) : '',
                        'description' => isset($_POST['criteria_description_' . $i]) ? trim($_POST['criteria_description_' . $i]) : '',
                        'weight' => isset($_POST['criteria_weight_' . $i]) ? (float)$_POST['criteria_weight_' . $i] : 0,
                        'subcriteria' => []
                    ];

                    $subcriteriaCount = isset($_POST['subcriteria_count_' . $i]) ? (int)$_POST['subcriteria_count_' . $i] : 0;

                    for ($j = 1; $j <= $subcriteriaCount; $j++) {
                        $criteriaData['subcriteria'][] = [
                            'name' => isset($_POST['subcriteria_name_' . $i . '_' . $j]) ? trim($_POST['subcriteria_name_' . $i . '_' . $j]) : '',
                            'description' => isset($_POST['subcriteria_description_' . $i . '_' . $j]) ? trim($_POST['subcriteria_description_' . $i . '_' . $j]) : '',
                            'weight' => isset($_POST['subcriteria_weight_' . $i . '_' . $j]) ? (float)$_POST['subcriteria_weight_' . $i . '_' . $j] : 0
                        ];
                    }

                    $submittedData['criteria'][] = $criteriaData;
                }

                $template['submitted_data'] = $submittedData;

                $data = [
                    'title' => 'Template Builder',
                    'template' => $template,
                    'user' => $user
                ];

                $this->view('evaluation_template/builder', $data);
                return;
            }

            // Begin transaction
            $this->evaluationCriteriaModel->beginTransaction();

            try {
                // Delete existing criteria and subcriteria
                $this->evaluationCriteriaModel->deleteByTemplateId($id);

                // Get all criteria indices from POST data if not already defined
                if (!isset($criteriaIndices)) {
                    $criteriaIndices = [];
                    foreach ($_POST as $key => $value) {
                        if (preg_match('/^criteria_name_(\d+)$/', $key, $matches)) {
                            $criteriaIndices[] = (int)$matches[1];
                        }
                    }
                }

                // Create new criteria and subcriteria
                $orderIndex = 1; // Use a separate counter for the order
                foreach ($criteriaIndices as $i) {
                    $criteriaName = isset($_POST['criteria_name_' . $i]) ? trim($_POST['criteria_name_' . $i]) : '';
                    $criteriaDescription = isset($_POST['criteria_description_' . $i]) ? trim($_POST['criteria_description_' . $i]) : '';
                    $criteriaWeight = isset($_POST['criteria_weight_' . $i]) ? (float)$_POST['criteria_weight_' . $i] : 0;

                    // Create criterion
                    $criteriaId = $this->evaluationCriteriaModel->create([
                        'template_id' => $id,
                        'name' => $criteriaName,
                        'description' => $criteriaDescription,
                        'weight' => $criteriaWeight,
                        'order' => $orderIndex++
                    ]);

                    if (!$criteriaId) {
                        throw new Exception('Failed to create criterion');
                    }

                    // Get subcriteria count for this criterion
                    $subcriteriaCount = isset($_POST['subcriteria_count_' . $i]) ? (int)$_POST['subcriteria_count_' . $i] : 0;

                    // Create subcriteria
                    for ($j = 1; $j <= $subcriteriaCount; $j++) {
                        $subcriteriaName = isset($_POST['subcriteria_name_' . $i . '_' . $j]) ? trim($_POST['subcriteria_name_' . $i . '_' . $j]) : '';
                        $subcriteriaDescription = isset($_POST['subcriteria_description_' . $i . '_' . $j]) ? trim($_POST['subcriteria_description_' . $i . '_' . $j]) : '';
                        $subcriteriaWeight = isset($_POST['subcriteria_weight_' . $i . '_' . $j]) ? (float)$_POST['subcriteria_weight_' . $i . '_' . $j] : 0;

                        $subcriteriaId = $this->evaluationSubcriteriaModel->create([
                            'criteria_id' => $criteriaId,
                            'name' => $subcriteriaName,
                            'description' => $subcriteriaDescription,
                            'weight' => $subcriteriaWeight,
                            'order' => $j
                        ]);

                        if (!$subcriteriaId) {
                            throw new Exception('Failed to create subcriterion');
                        }
                    }
                }

                // Commit transaction
                $this->evaluationCriteriaModel->commit();

                // Set success message
                flash('success', 'Evaluation template updated successfully');

                // Redirect to templates page
                redirect(base_url('evaluation_template'));
            } catch (Exception $e) {
                // Rollback transaction
                $this->evaluationCriteriaModel->rollBack();

                // Set error message
                flash('error', 'Failed to update evaluation template: ' . $e->getMessage());

                // Reload the builder view
                $template = $this->evaluationTemplateModel->getWithCriteriaAndSubcriteria($id);

                $data = [
                    'title' => 'Template Builder',
                    'template' => $template,
                    'user' => $user
                ];

                $this->view('evaluation_template/builder', $data);
            }
        } else {
            // Load the builder view
            $data = [
                'title' => 'Template Builder',
                'template' => $template,
                'user' => $user
            ];

            $this->view('evaluation_template/builder', $data);
        }
    }

    /**
     * Edit method - Display the edit form and process form submission
     *
     * @param int $id The template ID
     * @return void
     */
    public function edit($id) {
        // Check if user is authenticated and is admin
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        $user = current_user();

        if ($user['role'] !== 'admin') {
            flash('error', 'You do not have permission to edit evaluation templates');
            redirect(base_url('evaluation_template'));
        }

        // Get template
        $template = $this->evaluationTemplateModel->getById($id);

        if (!$template) {
            flash('error', 'Evaluation template not found');
            redirect(base_url('evaluation_template'));
        }

        // Get activities
        $activities = $this->activityModel->getAllWithBusinessUnitName();

        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate form data
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $activityId = isset($_POST['activity_id']) ? (int)$_POST['activity_id'] : 0;
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            // Check if name is empty
            if (empty($name)) {
                flash('error', 'Template name is required');

                $data = [
                    'title' => 'Edit Evaluation Template',
                    'template' => $template,
                    'name' => $name,
                    'activity_id' => $activityId,
                    'is_active' => $isActive,
                    'activities' => $activities,
                    'user' => $user
                ];

                $this->view('evaluation_template/edit', $data);
                return;
            }

            // Check if activity is selected
            if ($activityId <= 0) {
                flash('error', 'Please select an activity');

                $data = [
                    'title' => 'Edit Evaluation Template',
                    'template' => $template,
                    'name' => $name,
                    'activity_id' => $activityId,
                    'is_active' => $isActive,
                    'activities' => $activities,
                    'user' => $user
                ];

                $this->view('evaluation_template/edit', $data);
                return;
            }

            // Check if activity exists
            if (!$this->activityModel->getById($activityId)) {
                flash('error', 'Selected activity does not exist');

                $data = [
                    'title' => 'Edit Evaluation Template',
                    'template' => $template,
                    'name' => $name,
                    'activity_id' => $activityId,
                    'is_active' => $isActive,
                    'activities' => $activities,
                    'user' => $user
                ];

                $this->view('evaluation_template/edit', $data);
                return;
            }

            // If this template is active, deactivate other templates for this activity
            if ($isActive) {
                $activeTemplate = $this->evaluationTemplateModel->getActiveByActivityId($activityId);

                if ($activeTemplate && $activeTemplate['id'] != $id) {
                    $this->evaluationTemplateModel->update($activeTemplate['id'], [
                        'is_active' => 0
                    ]);
                }
            }

            // Update template
            $result = $this->evaluationTemplateModel->update($id, [
                'name' => $name,
                'activity_id' => $activityId,
                'is_active' => $isActive
            ]);

            if ($result) {
                // Set success message
                flash('success', 'Evaluation template updated successfully');

                // Redirect to templates page
                redirect(base_url('evaluation_template'));
            } else {
                // Set error message
                flash('error', 'Failed to update evaluation template');

                $data = [
                    'title' => 'Edit Evaluation Template',
                    'template' => $template,
                    'name' => $name,
                    'activity_id' => $activityId,
                    'is_active' => $isActive,
                    'activities' => $activities,
                    'user' => $user
                ];

                $this->view('evaluation_template/edit', $data);
            }
        } else {
            // Load the edit view
            $data = [
                'title' => 'Edit Evaluation Template',
                'template' => $template,
                'name' => $template['name'],
                'activity_id' => $template['activity_id'],
                'is_active' => $template['is_active'],
                'activities' => $activities,
                'user' => $user
            ];

            $this->view('evaluation_template/edit', $data);
        }
    }

    /**
     * Inspect method - Display the template details
     *
     * @param int $id The template ID
     * @return void
     */
    public function inspect($id) {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        $user = current_user();

        // Get template with criteria and subcriteria
        $template = $this->evaluationTemplateModel->getWithCriteriaAndSubcriteria($id);

        if (!$template) {
            flash('error', 'Evaluation template not found');
            redirect(base_url('evaluation_template'));
        }

        // Get activity
        $activity = $this->activityModel->getByIdWithBusinessUnitName($template['activity_id']);
        // Calculate total weight for criteria



        $data = [
            'title' => 'View Template',
            'template' => $template,
            'activity' => $activity,
            'user' => $user
        ];

        $this->view('evaluation_template/view', $data);
    }

    /**
     * Delete method - Delete a template
     *
     * @param int $id The template ID
     * @return void
     */
    public function delete($id) {
        // Check if user is authenticated and is admin
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        $user = current_user();

        if ($user['role'] !== 'admin') {
            flash('error', 'You do not have permission to delete evaluation templates');
            redirect(base_url('evaluation_template'));
        }

        // Get template
        $template = $this->evaluationTemplateModel->getById($id);

        if (!$template) {
            flash('error', 'Evaluation template not found');
            redirect(base_url('evaluation_template'));
        }

        // Check if template is used in evaluations
        if ($this->evaluationTemplateModel->isUsedInEvaluations($id)) {
            flash('error', 'Cannot delete template because it is used in evaluations');
            redirect(base_url('evaluation_template'));
        }

        // Delete template with related criteria and subcriteria
        $result = $this->evaluationTemplateModel->deleteWithRelated($id);

        if ($result) {
            // Set success message
            flash('success', 'Evaluation template deleted successfully');
        } else {
            // Set error message
            flash('error', 'Failed to delete evaluation template');
        }

        // Redirect to templates page
        redirect(base_url('evaluation_template'));
    }
}
