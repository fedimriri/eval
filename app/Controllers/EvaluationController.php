<?php
/**
 * Evaluation Controller
 */
class EvaluationController extends Controller {
    private $evaluationModel;
    private $evaluationSubcriteriaResultModel;
    private $evaluationTemplateModel;
    private $agentModel;
    private $activityModel;
    private $businessUnitModel;

    /**
     * Constructor - Load models
     */
    public function __construct() {
        $this->evaluationModel = $this->model('EvaluationModel');
        $this->evaluationSubcriteriaResultModel = $this->model('EvaluationSubcriteriaResultModel');
        $this->evaluationTemplateModel = $this->model('EvaluationTemplateModel');
        $this->agentModel = $this->model('AgentModel');
        $this->activityModel = $this->model('ActivityModel');
        $this->businessUnitModel = $this->model('BusinessUnitModel');
    }

    /**
     * Index method - Display all evaluations with filters
     *
     * @return void
     */
    public function index() {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        $user = current_user();

        // Get filter parameters
        $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
        $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;
        $businessUnits = isset($_GET['business_units']) && is_array($_GET['business_units']) ? $_GET['business_units'] : [];
        $activities = isset($_GET['activities']) && is_array($_GET['activities']) ? $_GET['activities'] : [];
        $agents = isset($_GET['agents']) && is_array($_GET['agents']) ? $_GET['agents'] : [];

        // Default to one month range if no dates provided
        if (!$startDate && !$endDate) {
            $startDate = date('Y-m-d', strtotime('-1 month'));
            $endDate = date('Y-m-d');
        }

        // Get evaluations with filters
        if ($user['role'] === 'admin') {
            // Get all business units for filter dropdown
            $allBusinessUnits = $this->businessUnitModel->getAll();

            // Get all activities for filter dropdown
            $allActivities = $this->activityModel->getAllWithBusinessUnitName();

            // Get all agents for filter dropdown
            $allAgents = $this->agentModel->getAllWithDetails();

            // Apply filters
            $evaluations = (!empty($businessUnits) || !empty($activities) || !empty($agents) || ($startDate && $endDate))
                ? $this->evaluationModel->getFiltered($businessUnits, $activities, $agents, $startDate, $endDate)
                : $this->evaluationModel->getAll();
        } else {
            // Get business units accessible to manager
            $allBusinessUnits = $this->businessUnitModel->getByManagerId($user['id']);

            // Get activities accessible to manager
            $allActivities = $this->activityModel->getAccessibleToManager($user['id']);

            // Get agents accessible to manager
            $allAgents = $this->agentModel->getAccessibleToManager($user['id']);

            // Apply filters
            $evaluations = (!empty($businessUnits) || !empty($activities) || !empty($agents) || ($startDate && $endDate))
                ? $this->evaluationModel->getFilteredForManager($user['id'], $businessUnits, $activities, $agents, $startDate, $endDate)
                : $this->evaluationModel->getAccessibleToManager($user['id']);
        }

        // Load the view
        $data = [
            'title' => 'Evaluations',
            'evaluations' => $evaluations,
            'businessUnits' => $allBusinessUnits,
            'activities' => $allActivities,
            'agents' => $allAgents,
            'user' => $user
        ];

        $this->view('evaluation/index', $data);
    }

