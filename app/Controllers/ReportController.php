<?php
/**
 * Report Controller
 */
class ReportController extends Controller {
    private $evaluationModel;
    private $agentModel;
    private $activityModel;
    private $businessUnitModel;
    private $evaluationTemplateModel;
    private $evaluationSubcriteriaResultModel;

    /**
     * Constructor - Load models
     */
    public function __construct() {
        $this->evaluationModel = $this->model('EvaluationModel');
        $this->agentModel = $this->model('AgentModel');
        $this->activityModel = $this->model('ActivityModel');
        $this->businessUnitModel = $this->model('BusinessUnitModel');
        $this->evaluationTemplateModel = $this->model('EvaluationTemplateModel');
        $this->evaluationSubcriteriaResultModel = $this->model('EvaluationSubcriteriaResultModel');
    }

    /**
     * Index method - Display the report dashboard
     *
     * @return void
     */
    public function index() {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        $user = current_user();

        // Get summary data
        $summaryData = $this->getSummaryData($user);

        // Load the view
        $data = [
            'title' => 'Reports Dashboard',
            'user' => $user,
            'summaryData' => $summaryData
        ];

        $this->view('report/index', $data);
    }

    /**
     * Business Unit Report
     *
     * @return void
     */
    public function business_unit() {
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

        // Get selected business unit
        $businessUnitId = isset($_GET['business_unit_id']) ? $_GET['business_unit_id'] : null;
        $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
        $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

        // Get report data
        $reportData = [];
        if ($businessUnitId) {
            $reportData = $this->getBusinessUnitReportData($businessUnitId, $startDate, $endDate);
        }

        // Load the view
        $data = [
            'title' => 'Business Unit Report',
            'user' => $user,
            'businessUnits' => $businessUnits,
            'selectedBusinessUnitId' => $businessUnitId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportData' => $reportData
        ];

        $this->view('report/business_unit', $data);
    }

    /**
     * Agent Report
     *
     * @return void
     */
    public function agent() {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        $user = current_user();

        // Get agents
        if ($user['role'] === 'admin') {
            $agents = $this->agentModel->getAllWithDetails();
        } else {
            $agents = $this->agentModel->getAccessibleToManager($user['id']);
        }

        // Get selected agent
        $agentId = isset($_GET['agent_id']) ? $_GET['agent_id'] : null;
        $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
        $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

        // Get report data
        $reportData = [];
        if ($agentId) {
            $reportData = $this->getAgentReportData($agentId, $startDate, $endDate);
        }

        // Load the view
        $data = [
            'title' => 'Agent Report',
            'user' => $user,
            'agents' => $agents,
            'selectedAgentId' => $agentId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportData' => $reportData
        ];

        $this->view('report/agent', $data);
    }

    /**
     * Activity Report
     *
     * @return void
     */
    public function activity() {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        $user = current_user();

        // Get activities
        if ($user['role'] === 'admin') {
            $activities = $this->activityModel->getAllWithBusinessUnitName();
        } else {
            $activities = $this->activityModel->getAccessibleToManager($user['id']);
        }

        // Get selected activity
        $activityId = isset($_GET['activity_id']) ? $_GET['activity_id'] : null;
        $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
        $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

        // Get report data
        $reportData = [];
        if ($activityId) {
            $reportData = $this->getActivityReportData($activityId, $startDate, $endDate);
        }

        // Load the view
        $data = [
            'title' => 'Activity Report',
            'user' => $user,
            'activities' => $activities,
            'selectedActivityId' => $activityId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportData' => $reportData
        ];

        $this->view('report/activity', $data);
    }

    /**
     * Get summary data for dashboard
     *
     * @param array $user Current user
     * @return array Summary data
     */
    private function getSummaryData($user) {
        // Get evaluation counts
        if ($user['role'] === 'admin') {
            $totalEvaluations = $this->evaluationModel->getCount();
            $businessUnits = $this->businessUnitModel->getAll();
            $agents = $this->agentModel->getAll();
            $activities = $this->activityModel->getAll();
        } else {
            $totalEvaluations = $this->evaluationModel->getCountForManager($user['id']);
            $businessUnits = $this->businessUnitModel->getByManagerId($user['id']);
            $agents = $this->agentModel->getAccessibleToManager($user['id']);
            $activities = $this->activityModel->getAccessibleToManager($user['id']);
        }

        // Get average scores
        $averageScore = $this->evaluationModel->getAverageScore($user);

        // Get recent evaluations
        $recentEvaluations = $this->evaluationModel->getRecent(5, $user);

        return [
            'totalEvaluations' => $totalEvaluations,
            'totalBusinessUnits' => count($businessUnits),
            'totalAgents' => count($agents),
            'totalActivities' => count($activities),
            'averageScore' => $averageScore,
            'recentEvaluations' => $recentEvaluations
        ];
    }

