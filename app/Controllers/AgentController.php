<?php
/**
 * Agent Controller
 */
class AgentController extends Controller {
    private $agentModel;
    private $activityModel;
    private $businessUnitModel;
    private $managerBusinessUnitModel;
    
    /**
     * Constructor - Load models
     */
    public function __construct() {
        $this->agentModel = $this->model('AgentModel');
        $this->activityModel = $this->model('ActivityModel');
        $this->businessUnitModel = $this->model('BusinessUnitModel');
        $this->managerBusinessUnitModel = $this->model('ManagerBusinessUnitModel');
    }
    
    /**
     * Index method - Display all agents
     * 
     * @return void
     */
    public function index() {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }
        
        $user = current_user();
        
        // Get agents
        if ($user['role'] === 'admin') {
            $agents = $this->agentModel->getAllWithDetails();
        } else {
            $agents = $this->agentModel->getAccessibleToManager($user['id']);
        }
        
        // Load the view
        $data = [
            'title' => 'Agents',
            'agents' => $agents,
            'user' => $user
        ];
        
        $this->view('agent/index', $data);
    }
    
    /**
     * Create method - Display the create form and process form submission
     * 
     * @return void
     */
    public function create() {
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
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate form data
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $activityId = isset($_POST['activity_id']) ? (int)$_POST['activity_id'] : 0;
            
            // Check if name is empty
            if (empty($name)) {
                flash('error', 'Agent name is required');
                
                $data = [
                    'title' => 'Create Agent',
                    'name' => $name,
                    'email' => $email,
                    'activity_id' => $activityId,
                    'activities' => $activities,
                    'user' => $user
                ];
                
                $this->view('agent/create', $data);
                return;
            }
            
            // Check if activity is selected
            if ($activityId <= 0) {
                flash('error', 'Please select an activity');
                
                $data = [
                    'title' => 'Create Agent',
                    'name' => $name,
                    'email' => $email,
                    'activity_id' => $activityId,
                    'activities' => $activities,
                    'user' => $user
                ];
                
                $this->view('agent/create', $data);
                return;
            }
            
            // Check if activity exists
            if (!$this->activityModel->getById($activityId)) {
                flash('error', 'Selected activity does not exist');
                
                $data = [
                    'title' => 'Create Agent',
                    'name' => $name,
                    'email' => $email,
                    'activity_id' => $activityId,
                    'activities' => $activities,
                    'user' => $user
                ];
                
                $this->view('agent/create', $data);
                return;
            }
            
            // Check if email is valid
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash('error', 'Invalid email format');
                
                $data = [
                    'title' => 'Create Agent',
                    'name' => $name,
                    'email' => $email,
                    'activity_id' => $activityId,
                    'activities' => $activities,
                    'user' => $user
                ];
                
                $this->view('agent/create', $data);
                return;
            }
            
            // Check if email already exists
            if (!empty($email) && $this->agentModel->getByEmail($email)) {
                flash('error', 'Email already exists');
                
                $data = [
                    'title' => 'Create Agent',
                    'name' => $name,
                    'email' => $email,
                    'activity_id' => $activityId,
                    'activities' => $activities,
                    'user' => $user
                ];
                
                $this->view('agent/create', $data);
                return;
            }
            
            // Create agent
            $agentId = $this->agentModel->create([
                'name' => $name,
                'email' => $email,
                'activity_id' => $activityId
            ]);
            
            if ($agentId) {
                // Set success message
                flash('success', 'Agent created successfully');
                
                // Redirect to agents page
                redirect(base_url('agent'));
            } else {
                // Set error message
                flash('error', 'Failed to create agent');
                
                $data = [
                    'title' => 'Create Agent',
                    'name' => $name,
                    'email' => $email,
                    'activity_id' => $activityId,
                    'activities' => $activities,
                    'user' => $user
                ];
                
                $this->view('agent/create', $data);
            }
        } else {
            // Load the create view
            $data = [
                'title' => 'Create Agent',
                'name' => '',
                'email' => '',
                'activity_id' => 0,
                'activities' => $activities,
                'user' => $user
            ];
            
            $this->view('agent/create', $data);
        }
    }
    
    /**
     * Edit method - Display the edit form and process form submission
     * 
     * @param int $id The agent ID
     * @return void
     */
    public function edit($id) {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }
        
        $user = current_user();
        
        // Get agent
        $agent = $this->agentModel->getById($id);
        
        if (!$agent) {
            flash('error', 'Agent not found');
            redirect(base_url('agent'));
        }
        
        // Check if user has access to this agent
        if ($user['role'] !== 'admin') {
            $accessibleAgents = $this->agentModel->getAccessibleToManager($user['id']);
            $hasAccess = false;
            
            foreach ($accessibleAgents as $accessibleAgent) {
                if ($accessibleAgent['id'] == $id) {
                    $hasAccess = true;
                    break;
                }
            }
            
            if (!$hasAccess) {
                flash('error', 'You do not have permission to edit this agent');
                redirect(base_url('agent'));
            }
        }
        
        // Get activities
        if ($user['role'] === 'admin') {
            $activities = $this->activityModel->getAllWithBusinessUnitName();
        } else {
            $activities = $this->activityModel->getAccessibleToManager($user['id']);
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate form data
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $activityId = isset($_POST['activity_id']) ? (int)$_POST['activity_id'] : 0;
            
            // Check if name is empty
            if (empty($name)) {
                flash('error', 'Agent name is required');
                
                $data = [
                    'title' => 'Edit Agent',
                    'agent' => $agent,
                    'name' => $name,
                    'email' => $email,
                    'activity_id' => $activityId,
                    'activities' => $activities,
                    'user' => $user
                ];
                
                $this->view('agent/edit', $data);
                return;
            }
            
            // Check if activity is selected
            if ($activityId <= 0) {
                flash('error', 'Please select an activity');
                
                $data = [
                    'title' => 'Edit Agent',
                    'agent' => $agent,
                    'name' => $name,
                    'email' => $email,
                    'activity_id' => $activityId,
                    'activities' => $activities,
                    'user' => $user
                ];
                
                $this->view('agent/edit', $data);
                return;
            }
            
            // Check if activity exists
            if (!$this->activityModel->getById($activityId)) {
                flash('error', 'Selected activity does not exist');
                
                $data = [
                    'title' => 'Edit Agent',
                    'agent' => $agent,
                    'name' => $name,
                    'email' => $email,
                    'activity_id' => $activityId,
                    'activities' => $activities,
                    'user' => $user
                ];
                
                $this->view('agent/edit', $data);
                return;
            }
            
            // Check if email is valid
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash('error', 'Invalid email format');
                
                $data = [
                    'title' => 'Edit Agent',
                    'agent' => $agent,
                    'name' => $name,
                    'email' => $email,
                    'activity_id' => $activityId,
                    'activities' => $activities,
                    'user' => $user
                ];
                
                $this->view('agent/edit', $data);
                return;
            }
            
            // Check if email already exists (excluding current agent)
            if (!empty($email)) {
                $existingAgent = $this->agentModel->getByEmail($email);
                if ($existingAgent && $existingAgent['id'] != $id) {
                    flash('error', 'Email already exists');
                    
                    $data = [
                        'title' => 'Edit Agent',
                        'agent' => $agent,
                        'name' => $name,
                        'email' => $email,
                        'activity_id' => $activityId,
                        'activities' => $activities,
                        'user' => $user
                    ];
                    
                    $this->view('agent/edit', $data);
                    return;
                }
            }
            
            // Update agent
            $result = $this->agentModel->update($id, [
                'name' => $name,
                'email' => $email,
                'activity_id' => $activityId
            ]);
            
            if ($result) {
                // Set success message
                flash('success', 'Agent updated successfully');
                
                // Redirect to agents page
                redirect(base_url('agent'));
            } else {
                // Set error message
                flash('error', 'Failed to update agent');
                
                $data = [
                    'title' => 'Edit Agent',
                    'agent' => $agent,
                    'name' => $name,
                    'email' => $email,
                    'activity_id' => $activityId,
                    'activities' => $activities,
                    'user' => $user
                ];
                
                $this->view('agent/edit', $data);
            }
        } else {
            // Load the edit view
            $data = [
                'title' => 'Edit Agent',
                'agent' => $agent,
                'name' => $agent['name'],
                'email' => $agent['email'],
                'activity_id' => $agent['activity_id'],
                'activities' => $activities,
                'user' => $user
            ];
            
            $this->view('agent/edit', $data);
        }
    }
    
    /**
     * Delete method - Delete an agent
     * 
     * @param int $id The agent ID
     * @return void
     */
    public function delete($id) {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }
        
        $user = current_user();
        
        // Get agent
        $agent = $this->agentModel->getById($id);
        
        if (!$agent) {
            flash('error', 'Agent not found');
            redirect(base_url('agent'));
        }
        
        // Check if user has access to this agent
        if ($user['role'] !== 'admin') {
            $accessibleAgents = $this->agentModel->getAccessibleToManager($user['id']);
            $hasAccess = false;
            
            foreach ($accessibleAgents as $accessibleAgent) {
                if ($accessibleAgent['id'] == $id) {
                    $hasAccess = true;
                    break;
                }
            }
            
            if (!$hasAccess) {
                flash('error', 'You do not have permission to delete this agent');
                redirect(base_url('agent'));
            }
        }
        
        // Delete agent
        $result = $this->agentModel->delete($id);
        
        if ($result) {
            // Set success message
            flash('success', 'Agent deleted successfully');
        } else {
            // Set error message
            flash('error', 'Failed to delete agent');
        }
        
        // Redirect to agents page
        redirect(base_url('agent'));
    }
}
