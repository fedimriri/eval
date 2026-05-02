<?php
/**
 * Business Unit Model
 */
class BusinessUnitModel extends Model {
    /**
     * Constructor - Set the table name
     */
    public function __construct() {
        parent::__construct();
        $this->table = 'business_units';
    }

    /**
     * Get business unit by name
     *
     * @param string $name The name to search for
     * @return object The business unit
     */
    public function getByName($name) {
        return $this->getSingleBy('name', $name);
    }

    /**
     * Get all business units with manager count
     *
     * @return array The business units with manager count
     */
    public function getAllWithManagerCount() {
        $this->db->query("
            SELECT bu.*, COUNT(mbu.manager_id) as manager_count
            FROM {$this->table} bu
            LEFT JOIN manager_business_units mbu ON bu.id = mbu.business_unit_id
            GROUP BY bu.id
        ");
        return $this->db->resultSet();
    }

    /**
     * Get business units assigned to a manager
     *
     * @param int $managerId The manager ID
     * @return array The business units
     */
    public function getByManagerId($managerId) {
        $this->db->query("
            SELECT bu.*
            FROM {$this->table} bu
            JOIN manager_business_units mbu ON bu.id = mbu.business_unit_id
            WHERE mbu.manager_id = :manager_id
        ");
        $this->db->bind(':manager_id', $managerId);
        return $this->db->resultSet();
    }

    /**
     * Get business units not assigned to a manager
     *
     * @param int $managerId The manager ID
     * @return array The business units
     */
    public function getNotAssignedToManager($managerId) {
        $this->db->query("
            SELECT bu.*
            FROM {$this->table} bu
            WHERE bu.id NOT IN (
                SELECT business_unit_id
                FROM manager_business_units
                WHERE manager_id = :manager_id
            )
        ");
        $this->db->bind(':manager_id', $managerId);
        return $this->db->resultSet();
    }
}
