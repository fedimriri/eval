<?php
/**
 * App class - The main application class
 */

 require_once dirname(__DIR__) . '/Config/config.php';

class App {
    protected $controller = 'Home';
    protected $method = 'index';
    protected $params = [];

    /**
     * Constructor - Parse URL and load controller
     */
    public function __construct() {
        global $routes;

        // Get URL parts
        $url = $this->parseUrl();
        // Set default controller if URL is empty
        if (empty($url[0])) {
            $this->controller = $routes['default_controller'];
        } else {
            // Convert URL segment to controller name (handle underscores)
            // Split by underscore, capitalize each part, then join without underscores
            $parts = explode('_', $url[0]);
            $parts = array_map('ucfirst', $parts);
            $controllerName = implode('', $parts);

            // Check if controller exists
            if (file_exists(dirname(__DIR__) . '/Controllers/' . $controllerName . 'Controller.php')) {
                $this->controller = $controllerName;
                unset($url[0]);
            } else {
                // If controller doesn't exist, use error controller
                $this->controller = $routes['error_controller'];
                $this->method = 'notFound';
            }
        }
        // Include the controller
        require_once dirname(__DIR__) . '/Controllers/' . $this->controller . 'Controller.php';

        // Instantiate controller
        $controllerName = $this->controller . 'Controller';

        $this->controller = new $controllerName;

        // Check if method exists
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            } else {
                // If method doesn't exist, use notFound method
                $this->method = 'notFound';
            }
        } else {
            // Use default method if not specified
            $this->method = $routes['default_method'];
        }
        // Get parameters
        $this->params = $url ? array_values($url) : [];

        // Call the method with parameters
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    /**
     * Parse URL and return parts
     *
     * @return array URL parts
     */
    protected function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }

        return [];
    }
}
