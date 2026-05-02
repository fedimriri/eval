<?php
/**
 * Evaluation Model
 */
class EvaluationModel extends Model {
    /**
     * Constructor - Set the table name
     */
    public function __construct() {
        parent::__construct();
        $this->table = 'evaluations';
    }

    /**
     * Get all evaluations with details
     *
     * @return array The evaluations
     */
    public function getAll() {
        $this->db->query("
            SELECT e.*, u.name as evaluator_name, a.name as agent_name, act.name as activity_name, bu.name as business_unit_name
            FROM {$this->table} e
            JOIN users u ON e.evaluator_id = u.id
            JOIN agents a ON e.agent_id = a.id
            JOIN activities act ON e.activity_id = act.id
            JOIN business_units bu ON act.business_unit_id = bu.id
            ORDER BY e.evaluation_date DESC
        ");
        return $this->db->resultSet();
    }

    /**
     * Get evaluations by agent ID
     *
     * @param int $agentId The agent ID
     * @return array The evaluations
     */
    public function getByAgentId($agentId) {
        $this->db->query("
            SELECT e.*, u.name as evaluator_name, a.name as agent_name, act.name as activity_name, bu.name as business_unit_name
            FROM {$this->table} e
            JOIN users u ON e.evaluator_id = u.id
            JOIN agents a ON e.agent_id = a.id
            JOIN activities act ON e.activity_id = act.id
            JOIN business_units bu ON act.business_unit_id = bu.id
            WHERE e.agent_id = :agent_id
            ORDER BY e.evaluation_date DESC
        ");
        $this->db->bind(':agent_id', $agentId);
        return $this->db->resultSet();
    }

    /**
     * Get evaluations by evaluator ID
     *
     * @param int $evaluatorId The evaluator ID
     * @return array The evaluations
     */
    public function getByEvaluatorId($evaluatorId) {
        $this->db->query("
            SELECT e.*, u.name as evaluator_name, a.name as agent_name, act.name as activity_name, bu.name as business_unit_name
            FROM {$this->table} e
            JOIN users u ON e.evaluator_id = u.id
            JOIN agents a ON e.agent_id = a.id
            JOIN activities act ON e.activity_id = act.id
            JOIN business_units bu ON act.business_unit_id = bu.id
            WHERE e.evaluator_id = :evaluator_id
            ORDER BY e.evaluation_date DESC
        ");
        $this->db->bind(':evaluator_id', $evaluatorId);
        return $this->db->resultSet();
    }

    /**
     * Get evaluations by activity ID
     *
     * @param int $activityId The activity ID
     * @return array The evaluations
     */
    public function getByActivityId($activityId) {
        $this->db->query("
            SELECT e.*, u.name as evaluator_name, a.name as agent_name, act.name as activity_name, bu.name as business_unit_name
            FROM {$this->table} e
            JOIN users u ON e.evaluator_id = u.id
            JOIN agents a ON e.agent_id = a.id
            JOIN activities act ON e.activity_id = act.id
            JOIN business_units bu ON act.business_unit_id = bu.id
            WHERE e.activity_id = :activity_id
            ORDER BY e.evaluation_date DESC
        ");
        $this->db->bind(':activity_id', $activityId);
        return $this->db->resultSet();
    }

    /**
     * Get evaluations by business unit ID
     *
     * @param int $businessUnitId The business unit ID
     * @return array The evaluations
     */
    public function getByBusinessUnitId($businessUnitId) {
        $this->db->query("
            SELECT e.*, u.name as evaluator_name, a.name as agent_name, act.name as activity_name, bu.name as business_unit_name
            FROM {$this->table} e
            JOIN users u ON e.evaluator_id = u.id
            JOIN agents a ON e.agent_id = a.id
            JOIN activities act ON e.activity_id = act.id
            JOIN business_units bu ON act.business_unit_id = bu.id
            WHERE act.business_unit_id = :business_unit_id
            ORDER BY e.evaluation_date DESC
        ");
        $this->db->bind(':business_unit_id', $businessUnitId);
        return $this->db->resultSet();
    }

    /**
     * Get evaluations accessible to a manager
     *
     * @param int $managerId The manager ID
     * @return array The evaluations
     */
    public function getAccessibleToManager($managerId) {
        $this->db->query("
            SELECT e.*, u.name as evaluator_name, a.name as agent_name, act.name as activity_name, bu.name as business_unit_name
            FROM {$this->table} e
            JOIN users u ON e.evaluator_id = u.id
            JOIN agents a ON e.agent_id = a.id
            JOIN activities act ON e.activity_id = act.id
            JOIN business_units bu ON act.business_unit_id = bu.id
            JOIN manager_business_units mbu ON bu.id = mbu.business_unit_id
            WHERE mbu.manager_id = :manager_id
            ORDER BY e.evaluation_date DESC
        ");
        $this->db->bind(':manager_id', $managerId);
        return $this->db->resultSet();
    }

    /**
     * Get evaluation with results
     *
     * @param int $evaluationId The evaluation ID
     * @return array The evaluation with results
     */
    public function getWithScores($evaluationId) {
        // Get evaluation
        $evaluation = $this->getById($evaluationId);

        if (!$evaluation) {
            return null;
        }

        // Get results
        $this->db->query("
            SELECT esr.*, esc.name as subcriteria_name, ec.name as criteria_name
            FROM evaluation_subcriteria_results esr
            JOIN evaluation_subcriteria esc ON esr.subcriteria_id = esc.id
            JOIN evaluation_criteria ec ON esc.criteria_id = ec.id
            WHERE esr.evaluation_id = :evaluation_id
        ");
        $this->db->bind(':evaluation_id', $evaluationId);
        $evaluation['scores'] = $this->db->resultSet();

        return $evaluation;
    }

    /**
     * Create evaluation with scores
     *
     * @param array $evaluation The evaluation data
     * @param array $scores The scores data
     * @return int|bool The evaluation ID on success, false on failure
     */
    public function createWithScores($evaluation, $scores) {
        $this->db->beginTransaction();

        try {
            // Create evaluation
            $evaluationId = $this->create($evaluation);

            if (!$evaluationId) {
                throw new Exception('Failed to create evaluation');
            }

            require_once 'evaluationScoreModel.php';

            // Create scores
            $evaluationScoreModel = new EvaluationScoreModel();

            foreach ($scores as $score) {
                $score['evaluation_id'] = $evaluationId;
                $result = $evaluationScoreModel->create($score);

                if (!$result) {
                    throw new Exception('Failed to create score');
                }
            }

            $this->db->commit();
            return $evaluationId;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Update evaluation with scores
     *
     * @param int $evaluationId The evaluation ID
     * @param array $evaluation The evaluation data
     * @param array $scores The scores data
     * @return bool True on success, false on failure
     */
    public function updateWithScores($evaluationId, $evaluation, $scores) {
        $this->db->beginTransaction();

        try {
            // Update evaluation
            $result = $this->update($evaluationId, $evaluation);

            if (!$result) {
                throw new Exception('Failed to update evaluation');
            }
            require_once 'evaluationScoreModel.php';

            // Delete existing scores
            $evaluationScoreModel = new EvaluationScoreModel();
            $result = $evaluationScoreModel->deleteByEvaluationId($evaluationId);

            if (!$result) {
                throw new Exception('Failed to delete existing scores');
            }

            // Create new scores
            foreach ($scores as $score) {
                $score['evaluation_id'] = $evaluationId;
                $result = $evaluationScoreModel->create($score);

                if (!$result) {
                    throw new Exception('Failed to create score');
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Delete evaluation with results
     *
     * @param int $evaluationId The evaluation ID
     * @return bool True on success, false on failure
     */
    public function deleteWithScores($evaluationId) {
        $this->db->beginTransaction();

        try {
            // Delete results
            $evaluationSubcriteriaResultModel = new EvaluationSubcriteriaResultModel();
            $result = $evaluationSubcriteriaResultModel->deleteByEvaluationId($evaluationId);

            if (!$result) {
                throw new Exception('Failed to delete results');
            }

            // Delete evaluation
            $result = $this->delete($evaluationId);

            if (!$result) {
                throw new Exception('Failed to delete evaluation');
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }



    /**
     * Get evaluations by business unit ID and date range
     *
     * @param int $businessUnitId The business unit ID
     * @param string|null $startDate Start date (Y-m-d)
     * @param string|null $endDate End date (Y-m-d)
     * @return array The evaluations
     */
    public function getByBusinessUnitIdAndDateRange($businessUnitId, $startDate = null, $endDate = null) {
        $sql = "
            SELECT e.*, u.name as evaluator_name, a.name as agent_name, act.name as activity_name, bu.name as business_unit_name
            FROM {$this->table} e
            JOIN users u ON e.evaluator_id = u.id
            JOIN agents a ON e.agent_id = a.id
            JOIN activities act ON e.activity_id = act.id
            JOIN business_units bu ON act.business_unit_id = bu.id
            WHERE act.business_unit_id = :business_unit_id
        ";

        if ($startDate && $endDate) {
            $sql .= " AND e.evaluation_date >= :start_date AND e.evaluation_date <= CONCAT(:end_date, ' 23:59:59')";
        }

        $sql .= " ORDER BY e.evaluation_date DESC";

        $this->db->query($sql);
        $this->db->bind(':business_unit_id', $businessUnitId);

        if ($startDate && $endDate) {
            $this->db->bind(':start_date', "{$startDate} 00:00:00");
            $this->db->bind(':end_date', $endDate);
        }

        return $this->db->resultSet();
    }

    /**
     * Get count of evaluations
     *
     * @return int The count
     */
    public function getCount() {
        $this->db->query("SELECT COUNT(*) as count FROM {$this->table}");
        $result = $this->db->single();
        return $result['count'];
    }

    /**
     * Get count of evaluations for a manager
     *
     * @param int $managerId The manager ID
     * @return int The count
     */
    public function getCountForManager($managerId) {
        $this->db->query("
            SELECT COUNT(*) as count
            FROM {$this->table} e
            JOIN activities act ON e.activity_id = act.id
            JOIN business_units bu ON act.business_unit_id = bu.id
            JOIN manager_business_units mbu ON bu.id = mbu.business_unit_id
            WHERE mbu.manager_id = :manager_id
        ");
        $this->db->bind(':manager_id', $managerId);
        $result = $this->db->single();
        return $result['count'];
    }

    /**
     * Get average score of evaluations
     *
     * @param array $user Current user
     * @return float The average score
     */
    public function getAverageScore($user) {
        if ($user['role'] === 'admin') {
            $this->db->query("SELECT AVG(score_total) as average FROM {$this->table}");
        } else {
            $this->db->query("
                SELECT AVG(e.score_total) as average
                FROM {$this->table} e
                JOIN activities act ON e.activity_id = act.id
                JOIN business_units bu ON act.business_unit_id = bu.id
                JOIN manager_business_units mbu ON bu.id = mbu.business_unit_id
                WHERE mbu.manager_id = :manager_id
            ");
            $this->db->bind(':manager_id', $user['id']);
        }

        $result = $this->db->single();
        return $result['average'] ? round($result['average'], 2) : 0;
    }

    /**
     * Get recent evaluations
     *
     * @param int $limit The limit
     * @param array $user Current user
     * @return array The evaluations
     */
    public function getRecent($limit, $user) {
        if ($user['role'] === 'admin') {
            $this->db->query("
                SELECT e.*, u.name as evaluator_name, a.name as agent_name, act.name as activity_name, bu.name as business_unit_name
                FROM {$this->table} e
                JOIN users u ON e.evaluator_id = u.id
                JOIN agents a ON e.agent_id = a.id
                JOIN activities act ON e.activity_id = act.id
                JOIN business_units bu ON act.business_unit_id = bu.id
                ORDER BY e.created_at DESC
                LIMIT :limit
            ");
            $this->db->bind(':limit', $limit);
        } else {
            $this->db->query("
                SELECT e.*, u.name as evaluator_name, a.name as agent_name, act.name as activity_name, bu.name as business_unit_name
                FROM {$this->table} e
                JOIN users u ON e.evaluator_id = u.id
                JOIN agents a ON e.agent_id = a.id
                JOIN activities act ON e.activity_id = act.id
                JOIN business_units bu ON act.business_unit_id = bu.id
                JOIN manager_business_units mbu ON bu.id = mbu.business_unit_id
                WHERE mbu.manager_id = :manager_id
                ORDER BY e.created_at DESC
                LIMIT :limit
            ");
            $this->db->bind(':manager_id', $user['id']);
            $this->db->bind(':limit', $limit);
        }

        return $this->db->resultSet();
    }

    /**
     * Begin a transaction
     *
     * @return void
     */
    public function beginTransaction() {
        $this->db->beginTransaction();
    }

    /**
     * Commit a transaction
     *
     * @return void
     */
    public function commit() {
        $this->db->commit();
    }

    /**
     * Roll back a transaction
     *
     * @return void
     */
    public function rollBack() {
        $this->db->rollBack();
    }

    /**
     * Get evaluations with filters
     *
     * @param array $businessUnits Business unit IDs
     * @param array $activities Activity IDs
     * @param array $agents Agent IDs
     * @param string|null $startDate Start date (Y-m-d)
     * @param string|null $endDate End date (Y-m-d)
     * @return array The evaluations
     */
    public function getFiltered($businessUnits = [], $activities = [], $agents = [], $startDate = null, $endDate = null) {
        $sql = "
            SELECT e.*, u.name as evaluator_name, a.name as agent_name, act.name as activity_name, bu.name as business_unit_name
            FROM {$this->table} e
            JOIN users u ON e.evaluator_id = u.id
            JOIN agents a ON e.agent_id = a.id
            JOIN activities act ON e.activity_id = act.id
            JOIN business_units bu ON act.business_unit_id = bu.id
            WHERE 1=1
        ";

        $params = [];

        // Add business unit filter
        if (!empty($businessUnits)) {
            $placeholders = implode(',', array_fill(0, count($businessUnits), '?'));
            $sql .= " AND bu.id IN ({$placeholders})";
            $params = array_merge($params, $businessUnits);
        }

        // Add activity filter
        if (!empty($activities)) {
            $placeholders = implode(',', array_fill(0, count($activities), '?'));
            $sql .= " AND act.id IN ({$placeholders})";
            $params = array_merge($params, $activities);
        }

        // Add agent filter
        if (!empty($agents)) {
            $placeholders = implode(',', array_fill(0, count($agents), '?'));
            $sql .= " AND a.id IN ({$placeholders})";
            $params = array_merge($params, $agents);
        }


        // Add date range filter
        if ($startDate && $endDate) {
            // Since evaluation_date is now datetime, convert date strings to datetime range
            $sql .= " AND e.evaluation_date >= ? AND e.evaluation_date <= CONCAT(?, ' 23:59:59')";
            $params[] = "{$startDate} 00:00:00";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY e.evaluation_date DESC";

        $this->db->query($sql);

        // Bind parameters
        foreach ($params as $i => $param) {
            $this->db->bind($i + 1, $param);
        }

        return $this->db->resultSet();
    }

    /**
     * Get evaluations with filters for a manager
     *
     * @param int $managerId The manager ID
     * @param array $businessUnits Business unit IDs
     * @param array $activities Activity IDs
     * @param array $agents Agent IDs
     * @param string|null $startDate Start date (Y-m-d)
     * @param string|null $endDate End date (Y-m-d)
     * @return array The evaluations
     */
    public function getFilteredForManager($managerId, $businessUnits = [], $activities = [], $agents = [], $startDate = null, $endDate = null) {
        $sql = "
            SELECT e.*, u.name as evaluator_name, a.name as agent_name, act.name as activity_name, bu.name as business_unit_name
            FROM {$this->table} e
            JOIN users u ON e.evaluator_id = u.id
            JOIN agents a ON e.agent_id = a.id
            JOIN activities act ON e.activity_id = act.id
            JOIN business_units bu ON act.business_unit_id = bu.id
            JOIN manager_business_units mbu ON bu.id = mbu.business_unit_id
            WHERE mbu.manager_id = ?
        ";

        $params = [$managerId];

        // Add business unit filter
        if (!empty($businessUnits)) {
            $placeholders = implode(',', array_fill(0, count($businessUnits), '?'));
            $sql .= " AND bu.id IN ({$placeholders})";
            $params = array_merge($params, $businessUnits);
        }

        // Add activity filter
        if (!empty($activities)) {
            $placeholders = implode(',', array_fill(0, count($activities), '?'));
            $sql .= " AND act.id IN ({$placeholders})";
            $params = array_merge($params, $activities);
        }

        // Add agent filter
        if (!empty($agents)) {
            $placeholders = implode(',', array_fill(0, count($agents), '?'));
            $sql .= " AND a.id IN ({$placeholders})";
            $params = array_merge($params, $agents);
        }

        // Add date range filter
        if ($startDate && $endDate) {
            // Since evaluation_date is now datetime, convert date strings to datetime range
            $sql .= " AND e.evaluation_date >= ? AND e.evaluation_date <= CONCAT(?, ' 23:59:59')";
            $params[] = "{$startDate} 00:00:00";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY e.evaluation_date DESC";

        $this->db->query($sql);

        // Bind parameters
        foreach ($params as $i => $param) {
            $this->db->bind($i + 1, $param);
        }

        return $this->db->resultSet();
    }

    /**
     * Get evaluations by agent ID and date range
     *
     * @param int $agentId The agent ID
     * @param string|null $startDate Start date (Y-m-d)
     * @param string|null $endDate End date (Y-m-d)
     * @return array The evaluations
     */
    public function getByAgentIdAndDateRange($agentId, $startDate = null, $endDate = null) {
        $sql = "
            SELECT e.*, u.name as evaluator_name, a.name as agent_name, act.name as activity_name, bu.name as business_unit_name
            FROM {$this->table} e
            JOIN users u ON e.evaluator_id = u.id
            JOIN agents a ON e.agent_id = a.id
            JOIN activities act ON e.activity_id = act.id
            JOIN business_units bu ON act.business_unit_id = bu.id
            WHERE e.agent_id = :agent_id
        ";

        if ($startDate && $endDate) {
            $sql .= " AND e.evaluation_date >= :start_date AND e.evaluation_date <= CONCAT(:end_date, ' 23:59:59')";
        }

        $sql .= " ORDER BY e.evaluation_date DESC";

        $this->db->query($sql);
        $this->db->bind(':agent_id', $agentId);

        if ($startDate && $endDate) {
            $this->db->bind(':start_date', "{$startDate} 00:00:00");
            $this->db->bind(':end_date', $endDate);
        }

        return $this->db->resultSet();
    }

    /**
     * Get evaluations by activity ID and date range
     *
     * @param int $activityId The activity ID
     * @param string|null $startDate Start date (Y-m-d)
     * @param string|null $endDate End date (Y-m-d)
     * @return array The evaluations
     */
    public function getByActivityIdAndDateRange($activityId, $startDate = null, $endDate = null) {
        $sql = "
            SELECT e.*, u.name as evaluator_name, a.name as agent_name, act.name as activity_name, bu.name as business_unit_name
            FROM {$this->table} e
            JOIN users u ON e.evaluator_id = u.id
            JOIN agents a ON e.agent_id = a.id
            JOIN activities act ON e.activity_id = act.id
            JOIN business_units bu ON act.business_unit_id = bu.id
            WHERE e.activity_id = :activity_id
        ";
        if ($startDate && $endDate) {
            $sql .= " AND e.evaluation_date >= :start_date AND e.evaluation_date <= CONCAT(:end_date, ' 23:59:59')";
        }

        $sql .= " ORDER BY e.evaluation_date DESC";

        $this->db->query($sql);
        $this->db->bind(':activity_id', $activityId);

        if ($startDate && $endDate) {
            $this->db->bind(':start_date', "{$startDate} 00:00:00");
            $this->db->bind(':end_date', $endDate);
        }

        return $this->db->resultSet();
    }





}
