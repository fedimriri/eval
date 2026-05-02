<?php
/**
 * Evaluation Template Model
 */
class EvaluationTemplateModel extends Model {
    /**
     * Constructor - Set the table name
     */
    public function __construct() {
        parent::__construct();
        $this->table = 'evaluation_templates';
    }

    /**
     * Get templates by activity ID
     *
     * @param int $activityId The activity ID
     * @return array The templates
     */
    public function getByActivityId($activityId) {
        return $this->getBy('activity_id', $activityId);
    }

    /**
     * Get active template by activity ID
     *
     * @param int $activityId The activity ID
     * @return object The active template
     */
    public function getActiveByActivityId($activityId) {
        $this->db->query("
            SELECT *
            FROM {$this->table}
            WHERE activity_id = :activity_id AND is_active = 1
            LIMIT 1
        ");
        $this->db->bind(':activity_id', $activityId);
        return $this->db->single();
    }

    /**
     * Get all templates with activity and business unit information
     *
     * @return array The templates with activity and business unit information
     */
    public function getAllWithDetails() {
        $this->db->query("
            SELECT et.*, a.name as activity_name, bu.name as business_unit_name
            FROM {$this->table} et
            JOIN activities a ON et.activity_id = a.id
            JOIN business_units bu ON a.business_unit_id = bu.id
            ORDER BY et.created_at DESC
        ");
        return $this->db->resultSet();
    }

    /**
     * Get templates accessible to a manager
     *
     * @param int $managerId The manager ID
     * @return array The templates
     */
    public function getAccessibleToManager($managerId) {
        $this->db->query("
            SELECT et.*, a.name as activity_name, bu.name as business_unit_name
            FROM {$this->table} et
            JOIN activities a ON et.activity_id = a.id
            JOIN business_units bu ON a.business_unit_id = bu.id
            JOIN manager_business_units mbu ON bu.id = mbu.business_unit_id
            WHERE mbu.manager_id = :manager_id
            ORDER BY et.created_at DESC

        ");
        $this->db->bind(':manager_id', $managerId);
        return $this->db->resultSet();
    }

    /**
     * Get template with criteria and subcriteria
     *
     * @param int $templateId The template ID
     * @return array The template with criteria and subcriteria
     */
    public function getWithCriteriaAndSubcriteria($templateId) {
        // Get template
        $template = $this->getById($templateId);

        if (!$template) {
            return null;
        }

        // Get criteria
        $this->db->query("
            SELECT *
            FROM evaluation_criteria
            WHERE template_id = :template_id
            ORDER BY `order` ASC
        ");
        $this->db->bind(':template_id', $templateId);
        $criteria = $this->db->resultSet();

        // Get subcriteria for each criterion
        foreach ($criteria as &$criterion) {
            $this->db->query("
                SELECT *
                FROM evaluation_subcriteria
                WHERE criteria_id = :criteria_id
                ORDER BY `order` ASC
            ");
            $this->db->bind(':criteria_id', $criterion['id']);
            $criterion['subcriteria'] = $this->db->resultSet();
        }

        $template['criteria'] = $criteria;

        return $template;
    }

    /**
     * Delete template with all related criteria and subcriteria
     *
     * @param int $templateId The template ID
     * @return bool True on success, false on failure
     */
    public function deleteWithRelated($templateId) {
        $this->db->beginTransaction();

        try {
            // Get all criteria for this template
            $this->db->query("
                SELECT id
                FROM evaluation_criteria
                WHERE template_id = :template_id
            ");
            $this->db->bind(':template_id', $templateId);
            $criteria = $this->db->resultSet();

            // Delete subcriteria for each criterion
            $evaluationSubcriteriaModel = new EvaluationSubcriteriaModel();
            foreach ($criteria as $criterion) {
                $result = $evaluationSubcriteriaModel->deleteByCriteriaId($criterion['id']);
                if (!$result) {
                    throw new Exception('Failed to delete subcriteria');
                }
            }

            // Delete criteria
            $evaluationCriteriaModel = new EvaluationCriteriaModel();
            $result = $evaluationCriteriaModel->deleteByTemplateId($templateId);
            if (!$result) {
                throw new Exception('Failed to delete criteria');
            }

            // Delete template
            $result = $this->delete($templateId);
            if (!$result) {
                throw new Exception('Failed to delete template');
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Check if template is used in evaluations
     *
     * @param int $templateId The template ID
     * @return bool True if template is used, false otherwise
     */
    public function isUsedInEvaluations($templateId) {
        $this->db->query("
            SELECT COUNT(*) as count
            FROM evaluations
            WHERE template_id = :template_id
        ");
        $this->db->bind(':template_id', $templateId);
        $result = $this->db->single();

        return $result['count'] > 0;
    }
}
