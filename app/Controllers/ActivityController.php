<?php
/**
 * Activity Controller
 */
class ActivityController extends Controller {
    private $activityModel;
    private $businessUnitModel;
    private $managerBusinessUnitModel;
    
    /**
     * Constructor - Load models
     */
    public function __construct() {
        $this->activityModel = $this->model('ActivityModel');
        $this->businessUnitModel = $this->model('BusinessUnitModel');
        $this->managerBusinessUnitModel = $this->model('ManagerBusinessUnitModel');
    }
    
    /**
     * Index method - Display all activities
     * 
     * @return void
     */
    public function index() {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }
        
        $user = current_user();
        
        // Get activities
        if ($user['role'] === 'admin') {
            $activities = $this->activityModel->getAllWithBusinessUnitName();
        } else {
            $activities = $this->activityModel->getAccessibleToManager($user['id']);
        }
        
        // Load the view
        $data = [
            'title' => 'Activities',
            'activities' => $activities,
            'user' => $user
        ];
        
        $this->view('activity/index', $data);
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
            flash('error', 'You do not have permission to create activities');
            redirect(base_url('activity'));
        }
        
        // Get business units
        $businessUnits = $this->businessUnitModel->getAll();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate form data
            $name = trim($_POST['name']);
            $businessUnitId = isset($_POST['business_unit_id']) ? (int)$_POST['business_unit_id'] : 0;
            $description = trim($_POST['description']);
            
            // Check if name is empty
            if (empty($name)) {
                flash('error', 'Activity name is required');
                
                $data = [
                    'title' => 'Create Activity',
                    'name' => $name,
                    'business_unit_id' => $businessUnitId,
                    'description' => $description,
                    'businessUnits' => $businessUnits,
                    'user' => $user
                ];
                
                $this->view('activity/create', $data);
                return;
            }
            
            // Check if business unit is selected
            if ($businessUnitId <= 0) {
                flash('error', 'Please select a business unit');
                
                $data = [
                    'title' => 'Create Activity',
                    'name' => $name,
                    'business_unit_id' => $businessUnitId,
                    'description' => $description,
                    'businessUnits' => $businessUnits,
                    'user' => $user
                ];
                
                $this->view('activity/create', $data);
                return;
            }
            
            // Check if business unit exists
            if (!$this->businessUnitModel->getById($businessUnitId)) {
                flash('error', 'Selected business unit does not exist');
                
                $data = [
                    'title' => 'Create Activity',
                    'name' => $name,
                    'business_unit_id' => $businessUnitId,
                    'description' => $description,
                    'businessUnits' => $businessUnits,
                    'user' => $user
                ];
                
                $this->view('activity/create', $data);
                return;
            }
            
            // Create activity
            $activityId = $this->activityModel->create([
                'name' => $name,
                'business_unit_id' => $businessUnitId,
                'description' => $description
            ]);
            
            if ($activityId) {
                // Set success message
                flash('success', 'Activity created successfully');
                
                // Redirect to activities page
                redirect(base_url('activity'));
            } else {
                // Set error message
                flash('error', 'Failed to create activity');
                
                $data = [
                    'title' => 'Create Activity',
                    'name' => $name,
                    'business_unit_id' => $businessUnitId,
                    'description' => $description,
                    'businessUnits' => $businessUnits,
                    'user' => $user
                ];
                
                $this->view('activity/create', $data);
            }
        } else {
            // Load the create view
            $data = [
                'title' => 'Create Activity',
                'name' => '',
                'business_unit_id' => 0,
                'description' => '',
                'businessUnits' => $businessUnits,
                'user' => $user
            ];
            
            $this->view('activity/create', $data);
        }
    }
    
    /**
     * Edit method - Display the edit form and process form submission
     * 
     * @param int $id The activity ID
     * @return void
     */
    public function edit($id) {
        // Check if user is authenticated and is admin
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }
        
        $user = current_user();
        
        if ($user['role'] !== 'admin') {
            flash('error', 'You do not have permission to edit activities');
            redirect(base_url('activity'));
        }
        
        // Get activity
        $activity = $this->activityModel->getById($id);
        
        if (!$activity) {
            flash('error', 'Activity not found');
            redirect(base_url('activity'));
        }
        
        // Get business units
        $businessUnits = $this->businessUnitModel->getAll();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate form data
            $name = trim($_POST['name']);
            $businessUnitId = isset($_POST['business_unit_id']) ? (int)$_POST['business_unit_id'] : 0;
            $description = trim($_POST['description']);
            
            // Check if name is empty
            if (empty($name)) {
                flash('error', 'Activity name is required');
                
                $data = [
                    'title' => 'Edit Activity',
                    'activity' => $activity,
                    'name' => $name,
                    'business_unit_id' => $businessUnitId,
                    'description' => $description,
                    'businessUnits' => $businessUnits,
                    'user' => $user
                ];
                
                $this->view('activity/edit', $data);
                return;
            }
            
            // Check if business unit is selected
            if ($businessUnitId <= 0) {
                flash('error', 'Please select a business unit');
                
                $data = [
                    'title' => 'Edit Activity',
                    'activity' => $activity,
                    'name' => $name,
                    'business_unit_id' => $businessUnitId,
                    'description' => $description,
                    'businessUnits' => $businessUnits,
                    'user' => $user
                ];
                
                $this->view('activity/edit', $data);
                return;
            }
            
            // Check if business unit exists
            if (!$this->businessUnitModel->getById($businessUnitId)) {
                flash('error', 'Selected business unit does not exist');
                
                $data = [
                    'title' => 'Edit Activity',
                    'activity' => $activity,
                    'name' => $name,
                    'business_unit_id' => $businessUnitId,
                    'description' => $description,
                    'businessUnits' => $businessUnits,
                    'user' => $user
                ];
                
                $this->view('activity/edit', $data);
                return;
            }
            
            // Update activity
            $result = $this->activityModel->update($id, [
                'name' => $name,
                'business_unit_id' => $businessUnitId,
                'description' => $description
            ]);
            
            if ($result) {
                // Set success message
                flash('success', 'Activity updated successfully');
                
                // Redirect to activities page
                redirect(base_url('activity'));
            } else {
                // Set error message
                flash('error', 'Failed to update activity');
                
                $data = [
                    'title' => 'Edit Activity',
                    'activity' => $activity,
                    'name' => $name,
                    'business_unit_id' => $businessUnitId,
                    'description' => $description,
                    'businessUnits' => $businessUnits,
                    'user' => $user
                ];
                
                $this->view('activity/edit', $data);
            }
        } else {
            // Load the edit view
            $data = [
                'title' => 'Edit Activity',
                'activity' => $activity,
                'name' => $activity['name'],
                'business_unit_id' => $activity['business_unit_id'],
                'description' => $activity['description'],
                'businessUnits' => $businessUnits,
                'user' => $user
            ];
            
            $this->view('activity/edit', $data);
        }
    }
    
    /**
     * Delete method - Delete an activity
     * 
     * @param int $id The activity ID
     * @return void
     */
    public function delete($id) {
        // Check if user is authenticated and is admin
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }
        
        $user = current_user();
        
        if ($user['role'] !== 'admin') {
            flash('error', 'You do not have permission to delete activities');
            redirect(base_url('activity'));
        }
        
        // Get activity
        $activity = $this->activityModel->getById($id);
        
        if (!$activity) {
            flash('error', 'Activity not found');
            redirect(base_url('activity'));
        }
        
        // Delete activity
        $result = $this->activityModel->delete($id);
        
        if ($result) {
            // Set success message
            flash('success', 'Activity deleted successfully');
        } else {
            // Set error message
            flash('error', 'Failed to delete activity');
        }
        
        // Redirect to activities page
        redirect(base_url('activity'));
    }
}
