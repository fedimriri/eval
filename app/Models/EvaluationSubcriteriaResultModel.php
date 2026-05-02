<?php
/**
 * Evaluation Subcriteria Result Model
 */
class EvaluationSubcriteriaResultModel extends Model {
    /**
     * Constructor - Set the table name
     */
    public function __construct() {
        parent::__construct();
        $this->table = 'evaluation_subcriteria_results';
    }

    /**
     * Get results by evaluation ID
     *
     * @param int $evaluationId The evaluation ID
     * @return array The results
     */
    public function getByEvaluationId($evaluationId) {
        $this->db->query("
            SELECT esr.*, esc.name as subcriteria_name, ec.name as criteria_name, ec.id as criteria_id
            FROM {$this->table} esr
            JOIN evaluation_subcriteria esc ON esr.subcriteria_id = esc.id
            JOIN evaluation_criteria ec ON esc.criteria_id = ec.id
            WHERE esr.evaluation_id = :evaluation_id
        ");
        $this->db->bind(':evaluation_id', $evaluationId);
        return $this->db->resultSet();
    }

    /**
     * Get results by subcriteria ID
     *
     * @param int $subcriteriaId The subcriteria ID
     * @return array The results
     */
    public function getBySubcriteriaId($subcriteriaId) {
        return $this->getBy('subcriteria_id', $subcriteriaId);
    }

    /**
     * Create multiple results
     *
     * @param array $results The results to create
     * @return bool True on success, false on failure
     */
    public function createMultiple($results) {
        $this->db->beginTransaction();
        
        try {
            foreach ($results as $result) {
                $this->create($result);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Delete results by evaluation ID
     *
     * @param int $evaluationId The evaluation ID
     * @return bool True on success, false on failure
     */
    public function deleteByEvaluationId($evaluationId) {
        $this->db->query("DELETE FROM {$this->table} WHERE evaluation_id = :evaluation_id");
        $this->db->bind(':evaluation_id', $evaluationId);
        return $this->db->execute();
    }

    /**
     * Calculate score based on notation
     *
     * @param string $notation The notation (C, NC, PC, SI)
     * @param float $weight The weight of the subcriterion
     * @return float The calculated score
     */
    public function calculateScore($notation, $weight) {
        switch ($notation) {
            case 'C':
                return $weight; // Full weight
            case 'NC':
            case 'PC':
            case 'SI':
                return 0; // Zero score
            default:
                return 0;
        }
    }
}