    /**
     * Get business unit report data
     *
     * @param int $businessUnitId Business unit ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Report data
     */
    private function getBusinessUnitReportData($businessUnitId, $startDate, $endDate) {
        // Get business unit
        $businessUnit = $this->businessUnitModel->getById($businessUnitId);

        // Get evaluations for this business unit
        $evaluations = $this->evaluationModel->getByBusinessUnitIdAndDateRange($businessUnitId, $startDate, $endDate);

        // Get activities for this business unit
        $activities = $this->activityModel->getByBusinessUnitId($businessUnitId);

        // Get agents for this business unit
        $agents = $this->agentModel->getByBusinessUnitId($businessUnitId);

        // Calculate average scores by activity
        $activityScores = [];
        foreach ($activities as $activity) {
            $activityEvaluations = array_filter($evaluations, function($eval) use ($activity) {
                return $eval['activity_id'] == $activity['id'];
            });

            $totalScore = 0;
            $count = count($activityEvaluations);

            foreach ($activityEvaluations as $eval) {
                $totalScore += $eval['score_total'];
            }

            $averageScore = $count > 0 ? $totalScore / $count : 0;

            $activityScores[] = [
                'activity_id' => $activity['id'],
                'activity_name' => $activity['name'],
                'average_score' => $averageScore,
                'evaluation_count' => $count
            ];
        }

        // Calculate average scores by agent
        $agentScores = [];
        foreach ($agents as $agent) {
            $agentEvaluations = array_filter($evaluations, function($eval) use ($agent) {
                return $eval['agent_id'] == $agent['id'];
            });

            $totalScore = 0;
            $count = count($agentEvaluations);

            foreach ($agentEvaluations as $eval) {
                $totalScore += $eval['score_total'];
            }

            $averageScore = $count > 0 ? $totalScore / $count : 0;

            $agentScores[] = [
                'agent_id' => $agent['id'],
                'agent_name' => $agent['name'],
                'average_score' => $averageScore,
                'evaluation_count' => $count
            ];
        }

        // Calculate overall average score
        $totalScore = 0;
        $count = count($evaluations);

        foreach ($evaluations as $eval) {
            $totalScore += $eval['score_total'];
        }

        $overallAverageScore = $count > 0 ? $totalScore / $count : 0;

        return [
            'businessUnit' => $businessUnit,
            'evaluations' => $evaluations,
            'activities' => $activities,
            'agents' => $agents,
            'activityScores' => $activityScores,
            'agentScores' => $agentScores,
            'overallAverageScore' => $overallAverageScore,
            'evaluationCount' => $count
        ];
    }

    /**
     * Get agent report data
     *
     * @param int $agentId Agent ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Report data
     */
    private function getAgentReportData($agentId, $startDate, $endDate) {
        // Get agent
        $agent = $this->agentModel->getById($agentId);

        // Get evaluations for this agent
        $evaluations = $this->evaluationModel->getByAgentIdAndDateRange($agentId, $startDate, $endDate);

        // Calculate average score
        $totalScore = 0;
        $count = count($evaluations);

        foreach ($evaluations as $eval) {
            $totalScore += $eval['score_total'];
        }

        $averageScore = $count > 0 ? $totalScore / $count : 0;

        // Get score trend over time
        $scoreTrend = [];
        foreach ($evaluations as $eval) {
            $date = date('Y-m-d', strtotime($eval['evaluation_date']));
            if (!isset($scoreTrend[$date])) {
                $scoreTrend[$date] = [
                    'total' => 0,
                    'count' => 0
                ];
            }

            $scoreTrend[$date]['total'] += $eval['score_total'];
            $scoreTrend[$date]['count']++;
        }

        // Calculate daily averages
        $dailyAverages = [];
        foreach ($scoreTrend as $date => $data) {
            $dailyAverages[$date] = $data['total'] / $data['count'];
        }

        // Sort by date
        ksort($dailyAverages);

        return [
            'agent' => $agent,
            'evaluations' => $evaluations,
            'averageScore' => $averageScore,
            'evaluationCount' => $count,
            'dailyAverages' => $dailyAverages
        ];
    }

