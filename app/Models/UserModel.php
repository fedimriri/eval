<?php
/**
 * User Model
 */
class UserModel extends Model {
    /**
     * Constructor - Set the table name
     */
    public function __construct() {
        parent::__construct();
        $this->table = 'users';
    }

    /**
     * Get user by email
     *
     * @param string $email The email to search for
     * @return object The user
     */
    public function getByEmail($email) {
        return $this->getSingleBy('email', $email);
    }

    /**
     * Validate user credentials
     *
     * @param string $email The email
     * @param string $password The password
     * @return object|bool The user if valid, false otherwise
     */
    public function validateCredentials($email, $password) {
        $user = $this->getByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    /**
     * Get users by role
     *
     * @param string $role The role to search for
     * @return array The users with the specified role
     */
    public function getByRole($role) {
        return $this->getBy('role', $role);
    }

    /**
     * Update last active timestamp
     *
     * @param int $userId The user ID
     * @return bool True on success, false on failure
     */
    public function updateLastActive($userId) {
        return $this->update($userId, [
            'last_active' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get users assigned to a specific business unit
     *
     * @param int $buId The business unit ID
     * @return array The users assigned to the business unit
     */
    public function getUsersByBusinessUnit($buId) {
        $this->db->query("
            SELECT u.*
            FROM {$this->table} u
            JOIN user_bu ub ON u.id = ub.id_user
            WHERE ub.id_bu = :bu_id
        ");
        $this->db->bind(':bu_id', $buId);
        return $this->db->resultSet();
    }
}
