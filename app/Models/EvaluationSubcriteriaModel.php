<?php
/**
 * Evaluation Subcriteria Model
 */
class EvaluationSubcriteriaModel extends Model {
    /**
     * Constructor - Set the table name
     */
    public function __construct() {
        parent::__construct();
        $this->table = 'evaluation_subcriteria';
    }

    /**
     * Get subcriteria by criteria ID
     *
     * @param int $criteriaId The criteria ID
     * @return array The subcriteria
     */
    public function getByCriteriaId($criteriaId) {
        $this->db->query("
            SELECT *
            FROM {$this->table}
            WHERE criteria_id = :criteria_id
            ORDER BY `order` ASC
        ");
        $this->db->bind(':criteria_id', $criteriaId);
        return $this->db->resultSet();
    }

    /**
     * Create multiple subcriteria
     *
     * @param array $subcriteria The subcriteria to create
     * @return bool True on success, false on failure
     */
    public function createMultiple($subcriteria) {
        $this->db->beginTransaction();
        
        try {
            foreach ($subcriteria as $subcriterion) {
                $this->create($subcriterion);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Update subcriterion order
     *
     * @param int $id The subcriterion ID
     * @param int $order The new order
     * @return bool True on success, false on failure
     */
    public function updateOrder($id, $order) {
        return $this->update($id, ['order' => $order]);
    }

    /**
     * Delete subcriteria by criteria ID
     *
     * @param int $criteriaId The criteria ID
     * @return bool True on success, false on failure
     */
    public function deleteByCriteriaId($criteriaId) {
        $this->db->query("DELETE FROM {$this->table} WHERE criteria_id = :criteria_id");
        $this->db->bind(':criteria_id', $criteriaId);
        return $this->db->execute();
    }
}