    /**
     * Get activity report data
     *
     * @param int $activityId Activity ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Report data
     */
    private function getActivityReportData($activityId, $startDate, $endDate) {
        // Get activity
        $activity = $this->activityModel->getById($activityId);

        // Get evaluations for this activity
        $evaluations = $this->evaluationModel->getByActivityIdAndDateRange($activityId, $startDate, $endDate);

        // Get agents for this activity
        $agents = $this->agentModel->getByActivityId($activityId);

        // Calculate average scores by agent
        $agentScores = [];
        foreach ($agents as $agent) {
            $agentEvaluations = array_filter($evaluations, function($eval) use ($agent) {
                return $eval['agent_id'] == $agent['id'];
            });

            $totalScore = 0;
            $count = count($agentEvaluations);

            foreach ($agentEvaluations as $eval) {
                $totalScore += $eval['score_total'];
            }

            $averageScore = $count > 0 ? $totalScore / $count : 0;

            $agentScores[] = [
                'agent_id' => $agent['id'],
                'agent_name' => $agent['name'],
                'average_score' => $averageScore,
                'evaluation_count' => $count
            ];
        }

        // Calculate overall average score
        $totalScore = 0;
        $count = count($evaluations);

        foreach ($evaluations as $eval) {
            $totalScore += $eval['score_total'];
        }

        $overallAverageScore = $count > 0 ? $totalScore / $count : 0;

        return [
            'activity' => $activity,
            'evaluations' => $evaluations,
            'agents' => $agents,
            'agentScores' => $agentScores,
            'overallAverageScore' => $overallAverageScore,
            'evaluationCount' => $count
        ];
    }

    /**
     * Export report to Excel
     *
     * @param string $type Report type (business_unit, agent, activity)
     * @return void
     */
    public function export_excel($type) {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        // Include export helper
        require_once dirname(__DIR__) . '/Helpers/export_helper.php';

        // Get report data based on type
        $data = [];
        $filename = 'report_' . date('Y-m-d') . '.xlsx';

        switch ($type) {
            case 'business_unit':
                $businessUnitId = isset($_GET['business_unit_id']) ? $_GET['business_unit_id'] : null;
                $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
                $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

                if ($businessUnitId) {
                    $data = $this->getBusinessUnitReportData($businessUnitId, $startDate, $endDate);
                    $filename = 'business_unit_report_' . $businessUnitId . '_' . date('Y-m-d') . '.xlsx';
                }
                break;

            case 'agent':
                $agentId = isset($_GET['agent_id']) ? $_GET['agent_id'] : null;
                $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
                $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

                if ($agentId) {
                    $data = $this->getAgentReportData($agentId, $startDate, $endDate);
                    $filename = 'agent_report_' . $agentId . '_' . date('Y-m-d') . '.xlsx';
                }
                break;

            case 'activity':
                $activityId = isset($_GET['activity_id']) ? $_GET['activity_id'] : null;
                $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
                $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

                if ($activityId) {
                    $data = $this->getActivityReportData($activityId, $startDate, $endDate);
                    $filename = 'activity_report_' . $activityId . '_' . date('Y-m-d') . '.xlsx';
                }
                break;
        }

        // Generate Excel
        if (!empty($data)) {
            export_to_excel($filename, $data, $type);
        }

        // Redirect back if no data
        redirect(base_url('report/' . $type));
    }

