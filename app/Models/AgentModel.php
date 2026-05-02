<?php
/**
 * Agent Model
 */
class AgentModel extends Model {
    /**
     * Constructor - Set the table name
     */
    public function __construct() {
        parent::__construct();
        $this->table = 'agents';
    }

    /**
     * Get agent by email
     *
     * @param string $email The email to search for
     * @return object The agent
     */
    public function getByEmail($email) {
        return $this->getSingleBy('email', $email);
    }

    /**
     * Get agents by activity ID
     *
     * @param int $activityId The activity ID
     * @return array The agents
     */
    public function getByActivityId($activityId) {
        return $this->getBy('activity_id', $activityId);
    }

    /**
     * Get all agents with activity and business unit information
     *
     * @return array The agents with activity and business unit information
     */
    public function getAllWithDetails() {
        $this->db->query("
            SELECT a.*, act.name as activity_name, bu.name as business_unit_name
            FROM {$this->table} a
            JOIN activities act ON a.activity_id = act.id
            JOIN business_units bu ON act.business_unit_id = bu.id
        ");
        return $this->db->resultSet();
    }

    /**
     * Get agents by business unit ID
     *
     * @param int $businessUnitId The business unit ID
     * @return array The agents
     */
    public function getByBusinessUnitId($businessUnitId) {
        $this->db->query("
            SELECT a.*, act.name as activity_name
            FROM {$this->table} a
            JOIN activities act ON a.activity_id = act.id
            WHERE act.business_unit_id = :business_unit_id
        ");
        $this->db->bind(':business_unit_id', $businessUnitId);
        return $this->db->resultSet();
    }

    /**
     * Get agents accessible to a manager
     *
     * @param int $managerId The manager ID
     * @return array The agents
     */
    public function getAccessibleToManager($managerId) {
        $this->db->query("
            SELECT a.*, act.name as activity_name, bu.name as business_unit_name
            FROM {$this->table} a
            JOIN activities act ON a.activity_id = act.id
            JOIN business_units bu ON act.business_unit_id = bu.id
            JOIN manager_business_units mbu ON bu.id = mbu.business_unit_id
            WHERE mbu.manager_id = :manager_id
        ");
        $this->db->bind(':manager_id', $managerId);
        return $this->db->resultSet();
    }
}