    /**
     * Create method - Display the create form and process form submission
     *
     * @return void
     */
    public function create() {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        $user = current_user();

        // Get business units
        if ($user['role'] === 'admin') {
            $businessUnits = $this->businessUnitModel->getAll();
        } else {
            $businessUnits = $this->businessUnitModel->getByManagerId($user['id']);
        }

        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate form data
            $businessUnitId = isset($_POST['business_unit_id']) ? (int)$_POST['business_unit_id'] : 0;
            $activityId = isset($_POST['activity_id']) ? (int)$_POST['activity_id'] : 0;
            $agentId = isset($_POST['agent_id']) ? (int)$_POST['agent_id'] : 0;

            // Check if all required fields are provided
            if ($businessUnitId <= 0 || $activityId <= 0 || $agentId <= 0) {
                flash('error', 'Please select business unit, activity, and agent');

                $data = [
                    'title' => 'Create Evaluation',
                    'businessUnits' => $businessUnits,
                    'business_unit_id' => $businessUnitId,
                    'activity_id' => $activityId,
                    'agent_id' => $agentId,
                    'user' => $user
                ];

                $this->view('evaluation/create', $data);
                return;
            }

            // Check if agent exists
            $agent = $this->agentModel->getById($agentId);
            if (!$agent) {
                flash('error', 'Selected agent does not exist');

                $data = [
                    'title' => 'Create Evaluation',
                    'businessUnits' => $businessUnits,
                    'business_unit_id' => $businessUnitId,
                    'activity_id' => $activityId,
                    'agent_id' => $agentId,
                    'user' => $user
                ];

                $this->view('evaluation/create', $data);
                return;
            }

            // Check if user has access to this agent
            if ($user['role'] !== 'admin') {
                $accessibleAgents = $this->agentModel->getAccessibleToManager($user['id']);
                $hasAccess = false;

                foreach ($accessibleAgents as $accessibleAgent) {
                    if ($accessibleAgent['id'] == $agentId) {
                        $hasAccess = true;
                        break;
                    }
                }

                if (!$hasAccess) {
                    flash('error', 'You do not have permission to evaluate this agent');

                    $data = [
                        'title' => 'Create Evaluation',
                        'businessUnits' => $businessUnits,
                        'business_unit_id' => $businessUnitId,
                        'activity_id' => $activityId,
                        'agent_id' => $agentId,
                        'user' => $user
                    ];

                    $this->view('evaluation/create', $data);
                    return;
                }
            }

            // Get activity
            $activity = $this->activityModel->getById($agent['activity_id']);

            // Get active template
            $template = $this->evaluationTemplateModel->getActiveByActivityId($activity['id']);

            if (!$template) {
                flash('error', 'No active evaluation template found for this agent\'s activity');

                $data = [
                    'title' => 'Create Evaluation',
                    'businessUnits' => $businessUnits,
                    'business_unit_id' => $businessUnitId,
                    'activity_id' => $activityId,
                    'agent_id' => $agentId,
                    'user' => $user
                ];

                $this->view('evaluation/create', $data);
                return;
            }

            // Redirect to evaluation form
            redirect(base_url('evaluation/form/' . $agentId . '/' . $template['id']));
        } else {
            // Load the create view
            $data = [
                'title' => 'Create Evaluation',
                'businessUnits' => $businessUnits,
                'user' => $user
            ];

            $this->view('evaluation/create', $data);
        }
    }

    /**
     * Get activities by business unit ID (AJAX endpoint)
     *
     * @param int $businessUnitId The business unit ID
     * @return void
     */
    public function get_activities_by_business_unit($businessUnitId) {
        // Check if user is authenticated
        if (!is_authenticated()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $user = current_user();

        // Validate business unit ID
        $businessUnitId = (int)$businessUnitId;

        if ($businessUnitId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid business unit ID']);
            return;
        }

        // Check if user has access to this business unit
        if ($user['role'] !== 'admin') {
            $accessibleBusinessUnits = $this->businessUnitModel->getByManagerId($user['id']);
            $hasAccess = false;

            foreach ($accessibleBusinessUnits as $bu) {
                if ($bu['id'] == $businessUnitId) {
                    $hasAccess = true;
                    break;
                }
            }

            if (!$hasAccess) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                return;
            }
        }

        // Get activities for this business unit
        $activities = $this->activityModel->getByBusinessUnitId($businessUnitId);

        // Return as JSON
        header('Content-Type: application/json');
        echo json_encode($activities);
    }

    /**
     * Get agents by activity ID (AJAX endpoint)
     *
     * @param int $activityId The activity ID
     * @return void
     */
    public function get_agents_by_activity($activityId) {
        // Check if user is authenticated
        if (!is_authenticated()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $user = current_user();

        // Validate activity ID
        $activityId = (int)$activityId;

        if ($activityId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid activity ID']);
            return;
        }

        // Get activity
        $activity = $this->activityModel->getById($activityId);

        if (!$activity) {
            http_response_code(404);
            echo json_encode(['error' => 'Activity not found']);
            return;
        }

        // Check if user has access to this activity's business unit
        if ($user['role'] !== 'admin') {
            $accessibleBusinessUnits = $this->businessUnitModel->getByManagerId($user['id']);
            $hasAccess = false;

            foreach ($accessibleBusinessUnits as $bu) {
                if ($bu['id'] == $activity['business_unit_id']) {
                    $hasAccess = true;
                    break;
                }
            }

            if (!$hasAccess) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                return;
            }
        }

        // Get agents for this activity
        $agents = $this->agentModel->getByActivityId($activityId);

        // Return as JSON
        header('Content-Type: application/json');
        echo json_encode($agents);
    }

    /**
     * Form method - Display the evaluation form and process form submission
     *
     * @param int $agentId The agent ID
     * @param int $templateId The template ID
     * @return void
     */
    public function form($agentId, $templateId) {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        $user = current_user();

        // Get agent
        $agent = $this->agentModel->getById($agentId);

        if (!$agent) {
            flash('error', 'Agent not found');
            redirect(base_url('evaluation/create'));
        }

        // Check if user has access to this agent
        if ($user['role'] !== 'admin') {
            $accessibleAgents = $this->agentModel->getAccessibleToManager($user['id']);
            $hasAccess = false;

            foreach ($accessibleAgents as $accessibleAgent) {
                if ($accessibleAgent['id'] == $agentId) {
                    $hasAccess = true;
                    break;
                }
            }

            if (!$hasAccess) {
                flash('error', 'You do not have permission to evaluate this agent');
                redirect(base_url('evaluation/create'));
            }
        }

        // Get template
        $template = $this->evaluationTemplateModel->getWithCriteriaAndSubcriteria($templateId);

        if (!$template) {
            flash('error', 'Evaluation template not found');
            redirect(base_url('evaluation/create'));
        }
        // Get activity
        $activity = $this->activityModel->getById($agent['activity_id']);

        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate form data
            $comments = trim($_POST['comments']);

            // Process subcriteria results
            $results = [];

            foreach ($template['criteria'] as $criterion) {
                foreach ($criterion['subcriteria'] as $subcriterion) {
                    $notationKey = 'notation_' . $subcriterion['id'];
                    $commentKey = 'comment_' . $subcriterion['id'];

                    $notation = isset($_POST[$notationKey]) ? trim($_POST[$notationKey]) : '';
                    $comment = isset($_POST[$commentKey]) ? trim($_POST[$commentKey]) : '';

                    // Validate notation
                    if (!in_array($notation, ['C', 'NC', 'PC', 'SI'])) {
                        flash('error', 'Please select a valid notation for all subcriteria');

                        $data = [
                            'title' => 'Evaluation Form',
                            'agent' => $agent,
                            'template' => $template,
                            'activity' => $activity,
                            'comments' => $comments,
                            'user' => $user
                        ];

                        $this->view('evaluation/form', $data);
                        return;
                    }

                    // Add to results array
                    $results[] = [
                        'subcriteria_id' => $subcriterion['id'],
                        'notation' => $notation,
                        'comments' => $comment,
                        'criteria_id' => $criterion['id']
                    ];
                }
            }

            // Calculate scores
            $scoreData = $this->calculateEvaluationScore($results, $template);
            $totalScore = $scoreData['total_score'];
            $resultsWithScores = $scoreData['results'];

            // Create evaluation
            $evaluation = [
                'agent_id' => $agentId,
                'template_id' => $templateId,
                'evaluator_id' => $user['id'],
                'activity_id' => $activity['id'],
                'score_total' => $totalScore, // Calculated score
                'comments' => $comments,
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Begin transaction
            $this->evaluationModel->beginTransaction();

            try {
                // Create evaluation
                $evaluationId = $this->evaluationModel->create($evaluation);

                if (!$evaluationId) {
                    throw new Exception('Failed to create evaluation');
                }

                // Create subcriteria results
                foreach ($resultsWithScores as $result) {
                    $resultData = [
                        'evaluation_id' => $evaluationId,
                        'subcriteria_id' => $result['subcriteria_id'],
                        'notation' => $result['notation'],
                        'comments' => $result['comments'],
                        'score' => $result['score']
                    ];

                    $resultSuccess = $this->evaluationSubcriteriaResultModel->create($resultData);

                    if (!$resultSuccess) {
                        throw new Exception('Failed to create subcriteria result');
                    }
                }

                // Commit transaction
                $this->evaluationModel->commit();

                // Set success message
                flash('success', 'Evaluation created successfully');

                // Redirect to evaluations page
                redirect(base_url('evaluation'));
            } catch (Exception $e) {
                // Rollback transaction
                $this->evaluationModel->rollBack();

                // Set error message
                flash('error', 'Failed to create evaluation: ' . $e->getMessage());

                $data = [
                    'title' => 'Evaluation Form',
                    'agent' => $agent,
                    'template' => $template,
                    'activity' => $activity,
                    'comments' => $comments,
                    'user' => $user
                ];

                $this->view('evaluation/form', $data);
            }
        } else {
            // Load the form view
            $data = [
                'title' => 'Evaluation Form',
                'agent' => $agent,
                'template' => $template,
                'activity' => $activity,
                'comments' => '',
                'user' => $user
            ];

            $this->view('evaluation/form', $data);
        }
    }

    /**
     * View Evaluation method - Display an evaluation
     *
     * @param int $id The evaluation ID
     * @return void
     */
    public function viewEvaluation($id) {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        $user = current_user();

        // Get evaluation
        $evaluation = $this->evaluationModel->getWithScores($id);
        if (!$evaluation) {
            flash('error', 'Evaluation not found');
            redirect(base_url('evaluation'));
        }

        // Check if user has access to this evaluation
        if ($user['role'] !== 'admin' && $evaluation['evaluator_id'] != $user['id']) {
            $accessibleEvaluations = $this->evaluationModel->getAccessibleToManager($user['id']);
            $hasAccess = false;

            foreach ($accessibleEvaluations as $accessibleEvaluation) {
                if ($accessibleEvaluation['id'] == $id) {
                    $hasAccess = true;
                    break;
                }
            }

            if (!$hasAccess) {
                flash('error', 'You do not have permission to view this evaluation');
                redirect(base_url('evaluation'));
            }
        }

        // Get agent
        $agent = $this->agentModel->getById($evaluation['agent_id']);

        // Get activity
        $activity = $this->activityModel->getById($evaluation['activity_id']);

        // Get template
        $template = $this->evaluationTemplateModel->getWithCriteriaAndSubcriteria($evaluation['template_id']);

        // Get results for this evaluation
        $results = $this->evaluationSubcriteriaResultModel->getByEvaluationId($id);

        // Prepare results map
        $resultsMap = [];
        foreach ($results as $result) {
            $resultsMap[$result['subcriteria_id']] = [
                'notation' => $result['notation'],
                'score' => $result['score'],
                'comments' => $result['comments']
            ];
        }

        // Load the view
        $data = [
            'title' => 'View Evaluation',
            'evaluation' => $evaluation,
            'agent' => $agent,
            'activity' => $activity,
            'template' => $template,
            'results_map' => $resultsMap,
            'user' => $user
        ];
        $this->view('evaluation/view', $data);
    }

    /**
     * Export method - Route to the appropriate export method
     *
     * @param string $type The export type (excel or pdf)
     * @param int $id The evaluation ID
     * @return void
     */
    public function export($type, $id) {
        if ($type === 'excel') {
            $this->exportExcel($id);
        } elseif ($type === 'pdf') {
            $this->exportPdf($id);
        } else {
            flash('error', 'Invalid export type');
            redirect(base_url('evaluation/viewEvaluation/' . $id));
        }
    }

    /**
     * Export evaluation to Excel
     *
     * @param int $id The evaluation ID
     * @return void
     */
    private function exportExcel($id) {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        $user = current_user();

        // Get evaluation data
        $evaluationData = $this->getEvaluationData($id, $user);

        if (!$evaluationData) {
            redirect(base_url('evaluation'));
        }

        // Include export helper
        require_once dirname(__DIR__) . '/Helpers/export_helper.php';

        // Create a new Spreadsheet object
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('QA Evaluation App')
            ->setLastModifiedBy('QA Evaluation App')
            ->setTitle('Evaluation Report')
            ->setSubject('Evaluation Report')
            ->setDescription('Evaluation Report generated by QA Evaluation App')
            ->setKeywords('qa evaluation report')
            ->setCategory('Report');

        // Get the active sheet
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Evaluation');

        // Define starting position
        $Scol = 'C';
        $Srow = 5;

        // Add title
        $sheet->setCellValue($Scol . $Srow, 'EVALUATION REPORT');
        $sheet->getStyle($Scol . $Srow)->getFont()->setBold(true)->setSize(16);
        $Srow += 2;

        // Add evaluation information
        $sheet->setCellValue($Scol . $Srow, 'Evaluation Details');
        $sheet->getStyle($Scol . $Srow)->getFont()->setBold(true)->setSize(14);
        $Srow += 2;

        // Create evaluation summary table
        $sheet->setCellValue($Scol . $Srow, 'Agent:');
        $sheet->setCellValue(chr(ord($Scol) + 1) . $Srow, $evaluationData['agent']['name']);
        $sheet->setCellValue(chr(ord($Scol) + 3) . $Srow, 'Evaluation Date:');
        $sheet->setCellValue(chr(ord($Scol) + 4) . $Srow, date('d M Y', strtotime($evaluationData['evaluation']['evaluation_date'])));
        $Srow++;

        $sheet->setCellValue($Scol . $Srow, 'Email:');
        $sheet->setCellValue(chr(ord($Scol) + 1) . $Srow, $evaluationData['agent']['email']);
        $sheet->setCellValue(chr(ord($Scol) + 3) . $Srow, 'Evaluator:');
        $sheet->setCellValue(chr(ord($Scol) + 4) . $Srow, $evaluationData['user']['name']);
        $Srow++;

        $sheet->setCellValue($Scol . $Srow, 'Activity:');
        $sheet->setCellValue(chr(ord($Scol) + 1) . $Srow, $evaluationData['activity']['name']);
        $sheet->setCellValue(chr(ord($Scol) + 3) . $Srow, 'Total Score:');
        $sheet->setCellValue(chr(ord($Scol) + 4) . $Srow, $evaluationData['evaluation']['score_total'] . '%');
        $Srow += 2;

        // Add overall comments if available
        if (!empty($evaluationData['evaluation']['comments'])) {
            $sheet->setCellValue($Scol . $Srow, 'Overall Comments:');
            $Srow++;
            $sheet->setCellValue($Scol . $Srow, $evaluationData['evaluation']['comments']);
            $Srow += 2;
        }

        // Add criteria and subcriteria
        $sheet->setCellValue($Scol . $Srow, 'Evaluation Results');
        $sheet->getStyle($Scol . $Srow)->getFont()->setBold(true)->setSize(14);
        $Srow += 2;

        // Create header row for criteria table
        $sheet->setCellValue($Scol . $Srow, 'Criterion / Subcriterion');
        $sheet->setCellValue(chr(ord($Scol) + 1) . $Srow, 'Weight');
        $sheet->setCellValue(chr(ord($Scol) + 2) . $Srow, 'Score');
        $sheet->setCellValue(chr(ord($Scol) + 3) . $Srow, 'Notation');
        $sheet->setCellValue(chr(ord($Scol) + 4) . $Srow, 'Comments');

        // Style header row
        $headerStyle = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E0E0E0',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $sheet->getStyle($Scol . $Srow . ':' . chr(ord($Scol) + 4) . $Srow)->applyFromArray($headerStyle);
        $Srow++;

        // Add criteria and subcriteria data
        foreach ($evaluationData['template']['criteria'] as $criterion) {
            // Add criterion row
            $sheet->setCellValue($Scol . $Srow, $criterion['name'] . ' (Weight: ' . $criterion['weight'] . ')');
            $sheet->getStyle($Scol . $Srow)->getFont()->setBold(true);
            $sheet->mergeCells($Scol . $Srow . ':' . chr(ord($Scol) + 4) . $Srow);

            // Style criterion row
            $criterionStyle = [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => '007BFF',
                    ],
                ],
                'font' => [
                    'color' => [
                        'rgb' => 'FFFFFF',
                    ],
                    'bold' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];

            $sheet->getStyle($Scol . $Srow . ':' . chr(ord($Scol) + 4) . $Srow)->applyFromArray($criterionStyle);
            $Srow++;

            // Add description if available
            if (!empty($criterion['description'])) {
                $sheet->setCellValue($Scol . $Srow, $criterion['description']);
                $sheet->mergeCells($Scol . $Srow . ':' . chr(ord($Scol) + 4) . $Srow);
                $sheet->getStyle($Scol . $Srow)->getAlignment()->setWrapText(true);
                $Srow++;
            }

            // Add subcriteria
            if (empty($criterion['subcriteria'])) {
                $sheet->setCellValue($Scol . $Srow, 'This criterion has no subcriteria.');
                $sheet->mergeCells($Scol . $Srow . ':' . chr(ord($Scol) + 4) . $Srow);
                $Srow++;
            } else {
                foreach ($criterion['subcriteria'] as $subcriterion) {
                    // Get result for this subcriterion
                    $score = null;
                    $comments = '';
                    $notation = '';

                    if (isset($evaluationData['results_map'][$subcriterion['id']])) {
                        $result = $evaluationData['results_map'][$subcriterion['id']];
                        $score = $result['score'];
                        $comments = $result['comments'];
                        $notation = $result['notation'];
                    }

                    // Add subcriterion data
                    $sheet->setCellValue($Scol . $Srow, $subcriterion['name']);
                    $sheet->setCellValue(chr(ord($Scol) + 1) . $Srow, $subcriterion['weight']);
                    $sheet->setCellValue(chr(ord($Scol) + 2) . $Srow, $score !== null ? $score : 'N/A');

                    // Add notation with full text
                    $notationText = 'N/A';
                    if (!empty($notation)) {
                        switch ($notation) {
                            case 'C':
                                $notationText = 'C - Conforme';
                                break;
                            case 'NC':
                                $notationText = 'NC - Non conforme';
                                break;
                            case 'PC':
                                $notationText = 'PC - Point critique';
                                break;
                            case 'SI':
                                $notationText = 'SI - Situation inacceptable';
                                break;
                        }
                    }

                    $sheet->setCellValue(chr(ord($Scol) + 3) . $Srow, $notationText);
                    $sheet->setCellValue(chr(ord($Scol) + 4) . $Srow, $comments);

                    // Style subcriterion row
                    $sheet->getStyle($Scol . $Srow . ':' . chr(ord($Scol) + 4) . $Srow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    $Srow++;
                }
            }
        }

        // Auto-size columns
        foreach (range($Scol, chr(ord($Scol) + 4)) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set active sheet index to the first sheet
        $spreadsheet->setActiveSheetIndex(0);

        // Create writer
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="evaluation_' . $id . '_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        // Save file to PHP output
        $writer->save('php://output');
        exit;
    }

    /**
     * Export evaluation to PDF
     *
     * @param int $id The evaluation ID
     * @return void
     */
    private function exportPdf($id) {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        $user = current_user();

        // Get evaluation data
        $evaluationData = $this->getEvaluationData($id, $user);

        if (!$evaluationData) {
            redirect(base_url('evaluation'));
        }

        // Include export helper
        require_once dirname(__DIR__) . '/Helpers/export_helper.php';

        // Create new mPDF instance
        $mpdf = new \Mpdf\Mpdf([
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);

        // Set document properties
        $mpdf->SetTitle('Evaluation Report');
        $mpdf->SetAuthor('QA Evaluation App');
        $mpdf->SetCreator('QA Evaluation App');

        // Start building HTML content
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Evaluation Report</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    font-size: 11pt;
                    line-height: 1.4;
                    color: #333;
                }
                h1 {
                    font-size: 18pt;
                    color: #333;
                    margin-bottom: 15px;
                    text-align: center;
                }
                h2 {
                    font-size: 14pt;
                    color: #333;
                    margin-top: 15px;
                    margin-bottom: 8px;
                }
                h5 {
                    font-size: 11pt;
                    margin: 0;
                    font-weight: bold;
                    color: white;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 15px;
                    font-size: 10pt;
                }
                .evaluation-table {
                    border: 1px solid #dee2e6;
                }
                th, td {
                    border: 1px solid #dee2e6;
                    padding: 4px 6px;
                    text-align: left;
                    vertical-align: middle;
                }
                th {
                    background-color: #f8f9fa;
                    font-weight: 600;
                    text-align: center;
                    font-size: 10pt;
                }
                .criterion-header {
                    background-color: #007bff;
                    color: white;
                    padding: 6px 8px;
                    font-weight: bold;
                    border: 1px solid #0062cc;
                }
                .subcriterion-row td {
                    border-top: none;
                }
                .subcriterion-row:nth-child(even) {
                    background-color: rgba(0, 0, 0, 0.02);
                }
                .text-muted {
                    color: #6c757d;
                    font-size: 9pt;
                }
                strong {
                    font-weight: bold;
                }
                .score {
                    font-weight: bold;
                }
                .score-high {
                    color: #28a745;
                }
                .score-medium {
                    color: #ffc107;
                }
                .score-low {
                    color: #dc3545;
                }

                /* Badge styling */
                .badge-lg {
                    position: relative;
                    padding: 3px 6px;
                    border-radius: 3px;
                    min-width: 20px;
                    text-align: center;
                    background-color: #fff;
                    color: #333;
                    font-weight: normal;
                    display: inline-block;
                }

                .badge-success {
                    border: 1px solid #28a745;
                    color: #28a745;
                }

                .badge-danger {
                    border: 1px solid #dc3545;
                    color: #dc3545;
                }

                .badge-warning {
                    border: 1px solid #ffc107;
                    color: #ffc107;
                }

                .badge-dark {
                    border: 1px solid #343a40;
                    color: #343a40;
                }

                .selected-notation.badge-success {
                    background-color: #28a745;
                    color: #fff;
                }

                .selected-notation.badge-danger {
                    background-color: #dc3545;
                    color: #fff;
                }

                .selected-notation.badge-warning {
                    background-color: #ffc107;
                    color: #333;
                }

                .selected-notation.badge-dark {
                    background-color: #343a40;
                    color: #fff;
                }

                small {
                    font-size: 85%;
                }
            </style>
        </head>
        <body>
            <h1>EVALUATION REPORT</h1>

            <h2>Evaluation Summary</h2>
            <table class="evaluation-table">
                <tbody>
                    <tr>
                        <td colspan="4" class="criterion-header">
                            <h5>Evaluation Information</h5>
                        </td>
                    </tr>
                    <tr>
                        <td width="20%"><strong>Agent:</strong></td>
                        <td width="30%">' . $evaluationData['agent']['name'] . '</td>
                        <td width="20%"><strong>Evaluation Date:</strong></td>
                        <td width="30%">' . date('d M Y', strtotime($evaluationData['evaluation']['evaluation_date'])) . '</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>' . $evaluationData['agent']['email'] . '</td>
                        <td><strong>Evaluator:</strong></td>
                        <td>' . $evaluationData['user']['name'] . '</td>
                    </tr>
                    <tr>
                        <td><strong>Activity:</strong></td>
                        <td>' . $evaluationData['activity']['name'] . '</td>
                        <td><strong>Total Score:</strong></td>
                        <td><span class="score ' . ($evaluationData['evaluation']['score_total'] >= 80 ? 'score-high' : ($evaluationData['evaluation']['score_total'] >= 60 ? 'score-medium' : 'score-low')) . '">' . $evaluationData['evaluation']['score_total'] . '%</span></td>
                    </tr>';

        // Add overall comments if available
        if (!empty($evaluationData['evaluation']['comments'])) {
            $html .= '
                <tr>
                    <td><strong>Overall Comments:</strong></td>
                    <td colspan="3">' . nl2br(htmlspecialchars($evaluationData['evaluation']['comments'])) . '</td>
                </tr>';
        }

        $html .= '
                </tbody>
            </table>

            <h2>Evaluation Results</h2>
            <table class="evaluation-table">
                <thead>
                    <tr>
                        <th width="30%">Criterion / Subcriterion</th>
                        <th width="10%">Weight</th>
                        <th width="15%">Score</th>
                        <th width="15%">Notation</th>
                        <th width="30%">Comments</th>
                    </tr>
                </thead>
                <tbody>';

        // Add criteria and subcriteria
        foreach ($evaluationData['template']['criteria'] as $criterion) {
            // Add criterion row
            $html .= '
                    <tr>
                        <td colspan="5" class="criterion-header">' . htmlspecialchars($criterion['name']) . ' (Weight: ' . $criterion['weight'] . ')</td>
                    </tr>';

            // Add description if available
            if (!empty($criterion['description'])) {
                $html .= '
                    <tr>
                        <td colspan="5">' . htmlspecialchars($criterion['description']) . '</td>
                    </tr>';
            }

            // Add subcriteria
            if (empty($criterion['subcriteria'])) {
                $html .= '
                    <tr>
                        <td colspan="5">This criterion has no subcriteria.</td>
                    </tr>';
            } else {
                foreach ($criterion['subcriteria'] as $subcriterion) {
                    // Get result for this subcriterion
                    $score = null;
                    $comments = '';
                    $notation = '';

                    if (isset($evaluationData['results_map'][$subcriterion['id']])) {
                        $result = $evaluationData['results_map'][$subcriterion['id']];
                        $score = $result['score'];
                        $comments = $result['comments'];
                        $notation = $result['notation'];
                    }

                    // Prepare notation display
                    $notationHtml = '<span class="badge badge-secondary">N/A</span>';
                    $notationText = '';

                    if (!empty($notation)) {
                        switch ($notation) {
                            case 'C':
                                $notationHtml = '<span class="badge-lg badge-success selected-notation">C</span>';
                                $notationText = 'Conforme';
                                break;
                            case 'NC':
                                $notationHtml = '<span class="badge-lg badge-danger selected-notation">NC</span>';
                                $notationText = 'Non conforme';
                                break;
                            case 'PC':
                                $notationHtml = '<span class="badge-lg badge-warning selected-notation">PC</span>';
                                $notationText = 'Point critique';
                                break;
                            case 'SI':
                                $notationHtml = '<span class="badge-lg badge-dark selected-notation">SI</span>';
                                $notationText = 'Situation inacceptable';
                                break;
                        }
                    }

                    // Prepare score display
                    $scoreHtml = '<span class="badge badge-secondary">N/A</span>';
                    if ($score !== null) {
                        $badgeClass = $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'danger');
                        $scoreHtml = '<span class="badge-lg badge-' . $badgeClass . '">' . $score . '</span>';
                    }

                    // Add subcriterion row
                    $html .= '
                    <tr class="subcriterion-row">
                        <td><strong>' . htmlspecialchars($subcriterion['name']) . '</strong>';

                    if (!empty($subcriterion['description'])) {
                        $html .= '<br><small>' . htmlspecialchars($subcriterion['description']) . '</small>';
                    }

                    $html .= '</td>
                        <td>' . $subcriterion['weight'] . '</td>
                        <td>' . $scoreHtml . '</td>
                        <td>' . $notationHtml . ' <small>' . $notationText . '</small></td>
                        <td>' . nl2br(htmlspecialchars($comments)) . '</td>
                    </tr>';
                }
            }
        }

        $html .= '
                </tbody>
            </table>
        </body>
        </html>';

        // Write HTML content to PDF
        $mpdf->WriteHTML($html);

        // Output PDF
        $mpdf->Output('evaluation_' . $id . '_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }

    /**
     * Get evaluation data for export
     *
     * @param int $id The evaluation ID
     * @param array $user The current user
     * @return array|false The evaluation data or false if not found or no access
     */
    private function getEvaluationData($id, $user) {
        // Get evaluation
        $evaluation = $this->evaluationModel->getWithScores($id);

        if (!$evaluation) {
            flash('error', 'Evaluation not found');
            return false;
        }

        // Check if user has access to this evaluation
        if ($user['role'] !== 'admin' && $evaluation['evaluator_id'] != $user['id']) {
            $accessibleEvaluations = $this->evaluationModel->getAccessibleToManager($user['id']);
            $hasAccess = false;

            foreach ($accessibleEvaluations as $accessibleEvaluation) {
                if ($accessibleEvaluation['id'] == $id) {
                    $hasAccess = true;
                    break;
                }
            }

            if (!$hasAccess) {
                flash('error', 'You do not have permission to export this evaluation');
                return false;
            }
        }

        // Get agent
        $agent = $this->agentModel->getById($evaluation['agent_id']);

        // Get activity
        $activity = $this->activityModel->getById($evaluation['activity_id']);

        // Get template
        $template = $this->evaluationTemplateModel->getWithCriteriaAndSubcriteria($evaluation['template_id']);

        // Get results for this evaluation
        $results = $this->evaluationSubcriteriaResultModel->getByEvaluationId($id);

        // Prepare results map
        $resultsMap = [];
        foreach ($results as $result) {
            $resultsMap[$result['subcriteria_id']] = [
                'notation' => $result['notation'],
                'score' => $result['score'],
                'comments' => $result['comments']
            ];
        }

        return [
            'evaluation' => $evaluation,
            'agent' => $agent,
            'template' => $template,
            'activity' => $activity,
            'results_map' => $resultsMap,
            'user' => $user
        ];
    }


    /**
     * Calculate evaluation score based on results
     *
     * @param array $results The evaluation results
     * @param array $template The evaluation template with criteria and subcriteria
     * @return array Array with total_score and updated results with scores
     */
    private function calculateEvaluationScore($results, $template) {
        $totalScore = 0;
        $totalWeight = 0;
        $siFound = false;
        $pcCriteriaIds = [];
        $resultsWithScores = [];

        // Calculate total possible weight
        foreach ($template['criteria'] as $criterion) {
            foreach ($criterion['subcriteria'] as $subcriterion) {
                $totalWeight += $subcriterion['weight'];
            }
        }

        // First pass: check for SI and PC notations
        foreach ($results as $result) {
            if ($result['notation'] === 'SI') {
                $siFound = true;
                break;
            }

            if ($result['notation'] === 'PC') {
                // Find the criteria_id for this subcriteria
                foreach ($template['criteria'] as $criterion) {
                    foreach ($criterion['subcriteria'] as $subcriterion) {
                        if ($subcriterion['id'] == $result['subcriteria_id']) {
                            $pcCriteriaIds[] = $criterion['id'];
                            break 2;
                        }
                    }
                }
            }
        }

        // Second pass: calculate scores
        foreach ($results as $result) {
            $score = 0;
            $criteriaId = null;

            // Find the subcriteria weight and criteria_id
            foreach ($template['criteria'] as $criterion) {
                foreach ($criterion['subcriteria'] as $subcriterion) {
                    if ($subcriterion['id'] == $result['subcriteria_id']) {
                        $weight = $subcriterion['weight'];
                        $criteriaId = $criterion['id'];
                        break 2;
                    }
                }
            }

            // Calculate score based on notation
            if ($siFound) {
                $score = 0;
            } else if (in_array($criteriaId, $pcCriteriaIds)) {
                $score = 0;
            } else if ($result['notation'] === 'C') {
                $score = $weight;
                $totalScore += $score;
            } else {
                $score = 0;
            }

            // Add score to result
            $result['score'] = $score;
            $resultsWithScores[] = $result;
        }

        // Calculate percentage score
        $percentageScore = ($totalWeight > 0) ? round(($totalScore / $totalWeight) * 100, 2) : 0;

        return [
            'total_score' => $percentageScore,
            'results' => $resultsWithScores
        ];
    }

    /**
     * Edit method - Display the edit form and process form submission
     *
     * @param int $id The evaluation ID
     * @return void
     */
    public function edit($id) {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        $user = current_user();

        // Get evaluation
        $evaluation = $this->evaluationModel->getWithScores($id);

        if (!$evaluation) {
            flash('error', 'Evaluation not found');
            redirect(base_url('evaluation'));
        }

        // Check if user has access to this evaluation
        if ($user['role'] !== 'admin' && $evaluation['evaluator_id'] != $user['id']) {
            $accessibleEvaluations = $this->evaluationModel->getAccessibleToManager($user['id']);
            $hasAccess = false;

            foreach ($accessibleEvaluations as $accessibleEvaluation) {
                if ($accessibleEvaluation['id'] == $id) {
                    $hasAccess = true;
                    break;
                }
            }

            if (!$hasAccess) {
                flash('error', 'You do not have permission to edit this evaluation');
                redirect(base_url('evaluation'));
            }
        }

        // Get agent
        $agent = $this->agentModel->getById($evaluation['agent_id']);

        // Get activity
        $activity = $this->activityModel->getById($evaluation['activity_id']);

        // Get template
        $template = $this->evaluationTemplateModel->getWithCriteriaAndSubcriteria($evaluation['template_id']);

        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate form data
            $comments = trim($_POST['comments']);

            // Process subcriteria results
            $results = [];

            foreach ($template['criteria'] as $criterion) {
                foreach ($criterion['subcriteria'] as $subcriterion) {
                    $notationKey = 'notation_' . $subcriterion['id'];
                    $commentKey = 'comment_' . $subcriterion['id'];

                    $notation = isset($_POST[$notationKey]) ? trim($_POST[$notationKey]) : '';
                    $comment = isset($_POST[$commentKey]) ? trim($_POST[$commentKey]) : '';

                    // Validate notation
                    if (!in_array($notation, ['C', 'NC', 'PC', 'SI'])) {
                        flash('error', 'Please select a valid notation for all subcriteria');

                        $data = [
                            'title' => 'Edit Evaluation',
                            'evaluation' => $evaluation,
                            'agent' => $agent,
                            'template' => $template,
                            'activity' => $activity,
                            'comments' => $comments,
                            'user' => $user
                        ];

                        $this->view('evaluation/edit', $data);
                        return;
                    }

                    // Add to results array
                    $results[] = [
                        'subcriteria_id' => $subcriterion['id'],
                        'notation' => $notation,
                        'comments' => $comment,
                        'criteria_id' => $criterion['id'] // Add criteria_id for score calculation
                    ];
                }
            }

            // Calculate scores
            $scoreData = $this->calculateEvaluationScore($results, $template);
            $totalScore = $scoreData['total_score'];
            $resultsWithScores = $scoreData['results'];

            // Update evaluation data
            $evaluationData = [
                'comments' => $comments,
                'score_total' => $totalScore
            ];

            // Begin transaction
            $this->evaluationModel->beginTransaction();

            try {
                // Update evaluation
                $result = $this->evaluationModel->update($id, $evaluationData);

                if (!$result) {
                    throw new Exception('Failed to update evaluation');
                }

                // Delete existing results
                $result = $this->evaluationSubcriteriaResultModel->deleteByEvaluationId($id);

                if (!$result) {
                    throw new Exception('Failed to delete existing results');
                }

                // Create new results
                foreach ($resultsWithScores as $result) {
                    $resultData = [
                        'evaluation_id' => $id,
                        'subcriteria_id' => $result['subcriteria_id'],
                        'notation' => $result['notation'],
                        'comments' => $result['comments'],
                        'score' => $result['score']
                    ];

                    $resultSuccess = $this->evaluationSubcriteriaResultModel->create($resultData);

                    if (!$resultSuccess) {
                        throw new Exception('Failed to create subcriteria result');
                    }
                }

                // Commit transaction
                $this->evaluationModel->commit();

                // Set success message
                flash('success', 'Evaluation updated successfully');

                // Redirect to evaluations page
                redirect(base_url('evaluation/viewEvaluation/' . $id));
            } catch (Exception $e) {
                // Rollback transaction
                $this->evaluationModel->rollBack();

                // Set error message
                flash('error', 'Failed to update evaluation: ' . $e->getMessage());

                $data = [
                    'title' => 'Edit Evaluation',
                    'evaluation' => $evaluation,
                    'agent' => $agent,
                    'template' => $template,
                    'activity' => $activity,
                    'comments' => $comments,
                    'user' => $user
                ];

                $this->view('evaluation/edit', $data);
            }
        } else {
            // Get results for this evaluation
            $results = $this->evaluationSubcriteriaResultModel->getByEvaluationId($id);

            // Prepare results map
            $resultsMap = [];
            foreach ($results as $result) {
                $resultsMap[$result['subcriteria_id']] = [
                    'notation' => $result['notation'],
                    'score' => $result['score'],
                    'comments' => $result['comments']
                ];
            }

            // Load the edit view
            $data = [
                'title' => 'Edit Evaluation',
                'evaluation' => $evaluation,
                'agent' => $agent,
                'template' => $template,
                'activity' => $activity,
                'comments' => $evaluation['comments'],
                'results_map' => $resultsMap,
                'user' => $user
            ];

            $this->view('evaluation/edit', $data);
        }
    }

    /**
     * Delete method - Delete an evaluation
     *
     * @param int $id The evaluation ID
     * @return void
     */
    public function delete($id) {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        $user = current_user();

        // Get evaluation
        $evaluation = $this->evaluationModel->getById($id);

        if (!$evaluation) {
            flash('error', 'Evaluation not found');
            redirect(base_url('evaluation'));
        }

        // Check if user has access to this evaluation
        if ($user['role'] !== 'admin' && $evaluation['evaluator_id'] != $user['id']) {
            flash('error', 'You do not have permission to delete this evaluation');
            redirect(base_url('evaluation'));
        }

        // Delete evaluation
        $result = $this->evaluationModel->deleteWithScores($id);

        if ($result) {
            flash('success', 'Evaluation deleted successfully');
        } else {
            flash('error', 'Failed to delete evaluation');
        }

        redirect(base_url('evaluation'));
    }
}