    /**
     * Export report to PDF
     *
     * @param string $type Report type (business_unit, agent, activity)
     * @return void
     */
    public function export_pdf($type) {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        // Include export helper
        require_once dirname(__DIR__) . '/Helpers/export_helper.php';

        // Get report data based on type
        $data = [];
        $filename = 'report_' . date('Y-m-d') . '.pdf';

        switch ($type) {
            case 'business_unit':
                $businessUnitId = isset($_GET['business_unit_id']) ? $_GET['business_unit_id'] : null;
                $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
                $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

                if ($businessUnitId) {
                    $data = $this->getBusinessUnitReportData($businessUnitId, $startDate, $endDate);
                    $filename = 'business_unit_report_' . $businessUnitId . '_' . date('Y-m-d') . '.pdf';
                }
                break;

            case 'agent':
                $agentId = isset($_GET['agent_id']) ? $_GET['agent_id'] : null;
                $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
                $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

                if ($agentId) {
                    $data = $this->getAgentReportData($agentId, $startDate, $endDate);
                    $filename = 'agent_report_' . $agentId . '_' . date('Y-m-d') . '.pdf';
                }
                break;

            case 'activity':
                $activityId = isset($_GET['activity_id']) ? $_GET['activity_id'] : null;
                $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
                $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

                if ($activityId) {
                    $data = $this->getActivityReportData($activityId, $startDate, $endDate);
                    $filename = 'activity_report_' . $activityId . '_' . date('Y-m-d') . '.pdf';
                }
                break;
        }

        // Generate PDF
        if (!empty($data)) {
            export_to_pdf($filename, $data, $type);
        }

        // Redirect back if no data
        redirect(base_url('report/' . $type));
    }

    /**
     * Export report to CSV
     *
     * @param string $type Report type (business_unit, agent, activity)
     * @return void
     */
    public function export_csv($type) {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        // Get report data based on type
        $data = [];
        $filename = 'report_' . date('Y-m-d') . '.csv';

        switch ($type) {
            case 'business_unit':
                $businessUnitId = isset($_GET['business_unit_id']) ? $_GET['business_unit_id'] : null;
                $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
                $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

                if ($businessUnitId) {
                    $data = $this->getBusinessUnitReportData($businessUnitId, $startDate, $endDate);
                    $filename = 'business_unit_report_' . $businessUnitId . '_' . date('Y-m-d') . '.csv';
                }
                break;

            case 'agent':
                $agentId = isset($_GET['agent_id']) ? $_GET['agent_id'] : null;
                $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
                $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

                if ($agentId) {
                    $data = $this->getAgentReportData($agentId, $startDate, $endDate);
                    $filename = 'agent_report_' . $agentId . '_' . date('Y-m-d') . '.csv';
                }
                break;

            case 'activity':
                $activityId = isset($_GET['activity_id']) ? $_GET['activity_id'] : null;
                $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
                $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

                if ($activityId) {
                    $data = $this->getActivityReportData($activityId, $startDate, $endDate);
                    $filename = 'activity_report_' . $activityId . '_' . date('Y-m-d') . '.csv';
                }
                break;
        }

        // Generate CSV
        if (!empty($data)) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');

            // Output CSV based on report type
            switch ($type) {
                case 'business_unit':
                    // Header row
                    fputcsv($output, ['Business Unit', 'Evaluation Date', 'Agent', 'Activity', 'Score']);

                    // Data rows
                    foreach ($data['evaluations'] as $eval) {
                        fputcsv($output, [
                            $data['businessUnit']['name'],
                            $eval['evaluation_date'],
                            $eval['agent_name'],
                            $eval['activity_name'],
                            $eval['score_total']
                        ]);
                    }
                    break;

                case 'agent':
                    // Header row
                    fputcsv($output, ['Agent', 'Evaluation Date', 'Activity', 'Score']);

                    // Data rows
                    foreach ($data['evaluations'] as $eval) {
                        fputcsv($output, [
                            $data['agent']['name'],
                            $eval['evaluation_date'],
                            $eval['activity_name'],
                            $eval['score_total']
                        ]);
                    }
                    break;

                case 'activity':
                    // Header row
                    fputcsv($output, ['Activity', 'Evaluation Date', 'Agent', 'Score']);

                    // Data rows
                    foreach ($data['evaluations'] as $eval) {
                        fputcsv($output, [
                            $data['activity']['name'],
                            $eval['evaluation_date'],
                            $eval['agent_name'],
                            $eval['score_total']
                        ]);
                    }
                    break;
            }

            fclose($output);
            exit;
        }

        // Redirect back if no data
        redirect(base_url('report/' . $type));
    }
}
