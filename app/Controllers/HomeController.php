<?php
/**
 * Home Controller
 */
class HomeController extends Controller {
    /**
     * Index method - Display the dashboard
     * 
     * @return void
     */
    public function index() {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }
        
        // Load the dashboard view
        $data = [
            'title' => 'Dashboard',
            'user' => current_user()
        ];
        
        $this->view('home/index', $data);
    }
}
