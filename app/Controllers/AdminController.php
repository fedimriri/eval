<?php
/**
 * Admin Controller
 */
class AdminController extends Controller {
    private $userModel;
    private $businessUnitModel;
    private $managerBusinessUnitModel;
    
    /**
     * Constructor - Load models
     */
    public function __construct() {
        $this->userModel = $this->model('UserModel');
        $this->businessUnitModel = $this->model('BusinessUnitModel');
        $this->managerBusinessUnitModel = $this->model('ManagerBusinessUnitModel');
    }
    
    /**
     * Index method - Display admin dashboard
     * 
     * @return void
     */
    public function index() {
        // Check if user is authenticated and is admin
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }
        
        $user = current_user();
        
        if ($user['role'] !== 'admin') {
            flash('error', 'You do not have permission to access the admin dashboard');
            redirect(base_url('home/index'));
        }
        
        // Load the view
        $data = [
            'title' => 'Admin Dashboard',
            'user' => $user
        ];
        
        $this->view('admin/index', $data);
    }
    
    /**
     * Users method - Display and manage users
     * 
     * @return void
     */
    public function users() {
        // Check if user is authenticated and is admin
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }
        
        $user = current_user();
        
        if ($user['role'] !== 'admin') {
            flash('error', 'You do not have permission to manage users');
            redirect(base_url('home/index'));
        }
        
        // Get users
        $users = $this->userModel->getAll();
        
        // Load the view
        $data = [
            'title' => 'Manage Users',
            'users' => $users,
            'user' => $user
        ];
        
        $this->view('admin/users', $data);
    }
    
    /**
     * Create User method - Display the create form and process form submission
     * 
     * @return void
     */
    public function create_user() {
        // Check if user is authenticated and is admin
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }
        
        $user = current_user();
        
        if ($user['role'] !== 'admin') {
            flash('error', 'You do not have permission to create users');
            redirect(base_url('home/index'));
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate form data
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirmPassword = trim($_POST['confirm_password']);
            $role = trim($_POST['role']);
            
            // Check if name is empty
            if (empty($name)) {
                flash('error', 'Name is required');
                
                $data = [
                    'title' => 'Create User',
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'user' => $user
                ];
                
                $this->view('admin/create_user', $data);
                return;
            }
            
            // Check if email is empty
            if (empty($email)) {
                flash('error', 'Email is required');
                
                $data = [
                    'title' => 'Create User',
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'user' => $user
                ];
                
                $this->view('admin/create_user', $data);
                return;
            }
            
            // Check if email is valid
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash('error', 'Invalid email format');
                
                $data = [
                    'title' => 'Create User',
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'user' => $user
                ];
                
                $this->view('admin/create_user', $data);
                return;
            }
            
            // Check if email already exists
            if ($this->userModel->getByEmail($email)) {
                flash('error', 'Email already exists');
                
                $data = [
                    'title' => 'Create User',
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'user' => $user
                ];
                
                $this->view('admin/create_user', $data);
                return;
            }
            
            // Check if password is empty
            if (empty($password)) {
                flash('error', 'Password is required');
                
                $data = [
                    'title' => 'Create User',
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'user' => $user
                ];
                
                $this->view('admin/create_user', $data);
                return;
            }
            
            // Check if password is at least 6 characters
            if (strlen($password) < 6) {
                flash('error', 'Password must be at least 6 characters');
                
                $data = [
                    'title' => 'Create User',
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'user' => $user
                ];
                
                $this->view('admin/create_user', $data);
                return;
            }
            
            // Check if passwords match
            if ($password !== $confirmPassword) {
                flash('error', 'Passwords do not match');
                
                $data = [
                    'title' => 'Create User',
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'user' => $user
                ];
                
                $this->view('admin/create_user', $data);
                return;
            }
            
            // Check if role is valid
            if ($role !== 'admin' && $role !== 'manager') {
                flash('error', 'Invalid role');
                
                $data = [
                    'title' => 'Create User',
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'user' => $user
                ];
                
                $this->view('admin/create_user', $data);
                return;
            }
            
            // Hash password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Create user
            $userId = $this->userModel->create([
                'name' => $name,
                'email' => $email,
                'password' => $passwordHash,
                'role' => $role,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($userId) {
                // Set success message
                flash('success', 'User created successfully');
                
                // Redirect to users page
                redirect(base_url('admin/users'));
            } else {
                // Set error message
                flash('error', 'Failed to create user');
                
                $data = [
                    'title' => 'Create User',
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'user' => $user
                ];
                
                $this->view('admin/create_user', $data);
            }
        } else {
            // Load the create view
            $data = [
                'title' => 'Create User',
                'name' => '',
                'email' => '',
                'role' => 'manager',
                'user' => $user
            ];
            
            $this->view('admin/create_user', $data);
        }
    }
    
    /**
     * Edit User method - Display the edit form and process form submission
     * 
     * @param int $id The user ID
     * @return void
     */
    public function edit_user($id) {
        // Check if user is authenticated and is admin
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }
        
        $user = current_user();
        
        if ($user['role'] !== 'admin') {
            flash('error', 'You do not have permission to edit users');
            redirect(base_url('home/index'));
        }
        
        // Get user to edit
        $editUser = $this->userModel->getById($id);
        
        if (!$editUser) {
            flash('error', 'User not found');
            redirect(base_url('admin/users'));
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate form data
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirmPassword = trim($_POST['confirm_password']);
            $role = trim($_POST['role']);
            
            // Check if name is empty
            if (empty($name)) {
                flash('error', 'Name is required');
                
                $data = [
                    'title' => 'Edit User',
                    'editUser' => $editUser,
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'user' => $user
                ];
                
                $this->view('admin/edit_user', $data);
                return;
            }
            
            // Check if email is empty
            if (empty($email)) {
                flash('error', 'Email is required');
                
                $data = [
                    'title' => 'Edit User',
                    'editUser' => $editUser,
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'user' => $user
                ];
                
                $this->view('admin/edit_user', $data);
                return;
            }
            
            // Check if email is valid
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash('error', 'Invalid email format');
                
                $data = [
                    'title' => 'Edit User',
                    'editUser' => $editUser,
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'user' => $user
                ];
                
                $this->view('admin/edit_user', $data);
                return;
            }
            
            // Check if email already exists (excluding current user)
            $existingUser = $this->userModel->getByEmail($email);
            if ($existingUser && $existingUser['id'] != $id) {
                flash('error', 'Email already exists');
                
                $data = [
                    'title' => 'Edit User',
                    'editUser' => $editUser,
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'user' => $user
                ];
                
                $this->view('admin/edit_user', $data);
                return;
            }
            
            // Check if role is valid
            if ($role !== 'admin' && $role !== 'manager') {
                flash('error', 'Invalid role');
                
                $data = [
                    'title' => 'Edit User',
                    'editUser' => $editUser,
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'user' => $user
                ];
                
                $this->view('admin/edit_user', $data);
                return;
            }
            
            // Prepare update data
            $updateData = [
                'name' => $name,
                'email' => $email,
                'role' => $role
            ];
            
            // Update password if provided
            if (!empty($password)) {
                // Check if password is at least 6 characters
                if (strlen($password) < 6) {
                    flash('error', 'Password must be at least 6 characters');
                    
                    $data = [
                        'title' => 'Edit User',
                        'editUser' => $editUser,
                        'name' => $name,
                        'email' => $email,
                        'role' => $role,
                        'user' => $user
                    ];
                    
                    $this->view('admin/edit_user', $data);
                    return;
                }
                
                // Check if passwords match
                if ($password !== $confirmPassword) {
                    flash('error', 'Passwords do not match');
                    
                    $data = [
                        'title' => 'Edit User',
                        'editUser' => $editUser,
                        'name' => $name,
                        'email' => $email,
                        'role' => $role,
                        'user' => $user
                    ];
                    
                    $this->view('admin/edit_user', $data);
                    return;
                }
                
                // Hash password
                $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            
            // Update user
            $result = $this->userModel->update($id, $updateData);
            
            if ($result) {
                // Set success message
                flash('success', 'User updated successfully');
                
                // Redirect to users page
                redirect(base_url('admin/users'));
            } else {
                // Set error message
                flash('error', 'Failed to update user');
                
                $data = [
                    'title' => 'Edit User',
                    'editUser' => $editUser,
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'user' => $user
                ];
                
                $this->view('admin/edit_user', $data);
            }
        } else {
            // Load the edit view
            $data = [
                'title' => 'Edit User',
                'editUser' => $editUser,
                'name' => $editUser['name'],
                'email' => $editUser['email'],
                'role' => $editUser['role'],
                'user' => $user
            ];
            
            $this->view('admin/edit_user', $data);
        }
    }
    
    /**
     * Delete User method - Delete a user
     * 
     * @param int $id The user ID
     * @return void
     */
    public function delete_user($id) {
        // Check if user is authenticated and is admin
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }
        
        $user = current_user();
        
        if ($user['role'] !== 'admin') {
            flash('error', 'You do not have permission to delete users');
            redirect(base_url('home/index'));
        }
        
        // Get user to delete
        $deleteUser = $this->userModel->getById($id);
        
        if (!$deleteUser) {
            flash('error', 'User not found');
            redirect(base_url('admin/users'));
        }
        
        // Prevent deleting self
        if ($deleteUser['id'] == $user['id']) {
            flash('error', 'You cannot delete your own account');
            redirect(base_url('admin/users'));
        }
        
        // Delete user
        $result = $this->userModel->delete($id);
        
        if ($result) {
            // Set success message
            flash('success', 'User deleted successfully');
        } else {
            // Set error message
            flash('error', 'Failed to delete user');
        }
        
        // Redirect to users page
        redirect(base_url('admin/users'));
    }
    
    /**
     * Assign Business Units method - Assign business units to a manager
     * 
     * @param int $id The manager ID
     * @return void
     */
    public function assign_business_units($id) {
        // Check if user is authenticated and is admin
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }
        
        $user = current_user();
        
        if ($user['role'] !== 'admin') {
            flash('error', 'You do not have permission to assign business units');
            redirect(base_url('home/index'));
        }
        
        // Get manager
        $manager = $this->userModel->getById($id);
        
        if (!$manager) {
            flash('error', 'Manager not found');
            redirect(base_url('admin/users'));
        }
        
        // Check if manager is a manager
        if ($manager['role'] !== 'manager') {
            flash('error', 'User is not a manager');
            redirect(base_url('admin/users'));
        }
        
        // Get all business units
        $businessUnits = $this->businessUnitModel->getAll();
        
        // Get assigned business units
        $assignedBusinessUnits = $this->businessUnitModel->getByManagerId($id);
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get selected business units
            $selectedBusinessUnitIds = isset($_POST['business_units']) ? $_POST['business_units'] : [];
            
            // Remove all business units from manager
            $this->managerBusinessUnitModel->unassignAll($id);
            
            // Assign selected business units
            foreach ($selectedBusinessUnitIds as $businessUnitId) {
                $this->managerBusinessUnitModel->assign($id, $businessUnitId);
            }
            
            // Set success message
            flash('success', 'Business units assigned successfully');
            
            // Redirect to users page
            redirect(base_url('admin/users'));
        } else {
            // Load the assign view
            $data = [
                'title' => 'Assign Business Units',
                'manager' => $manager,
                'businessUnits' => $businessUnits,
                'assignedBusinessUnits' => $assignedBusinessUnits,
                'user' => $user
            ];
            
            $this->view('admin/assign_business_units', $data);
        }
    }
}
