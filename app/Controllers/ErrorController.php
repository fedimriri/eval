<?php
/**
 * Error Controller
 */
class ErrorController extends Controller {
    /**
     * Index method - Display the default error page
     * 
     * @return void
     */
    public function index() {
        $this->view('errors/index', ['title' => 'Error']);
    }
    
    /**
     * Not Found method - Display the 404 error page
     * 
     * @return void
     */
    public function notFound() {
        http_response_code(404);
        $this->view('errors/404', ['title' => 'Page Not Found']);
    }
    
    /**
     * Server Error method - Display the 500 error page
     * 
     * @return void
     */
    public function serverError() {
        http_response_code(500);
        $this->view('errors/500', ['title' => 'Server Error']);
    }
}
