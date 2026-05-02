<?php
/**
 * Evaluation Criteria Model
 */
class EvaluationCriteriaModel extends Model {
    /**
     * Constructor - Set the table name
     */
    public function __construct() {
        parent::__construct();
        $this->table = 'evaluation_criteria';
    }

    /**
     * Get criteria by template ID
     *
     * @param int $templateId The template ID
     * @return array The criteria
     */
    public function getByTemplateId($templateId) {
        $this->db->query("
            SELECT *
            FROM {$this->table}
            WHERE template_id = :template_id
            ORDER BY `order` ASC
        ");
        $this->db->bind(':template_id', $templateId);
        return $this->db->resultSet();
    }

    /**
     * Create multiple criteria
     *
     * @param array $criteria The criteria to create
     * @return bool True on success, false on failure
     */
    public function createMultiple($criteria) {
        $this->db->beginTransaction();
        
        try {
            foreach ($criteria as $criterion) {
                $this->create($criterion);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Update criterion order
     *
     * @param int $id The criterion ID
     * @param int $order The new order
     * @return bool True on success, false on failure
     */
    public function updateOrder($id, $order) {
        return $this->update($id, ['order' => $order]);
    }

    /**
     * Delete criteria by template ID
     *
     * @param int $templateId The template ID
     * @return bool True on success, false on failure
     */
    public function deleteByTemplateId($templateId) {
        $this->db->query("DELETE FROM {$this->table} WHERE template_id = :template_id");
        $this->db->bind(':template_id', $templateId);
        return $this->db->execute();
    }
}
