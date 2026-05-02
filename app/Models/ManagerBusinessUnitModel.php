<?php
/**
 * Manager Business Unit Model
 */
class ManagerBusinessUnitModel extends Model {
    /**
     * Constructor - Set the table name
     */
    public function __construct() {
        parent::__construct();
        $this->table = 'manager_business_units';
    }

    /**
     * Get by manager ID and business unit ID
     *
     * @param int $managerId The manager ID
     * @param int $businessUnitId The business unit ID
     * @return object The manager business unit
     */
    public function getByManagerAndBusinessUnit($managerId, $businessUnitId) {
        $this->db->query("
            SELECT *
            FROM {$this->table}
            WHERE manager_id = :manager_id AND business_unit_id = :business_unit_id
        ");
        $this->db->bind(':manager_id', $managerId);
        $this->db->bind(':business_unit_id', $businessUnitId);
        return $this->db->single();
    }

    /**
     * Get by manager ID
     *
     * @param int $managerId The manager ID
     * @return array The manager business units
     */
    public function getByManagerId($managerId) {
        return $this->getBy('manager_id', $managerId);
    }

    /**
     * Get by business unit ID
     *
     * @param int $businessUnitId The business unit ID
     * @return array The manager business units
     */
    public function getByBusinessUnitId($businessUnitId) {
        return $this->getBy('business_unit_id', $businessUnitId);
    }

    /**
     * Assign manager to business unit
     *
     * @param int $managerId The manager ID
     * @param int $businessUnitId The business unit ID
     * @return bool True on success, false on failure
     */
    public function assign($managerId, $businessUnitId) {
        // Check if already assigned
        if ($this->getByManagerAndBusinessUnit($managerId, $businessUnitId)) {
            return true;
        }
        
        // Assign
        return $this->create([
            'manager_id' => $managerId,
            'business_unit_id' => $businessUnitId
        ]);
    }

    /**
     * Unassign manager from business unit
     *
     * @param int $managerId The manager ID
     * @param int $businessUnitId The business unit ID
     * @return bool True on success, false on failure
     */
    public function unassign($managerId, $businessUnitId) {
        $this->db->query("
            DELETE FROM {$this->table}
            WHERE manager_id = :manager_id AND business_unit_id = :business_unit_id
        ");
        $this->db->bind(':manager_id', $managerId);
        $this->db->bind(':business_unit_id', $businessUnitId);
        return $this->db->execute();
    }

    /**
     * Unassign manager from all business units
     *
     * @param int $managerId The manager ID
     * @return bool True on success, false on failure
     */
    public function unassignAll($managerId) {
        $this->db->query("DELETE FROM {$this->table} WHERE manager_id = :manager_id");
        $this->db->bind(':manager_id', $managerId);
        return $this->db->execute();
    }

    /**
     * Unassign all managers from business unit
     *
     * @param int $businessUnitId The business unit ID
     * @return bool True on success, false on failure
     */
    public function unassignAllFromBusinessUnit($businessUnitId) {
        $this->db->query("DELETE FROM {$this->table} WHERE business_unit_id = :business_unit_id");
        $this->db->bind(':business_unit_id', $businessUnitId);
        return $this->db->execute();
    }
}
