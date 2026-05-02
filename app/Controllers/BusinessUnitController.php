<?php
/**
 * Business Unit Controller
 */
class BusinessUnitController extends Controller {
    private $businessUnitModel;
    private $managerBusinessUnitModel;
    private $userModel;
    
    /**
     * Constructor - Load models
     */
    public function __construct() {
        $this->businessUnitModel = $this->model('BusinessUnitModel');
        $this->managerBusinessUnitModel = $this->model('ManagerBusinessUnitModel');
        $this->userModel = $this->model('UserModel');
    }
    
    /**
     * Index method - Display all business units
     * 
     * @return void
     */
    public function index() {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }
        
        $user = current_user();
        
        // Get business units
        if ($user['role'] === 'admin') {
            $businessUnits = $this->businessUnitModel->getAllWithManagerCount();
        } else {
            $businessUnits = $this->businessUnitModel->getByManagerId($user['id']);
        }
        
        // Load the view
        $data = [
            'title' => 'Business Units',
            'businessUnits' => $businessUnits,
            'user' => $user
        ];
        
        $this->view('businessUnit/index', $data);
    }
    
    /**
     * Create method - Display the create form and process form submission
     * 
     * @return void
     */
    public function create() {
        // Check if user is authenticated and is admin
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }
        
        $user = current_user();
        
        if ($user['role'] !== 'admin') {
            flash('error', 'You do not have permission to create business units');
            redirect(base_url('business_unit'));
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate form data
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            
            // Check if name is empty
            if (empty($name)) {
                flash('error', 'Business unit name is required');
                
                $data = [
                    'title' => 'Create Business Unit',
                    'name' => $name,
                    'description' => $description,
                    'user' => $user
                ];
                
                $this->view('businessUnit/create', $data);
                return;
            }
            
            // Check if name already exists
            if ($this->businessUnitModel->getByName($name)) {
                flash('error', 'Business unit name already exists');
                
                $data = [
                    'title' => 'Create Business Unit',
                    'name' => $name,
                    'description' => $description,
                    'user' => $user
                ];
                
                $this->view('businessUnit/create', $data);
                return;
            }
            
            // Create business unit
            $businessUnitId = $this->businessUnitModel->create([
                'name' => $name,
                'description' => $description
            ]);
            
            if ($businessUnitId) {
                // Set success message
                flash('success', 'Business unit created successfully');
                
                // Redirect to business units page
                redirect(base_url('business_unit'));
            } else {
                // Set error message
                flash('error', 'Failed to create business unit');
                
                $data = [
                    'title' => 'Create Business Unit',
                    'name' => $name,
                    'description' => $description,
                    'user' => $user
                ];
                
                $this->view('businessUnit/create', $data);
            }
        } else {
            // Load the create view
            $data = [
                'title' => 'Create Business Unit',
                'name' => '',
                'description' => '',
                'user' => $user
            ];
            
            $this->view('businessUnit/create', $data);
        }
    }
    
    /**
     * Edit method - Display the edit form and process form submission
     * 
     * @param int $id The business unit ID
     * @return void
     */
    public function edit($id) {
        // Check if user is authenticated and is admin
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }
        
        $user = current_user();
        
        if ($user['role'] !== 'admin') {
            flash('error', 'You do not have permission to edit business units');
            redirect(base_url('business_unit'));
        }
        
        // Get business unit
        $businessUnit = $this->businessUnitModel->getById($id);
        
        if (!$businessUnit) {
            flash('error', 'Business unit not found');
            redirect(base_url('business_unit'));
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate form data
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            
            // Check if name is empty
            if (empty($name)) {
                flash('error', 'Business unit name is required');
                
                $data = [
                    'title' => 'Edit Business Unit',
                    'businessUnit' => $businessUnit,
                    'name' => $name,
                    'description' => $description,
                    'user' => $user
                ];
                
                $this->view('businessUnit/edit', $data);
                return;
            }
            
            // Check if name already exists (excluding current business unit)
            $existingBusinessUnit = $this->businessUnitModel->getByName($name);
            if ($existingBusinessUnit && $existingBusinessUnit['id'] != $id) {
                flash('error', 'Business unit name already exists');
                
                $data = [
                    'title' => 'Edit Business Unit',
                    'businessUnit' => $businessUnit,
                    'name' => $name,
                    'description' => $description,
                    'user' => $user
                ];
                
                $this->view('businessUnit/edit', $data);
                return;
            }
            
            // Update business unit
            $result = $this->businessUnitModel->update($id, [
                'name' => $name,
                'description' => $description
            ]);
            
            if ($result) {
                // Set success message
                flash('success', 'Business unit updated successfully');
                
                // Redirect to business units page
                redirect(base_url('business_unit'));
            } else {
                // Set error message
                flash('error', 'Failed to update business unit');
                
                $data = [
                    'title' => 'Edit Business Unit',
                    'businessUnit' => $businessUnit,
                    'name' => $name,
                    'description' => $description,
                    'user' => $user
                ];
                
                $this->view('businessUnit/edit', $data);
            }
        } else {
            // Load the edit view
            $data = [
                'title' => 'Edit Business Unit',
                'businessUnit' => $businessUnit,
                'name' => $businessUnit['name'],
                'description' => $businessUnit['description'],
                'user' => $user
            ];
            
            $this->view('businessUnit/edit', $data);
        }
    }
    
    /**
     * Delete method - Delete a business unit
     * 
     * @param int $id The business unit ID
     * @return void
     */
    public function delete($id) {
        // Check if user is authenticated and is admin
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }
        
        $user = current_user();
        
        if ($user['role'] !== 'admin') {
            flash('error', 'You do not have permission to delete business units');
            redirect(base_url('business_unit'));
        }
        
        // Get business unit
        $businessUnit = $this->businessUnitModel->getById($id);
        
        if (!$businessUnit) {
            flash('error', 'Business unit not found');
            redirect(base_url('business_unit'));
        }
        
        // Delete business unit
        $result = $this->businessUnitModel->delete($id);
        
        if ($result) {
            // Set success message
            flash('success', 'Business unit deleted successfully');
        } else {
            // Set error message
            flash('error', 'Failed to delete business unit');
        }
        
        // Redirect to business units page
        redirect(base_url('business_unit'));
    }
    
    /**
     * Managers method - Display and manage managers for a business unit
     * 
     * @param int $id The business unit ID
     * @return void
     */
    public function managers($id) {
        // Check if user is authenticated and is admin
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }
        
        $user = current_user();
        
        if ($user['role'] !== 'admin') {
            flash('error', 'You do not have permission to manage business unit managers');
            redirect(base_url('business_unit'));
        }
        
        // Get business unit
        $businessUnit = $this->businessUnitModel->getById($id);
        
        if (!$businessUnit) {
            flash('error', 'Business unit not found');
            redirect(base_url('business_unit'));
        }
        
        // Get managers
        $managers = $this->userModel->getByRole('manager');
        
        // Get assigned managers
        $assignedManagers = [];
        $managerBusinessUnits = $this->managerBusinessUnitModel->getByBusinessUnitId($id);
        
        foreach ($managerBusinessUnits as $mbu) {
            $manager = $this->userModel->getById($mbu['manager_id']);
            if ($manager) {
                $assignedManagers[] = $manager;
            }
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get selected managers
            $selectedManagerIds = isset($_POST['managers']) ? $_POST['managers'] : [];
            
            // Remove all managers from business unit
            $this->managerBusinessUnitModel->unassignAllFromBusinessUnit($id);
            
            // Assign selected managers
            foreach ($selectedManagerIds as $managerId) {
                $this->managerBusinessUnitModel->assign($managerId, $id);
            }
            
            // Set success message
            flash('success', 'Business unit managers updated successfully');
            
            // Redirect to business units page
            redirect(base_url('business_unit'));
        } else {
            // Load the managers view
            $data = [
                'title' => 'Manage Business Unit Managers',
                'businessUnit' => $businessUnit,
                'managers' => $managers,
                'assignedManagers' => $assignedManagers,
                'user' => $user
            ];
            
            $this->view('businessUnit/managers', $data);
        }
    }
}
