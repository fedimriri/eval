<?php
/**
 * Activity Model
 */
class ActivityModel extends Model {
    /**
     * Constructor - Set the table name
     */
    public function __construct() {
        parent::__construct();
        $this->table = 'activities';
    }



    /**
     * Get a record by ID
     *
     * @param int $id The ID of the record
     * @return object The record
     */
    public function getByIdWithBusinessUnitName($id) {
         $this->db->query("
            SELECT a.*, bu.name as business_unit_name
            FROM {$this->table} a
            JOIN business_units bu ON a.business_unit_id = bu.id
            WHERE a.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }



    /**
     * Get activity by name
     *
     * @param string $name The name to search for
     * @return object The activity
     */
    public function getByName($name) {
        return $this->getSingleBy('name', $name);
    }

    /**
     * Get activities by business unit ID
     *
     * @param int $businessUnitId The business unit ID
     * @return array The activities
     */
    public function getByBusinessUnitId($businessUnitId) {
        return $this->getBy('business_unit_id', $businessUnitId);
    }

    /**
     * Get all activities with business unit name
     *
     * @return array The activities with business unit name
     */
    public function getAllWithBusinessUnitName() {
        $this->db->query("
            SELECT a.*, bu.name as business_unit_name
            FROM {$this->table} a
            JOIN business_units bu ON a.business_unit_id = bu.id
        ");
        return $this->db->resultSet();
    }

    /**
     * Get activities by business unit IDs
     *
     * @param array $businessUnitIds The business unit IDs
     * @return array The activities
     */
    public function getByBusinessUnitIds($businessUnitIds) {
        if (empty($businessUnitIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($businessUnitIds), '?'));
        
        $this->db->query("
            SELECT a.*, bu.name as business_unit_name
            FROM {$this->table} a
            JOIN business_units bu ON a.business_unit_id = bu.id
            WHERE a.business_unit_id IN ({$placeholders})
        ");
        
        foreach ($businessUnitIds as $i => $id) {
            $this->db->bind($i + 1, $id);
        }
        
        return $this->db->resultSet();
    }

    /**
     * Get activities accessible to a manager
     *
     * @param int $managerId The manager ID
     * @return array The activities
     */
    public function getAccessibleToManager($managerId) {
        $this->db->query("
            SELECT a.*, bu.name as business_unit_name
            FROM {$this->table} a
            JOIN business_units bu ON a.business_unit_id = bu.id
            JOIN manager_business_units mbu ON bu.id = mbu.business_unit_id
            WHERE mbu.manager_id = :manager_id
        ");
        $this->db->bind(':manager_id', $managerId);
        return $this->db->resultSet();
    }
}
